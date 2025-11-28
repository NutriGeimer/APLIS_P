<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/_headers.php';

$user = current_user_or_401();
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    echo json_encode(['ok' => false]);
    exit;
}

$id = intval($data['id']);

try {
    global $pdo;

    $stmt = $pdo->prepare("DELETE FROM cart WHERE id=?");
    $stmt->execute([$id]);

    echo json_encode(['ok' => true]);

} catch (Exception $e) {
    echo json_encode(['ok' => false]);
}
