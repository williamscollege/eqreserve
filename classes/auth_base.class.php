<?php
class Auth_Base
{

	# define attributes of object
	public $msg;
	public $position;
	public $mail;
	public $lname;
	public $fname;
	public $name;
	public $sortname;
	public $debug;

	
    public function authenticate($user,$pass) {
		$this->msg			= '';
		$this->position	= '';
		$this->mail		= '';
		$this->lname		= '';
		$this->fname		= '';
		$this->name		= '';
		$this->sortname	= '';
		$this->debug		= '';

		//echo "authenticating...\n";
		//echo 'user='.$user."\n";
		//echo 'TESTINGUSER='.TESTINGUSER."\n";
		//echo 'pass='.$pass."\n";
		//echo 'TESTINGPASSWORD='.TESTINGPASSWORD."\n";
		
        return (($user == TESTINGUSER) && ($pass==TESTINGPASSWORD));
    }

    public function getGroups($user) {
    	$this->group_name		= '';

    }
}
