# Auditoría de seguridad — API Market KASU (`/apimarket`)

- **Fecha:** 2026-08-14
- **Alcance:** 37 archivos del módulo `apimarket/` (páginas públicas, documentación, helpers, endpoints de API, módulo NFT y conexiones).
- **Estado:** Fase 1 de correcciones aplicada (CRIT-01, HIGH-01, HIGH-02, HIGH-03).
- **Pendiente:** Rediseño de autenticación (HIGH-04) y hardening de severidad media/baja.

---

## 1. Resumen ejecutivo

Los endpoints principales (`Accounts_V1`, `Customer_V1`, `Payments_V1`, `ValidateMexico_V1`) están bien construidos en lo fundamental: consultas preparadas, transacciones con rollback, headers de seguridad y rate limiting. Sin embargo, existían **1 hallazgo crítico y varios altos** concentrados en tres frentes:

1. Secretos en código y autorización *fail-open*.
2. Endpoints operativos del proveedor sin control de permisos.
3. Controles perimetrales débiles (CSRF, rate-limit, archivos internos expuestos).

---

## 2. Resumen de hallazgos

| ID | Severidad | Título | Estado |
|----|-----------|--------|--------|
| CRIT-01 | Crítico | Clave interna con *fallback* hardcodeado en `mint_signature.php` | ✅ Corregido |
| HIGH-01 | Alto | Endpoints `upstream_saldo`/`upstream_peticiones` sin control de permisos | ✅ Corregido |
| HIGH-02 | Alto | CSRF inefectivo en `contacto.php` | ✅ Corregido |
| HIGH-03 | Alto | `api_access_has_grant` con lógica *fail-open* | ✅ Corregido |
| HIGH-04 | Alto | Autenticación dependiente del `User-Agent` como identificador de clave | ✅ Corregido (Fase 3: X-API-Key + panel) |
| MED-01 | Medio | Archivos internos accesibles por HTTP (`Funciones/`) | ✅ Corregido |
| MED-02 | Medio | Inyección SQL latente en helpers legacy (`Funciones_Basicas.php`) | ⏳ Pendiente |
| MED-03 | Medio | Salida sin escapar en `versiones.php` / `Inf_general.php` (XSS almacenado) | ⏳ Pendiente |
| MED-04 | Medio | Rate limit evadible por cabeceras `X-Forwarded-For` | ⏳ Pendiente |
| MED-05 | Medio | Endpoint sandbox desplegable en producción (`Registro_sbx.php`) | ✅ Corregido |
| MED-06 | Medio | Datos personales de prueba en código (CURPs en `Registro_sbx.php`) | ⏳ Pendiente |
| MED-07 | Medio | Credenciales upstream en URL (`conectame.ddns.net`) | ⏳ Pendiente |
| LOW-01..07 | Bajo | Hash sha3 vs keccak, AES-CBC sin auth, bind frágil, proxy bypasseable, DoS PNG, host/usr de BD hardcodeados, `.DS_Store` | ⏳ Pendiente |

---

## 3. Detalle de hallazgos corregidos (Fase 1 y 1.5)

### CRIT-01 — Clave interna con *fallback* hardcodeado
**Archivo:** `api/nft/mint_signature.php`

Antes:
```php
$adminKey = getenv('KASU_INTERNAL_API_KEY') ?: 'CLAVE_INTERNA_KASU_123';
```

Después:
```php
$adminKey = getenv('KASU_INTERNAL_API_KEY') ?: '';
if ($adminKey === '') {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Configuracion interna incompleta: falta KASU_INTERNAL_API_KEY.'], JSON_UNESCAPED_UNICODE);
    exit;
}
```

**Impacto eliminado:** si la variable de entorno no está definida, el oráculo de minteo dejaba de ser accesible con una clave pública del repositorio. Ahora aborta con `500`.

### HIGH-03 — `api_access_has_grant` fail-closed
**Archivo:** `Funciones/Funciones_ApiAccess.php`

Se invirtió toda la lógica de autorización: cualquier condición que impida verificar un grant explícito (conexión no disponible, tabla inexistente, usuario sin grants, excepción) ahora retorna `false` (denegado) en lugar de `true` (permitido).

**⚠️ Nota de despliegue:** este cambio exige que la tabla `api_access_grants` exista y que **todos** los usuarios API tengan sus grants sincronizados **antes** de desplegar. Si la tabla no está migrada o un usuario no tiene filas de grant, se denegará el acceso (comportamiento seguro, pero rompe silenciosamente si no se prepara la BD).

### HIGH-01 — Restricción de endpoints operativos upstream
**Archivo:** `api/ValidateMexico_V1.php`

Antes, `upstream_saldo` y `upstream_peticiones` solo exigían un Bearer token válido de cualquier usuario. Ahora exigen un grant operativo explícito:

```php
if ($metodo === 'upstream_saldo' || $metodo === 'upstream_peticiones') {
    if (!api_access_has_grant($mysqli_api, $usuario, 'Validate_Mexico:admin')) {
        jout(['ok'=>false,'error'=>'Permiso insuficiente para operacion upstream'], 403);
    }
}
```

