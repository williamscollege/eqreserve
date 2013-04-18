<?php
require_once dirname(__FILE__) . '/time_block.class.php';
require_once dirname(__FILE__) . '/reservation.class.php';

	class Schedule extends Db_Linked {
		public static $fields = array('schedule_id', 'type', 'user_id', 'notes', 'flag_delete');
		public static $primaryKeyField = 'schedule_id';
		public static $dbTable = 'schedules';

		public $user;
		public $time_blocks;
		public $reservations;

		public static function cmp($a, $b) {
			// first by user
			// if users are equal, then by manager consumer
			// if manager / consumer are equal, then by earliest time block
			if ($a->user_id == $b->user_id) {
				if ($a->type == $b->type) {
					if (!$a->time_blocks) {
						$a->loadTimeBlocks();
					}
					if (!$b->time_blocks) {
						$b->loadTimeBlocks();
					}
					return TimeBlock::cmp($a->time_blocks[0], $b->time_blocks[0]);
				}
				return (($a->type == 'manager') && ($b->type == 'consumer')) ? -1 : 1;
			}
			if (!$a->user) {
				$a->loadUser();
			}
			if (!$b->user) {
				$b->loadUser();
			}
			return User::cmp($a->user, $b->user);
		}

		// instance methods

		public function loadUser() {
			$this->user = User::getOneFromDb(['user_id' => $this->user_id, 'flag_delete' => FALSE], $this->dbConnection);
		}

		public function loadTimeBlocks() {
			$this->time_blocks = TimeBlock::getAllFromDb(['schedule_id' => $this->schedule_id, 'flag_delete' => FALSE], $this->dbConnection);
			usort($this->time_blocks, "TimeBlock::cmp");
		}

		public function loadReservations() {
			$this->reservations = Reservation::getAllFromDb(['schedule_id' => $this->schedule_id, 'flag_delete' => FALSE], $this->dbConnection);
			usort($this->reservations, "Reservation::cmp");
		}

        public function loadReservationsDeeply() {
            $this->reservations = Reservation::getAllFromDb(['schedule_id' => $this->schedule_id, 'flag_delete' => FALSE], $this->dbConnection);
            usort($this->reservations, "Reservation::cmp");
            foreach ($this->reservations as $r) {
                $r->loadEqItem();
                $r->eq_item->loadEqGroup(); // NOTE: also loads the subgroup
            }
        }

        public function toString() {
            if (! $this->time_blocks) { $this->loadTimeBlocks(); }
            if (count($this->time_blocks) == 0) {
                return 'no time blocks';
            }
            if (count($this->time_blocks) == 1) {
                return $this->time_blocks[0]->toString();
            }
            $ret = '';
            foreach ($this->time_blocks as $tb) {
                if ($ret != '') { $ret .= ', '; }
                $ret .= '['.$tb->toString().']';
            }
            return $ret;
        }

        function toListItemLinked($id='',$class_ar=[],$other_attr_hash=[]) {
            if (! $this->reservations) { $this->loadReservationsDeeply(); }
            if (! $this->reservations[0]->eq_item) { $this->loadReservationsDeeply(); }

            $li = parent::listItemTag($id,$class_ar,$other_attr_hash);
            if ($this->type == 'manager') {
                $li .= '<strong><span class="text-warning">(MANAGEMENT)</span></strong> ';
            }
            $li .= '<a href="schedule.php?schedule='.$this->schedule_id.'"> '.$this->toString().'</a><br/>';
            $li .= 'for <a href="equipment_group.php?eid='
                .$this->reservations[0]->eq_item->eq_group->eq_group_id
                .'">'.$this->reservations[0]->eq_item->eq_group->name.'</a> you have reserved:';

            $li .= "<ul>\n";
            foreach ($this->reservations as $r) {
                $li .= '<li>'.$r->eq_item->eq_subgroup->name.': '.$r->eq_item->name."</li>\n";
            }
            $li .= '</ul></li>';

            return $li;
        }
	}

?>