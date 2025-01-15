<?php
error_reporting(E_ALL);
ini_set ('display_errors', 1);
ini_set ('log_errors', 1);

require_once(__DIR__."/conf/esa-config.php");
require_once(ESA_DIR."/modules/esa-db.php");
require_once(ESA_DIR.'/vendor/autoload.php');
require_once(ESA_DIR.'/modules/esa.php');

session_start();

$esa = new esa;
$esa->proccessRequest();

?>