<?php
// config.php
// Este archivo hace UNA sola cosa: abrir la conexión a la base de datos.
// Lo vamos a "incluir" (require) en cada página que necesite hablar con MySQL,
// así no repetimos este código por todos lados.

$host    = 'localhost';
$db      = 'inventario_boxers';
$usuario = 'root';   // usuario por defecto de XAMPP
$clave   = '';       // XAMPP por defecto no le pone contraseña a root
$charset = 'utf8mb4'; // para que tildes y ñ se guarden y lean bien

// DSN = Data Source Name: le dice a PHP CÓMO y A QUÉ conectarse
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$opciones = [
    // Si algo falla, que lance un error claro en vez de fallar en silencio
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    // Que los resultados vengan como array asociativo: $fila['codigo']
    // en vez de $fila[0], $fila[1]... (mucho más legible)
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $usuario, $clave, $opciones);
} catch (PDOException $e) {
    // Si la conexión falla (ej: MySQL apagado, o mal el nombre de la BD),
    // detenemos todo y mostramos por qué.
    die('No se pudo conectar a la base de datos: ' . $e->getMessage());
}

// A partir de aquí, cualquier archivo que haga "require 'config.php';"
// ya tiene disponible la variable $pdo lista para usar.
