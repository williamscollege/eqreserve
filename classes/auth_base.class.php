<?php
class Auth_Base
{

	# define attributes of object
	# authenticate
	public $msg;
	public $position;
	public $mail;
	public $lname;
	public $fname;
	public $name;
	public $sortname;
	public $debug;
	#getGroups
	public $eq_groups;

	
    public function authenticate($user,$pass) {
		$this->msg			= '';
		$this->position		= '';
		$this->mail			= '';
		$this->lname		= '';
		$this->fname		= '';
		$this->name			= '';
		$this->sortname		= '';
		$this->debug		= '';

		//echo "authenticating...\n";
		//echo 'user='.$user."\n";
		//echo 'TESTINGUSER='.TESTINGUSER."\n";
		//echo 'pass='.$pass."\n";
		//echo 'TESTINGPASSWORD='.TESTINGPASSWORD."\n";
		
        return (($user == TESTINGUSER) && ($pass==TESTINGPASSWORD));
    }

    public function getGroups($user) {
    	$this->eq_groups	= array();

		if ($_SESSION['isAuthenticated'] == true) {
			# continue: session is authenticated

			# test data (real data would call the DB and build eq_groups array based on rowcount of groups)
	    	$this->eq_groups = array();
	    	$this->eq_groups[0]['name'] = TESTGROUP1_NAME;
	    	$this->eq_groups[0]['role'] = TESTGROUP1_ROLE;
	    	$this->eq_groups[1]['name'] = TESTGROUP2_NAME;
	    	$this->eq_groups[1]['role'] = TESTGROUP2_ROLE;
	    	$this->eq_groups[2]['name'] = TESTGROUP3_NAME;
	    	$this->eq_groups[2]['role'] = TESTGROUP3_ROLE;
	    	
			return true;
		} else {
			# exit: session is not authenticated
			return false;
		}
    }
}
