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
				$MESSAGE = 'Sign in failed';
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
			// SECTION: must be signed in to view pages; otherwise, redirect to index splash page

			if (!strpos(APP_FOLDER . "/index.php", $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'])) {
				//				echo APP_FOLDER ."/index.php <br />";
				//				echo $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] ."<br />";
				header('Location: ' . APP_FOLDER);
			}
		}
	} else {
		// SECTION: authenticated

		if (isset($_REQUEST['submit_signout'])) {
			// SECTION: wants to log out
			unset($_SESSION['isAuthenticated']);
			unset($_SESSION['userdata']);
			header('Location: ' . APP_FOLDER . '/index.php');
		}
	}


	if (isset($_SESSION['isAuthenticated']) && ($_SESSION['isAuthenticated'])) {
		// SECTION: is signed in

		$DB = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";port=3306", DB_USER, DB_PASS);
		//print_r($_SERVER);
		if ($_SERVER['SERVER_NAME'] == 'localhost') {
			$DB = new PDO("mysql:host=" . TESTING_DB_SERVER . ";dbname=" . TESTING_DB_NAME . ";port=3306", TESTING_DB_USER, TESTING_DB_PASS);
		}

		// now create user object
		$USER = new User(['username' => $_SESSION['userdata']['username'], 'DB' => $DB]);
		//		print_r($USER);

		// now check if user data differs from session data, and if so, update the users db record (this might be a part of the User construct method)
		$USER->refreshFromDb();
		//		echo "SHOULD HAVE APPROPRIATE PK ID HERE IF USERNAME ALREADY EXISTS:";
		//		print_r($USER);
		//print_r($_SESSION['userdata']);
		$USER->updateDbFromAuth($_SESSION['userdata']);
		//$USER->refreshFromDb();
		//echo "<pre>"; print_r($USER); echo "</pre>";
		$USER->loadInstGroups();
		$USER->loadEqGroups();

		//		print_r($USER);
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
    <!--padding for bootstrap.css only, not for bootstrap-responsive.css-->
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
    <!--<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/jquery-ui.min.js"></script>-->
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
					if ((isset($_SESSION['isAuthenticated'])) && ($_SESSION['isAuthenticated'])) {
						// Loop through eq_groups and check for admin level access for 1 or more groups. if yes, display link
						$tmp_flag_eq_group_admin = 0;
						foreach ($USER->eq_groups as $eg) {
							if ($eg->name->permission) {
								$tmp_flag_eq_group_admin = 1;
							}
						}
						if ($tmp_flag_eq_group_admin == 1) {
							echo "<li><a href=\"manage_groups_users.php\">Manage Groups/Users</a></li>";
						}
						// TODO: Create db field and user class property: flag_is_system_admin
						if ($USER->flag_is_system_admin == true) {
							?>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin Only <b class="caret"></b></a>
                                <ul class="dropdown-menu">
                                    <li><a href="admin_manage_users.php">Manage Users</a></li>
                                    <li><a href="admin_manage_groups_courses.php">Manage LDAP Groups/Courses</a></li>
                                    <li class="divider"></li>
                                    <li><a href="admin_reports.php">Reports</a></li>
                                </ul>
                            </li>
							<?php
						}
					}
					?>
                </ul>
				<?php
				if ((isset($_SESSION['isAuthenticated'])) && ($_SESSION['isAuthenticated'])) {
					?>
                    <div id="signedInControls">
                        <form id="frmSignout" class="navbar-form pull-right" method="post" action="">
                            <span class="muted">You are signed in as <a href="account_management.php"><?php echo $_SESSION['userdata']['username']; ?></a></span>.
                            <input type="submit" id="submit_signout" class="btn" name="submit_signout" value="Sign out" />
                        </form>
                    </div>
					<?php
				} else {
					?>
                    <form id="frmSignin" class="navbar-form pull-right" method="post" action="">
                        <input type="text" id="username" class="span2" name="username" placeholder="Williams Username" value="" />
                        <input type="password" id="password_login" class="span2" name="password" placeholder="Password" value="" />
                        <input type="submit" id="submit_signin" class="btn" name="submit_signin" value="Sign in" />
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

