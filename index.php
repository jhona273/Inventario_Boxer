<?php
// index.php
// Página principal: muestra el inventario y deja disparar entradas/salidas.

require 'config.php';

$buscar = $_GET['buscar'] ?? '';

if ($buscar !== '') {
    // Consulta PREPARADA: los "?" son un espacio reservado, y execute()
    // le pasa los valores reales por separado. Así, aunque alguien escriba
    // algo raro en el buscador, PHP nunca lo va a tratar como código SQL.
    $termino = '%' . $buscar . '%';
    $stmt = $pdo->prepare(
        'SELECT * FROM productos
         WHERE codigo LIKE ? OR descripcion LIKE ? OR talla LIKE ?
         ORDER BY codigo, talla'
    );
    $stmt->execute([$termino, $termino, $termino]);
} else {
    $stmt = $pdo->query('SELECT * FROM productos ORDER BY codigo, talla');
}

$productos = $stmt->fetchAll();

// Un par de números para el resumen de arriba
$totalUnidades = array_sum(array_column($productos, 'cantidad_estanteria'))
                + array_sum(array_column($productos, 'cantidad_bodega'));
$stockBajo = count(array_filter($productos, function ($p) {
    return ($p['cantidad_estanteria'] + $p['cantidad_bodega']) <= $p['cantidad_minima'];
}));
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inventario · Boxer Matheus</title>
  <link rel="stylesheet" href="estilo.css">
</head>
<body>

  <header class="topbar">
    <h1>Inventario Boxer Matheus</h1>
    <div class="topbar__stats">
      <div class="stat"><span class="stat__num"><?php echo $totalUnidades; ?></span><span class="stat__label">Unidades totales</span></div>
      <div class="stat"><span class="stat__num"><?php echo count($productos); ?></span><span class="stat__label">Referencias/tallas</span></div>
      <div class="stat stat--warn"><span class="stat__num"><?php echo $stockBajo; ?></span><span class="stat__label">Stock bajo</span></div>
    </div>
  </header>

  <main class="contenido">

    <?php if (isset($_GET['error'])): ?>
      <p class="mensaje mensaje--error"><?php echo htmlspecialchars($_GET['error']); ?></p>
    <?php endif; ?>

    <form method="get" class="buscador">
      <input type="search" name="buscar" placeholder="Buscar por código, descripción o talla..."
             value="<?php echo htmlspecialchars($buscar); ?>">
      <button type="submit" class="btn btn--secundario">Buscar</button>
      <?php if ($buscar !== ''): ?>
        <a href="index.php" class="btn btn--secundario">Limpiar</a>
      <?php endif; ?>
      <a href="agregar.php" class="btn btn--primario" style="margin-left:auto; text-decoration:none;">+ Nueva referencia</a>
    </form>

    <table class="tabla">
      <thead>
        <tr>
          <th>Código</th>
          <th>Descripción</th>
          <th>Talla</th>
          <th>Estantería</th>
          <th>Bodega</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($productos as $p): ?>
          <?php $bajo = ($p['cantidad_estanteria'] + $p['cantidad_bodega']) <= $p['cantidad_minima']; ?>
          <tr class="<?php echo $bajo ? 'fila--bajo' : ''; ?>">
            <td data-label="Código"><?php echo htmlspecialchars($p['codigo']); ?></td>
            <td data-label="Descripción"><?php echo htmlspecialchars($p['descripcion']); ?></td>
            <td data-label="Talla"><?php echo htmlspecialchars($p['talla']); ?></td>

            <td data-label="Estantería">
              <span class="cantidad-num"><?php echo $p['cantidad_estanteria']; ?></span>
              <form method="post" action="movimiento.php" class="form-mov">
                <input type="hidden" name="producto_id" value="<?php echo $p['id']; ?>">
                <input type="hidden" name="ubicacion" value="estanteria">
                <input type="number" name="cantidad" min="1" placeholder="Cant." required>
                <button type="submit" name="tipo" value="entrada" class="btn btn--fila">+</button>
                <button type="submit" name="tipo" value="salida" class="btn btn--fila">−</button>
              </form>
            </td>

            <td data-label="Bodega">
              <span class="cantidad-num"><?php echo $p['cantidad_bodega']; ?></span>
              <form method="post" action="movimiento.php" class="form-mov">
                <input type="hidden" name="producto_id" value="<?php echo $p['id']; ?>">
                <input type="hidden" name="ubicacion" value="bodega">
                <input type="number" name="cantidad" min="1" placeholder="Cant." required>
                <button type="submit" name="tipo" value="entrada" class="btn btn--fila">+</button>
                <button type="submit" name="tipo" value="salida" class="btn btn--fila">−</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>

        <?php if (count($productos) === 0): ?>
          <tr><td colspan="5" class="vacio">No se encontraron productos.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

  </main>
</body>
</html>