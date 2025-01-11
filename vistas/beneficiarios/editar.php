<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once $_SESSION['DIR_FULL'] .'config/conexion.php';
require_once $_SESSION['DIR_FULL'] .'vistas/plantillas/cabecera.php';


$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: '.$_SESSION['DIR_FULL']. 'vistas/beneficiarios/listado.php?error=No se especificó el beneficiario');
    exit();
}

// Calcular la fecha máxima (18 años atrás)
$fechaMaxima = date('Y-m-d', strtotime('-18 years'));

// Obtener datos del beneficiario
$pdo=conexion_pdo();
$stmt = $pdo->prepare("SELECT * FROM beneficiarios WHERE id = ?");
$stmt->execute([$id]);
$beneficiario = $stmt->fetch();

if (!$beneficiario) {
    header('Location: '.$_SESSION['DIR_FULL']. 'vistas/beneficiarios/listado.php?error=Beneficiario no encontrado');
    exit();
}
?>

<main>
    <div class="form-container">
        <h2>Editar Beneficiario</h2>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <form action="<?=$_SESSION['DIR_HOME']?>controladores/BeneficiarioControlador.php" method="POST" class="beneficiario-form">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?php echo $id; ?>">

            <div class="form-group">
                <label for="numero_expediente">Número Expediente:</label>
                <input type="text" id="numero_expediente" name="numero_expediente"
                       value="<?php echo htmlspecialchars($beneficiario['numero_expediente']); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="nif_nie">NIF/NIE:</label>
                <input type="text" id="nif_nie" name="nif_nie"
                       value="<?php echo htmlspecialchars($beneficiario['nif_nie'] ?? ''); ?>" maxlength="15">
            </div>

            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre"
                       value="<?php echo htmlspecialchars($beneficiario['nombre']); ?>"
                       maxlength="100" required>
            </div>

            <div class="form-group">
                <label for="apellido1">Primer Apellido:</label>
                <input type="text" id="apellido1" name="apellido1"
                       value="<?php echo htmlspecialchars($beneficiario['apellido1']); ?>"
                       maxlength="100" required>
            </div>

            <div class="form-group">
                <label for="apellido2">Segundo Apellido:</label>
                <input type="text" id="apellido2" name="apellido2"
                       value="<?php echo htmlspecialchars($beneficiario['apellido2'] ?? ''); ?>"
                       maxlength="100">
            </div>

            <div class="form-group">
                <label for="fecha_nacimiento">Fecha Nacimiento:</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento"
                       value="<?php echo $beneficiario['fecha_nacimiento']; ?>" max="<?php echo $fechaMaxima; ?>" required>
            </div>

            <div class="form-group">
                <label for="genero">Género:</label>
                <select id="genero" name="genero" required>
                    <option value="H" <?php echo $beneficiario['genero'] === 'H' ? 'selected' : ''; ?>>Hombre</option>
                    <option value="M" <?php echo $beneficiario['genero'] === 'M' ? 'selected' : ''; ?>>Mujer</option>
                </select>
            </div>

            <div class="form-group">
                <label for="vive_solo">¿Vive Solo?:</label>
                <select id="vive_solo" name="vive_solo">
                    <option value="">Seleccione...</option>
                    <option value="1" <?php echo $beneficiario['vive_solo'] == 1 ? 'selected' : ''; ?>>Sí</option>
                    <option value="0" <?php echo $beneficiario['vive_solo'] == 0 ? 'selected' : ''; ?>>No</option>
                </select>
            </div>

            <div class="form-group">
                <label for="direccion">Dirección:</label>
                <input type="text" id="direccion" name="direccion"
                       value="<?php echo htmlspecialchars($beneficiario['direccion']); ?>"
                       maxlength="255" required>
            </div>

            <div class="form-group">
                <label for="codigo_postal">Código Postal:</label>
                <input type="text" id="codigo_postal" name="codigo_postal"
                       value="<?php echo htmlspecialchars($beneficiario['codigo_postal'] ?? ''); ?>"
                       maxlength="5" pattern="[0-9]{5}">
            </div>

            <div class="form-group">
                <label for="provincia">Provincia:</label>
                <input type="text" id="provincia" name="provincia"
                       value="<?php echo htmlspecialchars($beneficiario['provincia'] ?? ''); ?>"
                       maxlength="100">
            </div>

            <div class="form-group">
                <label for="poblacion">Población:</label>
                <input type="text" id="poblacion" name="poblacion"
                       value="<?php echo htmlspecialchars($beneficiario['poblacion'] ?? ''); ?>"
                       maxlength="100">
            </div>

            <div class="form-group">
                <label for="telefono_fijo">Teléfono Fijo:</label>
                <input type="tel" id="telefono_fijo" name="telefono_fijo"
                       value="<?php echo htmlspecialchars($beneficiario['telefono_fijo'] ?? ''); ?>"
                       maxlength="15" pattern="[0-9]{9}">
            </div>

            <div class="form-group">
                <label for="telefono_movil">Teléfono Móvil:</label>
                <input type="tel" id="telefono_movil" name="telefono_movil"
                       value="<?php echo htmlspecialchars($beneficiario['telefono_movil'] ?? ''); ?>"
                       maxlength="15" pattern="[0-9]{9}">
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email"
                       value="<?php echo htmlspecialchars($beneficiario['email'] ?? ''); ?>"
                       maxlength="255">
            </div>

            <div class="form-group">
                <label for="centro_salud">Centro de Salud:</label>
                <input type="text" id="centro_salud" name="centro_salud"
                       value="<?php echo htmlspecialchars($beneficiario['centro_salud'] ?? ''); ?>"
                       maxlength="200">
            </div>

            <div class="form-group">
                <label for="estado">Estado:</label>
                <select id="estado" name="estado" required>
                    <?php
                    $stmt = $pdo->query("SELECT id, descripcion FROM beneficiario_estado");
                    while ($row = $stmt->fetch()) {
                        $selected = ($row['id'] == $beneficiario['estado']) ? 'selected' : '';
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
                <a href="<?=$_SESSION['DIR_HOME']?>vistas/beneficiarios/ver.php?id=<?php echo $id; ?>" class="btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</main>

<script>
    document.querySelector('.beneficiario-form').addEventListener('submit', function(e) {
        const nifNie = document.getElementById('nif_nie').value;
        const telefonoFijo = document.getElementById('telefono_fijo').value;
        const telefonoMovil = document.getElementById('telefono_movil').value;
        const codigoPostal = document.getElementById('codigo_postal').value;

        // Validación de NIF/NIE si está presente
        if (nifNie && !validarNIFNIE(nifNie)) {
            e.preventDefault();
            alert('El formato del NIF/NIE no es válido');
            return;
        }

        // Validación de teléfonos si están presentes
        if (telefonoFijo && !validarTelefono(telefonoFijo)) {
            e.preventDefault();
            alert('El formato del teléfono fijo no es válido');
            return;
        }

        if (telefonoMovil && !validarTelefono(telefonoMovil)) {
            e.preventDefault();
            alert('El formato del teléfono móvil no es válido');
            return;
        }

        // Validación de código postal si está presente
        if (codigoPostal && !validarCodigoPostal(codigoPostal)) {
            e.preventDefault();
            alert('El formato del código postal no es válido');
            return;
        }
    });



    function validarNIFNIE(valor) {
        return /^[0-9XYZ][0-9]{7}[TRWAGMYFPDXBNJZSQVHLCKE]$/i.test(valor);
    }

    function validarTelefono(valor) {
        return /^[6789][0-9]{8}$/.test(valor);
    }

    function validarCodigoPostal(valor) {
        return /^[0-9]{5}$/.test(valor);
    }
</script>


<?php require_once $_SESSION['DIR_FULL'] . '/vistas/plantillas/pie.php';?>


