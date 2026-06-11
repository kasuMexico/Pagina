-- Registra el inicio real del periodo de activación y regulariza pólizas históricas.

SET @existe_fecha_liquidacion = (
  SELECT COUNT(*)
  FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'Venta'
    AND column_name = 'FechaLiquidacion'
);
SET @sql_fecha_liquidacion = IF(
  @existe_fecha_liquidacion = 0,
  'ALTER TABLE Venta ADD COLUMN FechaLiquidacion DATETIME NULL AFTER Status',
  'SELECT 1'
);
PREPARE stmt_fecha_liquidacion FROM @sql_fecha_liquidacion;
EXECUTE stmt_fecha_liquidacion;
DEALLOCATE PREPARE stmt_fecha_liquidacion;

SET @existe_indice_liquidacion = (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'Venta'
    AND index_name = 'idx_venta_status_fecha_liquidacion'
);
SET @sql_indice_liquidacion = IF(
  @existe_indice_liquidacion = 0,
  'ALTER TABLE Venta ADD INDEX idx_venta_status_fecha_liquidacion (Status, FechaLiquidacion)',
  'SELECT 1'
);
PREPARE stmt_indice_liquidacion FROM @sql_indice_liquidacion;
EXECUTE stmt_indice_liquidacion;
DEALLOCATE PREPARE stmt_indice_liquidacion;

UPDATE Venta v
LEFT JOIN (
  SELECT IdVenta, MAX(FechaRegistro) AS FechaUltimoPago
  FROM Pagos
  WHERE status IS NULL OR status != 'Mora'
  GROUP BY IdVenta
) p ON p.IdVenta = v.Id
SET v.FechaLiquidacion = COALESCE(v.FechaLiquidacion, p.FechaUltimoPago, v.FechaRegistro)
WHERE v.Status IN ('ACTIVACION', 'ACTIVO')
  AND v.FechaLiquidacion IS NULL;

UPDATE Venta
SET Status = 'ACTIVO'
WHERE Status = 'ACTIVACION'
  AND DATE(FechaLiquidacion) <= DATE_SUB(CURDATE(), INTERVAL 30 DAY);
