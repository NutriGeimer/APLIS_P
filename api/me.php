<?php
    require __DIR__ . '/_headers.php';
    require __DIR__ . '/auth.php';

    $user = current_user_or_401();
    echo json_encode(['ok' => true, 'user' => $user]);