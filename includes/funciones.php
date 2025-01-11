<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
function registrarLog($tipo, $mensaje, $datos = [])
{
    // Obtiene la ruta del archivo de logs
    $log_file = $_SESSION['DIR_FULL'] . '/logs/app.logs';

    // Prepara la estructura del registro
    $entrada = [
        'fecha' => date('Y-m-d H:i:s'),    // Fecha y hora actual
        'tipo' => $tipo,                    // Tipo de logs (ERROR, INFO, etc)
        'mensaje' => $mensaje,              // Mensaje descriptivo
        // ?? es el operador de fusión null - si no hay usuario_id, usa 'no_auth'
        'usuario' => $_SESSION['usuario_id'] ?? 'no_auth',
        'ip' => $_SERVER['REMOTE_ADDR'],    // IP del cliente
        'datos' => $datos                   // Datos adicionales opcionales
    ];

    // Escribe en el archivo de logs
    // FILE_APPEND agrega al final del archivo
    // LOCK_EX obtiene un bloqueo exclusivo del archivo
    file_put_contents(
        $log_file,
        json_encode($entrada) . "\n",
        FILE_APPEND | LOCK_EX
    );
}
function registrarErr($tipo, $mensaje, $datos = [])
{
    // Obtiene la ruta del archivo de logs
    $log_file = $_SESSION['DIR_FULL'] . '/logs/app.errs';

    // Prepara la estructura del registro
    $entrada = [
        'fecha' => date('Y-m-d H:i:s'),    // Fecha y hora actual
        'tipo' => $tipo,                    // Tipo de logs (ERROR, INFO, etc)
        'mensaje' => $mensaje,              // Mensaje descriptivo
        // ?? es el operador de fusión null - si no hay usuario_id, usa 'no_auth'
        'usuario' => $_SESSION['usuario_id'] ?? 'no_auth',
        'ip' => $_SERVER['REMOTE_ADDR'],    // IP del cliente
        'datos' => $datos                   // Datos adicionales opcionales
    ];

    // Escribe en el archivo de logs
    // FILE_APPEND agrega al final del archivo
    // LOCK_EX obtiene un bloqueo exclusivo del archivo
    file_put_contents(
        $log_file,
        json_encode($entrada) . "\n",
        FILE_APPEND | LOCK_EX
    );
}

function verificarSesion()
{
    // crea una nueva sesión o reanuda la existente
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Verifica si han pasado más tiempo del permitido sin interactuar
    if (isset($_SESSION['ultimo_acceso']) && (time() - $_SESSION['ultimo_acceso'] > 1200)) { // 1200 SEGUNDOS, 20 minutos
        // elimina login y fuerza a que se logeuen de nuevo
        $_SESSION['usuario_id'] = null;
        header('Location:' . $_SESSION['DIR_HOME'] . 'index.php');
    } else {
        $_SESSION['ultimo_acceso'] = time();
    }

}

// Función que limpia datos de entrada para prevenir XSS (Cross-Site Scripting)
function sanitizar($dato)
{
    // Si el dato es un array, aplica la función recursivamente a cada elemento
    if (is_array($dato)) {
        return array_map('sanitizar', $dato);
    }
    // trim() elimina espacios en blanco al inicio y final
    // strip_tags() elimina etiquetas HTML y PHP
    // htmlspecialchars() convierte caracteres especiales en entidades HTML
    return htmlspecialchars(strip_tags(trim($dato)), ENT_QUOTES, 'UTF-8');
}

// Función que aplica la función sanitizar a un array de datos
function sanitizarDatos($datos)
{
    $sanitizado = [];
    foreach ($datos as $key => $value) {
        $sanitizado[$key] = sanitizar($value);
    }
    return $sanitizado;
}


