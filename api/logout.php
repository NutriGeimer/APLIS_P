<?php
    require __DIR__ . '/_headers.php';
    require __DIR__ . '/db.php';

    $token = $_COOKIE['token'] ?? null;
    if ($token) {
        $pdo->prepare("DELETE FROM sessions WHERE token=?")->execute([$token]);
        setcookie('token', '', time() - 3600, '/');
    }
    return json_encode(['ok' => true]);
