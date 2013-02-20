<?php
class User extends Db_Linked
{
    /////////////////////////////////////////////////////
    // this array defined the db-tied properties of this object
    // due to use of magic function __get and __set they may be accessed as if
    // real properties after object creations. E.g.
    //  var $efoo = new Eq_Group();
    //  echo $efoo->name;

    public $fields = array('user_id','username','fname','lname','email','advisor','notes','flag_is_banned','flag_delete');
    public $primaryKeyField = 'user_id';    
    public $dbTable = 'users';
} 

?>