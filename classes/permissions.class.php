<?php
require_once dirname(__FILE__) . '/db_linked.class.php';

class Permissions extends Db_Linked
{
    public static $fields = array('permission_id','entity_id','entity_type','role_id','eq_group_id','flag_delete');
    public static $primaryKeyField = 'permission_id';
    public static $dbTable = 'permissions';


}
?>