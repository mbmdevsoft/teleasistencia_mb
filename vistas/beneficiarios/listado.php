<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once $_SESSION['DIR_FULL'] . 'config/conexion.php';
require_once $_SESSION['DIR_FULL'] . 'includes/funciones.php';
require_once $_SESSION['DIR_FULL'] . 'vistas/plantillas/cabecera.php';


verificarSesion();
// Consulta para obtener todos los beneficiarios
$pdo = conexion_pdo();
$stmt = $pdo->query("SELECT * FROM beneficiarios ORDER BY apellido1, apellido2, nombre");
$beneficiarios = $stmt->fetchAll();

// Consulta para obtener las descripciones de los estados
$stmt = $pdo->query("SELECT id, descripcion FROM beneficiario_estado");
$estados = [];
while ($row = $stmt->fetch()) {
    $estados[$row['id']] = $row['descripcion'];
}
?>

<main>
    <h2>Listado de Beneficiarios</h2>
    <div class="module-menu">
        <a href="<?= $_SESSION['DIR_HOME'] ?>vistas/beneficiarios/crear.php" class="btn-primary">
            <i class="fas fa-plus"></i> Nuevo Beneficiario
        </a>
    </div>


    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            Operación realizada con éxito
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>

    <div class="search-container">
        <input type="text" id="busqueda" placeholder="Buscar beneficiario..." class="search-input">

    </div>

    <?php if (count($beneficiarios) > 0): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Expediente</th>
                    <th>Nombre</th>
                    <th>Apellidos</th>
                    <th>NIF/NIE</th>
                    <th>Teléfono</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($beneficiarios as $beneficiario): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($beneficiario['numero_expediente']); ?></td>
                        <td><?php echo htmlspecialchars($beneficiario['nombre']); ?></td>
                        <td>
                            <?php
                            echo htmlspecialchars($beneficiario['apellido1'] . ' ' .
                                ($beneficiario['apellido2'] ?? ''));
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($beneficiario['nif_nie'] ?? '-'); ?></td>
                        <td>
                            <?php
                            echo htmlspecialchars(
                                $beneficiario['telefono_movil'] ??
                                $beneficiario['telefono_fijo'] ??
                                '-'
                            );
                            ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($estados[$beneficiario['estado_id']] ?? 'Desconocido'); ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="<?= $_SESSION['DIR_HOME'] ?>vistas/beneficiarios/ver.php?id=<?php echo $beneficiario['id']; ?>"
                                    class="btn-view" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <script>
                                    function eliminarBeneficiario(id, nombre, expediente) {
                                        Swal.fire({
                                            title: '¿Eliminar beneficiario?',
                                            html: `¿Está seguro de que desea eliminar al beneficiario <strong>${nombre}</strong>?<br>
                              Expediente: ${expediente}<br><br>
                              <span style="color: red">Esta acción eliminará todos los datos asociados al beneficiario</span>`,
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonColor: '#ef5a64',
                                            cancelButtonColor: '#6c757d',
                                            confirmButtonText: 'Sí, eliminar',
                                            cancelButtonText: 'Cancelar'
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                fetch(<?= $_SESSION['DIR_HOME'] ?> + 'controladores/BeneficiarioControlador.php', {
                                                    method: 'POST',
                                                    headers: {
                                                        'Content-Type': 'application/x-www-form-urlencoded',
                                                    },
                                                    body: `action=eliminar&id=${id}`
                                                })
                                                    .then(response => response.json())
                                                    .then(data => {
                                                        if (data.success) {
                                                            Swal.fire({
                                                                title: '¡Eliminado!',
                                                                html: `El beneficiario <strong>${nombre}</strong> ha sido eliminado correctamente`,
                                                                icon: 'success',
                                                                confirmButtonColor: '#ef5a64'
                                                            }).then(() => {
                                                                window.location.reload();
                                                            });
                                                        } else {
                                                            Swal.fire({
                                                                title: 'Error',
                                                                text: 'No se pudo eliminar el beneficiario: ' + (data.error || 'Error desconocido'),
                                                                icon: 'error',
                                                                confirmButtonColor: '#ef5a64'
                                                            });
                                                        }
                                                    })
                                                    .catch(error => {
                                                        console.error('Error:', error);
                                                        Swal.fire({
                                                            title: 'Error',
                                                            text: 'No se pudo eliminar el beneficiario',
                                                            icon: 'error',
                                                            confirmButtonColor: '#ef5a64'
                                                        });
                                                    });
                                            }
                                        });
                                    }
                                </script>


                                <button onclick="eliminarBeneficiario(
                                <?php echo $beneficiario['id']; ?>,
                                        '<?php echo htmlspecialchars(str_replace("'", "\\'", $beneficiario['nombre'] . ' ' . $beneficiario['apellido1']), ENT_QUOTES); ?>',
                                        '<?php echo htmlspecialchars(str_replace("'", "\\'", $beneficiario['numero_expediente']), ENT_QUOTES); ?>'
                                        )" class="btn-delete" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>

                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-center">No hay beneficiarios registrados</p>
    <?php endif; ?>
</main>

<script>

    document.getElementById('busqueda').addEventListener('input', function (e) {
        const texto = e.target.value.toLowerCase();
        const filas = document.querySelectorAll('tbody tr');

        filas.forEach(fila => {
            const contenido = fila.textContent.toLowerCase();
            fila.style.display = contenido.includes(texto) ? '' : 'none';
        });
    });
</script>


<?php require_once $_SESSION['DIR_FULL'] . 'vistas/plantillas/pie.php'; ?>