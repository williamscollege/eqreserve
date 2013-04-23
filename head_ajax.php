<?php
    require_once('/institution.cfg.php');
	require_once('/util.php');
    require_once('/classes/user.class.php');

    session_start();

    # TODO: validate the request (user logged in, fingerprint checks out)
    if (! $_SESSION['isAuthenticated']) {
        echo 'not authenticated';
        exit;
    }
    $FINGERPRINT = util_generateRequestFingerprint(); // used to prevent/complicate session hijacking ands XSS attacks
    if ($_SESSION['fingerprint'] != $FINGERPRINT) {
        echo 'bad fingerprint';
        exit;
    }

	# Create database connection object
	$DB = util_createDbConnection();

    $USER = User::getOneFromDb(['username' => $_SESSION['userdata']['username']],$DB);
    if (! $USER->matchesDb) {
        echo 'user did not load correctly';
        exit;
    }
?>