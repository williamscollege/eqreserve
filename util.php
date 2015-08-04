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

    function util_genRandomAlphNumString($len = 128) {
    $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
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
		unset($_SESSION['eqreserve_id']);
		$_COOKIE['eqreserve_id'] = "";
		setcookie("eqreserve_id", "", time() - 3600);

		return;
	}

	function util_redirectToAppHome($status = "", $num = 0, $log = 0) {
		// ensure value conforms to expectations
		if ($status != "success" && $status != "failure" && $status != "info") {
			# security: ensure status = ""
			$status = "";
		}
		if ($num > 0 && $log > 0) {
			# redirect: log record, display message
			# TODO: Add database log capability
			header('Location: ' . APP_FOLDER . '/index.php?' . $status . '=' . $num);
		}
		elseif ($num > 0 && $log == 0) {
			# redirect: display message
			header('Location: ' . APP_FOLDER . '/index.php?' . $status . '=' . $num);
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

	function library_ScreenMessages($num = 0) {
		$screen_messages = [
			10   => "Please sign in."
			, 11 => "Sign in failed."
			, 20 => "Missing required parameter: equipment group id (eid)"
			, 50 => "You do not have access to that group."
			, 51 => "You do not have access to that institutional group."
			, 52 => "You do not have access to that schedule."
			, 53 => "You do not have access to that account."
			, 60 => "Record does not exist in database"
			, 61 => "Record already exists in database"
			#, 100 => "User or LDAP something or other message"
		];
		if (array_key_exists($num, $screen_messages)) {
			return $screen_messages[$num];
		}
	}

	function util_displaySuccessMessage($num = 0) {
		$message = library_ScreenMessages($num);
		if ($message) {
			// success message
			echo "<div class=\"alert alert-success\">";
			echo "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>";
			echo "<h4>Success!</h4>";
			echo $message;
			echo "</div>";
		}
	}

	function util_displayFailureMessage($num = 0) {
		$message = library_ScreenMessages($num);
		if ($message) {
			// failure message
			echo "<div class=\"alert alert-error\">";
			echo "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>";
			echo "<h4>Failed!</h4>";
			echo $message;
			echo "</div>";
		}
	}

	function util_displayInfoMessage($num = 0) {
		$message = library_ScreenMessages($num);
		if ($message) {
			// info message
			echo "<div class=\"alert alert-info\">";
			echo "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>";
			echo "<h4>Oops!</h4>";
			echo $message;
			echo "</div>";
		}
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
		//        TODO: figure out how to handle this for command line scripts (possibly build this directly into the command line header, but still need to resolve test vs live)
		//		if ((array_key_exists('SERVER_NAME',$_SERVER)) && ($_SERVER['SERVER_NAME'] == 'localhost')) {
		if ($_SERVER['SERVER_NAME'] == 'localhost') {
			return new PDO("mysql:host=" . TESTING_DB_SERVER . ";dbname=" . TESTING_DB_NAME . ";port=3306", TESTING_DB_USER, TESTING_DB_PASS);
		}
		return new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";port=3306", DB_USER, DB_PASS);
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

		# convert minute to pretty words using: days, hours, minutes
		function util_minutesToWords($minutes) {
            $ret = "";

            /*** get the days ***/
            $days = floor($minutes/ (60 * 24));
            /*** get the weeks ***/
            if($days>7){
                $weeks = floor($days/7);
                if($weeks == 1){
                    $ret .= "$weeks week ";
                }else{
                    $ret .= "$weeks weeks ";
                }
                $days = floor($days%7);
            }
            if ($days > 0) {
                if($days == 1){
                    $ret .= "$days day ";
                }else{
                    $ret .= "$days days ";
                }
            }

            /*** get the hours ***/
            $hours = floor(($minutes / 60) % 24);
            if ($hours > 0) {
                if($hours == 1){
                    $ret .= "$hours hour ";
                }else{
                    $ret .= "$hours hours ";
                }
            }

            /*** get the minutes ***/
            $mins = floor($minutes % 60);
            if ($mins > 0) {
                $ret .= "$mins minutes ";
            }

            return $ret;
		}

	# Output an object wrapped with HTML PRE tags for pretty output
	function util_prePrintR($obj) {
		echo "<pre>";
		print_r($obj);
		echo "</pre>";
		return TRUE;
	}

	/**
	 * takes: a time string of the form YYYY-MM-DD HH:MI:SS (i.e. as it comes from MySQL)
	 * returns: a hash with the following keys-
	 * YYYY - the year
	 * Y - the year
	 * MM - the month with 2 characters (leading 0)
	 * M - the month with 1 character if < 10
	 * DD - the day with 2 characters
	 * D - the day with 1 character if < 10
	 * hh - the 24-clock hour with 2 characters
	 * h - the 24-clock hour with 1 character if < 10
	 * hhap - the 12-clock with 2 characters
	 * hap - the 12-clock with 1 character if < 10
	 * ap - AM or PM
	 * mm - the minutes with 2 characters
	 * m - the minutes with 1 character if < 10
	 * ss - the seconds with 2 characters
	 * s - the seconds with 1 character if < 10
	 */
	function util_processTimeString($ts) {
		$parts = preg_split('/[-: ]/', $ts);

		$res = [
			'YYYY' => $parts[0],
			'Y'    => $parts[0],
			'MM'   => $parts[1],
			'M'    => $parts[1],
			'DD'   => $parts[2],
			'D'    => $parts[2],
			'hh'   => $parts[3],
			'h'    => $parts[3],
			'hhap' => $parts[3],
			'hap'  => $parts[3],
			'ap'   => ($parts[3] < 12) ? 'AM' : 'PM',
			'mi'   => $parts[4],
			'm'    => $parts[4],
			'ss'   => $parts[5],
			's'    => $parts[5]
		];

		if ($res['hhap'] > 12) {
			$res['hhap'] -= 12;
		}
		if ($res['hhap'] < 1) {
			$res['hhap'] = '12';
		}
		if ($res['hap'] > 12) {
			$res['hap'] -= 12;
		}
		if ($res['hap'] < 1) {
			$res['hap'] = '12';
		}

		$res['M'] = preg_replace('/^0+/', '', $res['M']);

		$res['D'] = preg_replace('/^0+/', '', $res['D']);

		$res['h'] = preg_replace('/^0+/', '', $res['h']);
		if (!$res['h']) {
			$res['h'] = '0';
		}

		$res['hap'] = preg_replace('/^0+/', '', $res['hap']);
		if (!$res['hap']) {
			$res['hap'] = '0';
		}

		$res['m'] = preg_replace('/^0+/', '', $res['m']);
		if (!$res['m']) {
			$res['m'] = '0';
		}

		$res['s'] = preg_replace('/^0+/', '', $res['s']);
		if (!$res['s']) {
			$res['s'] = '0';
		}

		$res['date'] = $res['Y'] . '/' . $res['M'] . '/' . $res['D'];

		return $res;
	}

	function util_timeRangeString($tstart, $tstop) {
		if (!is_array($tstart)) {
			$tstart = util_processTimeString($tstart);
		}
		if (!is_array($tstop)) {
			$tstop = util_processTimeString($tstop);
		}

		$first_part  = $tstart['date'] . ' ' . $tstart['hap'] . ':' . $tstart['mi'];
		$second_part = '';

		if ($tstart['date'] != $tstop['date']) {
			$first_part .= ' ' . $tstart['ap'];
			$second_part = $tstop['date'] . ' ' . $tstop['hap'] . ':' . $tstop['mi'] . ' ' . $tstop['ap'];
		}
		elseif ($tstart['ap'] != $tstop['ap']) {
			$first_part .= ' ' . $tstart['ap'];
			$second_part = $tstop['hap'] . ':' . $tstop['mi'] . ' ' . $tstop['ap'];
		}
		else {
			$second_part = $tstop['hap'] . ':' . $tstop['mi'] . ' ' . $tstop['ap'];
		}

		return "$first_part-$second_part";
	}

    //Same as above without the date
    function util_timeRangeStringShort($tstart, $tstop){
        if (!is_array($tstart)) {
            $tstart = util_processTimeString($tstart);
        }
        if (!is_array($tstop)) {
            $tstop = util_processTimeString($tstop);
        }

        $first_part  = $tstart['hap'] . ':' . $tstart['mi'];
        $second_part = '';

        if ($tstart['date'] != $tstop['date']) {
            $first_part .= ' ' . $tstart['ap'];
            $second_part = $tstop['hap'] . ':' . $tstop['mi'] . ' ' . $tstop['ap'];
        }
        elseif ($tstart['ap'] != $tstop['ap']) {
            $first_part .= ' ' . $tstart['ap'];
            $second_part = $tstop['hap'] . ':' . $tstop['mi'] . ' ' . $tstop['ap'];
        }
        else {
            $second_part = $tstop['hap'] . ':' . $tstop['mi'] . ' ' . $tstop['ap'];
        }

        return "$first_part-$second_part";
    }


	#####################################
	# Array Map Object Queries
	#####################################

	function util_returnUserID($e) {
		return $e->user_id;
	}


/***********FUNCTIONS TO FETCH DATE VALUES***********/
function util_getMonthNumFromDate($date){
    return $date->format('m');
}

function util_getCurrentMonthNum(){
    return date("m");
}

function util_getPrevMonthNum($date){
    $interval = new DateInterval('P1M');
    $date->sub($interval);
    return $date->format('m');
}

function util_getNextMonthNum($date){
    $interval = new DateInterval('P1M');
    $date->add($interval);
    return $date->format('m');
}

function util_getYearNumFromDate($someDate){
    return $someDate->format('Y');
}

function util_getCurrentYearNum(){
    return date("Y");
}

function util_getPrevYearNum($someDate){
    $interval = new DateInterval('P1Y');
    $someDate->sub($interval);
    return $someDate->format('Y');
}

function util_getNextYearNum($someDate){
    $interval = new DateInterval('P1Y');
    $someDate->add($interval);
    return $someDate->format('Y');
}


/***** convert durations types (5M, 2H, 3D) to integer minute form ******/
function util_durToInt($schedDur)
{
    $intReturn = 1;
    $length = strlen($schedDur);

    if(substr($schedDur, $length-1) == 'M'){
        $intReturn = intval(substr($schedDur, 0, $length-1));
    }elseif(substr($schedDur, $length-1) == 'H'){
        $intReturn = intval(substr($schedDur, 0, $length-1));
        $intReturn = $intReturn * 60;
    }elseif(substr($schedDur, $length-1) == 'D'){
        $intReturn = intval(substr($schedDur, 0, $length-1));
        $intReturn = $intReturn * 60 * 24;
    }

    return $intReturn;
}

/***** convert duration types (5M, 2H, 3D) to string form ******/
function util_durToString($schedDur)
{
    $strReturn = '';
    $length = strlen($schedDur);

    if(substr($schedDur, $length-1) == 'M'){
        $strReturn = substr($schedDur, 0, $length-1);
        $strReturn .= ' minutes';
    }elseif(substr($schedDur, $length-1) == 'H'){
        $strReturn = substr($schedDur, 0, $length-1);
        $strReturn .= ' hours';
    }elseif(substr($schedDur, $length-1) == 'D') {
        if(intval(substr($schedDur,0,$length-1)) == 1){
            $strReturn = '24 hours';
        }elseif(intval(substr($schedDur,0,$length-1)) > 7){
            $strReturn = intval(substr($schedDur, 0, $length - 1))/7;
            $strReturn .= ' weeks';
        }elseif(intval(substr($schedDur,0,$length-1)) == 7) {
            $strReturn = '1 week (7 days)';
        }else{
            $strReturn = substr($schedDur, 0, $length - 1);
            $strReturn .= ' days';
        }
    }

    return $strReturn;
}

/***** reduce minutes into strings (48 hours = 2 days) ******/
function util_hourToString($hour)
{

}