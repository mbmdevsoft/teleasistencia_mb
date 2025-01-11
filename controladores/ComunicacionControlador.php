<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once $_SESSION['DIR_FULL'] .'config/conexion.php';
require_once $_SESSION['DIR_FULL'] . 'includes/funciones.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validar que existe el beneficiario
    $beneficiario_id = $_POST['beneficiario_id'] ?? null;

    if (!$beneficiario_id) {
        throw new Exception("No se especificó el beneficiario");
    }

    $origen = $_POST['origen'] ?? '';

    $pdo = conexion_pdo();
    $stmt = $pdo->prepare("SELECT id FROM beneficiarios WHERE id = ?");
    $stmt->execute([$beneficiario_id]);
    if (!$stmt->fetch()) {
        throw new Exception("El beneficiario no existe");
    }

    // Si es una actualización
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        try {
            $sql = "UPDATE comunicaciones SET 
                tipo = ?,
                categoria_id = ?,
                motivo_llamada = ?,
                resolucion = ?
                WHERE id = ? ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['tipo'],
                $_POST['categoria'],
                $_POST['motivo'],
                $_POST['resolucion'] ?? null,
                $_POST['id'],
            ]);
            registrarLog('INFO', 'Comunicación actualizada', [
                'id' => $_POST['id'],
                'beneficiario_id' => $beneficiario_id
            ]);
            if ($origen == 'general') {
                header('Location: '. $_SESSION['DIR_HOME'] .'vistas/comunicaciones/listado.php');
                exit();
            }
            header('Location: '. $_SESSION['DIR_HOME'] .'vistas/beneficiarios/ver.php?id=' . $beneficiario_id);
            exit();

        } catch (Exception $e) {
            registrarLog('ERROR', 'Error al modificar comunicación', [
                'error' => $e->getMessage(),
                'beneficiario_id' => $beneficiario_id ?? null
            ]);
            header('Location: '. $_SESSION['DIR_HOME'] .'vistas/beneficiarios/comunicaciones/editar_salud.php?beneficiario_id=' .
                $beneficiario_id . '&error=' . urlencode($e->getMessage()));
            exit();
        }

        // Insertar nueva comunicación
    } else {
        try {
            $sql = "INSERT INTO comunicaciones (
                beneficiario_id, 
                usuario_id,
                tipo, 
                categoria_id,
                duracion_minutos,
                motivo_llamada,
                resolucion
            ) VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $beneficiario_id,
                $_SESSION['usuario_id'],
                $_POST['tipo'],
                $_POST['categoria'],
                0,
                $_POST['motivo'],
                $_POST['resolucion'] ?? null,
            ]);

            registrarLog('INFO', 'Nueva comunicación creada', [
                'beneficiario_id' => $beneficiario_id,
                'usuario_id' => $_SESSION['usuario_id']
            ]);
            header('Location: '. $_SESSION['DIR_HOME'] .'vistas/beneficiarios/ver.php?id=' . $beneficiario_id);
            exit();

        } catch (Exception $e) {
            registrarLog('ERROR', 'Error al crear comunicación', [
                'error' => $e->getMessage(),
                'beneficiario_id' => $beneficiario_id ?? null
            ]);
            header('Location: '. $_SESSION['DIR_HOME'] .'vistas/beneficiarios/comunicaciones/editar_salud.php?beneficiario_id=' .
                $beneficiario_id . '&error=' . urlencode($e->getMessage()));
            exit();
        }
    }
}

// Manejo de solicitudes GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');

    $action = $_GET['action'] ?? '';
    $beneficiario_id = $_GET['beneficiario_id'] ?? null;

    switch ($action) {
        case 'listar':
            if (!$beneficiario_id) {
                echo json_encode(['error' => 'ID de beneficiario no proporcionado']);
                exit();
            }

            try {
                $stmt = $pdo->prepare("
                    SELECT c.*, 
                           tc.descripcion as tipo_descripcion,
                           cc.descripcion as categoria_descripcion
                    FROM comunicaciones c
                    LEFT JOIN tipo_comunicacion tc ON c.tipo_comunicacion = tc.id
                    LEFT JOIN categoria_comunicacion cc ON c.categoria = cc.id
                    WHERE c.beneficiario_id = ?
                    ORDER BY c.fecha_hora DESC");
                $stmt->execute([$beneficiario_id]);
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            } catch (PDOException $e) {
                error_log("Error al listar comunicaciones: " . $e->getMessage());
                echo json_encode(['error' => 'Error al obtener las comunicaciones']);
            }
            break;

        case 'obtener':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                echo json_encode(['error' => 'ID de comunicación no proporcionado']);
                exit();
            }

            try {
                $stmt = $pdo->prepare("
                    SELECT c.*, 
                           tc.descripcion as tipo_descripcion,
                           cc.descripcion as categoria_descripcion
                    FROM comunicaciones c
                    LEFT JOIN tipo_comunicacion tc ON c.tipo_comunicacion = tc.id
                    LEFT JOIN categoria_comunicacion cc ON c.categoria = cc.id
                    WHERE c.id = ?");
                $stmt->execute([$id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($result) {
                    echo json_encode($result);
                } else {
                    echo json_encode(['error' => 'Comunicación no encontrada']);
                }
            } catch (PDOException $e) {
                error_log("Error al obtener comunicación: " . $e->getMessage());
                echo json_encode(['error' => 'Error al obtener la comunicación']);
            }
            break;

        case 'eliminar':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                echo json_encode(['error' => 'ID de comunicación no proporcionado']);
                exit();
            }

            try {
                $pdo=conexion_pdo();
                $stmt = $pdo->prepare("DELETE FROM comunicaciones WHERE id = ?");
                $stmt->execute([$id]);

                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true]);
                    registrarLog('INFO', 'Comunicación eliminada', ['id' => $id]);
                } else {
                    throw new Exception('Comunicación no encontrada');
                }
            } catch (PDOException $e) {
                error_log("Error al eliminar comunicación: " . $e->getMessage());
                echo json_encode(['error' => 'Error al eliminar la comunicación']);
            }
            break;
    }
    exit();
}
?>