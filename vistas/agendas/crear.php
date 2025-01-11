<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once $_SESSION['DIR_FULL'] .'config/conexion.php';
require_once $_SESSION['DIR_FULL'] .'vistas/plantillas/cabecera.php';

// Obtener el ID del beneficiario
$beneficiario_id = $_GET['beneficiario_id'] ?? null;

if (!$beneficiario_id) {
    header('Location: ' .$_SESSION['DIR_HOME']. 'vistas/beneficiarios/listado.php?error=No se especificó el beneficiario');
    exit();
}

// Obtener datos del beneficiario
$pdo = conexion_pdo();
$stmt = $pdo->prepare("SELECT nombre, apellido1, apellido2 FROM beneficiarios WHERE id = ?");
$stmt->execute([$beneficiario_id]);
$beneficiario = $stmt->fetch();

if (!$beneficiario) {
    header('Location: ' .$_SESSION['DIR_HOME']. 'vistas/beneficiarios/listado.php?error=Beneficiario no encontrado');
    exit();
}
?>

    <main>
        <div class="form-container">
            <h2>Nueva Agenda</h2>
            <h3>
                Beneficiario: <?php echo htmlspecialchars($beneficiario['nombre'] . ' ' . $beneficiario['apellido1'] . ' ' . $beneficiario['apellido2']); ?></h3>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <form action="<?=$_SESSION['DIR_HOME']?>controladores/AgendaControlador.php" method="POST" class="agenda-form">
                <input type="hidden" name="beneficiario_id" value="<?php echo $beneficiario_id; ?>">

                <div class="form-group">
                    <label for="tipo_agenda">Tipo de Agenda:</label>
                    <select id="tipo_agenda" name="tipo_agenda" required>
                        <option value="">Seleccione...</option>
                        <?php
                        $stmt = $pdo->query("SELECT id, descripcion FROM agenda_tipo ORDER BY descripcion");
                        while ($row = $stmt->fetch()) {
                            echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['descripcion']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fecha_programada">Fecha y Hora:</label>
                    <input type="datetime-local" id="fecha_programada" name="fecha_programada" required>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" rows="4" required></textarea>
                </div>

                <div class="form-group">
                    <label for="notas">Notas adicionales:</label>
                    <textarea id="notas" name="notas" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="estado">Estado:</label>
                    <select id="estado" name="estado" required>
                        <?php
                        $stmt = $pdo->query("SELECT id, descripcion FROM agenda_estado ORDER BY id");
                        while ($row = $stmt->fetch()) {
                            echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['descripcion']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-buttons">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Guardar Agenda
                    </button>
                    <a href="<?=$_SESSION['DIR_HOME']?>vistas/beneficiarios/ver.php?id=<?php echo $beneficiario_id; ?>"
                       class="btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </main>

<?php require_once $_SESSION['DIR_FULL'] . '/vistas/plantillas/pie.php';?>