<?php
	class Auth_Base {

		# define attributes of object
		# authenticate
		public $username;
		public $fname;
		public $lname;
		public $sortname;
		public $email;
		public $inst_groups;

		public $msg;
		public $debug;

		public static $TEST_USERNAME = TESTINGUSER;
		public static $TEST_FNAME = 'Violet';
		public static $TEST_LNAME = 'Bovine';
		public static $TEST_SORTNAME = 'Bovine, Violet C.';
		public static $TEST_EMAIL = 'vbovine@institution.edu';
		public static $TEST_INST_GROUPS = ['Everyone', 'STUDENT', 'helpdesk-staff', 'BIOL-710', '13S-BIOL-710-01', 'testInstGroup1'];

		// TAKES: this function takes two parameters, a username and a password, both strings
		// DOES: checks the username and password against an authentication source (details implemented by subclass); if the authentication checks out, the various attributes of this object are populated with the appropriate user data
		// RETURNS: true if the user was authenticated, false otherwise
		public function authenticate($username, $pass) {
			$this->username    = $username;
			$this->email       = '';
			$this->fname       = '';
			$this->lname       = '';
			$this->inst_groups = array();

			$this->msg   = '';
			$this->debug = '';

			//echo "authenticating...\n";
			//echo 'user='.$user."\n";
			//echo 'TESTINGUSER='.TESTINGUSER."\n";
			//echo 'pass='.$pass."\n";
			//echo 'TESTINGPASSWORD='.TESTINGPASSWORD."\n";

			if (($username == TESTINGUSER) && ($pass == TESTINGPASSWORD)) {
				$this->fname       = self::$TEST_FNAME;
				$this->lname       = self::$TEST_LNAME;
				$this->sortname    = self::$TEST_SORTNAME;
				$this->email       = self::$TEST_EMAIL;
				$this->inst_groups = array_slice(self::$TEST_INST_GROUPS, 0);
				return TRUE;
			}
		}

		// TAKES: this function takes one parameters, a username as a string
		// RETURNS: a array of strings, each string the name of an institutional group
		public function getInstGroupsFromAuthSource($username) {
			echo "TODO: implement group names for testing user<br/>\n";
		}

	}