<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';


class AjaxCalendarHandlerTest extends WMSWebTestCase
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

    function getToEquipmentGroupPage($eid = 201)
    {
        $this->signIn();
        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/equipment_group.php?eid='.$eid);
    }

    //############################################################

    function testAjaxNextMonthlyView()
    {
        $this->getToEquipmentGroupPage();

        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/ajax_actions/ajax_calendar_handler.php?next=1&month_num=7&year_num=2015');

        //Assert monthnum, year
        $this->assertPattern('/August/');
        $this->assertPattern('/2015/');
    }

    function testAjaxPrevMonthlyView()
    {
        $this->getToEquipmentGroupPage();

        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/ajax_actions/ajax_calendar_handler.php?prev=-1&month_num=7&year_num=2015');

        //Assert monthnum, year
        $this->assertPattern('/June/');
        $this->assertPattern('/2015/');

    }

    function testAjaxNextYearView()
    {
        $this->getToEquipmentGroupPage();

        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/ajax_actions/ajax_calendar_handler.php?next=1&month_num=12&year_num=2015');

        //Check for wrap around
        $this->assertPattern('/January/');
        $this->assertPattern('/2016/');

    }

    function testAjaxPrevYearView()
    {
        $this->getToEquipmentGroupPage();

        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/ajax_actions/ajax_calendar_handler.php?prev=-1&month_num=1&year_num=2015');

        //Check for wrap around
        $this->assertPattern('/December/');
        $this->assertPattern('/2014/');

    }

    function testAjaxMonthtoDayView()
    {
        $this->getToEquipmentGroupPage();

        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/ajax_actions/ajax_calendar_handler.php?caldate=1&calmonth=7&items=Array');

        //Assert prev, monthnum, year
        $this->assertPattern('/July 1/');
        $this->assertPattern('/1:30 PM/');

    }

    function testAjaxItemReservedDisplayed(){
        $initial_EqGroup = EqGroup::getOneFromDb(['eq_group_id'=>201],$this->DB);
        $this->assertTrue($initial_EqGroup->matchesDb);

        //Get a schedule from the database using the requested EqGroup
        $Requested_EqGroup = EqGroup::getOneFromDb(['eq_group_id' => 201], $this->DB);
        $this->assertTrue($Requested_EqGroup->matchesDb);
        $this->assertEqual($Requested_EqGroup->name, 'testEqGroup1');

        $Requested_EqGroup->loadSchedules();
        $this->assertEqual(count($Requested_EqGroup->schedules),0);

        $this->signIn();

        // This is what's being tested
        //March 22, 2013: testSubgroup1: testItem1 is reserved
        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/ajax_actions/ajax_calendar_handler.php?month_num=3&year_num=2013');

        $this->assertPattern('/March/');
        $this->assertEltByIdHasAttrOfValue('main-calendar-table','data-for-month',3);

        //check that appropriate schedule elements are present

        $this->assertEltByIdHasAttrOfValue('schedule-1003','id','1003');
        $this->assertEltByIdHasAttrOfValue('schedule-1004','id','1004');
        $this->assertEltByIdHasAttrOfValue('schedule-1003','start-date','2013-03-22');
        $this->assertEltByIdHasAttrOfValue('schedule-1004','start-date','2013-03-22');
        $this->assertEltByIdHasAttrOfValue('schedule-1003','start-time','19:00:00');
        $this->assertEltByIdHasAttrOfValue('schedule-1004','start-time','18:00:00');
        $this->assertEltByIdHasAttrOfValue('schedule-1003','duration','60M');
        $this->assertEltByIdHasAttrOfValue('schedule-1004','duration','60M');

        //check schedule display elements
        $this->assertPattern('/6:00-7:00 PM/');
        $this->assertPattern('/7:00-8:00 PM/');
        $this->assertPattern('/testSubgroup1/');
        $this->assertPattern('/testItem1/');

        //May not be getting everything


        $this->assertEqual($r->eq_item->eq_subgroup->name, 'testSubgroup1');
        $this->assertEqual($r->eq_item->name, 'testItem1');
    }

    function testAjaxPrevNav(){

        $this->signIn();

        // This is what's being tested
        //March 22, 2013: testSubgroup1: testItem1 is reserved
        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/ajax_actions/ajax_calendar_handler.php?prev=-1&month_num=4&year_num=2013');

        $this->assertPattern('/March/');
        $this->assertEltByIdHasAttrOfValue('main-calendar-table','data-for-month',3);
    }

    function testAjaxNextNav(){

        $this->signIn();

        // This is what's being tested
        //March 22, 2013: testSubgroup1: testItem1 is reserved
        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/ajax_actions/ajax_calendar_handler.php?next=1&month_num=4&year_num=2013');

        $this->assertPattern('/May/');
        $this->assertEltByIdHasAttrOfValue('main-calendar-table','data-for-month',5);
    }

  function testNoEqGroupID() {

      $this->signIn();

      $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/ajax_actions/ajax_calendar_handler.php?month_num=3&year_num=2013');

      $this->assertText("Missing equipment group ID");
  }
    function testBadEqGroupID() {

        $this->signIn();

        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/ajax_actions/ajax_calendar_handler.php?month_num=3&year_num=2013&eq_group_id=a');

        $this->assertText("Invalid equipment group ID");
    }

    function testNonExistentEqGroupID() {

        $this->signIn();

        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/ajax_actions/ajax_calendar_handler.php?month_num=3&year_num=2013&eq_group_id=234');

        $this->assertText("Equipment group does not exist");
    }

    function testEmptySchedule() {

        $this->signIn();

        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/ajax_actions/ajax_calendar_handler.php?month_num=3&year_num=2013&eq_group_id=206');

        $this->assertNoPattern('/schedule/');
    }

    function testScheduleOutOfRange() {
        $this->signIn();

        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/ajax_actions/ajax_calendar_handler.php?month_num=3&year_num=2013&eq_group_id=203');

        $this->assertNoPattern('/schedule/');
        $this->assertNoPattern('/11:00-11:15 PM/');
    }

    function testAjaxDailyItemReservedDisplayed(){
        $initial_EqGroup = EqGroup::getOneFromDb(['eq_group_id'=>201],$this->DB);
        $this->assertTrue($initial_EqGroup->matchesDb);

        //Get a schedule from the database using the requested EqGroup
        $Requested_EqGroup = EqGroup::getOneFromDb(['eq_group_id' => 201], $this->DB);
        $this->assertTrue($Requested_EqGroup->matchesDb);
        $this->assertEqual($Requested_EqGroup->name, 'testEqGroup1');

        $Requested_EqGroup->loadSchedules();
        $this->assertEqual(count($Requested_EqGroup->schedules),0);

        $this->signIn();

        // This is what's being tested
        //March 22, 2013: testSubgroup1: testItem1 is reserved
        //added calyear field
        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/ajax_actions/ajax_calendar_handler.php?caldate=22&calmonth=3&calyear=2013&items=Array');

        $this->assertPattern('/March/');
        $this->assertEltByIdHasAttrOfValue('main-calendar-table','data-for-month',3);

        //check that appropriate schedule elements are present

        $this->assertEltByIdHasAttrOfValue('schedule-1003','id','1003');
        $this->assertEltByIdHasAttrOfValue('schedule-1004','id','1004');
        $this->assertEltByIdHasAttrOfValue('schedule-1003','start-date','2013-03-22');
        $this->assertEltByIdHasAttrOfValue('schedule-1004','start-date','2013-03-22');
        $this->assertEltByIdHasAttrOfValue('schedule-1003','start-time','19:00:00');
        $this->assertEltByIdHasAttrOfValue('schedule-1004','start-time','18:00:00');
        $this->assertEltByIdHasAttrOfValue('schedule-1003','duration','60M');
        $this->assertEltByIdHasAttrOfValue('schedule-1004','duration','60M');

        //check schedule display elements
        $this->assertPattern('/testSubgroup1/');
        $this->assertPattern('/testItem1/');


    }

    function testDailyNextNav() {
        $this->signIn();

        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/ajax_actions/ajax_calendar_handler.php?caldate=22&calmonth=3&calyear=2013&next=1&items=Array');

        $this->assertPattern('/March/');
        $this->assertPattern('/23/');

    }

    function testDailyPrevNav() {
        $this->signIn();

        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/ajax_actions/ajax_calendar_handler.php?caldate=22&calmonth=3&calyear=2013&prev=-1&items=Array');

        $this->assertPattern('/March/');
        $this->assertPattern('/21/');

    }

    function testNoParameters() {
        $this->fail("Todo");
    }

    function testMissingEqGroupID() {
        $this->fail("Todo");
    }

    function testGetAllItemsForEqGroup() {
        $this->signIn();

        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/ajax_actions/ajax_calendar_handler.php?caldate=22&calmonth=3&calyear=2013&eq_group_id=201');

        $this->assertText(testItem1);
        $this->assertText(testItem2);
    }
}
