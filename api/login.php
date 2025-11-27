<?php
    require __DIR__ . '/_headers.php';
    require __DIR__ . '/db.php';
    
    $inputs = json_decode(file_get_contents('php://input'), true);
    $email = trim($inputs['email'] ?? '');
    $pass = trim($inputs['password'] ?? '');

    $query = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $query->execute([$email]);
    $user = $query->fetch();

    if (!$user || !password_verify($pass, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode([
            'ok' => false, 
            'message' => 'Credenciales inválidas'
        ]);
        exit;
    }

    $token = bin2hex(random_bytes(32));
    $exp = (new DateTime('+2 hours'))->format('Y-m-d H:i:s');
    $pdo->prepare("INSERT INTO sessions (user_id, token, expires_at) VALUES (?, ?, ?)")->execute([$user['id'], $token, $exp]);

    setcookie('token', $token, [
        'expires' => time() + 7200,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    ]);

    echo json_encode([
        'ok'=> true, 
        'user' => [
            'id'=>$user['id'], 
            'nombre'=>$user['nombre'], 
            'email'=>$user['email'], 
            'rol'=>$user['rol']
        ]
    ]);