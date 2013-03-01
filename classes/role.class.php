<?php
require_once dirname(__FILE__) . '/db_linked.class.php';


class Role extends Db_Linked
{
    public static $fields = array('role_id','name','flag_delete');
    public static $primaryKeyField = 'role_id';    
    public static $dbTable = 'roles';


	public static function cmpRolesByID($a,$b) {
		# Currently: role_id values are: admin role = 1, manager = 2, consumer = 3
		# TODO: Improve this comparison later by adding a role.order attribute, to remove the current overloading on the role.role_id primary key

		if ($a == $b) {
			return 0;
		}
		return ($a < $b) ? -1 : 1;
	}

} 
?>