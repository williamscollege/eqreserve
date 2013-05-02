<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class AjaxEqGroupTest extends WMSWebTestCase {

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

    function testEqGroupAjaxInvalidAction() {
//        $this->signIn();
//
//        $this->get('http://localhost/eqreserve/ajax_schedule.php?schedule=1007&scheduleAction=deleteSchedule');
//
//        $this->assertPattern('/"status":"failure"/');
//
//        $s = Schedule::getOneFromDb(['schedule_id'=>1001],$this->DB);
//        $this->assertTrue($s->matchesDb);
        $this->fail();
    }

    function testEqGroupAjaxNonManagerGetsNoAccess() {
        $this->fail();
    }

    function testEqGroupAjaxSystemAdminGetsAccess() {
        $this->fail();
    }

    function testEqGroupAjaxNonAdminCannotRemoveTheirOwnManagerAccess() {
        $this->fail();
    }

    function testEqGroupAjaxRemoveUserManagerAccess() {
        $this->fail();
    }

    function testEqGroupAjaxRemoveUserConsumerAccess() {
        $this->fail();
    }

    function testEqGroupAjaxRemoveInstGroupManagerAccess() {
        $this->fail();
    }

    function testEqGroupAjaxRemoveInstGroupConsumerAccess() {
        $this->fail();
    }
}