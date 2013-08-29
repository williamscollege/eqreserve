<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

	class SchedulesCreateTest extends WMSWebTestCase {

		private $urlbase = 'http://localhost/eqreserve/ajax_actions/ajax_schedule_reservations.php';

		function setUp() {
			createAllTestData($this->DB);
		}

		function tearDown() {
			removeAllTestData($this->DB);
		}

		//############################################################

		function signIn() {
			$this->get('http://localhost/eqreserve/');
			$this->setField('username', TESTINGUSER);
			$this->setField('password', TESTINGPASSWORD);
			$this->click('Sign in');
		}

		function getBaseUrlParamsArray() {
			return array(
				'eqGroupID'                => '201'
			, 'subgroup-301'               => '402'
			, 'subgroup-302-406'           => '406'
			, 'subgroup-302-412'           => '412'
			, 'scheduleStartTimeConverted' => '14:30:00'
			, 'scheduleSummaryText'        => 'Once%20at%2002:30%20PM%20for%2060%20minutes%20until%202013-08-15'
			, 'scheduleNotes'              => 'A%20sample%20note%20for%20this%20schedule'
			, 'scheduleStartOnDate'        => '2013-08-15'
			, 'scheduleEndOnDate'          => '2013-08-15'
			, 'hour'                       => '02'
			, 'minute'                     => '30'
			, 'meridian'                   => 'PM'
			, 'scheduleDuration'           => '30M'
			, 'scheduleFrequencyType'      => 'no_repeat'
			, 'scheduleRepeatInterval'     => '1'
			, 'scheduleIsTypeManager'      => 'on'
			, 'repeat_dow_sun'             => '0'
			, 'repeat_dow_mon'             => '0'
			, 'repeat_dow_tue'             => '0'
			, 'repeat_dow_wed'             => '0'
			, 'repeat_dow_thu'             => '0'
			, 'repeat_dow_fri'             => '0'
			, 'repeat_dow_sat'             => '0'
			, 'repeat_dom_1'               => '0'
			, 'repeat_dom_2'               => '0'
			, 'repeat_dom_3'               => '0'
			, 'repeat_dom_4'               => '0'
			, 'repeat_dom_5'               => '0'
			, 'repeat_dom_6'               => '0'
			, 'repeat_dom_7'               => '0'
			, 'repeat_dom_8'               => '0'
			, 'repeat_dom_9'               => '0'
			, 'repeat_dom_10'              => '0'
			, 'repeat_dom_11'              => '0'
			, 'repeat_dom_12'              => '0'
			, 'repeat_dom_13'              => '0'
			, 'repeat_dom_14'              => '0'
			, 'repeat_dom_15'              => '0'
			, 'repeat_dom_16'              => '0'
			, 'repeat_dom_17'              => '0'
			, 'repeat_dom_18'              => '0'
			, 'repeat_dom_19'              => '0'
			, 'repeat_dom_20'              => '0'
			, 'repeat_dom_21'              => '0'
			, 'repeat_dom_22'              => '0'
			, 'repeat_dom_23'              => '0'
			, 'repeat_dom_24'              => '0'
			, 'repeat_dom_25'              => '0'
			, 'repeat_dom_26'              => '0'
			, 'repeat_dom_27'              => '0'
			, 'repeat_dom_28'              => '0'
			, 'repeat_dom_29'              => '0'
			, 'repeat_dom_30'              => '0'
			, 'repeat_dom_31'              => '0'
			, 'btnReservationSubmit'       => ''
			);
		}

		function urlParamsArrayToString($ar) {
			$ret = '';
			foreach ($ar as $p => $v) {
				if ($ret) {
					$ret .= '&';
				}
				$ret .= "$p=$v";
			}
			return $ret;
		}

		//############################################################
		// access tests

		function testManagerAccessForManagerSchedule() {
			$this->signIn();
			$par = $this->getBaseUrlParamsArray();

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));

			$this->assertNoPattern('/cannot create manager reservation  - not a manager of this group/i');
			$this->assertNoPattern('/failure/i');
			$this->assertPattern('/success/i');
		}

		function testManagerAccessForConsumerSchedule() {
			$this->signIn();

			$par = $this->getBaseUrlParamsArray();
			unset($par['scheduleIsTypeManager']); // set type to consumer (i.e. not manager); NOTE: this is a checkbox: if not checked, it should not exist in this array

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));

			$this->assertNoPattern('/cannot create user reservation - not a user of this group/i');
			$this->assertNoPattern('/failure/i');
			$this->assertPattern('/success/i');
		}

		function testConsumerNoAccessForManagerSchedule() {
			$this->signIn();
			$par              = $this->getBaseUrlParamsArray();
			$par['eqGroupID'] = '207'; // set group to one which the user has consumer access and NOT manager access

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));

			$this->assertPattern('/cannot create manager reservation - not a manager of this group/i');
			$this->assertPattern('/failure/i');
			$this->assertNoPattern('/success/i');
		}

		function testConsumerAccessForConsumerSchedule() {
			$this->signIn();
			$par = $this->getBaseUrlParamsArray();
			unset($par['eqGroupID']); // remove unnecessary array elements
			unset($par['subgroup-301']); // remove unnecessary array elements
			unset($par['subgroup-302-406']);
			unset($par['subgroup-302-412']);
			unset($par['scheduleIsTypeManager']); // set type to consumer (i.e. not manager); NOTE: this is a checkbox: if not checked, it should not exist in this array
			$par['eqGroupID']    = '207'; // set group to one which the user has consumer access and NOT manager access
			$par['subgroup-308'] = '410'; // add necessary array element that corresponds to subgroup and subgroup item

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));

			$this->assertNoPattern('/cannot create user reservation - not a user of this group/i');
			$this->assertNoPattern('/failure/i');
			$this->assertPattern('/success/i');
		}

		function testAdminAccessForManagerSchedule() {
			$this->signIn();
			makeAuthedTestUserAdmin($this->DB);
			$par              = $this->getBaseUrlParamsArray();
			$par['eqGroupID'] = '208'; // set group to one which the user has NO access

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));

			$this->assertNoPattern('/cannot create manager reservation - not a manager of this group/i');
			$this->assertNoPattern('/failure/i');
			$this->assertPattern('/success/i');
		}

		function testAdminAccessForConsumerSchedule() {
			$this->signIn();
			makeAuthedTestUserAdmin($this->DB);
			$par              = $this->getBaseUrlParamsArray();
			$par['eqGroupID'] = '208'; // set group to one which the user has NO access
			unset($par['scheduleIsTypeManager']); // set type to consumer (i.e. not manager); NOTE: this is a checkbox: if not checked, it should not exist in this array

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));

			$this->assertNoPattern('/cannot create user reservation - not a user of this group/i');
			$this->assertNoPattern('/failure/i');
			$this->assertPattern('/success/i');
		}

		function testSignedInNoGroupAccessNoScheduling() {
			$this->signIn();

			$par              = $this->getBaseUrlParamsArray();
			$par['eqGroupID'] = '208'; // set group to one which the user has NO access

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));

			$this->assertPattern('/cannot create manager reservation - not a manager of this group/i');
			$this->assertPattern('/failure/i');
			$this->assertNoPattern('/success/i');

			# ANOTHER TEST VARIATION:
			unset($par['scheduleIsTypeManager']); // set type to consumer (i.e. not manager); NOTE: this is a checkbox: if not checked, it should not exist in this array

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));

			$this->assertPattern('/cannot create user reservation - not a user of this group/i');
			$this->assertPattern('/failure/i');
			$this->assertNoPattern('/success/i');
		}

		function testNotSignedInNoScheduling() {
			$this->get($this->urlbase);

			$this->assertText("not authenticated");
		}

		//############################################################
		// data validation tests
		function testFailOnEqGroupNotExists() {
			$this->signIn();

			$par              = $this->getBaseUrlParamsArray();
			$par['eqGroupID'] = '228'; // set group to one which the user has NO access

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));

			$this->assertPattern('/failure/i');
			$this->assertPattern('/equipment group does not exist or was deleted/i');
		}

		function testFailOnEqSubgroupNotExists() {
			$this->signIn();

			$par                 = $this->getBaseUrlParamsArray();
			$par['subgroup-305'] = '407'; // deleted sub-group (305)

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));

			$this->assertPattern('/failure/i');
			$this->assertPattern('/equipment sub-group does not exist or was deleted/i');
		}

		function testFailOnEqSubgroupParamMissing() {
			$this->signIn();

			$par              = $this->getBaseUrlParamsArray();
			$par['subgroup-'] = 'BOO!'; // deleted sub-group (305)

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));

			$this->assertPattern('/failure/i');
			$this->assertPattern('/equipment sub-group parameter empty/i');
		}

		function testFailOnEqItemNotExists() {
			$this->signIn();

			$par                 = $this->getBaseUrlParamsArray();
			$par['subgroup-301'] = '405'; // deleted item (405)

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));

			$this->assertPattern('/failure/i');
			$this->assertPattern('/equipment item does not exist or was deleted/i');
		}

		function testTimeBlockDurationIsValid() {
			$this->signIn();

			$par                     = $this->getBaseUrlParamsArray();
			$par['scheduleDuration'] = '60M'; // try valid value (e.g. "60M")

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));

			$this->assertNoPattern('/invalid time format for duration value/i');
			$this->assertNoPattern('/failure/i');
			$this->assertPattern('/success/i');


			# ANOTHER TEST VARIATION:
			$par['scheduleDuration'] = 'delete all'; // try invalid value (e.g. "delete all")

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));

			$this->assertPattern('/invalid time format for duration value/i');
			$this->assertPattern('/failure/i');
			$this->assertNoPattern('/success/i');
		}

		function testConflictOverrideOnlyOnScheduleOfTypeManager() {
			# if override set, type==manager
			# if type!=manager, override not set
			$this->fail("to be implemented");
		}


		//############################################################
		// action tests

		function testFailOnCreateNoRepeatSingleItemTimingConflictIsManager() {
			$this->signIn();
			$this->get($this->urlbase);

			$blocks_in_db        = TimeBlock::getAllFromDb(['start_datetime' => '2013-03-26 10:00:00', 'end_datetime' => '2013-03-26 10:30:00'], $this->DB);
			$initial_block_count = count($blocks_in_db);

			$par                               = $this->getBaseUrlParamsArray();
			$par['scheduleStartOnDate']        = '2013-03-26';
			$par['scheduleStartTimeConverted'] = '10:00:00';
			$par['scheduleDuration']           = '30M';

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));
			$results = json_decode($this->getBrowser()->getContent(), TRUE);

			$this->assertEqual('scheduling-conflict', $results['status']);

			$this->assertEqual(count($results['conflicts_by_datetime']), 1);
			$this->assertTrue(array_key_exists('2013-03-26 10:00:00', $results['conflicts_by_datetime']));
			$this->assertEqual($results['conflicts_by_datetime']['2013-03-26 10:00:00'][0], 'testItem2');

			$this->assertEqual(count($results['conflicts_by_item']), 1);
			$this->assertTrue(array_key_exists('testItem2', $results['conflicts_by_item']));
			$this->assertEqual($results['conflicts_by_item']['testItem2'][0], '2013-03-26 10:00:00');

			$blocks_in_db = TimeBlock::getAllFromDb(['start_datetime' => '2013-03-26 10:00:00', 'end_datetime' => '2013-03-26 10:30:00'], $this->DB);
			$this->assertEqual(count($blocks_in_db), $initial_block_count);
		}

		function testFailOnCreateNoRepeatSingleItemTimingConflictIsNotManager() {
			$this->signIn();
			$this->get($this->urlbase);

			$blocks_in_db        = TimeBlock::getAllFromDb(['start_datetime' => '2013-03-25 18:00:00', 'end_datetime' => '2013-03-25 19:00:00'], $this->DB);
			$initial_block_count = count($blocks_in_db);

			$par = $this->getBaseUrlParamsArray();
			unset($par['eqGroupID']); // remove unnecessary array elements
			unset($par['subgroup-301']); // remove unnecessary array elements
			unset($par['subgroup-302-406']);
			unset($par['subgroup-302-412']);
			unset($par['scheduleIsTypeManager']); // set type to consumer (i.e. not manager); NOTE: this is a checkbox: if not checked, it should not exist in this array

			$par['eqGroupID']                  = '207'; // set group to one which the user has consumer access and NOT manager access
			$par['subgroup-308']               = '410'; // add necessary array element that corresponds to subgroup and subgroup item
			$par['scheduleStartOnDate']        = '2013-03-25';
			$par['scheduleStartTimeConverted'] = '18:00:00';
			$par['scheduleDuration']           = '60M';

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));
			$results = json_decode($this->getBrowser()->getContent(), TRUE);

			$this->assertEqual('scheduling-conflict', $results['status']);

			$this->assertEqual(count($results['conflicts_by_datetime']), 1);
			$this->assertTrue(array_key_exists('2013-03-25 18:00:00', $results['conflicts_by_datetime']));
			$this->assertEqual($results['conflicts_by_datetime']['2013-03-25 18:00:00'][0], 'testItem9');

			$this->assertEqual(count($results['conflicts_by_item']), 1);
			$this->assertTrue(array_key_exists('testItem9', $results['conflicts_by_item']));
			$this->assertEqual($results['conflicts_by_item']['testItem9'][0], '2013-03-25 18:00:00');

			$blocks_in_db = TimeBlock::getAllFromDb(['start_datetime' => '2013-03-25 18:00:00', 'end_datetime' => '2013-03-25 19:00:00'], $this->DB);
			$this->assertEqual(count($blocks_in_db), $initial_block_count);
		}

		function testFailOnCreateRepeatingSingleItemTimingConflictIsManager() {
			$this->signIn();
			$this->get($this->urlbase);

			$blocks_in_db_1 = TimeBlock::getAllFromDb(['start_datetime' => '2013-07-02 11:00:00', 'end_datetime' => '2013-07-02 11:15:00'], $this->DB);
			$blocks_in_db_2 = TimeBlock::getAllFromDb(['start_datetime' => '2013-07-09 11:00:00', 'end_datetime' => '2013-07-09 11:15:00'], $this->DB);
			$blocks_in_db_3 = TimeBlock::getAllFromDb(['start_datetime' => '2013-07-16 11:00:00', 'end_datetime' => '2013-07-16 11:15:00'], $this->DB);

			$initial_block_count = count($blocks_in_db_1) + count($blocks_in_db_2) + count($blocks_in_db_3);

			$par = $this->getBaseUrlParamsArray();
			unset($par['eqGroupID']); // remove unnecessary array elements
			unset($par['subgroup-301']); // remove unnecessary array elements
			unset($par['subgroup-302-406']);
			unset($par['subgroup-302-412']);

			$par['eqGroupID']                  = '203'; // set group to one which the user has consumer access and NOT manager access
			$par['subgroup-309-413']           = '413'; // add necessary array element that corresponds to subgroup and subgroup item
			$par['scheduleStartOnDate']        = '2013-07-01';
			$par['scheduleEndOnDate']          = '2013-07-17';
			$par['scheduleStartTimeConverted'] = '11:00:00';
			$par['scheduleDuration']           = '15M';
			$par['repeat_dow_tue']             = TRUE;
			$par['scheduleFrequencyType']      = 'weekly';
			$par['scheduleSummaryText']        = 'Every%201%20weeks%20at%2011:00%20AM%20for%2015%20minutes%20on%20(Tuesday),%20until%202013-07-17';

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));
			$results = json_decode($this->getBrowser()->getContent(), TRUE);

			$this->assertEqual('scheduling-conflict', $results['status']);

			# count of conflicts encountered
			$this->assertEqual(count($results['conflicts_by_datetime']), 1);
			$this->assertEqual(count($results['conflicts_by_item']), 1);

			# week 2 (this is conflicting reservation)
			$this->assertTrue(array_key_exists('2013-07-09 11:00:00', $results['conflicts_by_datetime']));
			$this->assertEqual($results['conflicts_by_datetime']['2013-07-09 11:00:00'][0], 'testItem12');

			$this->assertTrue(array_key_exists('testItem12', $results['conflicts_by_item']));
			$this->assertEqual($results['conflicts_by_item']['testItem12'][0], '2013-07-09 11:00:00');

			$blocks_in_db_1 = TimeBlock::getAllFromDb(['start_datetime' => '2013-07-02 11:00:00', 'end_datetime' => '2013-07-02 11:15:00'], $this->DB);
			$blocks_in_db_2 = TimeBlock::getAllFromDb(['start_datetime' => '2013-07-09 11:00:00', 'end_datetime' => '2013-07-09 11:15:00'], $this->DB);
			$blocks_in_db_3 = TimeBlock::getAllFromDb(['start_datetime' => '2013-07-16 11:00:00', 'end_datetime' => '2013-07-16 11:15:00'], $this->DB);

			$this->assertNotEqual(count($blocks_in_db_1), $initial_block_count);
			$this->assertEqual(count($blocks_in_db_2), $initial_block_count);
			$this->assertNotEqual(count($blocks_in_db_3), $initial_block_count);
		}


		function testFailOnCreateRepeatingSingleItemTimingConflictIsNotManager() {
			$this->signIn();
			$this->get($this->urlbase);

			$blocks_in_db_30M = TimeBlock::getAllFromDb(['start_datetime' => '2013-03-25 18:00:00', 'end_datetime' => '2013-03-25 18:30:00'], $this->DB);
			$blocks_in_db_60M = TimeBlock::getAllFromDb(['start_datetime' => '2013-03-25 18:00:00', 'end_datetime' => '2013-03-25 19:00:00'], $this->DB);

			$initial_block_count_30M = count($blocks_in_db_30M);
			$initial_block_count_60M = count($blocks_in_db_60M);

			$par = $this->getBaseUrlParamsArray();
			unset($par['eqGroupID']); // remove unnecessary array elements
			unset($par['subgroup-301']); // remove unnecessary array elements
			unset($par['subgroup-302-406']);
			unset($par['subgroup-302-412']);
			unset($par['scheduleIsTypeManager']); // set type to consumer (i.e. not manager); NOTE: this is a checkbox: if not checked, it should not exist in this array

			$par['eqGroupID']                  = '207'; // set group to one which the user has consumer access and NOT manager access
			$par['subgroup-308']               = '410'; // add necessary array element that corresponds to subgroup and subgroup item
			$par['scheduleStartOnDate']        = '2013-03-17';
			$par['scheduleEndOnDate']          = '2013-04-02';
			$par['scheduleStartTimeConverted'] = '18:00:00';
			$par['scheduleDuration']           = '30M';
			$par['repeat_dow_mon']             = TRUE;
			$par['scheduleFrequencyType']      = 'weekly';
			$par['scheduleSummaryText']        = 'Every%201%20weeks%20at%2006:00%20PM%20for%2030%20minutes%20on%20(Monday),%20until%202013-04-02';

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));
			$results = json_decode($this->getBrowser()->getContent(), TRUE);

			$this->assertEqual('scheduling-conflict', $results['status']);

			# count of conflicts encountered
			$this->assertEqual(count($results['conflicts_by_datetime']), 1);
			$this->assertEqual(count($results['conflicts_by_item']), 1);

			# week 2 (this is conflicting reservation)
			$this->assertTrue(array_key_exists('2013-03-25 18:00:00', $results['conflicts_by_datetime']));
			$this->assertEqual($results['conflicts_by_datetime']['2013-03-25 18:00:00'][0], 'testItem9');

			$this->assertTrue(array_key_exists('testItem9', $results['conflicts_by_item']));
			$this->assertEqual($results['conflicts_by_item']['testItem9'][0], '2013-03-25 18:00:00');

			$blocks_in_db_30M = TimeBlock::getAllFromDb(['start_datetime' => '2013-03-25 18:00:00', 'end_datetime' => '2013-03-25 18:30:00'], $this->DB);
			$blocks_in_db_60M = TimeBlock::getAllFromDb(['start_datetime' => '2013-03-25 18:00:00', 'end_datetime' => '2013-03-25 19:00:00'], $this->DB);

			$this->assertEqual(count($blocks_in_db_30M), $initial_block_count_30M);
			$this->assertEqual(count($blocks_in_db_60M), $initial_block_count_60M);


			# ANOTHER TEST VARIATION: testFailOnCreateRepeatingMultipleItemsTimingConflictIsNotManager
			$par['subgroup-310-414'] = '414'; // add necessary array element that corresponds to subgroup and subgroup item
			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));
			$results = json_decode($this->getBrowser()->getContent(), TRUE);

			$this->assertEqual('scheduling-conflict', $results['status']);

			# count of conflicts encountered
			$this->assertEqual(count($results['conflicts_by_datetime']), 1);
			$this->assertEqual(count($results['conflicts_by_item']), 1);

			# week 2 (this is conflicting reservation)
			$this->assertTrue(array_key_exists('2013-03-25 18:00:00', $results['conflicts_by_datetime']));
			$this->assertEqual($results['conflicts_by_datetime']['2013-03-25 18:00:00'][0], 'testItem9');

			$this->assertTrue(array_key_exists('testItem9', $results['conflicts_by_item']));
			$this->assertEqual($results['conflicts_by_item']['testItem9'][0], '2013-03-25 18:00:00');

			$blocks_in_db_30M = TimeBlock::getAllFromDb(['start_datetime' => '2013-03-25 18:00:00', 'end_datetime' => '2013-03-25 18:30:00'], $this->DB);
			$blocks_in_db_60M = TimeBlock::getAllFromDb(['start_datetime' => '2013-03-25 18:00:00', 'end_datetime' => '2013-03-25 19:00:00'], $this->DB);

			$this->assertEqual(count($blocks_in_db_30M), $initial_block_count_30M);
			$this->assertEqual(count($blocks_in_db_60M), $initial_block_count_60M);
		}

		function testFailOnCreateRepeatingMultipleItemsTimingConflictIsManager() {
			$this->signIn();
			$this->get($this->urlbase);

			$blocks_in_db_1 = TimeBlock::getAllFromDb(['start_datetime' => '2013-07-02 11:00:00', 'end_datetime' => '2013-07-02 11:15:00'], $this->DB);
			$blocks_in_db_2 = TimeBlock::getAllFromDb(['start_datetime' => '2013-07-09 11:00:00', 'end_datetime' => '2013-07-09 11:15:00'], $this->DB);
			$blocks_in_db_3 = TimeBlock::getAllFromDb(['start_datetime' => '2013-07-16 11:00:00', 'end_datetime' => '2013-07-16 11:15:00'], $this->DB);

			$initial_block_count = count($blocks_in_db_1) + count($blocks_in_db_2) + count($blocks_in_db_3);

			$par = $this->getBaseUrlParamsArray();
			unset($par['eqGroupID']); // remove unnecessary array elements
			unset($par['subgroup-301']); // remove unnecessary array elements
			unset($par['subgroup-302-406']);
			unset($par['subgroup-302-412']);

			$par['eqGroupID']                  = '203'; // set group to one which the user has consumer access and NOT manager access
			$par['subgroup-309-413']           = '413'; // add necessary array element that corresponds to subgroup and subgroup item
			$par['subgroup-309-415']           = '415'; // add necessary array element that corresponds to subgroup and subgroup item
			$par['scheduleStartOnDate']        = '2013-07-01';
			$par['scheduleEndOnDate']          = '2013-07-17';
			$par['scheduleStartTimeConverted'] = '11:00:00';
			$par['scheduleDuration']           = '15M';
			$par['repeat_dow_tue']             = TRUE;
			$par['scheduleFrequencyType']      = 'weekly';
			$par['scheduleSummaryText']        = 'Every%201%20weeks%20at%2011:00%20AM%20for%2015%20minutes%20on%20(Tuesday),%20until%202013-07-17';

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));
			$results = json_decode($this->getBrowser()->getContent(), TRUE);

			$this->assertEqual('scheduling-conflict', $results['status']);

			# count of conflicts encountered
			$this->assertEqual(count($results['conflicts_by_datetime']), 1);
			$this->assertEqual(count($results['conflicts_by_item']), 1);

			# week 2 (this is conflicting reservation)
			$this->assertTrue(array_key_exists('2013-07-09 11:00:00', $results['conflicts_by_datetime']));
			$this->assertEqual($results['conflicts_by_datetime']['2013-07-09 11:00:00'][0], 'testItem12');

			$this->assertTrue(array_key_exists('testItem12', $results['conflicts_by_item']));
			$this->assertEqual($results['conflicts_by_item']['testItem12'][0], '2013-07-09 11:00:00');

			$blocks_in_db_1 = TimeBlock::getAllFromDb(['start_datetime' => '2013-07-02 11:00:00', 'end_datetime' => '2013-07-02 11:15:00'], $this->DB);
			$blocks_in_db_2 = TimeBlock::getAllFromDb(['start_datetime' => '2013-07-09 11:00:00', 'end_datetime' => '2013-07-09 11:15:00'], $this->DB);
			$blocks_in_db_3 = TimeBlock::getAllFromDb(['start_datetime' => '2013-07-16 11:00:00', 'end_datetime' => '2013-07-16 11:15:00'], $this->DB);

			$this->assertNotEqual(count($blocks_in_db_1), $initial_block_count);
			$this->assertEqual(count($blocks_in_db_2), $initial_block_count);
			$this->assertNotEqual(count($blocks_in_db_3), $initial_block_count);
		}

		function testSuccessOnCreateShorterThan1DayNoRepeat() {
			$this->signIn();
			$this->get($this->urlbase);

			$user_cp                                 = CommPref::getOneFromDb(['user_id' => 1101, 'eq_group_id' => 201], $this->DB);
			$user_cp->flag_contact_on_reserve_create = TRUE;
			$user_cp->updateDb();

			$blocks_in_db        = TimeBlock::getAllFromDb(['time_block_id !=' => 0], $this->DB);
			$initial_block_count = count($blocks_in_db);

			$par = $this->getBaseUrlParamsArray();

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));
			//$this->dump($this->getBrowser()->getContent());
			//$this->dump($this->urlbase . "?" . $this->urlParamsArrayToString($par));
			//exit;

			$blocks_in_db = TimeBlock::getAllFromDb(['time_block_id !=' => 0], $this->DB);
			$this->assertEqual(count($blocks_in_db), $initial_block_count + 1);
		}

		function testSuccessOnCreateRepeatingMultipleItemsIsManagerWeekly() {
			$this->signIn();
			$this->get($this->urlbase);

			$par                               = $this->getBaseUrlParamsArray();
			$par['scheduleStartOnDate']        = '2013-09-01';
			$par['scheduleEndOnDate']          = '2013-09-30';
			$par['scheduleStartTimeConverted'] = '09:00:00';
			$par['scheduleDuration']           = '4H';
			$par['repeat_dow_sun']             = TRUE;
			$par['repeat_dow_mon']             = TRUE;
			$par['scheduleFrequencyType']      = 'weekly';
			$par['scheduleRepeatInterval']     = '1';
			$par['scheduleSummaryText']        = 'Every%201%20weeks%20at%2009:00%20AM%20for%204%20hours%20on%20(Sunday,%20Monday),%20until%202013-09-30';

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));
			$results = json_decode($this->getBrowser()->getContent(), TRUE);
			// $this->dump($this->urlbase . "?" . $this->urlParamsArrayToString($par));

			$this->assertEqual('success', $results['status']);

			$blocks_in_db_1  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-01 09:00:00', 'end_datetime' => '2013-09-01 13:00:00'], $this->DB);
			$blocks_in_db_2  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-02 09:00:00', 'end_datetime' => '2013-09-02 13:00:00'], $this->DB);
			$blocks_in_db_3  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-08 09:00:00', 'end_datetime' => '2013-09-08 13:00:00'], $this->DB);
			$blocks_in_db_4  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-09 09:00:00', 'end_datetime' => '2013-09-09 13:00:00'], $this->DB);
			$blocks_in_db_5  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-15 09:00:00', 'end_datetime' => '2013-09-15 13:00:00'], $this->DB);
			$blocks_in_db_6  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-16 09:00:00', 'end_datetime' => '2013-09-16 13:00:00'], $this->DB);
			$blocks_in_db_7  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-22 09:00:00', 'end_datetime' => '2013-09-22 13:00:00'], $this->DB);
			$blocks_in_db_8  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-23 09:00:00', 'end_datetime' => '2013-09-23 13:00:00'], $this->DB);
			$blocks_in_db_9  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-29 09:00:00', 'end_datetime' => '2013-09-29 13:00:00'], $this->DB);
			$blocks_in_db_10 = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-30 09:00:00', 'end_datetime' => '2013-09-30 13:00:00'], $this->DB);

			// test scheduleRepeatInterval=1 (Weekly)
			$this->assertEqual(count($blocks_in_db_1), 1);
			$this->assertEqual(count($blocks_in_db_2), 1);
			$this->assertEqual(count($blocks_in_db_3), 1);
			$this->assertEqual(count($blocks_in_db_4), 1);
			$this->assertEqual(count($blocks_in_db_5), 1);
			$this->assertEqual(count($blocks_in_db_6), 1);
			$this->assertEqual(count($blocks_in_db_7), 1);
			$this->assertEqual(count($blocks_in_db_8), 1);
			$this->assertEqual(count($blocks_in_db_9), 1);
			$this->assertEqual(count($blocks_in_db_10), 1);

			$qm = QueuedMessage::getOneFromDb(['target' => 'tu6@inst.edu'], $this->DB);
			//print_r($qm);
			$this->assertEqual(count($qm), 1);
			$this->assertEqual($qm->target, 'tu6@inst.edu');
		}

		function testSuccessOnCreateRepeatingMultipleItemsIsManagerBiWeekly() {
			$this->signIn();
			$this->get($this->urlbase);

			$par                               = $this->getBaseUrlParamsArray();
			$par['scheduleStartOnDate']        = '2013-09-01';
			$par['scheduleEndOnDate']          = '2013-09-30';
			$par['scheduleStartTimeConverted'] = '09:00:00';
			$par['scheduleDuration']           = '4H';
			$par['repeat_dow_sun']             = TRUE;
			$par['repeat_dow_mon']             = TRUE;
			$par['scheduleFrequencyType']      = 'weekly';
			$par['scheduleRepeatInterval']     = '2';
			$par['scheduleSummaryText']        = 'Every%202%20weeks%20at%2009:00%20AM%20for%204%20hours%20on%20(Sunday,%20Monday),%20until%202013-09-30';

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));
			$results = json_decode($this->getBrowser()->getContent(), TRUE);
			// $this->dump($this->urlbase . "?" . $this->urlParamsArrayToString($par));

			$this->assertEqual('success', $results['status']);

			$blocks_in_db_1  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-01 09:00:00', 'end_datetime' => '2013-09-01 13:00:00'], $this->DB);
			$blocks_in_db_2  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-02 09:00:00', 'end_datetime' => '2013-09-02 13:00:00'], $this->DB);
			$blocks_in_db_3  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-08 09:00:00', 'end_datetime' => '2013-09-08 13:00:00'], $this->DB);
			$blocks_in_db_4  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-09 09:00:00', 'end_datetime' => '2013-09-09 13:00:00'], $this->DB);
			$blocks_in_db_5  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-15 09:00:00', 'end_datetime' => '2013-09-15 13:00:00'], $this->DB);
			$blocks_in_db_6  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-16 09:00:00', 'end_datetime' => '2013-09-16 13:00:00'], $this->DB);
			$blocks_in_db_7  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-22 09:00:00', 'end_datetime' => '2013-09-22 13:00:00'], $this->DB);
			$blocks_in_db_8  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-23 09:00:00', 'end_datetime' => '2013-09-23 13:00:00'], $this->DB);
			$blocks_in_db_9  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-29 09:00:00', 'end_datetime' => '2013-09-29 13:00:00'], $this->DB);
			$blocks_in_db_10 = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-30 09:00:00', 'end_datetime' => '2013-09-30 13:00:00'], $this->DB);

			// test scheduleRepeatInterval=2 (BiWeekly)
			$this->assertEqual(count($blocks_in_db_1), 1);
			$this->assertEqual(count($blocks_in_db_2), 1);
			$this->assertEqual(count($blocks_in_db_3), 0);
			$this->assertEqual(count($blocks_in_db_4), 0);
			$this->assertEqual(count($blocks_in_db_5), 1);
			$this->assertEqual(count($blocks_in_db_6), 1);
			$this->assertEqual(count($blocks_in_db_7), 0);
			$this->assertEqual(count($blocks_in_db_8), 0);
			$this->assertEqual(count($blocks_in_db_9), 1);
			$this->assertEqual(count($blocks_in_db_10), 1);

			$qm = QueuedMessage::getOneFromDb(['target' => 'tu6@inst.edu'], $this->DB);
			//print_r($qm);
			$this->assertEqual(count($qm), 1);
			$this->assertEqual($qm->target, 'tu6@inst.edu');
		}

		function testSuccessOnCreateRepeatingMultipleItemsIsManagerMonthly() {
			$this->signIn();
			$this->get($this->urlbase);

			$par                               = $this->getBaseUrlParamsArray();
			$par['scheduleStartOnDate']        = '2013-09-01';
			$par['scheduleEndOnDate']          = '2014-03-31';
			$par['scheduleStartTimeConverted'] = '08:00:00';
			$par['scheduleDuration']           = '2H';
			$par['repeat_dom_1']               = TRUE;
			$par['repeat_dom_30']              = TRUE;
			$par['scheduleFrequencyType']      = 'monthly';
			$par['scheduleRepeatInterval']     = '1';
			$par['scheduleSummaryText']        = 'Every%201%20months%20at%2008:00%20AM%20for%202%20hours%20on%20days%20(1,%2030),%20until%202014-03-31';

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));
			$results = json_decode($this->getBrowser()->getContent(), TRUE);
			// $this->dump($this->urlbase . "?" . $this->urlParamsArrayToString($par));

			$this->assertEqual('success', $results['status']);

			$blocks_in_db_1  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-01 08:00:00', 'end_datetime' => '2013-09-01 10:00:00'], $this->DB);
			$blocks_in_db_2  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-30 08:00:00', 'end_datetime' => '2013-09-30 10:00:00'], $this->DB);
			$blocks_in_db_3  = TimeBlock::getAllFromDb(['start_datetime' => '2013-10-01 08:00:00', 'end_datetime' => '2013-10-01 10:00:00'], $this->DB);
			$blocks_in_db_4  = TimeBlock::getAllFromDb(['start_datetime' => '2013-10-30 08:00:00', 'end_datetime' => '2013-10-30 10:00:00'], $this->DB);
			$blocks_in_db_5  = TimeBlock::getAllFromDb(['start_datetime' => '2013-11-01 08:00:00', 'end_datetime' => '2013-11-01 10:00:00'], $this->DB);
			$blocks_in_db_6  = TimeBlock::getAllFromDb(['start_datetime' => '2013-11-30 08:00:00', 'end_datetime' => '2013-11-30 10:00:00'], $this->DB);
			$blocks_in_db_7  = TimeBlock::getAllFromDb(['start_datetime' => '2013-12-01 08:00:00', 'end_datetime' => '2013-12-01 10:00:00'], $this->DB);
			$blocks_in_db_8  = TimeBlock::getAllFromDb(['start_datetime' => '2013-12-30 08:00:00', 'end_datetime' => '2013-12-30 10:00:00'], $this->DB);
			$blocks_in_db_9  = TimeBlock::getAllFromDb(['start_datetime' => '2014-01-01 08:00:00', 'end_datetime' => '2014-01-01 10:00:00'], $this->DB);
			$blocks_in_db_10 = TimeBlock::getAllFromDb(['start_datetime' => '2014-01-30 08:00:00', 'end_datetime' => '2014-01-30 10:00:00'], $this->DB);
			$blocks_in_db_11 = TimeBlock::getAllFromDb(['start_datetime' => '2014-02-01 08:00:00', 'end_datetime' => '2014-02-01 10:00:00'], $this->DB);
			$blocks_in_db_12 = TimeBlock::getAllFromDb(['start_datetime' => '2014-02-30 08:00:00', 'end_datetime' => '2014-02-30 10:00:00'], $this->DB);
			$blocks_in_db_13 = TimeBlock::getAllFromDb(['start_datetime' => '2014-03-01 08:00:00', 'end_datetime' => '2014-03-01 10:00:00'], $this->DB);
			$blocks_in_db_14 = TimeBlock::getAllFromDb(['start_datetime' => '2014-03-30 08:00:00', 'end_datetime' => '2014-03-30 10:00:00'], $this->DB);

			// test scheduleRepeatInterval=1 (Monthly)
			$this->assertEqual(count($blocks_in_db_1), 1);
			$this->assertEqual(count($blocks_in_db_2), 1);
			$this->assertEqual(count($blocks_in_db_3), 1);
			$this->assertEqual(count($blocks_in_db_4), 1);
			$this->assertEqual(count($blocks_in_db_5), 1);
			$this->assertEqual(count($blocks_in_db_6), 1);
			$this->assertEqual(count($blocks_in_db_7), 1);
			$this->assertEqual(count($blocks_in_db_8), 1);
			$this->assertEqual(count($blocks_in_db_9), 1);
			$this->assertEqual(count($blocks_in_db_10), 1);
			$this->assertEqual(count($blocks_in_db_11), 1);
			$this->assertEqual(count($blocks_in_db_12), 0); // February!
			$this->assertEqual(count($blocks_in_db_13), 1);
			$this->assertEqual(count($blocks_in_db_14), 1);

			$qm = QueuedMessage::getOneFromDb(['target' => 'tu6@inst.edu'], $this->DB);
			//print_r($qm);
			$this->assertEqual(count($qm), 1);
			$this->assertEqual($qm->target, 'tu6@inst.edu');
		}

		function testSuccessOnCreateRepeatingMultipleItemsIsManagerBiMonthly() {
			$this->signIn();
			$this->get($this->urlbase);

			$par                               = $this->getBaseUrlParamsArray();
			$par['scheduleStartOnDate']        = '2013-09-01';
			$par['scheduleEndOnDate']          = '2014-03-31';
			$par['scheduleStartTimeConverted'] = '08:00:00';
			$par['scheduleDuration']           = '2H';
			$par['repeat_dom_1']               = TRUE;
			$par['repeat_dom_30']              = TRUE;
			$par['scheduleFrequencyType']      = 'monthly';
			$par['scheduleRepeatInterval']     = '2';
			$par['scheduleSummaryText']        = 'Every%202%20months%20at%2008:00%20AM%20for%202%20hours%20on%20days%20(1,%2030),%20until%202014-03-31';

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));
			$results = json_decode($this->getBrowser()->getContent(), TRUE);
			// $this->dump($this->urlbase . "?" . $this->urlParamsArrayToString($par));

			$this->assertEqual('success', $results['status']);

			$blocks_in_db_1  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-01 08:00:00', 'end_datetime' => '2013-09-01 10:00:00'], $this->DB);
			$blocks_in_db_2  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-30 08:00:00', 'end_datetime' => '2013-09-30 10:00:00'], $this->DB);
			$blocks_in_db_3  = TimeBlock::getAllFromDb(['start_datetime' => '2013-10-01 08:00:00', 'end_datetime' => '2013-10-01 10:00:00'], $this->DB);
			$blocks_in_db_4  = TimeBlock::getAllFromDb(['start_datetime' => '2013-10-30 08:00:00', 'end_datetime' => '2013-10-30 10:00:00'], $this->DB);
			$blocks_in_db_5  = TimeBlock::getAllFromDb(['start_datetime' => '2013-11-01 08:00:00', 'end_datetime' => '2013-11-01 10:00:00'], $this->DB);
			$blocks_in_db_6  = TimeBlock::getAllFromDb(['start_datetime' => '2013-11-30 08:00:00', 'end_datetime' => '2013-11-30 10:00:00'], $this->DB);
			$blocks_in_db_7  = TimeBlock::getAllFromDb(['start_datetime' => '2013-12-01 08:00:00', 'end_datetime' => '2013-12-01 10:00:00'], $this->DB);
			$blocks_in_db_8  = TimeBlock::getAllFromDb(['start_datetime' => '2013-12-30 08:00:00', 'end_datetime' => '2013-12-30 10:00:00'], $this->DB);
			$blocks_in_db_9  = TimeBlock::getAllFromDb(['start_datetime' => '2014-01-01 08:00:00', 'end_datetime' => '2014-01-01 10:00:00'], $this->DB);
			$blocks_in_db_10 = TimeBlock::getAllFromDb(['start_datetime' => '2014-01-30 08:00:00', 'end_datetime' => '2014-01-30 10:00:00'], $this->DB);
			$blocks_in_db_11 = TimeBlock::getAllFromDb(['start_datetime' => '2014-02-01 08:00:00', 'end_datetime' => '2014-02-01 10:00:00'], $this->DB);
			$blocks_in_db_12 = TimeBlock::getAllFromDb(['start_datetime' => '2014-02-30 08:00:00', 'end_datetime' => '2014-02-30 10:00:00'], $this->DB);
			$blocks_in_db_13 = TimeBlock::getAllFromDb(['start_datetime' => '2014-03-01 08:00:00', 'end_datetime' => '2014-03-01 10:00:00'], $this->DB);
			$blocks_in_db_14 = TimeBlock::getAllFromDb(['start_datetime' => '2014-03-30 08:00:00', 'end_datetime' => '2014-03-30 10:00:00'], $this->DB);

			// test scheduleRepeatInterval=1 (Monthly)
			$this->assertEqual(count($blocks_in_db_1), 1);
			$this->assertEqual(count($blocks_in_db_2), 1);
			$this->assertEqual(count($blocks_in_db_3), 0);
			$this->assertEqual(count($blocks_in_db_4), 0);
			$this->assertEqual(count($blocks_in_db_5), 1);
			$this->assertEqual(count($blocks_in_db_6), 1);
			$this->assertEqual(count($blocks_in_db_7), 0);
			$this->assertEqual(count($blocks_in_db_8), 0);
			$this->assertEqual(count($blocks_in_db_9), 1);
			$this->assertEqual(count($blocks_in_db_10), 1);
			$this->assertEqual(count($blocks_in_db_11), 0);
			$this->assertEqual(count($blocks_in_db_12), 0);
			$this->assertEqual(count($blocks_in_db_13), 1);
			$this->assertEqual(count($blocks_in_db_14), 1);

			$qm = QueuedMessage::getOneFromDb(['target' => 'tu6@inst.edu'], $this->DB);
			//print_r($qm);
			$this->assertEqual(count($qm), 1);
			$this->assertEqual($qm->target, 'tu6@inst.edu');
		}

		function testSuccessOnCreateRepeatingMultipleItemsIsNotManagerWeekly() {
			$this->signIn();
			$this->get($this->urlbase);

			$par = $this->getBaseUrlParamsArray();
			unset($par['eqGroupID']); // remove unnecessary array elements
			unset($par['subgroup-301']); // remove unnecessary array elements
			unset($par['subgroup-302-406']);
			unset($par['subgroup-302-412']);
			unset($par['scheduleIsTypeManager']); // set type to consumer (i.e. not manager); NOTE: this is a checkbox: if not checked, it should not exist in this array
			$par['eqGroupID']        = '202'; // set group to one which the user has consumer access and NOT manager access
			$par['subgroup-306-409'] = '409'; // add necessary array element that corresponds to subgroup and subgroup item
			$par['subgroup-306-411'] = '411'; // add necessary array element that corresponds to subgroup and subgroup item

			$par['scheduleStartOnDate']        = '2013-09-01';
			$par['scheduleEndOnDate']          = '2013-09-30';
			$par['scheduleStartTimeConverted'] = '09:00:00';
			$par['scheduleDuration']           = '4H';
			$par['repeat_dow_sun']             = TRUE;
			$par['repeat_dow_mon']             = TRUE;
			$par['scheduleFrequencyType']      = 'weekly';
			$par['scheduleRepeatInterval']     = '1';
			$par['scheduleSummaryText']        = 'Every%201%20weeks%20at%2009:00%20AM%20for%204%20hours%20on%20(Sunday,%20Monday),%20until%202013-09-30';

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));
			$results = json_decode($this->getBrowser()->getContent(), TRUE);
			// $this->dump($this->urlbase . "?" . $this->urlParamsArrayToString($par));

			$this->assertEqual('success', $results['status']);

			$blocks_in_db_1  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-01 09:00:00', 'end_datetime' => '2013-09-01 13:00:00'], $this->DB);
			$blocks_in_db_2  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-02 09:00:00', 'end_datetime' => '2013-09-02 13:00:00'], $this->DB);
			$blocks_in_db_3  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-08 09:00:00', 'end_datetime' => '2013-09-08 13:00:00'], $this->DB);
			$blocks_in_db_4  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-09 09:00:00', 'end_datetime' => '2013-09-09 13:00:00'], $this->DB);
			$blocks_in_db_5  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-15 09:00:00', 'end_datetime' => '2013-09-15 13:00:00'], $this->DB);
			$blocks_in_db_6  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-16 09:00:00', 'end_datetime' => '2013-09-16 13:00:00'], $this->DB);
			$blocks_in_db_7  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-22 09:00:00', 'end_datetime' => '2013-09-22 13:00:00'], $this->DB);
			$blocks_in_db_8  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-23 09:00:00', 'end_datetime' => '2013-09-23 13:00:00'], $this->DB);
			$blocks_in_db_9  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-29 09:00:00', 'end_datetime' => '2013-09-29 13:00:00'], $this->DB);
			$blocks_in_db_10 = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-30 09:00:00', 'end_datetime' => '2013-09-30 13:00:00'], $this->DB);

			// test scheduleRepeatInterval=1 (Weekly)
			$this->assertEqual(count($blocks_in_db_1), 1);
			$this->assertEqual(count($blocks_in_db_2), 1);
			$this->assertEqual(count($blocks_in_db_3), 1);
			$this->assertEqual(count($blocks_in_db_4), 1);
			$this->assertEqual(count($blocks_in_db_5), 1);
			$this->assertEqual(count($blocks_in_db_6), 1);
			$this->assertEqual(count($blocks_in_db_7), 1);
			$this->assertEqual(count($blocks_in_db_8), 1);
			$this->assertEqual(count($blocks_in_db_9), 1);
			$this->assertEqual(count($blocks_in_db_10), 1);

			$qm = QueuedMessage::getOneFromDb(['target' => 'vbovine@institution.edu'], $this->DB);
			//print_r($qm);
			$this->assertEqual(count($qm), 1);
			$this->assertEqual($qm->target, 'vbovine@institution.edu');
		}

		function testSuccessOnCreateRepeatingMultipleItemsIsNotManagerBiWeekly() {
			$this->signIn();
			$this->get($this->urlbase);

			$par = $this->getBaseUrlParamsArray();
			unset($par['eqGroupID']); // remove unnecessary array elements
			unset($par['subgroup-301']); // remove unnecessary array elements
			unset($par['subgroup-302-406']);
			unset($par['subgroup-302-412']);
			unset($par['scheduleIsTypeManager']); // set type to consumer (i.e. not manager); NOTE: this is a checkbox: if not checked, it should not exist in this array
			$par['eqGroupID']        = '202'; // set group to one which the user has consumer access and NOT manager access
			$par['subgroup-306-409'] = '409'; // add necessary array element that corresponds to subgroup and subgroup item
			$par['subgroup-306-411'] = '411'; // add necessary array element that corresponds to subgroup and subgroup item

			$par['scheduleStartOnDate']        = '2013-09-01';
			$par['scheduleEndOnDate']          = '2013-09-30';
			$par['scheduleStartTimeConverted'] = '09:00:00';
			$par['scheduleDuration']           = '4H';
			$par['repeat_dow_sun']             = TRUE;
			$par['repeat_dow_mon']             = TRUE;
			$par['scheduleFrequencyType']      = 'weekly';
			$par['scheduleRepeatInterval']     = '2';
			$par['scheduleSummaryText']        = 'Every%202%20weeks%20at%2009:00%20AM%20for%204%20hours%20on%20(Sunday,%20Monday),%20until%202013-09-30';

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));
			$results = json_decode($this->getBrowser()->getContent(), TRUE);
			// $this->dump($this->urlbase . "?" . $this->urlParamsArrayToString($par));

			$this->assertEqual('success', $results['status']);

			$blocks_in_db_1  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-01 09:00:00', 'end_datetime' => '2013-09-01 13:00:00'], $this->DB);
			$blocks_in_db_2  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-02 09:00:00', 'end_datetime' => '2013-09-02 13:00:00'], $this->DB);
			$blocks_in_db_3  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-08 09:00:00', 'end_datetime' => '2013-09-08 13:00:00'], $this->DB);
			$blocks_in_db_4  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-09 09:00:00', 'end_datetime' => '2013-09-09 13:00:00'], $this->DB);
			$blocks_in_db_5  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-15 09:00:00', 'end_datetime' => '2013-09-15 13:00:00'], $this->DB);
			$blocks_in_db_6  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-16 09:00:00', 'end_datetime' => '2013-09-16 13:00:00'], $this->DB);
			$blocks_in_db_7  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-22 09:00:00', 'end_datetime' => '2013-09-22 13:00:00'], $this->DB);
			$blocks_in_db_8  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-23 09:00:00', 'end_datetime' => '2013-09-23 13:00:00'], $this->DB);
			$blocks_in_db_9  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-29 09:00:00', 'end_datetime' => '2013-09-29 13:00:00'], $this->DB);
			$blocks_in_db_10 = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-30 09:00:00', 'end_datetime' => '2013-09-30 13:00:00'], $this->DB);

			// test scheduleRepeatInterval=2 (BiWeekly)
			$this->assertEqual(count($blocks_in_db_1), 1);
			$this->assertEqual(count($blocks_in_db_2), 1);
			$this->assertEqual(count($blocks_in_db_3), 0);
			$this->assertEqual(count($blocks_in_db_4), 0);
			$this->assertEqual(count($blocks_in_db_5), 1);
			$this->assertEqual(count($blocks_in_db_6), 1);
			$this->assertEqual(count($blocks_in_db_7), 0);
			$this->assertEqual(count($blocks_in_db_8), 0);
			$this->assertEqual(count($blocks_in_db_9), 1);
			$this->assertEqual(count($blocks_in_db_10), 1);

			$qm = QueuedMessage::getOneFromDb(['target' => 'vbovine@institution.edu'], $this->DB);
			//print_r($qm);
			$this->assertEqual(count($qm), 1);
			$this->assertEqual($qm->target, 'vbovine@institution.edu');
		}

		function testSuccessOnCreateRepeatingMultipleItemsIsNotManagerMonthly() {
			$this->signIn();
			$this->get($this->urlbase);

			$par = $this->getBaseUrlParamsArray();
			unset($par['eqGroupID']); // remove unnecessary array elements
			unset($par['subgroup-301']); // remove unnecessary array elements
			unset($par['subgroup-302-406']);
			unset($par['subgroup-302-412']);
			unset($par['scheduleIsTypeManager']); // set type to consumer (i.e. not manager); NOTE: this is a checkbox: if not checked, it should not exist in this array
			$par['eqGroupID']        = '202'; // set group to one which the user has consumer access and NOT manager access
			$par['subgroup-306-409'] = '409'; // add necessary array element that corresponds to subgroup and subgroup item
			$par['subgroup-306-411'] = '411'; // add necessary array element that corresponds to subgroup and subgroup item

			$par['scheduleStartOnDate']        = '2013-09-01';
			$par['scheduleEndOnDate']          = '2014-03-31';
			$par['scheduleStartTimeConverted'] = '08:00:00';
			$par['scheduleDuration']           = '2H';
			$par['repeat_dom_1']               = TRUE;
			$par['repeat_dom_30']              = TRUE;
			$par['scheduleFrequencyType']      = 'monthly';
			$par['scheduleRepeatInterval']     = '1';
			$par['scheduleSummaryText']        = 'Every%201%20months%20at%2008:00%20AM%20for%202%20hours%20on%20days%20(1,%2030),%20until%202014-03-31';

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));
			$results = json_decode($this->getBrowser()->getContent(), TRUE);
			// $this->dump($this->urlbase . "?" . $this->urlParamsArrayToString($par));

			$this->assertEqual('success', $results['status']);

			$blocks_in_db_1  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-01 08:00:00', 'end_datetime' => '2013-09-01 10:00:00'], $this->DB);
			$blocks_in_db_2  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-30 08:00:00', 'end_datetime' => '2013-09-30 10:00:00'], $this->DB);
			$blocks_in_db_3  = TimeBlock::getAllFromDb(['start_datetime' => '2013-10-01 08:00:00', 'end_datetime' => '2013-10-01 10:00:00'], $this->DB);
			$blocks_in_db_4  = TimeBlock::getAllFromDb(['start_datetime' => '2013-10-30 08:00:00', 'end_datetime' => '2013-10-30 10:00:00'], $this->DB);
			$blocks_in_db_5  = TimeBlock::getAllFromDb(['start_datetime' => '2013-11-01 08:00:00', 'end_datetime' => '2013-11-01 10:00:00'], $this->DB);
			$blocks_in_db_6  = TimeBlock::getAllFromDb(['start_datetime' => '2013-11-30 08:00:00', 'end_datetime' => '2013-11-30 10:00:00'], $this->DB);
			$blocks_in_db_7  = TimeBlock::getAllFromDb(['start_datetime' => '2013-12-01 08:00:00', 'end_datetime' => '2013-12-01 10:00:00'], $this->DB);
			$blocks_in_db_8  = TimeBlock::getAllFromDb(['start_datetime' => '2013-12-30 08:00:00', 'end_datetime' => '2013-12-30 10:00:00'], $this->DB);
			$blocks_in_db_9  = TimeBlock::getAllFromDb(['start_datetime' => '2014-01-01 08:00:00', 'end_datetime' => '2014-01-01 10:00:00'], $this->DB);
			$blocks_in_db_10 = TimeBlock::getAllFromDb(['start_datetime' => '2014-01-30 08:00:00', 'end_datetime' => '2014-01-30 10:00:00'], $this->DB);
			$blocks_in_db_11 = TimeBlock::getAllFromDb(['start_datetime' => '2014-02-01 08:00:00', 'end_datetime' => '2014-02-01 10:00:00'], $this->DB);
			$blocks_in_db_12 = TimeBlock::getAllFromDb(['start_datetime' => '2014-02-30 08:00:00', 'end_datetime' => '2014-02-30 10:00:00'], $this->DB);
			$blocks_in_db_13 = TimeBlock::getAllFromDb(['start_datetime' => '2014-03-01 08:00:00', 'end_datetime' => '2014-03-01 10:00:00'], $this->DB);
			$blocks_in_db_14 = TimeBlock::getAllFromDb(['start_datetime' => '2014-03-30 08:00:00', 'end_datetime' => '2014-03-30 10:00:00'], $this->DB);

			// test scheduleRepeatInterval=1 (Monthly)
			$this->assertEqual(count($blocks_in_db_1), 1);
			$this->assertEqual(count($blocks_in_db_2), 1);
			$this->assertEqual(count($blocks_in_db_3), 1);
			$this->assertEqual(count($blocks_in_db_4), 1);
			$this->assertEqual(count($blocks_in_db_5), 1);
			$this->assertEqual(count($blocks_in_db_6), 1);
			$this->assertEqual(count($blocks_in_db_7), 1);
			$this->assertEqual(count($blocks_in_db_8), 1);
			$this->assertEqual(count($blocks_in_db_9), 1);
			$this->assertEqual(count($blocks_in_db_10), 1);
			$this->assertEqual(count($blocks_in_db_11), 1);
			$this->assertEqual(count($blocks_in_db_12), 0); // February!
			$this->assertEqual(count($blocks_in_db_13), 1);
			$this->assertEqual(count($blocks_in_db_14), 1);

			$qm = QueuedMessage::getOneFromDb(['target' => 'vbovine@institution.edu'], $this->DB);
			//print_r($qm);
			$this->assertEqual(count($qm), 1);
			$this->assertEqual($qm->target, 'vbovine@institution.edu');
		}

		function testSuccessOnCreateRepeatingMultipleItemsIsNotManagerBiMonthly() {
			$this->signIn();
			$this->get($this->urlbase);

			$par = $this->getBaseUrlParamsArray();
			unset($par['eqGroupID']); // remove unnecessary array elements
			unset($par['subgroup-301']); // remove unnecessary array elements
			unset($par['subgroup-302-406']);
			unset($par['subgroup-302-412']);
			unset($par['scheduleIsTypeManager']); // set type to consumer (i.e. not manager); NOTE: this is a checkbox: if not checked, it should not exist in this array
			$par['eqGroupID']        = '202'; // set group to one which the user has consumer access and NOT manager access
			$par['subgroup-306-409'] = '409'; // add necessary array element that corresponds to subgroup and subgroup item
			$par['subgroup-306-411'] = '411'; // add necessary array element that corresponds to subgroup and subgroup item

			$par['scheduleStartOnDate']        = '2013-09-01';
			$par['scheduleEndOnDate']          = '2014-03-31';
			$par['scheduleStartTimeConverted'] = '08:00:00';
			$par['scheduleDuration']           = '2H';
			$par['repeat_dom_1']               = TRUE;
			$par['repeat_dom_30']              = TRUE;
			$par['scheduleFrequencyType']      = 'monthly';
			$par['scheduleRepeatInterval']     = '2';
			$par['scheduleSummaryText']        = 'Every%202%20months%20at%2008:00%20AM%20for%202%20hours%20on%20days%20(1,%2030),%20until%202014-03-31';

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));
			$results = json_decode($this->getBrowser()->getContent(), TRUE);
			// $this->dump($this->urlbase . "?" . $this->urlParamsArrayToString($par));

			$this->assertEqual('success', $results['status']);

			$blocks_in_db_1  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-01 08:00:00', 'end_datetime' => '2013-09-01 10:00:00'], $this->DB);
			$blocks_in_db_2  = TimeBlock::getAllFromDb(['start_datetime' => '2013-09-30 08:00:00', 'end_datetime' => '2013-09-30 10:00:00'], $this->DB);
			$blocks_in_db_3  = TimeBlock::getAllFromDb(['start_datetime' => '2013-10-01 08:00:00', 'end_datetime' => '2013-10-01 10:00:00'], $this->DB);
			$blocks_in_db_4  = TimeBlock::getAllFromDb(['start_datetime' => '2013-10-30 08:00:00', 'end_datetime' => '2013-10-30 10:00:00'], $this->DB);
			$blocks_in_db_5  = TimeBlock::getAllFromDb(['start_datetime' => '2013-11-01 08:00:00', 'end_datetime' => '2013-11-01 10:00:00'], $this->DB);
			$blocks_in_db_6  = TimeBlock::getAllFromDb(['start_datetime' => '2013-11-30 08:00:00', 'end_datetime' => '2013-11-30 10:00:00'], $this->DB);
			$blocks_in_db_7  = TimeBlock::getAllFromDb(['start_datetime' => '2013-12-01 08:00:00', 'end_datetime' => '2013-12-01 10:00:00'], $this->DB);
			$blocks_in_db_8  = TimeBlock::getAllFromDb(['start_datetime' => '2013-12-30 08:00:00', 'end_datetime' => '2013-12-30 10:00:00'], $this->DB);
			$blocks_in_db_9  = TimeBlock::getAllFromDb(['start_datetime' => '2014-01-01 08:00:00', 'end_datetime' => '2014-01-01 10:00:00'], $this->DB);
			$blocks_in_db_10 = TimeBlock::getAllFromDb(['start_datetime' => '2014-01-30 08:00:00', 'end_datetime' => '2014-01-30 10:00:00'], $this->DB);
			$blocks_in_db_11 = TimeBlock::getAllFromDb(['start_datetime' => '2014-02-01 08:00:00', 'end_datetime' => '2014-02-01 10:00:00'], $this->DB);
			$blocks_in_db_12 = TimeBlock::getAllFromDb(['start_datetime' => '2014-02-30 08:00:00', 'end_datetime' => '2014-02-30 10:00:00'], $this->DB);
			$blocks_in_db_13 = TimeBlock::getAllFromDb(['start_datetime' => '2014-03-01 08:00:00', 'end_datetime' => '2014-03-01 10:00:00'], $this->DB);
			$blocks_in_db_14 = TimeBlock::getAllFromDb(['start_datetime' => '2014-03-30 08:00:00', 'end_datetime' => '2014-03-30 10:00:00'], $this->DB);

			// test scheduleRepeatInterval=1 (Monthly)
			$this->assertEqual(count($blocks_in_db_1), 1);
			$this->assertEqual(count($blocks_in_db_2), 1);
			$this->assertEqual(count($blocks_in_db_3), 0);
			$this->assertEqual(count($blocks_in_db_4), 0);
			$this->assertEqual(count($blocks_in_db_5), 1);
			$this->assertEqual(count($blocks_in_db_6), 1);
			$this->assertEqual(count($blocks_in_db_7), 0);
			$this->assertEqual(count($blocks_in_db_8), 0);
			$this->assertEqual(count($blocks_in_db_9), 1);
			$this->assertEqual(count($blocks_in_db_10), 1);
			$this->assertEqual(count($blocks_in_db_11), 0);
			$this->assertEqual(count($blocks_in_db_12), 0);
			$this->assertEqual(count($blocks_in_db_13), 1);
			$this->assertEqual(count($blocks_in_db_14), 1);

			$qm = QueuedMessage::getOneFromDb(['target' => 'vbovine@institution.edu'], $this->DB);
			//print_r($qm);
			$this->assertEqual(count($qm), 1);
			$this->assertEqual($qm->target, 'vbovine@institution.edu');
		}

		public function testCreateDefaultCommPrefsForValidUserLackingCommPrefs() {
			$u = User::getOneFromDb(['user_id' => 1107], $this->DB);

			# assert that user_id=1107 lacks permission to eq_group_id=201
			$p = Permission::getAllFromDb(['entity_id' => '1107', 'entity_type' => 'user'], $this->DB);
			$this->assertEqual(count($p), 0);

			# assert that user_id=1107 lacks a comm_prefs record for eq_group_id=201
			$c = CommPref::getAllFromDb(['user_id' => '1107', 'eq_group_id' => '201'], $this->DB);
			$this->assertEqual(count($c), 0);

			# create a permission for user_id=1107 to have manager access for eq_group_id=201
			$p = new Permission(['permission_id'=>3000,'entity_id'=>'1107','entity_type'=>'user','role_id'=>'1','eq_group_id'=>'201','flag_delete'=>'0','DB'=>$this->DB]);
			$p->updateDb();

			# Test that a successful reservation will automatically create the missing comm_pref DB record for user_id=1107 and eq_group_id=201
			$this->signIn();
			$this->get($this->urlbase);

			$blocks_in_db        = TimeBlock::getAllFromDb(['time_block_id !=' => 0], $this->DB);
			$initial_block_count = count($blocks_in_db);

			$par = $this->getBaseUrlParamsArray();

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));

			$blocks_in_db = TimeBlock::getAllFromDb(['time_block_id !=' => 0], $this->DB);
			$this->assertEqual(count($blocks_in_db), $initial_block_count + 1);


			# assert that user_id=1107 now has one comm_pref with default values (0,0,0)
			$c = CommPref::getOneFromDb(['user_id' => '1107', 'eq_group_id' => '201'], $this->DB);
			$this->assertEqual(count($c), 1);
			$this->assertEqual($c->flag_alert_on_upcoming_reservation, 0);
			$this->assertEqual($c->flag_contact_on_reserve_create, 0);
			$this->assertEqual($c->flag_contact_on_reserve_cancel, 0);
		}

		function testSuccessOnCreateRepeatingSingleItemTimingConflictOverrideIsManager() {

			//			QueuedMessage::getOneFromDb(['target'=>],$this->DB);
			$this->fail("to be implemented");
		}

		function testSuccessOnCreateRepeatingMultipleItemsTimingConflictOverrideIsManager() {

			//			QueuedMessage::getOneFromDb(['target'=>],$this->DB);
			$this->fail("to be implemented");
		}

		function testSuccessOnCreateRepeatingMultipleItemsIsManagerWeeklyAndGenerateMultipleQueuedMessages() {
			// unnecessary test??
			//			QueuedMessage::getOneFromDb(['target'=>],$this->DB);
			$this->fail("to be implemented");
		}

		# TODO - Do we need to standardize our use of SystemAdmin using canManageEqGroup() or other fxns for SystemAdmin CRUD work

	}