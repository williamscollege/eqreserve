<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';
	//	require_once dirname(__FILE__) . '/../../classes/role.class.php';


	class TestOfCommPref extends WMSUnitTestCaseDB {

		function setUp() {
			createTestData_CommPrefs($this->DB);
		}

		function tearDown() {
			removeTestData_CommPrefs($this->DB);
		}


		public function testCommPrefToHTML() {
			$cp = CommPref::getOneFromDb(['comm_pref_id' => 101], $this->DB);

			$html = $cp->toHTML(TRUE);

			$this->assertEqual($html,
				'<ul class="inline"><li>Reminder on upcoming reservations: NO</li><li>Alert on reservation created: NO</li><li>Alert on reservation cancelled: NO</li></ul>'
			);

			$html = $cp->toHTML(FALSE);

			$this->assertEqual($html,
				'<ul class="inline"><li>Reminder on upcoming reservations: NO</li></ul>'
			);
		}

		public function testCommPrefToHTMLForm() {
			$cp = CommPref::getOneFromDb(['comm_pref_id' => 102], $this->DB);

			$html = $cp->toHTMLForm(TRUE);

			$this->assertEqual($html,
				'<ul class="inline">' .
				'<li>Reminder on upcoming reservations: <input type="checkbox" class="comm_pref-checkbox" data-comm-pref-type="reminder" id="reminder_comm_pref_102" name="reminder_comm_pref_102" data-for-comm-pref="102" checked="checked"/></li>' .
				'<li>Alert on reservation created: <input type="checkbox" class="comm_pref-checkbox" data-comm-pref-type="alert_create" id="alert_create_comm_pref_102" name="alert_create_comm_pref_102" data-for-comm-pref="102"/></li>' .
				'<li>Alert on reservation cancelled: <input type="checkbox" class="comm_pref-checkbox" data-comm-pref-type="alert_cancel" id="alert_cancel_comm_pref_102" name="alert_cancel_comm_pref_102" data-for-comm-pref="102"/></li>' .
				'</ul>'
			);

			$html = $cp->toHTMLForm(FALSE);

			$this->assertEqual($html,
				'<ul class="inline">' .
				'<li>Reminder on upcoming reservations: <input type="checkbox" class="comm_pref-checkbox" data-comm-pref-type="reminder" id="reminder_comm_pref_102" name="reminder_comm_pref_102" data-for-comm-pref="102" checked="checked"/></li>' .
				'</ul>'
			);
		}

		public function testFailOnNoCommPrefRecordsFoundForValidUserID() {
			$cp_group        = CommPref::getAllFromDb(['eq_group_id' => 201], $this->DB);
			$cp_user_valid   = CommPref::getAllFromDb(['user_id' => 1101], $this->DB);
			$cp_user_invalid = CommPref::getAllFromDb(['user_id' => 1107], $this->DB);

			$this->assertEqual(count($cp_group), 2, 'there are 1 comm_pref records for eq_group_id=201');
			$this->assertEqual(count($cp_user_valid), 4, 'there are 4 comm_pref records for user_id=1101');
			$this->assertEqual(count($cp_user_invalid), 0, 'there are NO comm_pref records for user_id=1107');
		}

	}

?>