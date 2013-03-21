<?php

// general utility functions

function util_genRandomIdString($len=128) {
    $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#%^&*()-_=+,.<>?~';
    $id = '';
    for ($i=0;$i<$len;$i++) {
        $id .= substr($pool,rand(0,strlen($pool)-1),1);        
    }
    return $id;
}

function util_wipeSession() {
    unset($_SESSION['isAuthenticated']);
    unset($_SESSION['fingerprint']);
    unset($_SESSION['userdata']);

    return;
}

function util_redirectToAppHome() {
    header('Location: ' . APP_FOLDER . '/index.php');
    exit;
}

function util_redirectToAppHomeWithPrejudice() {
    util_wipeSession();
    util_redirectToAppHome();
}

// this section adds and checks a random id string for the browser and does some checking against that ID string.
// this makes it much harder to spoof sessions
function util_doEqReserveIdSecurityCheck() {
    if ((! isset($_COOKIE["eqreserve_id"])) || (! $_COOKIE["eqreserve_id"])) {
        if (isset($_SESSION['eqreserve_id']) && ($_SESSION['eqreserve_id'])) { // the session has an eqreserve id, but there was no cookie set for it - highly suspicious
            // TODO: log and/or message?
            util_redirectToAppHomeWithPrejudice();
        }
        $eqreserve_id = util_genRandomIdString(300);
        setcookie("eqreserve_id",$eqreserve_id);
        $_SESSION['eqreserve_id'] = $eqreserve_id;
    } 
    elseif ((! isset($_SESSION['eqreserve_id'])) || ($_COOKIE["eqreserve_id"] != $_SESSION['eqreserve_id'])) {
        // there was an appropriately named cookie, but the value doesn't match the one associated with this session
        // TODO: log and/or message?
        util_redirectToAppHomeWithPrejudice();
    }
}

function util_generateRequestFingerprint() {
    util_doEqReserveIdSecurityCheck();
    return md5(FINGERPRINT_SALT . $_SESSION["eqreserve_id"] . substr($_SERVER['HTTP_USER_AGENT'],18));
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