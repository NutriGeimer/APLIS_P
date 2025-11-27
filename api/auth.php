<?php
require __DIR__ . '/db.php';  
require __DIR__ . '/_headers.php';

function current_user_or_401() {
    $token = $_COOKIE['token'] ?? null;

    if (!$token) {
        http_response_code(401);
        echo json_encode([
            'ok' => false,
            'message' => 'No token proporcionado'
        ]);
        exit;
    }

    global $pdo;
    $sql = "SELECT u.id, u.nombre, u.email, u.rol
            FROM sessions s
            JOIN users u ON u.id = s.user_id
            WHERE s.token = ? AND s.expires_at > NOW()";

    $query = $pdo->prepare($sql);
    $query->execute([$token]);
    $user = $query->fetch();

    if (!$user) {
        http_response_code(401);
        echo json_encode([
            'ok' => false,
            'message' => 'Token inválido o expirado'
        ]);
        exit;
    }

    return $user;
}
