<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/_headers.php';

$user = current_user_or_401();
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['product_id'])) {
    echo json_encode(['ok' => false, 'message' => 'ID de producto requerido']);
    exit;
}

$uid = $user['id'];
$product_id = intval($data['product_id']);

try {
    global $pdo;

    // Verificar si ya existe en el carrito
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id=? AND product_id=?");
    $stmt->execute([$uid, $product_id]);
    $item = $stmt->fetch();

    if ($item) {
        $update = $pdo->prepare("UPDATE cart SET cantidad = cantidad + 1 WHERE id=?");
        $update->execute([$item['id']]);
    } else {
        $insert = $pdo->prepare("INSERT INTO cart(user_id, product_id, cantidad) VALUES (?, ?, 1)");
        $insert->execute([$uid, $product_id]);
    }

    echo json_encode(['ok' => true, 'message' => 'Producto agregado al carrito']);

} catch (Exception $e) {
    echo json_encode(['ok' => false, 'message' => 'Error al agregar']);
}
