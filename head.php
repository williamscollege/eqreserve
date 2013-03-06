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

		if (isset($_REQUEST['submit_logout'])) {
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
		$USER = new User(['username'=>$_SESSION['userdata']['username'],'DB'=>$DB]);

		// now check if user data differs from session data, and if so, update the users db record (this might be a part of the User construct method)
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo APP_NAME . ': ' . $pageTitle;?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo APP_NAME; ?>">
    <meta name="author" content="OIT Project Group">
    <!-- CSS: Framework -->
    <link rel="stylesheet" href="css/bootstrap.css" type="text/css" media="all">
    <link rel="stylesheet" href="css/bootstrap-responsive.css" type="text/css" media="all">
    <link rel="stylesheet" href="css/wms-style.css" type="text/css" media="all">
    <!-- CSS: Plugins -->
    <!-- jQuery: Framework -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <!-- jQuery: Plugins -->
    <script src="js/bootstrap.min.js"></script>
</head>
<body>

<div class="navbar navbar-inverse navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">
            <a class="brand" href="#"><?php echo APP_NAME; ?></a>
            <div class="nav-collapse collapse">
                <ul class="nav">
                    <li class="active"><a href="/eqreserve/">Home</a></li>
                    <li><a href="#contact">Contact</a></li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Dropdown <b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="#">Action</a></li>
                            <li><a href="#">Another action</a></li>
                            <li><a href="#">Something else here</a></li>
                            <li class="divider"></li>
                            <li class="nav-header">Nav header</li>
                            <li><a href="#">Separated link</a></li>
                            <li><a href="#">One more separated link</a></li>
                        </ul>
                    </li>
                </ul>
				<?php
				if ((isset($_SESSION['isAuthenticated'])) && ($_SESSION['isAuthenticated'])) {
					?>
                    <div id="loggedInControls">
                        <form id="frmLogout" class="navbar-form pull-right" type="post" action="">
                            <span class="muted">You are logged in as <a href="account_management.php"><?php echo $_SESSION['userdata']['username']; ?></a></span>.
                            <input type="submit" id="submit_logout" class="btn" name="submit_logout"  value="Sign out" />
                        </form>
                    </div>
					<?php
				} else {
					?>
                    <form id="frmLogin" class="navbar-form pull-right" type="post" action="">
                        <input type="text" id="username" class="span2" name="username" placeholder="Williams Username" value="" />
                        <input type="password" id="password_login" class="span2" name="password" placeholder="Password" value="" />
                        <input type="submit" id="submit_login" class="btn" name="submit_login" value="Sign in" />
                    </form>
					<?php
					if ($MESSAGE) {
						echo "<div id=\"message\" class=\"text-error\"><br />" . $MESSAGE . "</div>";
					}
				}
				?>
            </div>
        </div>
    </div>
</div>

<?php /* This div is closed in foot.php */ ?>
<div class="container">


