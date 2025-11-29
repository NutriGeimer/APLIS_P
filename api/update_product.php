<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/_headers.php';

$user = current_user_or_401();

if (($user['rol'] ?? null) !== 'admin') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Acceso denegado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
    exit;
}

$id = $_POST['id'] ?? null;
if (!$id) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'ID requerido']);
    exit;
}

$nombre = $_POST['nombre'] ?? null;
$precio = $_POST['precio'] ?? null;
$descripcion = $_POST['descripcion'] ?? '';

if (!$nombre || !$precio) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Nombre y precio son obligatorios']);
    exit;
}

global $pdo;

$stmtOldStock = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
$stmtOldStock->execute([$id]);
$stockActual = $stmtOldStock->fetchColumn();

$imagenNombre = null;

if (!empty($_FILES['imagen']['name'])) {

    $dir = __DIR__ . '/../uploads/';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
    $imagenNombre = 'img_' . uniqid() . '.' . $extension;

    $ruta = $dir . $imagenNombre;

    if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta)) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'No se pudo subir la imagen']);
        exit;
    }
}

if (!$imagenNombre) {
    $stmtOldImg = $pdo->prepare("SELECT imagen FROM products WHERE id = ?");
    $stmtOldImg->execute([$id]);
    $imagenNombre = $stmtOldImg->fetchColumn();
}

try {
    $stmt = $pdo->prepare("
        UPDATE products SET 
            nombre = ?, 
            precio = ?, 
            descripcion = ?, 
            stock = ?,
            imagen = ?
        WHERE id = ?
    ");

    $stmt->execute([
        $nombre,
        $precio,
        $descripcion,
        $stockActual,   
        $imagenNombre,
        $id
    ]);

    echo json_encode([
        'ok' => true,
        'message' => 'Producto actualizado correctamente'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => 'Error interno',
        'error' => $e->getMessage()
    ]);
}
