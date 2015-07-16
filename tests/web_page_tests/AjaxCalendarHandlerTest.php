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
        $this->assertText('August');
        $this->assertText('2015');
    }

    function testAjaxPrevMonthlyView()
    {
        $this->getToEquipmentGroupPage();

        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/ajax_actions/ajax_calendar_handler.php?prev=-1&month_num=7&year_num=2015');

        //Assert monthnum, year
        $this->assertText('June');
        $this->assertText('2015');

    }

    function testAjaxNextYearView()
    {
        $this->getToEquipmentGroupPage();

        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/ajax_actions/ajax_calendar_handler.php?next=1&month_num=12&year_num=2015');

        //Check for wrap around
        $this->assertText('January');
        $this->assertText('2016');

    }

    function testAjaxPrevYearView()
    {
        $this->getToEquipmentGroupPage();

        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/ajax_actions/ajax_calendar_handler.php?prev=-1&month_num=1&year_num=2015');

        //Check for wrap around
        $this->assertText('December');
        $this->assertText('2014');

    }

    function testAjaxMonthtoDayView()
    {
        $this->getToEquipmentGroupPage();

        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/ajax_actions/ajax_calendar_handler.php?caldate=1&calmonth=7&items=Array');

        //Assert prev, monthnum, year
        $this->assertText('July 1');
        $this->assertText('1:30 PM');

    }

    function testAjaxItemReservedDisplayed(){
        $initial_EqGroup = EqGroup::getOneFromDb(['eq_group_id'=>201],$this->DB);
        $this->assertTrue($initial_EqGroup->matchesDb);

        $this->getToEquipmentGroupPage();

        //March 22, 2013: testSubgroup1: testItem1 is reserved
        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/ajax_actions/ajax_calendar_handler.php?prev=-1&month_num=4&year_num=2013');

        //Get a schedule from the database using the requested EqGroup
        $Requested_EqGroup = EqGroup::getOneFromDb(['eq_group_id' => 201], $this->$DB);
        $this->assertTrue($Requested_EqGroup->matchesDb);
        $sched = $Requested_EqGroup->$schedules;
        $this->assertTrue($sched->matchesDb);

        //Check schedule_id
        $r = $sched->reservations;

        //Check start_on_date


        //May not be getting everything


        $this->assertEqual($r->eq_item->eq_subgroup->name, 'testSubgroup1');
        $this->assertEqual($r->eq_item->name, 'testItem1');
    }
}
