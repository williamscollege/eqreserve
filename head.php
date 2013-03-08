<?php
	session_start();
	require_once('institution.cfg.php');
	require_once('/classes/user.class.php');
	require_once('auth.cfg.php');

	$MESSAGE = '';

	if ((!isset($_SESSION['isAuthenticated'])) || (!$_SESSION['isAuthenticated'])) {
		if ((isset($_REQUEST['username'])) && (isset($_REQUEST['password']))) {
			// SECTION: not yet authenticated, wants to log in

			if ($AUTH->authenticate($_REQUEST['username'], $_REQUEST['password'])) {
				session_regenerate_id(TRUE);
				$_SESSION['isAuthenticated']       = TRUE;
				$_SESSION['userdata']              = array();
				$_SESSION['userdata']['username']  = $AUTH->username;
				$_SESSION['userdata']['email']     = $AUTH->email;
				$_SESSION['userdata']['firstname'] = $AUTH->fname;
				$_SESSION['userdata']['lastname']  = $AUTH->lname;
				$_SESSION['userdata']['sortname']  = $AUTH->sortname;
				// array of institutional group names for this user
				$_SESSION['userdata']['inst_groups'] = array_slice($AUTH->inst_groups, 0); // makes a copy of the array

				// $USER = new User(['username'=>$_SESSION['userdata']['username'],'DB'=>$DB]);
				// now check if user data differs from session data, and if so, update the users db record (this might be a part of the User construct method)
				// $USER->refreshFromDb();
			} else {
				$MESSAGE = 'Log in failed';
			}

			//		# START: Auth Debugging Info
			//			echo "<br /><h2>Development Messages</h2>";
			//			# echo "isAuthenticated = ".$_SESSION['isAuthenticated']."<br />\n";
			//			#if ($AUTH->msg != '' || $AUTH->debug != '') {
			//				// echo the attribute of the $AUTH object
			//				echo "<b>Message found:</b><br />" . $AUTH->msg . "<br />";
			//				echo "<b>Debug Info:</b><br />" . $AUTH->debug . "<br />";
			//			#}
			//		# END: Debugging Info

		} else {
			// SECTION: must be logged in to view pages; otherwise, redirect to index splash page

			if (! strpos(APP_FOLDER ."/index.php", $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'])) {
//				echo APP_FOLDER ."/index.php <br />";
//				echo $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] ."<br />";
				header('Location: ' . APP_FOLDER);
			}
		}
	} else {
		// SECTION: authenticated

		if (isset($_REQUEST['submit_logout'])) {
			// SECTION: wants to log out
			unset($_SESSION['isAuthenticated']);
			unset($_SESSION['userdata']);
			header('Location: ' . APP_FOLDER . '/index.php');
		}
	}


	if (isset($_SESSION['isAuthenticated']) && ($_SESSION['isAuthenticated'])) {
		// SECTION: is logged in

		$DB = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";port=3306", DB_USER, DB_PASS);

		// now create user object
		$USER = new User(['username' => $_SESSION['userdata']['username'], 'DB' => $DB]);

		// now check if user data differs from session data, and if so, update the users db record (this might be a part of the User construct method)
		$USER->refreshFromDb();
		//print_r($USER);
		//print_r($_SESSION['userdata']);
		$USER->updateDbFromAuth($_SESSION['userdata']);
		$USER->refreshFromDb();
		//print_r($USER);
		$USER->loadInstGroups();
		$USER->loadEqGroups();
		//print_r($USER);
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
    <!--Padding for bootstrap.css only, not for bootstrap-responsive.css-->
	<style type="text/css">
        body {
            padding-top: 60px;
            padding-bottom: 40px;
        }
    </style>
	<link rel="stylesheet" href="css/bootstrap-responsive.css" type="text/css">
    <link rel="stylesheet" href="css/style.css" type="text/css" media="all">
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
            <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="brand" href="<?php echo APP_FOLDER; ?>"><?php echo APP_NAME; ?></a>

            <div class="nav-collapse collapse">
                <ul class="nav">
                    <li class="active"><a href="/eqreserve/">Home</a></li>
					<?php
//					if ((isset($_SESSION['isAuthenticated'])) && ($_SESSION['isAuthenticated'])) {
//						if(USER IS ADMIN) {
							//SHOW "ADMIN LINK: Manage Groups/Users"
//						}
//					}
?>
					<li><a href="manage_groups_users.php">Manage Groups/Users</a></li>
                </ul>
				<?php
				if ((isset($_SESSION['isAuthenticated'])) && ($_SESSION['isAuthenticated'])) {
					?>
                    <div id="loggedInControls">
                        <form id="frmLogout" class="navbar-form pull-right" method="post" action="">
                            <span class="muted">You are logged in as <a href="account_management.php"><?php echo $_SESSION['userdata']['username']; ?></a></span>.
                            <input type="submit" id="submit_logout" class="btn" name="submit_logout" value="Sign out" />
                        </form>
                    </div>
					<?php
				} else {
					?>
                    <form id="frmLogin" class="navbar-form pull-right" method="post" action="">
                        <input type="text" id="username" class="span2" name="username" placeholder="Williams Username" value="" />
                        <input type="password" id="password_login" class="span2" name="password" placeholder="Password" value="" />
                        <input type="submit" id="submit_login" class="btn" name="submit_login" value="Sign in" />
                    </form>
					<?php
					if ($MESSAGE) {
						echo "<span class=\"text-warning pull-right\"><br />" . $MESSAGE . "&nbsp;</span>";
					}
				}
				?>
            </div>
        </div>
    </div>
</div>

<div class="container"> <!--div closed in the footer-->

<?php
if ((!isset($_SESSION['isAuthenticated'])) || (!$_SESSION['isAuthenticated'])) {
?>
    <!-- Main hero unit for a primary marketing message or call to action -->
	<div class="hero-unit">
        <h1>Welcome!</h1>
        <p>Please sign in to use this system for scheduling equipment reservations.</p>
        <p><a href="#" class="btn btn-primary btn-large">Learn more &raquo;</a></p>
    </div>
	<!-- Example row of columns -->
    <div class="row">
        <div class="span4">
            <h2>Spectrometers</h2>
            <p>description text here description text here description text here description text here description text here description text here description text here </p>
            <p><a class="btn" href="#">View details &raquo;</a></p>
        </div>
        <div class="span4">
            <h2>Nuclear Toys</h2>
            <p>description text here description text here description text here description text here description text here description text here description text here description text here description text here description text here description text here </p>
            <p><a class="btn" href="#">View details &raquo;</a></p>
        </div>
        <div class="span4">
            <h2>3 D Printer Projects</h2>
            <p>description text here description text here description text here description text here description text here description text here description text here description text here description text here </p>
            <p><a class="btn" href="#">View details &raquo;</a></p>
        </div>
    </div>
<?php
}
?>