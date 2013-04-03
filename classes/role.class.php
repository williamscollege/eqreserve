<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';


	class Role extends Db_Linked {
		public static $fields = array('role_id', 'priority', 'name', 'flag_delete');
		public static $primaryKeyField = 'role_id';
		public static $dbTable = 'roles';


		public static function cmpRoles($a, $b) {
			# The most powerful system admin role is priority = 1; lowest anonymous/guest priority is X
			if ($a->priority == $b->priority) {
				return 0;
			}
			return ($a->priority > $b->priority) ? -1 : 1;
		}

	}

?>