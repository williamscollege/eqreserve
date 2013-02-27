<?php
require_once dirname(__FILE__) . '/db_linked.class.php';
require_once dirname(__FILE__) . '/permissions.class.php';
require_once dirname(__FILE__) . '/role.class.php';
#require_once dirname(__FILE__) . '/inst_group.class.php';

class EqGroup extends Db_Linked
{
    public static $fields = array('eq_group_id','name','descr',
                           'start_minute','min_duration_minutes','max_duration_minutes','duration_chunk_minutes',
                           'flag_delete');
    public static $primaryKeyField = 'eq_group_id';    
    public static $dbTable = 'eq_groups';

/*
	public static function getAllEqGroups() {

		global $DB; //import this "global" variable

		$sysAdminAllEqGroups = EqGroup::loadAllFromDb(['flag_delete'=>0],'DB'=>$DB);
		return($sysAdminAllEqGroups);
	}

  */
	public static function cmpAlphabetical($a,$b) {
		if ($a->name == $b->name) {
			return 0;
		}
		return ($a->name < $b->name) ? -1 : 1;
	}

	public static function cmpPermissionLevels($inst,$eq) {
		# we are comparing the eq array against the inst array
		$user_perms = array();
		for($i=0, $size=count($eq); $i<$size; ++$i) {
			if (array_key_exists($eq[$i]->eq_group_id, $inst)) {
				if ($eq->role > $inst->role) {
					# admin role = 1, manager = 2, consumer = 3
					array_push($user_perms, $inst);
				} elseif ($eq->role <= $inst->role) {
					array_push($user_perms, $eq);
				}
			} else {
				# pop on inst permission, as it does not match an existing eq_group
				array_push($user_perms, $inst);
			}
		}
		return $user_perms;
	}

	public static function getEqGroupsForUser($user) {
		#$user->inst_groups;
		print_r($user->inst_groups);

		#print_r($user->inst_groups);

		// for each inst group, get all associated eq_groups
		$user_inst_eq_groups = array();
		foreach ($user->inst_groups as $inst) {
			$eq_groups = Permissions::loadAllFromDb(['entitity_id'=>$inst->inst_group_id,'entity_type'=>'inst_group'],$user->dbConnection);
			array_push($user_inst_eq_groups, $eq_groups);
		}

		// get all individual eq_group permissions for this user (for the moment, ignoring inst_groups)
		$user_eq_groups = Permissions::loadAllFromDb(['entitity_id'=>$user->user_id,'entity_type'=>'user'],$user->dbConnection);

		// merge the inst_group eq_groups and individual eq_groups, checking for overlaps and using the highest permission for each group
		$user_all_perms = EqGroup::cmpPermissionLevels($user_inst_eq_groups, $user_eq_groups);

		return $user_all_perms;
	}

}
?>
