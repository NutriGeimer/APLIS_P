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
	echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
	exit;
}

// Ensure uploads dir exists
$uploadsDir = __DIR__ . '/../uploads';
if (!is_dir($uploadsDir)) {
	mkdir($uploadsDir, 0755, true);
}

$nombre = trim($_POST['nombre'] ?? '');
$precio = $_POST['precio'] ?? null;
$descripcion = trim($_POST['descripcion'] ?? '');
$stock = isset($_POST['stock']) ? (int) $_POST['stock'] : 0;

if ($nombre === '' || $precio === null || !is_numeric($precio)) {
	http_response_code(400);
	echo json_encode(['ok' => false, 'message' => 'Datos inválidos (nombre o precio)']);
	exit;
}

$imagenFilename = null;
if (!empty($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
	$file = $_FILES['imagen'];
	$allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
	$finfo = finfo_open(FILEINFO_MIME_TYPE);
	$mime = finfo_file($finfo, $file['tmp_name']);
	finfo_close($finfo);

	if (!array_key_exists($mime, $allowed)) {
		http_response_code(400);
		echo json_encode(['ok' => false, 'message' => 'Tipo de imagen no permitido']);
		exit;
	}

	$ext = $allowed[$mime];
	$imagenFilename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
	$dest = $uploadsDir . '/' . $imagenFilename;

	if (!move_uploaded_file($file['tmp_name'], $dest)) {
		http_response_code(500);
		echo json_encode(['ok' => false, 'message' => 'Error al guardar la imagen']);
		exit;
	}
}

try {
	global $pdo;
	$stmt = $pdo->prepare('INSERT INTO products (nombre, precio, descripcion, stock, imagen) VALUES (?, ?, ?, ?, ?)');
	$stmt->execute([$nombre, $precio, $descripcion, $stock, $imagenFilename]);
	$id = $pdo->lastInsertId();

	$resp = ['ok' => true, 'product_id' => (int)$id];
	echo json_encode($resp);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(['ok' => false, 'message' => 'Error interno', 'error' => $e->getMessage()]);
}

