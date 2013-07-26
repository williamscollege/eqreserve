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

        // NOTE: takes an optional array of eq_item_ids, which limits the conflict checking to just those items (which increases query efficiency)
        public static function timingConflictsExist($db_connection, $item_id_list=array()) {
        /*
 SELECT
  t1.time_block_id, r1.reservation_id, t2.time_block_id, r2.reservation_id
FROM
  time_blocks AS t1, reservations AS r1,
  time_blocks AS t2, reservations AS r2
WHERE t1.flag_delete = 0 AND t2.flag_delete = 0 AND r1.flag_delete = 0 AND r2.flag_delete = 0
  AND t1.schedule_id = r1.schedule_id
  AND t2.schedule_id = r2.schedule_id
  AND r1.eq_item_id = r2.eq_item_id
  AND t1.time_block_id != t2.time_block_id
  AND (
       (t1.start_datetime <= t2.start_datetime AND t1.end_datetime > t2.start_datetime)
    OR (t2.start_datetime <= t1.start_datetime AND t2.end_datetime > t1.start_datetime)
  )
         */
            // sanitize item id list - ensure item IDs are safe (i.e. numeric data only)
            $new_item_id_list = [];
            foreach ($item_id_list as $iid) {
                if (preg_match('/[0123456789]+/',$iid)) {
                    array_push($new_item_id_list,$iid);
                }
            }
            $item_id_list = $new_item_id_list;

            $checkSql =
                "SELECT
                  t1.time_block_id, r1.reservation_id, t2.time_block_id, r2.reservation_id
                FROM
                  time_blocks AS t1, reservations AS r1,
                  time_blocks AS t2, reservations AS r2
                WHERE t1.flag_delete = 0 AND t2.flag_delete = 0 AND r1.flag_delete = 0 AND r2.flag_delete = 0
                  AND t1.schedule_id = r1.schedule_id
                  AND t2.schedule_id = r2.schedule_id
                  AND r1.eq_item_id = r2.eq_item_id
                  AND t1.time_block_id != t2.time_block_id
                  AND (
                       (t1.start_datetime < t2.start_datetime AND t1.end_datetime > t2.start_datetime)
                    OR (t2.start_datetime < t1.start_datetime AND t2.end_datetime > t1.start_datetime)
                    OR (t2.start_datetime = t1.start_datetime AND t2.end_datetime = t1.end_datetime)
                  )";
            if ($item_id_list) {
                $ids = implode(',',$item_id_list);
                $checkSql .= " AND r1.eq_item_id IN ($ids) AND r1.eq_item_id IN ($ids)";
            }

            $checkStmt = $db_connection->prepare($checkSql);
            $res = $checkStmt->execute();
            Db_Linked::checkStmtError($checkStmt);

            return ($checkStmt->rowCount() >= 1);
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

		public function toListItemLinked($id = '', $class_ar = [], $other_attr_hash = []) {
			if (!$this->schedule) {
				$this->loadSchedule();
			}
			if (!$this->eq_item) {
				$this->loadEqItem();
			}
			$li = parent::listItemTag($id, $class_ar, $other_attr_hash);
			$li .= '<a href="schedule_reservations.php?reservation=' . $this->reservation_id . '">' . $this->eq_item->name . '</a> ' . $this->schedule->toString();
			$li .= '</li>';
			return $li;
		}

		public function toString() {
			if (!$this->eq_item) {
				$this->loadEqItem();
			}
			return $this->eq_item->name;
		}
	}

?>