<?php
session_start();
require_once('institution.cfg.php');

$MESSAGE = '';

if ((! isset($_SESSION['isAuthenticated'])) || (! $_SESSION['isAuthenticated'])) {
    if ((isset($_REQUEST['username'])) && (isset($_REQUEST['password']))) {
        require_once('auth.cfg.php');
        if ($AUTH->authenticate($_REQUEST['username'],$_REQUEST['password'])) {
            session_regenerate_id(TRUE);
            $_SESSION['isAuthenticated'] = true;
            $_SESSION['userdata'] = array();
            $_SESSION['userdata']['username'] = $_REQUEST['username'];
        } else {
            $MESSAGE = 'Log in failed';
        }
    }
}
else {
    if (isset($_REQUEST['logout'])) {
        unset($_SESSION['isAuthenticated']);
        unset($_SESSION['userdata']);
    }
    else {
        $DB = new PDO("mysql:host=".DB_SERVER.";dbname=".DB_NAME.";port=3306",DB_USER,DB_PASS);
    }
}

?>
<html>
 <head>
  <title><?php echo APP_NAME.': '.$pageTitle;?></title>
 </head>
 <body>
<?php 
if ($MESSAGE) { ?>
  <div id="message"><?php echo $MESSAGE; ?></div><?php
}

if ((isset($_SESSION['isAuthenticated'])) && ($_SESSION['isAuthenticated'])) {
?>
<div id="loggedInControls">You are logged in as <a href="account_management.php"><?php echo $_SESSION['userdata']['username']; ?></a>. <form id="frmLogout" class="" type="post" action=""><input type="submit" name="logout" id="logout_btn" value="log out"/></form></div>
<?php
} 
else if ((! isset($_SESSION['isAuthenticated'])) || (! $_SESSION['isAuthenticated'])) {
?>
<form id="frmIndex" class="" type="post" action="">
    <input type="text" id="username" name="username" value="" />
    <input type="password" id="password" name="password" value="" />
    <input type="submit" id="submit_login" name="submit_login" value="log In" />

</form>
<?php
    require_once('foot.php');
    exit;
}
