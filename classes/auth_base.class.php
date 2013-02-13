<?php
class Auth_Base
{
    public function authenticate($user,$pass) {
        return (($user == TESTINGUSER) && ($pass==TESTINGPASSWORD));
    }

    public function getGroups($user) {
    }
}
