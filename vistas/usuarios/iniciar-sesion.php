<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once $_SESSION['DIR_FULL'] . 'vistas/plantillas/cabecera-sin-menu.php';

?>

    <main>
        <div class="login-container">
            <div class="logo-login">
                <img src='<?=$_SESSION['DIR_HOME']?>recursos/imagenes/3.png' alt="Logo Teleasistencia">
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <form action='<?=$_SESSION['DIR_HOME']?>controladores/UsuarioControlador.php' method="POST">
                <div class="form-group">
                    <label for="username">Usuario:</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn-primary">Iniciar Sesión</button>
                
            </form>
        </div>
    </main>



<?php require_once $_SESSION['DIR_FULL'] .'vistas/plantillas/pie.php'; ?> 

 