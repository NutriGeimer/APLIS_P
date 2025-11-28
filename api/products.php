<?php
require __DIR__ . '/db.php';
require __DIR__ . '/_headers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
	http_response_code(405);
	echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
	exit;
}

try {
	$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

	if ($id) {
		$stmt = $pdo->prepare('SELECT id, nombre, precio, descripcion, stock, imagen, created_at FROM products WHERE id = ?');
		$stmt->execute([$id]);
		$product = $stmt->fetch();

		if (!$product) {
			http_response_code(404);
			echo json_encode(['ok' => false, 'message' => 'Producto no encontrado']);
			exit;
		}

		$product['imagen_url'] = $product['imagen'] ? '../uploads/' . $product['imagen'] : null;
		echo json_encode(['ok' => true, 'product' => $product]);
		exit;
	}

	$stmt = $pdo->query('SELECT id, nombre, precio, descripcion, stock, imagen, created_at FROM products ORDER BY created_at DESC');
	$products = $stmt->fetchAll();

	foreach ($products as &$p) {
		$p['imagen_url'] = $p['imagen'] ? '../uploads/' . $p['imagen'] : null;
	}

	echo json_encode(['ok' => true, 'products' => $products]);

} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(['ok' => false, 'message' => 'Error interno', 'error' => $e->getMessage()]);
}

