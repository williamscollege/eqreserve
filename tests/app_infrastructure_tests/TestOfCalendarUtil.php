<?php
    require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';
    require_once dirname(__FILE__) . '/../../calendar_util.php';

class TestOfCalendarUtil extends WMSUnitTestCaseDB {
    function setUp() {
        createAllTestData($this->DB);
    }

    function tearDown() {
        removeAllTestData($this->DB);
    }

    function TestOfMonthIntToString() {
        $month_name = monthIntToString(4);

        $this->assertEqual($month_name,"April");
    }

    function TestRenderDayHeader() {
        $header = renderDayHeader(6,9);

        $this->assertPattern('/June 9/',$header);
        $this->assertPattern('/day-name/',$header);
        $this->assertPattern('/calendar-row/',$header);
    }

    function TestRenderItemRows() {
        $items = array("this","that","the other");
        $headings = array("1","2","3");

        $rows = renderItemRows($items, $headings);

        $this->assertPattern('/daily-items">this</',$rows);
        $this->assertPattern('/daily-items">that</',$rows);
        $this->assertPattern('/daily-items">the other</',$rows);
    }

    function TestRenderMonthHeader() {
        $header = renderMonthHeader(6,9);

        $this->assertPattern('/June 9/',$header);
        $this->assertPattern('/month-name/',$header);
        $this->assertPattern('/calendar-row/',$header);
    }

    function TestRenderCalendarCells() {
        //uses equipment group 2 as testing group
        $eid = 202;
        $Requested_EqGroup = EqGroup::getOneFromDb(['eq_group_id' => $eid], $DB);
        $this->assertTrue($Requested_EqGroup);

        //gets the schedule of a given eq group
        $sched = $Requested_EqGroup->schedules;
        $this->assertTrue($sched);

        //method should create the cells of the calendar and populate with appropriate reservations
        $cells = renderCalendarCells($month,$year,$sched);
        $this->assertPattern('/6:00-7:00 PM/',$cells);
        $this->assertPattern('/testSubgroup1:testItem8/',$cells);

        $this->assertPattern('/="day-number">25/',$cells);
        $this->assertNoPattern('/="day-number">32/',$cells);

    }
}