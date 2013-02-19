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
            $_SESSION['userdata']['username']   = $_REQUEST['username'];
            $_SESSION['userdata']['email']      = $AUTH->mail;
            $_SESSION['userdata']['fullname']   = $AUTH->name;
            $_SESSION['userdata']['firstname']  = $AUTH->fname;
            $_SESSION['userdata']['lastname']   = $AUTH->lname;
            $_SESSION['userdata']['sortname']   = $AUTH->sortname;
            $_SESSION['userdata']['position']   = $AUTH->position; // e.g. (STUDENT, FACULTY, STAFF)
            
            $_SESSION['userdata']['eq_groups']  = $AUTH->getGroups($_SESSION['userdata']['username']); // hash of groups and roles?
            
            if($AUTH->getGroups($_SESSION['userdata']['username'])) {
                
            }
        } else {
            $MESSAGE = 'Log in failed';
        }
        
//		# START: Debugging Info
//			echo "<br /><h2>Development Messages</h2>";
//			# echo "isAuthenticated = ".$_SESSION['isAuthenticated']."<br />\n";
//			#if ($AUTH->msg != '' || $AUTH->debug != '') {
//				// echo the attribute of the $AUTH object
//				echo "<b>Message found:</b><br />" . $AUTH->msg . "<br />";
//				echo "<b>Debug Info:</b><br />" . $AUTH->debug . "<br />";
//			#}
//		# END: Debugging Info

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

<div class="DEVINFO">
<?php 
    echo "<br /><br /><b>User Info:</b><br />";
    echo "username: " . $_SESSION['userdata']['username'] . "<br />";
    echo "email: " . $_SESSION['userdata']['email'] . "<br />";
    echo "fullname: " . $_SESSION['userdata']['fullname'] . "<br />";
    echo "firstname: " . $_SESSION['userdata']['firstname'] . "<br />";
    echo "lastname: " . $_SESSION['userdata']['lastname'] . "<br />";
    echo "sortname: " . $_SESSION['userdata']['sortname'] . "<br />";
    echo "position: " . $_SESSION['userdata']['position'] . "<br />";
?>
</div>
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
