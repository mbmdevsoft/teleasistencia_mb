<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once $_SESSION['DIR_FULL'] . 'config/conexion.php';
require_once $_SESSION['DIR_FULL'] . 'includes/funciones.php';
require_once $_SESSION['DIR_FULL'] . 'vistas/plantillas/cabecera.php';


// Resto del código...
// Obtener el ID del beneficiario
$beneficiario_id = $_GET['beneficiario_id'] ?? null;

if (!$beneficiario_id) {
    header('Location: '.$_SESSION['DIR_HOME'].'vistas/beneficiarios/listado.php?error=No se especificó el beneficiario');
    exit();
}

// Obtener datos del beneficiario
$pdo=conexion_pdo();
$stmt = $pdo->prepare("SELECT nombre, apellido1, apellido2 FROM beneficiarios WHERE id = ?");
$stmt->execute([$beneficiario_id]);
$beneficiario = $stmt->fetch();

if (!$beneficiario) {
    header('Location: '.$_SESSION['DIR_HOME'].'vistas/beneficiarios/listado.php?error=Beneficiario no encontrado');
    exit();
}

?>

    <main>
        <div class="form-container">
            <h2>Registrar Llamada</h2>
            <h3>Beneficiario: <?php echo htmlspecialchars($beneficiario['nombre'] . ' ' . $beneficiario['apellido1'] . ' ' . $beneficiario['apellido2']); ?></h3>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <form action="<?=$_SESSION['DIR_HOME']?>controladores/ComunicacionControlador.php" method="POST" class="comunicacion-form">
                <input type="hidden" name="beneficiario_id" value="<?php echo $beneficiario_id; ?>">

                <div class="form-group">
                    <label for="tipo">Tipo de Llamada:</label>
                    <select id="tipo" name="tipo" required>
                        <option value="entrante">ENTRANTE</option>
                        <option value="saliente">SALIENTE</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="categoria">Categoría:</label>
                    <select id="categoria" name="categoria" required>
                        <option value="">Seleccione...</option>
                        <?php
                        $stmt = $pdo->query("SELECT id, descripcion FROM comunicacion_categoria");
                        while ($row = $stmt->fetch()) {
                            echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['descripcion']) . '</option>';
                        }
                        ?>
                    </select>
                </div>


                <div class="form-group">
                    <label for="motivo">Motivo de la llamada:</label>
                    <textarea id="motivo" name="motivo" rows="4" required></textarea>
                </div>

                <div class="form-group">
                    <label for="resolucion">Resolución:</label>
                    <textarea id="resolucion" name="resolucion" rows="4"></textarea>
                </div>


                <div class="form-buttons">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Guardar Comunicación
                    </button>
                    <a href="<?=$_SESSION['DIR_HOME']?>vistas/beneficiarios/ver.php?id=<?php echo $beneficiario_id; ?>" class="btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </main>

<?php require_once $_SESSION['DIR_FULL']. '/vistas/plantillas/pie.php';?>