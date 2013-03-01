<?php
require_once dirname(__FILE__) . '/db_linked.class.php';
require_once dirname(__FILE__) . '/permission.class.php';
require_once dirname(__FILE__) . '/role.class.php';


class EqGroup extends Db_Linked
{
    public static $fields = array('eq_group_id','name','descr',
                           'start_minute','min_duration_minutes','max_duration_minutes','duration_chunk_minutes',
                           'flag_delete');
    public static $primaryKeyField = 'eq_group_id';    
    public static $dbTable = 'eq_groups';

	// instance attributes
	public $permission = array();


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

	public static function getUnifiedEqGroupList($igs,$egs) {
		$assoc_egs = [];
		$egs_merged = [];

		# work-around: to enable matching by keys, put the existing array $egs into a new associative array where the index is string value of eq_group_id
		for($i=0, $size=count($egs); $i<$size; ++$i) {
			$assoc_egs[$egs[$i]->eq_group_id] = $egs[$i];
		}

		# final merged array begins with copy of all user eq_groups (avoids leaving them out if they are not included in an inst_group)
		$egs_merged = $assoc_egs;

		# loop through institutional groups
		for($i=0, $size=count($igs); $i<$size; ++$i) {
			$inst_egs = EqGroup::getEqGroupsForInstGroup($igs[$i]);

			# loop through eq_groups associated with this institutional group
			for($k=0, $size=count($inst_egs); $k<$size; ++$k) {

				# this lookup works because PHP is loosely typed (an array key of type integer can be compared to an array key of type string)
				if (array_key_exists($inst_egs[$k]->eq_group_id, $assoc_egs)) {
					$groupToPushOn = $egs[$k];

					$cmpRoles = Role::cmpRolesByID($inst_egs[$k]->permission[0]->role_id, $assoc_egs[$inst_egs[$k]->eq_group_id]->permission[0]->role_id);
					if ($cmpRoles == -1) {
						 $groupToPushOn = $inst_egs[$k];
					}

					# make sure that the $groupToPushOn does not already exist within the final merged array
					if (! array_key_exists($groupToPushOn->eq_group_id, $egs_merged)) {
						$egs_merged[$groupToPushOn->eq_group_id] = $groupToPushOn;
					} else {
						# eq_group_id already exists in final merged array!!
						# compare role_id of $groupToPushOn and match in final merged array
						$cmpRoles = Role::cmpRolesByID($groupToPushOn->permission[0]->role_id, $egs_merged[$groupToPushOn->eq_group_id]->permission[0]->role_id);
						if ($cmpRoles == -1) {
							# update role_id of existing array object with "improved" role_id
							$egs_merged[$groupToPushOn->eq_group_id]->permission[0]->role_id = $groupToPushOn->permission[0]->role_id;
						}
					}
				} else {
					# add inst_group eq_group (as it does not exist in user eq_groups)
					$egs_merged[$inst_egs[$k]->eq_group_id] = $inst_egs[$k];
				}
			}
		}

		return $egs_merged;
	}


	public static function getEqGroupsForInstGroup($ig) {
		// get all eq_groups associated with this institutional group (going from: ig -> permission -> eq_group)
		$permission = Permission::loadAllFromDb(['entity_id'=>$ig->inst_group_id,'entity_type'=>'inst_group','flag_delete'=>0],$ig->dbConnection);
		$groups = [];
		foreach ($permission as $p) {
			$eq = EqGroup::loadOneFromDb(['eq_group_id'=>$p->eq_group_id],$ig->dbConnection);
			$eq->permission = [$p];
			array_push($groups,$eq);
		}
		return $groups;
	}

	public static function getEqGroupsForUser($user) {
		// get all eq_groups associated with this user (going from: $user -> permission -> eq_group)
		$permission = Permission::loadAllFromDb(['entity_id'=>$user->user_id,'entity_type'=>'user','flag_delete'=>0],$user->dbConnection);
		$groups = [];
		foreach ($permission as $p) {
			$eq = EqGroup::loadOneFromDb(['eq_group_id'=>$p->eq_group_id],$user->dbConnection);
			$eq->permission = [$p];
			array_push($groups,$eq);
		}
		return $groups;
	}


	public static function getAllEqGroupsForNonAdminUser($user) {
		$u_igs = InstGroup::getInstGroupsForUser($user);
		$u_egs = EqGroup::getEqGroupsForUser($user);

		$all_egs = EqGroup::getUnifiedEqGroupList($u_igs,$u_egs);

		return $all_egs;
	}

}
?>
