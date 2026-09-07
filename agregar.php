<?php
// agregar.php
// Formulario para crear una referencia/talla nueva sin tener que
// entrar a phpMyAdmin cada vez.

require 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo      = trim($_POST['codigo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $talla       = trim($_POST['talla'] ?? '');
    $minima      = (int)($_POST['cantidad_minima'] ?? 5);

    if ($codigo === '' || $descripcion === '' || $talla === '') {
        $error = 'Código, descripción y talla son obligatorios';
    } else {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO productos (codigo, descripcion, talla, cantidad_estanteria, cantidad_bodega, cantidad_minima)
                 VALUES (?, ?, ?, 0, 0, ?)'
            );
            $stmt->execute([$codigo, $descripcion, $talla, $minima]);

            // Ya quedó guardado: volvemos al inventario a verlo
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            // Código 23000 = choque con el UNIQUE (codigo + talla) que ya existe
            if ($e->getCode() === '23000') {
                $error = "Ya existe la referencia {$codigo} en talla {$talla}";
            } else {
                $error = 'No se pudo guardar: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agregar referencia · Inventario</title>
  <link rel="stylesheet" href="estilo.css">
</head>
<body>

  <header class="topbar">
    <h1>Agregar referencia nueva</h1>
  </header>

  <main class="contenido">
    <a href="index.php" class="btn btn--secundario" style="margin-bottom:16px; display:inline-block;">← Volver al inventario</a>

    <?php if ($error): ?>
      <p class="mensaje mensaje--error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="post" class="panel-formulario">
      <label>
        Código
        <input type="text" name="codigo" placeholder="Ej: 277-7" required
               value="<?php echo htmlspecialchars($_POST['codigo'] ?? ''); ?>">
      </label>

      <label>
        Descripción
        <input type="text" name="descripcion" placeholder="Ej: UNIDAD DE BOXER MICROFIBRA NIÑO" required
               value="<?php echo htmlspecialchars($_POST['descripcion'] ?? ''); ?>">
      </label>

      <label>
        Talla
        <input type="text" name="talla" placeholder="Ej: M, 2XL, 14-16..." required
               value="<?php echo htmlspecialchars($_POST['talla'] ?? ''); ?>">
      </label>

      <label>
        Alertar si el total (estantería + bodega) baja de
        <input type="number" name="cantidad_minima" min="0" value="5">
      </label>

      <button type="submit" class="btn btn--primario">Guardar referencia</button>
    </form>
  </main>
</body>
</html>