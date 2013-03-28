<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfReservation extends WMSUnitTestCaseDB
	{

		function setUp() {
            createAllTestData($this->DB);
		}

		function tearDown() {
            removeAllTestData($this->DB);
		}

        //----------------
        // static method tests

        function testReservationCmp() {
            $r1 = Reservation::getOneFromDb(['reservation_id'=>801],$this->DB);
            $r2 = Reservation::getOneFromDb(['reservation_id'=>802],$this->DB);
            $this->assertTrue($r1->matchesDb);
            $this->assertTrue($r2->matchesDb);

            $r1->loadEqItem();
            $r2->loadEqItem();

            $this->assertEqual(Reservation::cmp($r2,$r1),EqItem::cmp($r2->eq_item,$r1->eq_item));
        }

        //----------------
        // instance method tests

        function testReservationLoadItem() {
            $r = Reservation::getOneFromDb(['reservation_id'=>801],$this->DB);
            $this->assertTrue($r->matchesDb);
            $this->assertFalse($r->eq_item);

            $r->loadEqItem();

            $this->assertEqual($r->eq_item->eq_item_id,401);
        }

        function testReservationLoadSchedule() {
            $r = Reservation::getOneFromDb(['reservation_id'=>801],$this->DB);
            $this->assertTrue($r->matchesDb);
            $this->assertFalse($r->schedule);

            $r->loadSchedule();

            $this->assertEqual($r->schedule->schedule_id,1001);
        }

        function testReservationLoadUser() {
            $r = Reservation::getOneFromDb(['reservation_id'=>801],$this->DB);
            $this->assertTrue($r->matchesDb);
            $this->assertFalse($r->user);

            $r->loadUser();

            $this->assertEqual($r->user->user_id,1101);
        }

        function testReservationLoadTimeBlocks() {
            $r = Reservation::getOneFromDb(['reservation_id'=>802],$this->DB);
            $this->assertTrue($r->matchesDb);
            $this->assertFalse($r->time_blocks);

            $r->loadTimeBlocks();

            $this->assertTrue(is_array($r->time_blocks));
            $this->assertEqual(count($r->time_blocks),3);
            $this->assertEqual($r->time_blocks[0]->time_block_id,902);
            $this->assertEqual($r->time_blocks[1]->time_block_id,903);
            $this->assertEqual($r->time_blocks[2]->time_block_id,904);
        }

    }

?>