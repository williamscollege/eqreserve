<?php
class Auth_Base
{
    public function authenticate($user,$pass) {
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
