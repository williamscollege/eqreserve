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
		$this->inst_groups	= '';

		//echo "authenticating...\n";
		//echo 'user='.$user."\n";
		//echo 'TESTINGUSER='.TESTINGUSER."\n";
		//echo 'pass='.$pass."\n";
		//echo 'TESTINGPASSWORD='.TESTINGPASSWORD."\n";
		
        return (($user == TESTINGUSER) && ($pass==TESTINGPASSWORD));
    }

    public function getInstGroups($user) {

    }
   
}
