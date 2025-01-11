<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once $_SESSION['DIR_FULL'] . 'config/conexion.php';
require_once $_SESSION['DIR_FULL'] . 'includes/funciones.php';
require_once $_SESSION['DIR_FULL'] . 'vistas/plantillas/cabecera.php';

verificarSesion();

// Obtener todos los beneficiarios activos
$pdo=conexion_pdo();
$stmt = $pdo->query("
    SELECT id, CONCAT(nombre, ' ', apellido1, ' ', COALESCE(apellido2, '')) as nombre_completo 
    FROM beneficiarios 
    WHERE estado_id = 1  
    ORDER BY apellido1, apellido2, nombre");
$beneficiarios = $stmt->fetchAll();

// Obtener el beneficiario seleccionado si existe
$beneficiario_id = $_GET['beneficiario_id'] ?? null;

// Obtener el mes y año actual o los proporcionados
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m');
$anio = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)date('Y');

// Obtener eventos del mes
$primer_dia = "$anio-$mes-01";
$ultimo_dia = date('Y-m-t', strtotime($primer_dia));

$sql = "SELECT a.*, 
               ta.descripcion as tipo_descripcion,
               CONCAT(b.nombre, ' ', b.apellido1) as beneficiario_nombre
        FROM agendas a
        LEFT JOIN agenda_tipo ta ON a.tipo_id = ta.id
        LEFT JOIN beneficiarios b ON a.beneficiario_id = b.id
        WHERE DATE(a.fecha_programada) BETWEEN ? AND ?";
$params = [$primer_dia, $ultimo_dia];

if ($beneficiario_id) {
    $sql .= " AND a.beneficiario_id = ?";
    $params[] = $beneficiario_id;
}

$sql .= " ORDER BY a.fecha_programada";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$eventos = $stmt->fetchAll();

// Organizar eventos por día
$eventos_por_dia = [];
foreach ($eventos as $evento) {
    $dia = date('j', strtotime($evento['fecha_programada']));
    if (!isset($eventos_por_dia[$dia])) {
        $eventos_por_dia[$dia] = [];
    }
    $eventos_por_dia[$dia][] = $evento;
}

// Obtener número de días del mes
$num_dias = date('t', strtotime($primer_dia));
// Obtener día de la semana del primer día (0-6)
$primer_dia_semana = date('w', strtotime($primer_dia));

$meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
?>

