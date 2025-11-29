<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/_headers.php';

$user = current_user_or_401();
$uid = $user['id'];

global $pdo;

$stmt = $pdo->prepare("
    SELECT v.id, v.total, v.fecha,
        vi.product_id, vi.cantidad, vi.precio_unitario, p.nombre, p.imagen
    FROM ventas v
    INNER JOIN ventas_items vi ON vi.venta_id = v.id
    INNER JOIN products p ON p.id = vi.product_id
    WHERE v.user_id = ?
    ORDER BY v.fecha DESC
");

$stmt->execute([$uid]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "ok" => true,
    "items" => $rows
]);