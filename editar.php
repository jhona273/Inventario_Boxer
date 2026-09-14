<?php
// editar.php
// Permite corregir código, descripción, talla o la cantidad mínima
// de una referencia que ya existe. NO toca cantidad_estanteria ni
// cantidad_bodega directamente — esas siempre se mueven por movimiento.php,
// para que el historial de movimientos siga siendo confiable.

require 'auth.php';
require 'config.php';

$id = $_GET['id'] ?? $_POST['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM productos WHERE id = ?');
$stmt->execute([$id]);
$producto = $stmt->fetch();

if (!$producto) {
    header('Location: index.php?error=' . urlencode('Producto no encontrado'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = trim($_POST['codigo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $talla = trim($_POST['talla'] ?? '');
    $minima = (int)($_POST['cantidad_minima'] ?? 5);

    if ($codigo === '' || $descripcion === '' || $talla === '') {
        $error = 'Código, descripción y talla son obligatorios';
    } else {
        try {
            $stmt = $pdo->prepare(
                'UPDATE productos SET codigo = ?, descripcion = ?, talla = ?, cantidad_minima = ? WHERE id = ?'
            );
            $stmt->execute([$codigo, $descripcion, $talla, $minima, $id]);

            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $error = "Ya existe otra referencia con código {$codigo} y talla {$talla}";
            } else {
                $error = 'Error al guardar: ' . $e->getMessage();
            }
            // Para que el formulario no pierda lo que ya habías escrito
            $producto['codigo'] = $codigo;
            $producto['descripcion'] = $descripcion;
            $producto['talla'] = $talla;
            $producto['cantidad_minima'] = $minima;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar referencia · Inventario</title>
  <link rel="stylesheet" href="estilo.css?v=2">
</head>
<body>

  <header class="topbar">
    <h1>Editar referencia</h1>
  </header>

  <main class="contenido">
    <a href="index.php" class="btn btn--secundario" style="margin-bottom:16px; display:inline-block; text-decoration:none;">← Volver al inventario</a>

    <?php if ($error): ?>
      <p class="mensaje mensaje--error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <p style="font-size:12.5px; color:var(--texto-tenue); max-width:420px;">
      Aquí solo corriges datos de la referencia (código, descripción, talla, mínimo).
      Las cantidades en estantería y bodega se siguen moviendo desde el inventario
      con los botones de entrada/salida, para que el historial cuadre.
    </p>

    <form method="post" class="panel-formulario">
      <input type="hidden" name="id" value="<?php echo (int)$producto['id']; ?>">

      <label>
        Código
        <input type="text" name="codigo" required
               value="<?php echo htmlspecialchars($producto['codigo']); ?>">
      </label>

      <label>
        Descripción
        <input type="text" name="descripcion" required
               value="<?php echo htmlspecialchars($producto['descripcion']); ?>">
      </label>

      <label>
        Talla
        <input type="text" name="talla" required
               value="<?php echo htmlspecialchars($producto['talla']); ?>">
      </label>

      <label>
        Alertar si el total baja de
        <input type="number" name="cantidad_minima" min="0"
               value="<?php echo (int)$producto['cantidad_minima']; ?>">
      </label>

      <div style="display:flex; gap:10px;">
        <button type="submit" class="btn btn--primario">Guardar cambios</button>
        <a href="index.php" class="btn btn--secundario" style="text-decoration:none; display:inline-flex; align-items:center;">Cancelar</a>
      </div>
    </form>
  </main>
</body>
</html>
