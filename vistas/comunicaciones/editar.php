<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once $_SESSION['DIR_FULL'] .'config/conexion.php';
require_once $_SESSION['DIR_FULL'] .'vistas/plantillas/cabecera.php';

// Obtener el ID de la comunicación
$id = $_GET['id'] ?? null;

// Obtener el origen del enlace para volver al mismo sitio
$origen = $_GET['origen'] ?? ''; // 'general' viene del listado general de llamadas


if (!$id) {
    header('Location:  '.$_SESSION['DIR_FULL']. 'vistas/beneficiarios/listado.php?error=No se especificó la comunicación');
    exit();
}

try {
    // Obtener datos de la comunicación
    $pdo = conexion_pdo();
    $stmt = $pdo->prepare("
        SELECT c.*, b.nombre as beneficiario_nombre, 
               b.apellido1 as beneficiario_apellido1, 
               b.apellido2 as beneficiario_apellido2
        FROM comunicaciones c
        JOIN beneficiarios b ON c.beneficiario_id = b.id
        WHERE c.id = ?");
    $stmt->execute([$id]);
    $comunicacion = $stmt->fetch();

    if (!$comunicacion) {
        throw new Exception('Comunicación no encontrada');
    }

    // Obtener contactos del beneficiario para el select de comunicante
    $stmt = $pdo->prepare("
        SELECT id, nombre, apellidos 
        FROM contactos 
        WHERE beneficiario_id = ? 
        ORDER BY orden_prioridad");
    $stmt->execute([$comunicacion['beneficiario_id']]);
    $contactos = $stmt->fetchAll();

} catch (Exception $e) {
    header('Location: '.$_SESSION['DIR_FULL']. 'vistas/beneficiarios/listado.php?error=' . urlencode($e->getMessage()));
    exit();
}
?>

<main>
    <div class="form-container">
        <h2>Editar Comunicación</h2>
        <h3>Beneficiario: <?php echo htmlspecialchars($comunicacion['beneficiario_nombre'] . ' ' .
                $comunicacion['beneficiario_apellido1'] . ' ' .
                ($comunicacion['beneficiario_apellido2'] ?? '')); ?></h3>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <form action="<?=$_SESSION['DIR_HOME']?>controladores/ComunicacionControlador.php" method="POST"
              class="comunicacion-form">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <input type="hidden" name="beneficiario_id" value="<?php echo $comunicacion['beneficiario_id']; ?>">
            <input type="hidden" name="origen" value="<?php echo $origen; ?>">

            <div class="form-group">
                <label for="tipo">Tipo de Llamada:</label>
                <select id="tipo" name="tipo" required>
                    <option value="ENTRANTE" <?php echo $comunicacion['tipo'] == 'ENTRANTE' ? 'selected' : ''; ?>>
                        ENTRANTE
                    </option>
                    <option value="SALIENTE" <?php echo $comunicacion['tipo'] === 'SALIENTE' ? 'selected' : ''; ?>>
                        SALIENTE
                    </option>
                </select>
            </div>


            <div class="form-group">
                <label for="categoria">Categoría:</label>
                <select id="categoria" name="categoria" required>
                    <option value="">Seleccione...</option>
                    <?php
                    $stmt = $pdo->query("SELECT id, descripcion FROM comunicacion_categoria");
                    while ($row = $stmt->fetch()) {
                        $selected = ($row['id'] == $comunicacion['categoria_id']) ? 'selected' : '';
                        echo '<option value="' . $row['id'] . '" ' . $selected . '>' .
                            htmlspecialchars($row['descripcion']) . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="motivo">Motivo de la llamada:</label>
                <textarea id="motivo" name="motivo" rows="4" required><?php
                    echo htmlspecialchars($comunicacion['motivo_llamada']);
                    ?></textarea>
            </div>

            <div class="form-group">
                <label for="resolucion">Resolución:</label>
                <textarea id="resolucion" name="resolucion" rows="4"><?php
                    echo htmlspecialchars($comunicacion['resolucion'] ?? '');
                    ?></textarea>
            </div>

            <div class="form-buttons">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
                <?php
                if ($origen == 'general') {
                    $v_origen = $_SESSION['DIR_HOME']."vistas/comunicaciones/listado.php";
                } else {
                    $v_origen = $_SESSION['DIR_HOME']."vistas/comunicaciones/ver.php?id={$comunicacion['beneficiario_id']}";
                }
                ?>
                <a href="<?=$v_origen ?>" class="btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>


            </div>
        </form>
    </div>
</main>


<?php require_once $_SESSION['DIR_FULL'] . '/vistas/plantillas/pie.php'; ?>
