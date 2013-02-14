<?php
require_once dirname(__FILE__) . '/auth_base.class.php';

class Auth_LDAP extends Auth_Base
{
    public function authenticate($user,$pass) {
		//echo "authenticating...\n";
		//echo 'user='.$user."\n";
		//echo 'pass='.$pass."\n";

		# check authentication of test user (default condition for testing)
        if (parent::authenticate($user,$pass)) {
            return true;
        }

		# check authentication against LDAP server
		# [run this fxn checkLDAP which utilizes the $AUTH object]
		if ($this->checkLDAP($user,$pass,FALSE,AUTH_SERVER)) {
			# passes authentication
			return true;
		}
		else {
	        # fails authentication
	        return false;		
		}
	}


	public function checkLDAP($user = "", $pass = "", $chat = FALSE, $ldap_server = AUTH_SERVER) {
		// Set $chat to true for debugging, false to hide messages
		$chat = TRUE;

		// Notes for HTTP and HTTPS connections
		# DKC: LIVE  SERVER: ldaps://nwldap.williams.edu/
		# DKC: LOCAL SERVER: nwldap.williams.edu

		// Information will be returned in the following global variables:
		# TODO: Convert these global variables into attributes of the object $AUTH, utilizing the parent class
//		global $ldap_msg, $ldap_position, $ldap_mail, $ldap_lname, $ldap_fname, $ldap_name, $ldap_sortname;
		global $ldap_msg, $ldap_position, $ldap_mail, $ldap_lname, $ldap_fname, $ldap_name, $ldap_sortname;

		// Don't show any errors while connecting and selecting.
		$errorLevel = error_reporting(0);

		// Make sure they supplied a username
		if (!$user) {
			$this->err_msg = "No username specified.";
			error_reporting($errorLevel);
			return FALSE;
		}

		// Make sure they supplied a password
		if (!$pass) {
			$this->err_msg = "No password specified.";
			error_reporting($errorLevel);
			return FALSE;
		}

# $this->err_msg = "forced message prior to ldap connect test";
# return false;
		// Connect to the LDAP server.
		# DKC: LIVE  SERVER: $ldap_server, 636
		# DKC: LOCAL SERVER: $ldap_server
		if (($connect = @ldap_connect($ldap_server)) == FALSE) {
			$this->err_msg = "Could not connect to the LDAP server ($ldap_server)." . ldap_error($connect);
			# if ($chat) echo ldap_error($connect);
			@ldap_close($connect);
			error_reporting($errorLevel);
			return FALSE;
		}
		# if ($chat) echo "Connected to $ldap_server.<br/>";

		ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);

		// search for user
		if (($res_id = ldap_search($connect,
			"o=williams",
			"cn=$user",
			array("dn", "sn", "mail", "gecos", "initials"))) == FALSE
		) {
			$this->err_msg = "Could not find the user in the LDAP tree.";
			error_reporting($errorLevel);
			return FALSE;
		}
		# if ($chat) echo "LDAP tree searched for username <b>$user</b>. <br/>";
		# if ($chat) echo ldap_count_entries($connect, $res_id) . " records found.<br/>";

		// get the first search result
		if (($entry_id = ldap_first_entry($connect, $res_id)) == FALSE) {
			$this->err_msg = "User record could not be fetched.";
			error_reporting($errorLevel);
			return FALSE;
		}
		# if ($chat) echo "First record fetched.<br/>";

		// get the user dn for use in authentication
		if (($user_dn = ldap_get_dn($connect, $entry_id)) == FALSE) {
			$this->err_msg = "The user-dn could not be fetched.";
			error_reporting($errorLevel);
			return FALSE;
		}
		# if ($chat) echo "user-dn found: <b>$user_dn</b><br/>";

		// get the e-mail address from the record
		$mail = ldap_get_values($connect, $entry_id, "mail");
		if (isset($mail[0])) {
			$ldap_mail = $mail[0];
			# if ($chat) echo "E-mail retrieved: <b>$ldap_mail</b><br />";
		} else {
			$ldap_mail = $user . '@williams.edu';
			# if ($chat) echo "E-mail constructed: <b>$ldap_mail</b><br />";
		}

		// Get the name from the record
		$sn       = ldap_get_values($connect, $entry_id, "sn");
		$initials = ldap_get_values($connect, $entry_id, "initials");
		$gecos    = ldap_get_values($connect, $entry_id, "gecos");

		$ldap_name  = (isset($gecos[0]) ? $gecos[0] : ''); // Nicholas Baker or Nicholas C. Baker
		$ldap_lname = (isset($sn[0]) ? $sn[0] : $ldap_name); // Baker

		$middle = (isset($initials[0]) ? $initials[0] : ''); // empty or C.
		$middle = preg_replace("/\.$/", "", trim($middle));

		$ldap_fname = preg_replace("/\s+$ldap_lname$/", "", $ldap_name); // strip surname
		$ldap_fname = preg_replace("/\s+$middle$/", "", $ldap_fname); // strip initial

		// Get a sortable name - Baker, Nicholas C.
		if ($ldap_fname && $ldap_fname != $ldap_name) {
			if ($middle) {
				$ldap_sortname = "$ldap_lname, $ldap_fname $middle.";
			} else {
				$ldap_sortname = "$ldap_lname, $ldap_fname";
			}
		} else {
			$ldap_sortname = $ldap_lname;
		}
		# if ($chat) echo "Name retrieved: <b>$ldap_sortname</b><br />";

		// get the position (STUDENT, FACULTY, STAFF)
		if (preg_match("/ou=(\w+),/", $user_dn, $Matches)) {
			$ldap_position = $Matches[1];
			# if ($chat) echo "Position retrieved: <b>$ldap_position</b>.<br/>";
		} else {
			$ldap_position = "OTHER";
		}

		// try to log in
		if (($link_id = ldap_bind($connect, $user_dn, $pass)) == FALSE) {
			$this->err_msg = "The username and password don't match."; //: $user_dn";
			error_reporting($errorLevel);
			return FALSE;
		}
		# if ($chat) echo "Bound successfully with user-dn and password.<br/>";

		@ldap_close($connect);

		error_reporting($errorLevel);
		return TRUE;

	    # safety check: fails authentication
	    return false;		
    }
}

?>
