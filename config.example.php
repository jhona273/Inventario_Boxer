<?php
// config.example.php
// Esta es una PLANTILLA que sí se sube a git. Cualquiera que clone el
// proyecto (o tú mismo en otro computador) sabe qué variables necesita
// llenar, sin exponer tu contraseña real.
//
// Cómo usarla: copia este archivo, renómbralo a "config.php", y pon
// ahí tus datos reales. "config.php" nunca se sube a git (ver .gitignore).

$host    = 'localhost';
$db      = 'inventario_boxers';
$usuario = 'root';
$clave   = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$opciones = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $usuario, $clave, $opciones);
} catch (PDOException $e) {
    die('No se pudo conectar a la base de datos: ' . $e->getMessage());
}
