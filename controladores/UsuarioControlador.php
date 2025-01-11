<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once $_SESSION['DIR_FULL'] .'config/conexion.php';
require_once $_SESSION['DIR_FULL'] . 'includes/funciones.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'] ?? '';

    try {

        // Verificar en la base de datos
        $pdo = conexion_pdo();
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = ? ");
        $stmt->execute([$username]);
        $usuario = $stmt->fetch();


        if ($usuario && password_verify($password, $usuario['password'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_name'] = $usuario['username'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_rol'] = 'admin';
            $_SESSION['ultimo_acceso'] = time();

            header('Location: ' . $_SESSION['DIR_HOME']. 'index.php');
            exit();
        } else {
            header('Location: ' . $_SESSION['DIR_HOME']. 'vistas/usuarios/iniciar-sesion.php?error=Credenciales invalidas');
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error en login: " . $e->getMessage());
        header('Location: ' . $_SESSION['DIR_HOME']. 'vistas/usuarios/iniciar-sesion.php?error=Error del servidor');
        exit();
    }
}

?>