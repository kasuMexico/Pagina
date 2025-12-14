#!/usr/bin/env python3
"""
portfolio_sync.py
Sincroniza precios diarios y dividendos desde Yahoo Finance hacia MySQL.

- Usa valores hardcode como fallback si no hay variables de entorno
- Símbolos origen: DISTINCT symbol de port_trades
- Mapea a Yahoo con port_symbol_map (symbol_input -> symbol_yahoo) solo para consulta; guarda con el símbolo interno
- Inserta/actualiza en port_prices_daily y port_dividends (ON DUPLICATE KEY UPDATE)
- Evita ejecuciones simultáneas con lock file
- Filtra dividendos a ~400 días para rendimiento
- Rollback por símbolo si falla (no deja transacción sucia)
"""

from __future__ import annotations

import os
import sys
import time
import traceback
from datetime import datetime, timedelta, date
from typing import Dict, List, Tuple, Set, Any

import mysql.connector
import yfinance as yf

LOCK_PATH_PRIMARY = "/tmp/portfolio_sync.lock"
SCRIPT_DIR = os.path.dirname(os.path.abspath(__file__))
LOCK_PATH_FALLBACK = os.path.join(SCRIPT_DIR, "portfolio_sync.lock")


def log(msg: str) -> None:
    ts = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    print(f"[{ts}] {msg}", flush=True)


def acquire_lock() -> str:
    path = LOCK_PATH_PRIMARY if os.access("/tmp", os.W_OK) else LOCK_PATH_FALLBACK
    if os.path.exists(path):
        try:
            with open(path, "r", encoding="utf-8") as f:
                pid_str = f.read().strip()
            pid = int(pid_str) if pid_str else 0
            if pid > 0:
                os.kill(pid, 0)  # si existe, no lanza
                log(f"Lock activo (PID {pid}); abortando.")
                sys.exit(0)
        except FileNotFoundError:
            pass
        except ProcessLookupError:
            log("Lock stale detectado; se reemplaza.")
        except PermissionError:
            log("Sin permisos para verificar PID; saliendo por seguridad.")
            sys.exit(1)
        except Exception as e:
            log(f"No se pudo verificar lock: {e}; saliendo por seguridad.")
            sys.exit(1)

    with open(path, "w", encoding="utf-8") as f:
        f.write(str(os.getpid()))
    return path


def release_lock(path: str) -> None:
    try:
        if path and os.path.exists(path):
            os.remove(path)
    except Exception:
        pass
    # limpieza adicional (por si cambió el path)
    for p in (LOCK_PATH_PRIMARY, LOCK_PATH_FALLBACK):
        try:
            if p != path and os.path.exists(p):
                os.remove(p)
        except Exception:
            pass


def get_db_connection():
    # Obtener de variables de entorno, usar valores hardcode como fallback
    # Compatible con portfolio_sync.php
    host = os.getenv("DB_HOST", "srv908.hstgr.io")
    db = os.getenv("DB_NAME", "u557645733_web")
    user = os.getenv("DB_USER", "u557645733_kasuw")
    pwd = os.getenv("DB_PASS", ";9Ai!5;G0QU")
    port = int(os.getenv("DB_PORT", "3306"))

    # Log para depuración
    log(f"Conectando a BD - Host: {host}, DB: {db}, User: {user}, Port: {port}")

    try:
        conn = mysql.connector.connect(
            host=host,
            user=user,
            password=pwd,
            database=db,
            port=port,
            charset="utf8mb4",
            use_unicode=True,
            autocommit=False,
        )
        log("Conexión a MySQL establecida exitosamente")
        return conn
    except mysql.connector.Error as e:
        log(f"Error de conexión MySQL: {e}")
        raise
    except Exception as e:
        log(f"Error inesperado en conexión: {e}")
        raise


def get_columns(conn, table: str) -> Set[str]:
    cols: Set[str] = set()
    cur = conn.cursor()
    try:
        cur.execute(f"SHOW COLUMNS FROM {table}")
        for row in cur.fetchall():
            cols.add(row[0])
    finally:
        cur.close()
    return cols


def fetch_symbols(conn) -> List[str]:
    cur = conn.cursor()
    symbols = []
    try:
        cur.execute("SELECT DISTINCT symbol FROM port_trades WHERE symbol IS NOT NULL AND symbol != ''")
        symbols = [r[0] for r in cur.fetchall() if r and r[0]]
    finally:
        cur.close()
    return symbols


