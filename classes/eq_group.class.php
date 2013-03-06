<?php
require_once dirname(__FILE__) . '/db_linked.class.php';
require_once dirname(__FILE__) . '/permission.class.php';
require_once dirname(__FILE__) . '/role.class.php';
require_once dirname(__FILE__) . '/eq_subgroup.class.php';


class EqGroup extends Db_Linked
{
    public static $fields = array('eq_group_id','name','descr',
                           'start_minute','min_duration_minutes','max_duration_minutes','duration_chunk_minutes',
                           'flag_delete');
    public static $primaryKeyField = 'eq_group_id';    
    public static $dbTable = 'eq_groups';

	// instance attributes
    public $permission = '';
    public $eq_subgroups = '';

/*
 	public static function getAllEqGroups() {

		global $DB; //import this "global" variable

		$sysAdminAllEqGroups = EqGroup::getAllFromDb(['flag_delete'=>0],'DB'=>$DB);
		return($sysAdminAllEqGroups);
	}
*/

    //##################################################
    // static functions

	public static function cmpAlphabetical($a,$b) {
		if ($a->name == $b->name) {
			return 0;
		}
		return ($a->name < $b->name) ? -1 : 1;
	}



	public static function getUnifiedEqGroupList($igs,$equipmentGroupsOfUser) {
		# to enable matching by keys: put the existing array of objects ($equipmentGroupsOfUser) into a new associative array where the index is set to the string value of eq_group_id
		$equipmentGroupsOfUserById = array();
		for($i=0, $size=count($equipmentGroupsOfUser); $i<$size; ++$i) {
			$equipmentGroupsOfUserById[$equipmentGroupsOfUser[$i]->eq_group_id] = $equipmentGroupsOfUser[$i];
		}

//print_r($igs);
//echo "processing ig of count(igs)".count($igs)."<br/>\n";
		# loop through institutional groups
		for($i=0, $ig_size=count($igs); $i<$ig_size; ++$i) {
//echo "processing ig of $i<br/>\n";
			$inst_egs = EqGroup::getEqGroupsForInstGroup($igs[$i]);

			# loop through eq_groups associated with this institutional group
			for($k=0, $ieg_size=count($inst_egs); $k<$ieg_size; ++$k) {

				 $eqGroupFromInst = $inst_egs[$k];

				# (this lookup works because PHP is loosely typed; an array key of type integer can be compared to an array key of type string)
				if (! array_key_exists($eqGroupFromInst->eq_group_id, $equipmentGroupsOfUserById)) {
					# add this eq_group: the eqGroupFromInst does not exist within equipmentGroupsOfUserById
					$equipmentGroupsOfUserById[$eqGroupFromInst->eq_group_id] = $eqGroupFromInst;
				} else {
					# it already exists, so see if we need to update the permission
					$cmpRoles = Role::cmpRoles($eqGroupFromInst->permission->role, $equipmentGroupsOfUserById[$eqGroupFromInst->eq_group_id]->permission->role);

					if ($cmpRoles == 1) {
						# replace the existing permission object with the more relevant version
						$equipmentGroupsOfUserById[$eqGroupFromInst->eq_group_id]->permission = $eqGroupFromInst->permission;
					}
				}
			}
		}

		return $equipmentGroupsOfUserById;
	}


	public static function getEqGroupsForInstGroup($ig) {
		// get all eq_groups associated with this institutional group (going from: ig -> permission -> eq_group)
		$permissions = Permission::getAllFromDb(['entity_id'=>$ig->inst_group_id,'entity_type'=>'inst_group','flag_delete'=>0],$ig->dbConnection);
		$groups = [];
		foreach ($permissions as $p) {
			$eq = EqGroup::getOneFromDb(['eq_group_id'=>$p->eq_group_id],$ig->dbConnection);
			$eq->permission = $p;
			$eq->permission->loadRole();
			array_push($groups,$eq);
		}
		return $groups;
	}

	public static function getEqGroupsForUser($user) {
		// get all eq_groups associated with this user (going from: $user -> permission -> eq_group)
		$permissions = Permission::getAllFromDb(['entity_id'=>$user->user_id,'entity_type'=>'user','flag_delete'=>0],$user->dbConnection);
		$groups = [];
		foreach ($permissions as $p) {
			$eq = EqGroup::getOneFromDb(['eq_group_id'=>$p->eq_group_id],$user->dbConnection);
			$eq->permission = $p;
			$eq->permission->loadRole();
			array_push($groups,$eq);
		}
		return $groups;
	}


	public static function getAllEqGroupsForNonAdminUser($user) {
		$u_igs = InstGroup::getInstGroupsForUser($user);
		$u_egs = EqGroup::getEqGroupsForUser($user);
		$all_egs = EqGroup::getUnifiedEqGroupList($u_igs,$u_egs);

//		echo "EqGroup::getUnifiedEqGroupList<br /><pre>";
//		print_r($all_egs);
//		echo "</pre>";

		return $all_egs;
	}

    //##################################################
    // instance functions

    public function loadEqSubgroups() {
        $this->eq_subgroups = EqSubgroup::getAllFromDb(['eq_group_id'=>$this->eq_group_id],$this->dbConnection);

        return true;
    }

}
?>