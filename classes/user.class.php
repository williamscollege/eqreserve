<?php
class User extends Db_Linked
{
    public $fields = array('user_id','username','fname','lname','email','advisor','notes','flag_is_banned','flag_delete');
    public $primaryKeyField = 'user_id';    
    public $dbTable = 'users';
} 

?>