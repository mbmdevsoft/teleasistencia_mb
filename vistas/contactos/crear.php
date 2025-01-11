<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once $_SESSION['DIR_FULL'] .'config/conexion.php';
require_once $_SESSION['DIR_FULL'] .'vistas/plantillas/cabecera.php';

// Obtener el ID del beneficiario
$beneficiario_id = $_GET['beneficiario_id'] ?? null;

if (!$beneficiario_id) {
    header('Location: ' . $_SESSION['DIR_HOME'] . 'vistas/beneficiarios/listado.php?error=No se especificó el beneficiario');
    exit();
}

// Obtener datos del beneficiario
$pdo=conexion_pdo();
$stmt = $pdo->prepare("SELECT nombre, apellido1, apellido2 FROM beneficiarios WHERE id = ?");
$stmt->execute([$beneficiario_id]);
$beneficiario = $stmt->fetch();

if (!$beneficiario) {
    header(header: 'Location: ' . $_SESSION['DIR_HOME'] . 'vistas/beneficiarios/listado.php?error=Beneficiario no encontrado');
    exit();
}

// Contar contactos existentes
$stmt = $pdo->prepare("SELECT COUNT(*) FROM contactos WHERE beneficiario_id = ?");
$stmt->execute([$beneficiario_id]);
$contactos_existentes = $stmt->fetchColumn();

// Si ya tiene 3 contactos, redirigir con error
if ($contactos_existentes >= 3) {
    header('Location: ' .$_SESSION['DIR_HOME']. 'vistas/beneficiarios/ver.php?id=' . $beneficiario_id . '&error=El beneficiario ya tiene el máximo de 3 contactos permitidos');
    exit();
}
?>

    <main>
        <div class="form-container">
            <h2>Registro de Contactos</h2>
            <h3>Beneficiario: <?php echo htmlspecialchars($beneficiario['nombre'] . ' ' . $beneficiario['apellido1'] . ' ' . $beneficiario['apellido2']); ?></h3>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <form action="<?=$_SESSION['DIR_HOME']?>controladores/ContactoControlador.php" method="POST" class="contacto-form">
                <input type="hidden" name="beneficiario_id" value="<?php echo $beneficiario_id; ?>">


                <div class="form-group">
                    <label for="parentesco">Parentesco:</label>

                    <select id="parentesco" name="parentesco" required>
                        <option value="">Seleccione...</option>
                        <?php
                        $stmt = $pdo->query("SELECT id, descripcion FROM contacto_parentesco");
                        while ($row = $stmt->fetch()) {
                            echo '<option value="' . $row['id'] . '">' . $row['descripcion'] . '</option>';
                        }
                        ?>
                    </select>
                </div>


                <div class="form-group">
                    <label for="orden_prioridad">Prioridad de contacto:</label>
                    <select id="orden_prioridad" name="orden_prioridad" required>
                        <option value="">Seleccione prioridad...</option>
                        <option value="1">Contacto Principal</option>
                        <option value="2">Contacto Secundario</option>
                        <option value="3">Contacto Alternativo</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" maxlength="100" required>
                </div>

                <div class="form-group">
                    <label for="apellidos">Apellidos:</label>
                    <input type="text" id="apellidos" name="apellidos" maxlength="200" required>
                </div>

                <div class="form-group">
                    <label for="tiene_llave">¿Tiene Llave?:</label>
                    <select id="tiene_llave" name="tiene_llave">
                        <option value="1">Sí</option>
                        <option value="0" selected>No</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="direccion">Dirección:</label>
                    <input type="text" id="direccion" name="direccion" maxlength="255">
                </div>

                <div class="form-group">
                    <label for="telefono_fijo">Teléfono Fijo:</label>
                    <input type="tel" id="telefono_fijo" name="telefono_fijo" maxlength="15" pattern="[0-9]{9}">
                </div>

                <div class="form-group">
                    <label for="telefono_movil">Teléfono Móvil:</label>
                    <input type="tel" id="telefono_movil" name="telefono_movil" maxlength="15" pattern="[0-9]{9}">
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" maxlength="255">
                </div>

                <div class="form-group">
                    <label for="horario_disponibilidad">Horario de Disponibilidad:</label>
                    <input type="time" id="horario_disponibilidad" name="horario_disponibilidad">
                </div>

                <div class="form-group">
                    <label for="distancia">Distancia metros:</label>
                    <input type="number" id="distancia" name="distancia">
                </div>

                <div class="form-group">
                    <label for="centro_trabajo">Centro de Trabajo:</label>
                    <input type="text" id="centro_trabajo" name="centro_trabajo" maxlength="200">
                </div>

                <div class="form-group">
                    <label for="telefono_trabajo">Teléfono Trabajo:</label>
                    <input type="tel" id="telefono_trabajo" name="telefono_trabajo" maxlength="15" pattern="[0-9]{9}">
                </div>

                <div class="form-buttons">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                    <a href="<?=$_SESSION['DIR_HOME']?>vistas/beneficiarios/ver.php?id=<?php echo $beneficiario_id; ?>" class="btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </main>


<script>
    document.querySelector('.contacto-form').addEventListener('submit', function (e) {
        const telefonoFijo = document.getElementById('telefono_fijo').value;
        const telefonoMovil = document.getElementById('telefono_movil').value;
        const telefonoTrabajo = document.getElementById('telefono_trabajo').value;

        if (!telefonoFijo && !telefonoMovil && !telefonoTrabajo) {
            e.preventDefault();
            alert('Debe proporcionar al menos un número de teléfono');
            return;
        }

        const validarTelefono = (tel) => {
            return tel === '' || /^[6789][0-9]{8}$/.test(tel);
        };

        if (!validarTelefono(telefonoFijo) || !validarTelefono(telefonoMovil) || !validarTelefono(telefonoTrabajo)) {
            e.preventDefault();
            alert('El formato del teléfono no es válido. Debe ser un número de 9 dígitos que empiece por 6, 7, 8 o 9');
            return;
        }
    });
</script>

<?php require_once $_SESSION['DIR_FULL'] . '/vistas/plantillas/pie.php';?>

