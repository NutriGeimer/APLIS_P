<?php
require __DIR__ . '/_headers.php';
require __DIR__ . '/db.php';

$name = "Administrador";
$email = "admin@nutricho.com";
$pass = "admin123";

$hash = password_hash($pass, PASSWORD_DEFAULT);

$query = $pdo->prepare("INSERT INTO users(nombre, email, password_hash, rol) VALUES (?, ?, ?, 'admin')");
$query->execute([$name, $email, $hash]);

echo json_encode([
    "ok" => true,
    "message" => "Admin creado correctamente"
]);

// Para crear el admin, ve a la direccion: http://localhost:8888/APLIS_P/api/create_admin.php
// Esto creara al admin y ya podras acceder a la pagina admin.html