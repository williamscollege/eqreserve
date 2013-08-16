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
			unset($par['subgroup-301']); // remove unnecessary array elements
			unset($par['subgroup-302-406']);
			unset($par['subgroup-302-412']);
			unset($par['scheduleIsTypeManager']); // set type to consumer (i.e. not manager); NOTE: this is a checkbox: if not checked, it should not exist in this array
			$par['eqGroupID']    = '207'; // set group to one which the user has consumer access and NOT manager access
			$par['subgroup-308'] = '410'; // add necessary array element that corresponds to subgroup and subgroup item

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));

			//		$this->dump($par);
			//		$this->dump($this->urlParamsArrayToString($par));
			//		exit;

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

			//		echo $this->urlbase."?".$this->urlParamsArrayToString($par);
			//		exit;

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));

			$this->assertPattern('/failure/i');
			$this->assertPattern('/equipment sub-group parameter empty/i');
		}

		function testFailOnEqItemNotExists() {
			$this->signIn();

			$par                 = $this->getBaseUrlParamsArray();
			$par['subgroup-301'] = '405'; // deleted item (405)

			//		echo $this->urlbase."?".$this->urlParamsArrayToString($par);
			//		exit;

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));

			$this->assertPattern('/failure/i');
			$this->assertPattern('/equipment item does not exist or was deleted/i');
		}

		function testConflictOverrideOnlyOnScheduleOfTypeManager() {
			# if override set, type==manager
			# if type!=manager, override not set
			$this->fail("to be implemented");
		}

		function testTimeBlockDurationIsValid() {
			$this->signIn();

			$par                     = $this->getBaseUrlParamsArray();
			$par['scheduleDuration'] = '60M'; // try valid value (e.g. "60M")

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));

			$this->assertNoPattern('/invalid time format for duration value/i');
			$this->assertNoPattern('/failure/i');
			$this->assertPattern('/success/i');


			$par['scheduleDuration'] = 'delete all'; // try invalid value (e.g. "delete all")

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));

			$this->assertPattern('/invalid time format for duration value/i');
			$this->assertPattern('/failure/i');
			$this->assertNoPattern('/success/i');
		}


		//############################################################
		// action tests

		function testCreateShorterThan1DayNoRepeat() {
			$this->signIn();
			$this->get($this->urlbase);

			$user_cp = CommPref::getOneFromDb(['user_id'=>1101,'eq_group_id'=>201],$this->DB);
			$user_cp->flag_contact_on_reserve_create = true;
			$user_cp->updateDb();

			$blocks_in_db        = TimeBlock::getAllFromDb(['time_block_id !='=>0], $this->DB);
			$initial_block_count = count($blocks_in_db);

			$par = $this->getBaseUrlParamsArray();

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));
			$this->dump($this->getBrowser()->getContent());

			$blocks_in_db        = TimeBlock::getAllFromDb(['time_block_id !='=>0], $this->DB);
			$this->assertEqual(count($blocks_in_db), $initial_block_count+1);

			exit;
		}

		function testCreateNoRepeatSingleItemTimingConflictFailsIsManager() {
			$this->signIn();
			$this->get($this->urlbase);



			$blocks_in_db        = TimeBlock::getAllFromDb(['start_datetime' => '2013-03-26 10:00:00', 'end_datetime' => '2013-03-26 10:30:00'], $this->DB);
			$initial_block_count = count($blocks_in_db);

			$par                               = $this->getBaseUrlParamsArray();
			$par['scheduleStartOnDate']        = '2013-03-26';
			$par['scheduleStartTimeConverted'] = '10:00:00';
			$par['scheduleDuration']           = '30M';

			$this->get($this->urlbase . "?" . $this->urlParamsArrayToString($par));

			//			$this->dump($this->getBrowser()->getContent());

			$results = json_decode($this->getBrowser()->getContent(), TRUE);

			//			$this->dump($results);

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

		function testCreateNoRepeatSingleItemTimingConflictFailsIsNotManager() {
		}


		function testCreateRepeatingSingleItemTimingConflictFailsIsManager() {
		}

		function testCreateRepeatingSingleItemTimingConflictFailsIsNotManager() {
		}

		function testCreateRepeatingMultipleItemsTimingConflictFailsIsManager() {
		}

		function testCreateRepeatingMultipleItemsTimingConflictFailsIsNotManager() {
		}

		function testCreateRepeatingSingleItemTimingConflictOverrideIsManager() {

//			QueuedMessage::getOneFromDb(['target'=>],$this->DB);
		}

		function testCreateRepeatingMultipleItemsTimingConflictOverrideIsManager() {

//			QueuedMessage::getOneFromDb(['target'=>],$this->DB);
		}

		function testCreateRepeatingMultipleItemsSuccessIsNotManager() {
			# do specific tests for both weekly and monthly cases

//			QueuedMessage::getOneFromDb(['target'=>],$this->DB);
		}



	}