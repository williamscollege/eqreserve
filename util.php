<?php

	// general utility functions

	function util_genRandomIdString($len = 128) {
		$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#%^&*()-_=+,.<>?~';
		$id   = '';
		for ($i = 0; $i < $len; $i++) {
			$id .= substr($pool, rand(0, strlen($pool) - 1), 1);
		}
		return $id;
	}

	function util_wipeSession() {
		unset($_SESSION['isAuthenticated']);
		unset($_SESSION['fingerprint']);
		unset($_SESSION['userdata']);

		return;
	}

	function util_redirectToAppHome($n = 0, $l = 0) {
		if ($n > 0 && $l > 0) {
			# redirect: log record, display message
			# TODO: Add database log capability
			header('Location: ' . APP_FOLDER . '/index.php?msg=' . $n);
		}
		elseif ($n > 0 && $l == 0) {
			# redirect: display message
			header('Location: ' . APP_FOLDER . '/index.php?msg=' . $n);
		}
		else {
			# redirect:
			header('Location: ' . APP_FOLDER . '/index.php');
		}
		exit;
	}

	function util_redirectToAppHomeWithPrejudice() {
		util_wipeSession();
		util_redirectToAppHome();
	}

	// this section adds and checks a random id string for the browser and does some checking against that ID string.
	// this makes it much harder to spoof sessions
	function util_doEqReserveIdSecurityCheck() {
		if ((!isset($_COOKIE["eqreserve_id"])) || (!$_COOKIE["eqreserve_id"])) {
			if (isset($_SESSION['eqreserve_id']) && ($_SESSION['eqreserve_id'])) { // the session has an eqreserve id, but there was no cookie set for it - highly suspicious
				// TODO: log and/or message?
				util_redirectToAppHomeWithPrejudice();
			}
			$eqreserve_id = util_genRandomIdString(300);
			setcookie("eqreserve_id", $eqreserve_id);
			$_SESSION['eqreserve_id'] = $eqreserve_id;
		}
		elseif ((!isset($_SESSION['eqreserve_id'])) || ($_COOKIE["eqreserve_id"] != $_SESSION['eqreserve_id'])) {
			// there was an appropriately named cookie, but the value doesn't match the one associated with this session
			// TODO: log and/or message?
			util_redirectToAppHomeWithPrejudice();
		}
	}

	function util_generateRequestFingerprint() {
		util_doEqReserveIdSecurityCheck();

		return md5(FINGERPRINT_SALT . $_SESSION["eqreserve_id"] .
				(isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 18) : 'nouseragent')
		);
	}


	// a quick handle for a slightly complex condition check
	function util_checkAuthentication() {
		return (isset($_SESSION['isAuthenticated']) && ($_SESSION['isAuthenticated']));
	}

	function util_createDbConnection() {
		//print_r($_SERVER);
		if ($_SERVER['SERVER_NAME'] == 'localhost') {
			return new PDO("mysql:host=" . TESTING_DB_SERVER . ";dbname=" . TESTING_DB_NAME . ";port=3306", TESTING_DB_USER, TESTING_DB_PASS);
		}
		return new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";port=3306", DB_USER, DB_PASS);
	}


	function util_displayScreenMessage($n = 0) {
		$screen_messages = [
			10   => "Please sign in."
			, 11 => "Sign in failed."
			, 50 => "You do not have access to that group."
			#, 100 => "User or LDAP something or other message"
		];

		if (array_key_exists($n, $screen_messages)) {
			// show error message
			echo "<div class=\"alert alert-error\">";
			echo "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>";
			echo "<h4>Failed!</h4>";
			echo $screen_messages[$n];
			echo "</div>";
		}
	}


	# validation routine: trim fxn strips (various types of) whitespace characters from the beginning and end of a string
	function util_quoteSmart($value) {
		// stripslashes — Un-quotes a quoted string
		// trim — Strip whitespace (or other characters) from the beginning and end of a string
		if (get_magic_quotes_gpc()) {
			$value = stripslashes($value);
			$value = trim($value);
		}
		return $value;
	}