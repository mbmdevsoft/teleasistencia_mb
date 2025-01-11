<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once $_SESSION['DIR_FULL'] . 'config/conexion.php';
require_once $_SESSION['DIR_FULL'] . 'vistas/plantillas/cabecera.php';



$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: ' . $_SESSION['DIR_FULL'] . 'vistas/beneficiarios/listado.php?error=No se especificó el beneficiario');
    exit();
}

// Obtener datos del beneficiario
$pdo = conexion_pdo();
$stmt = $pdo->prepare("SELECT b.*, eb.descripcion as estado_nombre 
    FROM beneficiarios b 
    LEFT JOIN beneficiario_estado eb ON b.estado_id = eb.id 
    WHERE b.id = ?");
$stmt->execute([$id]);
$beneficiario = $stmt->fetch();

if (!$beneficiario) {
    header('Location: ' . $_SESSION['DIR_FULL'] . 'vistas/beneficiarios/listado.php?error=Beneficiario no encontrado');
    exit();
}

// Obtener contactos del beneficiario
$stmt = $pdo->prepare("
    SELECT c.*,cp.descripcion as parentesco
    FROM contactos c
    LEFT JOIN contacto_parentesco cp ON c.parentesco_id = cp.id
    WHERE c.beneficiario_id = ? ");
$stmt->execute([$id]);
$contactos = $stmt->fetchAll();

// Obtener comunicaciones
$stmt = $pdo->prepare("
    SELECT c.*,  
           cc.descripcion as categoria_nombre
    FROM comunicaciones c
    LEFT JOIN comunicacion_categoria cc ON c.categoria_id = cc.id
    WHERE c.beneficiario_id = ?
    ORDER BY c.fecha_creacion DESC
    LIMIT 10");
$stmt->execute([$id]);
$comunicaciones = $stmt->fetchAll();

// Obtener agendas
$stmt = $pdo->prepare("
    SELECT a.*, 
           ta.descripcion as tipo_descripcion,
           ea.descripcion as estado_descripcion
    FROM agendas a
    LEFT JOIN agenda_tipo ta ON a.tipo_id = ta.id
    LEFT JOIN agenda_estado ea ON a.estado_id = ea.id
    WHERE a.beneficiario_id = ?
    AND a.fecha_programada >= CURDATE()
    ORDER BY a.fecha_programada ASC
    LIMIT 10");
$stmt->execute([$id]);
$agendas = $stmt->fetchAll();
?>

<main>
    <div class="container">
        <div class="module-header">
            <h2>Detalles del Beneficiario</h2>


            <!-- Información Personal -->
            <div class="info-section">
                <div class="section-header">
                    <div>
                        <i class="fas fa-user"></i> Información Personal
                    </div>
                    <a href="<?= $_SESSION['DIR_HOME'] ?>vistas/beneficiarios/editar.php?id=<?php echo $id; ?>"
                        class="btn-edit" title="Editar Informacion Personal">
                        <i class="fas fa-edit"></i>
                    </a>
                </div>

                <div class="info-content">
                    <div class="info-grid">
                        <div class="info-field">
                            <div class="field-label">Expediente:</div>
                            <div class="field-value"><?php echo htmlspecialchars($beneficiario['numero_expediente']); ?>
                            </div>
                        </div>
                        <div class="info-field">
                            <div class="field-label">NIF/NIE:</div>
                            <div class="field-value"><?php echo htmlspecialchars($beneficiario['nif_nie'] ?? '-'); ?>
                            </div>
                        </div>
                        <div class="info-field">
                            <div class="field-label">Nombre Completo:</div>
                            <div class="field-value">
                                <?php echo htmlspecialchars($beneficiario['nombre'] . ' ' .
                                    $beneficiario['apellido1'] . ' ' .
                                    ($beneficiario['apellido2'] ?? '')); ?>
                            </div>
                        </div>
                        <div class="info-field">
                            <div class="field-label">Fecha Nacimiento:</div>
                            <div class="field-value">
                                <?php echo date('d/m/Y', strtotime($beneficiario['fecha_nacimiento'])); ?>
                            </div>
                        </div>
                        <div class="info-field">
                            <div class="field-label">Género:</div>
                            <div class="field-value"><?php echo $beneficiario['genero'] === 'H' ? 'Hombre' : 'Mujer'; ?>
                            </div>
                        </div>
                        <div class="info-field">
                            <div class="field-label">Estado:</div>
                            <div class="field-value"><?php echo htmlspecialchars($beneficiario['estado_nombre']); ?>
                            </div>
                        </div>
                        <div class="info-field">
                            <div class="field-label">Dirección:</div>
                            <div class="field-value">
                                <?php
                                echo htmlspecialchars($beneficiario['direccion']) . '<br>';
                                echo htmlspecialchars($beneficiario['codigo_postal'] . ' ' .
                                    $beneficiario['poblacion'] . ', ' .
                                    $beneficiario['provincia']);
                                ?>
                            </div>
                        </div>
                        <div class="info-field">
                            <div class="field-label">Teléfonos:</div>
                            <div class="field-value">
                                <?php
                                if (!empty($beneficiario['telefono_fijo'])) {
                                    echo 'Fijo: <a href="tel:' . htmlspecialchars($beneficiario['telefono_fijo']) . '" class="phone-link">' .
                                        htmlspecialchars($beneficiario['telefono_fijo']) . '</a><br>';
                                }
                                if (!empty($beneficiario['telefono_movil'])) {
                                    echo 'Móvil: <a href="tel:' . htmlspecialchars($beneficiario['telefono_movil']) . ' " class="phone-link">' .
                                        htmlspecialchars($beneficiario['telefono_movil']) . '</a>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Datos de Salud -->
            <div class="info-section">
                <div class="section-header">
                    <div>
                        <i class="fas fa-heartbeat"></i> Datos de Salud
                    </div>
                    <a href="<?= $_SESSION['DIR_HOME'] ?>vistas/beneficiarios/editar_salud.php?id=<?php echo $id; ?>"
                        class="btn-edit" title="Editar Datos de Salud">
                        <i class="fas fa-edit"></i>
                    </a>

                </div>
                <div class="info-content">
                    <div class="info-grid">
                        <?php if (!empty($beneficiario['enfermedades'])): ?>
                            <div class="info-field">
                                <div class="field-label">Enfermedades:</div>
                                <div class="field-value">
                                    <?php echo nl2br(htmlspecialchars($beneficiario['enfermedades'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($beneficiario['alergias'])): ?>
                            <div class="info-field">
                                <div class="field-label">Alergias:</div>
                                <div class="field-value"><?php echo nl2br(htmlspecialchars($beneficiario['alergias'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($beneficiario['medicacion'])): ?>
                            <div class="info-field">
                                <div class="field-label">Medicación Actual:</div>
                                <div class="field-value"><?php echo nl2br(htmlspecialchars($beneficiario['medicacion'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($beneficiario['intervenciones'])): ?>
                            <div class="info-field">
                                <div class="field-label">Intervenciones Quirúrgicas:</div>
                                <div class="field-value">
                                    <?php echo nl2br(htmlspecialchars($beneficiario['intervenciones'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($beneficiario['dieta'])): ?>
                            <div class="info-field">
                                <div class="field-label">Dieta Especial:</div>
                                <div class="field-value"><?php echo nl2br(htmlspecialchars($beneficiario['dieta'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($beneficiario['otras'])): ?>
                            <div class="info-field">
                                <div class="field-label">Otras:</div>
                                <div class="field-value"><?php echo nl2br(htmlspecialchars($beneficiario['otras'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>

                </div>
            </div>

            <!-- Contactos -->
            <div class="info-section" id="contactos">
                <div class="section-header">
                    <div>
                        <i class="fas fa-address-book"></i> Contactos
                    </div>
                    <a href="<?= $_SESSION['DIR_HOME'] ?>vistas/contactos/crear.php?beneficiario_id=<?php echo $id; ?>"
                        class="btn-edit" title="Añadir contacto">
                        <i class="fas fa-user-plus"></i>
                    </a>

                </div>
                <div class="table-content">
                    <?php if (!empty($contactos)): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Parentesco</th>
                                    <th>Prioridad</th>
                                    <th>Teléfonos</th>
                                    <th>¿Tiene Llave?</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contactos as $contacto): ?>
                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars($contacto['nombre'] . ' ' . $contacto['apellidos']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($contacto['parentesco']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($contacto['orden_prioridad']); ?>
                                        </td>
                                        <td>
                                            <?php
                                            if ($contacto['telefono_fijo'])
                                                echo 'Fijo: <a href="tel:' . htmlspecialchars($contacto['telefono_fijo']) . '" class="phone-link">' .
                                                    htmlspecialchars($contacto['telefono_fijo']) . '</a><br>';
                                            if ($contacto['telefono_movil'])
                                                echo 'Móvil: <a href="tel:' . htmlspecialchars($contacto['telefono_movil']) . '" class="phone-link">' .
                                                    htmlspecialchars($contacto['telefono_movil']) . '</a><br>';
                                            if ($contacto['telefono_trabajo'])
                                                echo 'Móvil: <a href="tel:' . htmlspecialchars($contacto['telefono_movil']) . '" class="phone-link">' .
                                                    htmlspecialchars($contacto['telefono_movil']) . '</a><br>';
                                            ?>
                                        </td>
                                        <td><?php echo $contacto['tiene_llave'] ? 'Sí' : 'No'; ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="<?= $_SESSION['DIR_HOME'] ?>vistas/contactos/editar.php?id=<?php echo $contacto['id']; ?>"
                                                    class="btn-edit" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <script>
                                                    function eliminarContacto(id, beneficiarioId, nombre, nifnie) {
                                                        Swal.fire({
                                                            title: '¿Eliminar contacto?',
                                                            html: `¿Está seguro de que desea eliminar el contacto <strong>${nombre}</strong>?<br>Direccion: ${nifnie}`,
                                                            icon: 'warning',
                                                            showCancelButton: true,
                                                            confirmButtonColor: '#ef5a64',
                                                            cancelButtonColor: '#6c757d',
                                                            confirmButtonText: 'Sí, eliminar',
                                                            cancelButtonText: 'Cancelar'
                                                        }).then((result) => {
                                                            if (result.isConfirmed) {
                                                                fetch(<?= $_SESSION['DIR_HOME'] ?> + 'controladores/ContactoControlador.php?action=eliminar&id=' + id)
                                                                    .then(response => {
                                                                        if (!response.ok) {
                                                                            throw new Error('Error en la respuesta del servidor');
                                                                        }
                                                                        return response.json();
                                                                    })
                                                                    .then(data => {
                                                                        if (data.success) {
                                                                            Swal.fire({
                                                                                title: '¡Eliminado!',
                                                                                html: `El contacto <strong>${nombre}</strong> ha sido eliminado correctamente`,
                                                                                icon: 'success',
                                                                                confirmButtonColor: '#ef5a64'
                                                                            }).then(() => {
                                                                                window.location.reload();
                                                                            });
                                                                        } else {
                                                                            Swal.fire({
                                                                                title: 'Error',
                                                                                text: 'No se pudo eliminar el contacto: ' + (data.error || 'Error desconocido'),
                                                                                icon: 'error',
                                                                                confirmButtonColor: '#ef5a64'
                                                                            });
                                                                        }
                                                                    })
                                                                    .catch(error => {
                                                                        console.error('Error:', error);
                                                                        Swal.fire({
                                                                            title: 'Error',
                                                                            text: 'No se pudo eliminar el contacto',
                                                                            icon: 'error',
                                                                            confirmButtonColor: '#ef5a64'
                                                                        });
                                                                    });
                                                            }
                                                        });
                                                    }
                                                </script>

                                                <a href="#"
                                                    onclick="eliminarContacto(<?php echo $contacto['id']; ?>, <?php echo $id; ?>,
                                                    '<?php echo htmlspecialchars(str_replace("'", "\\'", $contacto['nombre'] . ' ' . $contacto['apellidos']), ENT_QUOTES); ?>',
                                                    '<?php echo htmlspecialchars(str_replace("'", "\\'", $contacto['direccion'] ?? ''), ENT_QUOTES); ?>')"
                                                    class="btn-delete" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </a>

                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="no-data">No hay contactos registrados</p>
                    <?php endif; ?>
                </div>
            </div>


            <!-- Agendas Próximas -->
            <div class="info-section">
                <div class="section-header">
                    <div>
                        <i class="fas fa-calendar"></i> Próximas Agendas
                    </div>
                    <a href="<?= $_SESSION['DIR_HOME'] ?>vistas/agendas/crear.php?beneficiario_id=<?php echo $id; ?>"
                        class="btn-edit" title="Nueva agenda">
                        <i class="fas fa-calendar-plus"></i>
                    </a>
                </div>
                <div class="table-content">
                    <?php if (!empty($agendas)): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($agendas as $agenda): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y H:i', strtotime($agenda['fecha_programada'])); ?></td>
                                        <td><?php echo htmlspecialchars($agenda['tipo_descripcion']); ?></td>
                                        <td><?php echo htmlspecialchars($agenda['descripcion']); ?></td>
                                        <td><?php echo htmlspecialchars($agenda['estado_descripcion']); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="<?= $_SESSION['DIR_HOME'] ?>vistas/agendas/editar.php?id=<?php echo $agenda['id']; ?>"
                                                    class="btn-edit" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <script>
                                                    function eliminarAgenda(id, descripcion) {
                                                        Swal.fire({
                                                            title: '¿Eliminar agenda?',
                                                            html: `¿Está seguro de que desea eliminar la agenda?<br><strong>${descripcion}</strong>`,
                                                            icon: 'warning',
                                                            showCancelButton: true,
                                                            confirmButtonColor: '#ef5a64',
                                                            cancelButtonColor: '#6c757d',
                                                            confirmButtonText: 'Sí, eliminar',
                                                            cancelButtonText: 'Cancelar'
                                                        }).then((result) => {
                                                            if (result.isConfirmed) {
                                                                fetch(<?= $_SESSION['DIR_HOME'] ?> + 'controladores/AgendaControlador.php?action=eliminar&id=' + id)
                                                                    .then(response => response.json())
                                                                    .then(data => {
                                                                        if (data.success) {
                                                                            Swal.fire({
                                                                                title: '¡Eliminada!',
                                                                                text: 'La agenda ha sido eliminada correctamente',
                                                                                icon: 'success',
                                                                                confirmButtonColor: '#ef5a64'
                                                                            }).then(() => {
                                                                                window.location.reload();
                                                                            });
                                                                        } else {
                                                                            Swal.fire({
                                                                                title: 'Error',
                                                                                text: 'No se pudo eliminar la agenda: ' + (data.error || 'Error desconocido'),
                                                                                icon: 'error',
                                                                                confirmButtonColor: '#ef5a64'
                                                                            });
                                                                        }
                                                                    })
                                                                    .catch(error => {
                                                                        console.error('Error:', error);
                                                                        Swal.fire({
                                                                            title: 'Error',
                                                                            text: 'No se pudo eliminar la agenda',
                                                                            icon: 'error',
                                                                            confirmButtonColor: '#ef5a64'
                                                                        });
                                                                    });
                                                            }
                                                        });
                                                    }
                                                </script>

                                                <a href="#"
                                                    onclick="eliminarAgenda(<?php echo $agenda['id']; ?>, '<?php echo htmlspecialchars($agenda['descripcion']); ?>')"
                                                    class="btn-delete" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </a>

                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                    <?php else: ?>
                        <p class="no-data">No hay agendas programadas</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Comunicaciones Recientes -->
            <div class="info-section">
                <div class="section-header">
                    <div>
                        <i class="fas fa-phone"></i> Llamadas Recientes
                    </div>
                    <a href="<?= $_SESSION['DIR_HOME'] ?>vistas/comunicaciones/crear.php?beneficiario_id=<?php echo $id; ?>"
                        class="btn-edit" title="Nueva comunicacion">
                        <i class="fas fa-phone"></i>+
                    </a>

                </div>
                <div class="table-content">
                    <?php if (!empty($comunicaciones)): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Categoría</th>
                                    <th>Motivo</th>
                                    <th>Resolucion</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($comunicaciones as $comunicacion): ?>
                                    <tr>
                                        <td>
                                            <?php echo date('d/m/Y H:i', strtotime($comunicacion['fecha_creacion'])); ?>
                                        </td>
                                        <td>
                                            <?php echo ucfirst(htmlspecialchars($comunicacion['tipo'])); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($comunicacion['categoria_nombre']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars(substr($comunicacion['motivo_llamada'], 0, 100)) . '...'; ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars(substr($comunicacion['resolucion'], 0, 100)) . '...'; ?>
                                        </td>
                                        <td>

                                            <div class="action-buttons">
                                                <a href="<?= $_SESSION['DIR_HOME'] ?>vistas/comunicaciones/editar.php?id=<?php echo $comunicacion['id']; ?>"
                                                    class="btn-edit" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <script>
                                                    function eliminarComunicacion(id, descripcion) {
                                                        Swal.fire({
                                                            title: '¿Eliminar comunicación?',
                                                            html: `¿Está seguro de que desea eliminar la comunicación?<br><strong>${descripcion}</strong>`,
                                                            icon: 'warning',
                                                            showCancelButton: true,
                                                            confirmButtonColor: '#ef5a64',
                                                            cancelButtonColor: '#6c757d',
                                                            confirmButtonText: 'Sí, eliminar',
                                                            cancelButtonText: 'Cancelar'
                                                        }).then((result) => {
                                                            if (result.isConfirmed) {
                                                                fetch(<?= $_SESSION['DIR_HOME'] ?> + 'controladores/ComunicacionControlador.php?action=eliminar&id=' + id)
                                                                    .then(response => response.json())
                                                                    .then(data => {
                                                                        if (data.success) {
                                                                            Swal.fire({
                                                                                title: '¡Eliminada!',
                                                                                text: 'La comunicación ha sido eliminada correctamente',
                                                                                icon: 'success',
                                                                                confirmButtonColor: '#ef5a64'
                                                                            }).then(() => {
                                                                                window.location.reload();
                                                                            });
                                                                        } else {
                                                                            Swal.fire({
                                                                                title: 'Error',
                                                                                text: 'No se pudo eliminar la comunicación: ' + (data.error || 'Error desconocido'),
                                                                                icon: 'error',
                                                                                confirmButtonColor: '#ef5a64'
                                                                            });
                                                                        }
                                                                    })
                                                                    .catch(error => {
                                                                        console.error('Error:', error);
                                                                        Swal.fire({
                                                                            title: 'Error',
                                                                            text: 'No se pudo eliminar la comunicación',
                                                                            icon: 'error',
                                                                            confirmButtonColor: '#ef5a64'
                                                                        });
                                                                    });
                                                            }
                                                        });
                                                    }
                                                </script>

                                                <a href="#"
                                                    onclick="eliminarComunicacion(<?php echo $comunicacion['id']; ?>,'<?php echo htmlspecialchars($comunicacion['motivo_llamada']); ?>')"
                                                    class="btn-delete" title="Eliminar Comunicación">
                                                    <i class="fas fa-trash"></i>
                                                </a>


                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="no-data">No hay comunicaciones registradas</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
</main>


<script>
    // Agregar estilos dinámicos para los estados de comunicación
    document.addEventListener('DOMContentLoaded', function () {
        const estados = document.querySelectorAll('.estado-comunicacion');
        estados.forEach(estado => {
            const tipo = estado.classList[1].split('-')[1];
            switch (tipo) {
                case 'abierta':
                    estado.style.backgroundColor = 'var(--warning-color)';
                    break;
                case 'cerrada':
                    estado.style.backgroundColor = 'var(--success-color)';
                    break;
                case 'en_seguimiento':
                    estado.style.backgroundColor = 'var(--info-color)';
                    break;
            }
        });
    });
</script>

<?php require_once $_SESSION['DIR_FULL'] . 'vistas/plantillas/pie.php'; ?>