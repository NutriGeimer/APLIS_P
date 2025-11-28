<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/_headers.php';

$user = current_user_or_401();
$uid = $user['id'];

try {
    global $pdo;

    $stmt = $pdo->prepare("SELECT SUM(cantidad) as total FROM cart WHERE user_id=?");
    $stmt->execute([$uid]);
    $row = $stmt->fetch();

    echo json_encode([
        'ok' => true,
        'count' => intval($row['total'] ?? 0)
    ]);

} catch (Exception $e) {
    echo json_encode(['ok' => false, 'count' => 0]);
}
