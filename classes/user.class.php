<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';
	require_once dirname(__FILE__) . '/comm_pref.class.php';
	require_once dirname(__FILE__) . '/inst_group.class.php';
	require_once dirname(__FILE__) . '/eq_group.class.php';
	require_once dirname(__FILE__) . '/schedule.class.php';

	class User extends Db_Linked {
		public static $fields = array('user_id', 'username', 'fname', 'lname', 'sortname', 'email', 'advisor', 'notes', 'flag_is_system_admin', 'flag_is_banned', 'flag_delete');
		public static $primaryKeyField = 'user_id';
		public static $dbTable = 'users';

		public $inst_groups;
		public $eq_groups;
		public $schedules;
		public $reservations;
		public $comm_prefs;

		public function __construct($initsHash) {
			parent::__construct($initsHash);

			// now do custom stuff
			// e.g. automatically load all accessibility info associated with the user

			$this->inst_groups = [];
			$this->eq_groups   = [];

			if ($this->user_id) {
				$this->loadInstGroups();
				$this->loadEqGroups();
			}

			//		$this->flag_is_system_admin = false;
			//		$this->flag_is_banned = false;
		}

		public static function cmp($a, $b) {
			if ($a->sortname == $b->sortname) {
				if ($a->lname == $b->lname) {
					if ($a->fname == $b->fname) {
						if ($a->email == $b->email) {
							return 0;
						}
						return ($a->email < $b->email) ? -1 : 1;
					}
					return ($a->fname < $b->fname) ? -1 : 1;
				}
				return ($a->lname < $b->lname) ? -1 : 1;
			}
			return ($a->sortname < $b->sortname) ? -1 : 1;
		}

		public function loadInstGroups() {
			//		echo "myuser_id=".$this->user_id;
			//		if ($this->user_id == 1) {trigger_error('cannot load inst groups for a user where user_id=1');}

			if (!$this->user_id) {
				//			echo "myuser_id=".$this->user_id;
				//			if ($this->user_id == 1) {trigger_error('NO VALUE: cannot load inst groups for a user where user_id=nothing');}

				trigger_error('cannot load inst groups for a user with no user_id');
				return;
			}

			$this->inst_groups = InstGroup::getInstGroupsForUser($this);
		}

		public function loadEqGroups() {
			if (!$this->user_id) {
				trigger_error('cannot load equipment groups for a user with no user_id');
				return;
			}
			$this->eq_groups = EqGroup::getAllEqGroupsForUser($this);
		}

		public function loadReservations() {
			if (!$this->user_id) {
				trigger_error('cannot load reservations for a user with no user_id');
				return;
			}
			if (!$this->schedules) {
				$this->loadSchedules();
			}
			$this->reservations = [];
			foreach ($this->schedules as $tbg) {
				$this->reservations = array_merge($this->reservations, $tbg->reservations);
			}
			usort($this->reservations, "Reservation::cmp");
		}

		public function loadSchedules() {
			if (!$this->user_id) {
				trigger_error('cannot load schedules for a user with no user_id');
				return;
			}
			$this->schedules = [];
			$initial_tbgs    = Schedule::getAllFromDb(['user_id' => $this->user_id, 'flag_delete' => FALSE], $this->dbConnection);
			foreach ($initial_tbgs as $tbg) {
				$tbg->loadTimeBlocks();
				if (count($tbg->time_blocks) > 0) {
					$tbg->loadReservations();
					if (count($tbg->reservations) > 0) {
						$tbg->user = $this;
						array_push($this->schedules, $tbg);
					}
				}
			}
			usort($this->schedules, "Schedule::cmp");
		}

		public function loadCommPrefs() {
			if (!$this->user_id) {
				trigger_error('cannot load schedules for a user with no user_id');
				return;
			}

			$this->updateCommPrefs();

			$this->comm_prefs = array();
			$comm_prefs_ar    = CommPref::getAllFromDb(['user_id' => $this->user_id], $this->dbConnection);
			if (!$comm_prefs_ar) {
				trigger_error('there are NO existing comm_pref records for user_id=' . $this->user_id);
				return;
			}
			foreach ($comm_prefs_ar as $cp) {
				$this->comm_prefs[$cp->eq_group_id] = $cp;
			}
		}

		public function updateCommPrefs() {
			// Ensure that a comm_pref exists for every group for which this user has access
			$this->loadEqGroups();

			foreach ($this->eq_groups as $grp) {
				$exists_comm_pref = CommPref::getOneFromDb(['user_id' => $this->user_id, 'eq_group_id' => $grp->eq_group_id], $this->dbConnection);

				if (!$exists_comm_pref->matchesDb) {
					// create a comm_pref record to this group for this user (flags receive default db values)
					$cp = new CommPref([
						'user_id'                            => $this->user_id,
						'eq_group_id'                        => $grp->eq_group_id,
						'flag_alert_on_upcoming_reservation' => TRUE,
						'flag_contact_on_reserve_create'     => TRUE,
						'flag_contact_on_reserve_cancel'     => TRUE,
						'DB'                                 => $this->dbConnection
					]);

					$cp->updateDb();
				}
			}
		}

		public function updateDbFromAuth($auth) {
			//echo "doing db update<br/>\n";
			//$this->refreshFromDb();

			// if we're passed in an array of auth data, convert it to an object
			if (is_array($auth)) {
				$a              = new Auth_Base();
				$a->username    = $auth['username'];
				$a->fname       = $auth['firstname'];
				$a->lname       = $auth['lastname'];
				$a->email       = $auth['email'];
				$a->sortname    = $auth['sortname'];
				$a->inst_groups = array_slice($auth['inst_groups'], 0);
				$auth           = $a;
			}

			//		print_r($auth);

			// test for basic invalid data
			if ($auth->fname == '') {
				return FALSE;
			}
			if ($auth->lname == '') {
				return FALSE;
			}
			if ($auth->email == '') {
				return FALSE;
			}

			// update info if changed
			if ($this->fname != $auth->fname) {
				$this->fname = $auth->fname;
			} // $this->__set('fname',$auth->fname)
			if ($this->lname != $auth->lname) {
				$this->lname = $auth->lname;
			}
			if ($this->email != $auth->email) {
				$this->email = $auth->email;
			}
			if ($this->sortname != $auth->sortname) {
				$this->sortname = $auth->sortname;
			}

			//User::getOneFromDb(['username'=>$this->username],$this->dbConnection)
			$this->updateDb();
			//echo "TESTUSERIDUPDATED=" . $this->user_id . "<br>";

			#$this->user_id =
			// get the user's current inst groups and the corresponding array of inst group names
			$initialInstGroups  = InstGroup::getInstGroupsForUser($this);
			$userInstGroupNames = array_map(function ($e) {
				return $e->name;
			}, $initialInstGroups);

			// determine the differences between the user inst groups and the auth inst groups
			$extraUserInstGroupNames = array_diff($userInstGroupNames, $auth->inst_groups);
			$extraAuthInstGroupNames = array_diff($auth->inst_groups, $userInstGroupNames);

			//print_r($extraUserInstGroupNames);
			//print_r($extraAuthInstGroupNames);

			// if there are differences, handle them...
			if ((count($extraUserInstGroupNames) > 0) || (count($extraAuthInstGroupNames) > 0)) {

				// remove extras (i.e. user group that aren't in the auth list)
				foreach ($initialInstGroups as $ig) {
					if (in_array($ig->name, $extraUserInstGroupNames)) {
						$ig->unlinkUser($this);
					}
				}

				// add new ones (i.e. auth list groups that the user doesn't have)
				foreach ($extraAuthInstGroupNames as $newGroupName) {
					$groupToAddToUser = InstGroup::getOneFromDb(['name' => $newGroupName, 'flag_delete' => FALSE], $this->dbConnection);

					// check if the group didn't exist in the DB
					if (!$groupToAddToUser->matchesDb) {
						//                    echo "handling new group creation for $newGroupName\n";
						$groupToAddToUser->name        = $newGroupName;
						$groupToAddToUser->flag_delete = FALSE;
						//print_r($groupToAddToUser);
						$groupToAddToUser->updateDb();
					}
					// else check if the group was prevriously deleted
					elseif ($groupToAddToUser->flag_delete) {
						//                    echo "handling group undelete";
						$groupToAddToUser->flag_delete = FALSE;
						$groupToAddToUser->updateDb();
					}

					//      echo "handle linking user: \n";
					$groupToAddToUser->linkUser($this);

				}

				$this->loadInstGroups();
			}
			else { //...otherwise the current groups are OK, so assign them to this user object
				$this->inst_groups = $initialInstGroups;
			}

			$this->updateCommPrefs();

			return TRUE;

		}

		public function canManageEqGroup($g) {
			if ($this->flag_is_system_admin) {
				return TRUE;
			}
			if (is_object($g)) {
				$g = $g->eq_group_id;
			}
			if (!$this->eq_groups) {
				$this->loadEqGroups();
			}
			return in_array($g, array_map(function ($eqg) {
				if ($eqg->permission->role_id == 1) {
					return $eqg->eq_group_id;
				}
				return -1;
			}, $this->eq_groups));
		}

		public function canUseEqGroup($g) {
			if ($this->flag_is_system_admin) {
				return TRUE;
			}
			if (is_object($g)) {
				$g = $g->eq_group_id;
			}
			if (!$this->eq_groups) {
				$this->loadEqGroups();
			}
			return in_array($g, array_map(function ($eqg) {
				if ($eqg->permission->role_id) {
					return $eqg->eq_group_id;
				}
				return -1;
			}, $this->eq_groups));
		}

	}


?>