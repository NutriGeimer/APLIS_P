<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/_headers.php';

$user = current_user_or_401();
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || !isset($data['accion'])) {
    echo json_encode(['ok' => false, 'message' => 'Datos incompletos']);
    exit;
}

$id = intval($data['id']);
$accion = $data['accion'];

try {
    global $pdo;

    if ($accion === '+') {
        $stmt = $pdo->prepare("UPDATE cart SET cantidad = cantidad + 1 WHERE id=?");
        $stmt->execute([$id]);
    } elseif ($accion === '-') {
        $stmt = $pdo->prepare("UPDATE cart SET cantidad = GREATEST(cantidad - 1, 1) WHERE id=?");
        $stmt->execute([$id]);
    }

    echo json_encode(['ok' => true]);

} catch (Exception $e) {
    echo json_encode(['ok' => false]);
}
