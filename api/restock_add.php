<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/_headers.php';

$user = current_user_or_401();

if ($user['rol'] !== 'admin') {
    echo json_encode(["ok" => false, "message" => "Acceso denegado"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["ok" => false, "message" => "Método no permitido"]);
    exit;
}

global $pdo;

$data = json_decode(file_get_contents("php://input"), true);
$product_id = $data["product_id"];
$cantidad = $data["cantidad"];
$costo = $data["costo"];

try {
    $pdo->beginTransaction();

    // Insert restock
    $stmt = $pdo->prepare("
        INSERT INTO restocks (product_id, cantidad, costo_unitario, fecha)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$product_id, $cantidad, $costo]);

    // Update stock
    $stmt2 = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
    $stmt2->execute([$cantidad, $product_id]);

    $pdo->commit();

    echo json_encode(["ok" => true, "message" => "Restock registrado correctamente"]);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["ok" => false, "message" => "Error al registrar restock", "error" => $e->getMessage()]);
}