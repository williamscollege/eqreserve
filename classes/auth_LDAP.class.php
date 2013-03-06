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

    public function getInstGroupsFromAuthSource($username) {
        $group_names = parent::getInstGroupsFromAuthSource($username);
        if (count($group_names) > 0) {
            return $group_names;
        }

        echo "TODO: implement fetching of group names<br/>\n";
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


		# get all entries
		if (($results = ldap_get_entries($connect, $res_id)) == false) {
			$this->msg = "User record could not be fetched.";
			error_reporting($errorLevel);
			return false;
		}
		else if ($results['count'] == 0) {
			# no results - user does not exists
			$this->msg = "User record could not be fetched: no (count == 0) data in search results";
			error_reporting($errorLevel);
			return false;
		}
		else if ($results['count'] > 1) {
			# multiple results - username appears more than once - invalid
			$this->msg = "User record appears more than once - invalid";
			error_reporting($errorLevel);
			return false;
		}

		# get the single entry we actually want
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
			$this->email = $mail[0];
		} else {
			# email constructed
			$this->email = $user . '@williams.edu';
		}
		# debugging info
		if ($chat) { $this->debug .= "passed - email address retrieved or constructed<br/>"; }


		// Get the name from the record
		$sn       = ldap_get_values($connect, $entry_id, "sn");
		$initials = ldap_get_values($connect, $entry_id, "initials");
		$gecos    = ldap_get_values($connect, $entry_id, "gecos");
		
        //$this->name  = (isset($gecos[0]) ? $gecos[0] : ''); // Nicholas Baker or Nicholas C. Baker
        $full_name  = (isset($gecos[0]) ? $gecos[0] : ''); // Nicholas Baker or Nicholas C. Baker
        $this->lname = (isset($sn[0]) ? $sn[0] : $full_name); // Baker

		$middle = (isset($initials[0]) ? $initials[0] : ''); // empty or C.
		$middle = preg_replace("/\.$/", "", trim($middle));

        $this->fname = preg_replace("/\s+$this->lname$/", "", $full_name); // strip surname
		$this->fname = preg_replace("/\s+$middle$/", "", $this->fname); // strip initial

		// Get a sortable name - Baker, Nicholas C.
        if ($this->fname && $this->fname != $full_name) {
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


		
        // Get the groupmemberships from the record
        $inst_groups = array();
		$gmembers = ldap_get_values($connect, $entry_id, "groupmembership");
		# $group_finder_pattern = '/cn=([^\,]*)\,.*/';	// match all groups
		$group_finder_pattern = '/cn=((Everyone|Jesup|[A-Z]{4}-[0-9]{3}|\d\dstudents)[^\,]*)/'; // match only desired groups, exclude all others
		for ($i = 0; $i < $gmembers['count']; ++$i) {
			// ensure no empty items
			if (preg_match($group_finder_pattern,$gmembers[$i],$matches)) { 
				array_push($inst_groups,$matches[1]);
			} 
		}
		// append the position, as this is another kind of institutional group we want to know about
        // get the position (STUDENT, FACULTY, STAFF)
        $position= "OTHER";        
        if (preg_match("/ou=(\w+),/", $user_dn, $Matches)) {
            $position = $Matches[1];
        }

		array_push($inst_groups,$position);
		$this->inst_groups = $inst_groups;
		// print_r($inst_groups); // debugging info
		
		// try to Sign in
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
