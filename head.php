<?php
session_start();

require_once('institution.cfg.php');
require_once('lang.cfg.php');
require_once('/classes/user.class.php');
require_once('auth.cfg.php');
require_once('util.php');

$FINGERPRINT = util_generateRequestFingerprint(); // used to prevent/complicate session hijacking ands XSS attacks

if ((!isset($_SESSION['isAuthenticated'])) || (!$_SESSION['isAuthenticated'])) {
	if ((isset($_REQUEST['username'])) && (isset($_REQUEST['password']))) { // SECTION: not yet authenticated, wants to log in

		if ($AUTH->authenticate($_REQUEST['username'], $_REQUEST['password'])) {
			session_regenerate_id(TRUE);
			$_SESSION['isAuthenticated']       = TRUE;
			$_SESSION['fingerprint']           = $FINGERPRINT;
			$_SESSION['userdata']              = array();
			$_SESSION['userdata']['username']  = $AUTH->username;
			$_SESSION['userdata']['email']     = $AUTH->email;
			$_SESSION['userdata']['firstname'] = $AUTH->fname;
			$_SESSION['userdata']['lastname']  = $AUTH->lname;
			$_SESSION['userdata']['sortname']  = $AUTH->sortname;
			// array of institutional group names for this user
			$_SESSION['userdata']['inst_groups'] = array_slice($AUTH->inst_groups, 0); // makes a copy of the array
			util_redirectToAppHome();
		}
		else {
			util_redirectToAppHome('failure', 11);
		}

	}
	else {
		// SECTION: must be signed in to view pages; otherwise, redirect to index splash page
		if (!strpos(APP_FOLDER . "/index.php", $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'])) {
			// TODO: add logging?
			util_redirectToAppHome('info', 10);
		}
	}
}
else { // SECTION: authenticated
	if ($_SESSION['fingerprint'] != $FINGERPRINT) {
		// TODO: add logging?
		util_redirectToAppHomeWithPrejudice();
	}
	if (isset($_REQUEST['submit_signout'])) {
		// SECTION: wants to log out
		util_wipeSession();
		util_redirectToAppHome();
		// NOTE: the above is the same as util_redirectToAppHomeWithPrejudice, but this code is easier to follow/read when the two parts are shown here
	}
}

$IS_AUTHENTICATED = util_checkAuthentication();
if ($IS_AUTHENTICATED) { // SECTION: is signed in
	$DB = util_createDbConnection();

	// now create user object
	$USER = new User(['username' => $_SESSION['userdata']['username'], 'DB' => $DB]);
	//echo "<pre>"; print_r($USER); echo "</pre>";

	// now check if user data differs from session data, and if so, update the users db record (this might be a part of the User construct method)
	$USER->refreshFromDb();
	//echo "<pre>"; print_r($USER); echo "</pre>";
	//print_r($_SESSION['userdata']);
	$USER->updateDbFromAuth($_SESSION['userdata']);
	//echo "<pre>"; print_r($USER); echo "</pre>";
	$USER->loadInstGroups();
	$USER->loadEqGroups();

	//echo "<pre>"; print_r($USER); echo "</pre>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title><?php echo LANG_APP_NAME . ': ' . $pageTitle;?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="<?php echo LANG_APP_NAME; ?>">
	<meta name="author" content="OIT Project Group">
	<!-- CSS: Framework -->
	<link rel="stylesheet" href="css/bootstrap.min.css" type="text/css" media="all">
	<!--padding for bootstrap.css only, not for bootstrap-responsive.css-->
	<style type="text/css">
		body {
			padding-top: 60px;
			padding-bottom: 40px;
		}
	</style>
	<link rel="stylesheet" href="css/bootstrap-responsive.min.css" type="text/css">
	<!-- CSS: Plugins -->
	<link rel="stylesheet" href="css/WMS_bootstrap_PATCH.css" type="text/css" media="all">
	<!-- jQuery: Framework -->
	<script src="<?php echo URL_JQUERY_CDN; ?>"></script>
	<!--<script src="<?php echo URL_JQUERYUI_CDN; ?>"></script>-->
	<!-- jQuery: Plugins -->
	<script src="js/bootstrap.min.js"></script>
	<script src="<?php echo URL_JQUERY_VALIDATE_CDN; ?>"></script>
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
			<a class="brand" href="<?php echo APP_FOLDER; ?>"><?php echo LANG_APP_NAME; ?></a>

			<div class="nav-collapse collapse">
				<ul class="nav">
					<li class="active"><a href="/eqreserve/"><i class="icon-home icon-white"></i> Home</a></li>
					<?php
					if ($IS_AUTHENTICATED) {
						# is manager of any group?
						$eg_manager = 0;
						foreach ($USER->eq_groups as $eg) {
							if ($eg->permission->role->priority == 1) {
								$eg_manager = 1;
							}
						}
						if ($eg_manager == 1) {
							echo "<li><a href=\"manage_groups_users.php\">Manage Groups/Users</a></li>";
						}

						# is system admin?
						if ($USER->flag_is_system_admin) {
							?>
							<li class="dropdown">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-wrench icon-white"></i>
									Admin Only <b class="caret"></b></a>
								<ul class="dropdown-menu">
									<li><a href="admin_manage_users.php"><i class="icon-pencil"></i> Manage Users</a>
									</li>
									<li><a href="admin_manage_groups_courses.php"><i class="icon-pencil"></i> Manage
											LDAP Groups/Courses</a></li>
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
				if ($IS_AUTHENTICATED) {
					?>
					<form id="frmSignout" class="navbar-form pull-right" method="post" action="">
						<span class="muted">Signed in: <a href="account_management.php" title="My Account"><?php echo $_SESSION['userdata']['username']; ?></a></span>.
						<input type="submit" id="submit_signout" class="btn" name="submit_signout" value="Sign out" />
					</form>
				<?php
				}
				else {
					?>
					<form id="frmSignin" class="navbar-form pull-right" method="post" action="">
						<input type="text" id="username" class="span2" name="username" placeholder="Williams Username" value="" />
						<input type="password" id="password_login" class="span2" name="password" placeholder="Password" value="" />
						<input type="submit" id="submit_signin" class="btn" name="submit_signin" value="Sign in" />
					</form>
				<?php
				}
				?>
			</div>
		</div>
	</div>
</div>

<div class="container"> <!--div closed in the footer-->
	<?php
	// display screen message?
	if (isset($_REQUEST["success"]) && (is_numeric($_REQUEST["success"]))) {
		if (util_displaySuccessMessage($_REQUEST["success"])) {
			;
		}
	}
	elseif (isset($_REQUEST["failure"]) && (is_numeric($_REQUEST["failure"]))) {
		if (util_displayFailureMessage($_REQUEST["failure"])) {
			;
		}
	}
	elseif (isset($_REQUEST["info"]) && (is_numeric($_REQUEST["info"]))) {
		if (util_displayInfoMessage($_REQUEST["info"])) {
			;
		}
	}
	?>
