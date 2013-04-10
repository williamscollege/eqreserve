<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';

	class Reservation extends Db_Linked {
		public static $fields = array('reservation_id', 'eq_item_id', 'schedule_id', 'flag_delete');
		public static $primaryKeyField = 'reservation_id';
		public static $dbTable = 'reservations';

		// instance attributes
		public $eq_item = '';
		public $schedule = '';
		public $user = '';
		public $time_blocks = '';

		public static function cmp($a, $b) {
			if (!$a->eq_item) {
				$a->loadEqItem();
			}
			if (!$b->eq_item) {
				$b->loadEqItem();
			}
			return EqItem::cmp($a->eq_item, $b->eq_item);
		}

		public function loadEqItem() {
			$this->eq_item = EqItem::getOneFromDb(['eq_item_id' => $this->eq_item_id, 'flag_delete' => FALSE], $this->dbConnection);
		}

		public function loadSchedule() {
			$this->schedule = Schedule::getOneFromDb(['schedule_id' => $this->schedule_id, 'flag_delete' => FALSE], $this->dbConnection);
		}

		/*
		 * NOTE: potentially serious inefficiencies here as we're doing an 'extra' db call to load the time block group
		 * so that we can get the user and list of time blocks. We could get around this by overriding the getOne and getAll
		 * static functions to do more complex fetching and object building. However, that's a messy and error-prone enough
		 * process that we're just living with this inefficiency until-and-unless it becomes clear that it's a problem on
		 * the usability end (as opposed to just an aesthetics of design / coding issue).
		 */

		public function loadUser() {
			if (!$this->schedule) {
				$this->loadSchedule();
			}
			$this->user = User::getOneFromDb(['user_id' => $this->schedule->user_id, 'flag_delete' => FALSE], $this->dbConnection);
		}

		public function loadTimeBlocks() {
			if (!$this->schedule) {
				$this->loadSchedule();
			}
			$this->time_blocks = TimeBlock::getAllFromDb(['schedule_id' => $this->schedule_id, 'flag_delete' => FALSE], $this->dbConnection);
			usort($this->time_blocks, "TimeBlock::cmp");
		}

        public function toListItemLinked($id='',$class_ar=[],$other_attr_hash=[]) {
            if (! $this->schedule) { $this->loadSchedule(); }
            if (! $this->eq_item) { $this->loadEqItem(); }
            $li = parent::listItemTag($id,$class_ar,$other_attr_hash);
            $li .= '<a href="reservation.php?reservation='.$this->reservation_id.'">'.$this->eq_item->name.'</a> '.$this->schedule->toString();
            $li .= '</li>';
            return $li;
        }
	}

?>