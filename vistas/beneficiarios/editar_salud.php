<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once $_SESSION['DIR_FULL'] .'config/conexion.php';
require_once $_SESSION['DIR_FULL'] .'vistas/plantillas/cabecera.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: ' . $_SESSION['DIR_FULL'] . 'vistas/beneficiarios/listado.php?error=No se especificó el beneficiario');
    exit();
}


try {
    // Obtener datos del beneficiario
    $pdo = conexion_pdo();
    $stmt = $pdo->prepare("SELECT * FROM beneficiarios WHERE id = ?");
    $stmt->execute([$id]);
    $beneficiario = $stmt->fetch();

} catch (Exception $e) {
    header('Location: ' . $_SESSION['DIR_FULL'] . 'vistas/beneficiarios/listado.php?error=' . urlencode($e->getMessage()));
    exit();
}

?>

<main>
    <div class="form-container">
        <h2>Datos de Salud</h2>
        <h3>
            Beneficiario: <?php echo htmlspecialchars($beneficiario['nombre'] . ' ' . $beneficiario['apellido1'] . ' ' . ($beneficiario['apellido2'] ?? '')); ?></h3>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <form action="<?=$_SESSION['DIR_HOME']?>controladores/BeneficiarioControlador.php" method="POST"
              class="datos-salud-form">

            <input type="hidden" name="action" value="update_sanitarios">
            <input type="hidden" name="id" value="<?php echo $id; ?>">



            <div class="form-group">
                <label for="enfermedades">Enfermedades:</label>
                <textarea id="enfermedades" name="enfermedades"
                          rows="4"><?php echo htmlspecialchars($beneficiario['enfermedades'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="alergias">Alergias:</label>
                <textarea id="alergias" name="alergias"
                          rows="4"><?php echo htmlspecialchars($beneficiario['alergias'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="medicacion">Medicación Actual:</label>
                <textarea id="medicacion" name="medicacion"
                          rows="4"><?php echo htmlspecialchars($beneficiario['medicacion'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="intervenciones">Intervenciones Quirúrgicas:</label>
                <textarea id="intervenciones" name="intervenciones"
                          rows="4"><?php echo htmlspecialchars($beneficiario['intervenciones'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="dieta">Dieta Especial:</label>
                <textarea id="dieta" name="dieta"
                          rows="4"><?php echo htmlspecialchars($beneficiario['dieta'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="otras">Otras:</label>
                <textarea id="otras" name="otras"
                          rows="4"><?php echo htmlspecialchars($beneficiario['otras'] ?? ''); ?></textarea>
            </div>

            <div class="form-buttons">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
                <a href="<?=$_SESSION['DIR_HOME']?>vistas/beneficiarios/ver.php?id=<?php echo $id; ?>"
                   class="btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</main>

<?php require_once $_SESSION['DIR_FULL'] . 'vistas/plantillas/pie.php'; ?>
