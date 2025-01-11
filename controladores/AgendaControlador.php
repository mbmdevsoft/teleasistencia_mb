<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once $_SESSION['DIR_FULL'] .'config/conexion.php';
require_once $_SESSION['DIR_FULL'] . 'includes/funciones.php';


//verificarSesion();

// Verificar si es una petición de eliminación
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'eliminar') {
    header('Content-Type: application/json');

    try {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;

        if (!$id) {
            throw new Exception('ID de agenda no proporcionado');
        }

        // Primero obtener el beneficiario_id para la redirección
        $pdo=conexion_pdo();
        $stmt = $pdo->prepare("SELECT beneficiario_id FROM agendas WHERE id = ?");
        $stmt->execute([$id]);
        $agenda = $stmt->fetch();

        if (!$agenda) {
            throw new Exception('Agenda no encontrada');
        }

        // Eliminar la agenda
        $stmt = $pdo->prepare("DELETE FROM agendas WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            registrarLog('INFO', 'Agenda eliminada', ['id' => $id]);
            echo json_encode([
                'success' => true,
                'message' => 'Agenda eliminada correctamente',
                'beneficiario_id' => $agenda['beneficiario_id']
            ]);
        } else {
            throw new Exception('No se pudo eliminar la agenda');
        }
    } catch (Exception $e) {
        registrarLog('ERROR', 'Error al eliminar agenda', ['error' => $e->getMessage()]);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}

// Manejo de POST para crear/actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $beneficiario_id = $_POST['beneficiario_id'] ?? null;

        if (!$beneficiario_id) {
            throw new Exception("No se especificó el beneficiario");
        }

        // Verificar que existe el beneficiario
        $pdo=conexion_pdo();
        $stmt = $pdo->prepare("SELECT id FROM beneficiarios WHERE id = ?");
        $stmt->execute([$beneficiario_id]);
        if (!$stmt->fetch()) {
            throw new Exception("El beneficiario no existe");
        }

        // Si es una actualización
        if (isset($_POST['action']) && $_POST['action'] === 'update') {
            $sql = "UPDATE agendas SET 
                tipo_id = ?,
                fecha_programada = ?,
                descripcion = ?,
                notas = ?,
                estado_id = ?
                WHERE id = ? AND beneficiario_id = ?";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['tipo_agenda'],
                $_POST['fecha_programada'],
                $_POST['descripcion'],
                $_POST['notas'] ?? null,
                $_POST['estado'],
                $_POST['id'],
                $beneficiario_id
            ]);

            registrarLog('INFO', 'Agenda actualizada', ['id' => $_POST['id']]);

        } else {
            // Insertar nueva agenda
            $sql = "INSERT INTO agendas (
                beneficiario_id,
                tipo_id,
                fecha_programada,
                descripcion,
                estado_id,
                notas,
                usuario_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $beneficiario_id,
                $_POST['tipo_agenda'],
                $_POST['fecha_programada'],
                $_POST['descripcion'],
                $_POST['estado'],
                $_POST['notas'] ?? null,
                $_SESSION['usuario_id']
            ]);

            registrarLog('INFO', 'Nueva agenda creada', ['beneficiario_id' => $beneficiario_id]);
        }

        header('Location: ' . $_SESSION['DIR_HOME']. 'vistas/beneficiarios/ver.php?id=' . $beneficiario_id);
        exit();

    } catch(Exception $e) {
        registrarLog('ERROR', 'Error en operación de agenda', [
            'error' => $e->getMessage(),
            'beneficiario_id' => $beneficiario_id ?? null
        ]);

        $redirect_url = isset($_POST['action']) && $_POST['action'] === 'update' ?
            'editar_salud.php?id=' . $_POST['id'] :
            'crear.php?beneficiario_id=' . $beneficiario_id;

        header('Location: ' . $_SESSION['DIR_HOME']. 'vistas/agendas/' . $redirect_url . '&error=' . urlencode($e->getMessage()));
        exit();
    }
}
?>
