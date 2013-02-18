<?php
class Auth_Base
{

	# define attributes of object
	public $auth_msg;
	public $auth_position;
	public $auth_mail;
	public $auth_lname;
	public $auth_fname;
	public $auth_name;
	public $auth_sortname;
	public $auth_debug;

	
    public function authenticate($user,$pass) {
		$this->auth_msg			= '';
		$this->auth_position	= '';
		$this->auth_mail		= '';
		$this->auth_lname		= '';
		$this->auth_fname		= '';
		$this->auth_name		= '';
		$this->auth_sortname	= '';
		$this->auth_debug		= '';

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
