<?php
class Auth_LDAP extends Auth_Base
{
    public function authenticate($user,$pass) {
        // does stuff
        if (parent::authenticate($user,$pass)) {
            return true;
        }
    }
}

?>