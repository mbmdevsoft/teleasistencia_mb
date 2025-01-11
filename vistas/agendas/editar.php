<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once $_SESSION['DIR_FULL'] .'config/conexion.php';
require_once $_SESSION['DIR_FULL'] .'vistas/plantillas/cabecera.php';

// Obtener el ID de la agenda
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: '.$_SESSION['DIR_FULL'].'vistas/beneficiarios/listado.php?error=No se especificó la agenda');
    exit();
}

// Obtener datos de la agenda
$pdo=conexion_pdo();
$stmt = $pdo->prepare("
    SELECT a.*, b.nombre, b.apellido1, b.apellido2 
    FROM agendas a
    JOIN beneficiarios b ON a.beneficiario_id = b.id
    WHERE a.id = ?");
$stmt->execute([$id]);
$agenda = $stmt->fetch();

if (!$agenda) {
    header('Location: '.$_SESSION['DIR_FULL'].'vistas/beneficiarios/listado.php?error=Agenda no encontrada');
    exit();
}
?>

<main>
    <div class="form-container">
        <h2>Editar Agenda</h2>
        <h3>Beneficiario: <?php echo htmlspecialchars($agenda['nombre'] . ' ' . $agenda['apellido1'] . ' ' . $agenda['apellido2']); ?></h3>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <form action="<?=$_SESSION['DIR_HOME']?>controladores/AgendaControlador.php" method="POST" class="agenda-form">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <input type="hidden" name="beneficiario_id" value="<?php echo $agenda['beneficiario_id']; ?>">

            <div class="form-group">
                <label for="tipo_agenda">Tipo de Agenda:</label>
                <select id="tipo_agenda" name="tipo_agenda" required>
                    <?php
                    $stmt = $pdo->query("SELECT id, descripcion FROM agenda_tipo ORDER BY descripcion");
                    while ($row = $stmt->fetch()) {
                        $selected = $row['id'] == $agenda['tipo_id'] ? 'selected' : '';
                        echo '<option value="' . $row['id'] . '" ' . $selected . '>' .
                            htmlspecialchars($row['descripcion']) . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="fecha_programada">Fecha y Hora:</label>
                <input type="datetime-local" id="fecha_programada" name="fecha_programada"
                       value="<?php echo date('Y-m-d\TH:i', strtotime($agenda['fecha_programada'])); ?>" required>
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion" rows="4" required><?php
                    echo htmlspecialchars($agenda['descripcion']);
                    ?></textarea>
            </div>

            <div class="form-group">
                <label for="notas">Notas adicionales:</label>
                <textarea id="notas" name="notas" rows="3"><?php
                    echo htmlspecialchars($agenda['notas'] ?? '');
                    ?></textarea>
            </div>

            <div class="form-group">
                <label for="estado">Estado:</label>
                <select id="estado" name="estado" required>
                    <?php
                    $stmt = $pdo->query("SELECT id, descripcion FROM agenda_estado ORDER BY id");
                    while ($row = $stmt->fetch()) {
                        $selected = $row['id'] == $agenda['estado_id'] ? 'selected' : '';
                        echo '<option value="' . $row['id'] . '" ' . $selected . '>' .
                            htmlspecialchars($row['descripcion']) . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="form-buttons">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
                <a href="<?=$_SESSION['DIR_HOME']?>vistas/beneficiarios/ver.php?id=<?php echo $agenda['beneficiario_id']; ?>" class="btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</main>

<?php require_once $_SESSION['DIR_FULL'] . '/vistas/plantillas/pie.php';?>
