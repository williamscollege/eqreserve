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

        $Requested_EqGroup = EqGroup::getOneFromDb(['eq_group_id' => 202], $this->DB);
        $this->assertTrue($Requested_EqGroup);

        //schedules array is null
        $this->assertNull($Requested_EqGroup->schedules);

        $Requested_EqGroup->loadSchedules();

        //gets the schedule of a given eq group
        $sched = $Requested_EqGroup->schedules;

        $rows = renderItemRows($items, $headings,$sched);

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
        $Requested_EqGroup = EqGroup::getOneFromDb(['eq_group_id' => 202], $this->DB);
        $this->assertTrue($Requested_EqGroup);

        //schedules array is null
        $this->assertNull($Requested_EqGroup->schedules);

        $Requested_EqGroup->loadSchedules();

        //gets the schedule of a given eq group
        $sched = $Requested_EqGroup->schedules;

        //schedules array contains something
        $this->assertTrue(is_array($sched));
        $this->assertNotNull($sched);
        $this->assertEqual(2,count($sched));

        $month = 3;
        $year = 2013;

        //method should create the cells of the calendar and populate with appropriate reservations
        $cells = renderCalendarCells($month,$year,$sched);

        //schedules valid but not overlapping with time
        $this->assertNoPattern('/2015/', $cells);
        $this->assertNoPattern('/05/', $cells);
        $this->assertPattern('/'.$year.'/', $cells);
        $this->assertPattern('/'.$month.'/', $cells);

        $this->assertPattern('/6:00-7:00 PM/',$cells);
        $this->assertPattern('/testSubgroup1:<br>testItem8/',$cells);

        $this->assertPattern('/="day-number">25/',$cells);
        $this->assertNoPattern('/="day-number">32/',$cells);
        $this->assertNoPattern('/="day-number">0/',$cells);

    }

    function testTimeToInt() {
        $this->assertEqual(timeToInt('00:00:00'), 1);
        $this->assertEqual(timeToInt('17:00:00'), 69);
        $this->assertEqual(timeToInt('test'), 0);
    }

    function testDurationToInt() {
        $this->assertEqual(durationToInt('60M'),4);
        $this->assertEqual(durationToInt('14D'),100);
        $this->assertEqual(durationToInt('a'),0);
        $this->assertEqual(durationToInt(7),0);
    }
    // rendering tests:
    // schedules contains bad data (e.g. non-schedule objects)
    // schedules valid but not overlapping w/ time given e.g. time is 2015/04, but scheduled things are all in 2013
}