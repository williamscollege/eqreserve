<?php
class Role extends Db_Linked
{
    public $fields = array('role_id','name','flag_delete');
    public $primaryKeyField = 'role_id';    
    public $dbTable = 'roles';


} 
?>