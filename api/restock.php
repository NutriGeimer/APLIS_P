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

$data = json_decode(file_get_contents("php://input"), true);

$product_id = $data['product_id'] ?? null;
$cantidad = $data['cantidad'] ?? null;
$costo = $data['costo'] ?? null;

if (!$product_id || !$cantidad || !$costo) {
    echo json_encode(["ok" => false, "message" => "Datos incompletos"]);
    exit;
}

if ($cantidad <= 0 || $costo <= 0) {
    echo json_encode(["ok" => false, "message" => "Valores inválidos"]);
    exit;
}

$subtotal = $cantidad * $costo;

try {
    global $pdo;

    // aumentar stock
    $stmt = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
    $stmt->execute([$cantidad, $product_id]);

    // registrar en restocks
    $stmt = $pdo->prepare("
        INSERT INTO restocks (product_id, cantidad, costo_unitario, subtotal)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$product_id, $cantidad, $costo, $subtotal]);

    echo json_encode(["ok" => true, "message" => "Restock registrado correctamente"]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "ok" => false,
        "message" => "Error al registrar restock",
        "error" => $e->getMessage()
    ]);
}