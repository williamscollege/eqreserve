<?php
	session_start();

	require_once('institution.cfg.php');
	require_once('lang.cfg.php');
	require_once('classes/user.class.php');
	require_once('auth.cfg.php');
	require_once('util.php');
    require_once('calendar_util.php');

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
