<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/_headers.php';

$user = current_user_or_401(); // cliente o admin

// Validación del método
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM products ORDER BY id DESC");
    $stmt->execute();
    $productos = $stmt->fetchAll();

    echo json_encode([
        'ok' => true,
        'productos' => $productos
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => 'Error al obtener productos',
        'error' => $e->getMessage()
    ]);
}
