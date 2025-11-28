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

// Accept both JSON and form-data (with optional file)
$data = null;
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($contentType, 'application/json') !== false) {
	$raw = file_get_contents('php://input');
	$data = json_decode($raw, true) ?: [];
} else {
	$data = $_POST;
}

$id = isset($data['id']) ? (int)$data['id'] : null;
if (!$id) {
	http_response_code(400);
	echo json_encode(['ok' => false, 'message' => 'ID de producto requerido']);
	exit;
}

// Ensure uploads dir exists
$uploadsDir = __DIR__ . '/../uploads';
if (!is_dir($uploadsDir)) {
	mkdir($uploadsDir, 0755, true);
}

$fields = [];
$params = [];

if (isset($data['nombre'])) { $fields[] = 'nombre = ?'; $params[] = trim($data['nombre']); }
if (isset($data['precio'])) { $fields[] = 'precio = ?'; $params[] = $data['precio']; }
if (isset($data['descripcion'])) { $fields[] = 'descripcion = ?'; $params[] = trim($data['descripcion']); }
if (isset($data['stock'])) { $fields[] = 'stock = ?'; $params[] = (int)$data['stock']; }

$newImage = null;
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
	$newImage = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
	$dest = $uploadsDir . '/' . $newImage;
	if (!move_uploaded_file($file['tmp_name'], $dest)) {
		http_response_code(500);
		echo json_encode(['ok' => false, 'message' => 'Error al guardar la imagen']);
		exit;
	}
	$fields[] = 'imagen = ?';
	$params[] = $newImage;
}

if (empty($fields)) {
	http_response_code(400);
	echo json_encode(['ok' => false, 'message' => 'No hay campos para actualizar']);
	exit;
}

try {
	global $pdo;

	// If new image uploaded, try to remove old image file
	if ($newImage) {
		$q = $pdo->prepare('SELECT imagen FROM products WHERE id = ?');
		$q->execute([$id]);
		$old = $q->fetchColumn();
		if ($old) {
			$oldPath = $uploadsDir . '/' . $old;
			if (is_file($oldPath)) {
				@unlink($oldPath);
			}
		}
	}

	$params[] = $id; // for WHERE
	$sql = 'UPDATE products SET ' . implode(', ', $fields) . ' WHERE id = ?';
	$stmt = $pdo->prepare($sql);
	$stmt->execute($params);

	echo json_encode(['ok' => true, 'message' => 'Producto actualizado']);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(['ok' => false, 'message' => 'Error interno', 'error' => $e->getMessage()]);
}

