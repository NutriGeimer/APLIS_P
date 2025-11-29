<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/_headers.php';

$user = current_user_or_401();

if ($user['rol'] !== 'admin') {
    echo json_encode(["ok" => false, "message" => "Acceso denegado"]);
    exit;
}

global $pdo;

try {
    // Obtener gastos de restock
    $restockStmt = $pdo->query("
        SELECT r.id, r.product_id, r.cantidad, r.costo_unitario, r.fecha,
               p.nombre, p.imagen
        FROM restocks r
        INNER JOIN products p ON p.id = r.product_id
        ORDER BY r.fecha DESC
    ");
    $restocks = $restockStmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcular total de gasto
    $total_gasto = 0;
    foreach ($restocks as $r) {
        $total_gasto += $r['cantidad'] * $r['costo_unitario'];
    }

    // Total de ventas
    $ventasStmt = $pdo->query("SELECT SUM(total) AS total_ventas FROM ventas");
    $ventasRow = $ventasStmt->fetch();
    $total_ventas = $ventasRow['total_ventas'] ?? 0;

    // Balance
    $balance = $total_ventas - $total_gasto;

    echo json_encode([
        "ok" => true,
        "restocks" => $restocks,
        "total_ventas" => floatval($total_ventas),
        "total_gasto" => floatval($total_gasto),
        "balance" => floatval($balance)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "ok" => false,
        "message" => "Error al obtener restocks",
        "error" => $e->getMessage()
    ]);
}