**Acción de despliegue:** crear la fila de grant `(api_user, 'Validate_Mexico:admin', 1)` en `api_access_grants` únicamente para usuarios operativos autorizados.

### HIGH-02 — CSRF fail-closed en `contacto.php`
**Archivo:** `contacto.php`

Antes:
```php
$csrf_ok = true;
if (isset($_POST['csrf'], $_SESSION['csrf_auth'])) {
    $csrf_ok = hash_equals(...);
}
```

Después:
```php
$csrf_ok = isset($_POST['csrf'], $_SESSION['csrf_auth'])
    && hash_equals((string)$_SESSION['csrf_auth'], (string)$_POST['csrf']);
```

Ahora la ausencia del token CSRF invalida la petición.

### MED-01 — Bloqueo de archivos internos
**Archivo:** `.htaccess`

Se añadió `RewriteRule ^Funciones/ - [F,L]` (bloquea todo el directorio de helpers; regla relativa, portable a subdominio y subdirectorio) y un `<FilesMatch "\.(log|txt|env|md)$">` que deniega logs, reportes, variables de entorno y la propia `AUDITORIA.md`.

### MED-05 — Guardia de entorno en sandbox
**Archivo:** `api/Registro_sbx.php`

Se añadió una guardia que aborta con `403` si `getenv('APIMARKET_ENV') !== 'sandbox'`, ejecutada antes de cualquier escritura a BD.

---

## 4. Hallazgos pendientes (Fase 3 y posteriores)

### HIGH-04 — Autenticación dependiente del `User-Agent`
El identificador de la *Secret_KEY* (`USUARIO_ID`) viaja en la cabecera `User-Agent` y se persiste en texto plano en `Eventos.Usuario` y en el *usage tracker*. El esquema no escala a clientes automatizados (IAs, bots) que rotan o suplantan cabeceras con facilidad.

**Dirección propuesta:** migrar a `X-API-Key` o token bearer estándar (ver sección 6).

### MED-02 — Inyección SQL latente
`Funciones_Basicas.php` (`BuscarCampos`, `Buscar2Campos`, `Buscar3Campos`, `ConUno`, `InsertCampo`, `MaxDat`) interpola con `mysqli_real_escape_string`, y en `InsertCampo`/`MaxDat` los nombres de tabla/columna no se validan. Migrar a prepared statements o validar identificadores con `api_assert_identifier()`.

### MED-03 — XSS almacenado potencial
`html/versiones.php` y `html/Inf_general.php` imprimen contenido de `ContApiMarket` sin `htmlspecialchars`. Escapar siempre o permitir HTML con whitelist.

### MED-04 — Rate limit evadible
`api_client_ip()` confía en `X-Forwarded-For`/`CF-Connecting-IP` sin validar; un cliente puede rotar XFF por petición y anular el límite.

### MED-06 — Datos personales de prueba
Tres CURPs con formato de CURP válida en `Registro_sbx.php`. Verificar que sean ficticias y no versionar datos con apariencia de dato personal.

### MED-07 — Credenciales upstream en URL
`Funciones_Seguridad.php` envía `CONECTAME_USER/PASS` en la query string a `https://conectame.ddns.net`. Usar POST/headers y validar TLS.

### LOW-01..07
- `mint_signature.php` usa `hash('sha3-256')` (NIST) documentado como `keccak256` (Solidity); no son el mismo algoritmo.
- `ValidateMexico_V1.php` cifra caché con AES-256-CBC sin autenticación (sin HMAC/GCM).
- `ValidateMexico_V1.php` enlaza `costo` (int) como `'s'` en `bind_param` (funciona por coerción).
- `blog_proxy.php` chequeo de referer bypasseable (proxy abierto de facto, bajo impacto).
- `image.php` regenera PNG por petición sin caché server-side ni rate-limit.
- `eia/Conexiones/cn_vtas.php` host/usuario de BD hardcodeados como fallback.
- Archivos `.DS_Store` versionados.

---

## 5. Buenas prácticas observadas

- Prepared statements en los endpoints V1.
- `password_hash`/`password_verify` en el portal de acceso.
- CSRF correcto (obligatorio + `hash_equals`) en `acceso.php`.
- Headers de seguridad y CSP en respuestas JSON.
- Transacciones con rollback en alta de servicios, pagos y wallet.
- Cifrado de caché en reposo (pendiente de autenticar).
- `.env` y `error.log` bloqueados en `.htaccess`; `Options -Indexes`.
- Escapado de salida (`htmlspecialchars`) en la mayoría de vistas.

---

## 6. Plan de remediación restante

| Orden | Hallazgo | Esfuerzo | Fase |
|---|---|---|---|
| 1 | HIGH-04 — rediseño de autenticación | Alto | Fase 3 |
| 2 | MED-01 — bloquear `Funciones/` y logs | Bajo | Fase 2 |
| 3 | MED-05 — guardia de sandbox | Bajo | Fase 2 |
| 4 | MED-02 / MED-03 — prepared statements y escape | Medio | Fase 2 |
| 5 | MED-04 / MED-07 — hardening IP y upstream | Medio | Fase 2 |
| 6 | LOW-01..07 — ajustes menores | Bajo/Variable | Fase 2 |
