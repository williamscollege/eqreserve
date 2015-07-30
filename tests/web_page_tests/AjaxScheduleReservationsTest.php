<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class AjaxScheduleReservationsTest extends WMSWebTestCase
{

    function setUp()
    {
        createAllTestData($this->DB);
    }

    function tearDown()
    {
        removeAllTestData($this->DB);
    }

    //############################################################

    function signIn()
    {
        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/');
        $this->setField('username', TESTINGUSER);
        $this->setField('password', TESTINGPASSWORD);
        $this->click('Sign in');
    }

    function getToSchedulePage($schedId = 1006)
    {
        $this->signIn();
        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/schedule.php?schedule=' . $schedId);
    }

//    //############################################################

    function testReservationRestrictionsApplication()
    {
        $this->getToSchedulePage();

        $this->get('http://localhost:8888/eqreserve/ajax_actions/ajax_schedule_reservations.php?eqGroupID=201&restrictionMin=15&restrictionMax=60&durationChunk=15&scheduleStartTimeConverted=09%3A30%3A00&scheduleSummaryText=Once+at+09%3A30+AM+for+45+minutes+until+2015-07-30&scheduleConflictOverrideFlag=&scheduleUserType=manager&subgroup-301=402&subgroup-302-406=406&scheduleStartOnDate=2015-07-30&hour=09&minute=30&meridian=AM&scheduleStartTimeRaw=09%3A30+AM&scheduleDuration=90M&scheduleFrequencyType=no_repeat&scheduleRepeatInterval=1&repeat_dow_sun=0&repeat_dow_mon=0&repeat_dow_tue=0&repeat_dow_wed=0&repeat_dow_thu=0&repeat_dow_fri=0&repeat_dow_sat=0&repeat_dom_1=0&repeat_dom_2=0&repeat_dom_3=0&repeat_dom_4=0&repeat_dom_5=0&repeat_dom_6=0&repeat_dom_7=0&repeat_dom_8=0&repeat_dom_9=0&repeat_dom_10=0&repeat_dom_11=0&repeat_dom_12=0&repeat_dom_13=0&repeat_dom_14=0&repeat_dom_15=0&repeat_dom_16=0&repeat_dom_17=0&repeat_dom_18=0&repeat_dom_19=0&repeat_dom_20=0&repeat_dom_21=0&repeat_dom_22=0&repeat_dom_23=0&repeat_dom_24=0&repeat_dom_25=0&repeat_dom_26=0&repeat_dom_27=0&repeat_dom_28=0&repeat_dom_29=0&repeat_dom_30=0&repeat_dom_31=0&scheduleEndOnDate=2015-07-30&scheduleNotes=&btnReservationSubmit=just_clicked');

        $this->assertPattern('/"status":"failure"/');
        $this->assertPattern('/"not within reservation restrictions"/');
    }

    function testDurationRestrictionApplication()
    {
        $this->getToSchedulePage();

        $this->get('http://localhost:8888/eqreserve/ajax_actions/ajax_schedule_reservations.php?eqGroupID=201&restrictionMin=15&restrictionMax=60&durationChunk=25&scheduleStartTimeConverted=09%3A30%3A00&scheduleSummaryText=Once+at+09%3A30+AM+for+45+minutes+until+2015-07-30&scheduleConflictOverrideFlag=&scheduleUserType=manager&subgroup-301=402&subgroup-302-406=406&scheduleStartOnDate=2015-07-30&hour=09&minute=30&meridian=AM&scheduleStartTimeRaw=09%3A30+AM&scheduleDuration=60M&scheduleFrequencyType=no_repeat&scheduleRepeatInterval=1&repeat_dow_sun=0&repeat_dow_mon=0&repeat_dow_tue=0&repeat_dow_wed=0&repeat_dow_thu=0&repeat_dow_fri=0&repeat_dow_sat=0&repeat_dom_1=0&repeat_dom_2=0&repeat_dom_3=0&repeat_dom_4=0&repeat_dom_5=0&repeat_dom_6=0&repeat_dom_7=0&repeat_dom_8=0&repeat_dom_9=0&repeat_dom_10=0&repeat_dom_11=0&repeat_dom_12=0&repeat_dom_13=0&repeat_dom_14=0&repeat_dom_15=0&repeat_dom_16=0&repeat_dom_17=0&repeat_dom_18=0&repeat_dom_19=0&repeat_dom_20=0&repeat_dom_21=0&repeat_dom_22=0&repeat_dom_23=0&repeat_dom_24=0&repeat_dom_25=0&repeat_dom_26=0&repeat_dom_27=0&repeat_dom_28=0&repeat_dom_29=0&repeat_dom_30=0&repeat_dom_31=0&scheduleEndOnDate=2015-07-30&scheduleNotes=&btnReservationSubmit=just_clicked');

        $this->assertPattern('/"status":"failure"/');
        $this->assertPattern('/"does not follow duration restriction"/');
    }
}