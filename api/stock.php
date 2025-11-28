<?php
//Actualiza el stock basado en ventas, faltan hacer ventan todavia
require_once "headers.php";
require_once "db.php";
require_once "auth.php";

requireAdmin();

/*
    POST /api/stock.php
    Body JSON:
    {
        "product_id": 3,
        "cantidad": 5,
        "tipo": "restar"  // o "sumar"
    }
*/

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["error" => "Datos inválidos"]);
    exit;
}

$id = $data["product_id"] ?? null;
$cantidad = $data["cantidad"] ?? 0;
$tipo = $data["tipo"] ?? "";

if (!$id || $cantidad <= 0 || ($tipo !== "sumar" && $tipo !== "restar")) {
    echo json_encode(["error" => "Datos insuficientes"]);
    exit;
}

$db = getDB();

try {
    if ($tipo === "restar") {
        $stmt = $db->prepare("
            UPDATE products SET stock = stock - :cantidad
            WHERE id = :id AND stock >= :cantidad
        ");
    } else {
        $stmt = $db->prepare("
            UPDATE products SET stock = stock + :cantidad
            WHERE id = :id
        ");
    }

    $stmt->execute([":cantidad" => $cantidad, ":id" => $id]);

    if ($stmt->rowCount() === 0 && $tipo === "restar") {
        echo json_encode(["error" => "No hay stock suficiente"]);
        exit;
    }

    echo json_encode(["success" => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al actualizar stock"]);
}
?>