// Función que valida todos los campos de un beneficiario
function validarDatosBeneficiario($datos)
{
    $errores = [];  // Array para almacenar mensajes de error

    // Serie de validaciones para cada campo
    // Si no pasa la validación, añade un mensaje de error
    if (!empty($datos['nif_nie']) && !validarDocumentoIdentidad($datos['nif_nie'])) {
        $errores[] = "El NIF/NIE no es valido";
    }

    if (!empty($datos['telefono_fijo']) && !validarTelefono($datos['telefono_fijo'])) {
        $errores[] = "El telefono fijo no es valido";
    }

    if (!empty($datos['telefono_movil']) && !validarTelefono($datos['telefono_movil'])) {
        $errores[] = "El telefono movil no es valido";
    }

    if (!validarEmail($datos['email'])) {
        $errores[] = "El email no tiene un formato valido";
    }

    if (!validarCP($datos['codigo_postal'])) {
        $errores[] = "El codigo postal no es valido";
    }

    if (!validarFecha($datos['fecha_nacimiento'])) {
        $errores[] = "La fecha de nacimiento no tiene un formato valido";
    }

    // Campos obligatorios
    if (empty($datos['nombre'])) {
        $errores[] = "El nombre es obligatorio";
    }

    if (empty($datos['apellido1'])) {
        $errores[] = "El primer apellido es obligatorio";
    }

    if (empty($datos['direccion'])) {
        $errores[] = "La dirección es obligatoria";
    }

    return $errores;
}


// Función que valida un NIF (Número de Identificación Fiscal) español
function validarNIF($nif)
{
    // Convierte a mayúsculas
    $nif = strtoupper($nif);
    // Letras válidas para el NIF en orden
    $letras = "TRWAGMYFPDXBNJZSQVHLCKE";

    // Verifica el formato: 8 números seguidos de una letra
    if (!preg_match('/^[0-9]{8}[A-Z]$/', $nif)) {
        return false;
    }

    // Extrae los números y la letra
    $numero = substr($nif, 0, 8);  // Primeros 8 caracteres
    $letra = substr($nif, 8, 1);   // Último carácter

    // Verifica si la letra corresponde al número según el algoritmo oficial
    return $letra === $letras[$numero % 23];
}

// Función que valida un NIE (Número de Identidad de Extranjero)
function validarNIE($nie)
{
    $nie = strtoupper($nie);
    $letras = "TRWAGMYFPDXBNJZSQVHLCKE";

    // Verifica el formato: X,Y,Z seguido de 7 números y una letra
    if (!preg_match('/^[XYZ][0-9]{7}[A-Z]$/', $nie)) {
        return false;
    }

    // Convierte la primera letra en su número correspondiente
    $primera = substr($nie, 0, 1);
    switch ($primera) {
        case 'X':
            $numero = '0' . substr($nie, 1, 7);
            break;
        case 'Y':
            $numero = '1' . substr($nie, 1, 7);
            break;
        case 'Z':
            $numero = '2' . substr($nie, 1, 7);
            break;
    }

    // Verifica la letra de control
    $letra = substr($nie, 8, 1);
    return $letra === $letras[$numero % 23];
}

// Función que valida tanto NIF como NIE
function validarDocumentoIdentidad($documento)
{
    // Si está vacío se considera válido (para campos opcionales)
    if (empty($documento)) {
        return true;
    }
    // Intenta validar como NIF o como NIE
    return validarNIF($documento) || validarNIE($documento);
}

// Función que valida números de teléfono españoles
function validarTelefono($telefono)
{
    if (empty($telefono)) {
        return true;
    }
    // Debe empezar por 6,7,8,9 y tener 9 dígitos en total
    return preg_match('/^[6789][0-9]{8}$/', $telefono);
}

// Función que valida un email
function validarEmail($email)
{
    if (empty($email)) {
        return true;
    }
    // Usa la función nativa de PHP para validar emails
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Función que valida un código postal español
function validarCP($cp)
{
    if (empty($cp)) {
        return true;
    }
    // Verifica que sean 5 dígitos
    if (!preg_match('/^[0-9]{5}$/', $cp)) {
        return false;
    }
    // Verifica que esté en el rango válido para España
    $num = intval($cp);
    return $num >= 1000 && $num <= 52999;
}

// Función que valida una fecha
function validarFecha($fecha)
{
    $formato = 'Y-m-d';  // Formato año-mes-día
    // Intenta crear un objeto DateTime con el formato especificado
    $d = DateTime::createFromFormat($formato, $fecha);
    // Verifica que la fecha sea válida y coincida exactamente con el formato
    return $d && $d->format($formato) === $fecha;
}




