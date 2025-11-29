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
    SELECT r.id, r.cantidad, r.costo_unitario, r.subtotal, r.fecha,
           p.nombre AS producto, p.imagen
    FROM restocks r
    INNER JOIN products p ON p.id = r.product_id
    ORDER BY r.fecha DESC
");

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "ok" => true,
    "items" => $data
]);