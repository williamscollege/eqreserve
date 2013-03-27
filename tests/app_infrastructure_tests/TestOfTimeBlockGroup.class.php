<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfTimeBlockGroup extends WMSUnitTestCaseDB
	{

		function setUp() {
            createAllTestData($this->DB);
		}

		function tearDown() {
            removeAllTestData($this->DB);
		}


        //----------------
        // static method tests

        function testTimeBlockGroupCmp() {
            $tg1 = TimeBlockGroup::getOneFromDb(['time_block_group_id'=>1001],$this->DB); // u 1101, c, 2013-03-22 15:00
            $tg2 = TimeBlockGroup::getOneFromDb(['time_block_group_id'=>1002],$this->DB); // u 1101, c, 2013-03-26 10:30
            $tg3 = TimeBlockGroup::getOneFromDb(['time_block_group_id'=>1006],$this->DB); // u 1101, m, 2013-03-25 18:00
            $tg4 = TimeBlockGroup::getOneFromDb(['time_block_group_id'=>1008],$this->DB); // u 1102, m, 2013-03-25 18:00
            $tg5 = TimeBlockGroup::getOneFromDb(['time_block_group_id'=>1009],$this->DB); // u 1103, c, 2013-03-26 18:00

            $this->assertEqual(TimeBlockGroup::cmp($tg1,$tg1),0);

            $this->assertEqual(TimeBlockGroup::cmp($tg1,$tg2),-1); // same user, diff times
            $this->assertEqual(TimeBlockGroup::cmp($tg2,$tg1),1); // flipped
            $this->assertEqual(TimeBlockGroup::cmp($tg1,$tg3),1); // same user, one manager (time is later, but it should come first)

            $tg3->loadUser();
            $tg4->loadUser();
            $this->assertEqual(TimeBlockGroup::cmp($tg3,$tg4),User::cmp($tg3->user,$tg4->user)); // diff users, same time, both manager
        }

        //----------------
        // instance method tests

        function testTimeBlockGroupLoadTimeBlocks() {
            $tg = TimeBlockGroup::getOneFromDb(['time_block_group_id'=>1002],$this->DB);
            $this->assertTrue($tg->matchesDb);
            $this->assertFalse($tg->time_blocks);

            $tg->loadTimeBlocks();

            $this->assertTrue(is_array($tg->time_blocks));
            $this->assertEqual(count($tg->time_blocks),3);
            $this->assertEqual($tg->time_blocks[0]->time_block_id,902);
            $this->assertEqual($tg->time_blocks[1]->time_block_id,903);
            $this->assertEqual($tg->time_blocks[2]->time_block_id,904);
        }

        function testTimeBlockGroupLoadUser() {
            $tg = TimeBlockGroup::getOneFromDb(['time_block_group_id'=>1001],$this->DB);
            $this->assertTrue($tg->matchesDb);
            $this->assertFalse($tg->user);

            $tg->loadUser();

            $this->assertEqual($tg->user->user_id,1101);
        }

        function testTimeBlockGroupLoadReservations() {
            $tg = TimeBlockGroup::getOneFromDb(['time_block_group_id'=>1009],$this->DB);
            $this->assertTrue($tg->matchesDb);
            $this->assertFalse($tg->reservations);

            $tg->loadReservations();

            $this->assertTrue(is_array($tg->reservations));
            $this->assertEqual(count($tg->reservations),2);
            $this->assertEqual($tg->reservations[0]->reservation_id,809);
            $this->assertEqual($tg->reservations[1]->reservation_id,810);
        }

    }

?>