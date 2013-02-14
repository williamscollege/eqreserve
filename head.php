<?php
session_start();
require_once('institution.cfg.php');

echo "head loaded\n";

if ((! isset($_SESSION['isAuthenticated'])) || (! $_SESSION['isAuthenticated'])) {
//echo 'session not authenticated';
    if ((isset($_REQUEST['username'])) && (isset($_REQUEST['password']))) {
//echo "trying to authenticate\n";
//echo 'username = '.$_REQUEST['username']."\n";
//echo 'password = '.$_REQUEST['password']."\n";
        require_once('auth.cfg.php');
        if ($AUTH->authenticate($_REQUEST['username'],$_REQUEST['password'])) {
            session_regenerate_id(TRUE);
            $_SESSION['isAuthenticated'] = true;
            $_SESSION['username'] = $_REQUEST['username'];
        }
//echo "post authenticate attempt\n";
//echo 'isAuthenticated = '.$_SESSION['isAuthenticated']."\n";
    }    
}

$DB = new PDO("mysql:host=".DB_SERVER.";dbname=".DB_NAME.";port=3306",DB_USER,DB_PASS);

?>
<html>
 <head>
  <title><?php echo APP_NAME.': '.$pageTitle;?></title>
 </head>
 <body>
