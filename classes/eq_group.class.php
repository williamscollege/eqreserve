<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';
	require_once dirname(__FILE__) . '/permission.class.php';
	require_once dirname(__FILE__) . '/role.class.php';
	require_once dirname(__FILE__) . '/eq_subgroup.class.php';


	class EqGroup extends Db_Linked {
		public static $fields = array('eq_group_id', 'name', 'descr',
			'start_minute', 'min_duration_minutes', 'max_duration_minutes', 'duration_chunk_minutes',
			'flag_delete');
		public static $primaryKeyField = 'eq_group_id';
		public static $dbTable = 'eq_groups';

		// instance attributes
		public $permission = ''; // the way the current user may access this eq group
		public $eq_subgroups;
		public $eq_items;
		public $permissions; // all permission object associated with this eq group
		public $reservations;
		public $schedules;
		public $managers; // mixed array of users and inst groups
		public $manager_users_direct_and_indirect; // associative hash of user_ids and user objects (including direct user managers and indirect inst_group user managers)
		public $consumers; // mixed array of users and inst groups
		public $consumer_users_direct_and_indirect; // associative hash of user_ids and user objects (including direct user consumers and indirect inst_group user consumers)

		public function __construct($initsHash) {
			parent::__construct($initsHash);

			// now do custom stuff
			// e.g. automatically load all accessibility info associated with the user

			if (!array_key_exists('start_minute', $initsHash)) {
				$this->start_minute = '0';
			}
			if (!array_key_exists('min_duration_minutes', $initsHash)) {
				$this->min_duration_minutes = '60';
			}
			if (!array_key_exists('max_duration_minutes', $initsHash)) {
				$this->max_duration_minutes = '60';
			}
			if (!array_key_exists('duration_chunk_minutes', $initsHash)) {
				$this->duration_chunk_minutes = '60';
			}

		}

		/*
			 public static function getAllEqGroups() {

				global $DB; //import this "global" variable

				$sysAdminAllEqGroups = EqGroup::getAllFromDb(['flag_delete'=>0],'DB'=>$DB);
				return($sysAdminAllEqGroups);
			}
		*/

		//##################################################
		// static functions

		public static function cmp($a, $b) {
			return self::cmpAlphabetical($a, $b);
		}

		public static function cmpAlphabetical($a, $b) {
			if ($a->name == $b->name) {
				return 0;
			}
			return ($a->name < $b->name) ? -1 : 1;
		}


		public static function getUnifiedEqGroupList($igs, $equipmentGroupsOfUser) {
			# to enable matching by keys: put the existing array of objects ($equipmentGroupsOfUser) into a new associative array where the index is set to the string value of eq_group_id
			$equipmentGroupsOfUserById = array();
			for ($i = 0, $size = count($equipmentGroupsOfUser); $i < $size; ++$i) {
				$equipmentGroupsOfUserById[$equipmentGroupsOfUser[$i]->eq_group_id] = $equipmentGroupsOfUser[$i];
			}

			//print_r($igs);
			//echo "processing ig of count(igs)".count($igs)."<br/>\n";
			# loop through institutional groups
			for ($i = 0, $ig_size = count($igs); $i < $ig_size; ++$i) {
				//echo "processing ig of $i<br/>\n";
				$inst_egs = EqGroup::getEqGroupsForInstGroup($igs[$i]);

				# loop through eq_groups associated with this institutional group
				for ($k = 0, $ieg_size = count($inst_egs); $k < $ieg_size; ++$k) {

					$eqGroupFromInst = $inst_egs[$k];

					# (this lookup works because PHP is loosely typed; an array key of type integer can be compared to an array key of type string)
					if (!array_key_exists($eqGroupFromInst->eq_group_id, $equipmentGroupsOfUserById)) {
						# add this eq_group: the eqGroupFromInst does not exist within equipmentGroupsOfUserById
						$equipmentGroupsOfUserById[$eqGroupFromInst->eq_group_id] = $eqGroupFromInst;
					}
					else {
						# it already exists, so see if we need to update the permission
						$cmpRoles = Role::cmpRoles($eqGroupFromInst->permission->role, $equipmentGroupsOfUserById[$eqGroupFromInst->eq_group_id]->permission->role);

						if ($cmpRoles == 1) {
							# replace the existing permission object with the more relevant version
							$equipmentGroupsOfUserById[$eqGroupFromInst->eq_group_id]->permission = $eqGroupFromInst->permission;
						}
					}
				}
			}

			return array_slice($equipmentGroupsOfUserById, 0); // convert the array by id to simple array
		}


		public static function getEqGroupsForInstGroup($ig) {
			// get all eq_groups associated with this institutional group (going from: ig -> permission -> eq_group)
			$permissions = Permission::getAllFromDb(['entity_id' => $ig->inst_group_id, 'entity_type' => 'inst_group', 'flag_delete' => FALSE], $ig->dbConnection);
			$groups      = [];
			foreach ($permissions as $p) {
				$eq = EqGroup::getOneFromDb(['eq_group_id' => $p->eq_group_id, 'flag_delete' => FALSE], $ig->dbConnection);
				if ($eq->matchesDb) {
					$eq->permission = $p;
					$eq->permission->loadRole();
					array_push($groups, $eq);
				}
			}
			return $groups;
		}

		public static function getEqGroupsForUser($user) {
			// get all eq_groups associated with this user (going from: $user -> permission -> eq_group)
			$permissions = Permission::getAllFromDb(['entity_id' => $user->user_id, 'entity_type' => 'user', 'flag_delete' => FALSE], $user->dbConnection);
			$groups      = [];
			foreach ($permissions as $p) {
				$eq = EqGroup::getOneFromDb(['eq_group_id' => $p->eq_group_id, 'flag_delete' => FALSE], $user->dbConnection);
				if ($eq->matchesDb) {
					$eq->permission = $p;
					$eq->permission->loadRole();
					array_push($groups, $eq);
				}
			}
			return $groups;
		}


		public static function getAllEqGroupsForNonAdminUser($user) {
			$u_igs   = InstGroup::getInstGroupsForUser($user);
			$u_egs   = EqGroup::getEqGroupsForUser($user);
			$all_egs = EqGroup::getUnifiedEqGroupList($u_igs, $u_egs);

			//		echo "EqGroup::getUnifiedEqGroupList<br /><pre>";
			//		print_r($all_egs);
			//		echo "</pre>";

			usort($all_egs, "EqGroup::cmp");
			return $all_egs;
		}

		public static function getAllEqGroupsForAdminUser($user) {
			// get all eq_groups for this system administrator
			$groups = EqGroup::getAllFromDb(['flag_delete' => FALSE], $user->dbConnection);
			usort($groups, "EqGroup::cmp");
			return $groups;
		}

		public static function getAllEqGroupsForUser($user) {
			if ($user->flag_is_system_admin) {
				return self::getAllEqGroupsForAdminUser($user);
			}
			return self::getAllEqGroupsForNonAdminUser($user);
		}

        public function getAllItems(){
            $this->loadEqItems();
            return $this->eq_items;
        }

		//##################################################
		// instance functions

		public function loadEqSubgroups() {
			$this->eq_subgroups = EqSubgroup::getAllFromDb(['eq_group_id' => $this->eq_group_id, 'flag_delete' => FALSE], $this->dbConnection);
			foreach ($this->eq_subgroups as $esg) {
				$esg->eq_group = $this;
			}
			usort($this->eq_subgroups, "EqSubgroup::cmp");
			return TRUE;
		}

		public function loadEqItems() {
			$this->loadEqSubgroups();
			$this->eq_items = array();
			foreach ($this->eq_subgroups as $esg) {
				$esg->loadEqItems();
				foreach ($esg->eq_items as $itm) { // could maybe use array_merge here instead; not sure which is faster...
					array_push($this->eq_items, $itm);
				}
				usort($esg->eq_items, "EqItem::cmp");
			}

			return TRUE;
		}

		public function loadPermissions() {
			$this->permissions = Permission::getAllFromDb(['eq_group_id' => $this->eq_group_id, 'flag_delete' => FALSE], $this->dbConnection);
			return TRUE;
		}

		public function loadManagers() {
			if (!$this->permissions) {
				$this->loadPermissions();
			}
			$this->managers                          = [];
			$this->manager_users_direct_and_indirect = [];
			$manager_user_ids                        = [];
			$manager_inst_group_ids                  = [];
			foreach ($this->permissions as $perm) {
				if ($perm->role_id == 1) {
					if ($perm->entity_type == 'user') {
						array_push($manager_user_ids, $perm->entity_id);
					}
					elseif ($perm->entity_type == 'inst_group') {
						array_push($manager_inst_group_ids, $perm->entity_id);
					}
				}
			}
			if (count($manager_user_ids) > 0) {
				$manager_users = User::getAllFromDb(['user_id' => $manager_user_ids], $this->dbConnection);
				foreach ($manager_users as $key => $val) {
					array_push($this->managers, $val); // convert hash to simple array
					array_push($this->manager_users_direct_and_indirect, $val); // convert hash to simple array
				}
			}
			if (count($manager_inst_group_ids) > 0) {
				$manager_inst_groups = InstGroup::getAllFromDb(['inst_group_id' => $manager_inst_group_ids], $this->dbConnection);
				foreach ($manager_inst_groups as $key => $val) {
					array_push($this->managers, $val); // convert hash to simple array ('managers' is a heterogeneous mix of users and inst_groups)
					$mgr_ig_users = $val->getAllUsers();
					foreach ($mgr_ig_users as $mgr_ig_key => $mgr_ig_user) {

						if (!in_array($mgr_ig_user->user_id, array_map(function ($e) {
							return $e->user_id;
						}, $this->manager_users_direct_and_indirect))
						) {
							array_push($this->manager_users_direct_and_indirect, $mgr_ig_user);
						}

					}
				}
			}
		}

		public function loadConsumers() {
			if (!$this->permissions) {
				$this->loadPermissions();
			}
			$this->consumers                          = [];
			$this->consumer_users_direct_and_indirect = [];
			$consumer_user_ids                        = [];
			$consumer_inst_group_ids                  = [];
			foreach ($this->permissions as $perm) {
				if ($perm->role_id == 2) {
					if ($perm->entity_type == 'user') {
						array_push($consumer_user_ids, $perm->entity_id);
					}
					elseif ($perm->entity_type == 'inst_group') {
						array_push($consumer_inst_group_ids, $perm->entity_id);
					}
				}
			}
			if (count($consumer_user_ids) > 0) {
				$consumer_users = User::getAllFromDb(['user_id' => $consumer_user_ids], $this->dbConnection);
				foreach ($consumer_users as $key => $val) {
					array_push($this->consumers, $val); // convert hash to simple array
					array_push($this->consumer_users_direct_and_indirect, $val); // convert hash to simple array
				}
			}
			if (count($consumer_inst_group_ids) > 0) {
				$consumer_inst_groups = InstGroup::getAllFromDb(['inst_group_id' => $consumer_inst_group_ids], $this->dbConnection);
				foreach ($consumer_inst_groups as $key => $val) {
					array_push($this->consumers, $val); // convert hash to simple array ('consumers' is a heterogeneous mix of users and inst_groups)
					$con_ig_users = $val->getAllUsers();

					foreach ($con_ig_users as $con_ig_key => $con_ig_user) {

						if (!in_array($con_ig_user->user_id, array_map("util_returnUserID", $this->consumer_users_direct_and_indirect))) {
							array_push($this->consumer_users_direct_and_indirect, $con_ig_user);
						}

						# Same looping as above, but more code to accomplish is
						//						$user_is_already_in_array = false;
						//						foreach ($this->consumer_users_direct_and_indirect as $user_obj) {
						//							if ($user_obj->user_id == $con_ig_user->user_id) {
						//								$user_is_already_in_array = true;
						//								break;
						//							}
						//						}
						//						if (! $user_is_already_in_array) {
						//							array_push($this->consumer_users_direct_and_indirect, $con_ig_user);
						//						}
					}
				}
				//				echo "<pre>";
				//				print_r($this->consumers);
				//				print_r($this->consumer_users_direct_and_indirect);
			}
		}


		public
		function loadSchedules($beginCutoff = '', $endCutoff = '') {
			if (!$this->eq_items) {
				$this->loadEqItems();
			}
			$this->reservations = array();
			$this->schedules    = array();

			$ei_ids = array();
			foreach ($this->eq_items as $ei) {
				array_push($ei_ids, $ei->eq_item_id);
			}
			if (count($ei_ids) > 0) {
				$this->reservations = Reservation::getAllFromDb(['eq_item_id' => $ei_ids], $this->dbConnection);
				$sched_ids          = array();
				foreach ($this->reservations as $r) {
					$sched_ids[$r->schedule_id] = 1;
				}
				if (count($sched_ids) > 0) {
					$init_scheds     = Schedule::getAllFromDb(['schedule_id' => array_keys($sched_ids)], $this->dbConnection);
					$this->schedules = array();
					foreach ($init_scheds as $insch) {
						$insch->loadTimeBlocks($beginCutoff, $endCutoff);
						if (count($insch->time_blocks) < 1) {
							continue;
						}
						$insch->loadReservationsDeeply($beginCutoff, $endCutoff);
						array_push($this->schedules, $insch);
					}
				}
			}
			return TRUE;
		}

		public
		function toListItemLinked($id = '', $class_ar = [], $other_attr_hash = []) {
			$li = parent::listItemTag($id, $class_ar, $other_attr_hash);
			$li .= $this->toHTML();
			$li .= '</li>';
			return $li;
		}

		public
		function toHTML() {
			$ret = '<a href="equipment_group.php?eid=' . $this->eq_group_id . '" title="' . $this->name . '">' . $this->name . '</a>: ' . $this->descr;
			if ($this->permission &&
				$this->permission->role &&
				$this->permission->role->priority == 1
			) {
				$ret .= " <b>(manager)</b>";
			}

			return $ret;
		}
	}

?>