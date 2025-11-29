<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/_headers.php';

$user = current_user_or_401();

if ($user['rol'] !== 'admin') {
    echo json_encode(["ok" => false, "message" => "Acceso denegado"]);
    exit;
}

global $pdo;

$stmt = $pdo->query("
    SELECT v.id, v.total, v.fecha, u.nombre AS cliente,
           vi.product_id, vi.cantidad, vi.precio_unitario, p.nombre, p.imagen
    FROM ventas v
    INNER JOIN users u ON u.id = v.user_id
    INNER JOIN ventas_items vi ON vi.venta_id = v.id
    INNER JOIN products p ON p.id = vi.product_id
    ORDER BY v.fecha DESC
");

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["ok" => true, "items" => $data]);