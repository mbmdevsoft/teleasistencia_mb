<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once $_SESSION['DIR_FULL'] . '/includes/funciones.php';

function conexion_pdo()
{
    // Definición de los parámetros de conexión a la base de datos
    $servidor = "localhost";   // Dirección del servidor de base de datos
    $usuario = "root";         // Nombre de usuario de MySQL
    $password = "";            // Contraseña del usuario (vacía por defecto en desarrollo local)
    $puerto = "3306";          // Puerto por defecto de MySQL
    $bbdd = "teleasistencia";  // Nombre de la base de datos a la que nos conectaremos


    try {
        // Opciones de configuración para la conexión PDO
        $opciones = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,         // Activa el modo de errores con excepciones
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,    // Establece el modo de obtención por defecto como array asociativo
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4", // Establece la codificación UTF8
            PDO::ATTR_EMULATE_PREPARES => false,                 // Desactiva la emulación de preparación de consultas
        ];

        // Crea la conexión PDO a la base de datos
        $pdo = new PDO("mysql:host=$servidor;dbname=$bbdd;charset=utf8mb4", $usuario, '', $opciones);

        // Registra en el logs que la conexión fue exitosa
        registrarLog('INFO', 'Conexion a base de datos establecida');

    } catch (PDOException $e) {
        // Registra en el logs si hay un error de conexión
        registrarLog('ERROR', 'Error de conexion a la base de datos: ' . $e->getMessage());
        // Detiene la ejecución y muestra mensaje de error
        die('Error de conexión. Por favor, inténtelo más tarde.' . $e->getMessage());
    }
    return $pdo;
}

