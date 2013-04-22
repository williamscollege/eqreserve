<?php
	require_once('/institution.cfg.php');
	require_once('/util.php');

    # TODO: validate the request (user logged in, fingerprint checks out)
    if (! $_SESSION['isAuthenticated']) {
        exit;
    }
    $FINGERPRINT = util_generateRequestFingerprint(); // used to prevent/complicate session hijacking ands XSS attacks
    if ($_SESSION['fingerprint'] != $FINGERPRINT) {
        exit;
    }

	# Create database connection object
	$DB = util_createDbConnection();

    $USER = User::getOneFromDb(['username' => $_SESSION['userdata']['username']],$DB);
    if (! $USER->matches_db) {
        exit;
    }
?>