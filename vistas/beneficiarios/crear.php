<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once $_SESSION['DIR_FULL'] .'config/conexion.php';
require_once $_SESSION['DIR_FULL'] .'vistas/plantillas/cabecera.php';


// Calcular la fecha máxima (18 años atrás)
$fechaMaxima = date('Y-m-d', strtotime('-18 years'));

// Obtener el último número de expediente
$pdo=conexion_pdo();
$stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(numero_expediente, 5) AS UNSIGNED)) as ultimo_num FROM beneficiarios");
$resultado = $stmt->fetch();
$ultimo_num = $resultado['ultimo_num'] ?? 0;
$nuevo_num = $ultimo_num + 1;
$nuevo_expediente = 'EXP-' . str_pad($nuevo_num, 5, '0', STR_PAD_LEFT);
?>

    <main>
        <div class="form-container">
            <h2>Registro de Beneficiario</h2>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <form action="<?=$_SESSION['DIR_HOME']?>controladores/BeneficiarioControlador.php" method="POST" class="beneficiario-form">
                <div class="form-group">
                    <label for="numero_expediente">Número Expediente:</label>
                    <input type="text" id="numero_expediente" name="numero_expediente" value="<?php echo $nuevo_expediente; ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="nif_nie">NIF/NIE:</label>
                    <input type="text" id="nif_nie" name="nif_nie" maxlength="15">
                </div>

                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" maxlength="100" required>
                </div>

                <div class="form-group">
                    <label for="apellido1">Primer Apellido:</label>
                    <input type="text" id="apellido1" name="apellido1" maxlength="100" required>
                </div>

                <div class="form-group">
                    <label for="apellido2">Segundo Apellido:</label>
                    <input type="text" id="apellido2" name="apellido2" maxlength="100">
                </div>

                <div class="form-group">
                    <label for="fecha_nacimiento">Fecha Nacimiento:</label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" max="<?php echo $fechaMaxima; ?>" required>
                </div>

                <div class="form-group">
                    <label for="genero">Género:</label>
                    <select id="genero" name="genero" required>
                        <option value="">Seleccione...</option>
                        <option value="H">Hombre</option>
                        <option value="M">Mujer</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="vive_solo">¿Vive Solo?:</label>
                    <select id="vive_solo" name="vive_solo">
                        <option value="">Seleccione...</option>
                        <option value="1">Sí</option>
                        <option value="0">No</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="direccion">Dirección:</label>
                    <input type="text" id="direccion" name="direccion" maxlength="255" required>
                </div>

                <div class="form-group">
                    <label for="codigo_postal">Código Postal:</label>
                    <input type="text" id="codigo_postal" name="codigo_postal" maxlength="5" pattern="[0-9]{5}">
                </div>

                <div class="form-group">
                    <label for="provincia">Provincia:</label>
                    <input type="text" id="provincia" name="provincia" maxlength="100">
                </div>

                <div class="form-group">
                    <label for="poblacion">Población:</label>
                    <input type="text" id="poblacion" name="poblacion" maxlength="100">
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
                    <label for="centro_salud">Centro de Salud:</label>
                    <input type="text" id="centro_salud" name="centro_salud" maxlength="200">
                </div>

                <div class="form-group">
                    <label for="estado">Estado:</label>
                    <select id="estado" name="estado" required>
                        <option value="">Seleccione...</option>
                        <?php
                        $stmt = $pdo->query("SELECT id, descripcion FROM beneficiario_estado");
                        while ($row = $stmt->fetch()) {
                            echo '<option value="' . $row['id'] . '">' . $row['descripcion'] . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-buttons">
                    <button type="submit" class="btn-primary">Guardar Beneficiario</button>
                    <a href="<?=$_SESSION['DIR_HOME']?>vistas/beneficiarios/listado.php" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </main>


<?php require_once $_SESSION['DIR_FULL'] . 'vistas/plantillas/pie.php';?>
