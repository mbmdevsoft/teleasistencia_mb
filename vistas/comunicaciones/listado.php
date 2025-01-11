<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once $_SESSION['DIR_FULL'] . 'config/conexion.php';
require_once $_SESSION['DIR_FULL'] . 'includes/funciones.php';
require_once $_SESSION['DIR_FULL'] . 'vistas/plantillas/cabecera.php';

verificarSesion();

// Obtener todas las comunicaciones
$pdo = conexion_pdo();
$stmt = $pdo->prepare("
    SELECT c.*, 
        cc.descripcion as categoria_descripcion,
           b.nombre as beneficiario_nombre,
           b.apellido1 as beneficiario_apellido1,
           b.apellido2 as beneficiario_apellido2,
           CONCAT(co.nombre, ' ', co.apellidos) as comunicante_nombre
    FROM comunicaciones c
    LEFT JOIN comunicacion_categoria cc ON c.categoria_id = cc.id
    LEFT JOIN beneficiarios b ON c.beneficiario_id = b.id
    LEFT JOIN contactos co ON c.beneficiario_id = co.id
    ORDER BY c.fecha_creacion DESC");
$stmt->execute();
$comunicaciones = $stmt->fetchAll();
?>

    <main>
        <div class="container">
            <div class="module-header">
                <h2>Listado de Llamadas</h2>
                <div class="module-menu">

                </div>
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

            <?php if (!empty($comunicaciones)): ?>
                <table class="table">
                    <thead>
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>Beneficiario</th>
                        <th>Tipo</th>
                        <th>Categoría</th>
                        <th>Motivo</th>
                        <th>Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($comunicaciones as $comunicacion): ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', strtotime($comunicacion['fecha_creacion'])); ?></td>
                            <td><?php echo htmlspecialchars($comunicacion['beneficiario_nombre'] . ' ' .
                                    $comunicacion['beneficiario_apellido1'] . ' ' .
                                    ($comunicacion['beneficiario_apellido2'] ?? '')); ?></td>
                            <td>
                                <?php echo $comunicacion['tipo'] ?>
                            </td>
                            <td>
                                <?php echo $comunicacion['categoria_descripcion'] ?>
                            </td>

                            <td><?php echo htmlspecialchars($comunicacion['motivo_llamada']); ?></td>


                            <td>
                                <div class="action-buttons">
                                    <a href="editar.php?id=<?php echo $comunicacion['id']; ?>&origen=general" class="btn-edit"
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center">No hay comunicaciones registradas</p>
            <?php endif; ?>
        </div>
    </main>


    <?php require_once $_SESSION['DIR_FULL'].'vistas/plantillas/pie.php'; ?>