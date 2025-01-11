<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once $_SESSION['DIR_FULL'] .'config/conexion.php';
require_once $_SESSION['DIR_FULL'] . 'includes/funciones.php';

$pdo = conexion_pdo();
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'listar':
            $stmt = $pdo->query("SELECT * FROM beneficiarios ORDER BY apellido1, apellido2, nombre");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        case 'estadisticas':
            $stmt = $pdo->query("SELECT 
               COUNT(*) as total,
               SUM(CASE WHEN estado_id = 1 THEN 1 ELSE 0 END) as activos,
               SUM(CASE WHEN estado_id = 2 THEN 1 ELSE 0 END) as bajas
               FROM beneficiarios");
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
            break;

        case 'buscar':
            $texto = sanitizar($_GET['texto'] ?? '');
            $stmt = $pdo->prepare("SELECT * FROM beneficiarios WHERE 
               nombre LIKE ? OR 
               apellido1 LIKE ? OR 
               apellido2 LIKE ? OR 
               nif_nie LIKE ? OR 
               numero_expediente LIKE ?
               ORDER BY apellido1, apellido2, nombre");
            $param = "%$texto%";
            $stmt->execute([$param, $param, $param, $param, $param]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    try {
        // Manejar la eliminación
        if (isset($_POST['action']) && $_POST['action'] === 'eliminar') {
            $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

            if ($id <= 0) {
                throw new Exception('ID de beneficiario inválido');
            }

            $pdo->beginTransaction();

            // Eliminar registros relacionados primero
            $stmt = $pdo->prepare("DELETE FROM contactos WHERE beneficiario_id = ?");
            $stmt->execute([$id]);

            $stmt = $pdo->prepare("DELETE FROM comunicaciones WHERE beneficiario_id = ?");
            $stmt->execute([$id]);

            $stmt = $pdo->prepare("DELETE FROM agendas WHERE beneficiario_id = ?");
            $stmt->execute([$id]);

            // Finalmente eliminar el beneficiario
            $stmt = $pdo->prepare("DELETE FROM beneficiarios WHERE id = ?");
            $stmt->execute([$id]);

            $pdo->commit();

            echo json_encode(['success' => true]);
            exit();
        } // Actualizar beneficiario: Datos generales
        else if (isset($_POST['action']) && $_POST['action'] === 'update') {
            header('Content-Type: text/html; charset=UTF-8');
            $datos = sanitizarDatos($_POST);
            $errores = validarDatosBeneficiario($datos);
            if (!empty($errores)) {
                header('Location: '. $_SESSION['DIR_HOME'] .'vistas/beneficiarios/editar.php?id=' . $_POST['id'] . '&error=' . implode(' ', $errores));
                exit();
            }

            $sql = "UPDATE beneficiarios SET 
                nif_nie = ?,
                nombre = ?,
                apellido1 = ?,
                apellido2 = ?,
                fecha_nacimiento = ?,
                genero = ?,
                vive_solo = ?,
                direccion = ?,
                codigo_postal = ?,
                provincia = ?,
                poblacion = ?,
                telefono_fijo = ?,
                telefono_movil = ?,
                email = ?,
                centro_salud = ?,
                estado_id = ?
                WHERE id = ?";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                !empty($_POST['nif_nie']) ? $_POST['nif_nie'] : null,
                $_POST['nombre'],
                $_POST['apellido1'],
                $_POST['apellido2'] ?? null,
                $_POST['fecha_nacimiento'],
                $_POST['genero'],
                $_POST['vive_solo'] ?? null,
                $_POST['direccion'],
                $_POST['codigo_postal'] ?? null,
                $_POST['provincia'] ?? null,
                $_POST['poblacion'] ?? null,
                $_POST['telefono_fijo'] ?? null,
                $_POST['telefono_movil'] ?? null,
                $_POST['email'] ?? null,
                $_POST['centro_salud'] ?? null,
                $_POST['estado'],
                $_POST['id']
            ]);

            header('Location: '. $_SESSION['DIR_HOME'] .'vistas/beneficiarios/ver.php?id=' . $_POST['id']);
            exit();
        } // Actualizar beneficiario: Datos Sanitarios
        else if (isset($_POST['action']) && $_POST['action'] === 'update_sanitarios') {
            header('Content-Type: text/html; charset=UTF-8');
            $sql = "UPDATE beneficiarios SET 
                enfermedades = ?,
                alergias = ?,
                medicacion = ?,
                intervenciones = ?,
                dieta = ?,
                otras = ?
                WHERE id = ?";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['enfermedades'] ?? null,
                $_POST['alergias'] ?? null,
                $_POST['medicacion'] ?? null,
                $_POST['intervenciones'] ?? null,
                $_POST['dieta'] ?? null,
                $_POST['otras'] ?? null,
                $_POST['id']
            ]);
            header('Location: '. $_SESSION['DIR_HOME'] .'vistas/beneficiarios/ver.php?id=' . $_POST['id']);
            exit();
        } // Crear nuevo beneficiario
        else {
            header('Content-Type: text/html; charset=UTF-8');
            // Verificar si el número de expediente ya existe
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM beneficiarios WHERE numero_expediente = ?");
            $stmt->execute([$_POST['numero_expediente']]);
            if ($stmt->fetchColumn() > 0) {
                throw new PDOException("El número de expediente ya existe");
            }

            $sql = "INSERT INTO beneficiarios (
                numero_expediente, nif_nie, nombre, apellido1, apellido2,
                fecha_nacimiento, genero, vive_solo, direccion, codigo_postal,
                provincia, poblacion, telefono_fijo, telefono_movil, email,
                centro_salud, estado_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['numero_expediente'],
                !empty($_POST['nif_nie']) ? $_POST['nif_nie'] : null,
                $_POST['nombre'],
                $_POST['apellido1'],
                $_POST['apellido2'] ?? null,
                $_POST['fecha_nacimiento'],
                $_POST['genero'],
                $_POST['vive_solo'] ?? null,
                $_POST['direccion'],
                $_POST['codigo_postal'] ?? null,
                $_POST['provincia'] ?? null,
                $_POST['poblacion'] ?? null,
                $_POST['telefono_fijo'] ?? null,
                $_POST['telefono_movil'] ?? null,
                $_POST['email'] ?? null,
                $_POST['centro_salud'] ?? null,
                $_POST['estado']
            ]);
            header('Location: '. $_SESSION['DIR_HOME'] .'vistas/beneficiarios/listado.php?success=1');
            exit();

        }

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        registrarErr('ERROR', 'Error en operacion del beneficiario ', [
            'error' => $e->getMessage(),
            'beneficiario_id' => $beneficiario_id ?? null
        ]);

        if (isset($_POST['action']) && $_POST['action'] === 'eliminar') {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } elseif (isset($_POST['action']) && $_POST['action'] === 'update') {
            header('Location: '. $_SESSION['DIR_HOME'] .'vistas/beneficiarios/editar.php?id=' . $_POST['id'] . '&error=' . $e->getMessage());
            exit();
        } elseif (isset($_POST['action']) && $_POST['action'] === 'update_sanitarios') {
            header('Location: '. $_SESSION['DIR_HOME'] .'vistas/beneficiarios/editar_salud.php?id=' . $_POST['id'] . '&error=' . $e->getMessage());
            exit();

        } else
            header('Location: '. $_SESSION['DIR_HOME'] .'vistas/beneficiarios/crear.php?error=' . urlencode($e->getMessage()));
        exit();
    }


}
?>