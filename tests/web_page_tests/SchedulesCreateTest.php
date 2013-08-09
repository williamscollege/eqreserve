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
			'eqGroupID' => '201'
			,'subgroup-301' => '402'
			,'scheduleStartTimeConverted' => '14:45:00'
			,'scheduleSummaryText' => 'Once%20at%2002:45%20PM%20for%200%20minutes%20until%202013-08-06'
			,'scheduleStartOnDate' => '2013-08-06'
			,'scheduleEndOnDate' => '2013-08-06'
			,'hour' => '02'
			,'minute' => '45'
			,'meridian' => 'PM'
			,'scheduleDuration' => '0'
			,'scheduleFrequencyType' => 'no_repeat'
			,'scheduleRepeatInterval' => '1'
			,'scheduleIsTypeManager' => 'on'
			,'repeat_dow_sun' => '0'
			,'repeat_dow_mon' => '0'
			,'repeat_dow_tue' => '0'
			,'repeat_dow_wed' => '0'
			,'repeat_dow_thu' => '0'
			,'repeat_dow_fri' => '0'
			,'repeat_dow_sat' => '0'
			,'repeat_dom_1' => '0'
			,'repeat_dom_2' => '0'
			,'repeat_dom_3' => '0'
			,'repeat_dom_4' => '0'
			,'repeat_dom_5' => '0'
			,'repeat_dom_6' => '0'
			,'repeat_dom_7' => '0'
			,'repeat_dom_8' => '0'
			,'repeat_dom_9' => '0'
			,'repeat_dom_10' => '0'
			,'repeat_dom_11' => '0'
			,'repeat_dom_12' => '0'
			,'repeat_dom_13' => '0'
			,'repeat_dom_14' => '0'
			,'repeat_dom_15' => '0'
			,'repeat_dom_16' => '0'
			,'repeat_dom_17' => '0'
			,'repeat_dom_18' => '0'
			,'repeat_dom_19' => '0'
			,'repeat_dom_20' => '0'
			,'repeat_dom_21' => '0'
			,'repeat_dom_22' => '0'
			,'repeat_dom_23' => '0'
			,'repeat_dom_24' => '0'
			,'repeat_dom_25' => '0'
			,'repeat_dom_26' => '0'
			,'repeat_dom_27' => '0'
			,'repeat_dom_28' => '0'
			,'repeat_dom_29' => '0'
			,'repeat_dom_30' => '0'
			,'repeat_dom_31' => '0'
			,'btnReservationSubmit' => ''
		);
	}

	function urlParamsArrayToString($ar) {
		$ret = '';
		foreach ($ar as $p=>$v) {
			if ($ret) { $ret .= '&'; }
			$ret .= "$p=$v";
		}
		return $ret;
	}

    //############################################################
    // access tests

    function testManagerAccessForManagerSchedule() {
		$this->signIn();
		$par = $this->getBaseUrlParamsArray();

		$this->get($this->urlbase."?".$this->urlParamsArrayToString($par));

		$this->assertNoPattern('/cannot create manager reservation  - not a manager of this group/i');
		$this->assertNoPattern('/failure/i');
		$this->assertPattern('/success/i');
    }

    function testManagerAccessForConsumerSchedule() {
		$this->signIn();

		$par = $this->getBaseUrlParamsArray();
		$par['scheduleIsTypeManager'] = ''; // set type to consumer (i.e. not manager)

		$this->get($this->urlbase."?".$this->urlParamsArrayToString($par));

		$this->assertNoPattern('/cannot create user reservation - not a user of this group/i');
		$this->assertNoPattern('/failure/i');
		$this->assertPattern('/success/i');
    }

    function testConsumerNoAccessForManagerSchedule() {
		$this->signIn();
		$par = $this->getBaseUrlParamsArray();
		$par['eqGroupID'] = '207'; // set group to one which the user has consumer access and NOT manager access

		$this->get($this->urlbase."?".$this->urlParamsArrayToString($par));

		$this->assertPattern('/cannot create manager reservation - not a manager of this group/i');
		$this->assertPattern('/failure/i');
		$this->assertNoPattern('/success/i');
    }

    function testConsumerAccessForConsumerSchedule() {
		$this->signIn();
		$par = $this->getBaseUrlParamsArray();
		$par['eqGroupID'] = '207'; // set group to one which the user has consumer access and NOT manager access
		$par['scheduleIsTypeManager'] = ''; // set type to consumer (i.e. not manager)

		$this->get($this->urlbase."?".$this->urlParamsArrayToString($par));

		$this->assertNoPattern('/cannot create user reservation - not a user of this group/i');
		$this->assertNoPattern('/failure/i');
		$this->assertPattern('/success/i');
    }

	function testAdminAccessForManagerSchedule() {
		$this->signIn();
		makeAuthedTestUserAdmin($this->DB);
		$par = $this->getBaseUrlParamsArray();
		$par['eqGroupID'] = '208'; // set group to one which the user has NO access

		$this->get($this->urlbase."?".$this->urlParamsArrayToString($par));

		$this->assertNoPattern('/cannot create manager reservation - not a manager of this group/i');
		$this->assertNoPattern('/failure/i');
		$this->assertPattern('/success/i');
	}

	function testAdminAccessForConsumerSchedule() {
		$this->signIn();
		makeAuthedTestUserAdmin($this->DB);
		$par = $this->getBaseUrlParamsArray();
		$par['eqGroupID'] = '208'; // set group to one which the user has NO access
		$par['scheduleIsTypeManager'] = ''; // set type to consumer (i.e. not manager)

		$this->get($this->urlbase."?".$this->urlParamsArrayToString($par));

		$this->assertNoPattern('/cannot create user reservation - not a user of this group/i');
		$this->assertNoPattern('/failure/i');
		$this->assertPattern('/success/i');
	}

	function testSignedInNoGroupAccessNoScheduling() {
		$this->signIn();

		$par = $this->getBaseUrlParamsArray();
		$par['eqGroupID'] = '208'; // set group to one which the user has NO access

		$this->get($this->urlbase."?".$this->urlParamsArrayToString($par));

		$this->assertPattern('/cannot create manager reservation - not a manager of this group/i');
		$this->assertPattern('/failure/i');
		$this->assertNoPattern('/success/i');


		$par['scheduleIsTypeManager'] = ''; // set type to consumer (i.e. not manager)

		$this->get($this->urlbase."?".$this->urlParamsArrayToString($par));

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

		$par = $this->getBaseUrlParamsArray();
		$par['eqGroupID'] = '228'; // set group to one which the user has NO access

//		echo $this->urlbase."?".$this->urlParamsArrayToString($par);
//		exit;

		$this->get($this->urlbase."?".$this->urlParamsArrayToString($par));

		$this->assertPattern('/failure/i');
		$this->assertPattern('/equipment group does not exist/i');
	}


	function testConflictOverrideOnlyOnScheduleOfTypeManager() {
        # if override set, type==manager
        # if type!=manager, override not set
        $this->fail("to be implemented");
    }

	function testTimeBlockDurationIsValid() {
		$this->signIn();

		$par = $this->getBaseUrlParamsArray();
		$par['scheduleDuration'] = '60M'; // try valid value (e.g. "60M")

		$this->get($this->urlbase."?".$this->urlParamsArrayToString($par));

		$this->assertNoPattern('/invalid time format for duration value/i');
		$this->assertNoPattern('/failure/i');
		$this->assertPattern('/success/i');


		$par['scheduleDuration'] = 'delete all'; // try invalid value (e.g. "delete all")

		$this->get($this->urlbase."?".$this->urlParamsArrayToString($par));

		$this->assertPattern('/invalid time format for duration value/i');
		$this->assertPattern('/failure/i');
		$this->assertNoPattern('/success/i');
	}

	function testCannotCreateReservationOnDeletedItems() {
		$this->signIn();

		$par = $this->getBaseUrlParamsArray();
		# try to create reservation for deleted group
		$par['eqGroupID'] = '205'; // deleted group (205)

		$this->get($this->urlbase."?".$this->urlParamsArrayToString($par));

		$this->assertPattern('/unable to create reservation for deleted group/i');
		$this->assertPattern('/failure/i');
		$this->assertNoPattern('/success/i');


		# TODO: try to create reservation for deleted subgroup (305)?
//		$this->assertNoPattern('/unable to create reservation for deleted subgroup/i');
//		$this->assertNoPattern('/failure/i');
//		$this->assertPattern('/success/i');


		# try to create reservation for deleted item
		$par['eqGroupID'] = '201'; // active group (201)
		$par['subgroup-301'] = '405'; // deleted item (405)

		$this->get($this->urlbase."?".$this->urlParamsArrayToString($par));

		$this->assertPattern('/unable to create reservation for deleted item/i');
		$this->assertPattern('/failure/i');
		$this->assertNoPattern('/success/i');
	}

    //############################################################
    // action tests
    function testCreateShortNoRepeat() {
        $this->signIn();
        $this->get($this->urlbase);

        $this->fail("to be implemented");

//        $this->assertResponse(200);
//        $this->assertNoPattern('/FAILED/i');
//        $this->assertPattern('/SUCCESS/i');
    }

}