<main>
    <div class="container">
        <div class="module-header">
            <h2>Calendario de Agendas</h2>

            <div class="filters">
                <select id="beneficiario_select" class="form-control" onchange="cambiarBeneficiario(this.value)">
                    <option value="">Todos los beneficiarios</option>
                    <?php foreach ($beneficiarios as $beneficiario): ?>
                        <option value="<?php echo $beneficiario['id']; ?>"
                            <?php echo $beneficiario_id == $beneficiario['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($beneficiario['nombre_completo']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="calendar-nav">
                <?php
                $mes_anterior = $mes - 1;
                $anio_anterior = $anio;
                if ($mes_anterior < 1) {
                    $mes_anterior = 12;
                    $anio_anterior--;
                }

                $mes_siguiente = $mes + 1;
                $anio_siguiente = $anio;
                if ($mes_siguiente > 12) {
                    $mes_siguiente = 1;
                    $anio_siguiente++;
                }
                ?>
                <a href="?<?php echo http_build_query(['mes' => $mes_anterior, 'anio' => $anio_anterior, 'beneficiario_id' => $beneficiario_id]); ?>" class="btn-secondary">
                    <i class="fas fa-chevron-left"></i> Mes anterior
                </a>
                <h3><?php echo $meses[$mes] . ' ' . $anio; ?></h3>
                <a href="?<?php echo http_build_query(['mes' => $mes_siguiente, 'anio' => $anio_siguiente, 'beneficiario_id' => $beneficiario_id]); ?>" class="btn-secondary">
                    Mes siguiente <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>

        <div class="calendar">
            <div class="calendar-header">
                <div>Domingo</div>
                <div>Lunes</div>
                <div>Martes</div>
                <div>Miércoles</div>
                <div>Jueves</div>
                <div>Viernes</div>
                <div>Sábado</div>
            </div>

            <div class="calendar-grid">
                <?php
                // Días vacíos antes del primer día del mes
                for ($i = 0; $i < $primer_dia_semana; $i++) {
                    echo '<div class="calendar-day empty"></div>';
                }

                // Días del mes
                for ($dia = 1; $dia <= $num_dias; $dia++) {
                    $es_hoy = $dia == date('j') && $mes == date('m') && $anio == date('Y');
                    $clase = $es_hoy ? 'calendar-day today' : 'calendar-day';

                    echo '<div class="' . $clase . '">';
                    echo '<div class="day-number">' . $dia . '</div>';

                    if (isset($eventos_por_dia[$dia])) {
                        echo '<div class="day-events">';
                        foreach ($eventos_por_dia[$dia] as $evento) {
                            $hora = date('H:i', strtotime($evento['fecha_programada']));
                            $id = $_SESSION['DIR_HOME']."vistas/agendas/editar.php?id=". $evento['id'];
                            echo "<a href=$id > VER </a>";
                            echo '<div class="event" onclick="verDetallesEvento(' . htmlspecialchars(json_encode($evento)) . ')">';
                            echo '<span class="event-time">' . $hora . '</span>';
                            echo '<span class="event-title">' . htmlspecialchars($evento['tipo_descripcion']) . '</span>';
                            if (!$beneficiario_id) {
                                echo '<span class="event-beneficiario">' . htmlspecialchars($evento['beneficiario_nombre']) . '</span>';
                            }
                            echo '</div>';
                        }
                        echo '</div>';
                    }

                    echo '</div>';
                }
                ?>
            </div>
        </div>

        <div class="module-actions">
            <a href="crear.php<?php echo $beneficiario_id ? '?beneficiario_id=' . $beneficiario_id : ''; ?>" class="btn-primary">
                <i class="fas fa-calendar-plus"></i> Nueva Agenda
            </a>
        </div>
    </div>

    <!-- Modal para detalles del evento -->
    <div id="eventoModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Detalles de la Agenda</h3>
            <div id="eventoDetalles"></div>
        </div>
    </div>
</main>

<style>
    .filters {
        margin-bottom: 20px;
    }

    .filters select {
        width: 100%;
        max-width: 300px;
        padding: 8px;
        border-radius: 4px;
        border: 1px solid #ddd;
    }

    .calendar {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
    }

    .calendar-nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .calendar-header {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        background: var(--primary-color);
        color: white;
        padding: 1rem;
        text-align: center;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 1px;
        background: #ddd;
    }

    .calendar-day {
        background: white;
        min-height: 120px;
        padding: 0.5rem;
    }

    .calendar-day.empty {
        background: #f5f5f5;
    }

    .calendar-day.today {
        background: #f0f7ff;
    }

    .day-number {
        font-weight: bold;
        margin-bottom: 0.5rem;
    }

    .day-events {
        font-size: 0.875rem;
    }

    .event {
        background: #e3f2fd;
        border-left: 3px solid var(--secondary-color);
        padding: 0.25rem 0.5rem;
        margin-bottom: 0.25rem;
        border-radius: 2px;
        cursor: pointer;
    }

    .event:hover {
        background: #bbdefb;
    }

    .event-time {
        font-weight: bold;
        margin-right: 0.5rem;
    }

    .event-beneficiario {
        display: block;
        font-size: 0.8em;
        color: #666;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }

    .modal-content {
        background-color: #fefefe;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 500px;
        border-radius: 8px;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover {
        color: black;
    }

    @media (max-width: 768px) {
        .calendar-header,
        .calendar-grid {
            font-size: 0.875rem;
        }

        .calendar-day {
            min-height: 80px;
        }

        .event {
            font-size: 0.75rem;
        }
    }
</style>

<script>
    function cambiarBeneficiario(beneficiarioId) {
        const url = new URL(window.location.href);
        if (beneficiarioId) {
            url.searchParams.set('beneficiario_id', beneficiarioId);
        } else {
            url.searchParams.delete('beneficiario_id');
        }
        window.location.href = url.toString();
    }

    function verDetallesEvento(evento) {
        const modal = document.getElementById('eventoModal');
        const detalles = document.getElementById('eventoDetalles');

        detalles.innerHTML = `

        <p><strong>Beneficiario:</strong> ${evento.beneficiario_nombre}</p>
        <p><strong>Fecha:</strong> ${new Date(evento.fecha_programada).toLocaleString()}</p>
        <p><strong>Tipo:</strong> ${evento.tipo_descripcion}</p>
        <p><strong>Descripción:</strong> ${evento.descripcion}</p>
    `;

        modal.style.display = "block";
    }

    // Cerrar modal
    document.querySelector('.close').onclick = function() {
        document.getElementById('eventoModal').style.display = "none";
    }

    window.onclick = function(event) {
        const modal = document.getElementById('eventoModal');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>


<?php require_once $_SESSION['DIR_FULL'].'vistas/plantillas/pie.php'; ?>
