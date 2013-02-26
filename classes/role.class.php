<?php
require_once dirname(__FILE__) . '/db_linked.class.php';


class Role extends Db_Linked
{
    public static $fields = array('role_id','name','flag_delete');
    public static $primaryKeyField = 'role_id';    
    public static $dbTable = 'roles';


} 
?>