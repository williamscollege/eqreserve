<?php
class Auth_Base
{

	# define attributes of object
	public $err_msg;
	public $err_code;
	public $err_severity;
	
	
    public function authenticate($user,$pass) {
		$this->err_msg = '';
		$this->err_code = '';
		$this->err_severity = '';

		//echo "authenticating...\n";
		//echo 'user='.$user."\n";
		//echo 'TESTINGUSER='.TESTINGUSER."\n";
		//echo 'pass='.$pass."\n";
		//echo 'TESTINGPASSWORD='.TESTINGPASSWORD."\n";
		
        return (($user == TESTINGUSER) && ($pass==TESTINGPASSWORD));
    }

    public function getGroups($user) {
    }
}
