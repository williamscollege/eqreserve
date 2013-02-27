<?php
	session_start();
	require_once('institution.cfg.php');

	$MESSAGE = '';

	if ((!isset($_SESSION['isAuthenticated'])) || (!$_SESSION['isAuthenticated'])) {
		if ((isset($_REQUEST['username'])) && (isset($_REQUEST['password']))) {
		// SECTION: not yet authenticated, wants to log in

			require_once('auth.cfg.php');

			if ($AUTH->authenticate($_REQUEST['username'], $_REQUEST['password'])) {
				session_regenerate_id(TRUE);
				$_SESSION['isAuthenticated']       = TRUE;
				$_SESSION['userdata']              = array();
                $_SESSION['userdata']['username']  = $AUTH->username;
				$_SESSION['userdata']['email']     = $AUTH->email;
				$_SESSION['userdata']['firstname'] = $AUTH->fname;
				$_SESSION['userdata']['lastname']  = $AUTH->lname;
				$_SESSION['userdata']['sortname']  = $AUTH->sortname;
				// array of institutional groups for this user
				$_SESSION['userdata']['inst_groups'] = array_slice($AUTH->inst_groups,0); // makes a copy of the array
								
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
	} else {
		// SECTION: authenticated
		
		if (isset($_REQUEST['logout'])) {
			// SECTION: wants to log out
			unset($_SESSION['isAuthenticated']);
			unset($_SESSION['userdata']);
		}
	}

    if (isset($_SESSION['isAuthenticated']) && ($_SESSION['isAuthenticated'])) {
			// SECTION: is logged in
			
			$DB = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";port=3306", DB_USER, DB_PASS);
			
			// now create user object
			require_once('/classes/user.class.php');
			$USER = new User(['username'=>$_SESSION['userdata']['username']],$DB);
			
			// now check if user data differs from session data, and if so, update the users db record (this might be a part of the User construct method)
	}
?>
<html>
<head>
    <title><?php echo APP_NAME . ': ' . $pageTitle;?></title>
</head>
<body>
<?php
	if ($MESSAGE) {
?>
    <div id="message"><?php echo $MESSAGE; ?></div>
	<?php
	}

	if ((isset($_SESSION['isAuthenticated'])) && ($_SESSION['isAuthenticated'])) {
	?>
		<div id="loggedInControls">You are logged in as <a href="account_management.php"><?php echo $_SESSION['userdata']['username']; ?></a>.
		    <form id="frmLogout" class="" type="post" action="">
		        <input type="submit" name="logout" id="logout_btn" value="log out" />
		    </form>
		</div>
	<?php
	} else {
	?>
	    <form id="frmLogin" class="" type="post" action="">
	        <input type="text" id="username" name="username" value="" />
	        <input type="password" id="password" name="password" value="" />
	        <input type="submit" id="submit_login" name="submit_login" value="log In" />
	    </form>
	<?php
		require_once('foot.php');
		exit;	
	}
	?>
