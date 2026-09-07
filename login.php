<?php
// login.php
// session_start() es OBLIGATORIO al inicio de cualquier página que use
// sesiones (tanto para iniciar sesión como para leerla después).
// Debe ir ANTES de que se imprima cualquier HTML.
session_start();

require 'config.php';

// Si ya inició sesión, no tiene sentido que vea el login de nuevo
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
    $clave = $_POST['clave'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE nombre_usuario = ?');
    $stmt->execute([$nombre_usuario]);
    $usuario = $stmt->fetch();

    // password_verify() compara la clave escrita contra el hash guardado.
    // Nunca comparamos claves "a mano" con == ; siempre con esta función.
    if ($usuario && password_verify($clave, $usuario['clave_hash'])) {
        // ¡Login correcto! Guardamos datos en la sesión: a partir de
        // aquí, en CUALQUIER otra página que haga session_start(),
        // podemos leer $_SESSION['usuario_id'] para saber quién es.
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];

        header('Location: index.php');
        exit;
    } else {
        $error = 'Usuario o clave incorrectos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar sesión · Inventario</title>
  <link rel="stylesheet" href="estilo.css">
</head>
<body>
  <main class="contenido" style="max-width:380px; margin-top:80px;">
    <h1 style="font-size:19px; margin-bottom:20px;">Inventario Boxer Matheus</h1>

    <?php if ($error): ?>
      <p class="mensaje mensaje--error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="post" class="panel-formulario">
      <label>
        Usuario
        <input type="text" name="nombre_usuario" required autofocus>
      </label>
      <label>
        Clave
        <input type="password" name="clave" required>
      </label>
      <button type="submit" class="btn btn--primario">Entrar</button>
    </form>
  </main>
</body>
</html>
