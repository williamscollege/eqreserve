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

        function testTimingConflictsExist() {
            # initial - no conflicts
            $this->assertFalse(Reservation::timingConflictsExist($this->DB));


            # global check - conflict (exact time block match)
            $s = Schedule::getOneFromDb(['schedule_id'=>1001],$this->DB);
            $s->schedule_id = false;
            $s->updateDb(0);
//            $this->dump($s);

            $t = TimeBlock::getOneFromDb(['time_block_id'=>901],$this->DB);
            $t->time_block_id = false;
            $t->schedule_id = $s->schedule_id;
            $t->updateDb();
//            $this->dump($t);

            $r = new Reservation(['eq_item_id'=>401, 'schedule_id'=>$s->schedule_id, 'flag_delete'=>0,'DB'=>$this->DB]);
            $r->updateDb();
//            $this->dump($r);

            $this->assertTrue(Reservation::timingConflictsExist($this->DB));


            # specific check - includes conflict
            $this->assertTrue(Reservation::timingConflictsExist($this->DB,[401]));


            # specific check - excludes conflict
            $this->assertFalse(Reservation::timingConflictsExist($this->DB,[404]));


            ##################
            # timing conflict relations
            # tPrime start_datetime = '2013-03-22 10:00:00'
            # tPrime end_datetime = '2013-03-22 10:15:00'

            # 1. no conflict
            $t->start_datetime = '2013-03-22 09:45:00';
            $t->end_datetime = '2013-03-22 10:00:00';
            $t->updateDb();
//            $this->dump($t);
            $this->assertFalse(Reservation::timingConflictsExist($this->DB,[401]));

            # 2. fully internal
            $t->start_datetime = '2013-03-22 10:05:00';
            $t->end_datetime = '2013-03-22 10:10:00';
            $t->updateDb();
            $this->assertTrue(Reservation::timingConflictsExist($this->DB,[401]));

            # 3. fully external
            $t->start_datetime = '2013-03-22 09:55:00';
            $t->end_datetime = '2013-03-22 10:20:00';
            $t->updateDb();
            $this->assertTrue(Reservation::timingConflictsExist($this->DB,[401]));

            # 4. begin is internal, end is external
            $t->start_datetime = '2013-03-22 10:05:00';
            $t->end_datetime = '2013-03-22 10:20:00';
            $t->updateDb();
            $this->assertTrue(Reservation::timingConflictsExist($this->DB,[401]));

            # 5. end is internal, begin is external
            $t->start_datetime = '2013-03-22 09:55:00';
            $t->end_datetime = '2013-03-22 10:10:00';
            $t->updateDb();
            $this->assertTrue(Reservation::timingConflictsExist($this->DB,[401]));
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

        function testReservationToListItemLinked() {
            $r = Reservation::getOneFromDb(['reservation_id'=>801],$this->DB);
            $r->loadEqItem();
            $r->loadSchedule();

            $r2 = Reservation::getOneFromDb(['reservation_id'=>801],$this->DB);

            $this->assertEqual($r2->toListItemLinked(),
                               '<li><a href="schedule_reservations.php?reservation='.$r->reservation_id.'">'.$r->eq_item->name.'</a> '.$r->schedule->toString().'</li>');
        }

        function testReservationToString() {
            $r = Reservation::getOneFromDb(['reservation_id'=>801],$this->DB);
            $r->loadEqItem();
            $r2 = Reservation::getOneFromDb(['reservation_id'=>801],$this->DB);

            $this->assertEqual($r2->toString(),$r->eq_item->name);
        }
    }

?>