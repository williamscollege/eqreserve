<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';
	require_once dirname(__FILE__) . '/inst_membership.class.php';


	class InstGroup extends Db_Linked {
		public static $fields = array('inst_group_id', 'name', 'flag_delete');
		public static $primaryKeyField = 'inst_group_id';
		public static $dbTable = 'inst_groups';

        /////////////////////

        public $eq_groups;

        /////////////////////


		// links the given user to this group; makes the given user a member of this group
		public function linkUser($u) {
			//		print_r($u);
			//		print_r($this);
			//		echo "a\n";
			$m = InstMembership::getOneFromDb(['user_id' => $u->user_id, 'inst_group_id' => $this->inst_group_id], $this->dbConnection);
			//print_r($m);
			//		echo "b\n";
			if (!$m->matchesDb) {
				//			echo "c\n";
				$m = new InstMembership(['user_id' => $u->user_id, 'inst_group_id' => $this->inst_group_id, 'flag_delete' => FALSE, 'DB' => $this->dbConnection]);
				$m->updateDb();
				//			echo "d\n";
			}
			elseif ($m->flag_delete) {
				//			echo "e\n";
				$m->flag_delete = FALSE;
				$m->updateDb();
				//			echo "f\n";
			}
			//		echo "g\n";
			$u->loadInstGroups();
			//		echo "h\n";
		}

		// unlinks the given user from this group; makes the given user a NOT member of this group
		public function unlinkUser($u) {
			$m = InstMembership::getOneFromDb(['user_id' => $u->user_id, 'inst_group_id' => $this->inst_group_id, 'flag_delete' => FALSE], $this->dbConnection);
			if ($m->matchesDb) {
				$m->flag_delete = TRUE;
				$m->updateDb();
			}
			$u->loadInstGroups();
		}

        public function loadEqGroups() {
            if (!$this->inst_group_id) {
                trigger_error('cannot load equipment groups for an inst_group with no inst_group_id');
                return;
            }
            $this->eq_groups = EqGroup::getEqGroupsForInstGroup($this);
        }

		// returns an array of all users that are members of this group
		public function getAllUsers() {
			$memberships = InstMembership::getAllFromDb(['inst_group_id' => $this->inst_group_id, 'flag_delete' => FALSE], $this->dbConnection);
			if (count($memberships) <= 0) {
				return [];
			}
			$userIds = array_map(function ($e) {
				return $e->user_id;
			}, $memberships);
			return User::getAllFromDb(['user_id' => $userIds, 'flag_delete' => FALSE], $this->dbConnection);
		}

        public function toListItemLinked($id='',$class_ar=[],$other_attr_hash=[]) {
            $li = parent::listItemTag($id,$class_ar,$other_attr_hash);
            $li .= '<a href="inst_group.php?inst_group='.$this->inst_group_id.'" title="'.$this->name.'">'.$this->name.'</a></li>';
            return $li;
        }

        /////////////////////

		public static function getInstGroupsForUser($user) {
			$memberships = InstMembership::getAllFromDb(['user_id' => $user->user_id, 'flag_delete' => FALSE], $user->dbConnection);
			if (count($memberships) <= 0) {
				return [];
			}
			$instGroupIds = array_map(function ($e) {
				return $e->inst_group_id;
			}, $memberships);
			return InstGroup::getAllFromDb(['inst_group_id' => $instGroupIds, 'flag_delete' => FALSE], $user->dbConnection);
		}


	}

?>