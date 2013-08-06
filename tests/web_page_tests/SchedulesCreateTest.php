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

    //############################################################
    // access tests

    function testAdminAccessForManagerSchedule() {
        $this->fail("to be implemented");
    }

    function testAdminAccessForConsumerSchedule() {
        $this->fail("to be implemented");
    }

    function testManagerAccessForManagerSchedule() {
        $this->fail("to be implemented");
    }

    function testManagerAccessForConsumerSchedule() {
        $this->fail("to be implemented");
    }

    function testConsumerNoAccessForManagerSchedule() {
        $this->fail("to be implemented");
    }

    function testConsumerAccessForConsumerSchedule() {
        $this->fail("to be implemented");
    }

    function testSignedInNoGroupAccessNoScheduling() {
        $this->fail("to be implemented");
    }

    function testNotSignedInNoScheduling() {
        $this->get($this->urlbase);

        $this->assertText("not authenticated");
    }

    //############################################################
    // data validation tests
    function testConflictOverrideOnlyOnScheduleOfTypeManager() {
        # if override set, type==manager
        # if type!=manager, override not set
        $this->fail("to be implemented");
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