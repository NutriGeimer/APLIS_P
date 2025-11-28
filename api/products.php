<?php
//Este archivo enlista los productos 
require_once "headers.php";
require_once "db.php";
require_once "auth.php";


$db = getDB();

try {
    $stmt = $db->prepare("SELECT * FROM products ORDER BY id DESC");
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($productos);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al obtener productos"]);
}

