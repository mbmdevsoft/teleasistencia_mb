<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once $_SESSION['DIR_FULL'] .'config/conexion.php';
require_once $_SESSION['DIR_FULL'] . 'includes/funciones.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = conexion_pdo();

    $beneficiario_id = $_POST['beneficiario_id'] ?? null;

    // Modificar contacto
    if (isset($_POST['action']) && $_POST['action'] == 'update') {
        try {
            // comprueba beneficiario
            if (!$beneficiario_id) {
                throw new Exception("No se especificó el beneficiario");
            }
            $stmt = $pdo->prepare("SELECT id FROM beneficiarios WHERE id = ?");
            $stmt->execute([$beneficiario_id]);
            if (!$stmt->fetch()) {
                throw new Exception("El beneficiario no existe");
            }


            $sql = " UPDATE contactos  set
            parentesco_id =?,   
            orden_prioridad =?, 
            nombre =?,
            apellidos =?, 
            tiene_llave =?, 
            direccion = ?,
            distancia_metros =?, 
            telefono_fijo=?, 
            telefono_movil=?, 
            email=?,
            horario_disponibilidad=?, 
            centro_trabajo=?, 
            telefono_trabajo=?
            WHERE id = ?";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['parentesco'] ?? null,
                $_POST['orden_prioridad'] ?? null,
                $_POST['nombre'],
                $_POST['apellidos'],
                $_POST['tiene_llave'],
                $_POST['direccion'] ?? null,
                $_POST['distancia_metros'] ?? null,
                $_POST['telefono_fijo'] ?? null,
                $_POST['telefono_movil'] ?? null,
                $_POST['email'] ?? null,
                $_POST['horario_disponibilidad'] ?? null,
                $_POST['centro_trabajo'] ?? null,
                $_POST['telefono_trabajo'] ?? null,
                $_POST['id'],
            ]);
            registrarLog('INFO', 'Contacto modificado', [
                'beneficiario_id' => $beneficiario_id,
                'nombre_contacto' => $_POST['nombre'] . ' ' . $_POST['apellidos']
            ]);


        } catch (Exception $e) {
            registrarErr('ERROR', 'Error al modificar contacto', [
                'error' => $e->getMessage(),
                'beneficiario_id' => $beneficiario_id ?? null
            ]);
            header('Location: ' . $_SESSION['DIR_HOME']. 'vistas/contactos/editar.php?beneficiario_id=' .
                $beneficiario_id . '&error=' . urlencode($e->getMessage()));
            exit();
        }


    } // Nuevo contacto
    else {
        try {
            // comprueba beneficiario
            if (!$beneficiario_id) {
                throw new Exception("No se especificó el beneficiario");
            }
            $stmt = $pdo->prepare("SELECT id FROM beneficiarios WHERE id = ?");
            $stmt->execute([$beneficiario_id]);
            if (!$stmt->fetch()) {
                throw new Exception("El beneficiario no existe");
            }

            // Verificar contactos existentes
            // Si se intenta añadir más de 3 contactos
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM contactos WHERE beneficiario_id = ?");
            $stmt->execute([$beneficiario_id]);
            $contactos_existentes = $stmt->fetchColumn();
            if ($contactos_existentes >= 3) {
                throw new Exception("El beneficiario ya tiene el máximo de 3 contactos permitidos");
            }


            // Validar campos requeridos
            if (empty($_POST['nombre']) || empty($_POST['apellidos'])) {
                throw new Exception("El nombre y apellidos son obligatorios");
            }


            // Validar formato de teléfonos
            foreach (['telefono_fijo', 'telefono_movil', 'telefono_trabajo'] as $tel) {
                if (!empty($_POST[$tel]) && !validarTelefono($_POST[$tel])) {
                    throw new Exception("El formato del teléfono no es válido. Debe ser un número de 9 dígitos");
                }
            }

            // Validar email si está presente
            if (!empty($_POST['email']) && !validarEmail($_POST['email'])) {
                throw new Exception("El formato del email no es válido");
            }


            // Insertar contacto
            $sql = "INSERT INTO contactos (
            beneficiario_id,
            parentesco_id,           
            orden_prioridad, 
            nombre,
            apellidos, 
            tiene_llave, 
            direccion,
            distancia_metros, 
            telefono_fijo, 
            telefono_movil, 
            email,
            horario_disponibilidad, 
            centro_trabajo, 
            telefono_trabajo
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $beneficiario_id,
                $_POST['parentesco'] ?? null,
                0,
                $_POST['nombre'],
                $_POST['apellidos'],
                $_POST['tiene_llave'],
                $_POST['direccion'] ?? null,
                0,
                $_POST['telefono_fijo'] ?? null,
                $_POST['telefono_movil'] ?? null,
                $_POST['email'] ?? null,
                $_POST['horario_disponibilidad'] ?? null,
                $_POST['centro_trabajo'] ?? null,
                $_POST['telefono_trabajo'] ?? null
            ]);

            registrarLog('INFO', 'Contacto creado', [
                'beneficiario_id' => $beneficiario_id,
                'nombre_contacto' => $_POST['nombre'] . ' ' . $_POST['apellidos']
            ]);

        } catch (Exception $e) {
            registrarErr('ERROR', 'Error al crear contacto', [
                'error' => $e->getMessage(),
                'beneficiario_id' => $beneficiario_id ?? null
            ]);
            header('Location: ' . $_SESSION['DIR_HOME']. 'vistas/contactos/crear.php?beneficiario_id=' .
                $beneficiario_id . '&error=' . urlencode($e->getMessage()));
            exit();
        }
    }

    header('Location: ' . $_SESSION['DIR_HOME']. 'vistas/beneficiarios/ver.php?id=' . $beneficiario_id);
    exit();

}


