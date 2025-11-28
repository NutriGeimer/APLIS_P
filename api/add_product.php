<?php
//este archivo agrega un nuevo producto
require_once "headers.php";
require_once "db.php";
require_once "auth.php";

requireAdmin(); 

/*
    POST /api/add_product.php
    Body JSON:
    {
        "nombre": "",
        "precio": 0,
        "descripcion": "",
        "stock": 10,
        "imagen": "ruta.jpg"
    }
*/

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["error" => "Datos inválidos"]);
    exit;
}

$nombre = $data["nombre"] ?? null;
$precio = $data["precio"] ?? null;
$descripcion = $data["descripcion"] ?? null;
$stock = $data["stock"] ?? 0;
$imagen = $data["imagen"] ?? null;

if (!$nombre || !$precio) {
    echo json_encode(["error" => "Nombre y precio son obligatorios"]);
    exit;
}

$db = getDB();

try {
    $stmt = $db->prepare("
        INSERT INTO products (nombre, precio, descripcion, stock, imagen)
        VALUES (:nombre, :precio, :descripcion, :stock, :imagen)
    ");

    $stmt->execute([
        ":nombre" => $nombre,
        ":precio" => $precio,
        ":descripcion" => $descripcion,
        ":stock" => $stock,
        ":imagen" => $imagen
    ]);

    echo json_encode(["success" => true, "id" => $db->lastInsertId()]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al agregar producto"]);
}
