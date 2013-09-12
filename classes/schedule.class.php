<?php
	require_once dirname(__FILE__) . '/time_block.class.php';
	require_once dirname(__FILE__) . '/reservation.class.php';

	class Schedule extends Db_Linked {
		public static $fields = array('schedule_id', 'type', 'user_id', 'notes', 'frequency_type', 'repeat_interval', 'which_days', 'timeblock_start_time', 'timeblock_duration', 'start_on_date', 'end_on_date', 'summary', 'flag_delete');
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
			$this->user = User::getOneFromDb(['user_id' => $this->user_id], $this->dbConnection);
		}

		public function loadTimeBlocks($beginCutoff = '', $endCutoff = '') {
			$searchHash = ['schedule_id' => $this->schedule_id];

			if ($beginCutoff) {
				$begin = date_create($beginCutoff);
				if ($begin) {
					$searchHash['start_datetime >='] = $begin->format('Y-m-d H:i:s');
				}
			}
			if ($endCutoff) {
				$end = date_create($endCutoff);
				if ($end) {
					$searchHash['start_datetime <='] = $end->format('Y-m-d H:i:s');
				}
			}
			//echo "<pre>------------------\n$beginCutoff,$endCutoff\n"; print_r($searchHash); echo '</pre>';
			$this->time_blocks = TimeBlock::getAllFromDb($searchHash, $this->dbConnection);
			usort($this->time_blocks, "TimeBlock::cmp");
		}

		public function loadReservations($beginCutoff = '', $endCutoff = '') {
			if (($beginCutoff != '') || ($endCutoff != '')) {
				$this->loadTimeBlocks($beginCutoff, $endCutoff);
				//echo "<pre>$beginCutoff,$endCutoff\n"; print_r($this->time_blocks); echo '</pre>';
				if (count($this->time_blocks) < 1) {
					$this->reservations = [];
					return;
				}
			}
			$this->reservations = Reservation::getAllFromDb(['schedule_id' => $this->schedule_id], $this->dbConnection);
			usort($this->reservations, "Reservation::cmp");
		}

		public function loadReservationsDeeply($beginCutoff = '', $endCutoff = '') {
			$this->loadReservations($beginCutoff, $endCutoff);
			foreach ($this->reservations as $r) {
				$r->loadEqItem();
				$r->eq_item->loadEqGroup(); // NOTE: also loads the subgroup
			}
		}

		public function toString() {
			if (!$this->time_blocks) {
				$this->loadTimeBlocks();
			}
			if (count($this->time_blocks) == 0) {
				return 'no time blocks';
			}
			if (count($this->time_blocks) == 1) {
				return $this->time_blocks[0]->toString();
			}
			$ret = '';
			foreach ($this->time_blocks as $tb) {
				if ($ret != '') {
					$ret .= ', ';
				}
				$ret .= '[' . $tb->toString() . ']';
			}
			return $ret;
		}

		function toListItemLinked($id = '', $class_ar = [], $other_attr_hash = []) {
			if (!$this->reservations) {
				$this->loadReservationsDeeply();
			}
			if (!$this->reservations[0]->eq_item) {
				$this->loadReservationsDeeply();
			}
			if (!$this->reservations[0]->eq_item->eq_group) {
				$this->loadReservationsDeeply();
			}

			$li = parent::listItemTag($id, $class_ar, $other_attr_hash);
			if ($this->type == 'manager') {
				$li .= '<strong><span class="text-warning">(MANAGEMENT)</span></strong> ';
			}
			$li .= '<strong><a href="schedule.php?schedule=' . $this->schedule_id . '"> ' . $this->toString() . '</a></strong><br/>';
			$li .= 'for <a href="equipment_group.php?eid='
				. $this->reservations[0]->eq_item->eq_group->eq_group_id
				. '">' . $this->reservations[0]->eq_item->eq_group->name . '</a>:';

			$li .= "<ul>\n";
			foreach ($this->reservations as $r) {
				$li .= '<li>' . $r->eq_item->eq_subgroup->name . ': ' . $r->eq_item->name . "</li>\n";
			}
			$li .= '</ul></li>';

			return $li;
		}

		# Queue Email Alerts
		function doCreateQueuedMessages($eq_group, $alertMessageData, $notify) {
			# get all managers of the group, using method 'manager_users_direct_and_indirect()'
			# for each manager:
			#   get their comm prefs for the group
			#   if flag_contact_on_reserve_[upcoming,create,cancel], queue an email alert to that manager about these reservations

			$eq_group->loadManagers();

			$itmCount = count($alertMessageData['item_names']) . " Item" . ((count($alertMessageData['item_names']) > 1) ? 's' : '');

			if ($notify == 'flag_alert_on_upcoming_reservation') {
				$itmCountStr = "Upcoming " . $itmCount . " reserved in ";
				$msgBody     = "Upcoming reservations for the following items include:\n\t";
			}
			elseif ($notify == 'flag_contact_on_reserve_create') {
				$itmCountStr = "Created " . $itmCount . " reserved in ";
				$msgBody     = "The following items have been reserved:\n\t";
			}
			elseif ($notify == 'flag_contact_on_reserve_cancel') {
				$itmCountStr = "Cancelled " . $itmCount . " reserved in ";
				$msgBody     = "Reservations for the following items have been cancelled:\n\t";
			}
			else {
				return FALSE;
			}
			$msgBody .= implode("\n\t", $alertMessageData['item_names']) . "\n";
			$msgBody .= "for " . $this->summary . ":\n\t";
			$msgBody .= implode("\n\t", $alertMessageData['time_ranges']) . "\n";
			$msgBody .= "\n
If you have any questions contact eqreserve-help@williams.edu.\n\n
If you no longer wish to receive these alerts you can change your communication preferences at " . APP_FOLDER . "/account_management.php in the Equipment Groups section.
		";

			foreach ($eq_group->manager_users_direct_and_indirect as $mgr) {
				$mgr->loadCommPrefs();
				if ($mgr->comm_prefs[$eq_group->eq_group_id]->flag_contact_on_reserve_cancel) {
					// echo "</br>mgr->email=" . $mgr->email . ', itmCountStr=' . $itmCountStr .', eq_group->name=' . $eq_group->name . ', mgr->fname=' . $mgr->fname . '.</br><pre>msgBody=' . $msgBody;
					$qm = QueuedMessage::factory($eq_group->dbConnection, $mgr->email, $itmCountStr . $eq_group->name, "Hello " . $mgr->fname . ",\n\n" . $msgBody);
					$qm->updateDb();
				}
			}
		}

	}

?>