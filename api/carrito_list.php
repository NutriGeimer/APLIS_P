<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/_headers.php';

$user = current_user_or_401();
$uid = $user['id'];

try {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT 
            c.id AS cart_id,
            c.cantidad,
            p.id AS product_id,
            p.nombre,
            p.descripcion,
            p.precio,
            p.imagen
        FROM cart c
        INNER JOIN products p ON p.id = c.product_id
        WHERE c.user_id = ?
        ORDER BY c.id DESC
    ");
    $stmt->execute([$uid]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convertir precio a número
    foreach ($items as &$i) {
        $i['precio'] = floatval($i['precio']);
        $i['cantidad'] = intval($i['cantidad']);
    }

    echo json_encode([
        "ok" => true,
        "items" => $items
    ]);

} catch (Exception $e) {
    echo json_encode([
        "ok" => false,
        "items" => []
    ]);
}
