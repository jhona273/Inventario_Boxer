<?php
// movimiento.php
// Este archivo nunca lo ve el usuario directamente: solo recibe el POST
// del formulario en index.php, hace el cálculo, y redirige de vuelta.

require 'auth.php';
require 'config.php';

$producto_id = $_POST['producto_id'] ?? null;
$ubicacion   = $_POST['ubicacion'] ?? null;
$tipo        = $_POST['tipo'] ?? null;
$cantidad    = $_POST['cantidad'] ?? null;

// --- Validaciones básicas ---
if (!$producto_id || !$ubicacion || !$tipo || !$cantidad || (int)$cantidad <= 0) {
    header('Location: index.php?error=' . urlencode('Datos incompletos o cantidad inválida'));
    exit;
}
if ($tipo !== 'entrada' && $tipo !== 'salida') {
    header('Location: index.php?error=' . urlencode('Tipo de movimiento inválido'));
    exit;
}
if ($ubicacion !== 'estanteria' && $ubicacion !== 'bodega') {
    header('Location: index.php?error=' . urlencode('Ubicación inválida'));
    exit;
}

$cantidad = (int)$cantidad;
// Según la ubicación, trabajamos sobre una columna distinta de la tabla
$columna = $ubicacion === 'estanteria' ? 'cantidad_estanteria' : 'cantidad_bodega';

// Buscamos el producto (consulta preparada de nuevo)
$stmt = $pdo->prepare('SELECT * FROM productos WHERE id = ?');
$stmt->execute([$producto_id]);
$producto = $stmt->fetch();

if (!$producto) {
    header('Location: index.php?error=' . urlencode('Producto no encontrado'));
    exit;
}

$cantidadActual = $producto[$columna];

// No dejamos que una salida deje el stock en negativo
if ($tipo === 'salida' && $cantidadActual < $cantidad) {
    $nombreUbicacion = $ubicacion === 'estanteria' ? 'estantería' : 'bodega';
    header('Location: index.php?error=' . urlencode(
        "Stock insuficiente en {$nombreUbicacion} para {$producto['codigo']} talla {$producto['talla']}. Solo hay {$cantidadActual} unidades."
    ));
    exit;
}

$nuevaCantidad = $tipo === 'entrada' ? $cantidadActual + $cantidad : $cantidadActual - $cantidad;

// --- Transacción ---
// O se guardan las DOS cosas (nuevo stock + registro en movimientos),
// o no se guarda ninguna. Así nunca queda la info a medias si algo falla
// a la mitad (ej: se corta la luz, se cae la conexión, etc).
try {
    $pdo->beginTransaction();

    // OJO: aquí "$columna" no viene del usuario directamente, sino que
    // nosotros la calculamos arriba a partir de un valor ya validado
    // (solo puede ser 'cantidad_estanteria' o 'cantidad_bodega').
    // Nunca metas una variable del usuario directo en el nombre de una
    // columna o tabla, aunque uses consultas preparadas para los VALORES.
    $pdo->prepare("UPDATE productos SET {$columna} = ? WHERE id = ?")
        ->execute([$nuevaCantidad, $producto_id]);

    $pdo->prepare('INSERT INTO movimientos (producto_id, ubicacion, tipo, cantidad) VALUES (?, ?, ?, ?)')
        ->execute([$producto_id, $ubicacion, $tipo, $cantidad]);

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    header('Location: index.php?error=' . urlencode('Error guardando el movimiento'));
    exit;
}

// Todo salió bien: volvemos al inventario para ver el cambio reflejado
header('Location: index.php');
exit;
