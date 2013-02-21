<?php
require_once dirname(__FILE__) . '/auth_base.class.php';

class Auth_LDAP extends Auth_Base
{
    public function authenticate($user,$pass) {
		# check authentication of test user (default condition for testing)
        if (parent::authenticate($user,$pass)) {
            return true;
        }

		# check authentication against LDAP server
		# [run this fxn checkLDAP which utilizes the $AUTH object]
		if ($this->checkLDAP($user,$pass,AUTH_SERVER)) {
			# passes authentication
			return true;
		}
		else {
	        # fails authentication
	        return false;
		}
	}

	public function getInstGroups($user) {
		if (parent::getInstGroups($user)) {
			return true;
		}
		
		if ($_SESSION['isAuthenticated'] == true) {
			# continue: session is authenticated
			return true;
		} else {
			# exit: session is not authenticated
			return false;
		}
	}

	public function checkLDAP($user = "", $pass = "", $ldap_server = AUTH_SERVER) {
		# debugging info: set $chat to true for debugging, false to hide messages
		$chat = true;
		if ($chat) { $this->debug .= "passed - beginning fxn 'checkLDAP'<br />"; }

		// HTTP and HTTPS connections
		# Note: LIVE  SERVER: ldaps://nwldap.williams.edu/; LOCAL SERVER: nwldap.williams.edu

		// Error Levels (while connecting and selecting): -1 will show all errors; 0 will hide all errors
		# TODO: Production Code should hide errors, using param 0
		$errorLevel = error_reporting(-1);
		
		// ensure username supplied
		if (!$user) {
			$this->msg = "No username specified.";
			error_reporting($errorLevel);
			return false;
		}
		# debugging info
		if ($chat) { $this->debug .= "passed - username supplied<br/>"; }


		// ensure password supplied
		if (!$pass) {
			$this->msg = "No password specified.";
			error_reporting($errorLevel);
			return false;
		}
		# debugging info
		if ($chat) { $this->debug .= "passed - password supplied<br/>"; }


		// Connect to the LDAP server
		# Note: LIVE  SERVER: $ldap_server, 636; LOCAL SERVER: $ldap_server
		# TODO: Production Code should ignore errors, using "@" as prefix to: @ldap_connect, @ldap_close
//		if (($connect = @ldap_connect($ldap_server)) == false) {
		if (($connect = ldap_connect($ldap_server)) == false) {
			$this->msg = "Could not connect to the LDAP server ($ldap_server)." . ldap_error($connect);
//			@ldap_close($connect);
			ldap_close($connect);
			error_reporting($errorLevel);
			return false;
		}
		# debugging info
		if ($chat) { $this->debug .= "passed - connected to $ldap_server<br/>"; }


		ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);

		// search for user
		if (($res_id = ldap_search($connect,
			"o=williams",
			"cn=$user",
			array("dn", "sn", "mail", "gecos", "initials", "groupmembership"))) == false
		) {
			$this->msg = "Could not find the user in the LDAP tree.";
			error_reporting($errorLevel);
			return false;
		}
		# debugging info
		if ($chat) { $this->debug .= "passed - LDAP tree searched for username <b>$user</b>.<br/>"; }
		if ($chat) { $this->debug .= "passed - " . ldap_count_entries($connect, $res_id) . " records found.<br/>"; }


		// get the first search result
		if (($entry_id = ldap_first_entry($connect, $res_id)) == false) {
			$this->msg = "User record could not be fetched.";
			error_reporting($errorLevel);
			return false;
		}
		# debugging info
		if ($chat) { $this->debug .= "passed - first record fetched<br/>"; }


		// get the user dn for use in authentication
		if (($user_dn = ldap_get_dn($connect, $entry_id)) == false) {
			$this->msg = "The user-dn could not be fetched.";
			error_reporting($errorLevel);
			return false;
		}
		# debugging info
		if ($chat) { $this->debug .= "passed - user-dn found: <b>$user_dn</b><br/>"; }


		// get the e-mail address from the record
		$mail = ldap_get_values($connect, $entry_id, "mail");
		if (isset($mail[0])) {
			# email retrieved
			$this->mail = $mail[0];
		} else {
			# email constructed
			$this->mail = $user . '@williams.edu';
		}
		# debugging info
		if ($chat) { $this->debug .= "passed - email address retrieved or constructed<br/>"; }


		// Get the name from the record
		$sn       = ldap_get_values($connect, $entry_id, "sn");
		$initials = ldap_get_values($connect, $entry_id, "initials");
		$gecos    = ldap_get_values($connect, $entry_id, "gecos");
		
		$this->name  = (isset($gecos[0]) ? $gecos[0] : ''); // Nicholas Baker or Nicholas C. Baker
		$this->lname = (isset($sn[0]) ? $sn[0] : $this->name); // Baker

		$middle = (isset($initials[0]) ? $initials[0] : ''); // empty or C.
		$middle = preg_replace("/\.$/", "", trim($middle));

		$this->fname = preg_replace("/\s+$this->lname$/", "", $this->name); // strip surname
		$this->fname = preg_replace("/\s+$middle$/", "", $this->fname); // strip initial

		// Get a sortable name - Baker, Nicholas C.
		if ($this->fname && $this->fname != $this->name) {
			if ($middle) {
				$this->sortname = "$this->lname, $this->fname $middle.";
			} else {
				$this->sortname = "$this->lname, $this->fname";
			}
		} else {
			$this->sortname = $this->lname;
		}
		# debugging info
		if ($chat) { $this->debug .= "passed - name retrieved<br/>"; }


		// get the position (STUDENT, FACULTY, STAFF)
		if (preg_match("/ou=(\w+),/", $user_dn, $Matches)) {
			$this->position = $Matches[1];
		} else {
			$this->position = "OTHER";
		}
		
		// Get the groupmemberships from the record
		$inst_groups = array();
		$gmembers = ldap_get_values($connect, $entry_id, "groupmembership");
		for ($i = 0, $size = count($gmembers); $i < $size; ++$i) {
			// ensure no empty items
			if(($tmp = preg_replace('/cn=(.*)\,.*/','$1',$gmembers[$i])) != ''){
				$inst_groups[$i] = $tmp;
			}
		}
		// append the position, as this is another kind of institutional group we want to know about
		$inst_groups[$i + 1] = $this->position;
		$this->inst_groups = $inst_groups;

		// try to log in
		if (($link_id = ldap_bind($connect, $user_dn, $pass)) == false) {
			$this->msg = "The username and password don't match."; //: $user_dn";
			error_reporting($errorLevel);
			return false;
		}
		# debugging info
		if ($chat) { $this->debug .= "passed - bound successfully with user-dn and password<br/>"; }


//		@ldap_close($connect);
		ldap_close($connect);

		error_reporting($errorLevel);

		# debugging info
		if ($chat) { $this->debug .= "passed - completed fxn 'checkLDAP'; return true.<br />"; }
		
		return true;
    }
}

?>
