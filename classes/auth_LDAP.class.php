<?php
require_once dirname(__FILE__) . '/auth_base.class.php';

class Auth_LDAP extends Auth_Base
{
    public function authenticate($user,$pass) {
//echo "authenticating...\n";
//echo 'user='.$user."\n";
//echo 'pass='.$pass."\n";
        if (parent::authenticate($user,$pass)) {
            return true;
        }


        return false;
    }
}

?>