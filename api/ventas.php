<?php
//Enlista las ventas 
require_once "headers.php";
require_once "db.php";
require_once "auth.php";
session_start();

// Admin y cliente pueden ver ventas
if (!isset($_SESSION['user_role'])) {
    http_response_code(403);
    echo json_encode(["error" => "Acceso no autorizado"]);
    exit();
}

$sql = "
    SELECT v.id, v.cantidad, v.total, v.fecha,
           p.nombre AS producto, p.precio
    FROM ventas v
    INNER JOIN products p ON v.product_id = p.id
    ORDER BY v.fecha DESC
";

$result = $conn->query($sql);

$ventas = [];

while ($row = $result->fetch_assoc()) {
    $ventas[] = $row;
}

echo json_encode($ventas);

$conn->close();
?>