// Manejo de solicitudes GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');

    $action = $_GET['action'] ?? '';
    $beneficiario_id = $_GET['beneficiario_id'] ?? null;
    $pdo = conexion_pdo();

    switch ($action) {
        case 'listar':
            if (!$beneficiario_id) {
                echo json_encode(['error' => 'ID de beneficiario no proporcionado']);
                exit();
            }

            try {
                $stmt = $pdo->prepare("
                    SELECT c.*, 
                           CASE c.orden_prioridad 
                               WHEN 1 THEN 'Principal'
                               WHEN 2 THEN 'Secundario'
                               WHEN 3 THEN 'Alternativo'
                               ELSE 'Sin especificar'
                           END as prioridad_texto
                    FROM contactos c 
                    WHERE c.beneficiario_id = ? 
                    ORDER BY c.orden_prioridad");
                $stmt->execute([$beneficiario_id]);
                echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            } catch (PDOException $e) {
                error_log("Error al listar contactos: " . $e->getMessage());
                echo json_encode(['error' => 'Error al obtener los contactos']);
            }
            break;

        case 'eliminar':
            $id = $_GET['id'] ?? null;

            if (!$id) {
                echo json_encode(['error' => 'ID de contacto no proporcionado']);
                exit();
            }

            try {
                // Verificar que el contacto existe y obtener datos para el log
                $stmt = $pdo->prepare("SELECT beneficiario_id, nombre, apellidos FROM contactos WHERE id = ?");
                $stmt->execute([$id]);
                $contacto = $stmt->fetch();

                if (!$contacto) {
                    throw new Exception("Contacto no encontrado");
                }

                // Eliminar el contacto
                $stmt = $pdo->prepare("DELETE FROM contactos WHERE id = ?");
                $stmt->execute([$id]);

                registrarLog('INFO', 'Contacto eliminado', [
                    'id' => $id,
                    'beneficiario_id' => $contacto['beneficiario_id'],
                    'nombre' => $contacto['nombre'] . ' ' . $contacto['apellidos']
                ]);

                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                error_log("Error al eliminar contacto: " . $e->getMessage());
                echo json_encode(['error' => 'Error al eliminar el contacto']);
            }
            break;

        default:
            echo json_encode(['error' => 'Acción no válida']);
            break;
    }
    exit();
}
?>