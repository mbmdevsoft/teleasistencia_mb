<?php
define('DIR_HOME', $_SERVER['DOCUMENT_ROOT'].'/');

define("FILE_LOGIN", DIR_HOME. "vistas/usuarios/iniciar-sesion.php");
define("FILE_HOME", DIR_HOME. "index.php");   /*  "<?= FILE_HOME ?>"  forma reducida    */
define("FILE_BENEFICIARIOS",DIR_HOME. "vistas/beneficiarios/listado.php");
define("FILE_AGENDA",DIR_HOME. "vistas/agendas/calendario_general.php");
define("FILE_COMUNICACIONES", DIR_HOME."vistas/comunicaciones/listado.php");

?>