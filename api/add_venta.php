<?php
//Registrar venta y restar stock
require_once "headers.php";
require_once "db.php";
require_once "auth.php";
session_start();

// Solo admin puede registrar ventas
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["error" => "Acceso no autorizado"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

$product_id = $data['product_id'];
$cantidad = $data['cantidad'];

// Obtener producto
$sql = "SELECT stock, precio FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    echo json_encode(["error" => "Producto no encontrado"]);
    exit();
}

// Verificar stock suficiente
if ($product['stock'] < $cantidad) {
    echo json_encode(["error" => "Stock insuficiente"]);
    exit();
}

$total = $product['precio'] * $cantidad;

// Registrar venta
$sql = "INSERT INTO ventas (product_id, cantidad, total) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iid", $product_id, $cantidad, $total);

if ($stmt->execute()) {
    // Restar stock
    $sql_update = "UPDATE products SET stock = stock - ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ii", $cantidad, $product_id);
    $stmt_update->execute();

    echo json_encode(["message" => "Venta registrada con éxito"]);
} else {
    echo json_encode(["error" => "Error al registrar venta"]);
}

$conn->close();
?>
