<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teleasistencia_mb</title>
    <link href="<?= $_SESSION['DIR_HOME'] ?>recursos/css/estilo.css" rel='stylesheet'>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!--    Alertas especiales-->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>


    <section style="display: flex;    flex-direction: row;    width: 100%;    height: 75px;    background-color: #05751c;">


    
        <a href=<?php echo $_SESSION['DIR_HOME'] . 'index.php' ?>>
            <img src="<?= $_SESSION['DIR_HOME'] ?>recursos/imagenes/3.png" alt="Logo Teleasistencia" class="logo-img">
        </a>

        <a href="tel:+34900123456">
            <img src='<?= $_SESSION['DIR_HOME'] ?>recursos/imagenes/telefono.png' alt="telefono" class="logo-img2">
        </a>

        <?php
        if (isset($_SESSION['usuario_name'])) {
            echo "<p class='usuario'>" . $_SESSION['usuario_name'] . "</p>";
        }
        ?>
        <ul class="menu">
            <li><a href="<?= $_SESSION['DIR_HOME'] ?>index.php">Panel</a></li>
            <li><a href="<?= $_SESSION['DIR_HOME'] ?>vistas/beneficiarios/listado.php">Beneficiarios</a></li>
            <li><a href="<?= $_SESSION['DIR_HOME'] ?>vistas/agendas/calendario_general.php">Agenda</a></li>
            <li><a href="<?= $_SESSION['DIR_HOME'] ?>vistas/comunicaciones/listado.php">Llamadas</a></li>
            <li><a href="<?= $_SESSION['DIR_HOME'] ?>vistas/usuarios/iniciar-sesion.php">Login</a></li>

        </ul>
    </section>