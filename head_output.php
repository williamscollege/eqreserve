<!DOCTYPE html>
<html lang="en">
<head>
	<title><?php echo LANG_APP_NAME . ': ' . $pageTitle; ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="<?php echo LANG_APP_NAME; ?>">
	<meta name="author" content="OIT Project Group">
	<!-- CSS: Framework -->
	<link rel="stylesheet" href="<?php echo PATH_BOOTSTRAP_CSS; ?>" type="text/css" media="all">
	<!-- apply padding for bootstrap.css only, not for bootstrap-responsive.css -->
	<style type="text/css">
		body {
			padding-top: 60px;
			padding-bottom: 40px;
		}
	</style>
	<!-- In our opinion, the BootStrap Responsive theme is optional; feel free to implement by uncommenting, below-->
	<!-- <link rel="stylesheet" href="--><?php //echo PATH_BOOTSTRAP_RESPONSIVE_CSS; ?><!--" type="text/css"> -->
	<!-- CSS: Plugins -->
	<link rel="stylesheet" href="<?php echo PATH_JQUERYUI_CSS; ?>" />
	<link rel="stylesheet" href="<?php echo PATH_BOOTSTRAP_TIMEPICKER_CSS; ?>" type="text/css" media="all">
	<link rel="stylesheet" href="css/WMS_bootstrap_PATCH.css" type="text/css" media="all">
	<!-- jQuery: Framework -->
	<script src="<?php echo PATH_JQUERY_JS; ?>"></script>
	<script src="<?php echo PATH_JQUERYUI_JS; ?>"></script>
	<!-- jQuery: Plugins -->
	<script src="<?php echo PATH_BOOTSTRAP_JS; ?>"></script>
	<script src="<?php echo PATH_BOOTSTRAP_BOOTBOX_JS; ?>"></script>
	<script src="<?php echo PATH_BOOTSTRAP_TIMEPICKER_JS; ?>"></script>
	<script src="<?php echo PATH_JQUERY_VALIDATION_JS; ?>"></script>
	<!-- local JS -->
	<script src="js/eq_reserve_util.js"></script>
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
						<form id="frmSignout" class="navbar-form pull-right" method="post" action="index.php">
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
