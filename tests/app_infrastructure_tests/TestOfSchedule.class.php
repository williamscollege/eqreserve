<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfSchedule extends WMSUnitTestCaseDB
	{

		function setUp() {
            createAllTestData($this->DB);
		}

		function tearDown() {
            removeAllTestData($this->DB);
		}


        //----------------
        // static method tests

        function testScheduleCmp() {
            $tg1 = Schedule::getOneFromDb(['schedule_id'=>1001],$this->DB); // u 1101, c, 2013-03-22 15:00
            $tg2 = Schedule::getOneFromDb(['schedule_id'=>1002],$this->DB); // u 1101, c, 2013-03-26 10:30
            $tg3 = Schedule::getOneFromDb(['schedule_id'=>1006],$this->DB); // u 1101, m, 2013-03-25 18:00
            $tg4 = Schedule::getOneFromDb(['schedule_id'=>1008],$this->DB); // u 1102, m, 2013-03-25 18:00
            $tg5 = Schedule::getOneFromDb(['schedule_id'=>1009],$this->DB); // u 1103, c, 2013-03-26 18:00

            $this->assertEqual(Schedule::cmp($tg1,$tg1),0);

            $this->assertEqual(Schedule::cmp($tg1,$tg2),-1); // same user, diff times
            $this->assertEqual(Schedule::cmp($tg2,$tg1),1); // flipped
            $this->assertEqual(Schedule::cmp($tg1,$tg3),1); // same user, one manager (time is later, but it should come first)

            $tg3->loadUser();
            $tg4->loadUser();
            $this->assertEqual(Schedule::cmp($tg3,$tg4),User::cmp($tg3->user,$tg4->user)); // diff users, same time, both manager
        }

        //----------------
        // instance method tests

        function testScheduleLoadTimeBlocks() {
            $tg = Schedule::getOneFromDb(['schedule_id'=>1002],$this->DB);
            $this->assertTrue($tg->matchesDb);
            $this->assertFalse($tg->time_blocks);

            $tg->loadTimeBlocks();

            $this->assertTrue(is_array($tg->time_blocks));
            $this->assertEqual(count($tg->time_blocks),3);
            $this->assertEqual($tg->time_blocks[0]->time_block_id,902);
            $this->assertEqual($tg->time_blocks[1]->time_block_id,903);
            $this->assertEqual($tg->time_blocks[2]->time_block_id,904);
        }

        function testScheduleLoadUser() {
            $tg = Schedule::getOneFromDb(['schedule_id'=>1001],$this->DB);
            $this->assertTrue($tg->matchesDb);
            $this->assertFalse($tg->user);

            $tg->loadUser();

            $this->assertEqual($tg->user->user_id,1101);
        }

        function testScheduleLoadReservations() {
            $tg = Schedule::getOneFromDb(['schedule_id'=>1009],$this->DB);
            $this->assertTrue($tg->matchesDb);
            $this->assertFalse($tg->reservations);

            $tg->loadReservations();

            $this->assertTrue(is_array($tg->reservations));
            $this->assertEqual(count($tg->reservations),2);
            $this->assertEqual($tg->reservations[0]->reservation_id,809);
            $this->assertEqual($tg->reservations[1]->reservation_id,810);
        }

        function testScheduleLoadReservationsDeeply() {
            $tg = Schedule::getOneFromDb(['schedule_id'=>1001],$this->DB);
            $this->assertTrue($tg->matchesDb);

            $tg->loadReservationsDeeply();

            $this->assertEqual($tg->reservations[0]->reservation_id,801);
            $this->assertEqual($tg->reservations[0]->eq_item->eq_item_id,401);
            $this->assertEqual($tg->reservations[0]->eq_item->eq_subgroup->eq_subgroup_id,301);
            $this->assertEqual($tg->reservations[0]->eq_item->eq_subgroup->eq_group->eq_group_id,201);
        }

        function testScheduleToString() {
            $s = Schedule::getOneFromDb(['schedule_id'=>1001],$this->DB);
            $this->assertTrue($s->matchesDb);

            $this->assertEqual($s->toString(),
                               '2013/3/22 3:00-3:45 PM');

            $s = Schedule::getOneFromDb(['schedule_id'=>1002],$this->DB);
            $this->assertTrue($s->matchesDb);
            $this->assertEqual($s->toString(),
                               '[2013/3/26 10:30-11:30 AM], [2013/4/2 10:30-11:30 AM], [2013/4/9 10:30-11:30 AM]');

        }

        function testScheduleToListItemLinked() {
            $s = Schedule::getOneFromDb(['schedule_id'=>1009],$this->DB);
            $this->assertTrue($s->matchesDb);


            $lil = $s->toListItemLinked();


            $this->assertPattern("/2013\/3\/26 6:00-7:00 PM/",$lil);
            $this->assertPattern("/testItem1/",$lil);
            $this->assertPattern("/testItem2/",$lil);
        }
    }

?>