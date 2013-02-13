<?php
session_start();

require_once('institution.cfg.php');

$DB = new PDO("mysql:host=".DB_SERVER.";dbname=".DB_NAME.";port=3306",DB_USER,DB_PASS);

?>
<html>
 <head>
  <title><?php echo APP_NAME.': '.$pageTitle;?></title>
 </head>
 <body>
