<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';

	class TestOfTimeBlock extends WMSUnitTestCaseDB
	{

		function setUp() {
            createAllTestData($this->DB);
		}

		function tearDown() {
            removeAllTestData($this->DB);
		}


        //----------------
        // static method tests

        function testTimeBlockCmp() {
            $t1 = new TimeBlock(['start_time'=>'2012-03-25 11:30:00','DB'=>$this->DB]);
            $t2 = new TimeBlock(['start_time'=>'2012-03-25 10:30:00','DB'=>$this->DB]);
            $t3 = new TimeBlock(['start_time'=>'2012-03-25 11:30:00','DB'=>$this->DB]);

            $this->assertEqual(TimeBlock::cmp($t2,$t1),-1);
            $this->assertEqual(TimeBlock::cmp($t1,$t3),0);
            $this->assertEqual(TimeBlock::cmp($t1,$t2),1);
        }

        //----------------
        // instance method tests

        function testTimeBlockLoadSchedule() {
            $t = TimeBlock::getOneFromDb(['time_block_id'=>901],$this->DB);
            $this->assertTrue($t->matchesDb);
            $this->assertFalse($t->schedule);

            $t->loadSchedule();

            $this->assertEqual($t->schedule->schedule_id,1001);
        }

        function testTimeBlockLoadUser() {
            $t = TimeBlock::getOneFromDb(['time_block_id'=>901],$this->DB);
            $this->assertTrue($t->matchesDb);
            $this->assertFalse($t->user);

            $t->loadUser();

            $this->assertEqual($t->user->user_id,1101);
        }

        function testTimeBlockLoadReservations() {
            $t = TimeBlock::getOneFromDb(['time_block_id'=>911],$this->DB);
            $this->assertTrue($t->matchesDb);
            $this->assertFalse($t->reservations);

            $t->loadReservations();

            $this->assertTrue(is_array($t->reservations));
            $this->assertEqual(count($t->reservations),2);
            $this->assertEqual($t->reservations[0]->reservation_id,809);
            $this->assertEqual($t->reservations[1]->reservation_id,810);
        }

    }

?>