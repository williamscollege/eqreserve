<?php
session_start();
require_once('institution.cfg.php');

if ((! isset($_SESSION['isAuthenticated'])) || (! $_SESSION['isAuthenticated'])) {
    if ((isset($_REQUEST['username'])) && (isset($_REQUEST['password']))) {
        require_once('auth.cfg.php');

        if ($AUTH->authenticate($_REQUEST['username'],$_REQUEST['password'])) {
            session_regenerate_id(TRUE);
            $_SESSION['isAuthenticated'] = true;
            $_SESSION['userdata'] = array();
            $_SESSION['userdata']['username']	= $_REQUEST['username'];
            $_SESSION['userdata']['email']		= $AUTH->auth_mail;
            $_SESSION['userdata']['fullname']	= $AUTH->auth_name;
            $_SESSION['userdata']['firstname']	= $AUTH->auth_fname;
            $_SESSION['userdata']['lastname']	= $AUTH->auth_lname;
            $_SESSION['userdata']['sortname']	= $AUTH->auth_sortname;
            $_SESSION['userdata']['position']	= $AUTH->auth_position; // e.g. (STUDENT, FACULTY, STAFF)
        }
        
		# START: Debugging Info
			echo "<br /><h2>Development Messages</h2>";
			# echo "isAuthenticated = ".$_SESSION['isAuthenticated']."<br />\n";
			#if ($AUTH->auth_msg != '' || $AUTH->auth_debug != '') {
				// echo the attribute of the $AUTH object
				echo "<b>Message found:</b><br />" . $AUTH->auth_msg . "<br />";
				echo "<b>Debug Info:</b><br />" . $AUTH->auth_debug . "<br />";
			#}
		# END: Debugging Info
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
if ((isset($_SESSION['isAuthenticated'])) && ($_SESSION['isAuthenticated'])) {
?>
<div id="loggedInControls">
	You are logged in as:<br />
		<?php 
			echo "username: " . $_SESSION['userdata']['username'] . "<br />";
			echo "email: " . $_SESSION['userdata']['email'] . "<br />";
			echo "fullname: " . $_SESSION['userdata']['fullname'] . "<br />";
			echo "firstname: " . $_SESSION['userdata']['firstname'] . "<br />";
			echo "lastname: " . $_SESSION['userdata']['lastname'] . "<br />";
			echo "sortname: " . $_SESSION['userdata']['sortname'] . "<br />";
			echo "position: " . $_SESSION['userdata']['position'] . "<br />";
		?>
		<form id="frmLogout" class="" type="post" action=""><input type="submit" name="logout" id="logout_btn" value="log out"/></form></div>
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
