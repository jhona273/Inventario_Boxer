<?php
// auth.php
// Este archivo NO se abre directamente. Se "incluye" al principio de
// cada página que quieras proteger. Su único trabajo: si no hay sesión
// activa, patea a la persona hacia login.php antes de que vea nada más.

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
