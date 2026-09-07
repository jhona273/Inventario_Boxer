<?php
// logout.php
session_start();
session_destroy(); // borra toda la información de la sesión
header('Location: login.php');
exit;
