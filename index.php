<?php
// Inicializa las variables de directorio
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    $_SESSION['DIR_HOME'] = '/teleasistencia_mb/';    /**     <?=$_SESSION['DIR_HOME']?>     */
    $_SESSION['DIR_FULL'] = $_SERVER['DOCUMENT_ROOT'] . $_SESSION['DIR_HOME'];

}
require_once $_SESSION['DIR_FULL'] . 'config/conexion.php';
require_once $_SESSION['DIR_FULL'] . 'includes/funciones.php';
require_once $_SESSION['DIR_FULL'] . 'vistas/plantillas/cabecera.php';

// Verifica si existe un ID de usuario en la sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . $_SESSION['DIR_HOME'] . 'vistas/usuarios/iniciar-sesion.php');
    exit();
}




verificarSesion();

// Consulta para obtener comunicaciones de emergencia
$pdo = conexion_pdo();

// TOTALES
$stmt = $pdo->query("
   SELECT COUNT(*) as total
   FROM beneficiarios c");
$totalBeneficiarios = $stmt->fetch()['total'];
$stmt = $pdo->query("
   SELECT COUNT(*) as total
   FROM contactos c");
$totalContactos = $stmt->fetch()['total'];
$stmt = $pdo->query("
   SELECT COUNT(*) as total
   FROM comunicaciones c");
$totalComunicaciones = $stmt->fetch()['total'];
$stmt = $pdo->query("
   SELECT COUNT(*) as total
   FROM agendas c");
$totalAgendas = $stmt->fetch()['total'];

// AGENDA AL DIA DE HOY (totales)
$stmt = $pdo->query("
   SELECT COUNT(*) as total
   FROM agendas a
   WHERE a.tipo_id= 1  AND DATE(a.fecha_programada) = CURDATE()");
$hoyAgenda1t = $stmt->fetch()['total'];
$stmt = $pdo->query("
   SELECT COUNT(*) as total
   FROM agendas a
   WHERE a.tipo_id= 2  AND DATE(a.fecha_programada) = CURDATE()");
$hoyAgenda2t = $stmt->fetch()['total'];
$stmt = $pdo->query("
   SELECT COUNT(*) as total
   FROM agendas a
   WHERE a.tipo_id= 3  AND DATE(a.fecha_programada) = CURDATE()");
$hoyAgenda3t = $stmt->fetch()['total'];
$stmt = $pdo->query("
   SELECT COUNT(*) as total
   FROM agendas a
   WHERE a.tipo_id= 4  AND DATE(a.fecha_programada) = CURDATE()");
$hoyAgenda4t = $stmt->fetch()['total'];
// AGENDA AL DIA DE HOY (pendientes)
$stmt = $pdo->query("
   SELECT COUNT(*) as total
   FROM agendas a
   WHERE a.tipo_id= 1 AND a.estado_id=1 AND DATE(a.fecha_programada) = CURDATE()");
$hoyAgenda1 = $stmt->fetch()['total'];
$stmt = $pdo->query("
   SELECT COUNT(*) as total
   FROM agendas a
   WHERE a.tipo_id= 2 AND a.estado_id=1 AND DATE(a.fecha_programada) = CURDATE()");
$hoyAgenda2 = $stmt->fetch()['total'];
$stmt = $pdo->query("
   SELECT COUNT(*) as total
   FROM agendas a
   WHERE a.tipo_id= 3 AND a.estado_id=1 AND DATE(a.fecha_programada) = CURDATE()");
$hoyAgenda3 = $stmt->fetch()['total'];
$stmt = $pdo->query("
   SELECT COUNT(*) as total
   FROM agendas a
   WHERE a.tipo_id= 4 AND a.estado_id=1 AND DATE(a.fecha_programada) = CURDATE()");
$hoyAgenda4 = $stmt->fetch()['total'];

// LLAMADAS AL DIA DE HOY (ENTRANTES)
$stmt = $pdo->query("
   SELECT COUNT(*) as total
   FROM comunicaciones c
   WHERE c.categoria_id= 1 AND c.tipo='ENTRANTE' AND DATE(c.fecha_creacion) = CURDATE()");
$hoyComunicacion1E = $stmt->fetch()['total'];
$stmt = $pdo->query("
   SELECT COUNT(*) as total
   FROM comunicaciones c
   WHERE c.categoria_id= 2 AND c.tipo='ENTRANTE' AND DATE(c.fecha_creacion) = CURDATE()");
$hoyComunicacion2E = $stmt->fetch()['total'];
$stmt = $pdo->query("
   SELECT COUNT(*) as total
   FROM comunicaciones c
   WHERE c.categoria_id= 3 AND c.tipo='ENTRANTE' AND DATE(c.fecha_creacion) = CURDATE()");
$hoyComunicacion3E = $stmt->fetch()['total'];
$stmt = $pdo->query("
   SELECT COUNT(*) as total
   FROM comunicaciones c
   WHERE c.categoria_id= 4 AND c.tipo='ENTRANTE' AND DATE(c.fecha_creacion) = CURDATE()");
$hoyComunicacion4E = $stmt->fetch()['total'];
// LLAMADAS AL DIA DE HOY (SALIENTES)
$stmt = $pdo->query("
   SELECT COUNT(*) as total
   FROM comunicaciones c
   WHERE c.categoria_id= 1 AND c.tipo='SALIENTE' AND DATE(c.fecha_creacion) = CURDATE()");
$hoyComunicacion1S = $stmt->fetch()['total'];
$stmt = $pdo->query("
   SELECT COUNT(*) as total
   FROM comunicaciones c
   WHERE c.categoria_id= 2 AND c.tipo='SALIENTE' AND DATE(c.fecha_creacion) = CURDATE()");
$hoyComunicacion2S = $stmt->fetch()['total'];
$stmt = $pdo->query("
   SELECT COUNT(*) as total
   FROM comunicaciones c
   WHERE c.categoria_id= 3 AND c.tipo='SALIENTE' AND DATE(c.fecha_creacion) = CURDATE()");
$hoyComunicacion3S = $stmt->fetch()['total'];
$stmt = $pdo->query("
   SELECT COUNT(*) as total
   FROM comunicaciones c
   WHERE c.categoria_id= 4 AND c.tipo='SALIENTE' AND DATE(c.fecha_creacion) = CURDATE()");
$hoyComunicacion4S = $stmt->fetch()['total'];



?>
<main>
    <!-- Panel principal con widgets de estadísticas -->
    <section class="dashboard">
        <!-- Widget de Beneficiarios -->
        <div class="widget">
            <h3>General</h3>
            <div class="stats">
                <div class="stat-line">
                    <span class="stat-label">Total beneficiarios:</span>
                    <span class="stat-value"> <?= $totalBeneficiarios ?> </span>
                </div>
                <div class="stat-line">
                    <span class="stat-label">Total contactos:</span>
                    <span class="stat-value"> <?= $totalContactos ?> </span>
                </div>
                <div class="stat-line">
                    <span class="stat-label">Total llamadas:</span>
                    <span class="stat-value"> <?= $totalComunicaciones ?> </span>
                </div>
                <div class="stat-line">
                    <span class="stat-label">Total agendas:</span>
                    <span class="stat-value"> <?= $totalAgendas ?> </span>
                </div>
            </div>

        </div>

        <!-- Widget de Agenda -->
        <div class="widget">
            <h3>Agenda pendiente para hoy</h3>
            <div class="stats">
                <!-- Citas pendientes del día -->
                <div class="stat-line">
                    <span class="stat-label">Medica:</span>
                    <span class="stat-value"> <?= $hoyAgenda1 ?> de <?= $hoyAgenda1t ?></span>
                </div>
                <div class="stat-line">
                    <span class="stat-label">Social:</span>
                    <span class="stat-value"> <?= $hoyAgenda2 ?> de <?= $hoyAgenda2t ?></span>
                </div>
                <div class="stat-line">
                    <span class="stat-label">Recordatorio:</span>
                    <span class="stat-value"> <?= $hoyAgenda3 ?> de <?= $hoyAgenda3t ?></span>
                </div>
                <div class="stat-line">
                    <span class="stat-label">Otros:</span>
                    <span class="stat-value"> <?= $hoyAgenda4 ?> de <?= $hoyAgenda4t ?></span>
                </div>


            </div>

        </div>

        <!-- Widget de Comunicaciones -->
        <div class="widget">
            <h3>Llamadas de hoy (ent./sal.)</h3>
            <div class="stats">
                <div class="stat-line">
                    <span class="stat-label">Informativas:</span>
                    <span class="stat-value"> <?= $hoyComunicacion1E ?> / <?= $hoyComunicacion1S ?> </span>
                </div>
                <div class="stat-line">
                    <span class="stat-label">De emergencia:</span>
                    <span id="com-emergencias" class="stat-value"> <?= $hoyComunicacion2E ?> / <?= $hoyComunicacion2S ?>
                    </span>
                </div>
                <div class="stat-line">
                    <span class="stat-label">Sugerencias / Reclamaciones:</span>
                    <span id="com-emergencias" class="stat-value"> <?= $hoyComunicacion3E ?> / <?= $hoyComunicacion3S ?>
                    </span>
                </div>

                <div class="stat-line">
                    <span class="stat-label">Agendadas:</span>
                    <span id="com-emergencias" class="stat-value"> <?= $hoyComunicacion4E ?> / <?= $hoyComunicacion4S ?>
                    </span>
                </div>

            </div>

        </div>
    </section>
</main>


<?php require_once $_SESSION['DIR_FULL'] . '/vistas/plantillas/pie.php'; ?>