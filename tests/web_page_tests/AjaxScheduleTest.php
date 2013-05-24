<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class AjaxScheduleTest extends WMSWebTestCase {

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

	function getToSchedulePage($schedId=1006) {
        $this->signIn();
        $this->get('http://localhost/eqreserve/schedule.php?schedule='.$schedId);
	}

    //############################################################

    function testScheduleAjaxUserCanNotAccessOthersSchedule() {
        $this->getToSchedulePage();

        $this->get('http://localhost/eqreserve/ajax_actions/ajax_schedule.php?schedule=1007&scheduleAction=deleteSchedule');

        $this->assertPattern('/"status":"failure"/');

        $s = Schedule::getOneFromDb(['schedule_id'=>1001],$this->DB);
        $this->assertTrue($s->matchesDb);
    }

    function testScheduleAjaxInvalidAction() {
        $initialSchedule = Schedule::getOneFromDb(['schedule_id'=>1006],$this->DB);
        $this->assertTrue($initialSchedule->matchesDb);
        $this->assertNotEqual($initialSchedule->notes,'foo123bar');
        $this->getToSchedulePage();

        $this->get('http://localhost/eqreserve/ajax_actions/ajax_schedule.php?schedule=1006&scheduleAction=superpowers&actionVal=foo123bar');

        $this->assertPattern('/"status":"failure"/');
        $s = Schedule::getOneFromDb(['schedule_id'=>1006],$this->DB);
        $this->assertEqual($s->notes,$initialSchedule->notes);
    }

    function testScheduleAjaxSaveNotes() {
        $initialSchedule = Schedule::getOneFromDb(['schedule_id'=>1006],$this->DB);
        $this->assertTrue($initialSchedule->matchesDb);
        $this->assertNotEqual($initialSchedule->notes,'foo123bar');
        $this->getToSchedulePage();

        $this->get('http://localhost/eqreserve/ajax_actions/ajax_schedule.php?schedule=1006&scheduleAction=updateNotes&actionVal=foo123bar');

        $this->assertPattern('/"status":"success"/');
        $s = Schedule::getOneFromDb(['schedule_id'=>1006],$this->DB);
        $this->assertTrue($s->matchesDb);
        $this->assertEqual($s->notes,'foo123bar');
    }

    function testScheduleAjaxAdminCanAccessOthersSchedule() {
        $u = User::getOneFromDb(['username'=>TESTINGUSER],$this->DB);
        $u->flag_is_system_admin = true;
        $u->updateDb();
        $this->assertTrue($u->matchesDb);

        $this->getToSchedulePage();

        $this->get('http://localhost/eqreserve/ajax_actions/ajax_schedule.php?schedule=1007&scheduleAction=updateNotes&actionVal=foo123bar');

        $this->assertPattern('/"status":"success"/');
        $s = Schedule::getOneFromDb(['schedule_id'=>1007],$this->DB);
        $this->assertTrue($s->matchesDb);
        $this->assertEqual($s->notes,'foo123bar');
    }

    function testScheduleAjaxGroupManagerCanAccessOthersSchedule() {
        $this->getToSchedulePage();

        $this->get('http://localhost/eqreserve/ajax_actions/ajax_schedule.php?schedule=1009&scheduleAction=deleteSchedule');

        $this->assertPattern('/"status":"success"/');

        $s = Schedule::getOneFromDb(['schedule_id'=>1009],$this->DB);
        $this->assertFalse($s->matchesDb);
    }

    function testScheduleAjaxSaveType() {
        $initialSchedule = Schedule::getOneFromDb(['schedule_id'=>1006],$this->DB);
        $this->assertTrue($initialSchedule->matchesDb);
        $this->assertEqual($initialSchedule->type,'manager');
        $this->getToSchedulePage();

        $this->get('http://localhost/eqreserve/ajax_actions/ajax_schedule.php?schedule=1006&scheduleAction=updateType&actionVal=consumer');

        $this->assertPattern('/"status":"success"/');
        $s = Schedule::getOneFromDb(['schedule_id'=>1006],$this->DB);
        $this->assertTrue($s->matchesDb);
        $this->assertEqual($s->type,'consumer');

        $this->get('http://localhost/eqreserve/ajax_actions/ajax_schedule.php?schedule=1006&scheduleAction=updateType&actionVal=manager');

        $this->assertPattern('/"status":"success"/');
        $s = Schedule::getOneFromDb(['schedule_id'=>1006],$this->DB);
        $this->assertTrue($s->matchesDb);
        $this->assertEqual($s->type,'manager');

        $this->get('http://localhost/eqreserve/ajax_actions/ajax_schedule.php?schedule=1006&scheduleAction=updateType&actionVal=blah');

        $this->assertPattern('/"status":"failure"/');
        $s = Schedule::getOneFromDb(['schedule_id'=>1006],$this->DB);
        $this->assertTrue($s->matchesDb);
        $this->assertEqual($s->type,'manager');
    }

    function testScheduleAjaxDeleteAll() {
        $initialSchedule = Schedule::getOneFromDb(['schedule_id'=>1006],$this->DB);
        $this->assertTrue($initialSchedule->matchesDb);
        $this->getToSchedulePage();

        $this->get('http://localhost/eqreserve/ajax_actions/ajax_schedule.php?schedule=1006&scheduleAction=deleteSchedule');

        $this->assertPattern('/"status":"success"/');
        $s = Schedule::getOneFromDb(['schedule_id'=>1006],$this->DB);
        $this->assertFalse($s->matchesDb);
        $tb = TimeBlock::getOneFromDb(['time_block_id'=>908],$this->DB);
        $this->assertFalse($tb->matchesDb);
        $r = Reservation::getOneFromDb(['reservation_id'=>806],$this->DB);
        $this->assertFalse($r->matchesDb);
    }

    function testScheduleAjaxDeleteItem() {
        $initialSchedule = Schedule::getOneFromDb(['schedule_id'=>1010],$this->DB);
        $this->assertTrue($initialSchedule->matchesDb);
        $this->getToSchedulePage(1010);

        $this->get('http://localhost/eqreserve/ajax_actions/ajax_schedule.php?schedule=1010&scheduleAction=deleteEqItem&actionVal=409');

        $this->assertPattern('/"status":"success"/');
        $s = Schedule::getOneFromDb(['schedule_id'=>1010],$this->DB);
        $s->loadReservations();
        $this->assertEqual(count($s->reservations),1);
        $this->assertEqual($s->reservations[0]->eq_item_id,411);
    }

    function testScheduleAjaxDeleteLastItem() {
        $initialSchedule = Schedule::getOneFromDb(['schedule_id'=>1001],$this->DB);
        $this->assertTrue($initialSchedule->matchesDb);
        $this->getToSchedulePage(1001);

        $this->get('http://localhost/eqreserve/ajax_actions/ajax_schedule.php?schedule=1001&scheduleAction=deleteEqItem&actionVal=401');

        $this->assertPattern('/"status":"success"/');
        $s = Schedule::getOneFromDb(['schedule_id'=>1001],$this->DB);
        $this->assertFalse($s->matchesDb);
        $tb = TimeBlock::getOneFromDb(['time_block_id'=>901],$this->DB);
        $this->assertFalse($tb->matchesDb);
        $r = Reservation::getOneFromDb(['reservation_id'=>801],$this->DB);
        $this->assertFalse($r->matchesDb);
    }

    function testScheduleAjaxDeleteReservation() {
        $initialSchedule = Schedule::getOneFromDb(['schedule_id'=>1010],$this->DB);
        $this->assertTrue($initialSchedule->matchesDb);
        $this->getToSchedulePage(1010);

        $this->get('http://localhost/eqreserve/ajax_actions/ajax_schedule.php?schedule=1010&scheduleAction=deleteReservation&actionVal=811');

        $this->assertPattern('/"status":"success"/');
        $s = Schedule::getOneFromDb(['schedule_id'=>1010],$this->DB);
        $s->loadReservations();
        $this->assertEqual(count($s->reservations),1);
        $this->assertEqual($s->reservations[0]->eq_item_id,411);
    }

    function testScheduleAjaxDeleteLastReservation() {
        $initialSchedule = Schedule::getOneFromDb(['schedule_id'=>1001],$this->DB);
        $this->assertTrue($initialSchedule->matchesDb);
        $this->getToSchedulePage(1001);

        $this->get('http://localhost/eqreserve/ajax_actions/ajax_schedule.php?schedule=1001&scheduleAction=deleteReservation&actionVal=801');

        $this->assertPattern('/"status":"success"/');
        $s = Schedule::getOneFromDb(['schedule_id'=>1001],$this->DB);
        $this->assertFalse($s->matchesDb);
        $tb = TimeBlock::getOneFromDb(['time_block_id'=>901],$this->DB);
        $this->assertFalse($tb->matchesDb);
        $r = Reservation::getOneFromDb(['reservation_id'=>801],$this->DB);
        $this->assertFalse($r->matchesDb);
    }

    function testScheduleAjaxDeleteTimeBlock() {
        $initialSchedule = Schedule::getOneFromDb(['schedule_id'=>1002],$this->DB);
        $this->assertTrue($initialSchedule->matchesDb);
        $this->getToSchedulePage(1002);

        $this->get('http://localhost/eqreserve/ajax_actions/ajax_schedule.php?schedule=1002&scheduleAction=deleteTimeBlock&actionVal=902');

        $this->assertPattern('/"status":"success"/');
        $s = Schedule::getOneFromDb(['schedule_id'=>1002],$this->DB);
        $s->loadTimeBlocks();
        $this->assertEqual(count($s->time_blocks),2);
        $this->assertEqual($s->time_blocks[0]->time_block_id,903);
        $this->assertEqual($s->time_blocks[1]->time_block_id,904);
    }

    function testScheduleAjaxDeleteLastTimeBlock() {
        $initialSchedule = Schedule::getOneFromDb(['schedule_id'=>1001],$this->DB);
        $this->assertTrue($initialSchedule->matchesDb);
        $this->getToSchedulePage(1001);

        $this->get('http://localhost/eqreserve/ajax_actions/ajax_schedule.php?schedule=1001&scheduleAction=deleteTimeBlock&actionVal=901');

        $this->assertPattern('/"status":"success"/');
        $s = Schedule::getOneFromDb(['schedule_id'=>1001],$this->DB);
        $this->assertFalse($s->matchesDb);
        $tb = TimeBlock::getOneFromDb(['time_block_id'=>901],$this->DB);
        $this->assertFalse($tb->matchesDb);
        $r = Reservation::getOneFromDb(['reservation_id'=>801],$this->DB);
        $this->assertFalse($r->matchesDb);
    }
}