def fetch_symbol_map(conn) -> Dict[str, str]:
    cur = conn.cursor()
    out = {}
    try:
        cur.execute("SELECT symbol_input, symbol_yahoo FROM port_symbol_map WHERE symbol_input IS NOT NULL AND symbol_yahoo IS NOT NULL")
        for r in cur.fetchall():
            if r and r[0] and r[1]:
                out[r[0]] = r[1]
    finally:
        cur.close()
    return out


def build_price_insert_query(columns: Set[str]) -> Tuple[str, List[str]]:
    base_cols = ["symbol", "price_date"]
    optional = ["open", "high", "low", "close", "adj_close", "volume"]
    cols = [c for c in base_cols + optional if c in columns]

    placeholders = ", ".join(["%s"] * len(cols))
    update_clause = ", ".join(
        [f"{c}=VALUES({c})" for c in cols if c not in ("symbol", "price_date")]
    )

    query = f"INSERT INTO port_prices_daily ({', '.join(cols)}) VALUES ({placeholders})"
    if update_clause:
        query += f" ON DUPLICATE KEY UPDATE {update_clause}"
    
    log(f"Query para precios: {query[:100]}...")
    return query, cols


def build_dividend_insert_query(columns: Set[str]) -> Tuple[str, List[str]]:
    base_cols = ["symbol", "pay_date"]
    optional = ["dividend_per_share"]
    cols = [c for c in base_cols + optional if c in columns]

    placeholders = ", ".join(["%s"] * len(cols))
    update_clause = ", ".join(
        [f"{c}=VALUES({c})" for c in cols if c not in ("symbol", "pay_date")]
    )

    query = f"INSERT INTO port_dividends ({', '.join(cols)}) VALUES ({placeholders})"
    if update_clause:
        query += f" ON DUPLICATE KEY UPDATE {update_clause}"
    
    log(f"Query para dividendos: {query[:100]}...")
    return query, cols


def to_date_str(dt_obj: Any) -> str:
    """
    Importante: NO convertir a UTC (evita corrimiento de día).
    Tomamos la fecha tal cual venga del índice/serie.
    """
    if hasattr(dt_obj, "to_pydatetime"):
        dt_obj = dt_obj.to_pydatetime()

    if isinstance(dt_obj, datetime):
        return dt_obj.date().isoformat()

    if isinstance(dt_obj, date):
        return dt_obj.isoformat()

    try:
        return dt_obj.isoformat()
    except Exception:
        return str(dt_obj)


def _safe_float(v: Any) -> float:
    try:
        return float(v)
    except Exception:
        return 0.0


def sync_prices(conn, internal_symbol: str, yahoo_symbol: str, price_cols: Set[str]) -> int:
    log(f"Obteniendo precios para {yahoo_symbol}...")
    try:
        ticker = yf.Ticker(yahoo_symbol)
        hist = ticker.history(period="400d", actions=False, auto_adjust=False)

        if hist is None or hist.empty:
            log(f"No hay datos históricos para {yahoo_symbol}")
            return 0

        query, cols = build_price_insert_query(price_cols)

        rows = []
        for idx, row in hist.iterrows():
            price_date = to_date_str(idx)
            data = {
                "symbol": internal_symbol,
                "price_date": price_date,
                "open": _safe_float(row.get("Open")) if "open" in price_cols else None,
                "high": _safe_float(row.get("High")) if "high" in price_cols else None,
                "low": _safe_float(row.get("Low")) if "low" in price_cols else None,
                "close": _safe_float(row.get("Close")) if "close" in price_cols else None,
                "adj_close": _safe_float(row.get("Adj Close")) if "adj_close" in price_cols else None,
                "volume": _safe_float(row.get("Volume")) if "volume" in price_cols else None,
            }
            rows.append(tuple(data[c] for c in cols))

        if not rows:
            log(f"No hay filas para insertar para {yahoo_symbol}")
            return 0

        cur = conn.cursor()
        cur.executemany(query, rows)
        affected = cur.rowcount
        cur.close()
        
        log(f"Precios sincronizados para {yahoo_symbol}: {affected} filas")
        return affected
        
    except Exception as e:
        log(f"Error en sync_prices para {yahoo_symbol}: {e}")
        raise


