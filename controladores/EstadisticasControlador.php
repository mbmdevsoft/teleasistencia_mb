<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once $_SESSION['DIR_FULL'] .'config/conexion.php';
require_once $_SESSION['DIR_FULL'] . 'includes/funciones.php';

header('Content-Type: application/json');

try {
    // Estadísticas de beneficiarios
    $pdo=conexion_pdo();
    $stmt = $pdo->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN estado_id = 1 THEN 1 ELSE 0 END) as activos,
        SUM(CASE WHEN estado_id = 2 THEN 1 ELSE 0 END) as bajas
        FROM beneficiarios 
        ");
    $beneficiarios = $stmt->fetch(PDO::FETCH_ASSOC);

    // Estadísticas de agenda para hoy
    $stmt = $pdo->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN estado_id = 1 THEN 1 ELSE 0 END) as pendientes,
        SUM(CASE WHEN estado_id = 2 THEN 1 ELSE 0 END) as completadas
        FROM agendas 
        WHERE DATE(fecha_programada) = CURDATE()");
    $agenda = $stmt->fetch(PDO::FETCH_ASSOC);

    // Estadísticas de comunicaciones
    $stmt = $pdo->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN tipo = 'entrante' THEN 1 ELSE 0 END) as entrantes,
        SUM(CASE WHEN tipo = 'saliente' THEN 1 ELSE 0 END) as salientes
        FROM comunicaciones
        WHERE DATE(comunicaciones.fecha_creacion) = CURDATE()");
    $comunicaciones = $stmt->fetch(PDO::FETCH_ASSOC);

    // Devolver todas las estadísticas
    echo json_encode([
        'success' => true,
        'beneficiariosTotal' => $beneficiarios['total'] ?? 0,
        'beneficiariosActivos' => $beneficiarios['activos'] ?? 0,
        'citasPendientes' => $agenda['pendientes'] ?? 0,
        'citasCompletadas' => $agenda['completadas'] ?? 0,
        'comunicacionesTotal' => $comunicaciones['total'] ?? 0,
        'comunicacionesEntrantes' => $comunicaciones['entrantes'] ?? 0,
        'comunicacionesSalientes' => $comunicaciones['salientes'] ?? 0
    ]);

} catch(PDOException $e) {
    error_log("Error en estadísticas: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener estadísticas'
    ]);
}
?>