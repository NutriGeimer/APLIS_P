<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/_headers.php';

$user = current_user_or_401();
if (($user['rol'] ?? null) !== 'admin') {
	http_response_code(403);
	echo json_encode(['ok' => false, 'message' => 'Acceso denegado']);
	exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
	http_response_code(405);
	echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
	exit;
}

$input = null;
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($contentType, 'application/json') !== false) {
	$raw = file_get_contents('php://input');
	$input = json_decode($raw, true) ?: [];
} else {
	$input = $_POST;
}

$id = isset($input['id']) ? (int)$input['id'] : null;
if (!$id) {
	http_response_code(400);
	echo json_encode(['ok' => false, 'message' => 'ID de producto requerido']);
	exit;
}

try {
	global $pdo;
	$stmt = $pdo->prepare('SELECT imagen FROM products WHERE id = ?');
	$stmt->execute([$id]);
	$imagen = $stmt->fetchColumn();

	$del = $pdo->prepare('DELETE FROM products WHERE id = ?');
	$del->execute([$id]);

	if ($imagen) {
		$path = __DIR__ . '/../uploads/' . $imagen;
		if (is_file($path)) {
			@unlink($path);
		}
	}

	echo json_encode(['ok' => true, 'message' => 'Producto eliminado']);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(['ok' => false, 'message' => 'Error interno', 'error' => $e->getMessage()]);
}

