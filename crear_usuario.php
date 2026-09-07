<?php
// crear_usuario.php
// Página TEMPORAL, solo para crear usuarios. Al final te digo por qué
// conviene borrarla (o protegerla) después de usarla.

require 'config.php';

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
    $clave = $_POST['clave'] ?? '';

    if ($nombre_usuario === '' || $clave === '') {
        $error = 'Usuario y clave son obligatorios';
    } elseif (strlen($clave) < 6) {
        $error = 'La clave debe tener al menos 6 caracteres';
    } else {
        // password_hash() convierte la clave en un "hash": una cadena
        // larga y sin sentido que representa la clave, pero que NO se
        // puede "deshacer" para recuperar la clave original.
        // PASSWORD_DEFAULT usa el algoritmo más seguro recomendado hoy en día.
        $hash = password_hash($clave, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare('INSERT INTO usuarios (nombre_usuario, clave_hash) VALUES (?, ?)');
            $stmt->execute([$nombre_usuario, $hash]);
            $mensaje = "Usuario '{$nombre_usuario}' creado correctamente. Ya puedes iniciar sesión.";
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $error = 'Ese nombre de usuario ya existe';
            } else {
                $error = 'Error al crear el usuario: ' . $e->getMessage();
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
  <title>Crear usuario</title>
  <link rel="stylesheet" href="estilo.css">
</head>
<body>
  <main class="contenido" style="max-width:420px; margin-top:60px;">
    <h1 style="font-size:18px;">Crear usuario</h1>

    <?php if ($mensaje): ?>
      <p class="mensaje" style="background:rgba(143,201,160,0.15); color:#8fc9a0; border:1px solid #8fc9a0; padding:10px 14px; border-radius:6px;">
        <?php echo htmlspecialchars($mensaje); ?>
      </p>
    <?php endif; ?>
    <?php if ($error): ?>
      <p class="mensaje mensaje--error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="post" class="panel-formulario">
      <label>
        Nombre de usuario
        <input type="text" name="nombre_usuario" required autofocus>
      </label>
      <label>
        Clave (mínimo 6 caracteres)
        <input type="password" name="clave" required>
      </label>
      <button type="submit" class="btn btn--primario">Crear usuario</button>
    </form>

    <p style="font-size:12px; color:var(--texto-tenue); margin-top:16px;">
      Recuerda borrar o proteger este archivo cuando termines de crear los usuarios que necesites.
    </p>
  </main>
</body>
</html>
