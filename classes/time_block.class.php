<?php
	require_once dirname(__FILE__) . '/db_linked.class.php';
	require_once dirname(__FILE__) . '/schedule.class.php';
	require_once dirname(__FILE__) . '/reservation.class.php';

	require_once dirname(__FILE__) . '../../util.php';

	class TimeBlock extends Db_Linked {
		public static $fields = array('time_block_id', 'schedule_id', 'start_datetime', 'end_datetime', 'flag_delete');
		public static $primaryKeyField = 'time_block_id';
		public static $dbTable = 'time_blocks';

		public $schedule = '';
		public $user = '';
		public $reservations = '';

		public static function cmp($a, $b) {
			if ($a->start_datetime == $b->start_datetime) {
				return 0;
			}
			return ($a->start_datetime < $b->start_datetime) ? -1 : 1;
		}

		public function loadSchedule() {
			$this->schedule = Schedule::getOneFromDb(['schedule_id' => $this->schedule_id, 'flag_delete' => FALSE], $this->dbConnection);
		}

		public function loadUser() {
			if (!$this->schedule) {
				$this->loadSchedule();
			}
			$this->user = User::getOneFromDb(['user_id' => $this->schedule->user_id, 'flag_delete' => FALSE], $this->dbConnection);
		}

		public function loadReservations() {
			if (!$this->schedule) {
				$this->loadSchedule();
			}
			$this->reservations = Reservation::getAllFromDb(['schedule_id' => $this->schedule->schedule_id, 'flag_delete' => FALSE], $this->dbConnection);
			usort($this->reservations, "Reservation::cmp");
		}

		public function loadAllRelated() {
			$this->loadSchedule();
			$this->loadReservations();
			$this->loadUser();
		}

		public function toString() {
			return util_timeRangeString($this->start_datetime, $this->end_datetime);
		}
	}

?>