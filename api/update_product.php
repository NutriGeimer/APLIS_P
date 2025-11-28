<?php
require_once "headers.php";
require_once "db.php";
require_once "auth.php";

requireAdmin(); // 🔒 Solo administrador

/*
    PUT /api/update_product.php?id=3
    Body JSON:
    {
        "nombre": "",
        "precio": 100,
        "descripcion": "",
        "stock": 50,
        "imagen": "nueva.jpg"
    }
*/

if (!isset($_GET["id"])) {
    http_response_code(400);
    echo json_encode(["error" => "ID requerido"]);
    exit;
}

$id = intval($_GET["id"]);
$data = json_decode(file_get_contents("php://input"), true);

$db = getDB();

try {
    $stmt = $db->prepare("
        UPDATE products 
        SET nombre = :nombre,
            precio = :precio,
            descripcion = :descripcion,
            stock = :stock,
            imagen = :imagen
        WHERE id = :id
    ");

    $stmt->execute([
        ":nombre" => $data["nombre"],
        ":precio" => $data["precio"],
        ":descripcion" => $data["descripcion"],
        ":stock" => $data["stock"],
        ":imagen" => $data["imagen"],
        ":id" => $id
    ]);

    echo json_encode(["success" => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al actualizar producto"]);
}
?>
