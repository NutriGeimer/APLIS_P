<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/_headers.php';

$user = current_user_or_401();
$uid = $user['id'];

try {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT c.id AS cart_id, c.cantidad, p.id AS product_id, p.precio, p.stock
        FROM cart c
        INNER JOIN products p ON p.id = c.product_id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$uid]);
    $cart = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$cart) {
        echo json_encode(["ok" => false, "message" => "Tu carrito está vacío"]);
        exit;
    }

    foreach ($cart as $item) {
        if ($item['cantidad'] > $item['stock']) {
            echo json_encode([
                "ok" => false,
                "message" => "No hay stock suficiente para el producto con ID " . $item['product_id']
            ]);
            exit;
        }
    }

    $total = 0;
    foreach ($cart as $item) {
        $total += $item['precio'] * $item['cantidad'];
    }

    $stmt = $pdo->prepare("INSERT INTO ventas (user_id, total) VALUES (?, ?)");
    $stmt->execute([$uid, $total]);
    $venta_id = $pdo->lastInsertId();

    foreach ($cart as $item) {
        $stmt = $pdo->prepare("
            INSERT INTO ventas_items (venta_id, product_id, cantidad, precio_unitario)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $venta_id,
            $item['product_id'],
            $item['cantidad'],
            $item['precio']
        ]);

        $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$item['cantidad'], $item['product_id']]);
    }

    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$uid]);

    echo json_encode(["ok" => true, "message" => "Compra realizada con éxito"]);
    
} catch (Exception $e) {
    echo json_encode(["ok" => false, "message" => "Error en la venta"]);
}