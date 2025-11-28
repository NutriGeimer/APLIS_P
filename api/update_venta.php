<?php
require_once "headers.php";
require_once "db.php";
require_once "auth.php";

session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["error" => "Acceso no autorizado"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

$venta_id = $data["id"];
$nueva_cantidad = $data["cantidad"];

// Obtener datos actuales de la venta
$sql = "SELECT product_id, cantidad FROM ventas WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $venta_id);
$stmt->execute();
$result = $stmt->get_result();
$venta = $result->fetch_assoc();

if (!$venta) {
    echo json_encode(["error" => "Venta no encontrada"]);
    exit();
}

$product_id = $venta["product_id"];
$cantidad_anterior = $venta["cantidad"];
$diferencia = $nueva_cantidad - $cantidad_anterior;

// Obtener stock actual
$sql = "SELECT stock, precio FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$producto = $result->fetch_assoc();

if (!$producto) {
    echo json_encode(["error" => "Producto no encontrado"]);
    exit();
}

$stock_actual = $producto["stock"];

// Si la diferencia es positiva, necesitamos más stock
if ($diferencia > 0 && $stock_actual < $diferencia) {
    echo json_encode(["error" => "Stock insuficiente para actualizar la venta"]);
    exit();
}

// Actualizar venta
$nuevo_total = $nueva_cantidad * $producto["precio"];

$sql = "UPDATE ventas SET cantidad = ?, total = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("idi", $nueva_cantidad, $nuevo_total, $venta_id);
$stmt->execute();

// Ajustar stock según la diferencia
$sql = "UPDATE products SET stock = stock - ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $diferencia, $product_id);
$stmt->execute();

echo json_encode(["message" => "Venta actualizada correctamente"]);

$conn->close();
?>
