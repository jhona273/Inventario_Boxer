<?php
// historial.php
// Muestra TODO lo que se ha movido: un resumen agrupado por día
// (cuánto entró y salió cada día) y el detalle movimiento por movimiento.

require 'auth.php';
require 'config.php';

// --- Resumen agrupado por día ---
// GROUP BY junta todas las filas que compartan la misma fecha+tipo
// y SUM() las suma. Así sacamos "el 4 de septiembre entraron 40 y
// salieron 12" sin tener que sumar nada a mano.
$resumen = $pdo->query("
    SELECT
        DATE(fecha) AS dia,
        SUM(CASE WHEN tipo = 'entrada' THEN cantidad ELSE 0 END) AS total_entradas,
        SUM(CASE WHEN tipo = 'salida'  THEN cantidad ELSE 0 END) AS total_salidas
    FROM movimientos
    GROUP BY DATE(fecha)
    ORDER BY dia DESC
    LIMIT 30
")->fetchAll();

// --- Detalle: los últimos 100 movimientos, uno por uno ---
$detalle = $pdo->query("
    SELECT m.*, p.codigo, p.descripcion, p.talla
    FROM movimientos m
    JOIN productos p ON p.id = m.producto_id
    ORDER BY m.fecha DESC, m.id DESC
    LIMIT 100
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Historial · Inventario</title>
  <link rel="stylesheet" href="estilo.css?v=2">
</head>
<body>

  <header class="topbar">
    <h1>Historial de movimientos</h1>
    <a href="index.php" class="btn btn--secundario" style="text-decoration:none;">← Volver al inventario</a>
  </header>

  <main class="contenido">

    <h2 style="font-size:14px; margin-bottom:10px;">Resumen por día</h2>
    <table class="tabla" style="margin-bottom:30px;">
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Entradas</th>
          <th>Salidas</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($resumen as $r): ?>
          <tr>
            <td data-label="Fecha"><?php echo date('d/m/Y', strtotime($r['dia'])); ?></td>
            <td data-label="Entradas"><span style="color:var(--entrada-texto);">+<?php echo $r['total_entradas']; ?></span></td>
            <td data-label="Salidas"><span style="color:var(--salida-texto, #e08d7d);">−<?php echo $r['total_salidas']; ?></span></td>
          </tr>
        <?php endforeach; ?>
        <?php if (count($resumen) === 0): ?>
          <tr><td colspan="3" class="vacio">Todavía no hay movimientos registrados.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <h2 style="font-size:14px; margin-bottom:10px;">Detalle (últimos 100 movimientos)</h2>
    <table class="tabla">
      <thead>
        <tr>
          <th>Fecha y hora</th>
          <th>Código</th>
          <th>Descripción</th>
          <th>Talla</th>
          <th>Ubicación</th>
          <th>Movimiento</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($detalle as $m): ?>
          <tr>
            <td data-label="Fecha"><?php echo date('d/m/Y H:i', strtotime($m['fecha'])); ?></td>
            <td data-label="Código"><?php echo htmlspecialchars($m['codigo']); ?></td>
            <td data-label="Descripción"><?php echo htmlspecialchars($m['descripcion']); ?></td>
            <td data-label="Talla"><?php echo htmlspecialchars($m['talla']); ?></td>
            <td data-label="Ubicación"><?php echo $m['ubicacion'] === 'estanteria' ? 'Estantería' : 'Bodega'; ?></td>
            <td data-label="Movimiento">
              <?php if ($m['tipo'] === 'entrada'): ?>
                <span style="color:var(--entrada-texto); font-family:var(--mono);">+<?php echo $m['cantidad']; ?></span>
              <?php else: ?>
                <span style="color:var(--salida-texto, #e08d7d); font-family:var(--mono);">−<?php echo $m['cantidad']; ?></span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (count($detalle) === 0): ?>
          <tr><td colspan="6" class="vacio">Todavía no hay movimientos registrados.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

  </main>
</body>
</html>
