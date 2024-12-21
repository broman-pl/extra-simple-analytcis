<?php
error_reporting(E_ERROR|E_WARNING|E_PARSE);
ini_set ('display_errors', 1);
ini_set ('log_errors', 1);

include ("../conf/esa-config.php");
include (ESA_DIR."/modules/esa-db.php");

$db = DB::getInstance();
$db->setConnectionParameters(ESA_DB_HOST ,ESA_DB_NAME, ESA_DB_USER, ESA_DB_PASSWORD);
$db->connect();

//TODO check if there is a user and only create users when there is not users in table 

$id = strval(time()).strval(mt_rand(100,999));
$login = 'main-user-login';
$passwdHash = md5(ESA_SALT.'main-user-password');

$db->execute("INSERT INTO ".ESA_DB_PREFIX."_users (id, login, pass_hash, created_date) VALUES ( ?, ?, ?, now())", [$id, $login, $passwdHash]);
$db->close();

?>