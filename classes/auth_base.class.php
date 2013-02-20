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
	#get_Inst_Groups
	public $inst_groups;

	
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

    public function get_Inst_Groups($user) {
    	$this->inst_groups	= array();

		if ($_SESSION['isAuthenticated'] == true) {
			# continue: session is authenticated
			return true;
		} else {
			# exit: session is not authenticated
			return false;
		}
    }
   
}
