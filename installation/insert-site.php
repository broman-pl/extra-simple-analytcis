<?php
error_reporting(E_ERROR|E_WARNING|E_PARSE);
ini_set ('display_errors', 1);
ini_set ('log_errors', 1);

include ("../conf/esa-config.php");
include (ESA_DIR."/modules/esa-db.php");

$db = DB::getInstance();
$db->setConnectionParameters(ESA_DB_HOST ,ESA_DB_NAME, ESA_DB_USER, ESA_DB_PASSWORD);
$db->connect();

$id = strval(time()).strval(mt_rand(100,999));
$sName = 'site-name';
$sKey = md5('site.domain');

$db->execute("INSERT INTO ".ESA_DB_PREFIX."_sites (`id`, `name`, `key`) VALUES (?, ?, ?)", [$id, $sName, $sKey]);
$db->close();

?>