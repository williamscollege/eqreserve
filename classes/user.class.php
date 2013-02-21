<?php
class User extends Db_Linked
{
    public static $fields = array('user_id','username','fname','lname','email','advisor','notes','flag_is_banned','flag_delete');
    public static $primaryKeyField = 'user_id';    
    public static $dbTable = 'users';
} 

?>