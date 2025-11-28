<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/_headers.php';

$user = current_user_or_401();

// Validar que sea admin
if (($user['rol'] ?? null) !== 'admin') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Acceso denegado']);
    exit;
}

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Método no permitido']);
    exit;
}

// Verificar campos obligatorios
$nombre = $_POST['nombre'] ?? null;
$precio = $_POST['precio'] ?? null;
$descripcion = $_POST['descripcion'] ?? '';
$stock = $_POST['stock'] ?? 0;

if (!$nombre || !$precio) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Nombre y precio son obligatorios']);
    exit;
}

// Manejo de imagen
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

try {
    global $pdo;

    $stmt = $pdo->prepare("
        INSERT INTO products (nombre, precio, descripcion, stock, imagen)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $nombre,
        $precio,
        $descripcion,
        $stock,
        $imagenNombre
    ]);

    echo json_encode([
        'ok' => true,
        'message' => 'Producto agregado correctamente'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => 'Error interno',
        'error' => $e->getMessage()
    ]);
}
