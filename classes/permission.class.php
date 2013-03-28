<?php
require_once dirname(__FILE__) . '/db_linked.class.php';

class Permission extends Db_Linked
{
    public static $fields = array('permission_id','entity_id','entity_type','role_id','eq_group_id','flag_delete');
    public static $primaryKeyField = 'permission_id';
    public static $dbTable = 'permissions';

	// instance attributes
	public $role='';

	public function loadRole() {
		$this->role = Role::getOneFromDb(['role_id'=>$this->role_id,'flag_delete'=>false],$this->dbConnection);
	}
}
?>