def sync_dividends(conn, internal_symbol: str, yahoo_symbol: str, div_cols: Set[str]) -> int:
    log(f"Obteniendo dividendos para {yahoo_symbol}...")
    try:
        ticker = yf.Ticker(yahoo_symbol)
        divs = ticker.dividends

        if divs is None or divs.empty:
            log(f"No hay dividendos para {yahoo_symbol}")
            return 0

        # Filtrar a ~400 días (menos carga)
        cutoff = (datetime.now().date() - timedelta(days=400))

        query, cols = build_dividend_insert_query(div_cols)

        rows = []
        for pay_date, value in divs.items():
            # pay_date puede ser Timestamp
            if hasattr(pay_date, "to_pydatetime"):
                d = pay_date.to_pydatetime().date()
            elif isinstance(pay_date, datetime):
                d = pay_date.date()
            elif isinstance(pay_date, date):
                d = pay_date
            else:
                # fallback: parse de string
                try:
                    d = datetime.fromisoformat(str(pay_date)).date()
                except Exception:
                    continue

            if d < cutoff:
                continue

            data = {
                "symbol": internal_symbol,
                "pay_date": d.isoformat(),
                "dividend_per_share": _safe_float(value),
            }
            rows.append(tuple(data[c] for c in cols))

        if not rows:
            log(f"No hay filas de dividendos para insertar para {yahoo_symbol}")
            return 0

        cur = conn.cursor()
        cur.executemany(query, rows)
        affected = cur.rowcount
        cur.close()
        
        log(f"Dividendos sincronizados para {yahoo_symbol}: {affected} filas")
        return affected
        
    except Exception as e:
        log(f"Error en sync_dividends para {yahoo_symbol}: {e}")
        raise


def main() -> None:
    lock_path = acquire_lock()
    start = time.time()
    log("Inicio sincronización de portafolio.")

    conn = None
    try:
        conn = get_db_connection()
    except Exception as e:
        log(f"No se pudo conectar a MySQL: {e}")
        release_lock(lock_path)
        sys.exit(1)

    try:
        log("Obteniendo estructura de tablas...")
        price_cols = get_columns(conn, "port_prices_daily")
        div_cols = get_columns(conn, "port_dividends")
        
        log(f"Columnas en port_prices_daily: {sorted(price_cols)}")
        log(f"Columnas en port_dividends: {sorted(div_cols)}")
        
        symbols = fetch_symbols(conn)
        symbol_map = fetch_symbol_map(conn)

        log(f"Mapeo de símbolos encontrado: {symbol_map}")

        if not symbols:
            log("No hay símbolos en port_trades; nada que sincronizar.")
            return

        log(f"{len(symbols)} símbolos encontrados: {symbols}")

        total_prices = 0
        total_divs = 0
        ok_symbols = 0
        fail_symbols = 0

        for sym in symbols:
            yahoo_sym = symbol_map.get(sym, sym)
            log(f"Procesando {sym} (Yahoo: {yahoo_sym})")

            try:
                aff_p = sync_prices(conn, sym, yahoo_sym, price_cols)
                aff_d = sync_dividends(conn, sym, yahoo_sym, div_cols)

                conn.commit()  # commit por símbolo (controlado)
                total_prices += aff_p
                total_divs += aff_d
                ok_symbols += 1

                log(f"{sym}: precios upsert={aff_p}, dividendos upsert={aff_d}")

            except Exception as e:
                fail_symbols += 1
                try:
                    conn.rollback()
                except Exception:
                    pass
                log(f"Error en {sym}: {e}")
                traceback.print_exc()

        elapsed = time.time() - start
        log(
            "Fin sincronización. "
            f"Símbolos OK={ok_symbols}, FAIL={fail_symbols}. "
            f"Totales -> precios upsert: {total_prices}, dividendos upsert: {total_divs}. "
            f"Tiempo: {elapsed:.1f}s"
        )

    except Exception as e:
        log(f"Error general en la sincronización: {e}")
        traceback.print_exc()
        if conn is not None:
            try:
                conn.rollback()
            except Exception:
                pass
        
    finally:
        try:
            if conn is not None:
                conn.close()
                log("Conexión a BD cerrada")
        except Exception:
            pass
        release_lock(lock_path)


if __name__ == "__main__":
    main()