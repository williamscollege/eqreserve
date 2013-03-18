<?php
	require_once dirname(__FILE__) . '/../simpletest/unit_tester_DB.php';
    require_once dirname(__FILE__) . '/dataForTesting.php';


	class TestOfEqSubgroup extends UnitTestCaseDB
	{

		function setUp() {
            createTestData_EqGroups($this->DB);
            createTestData_EqSubgroups($this->DB);
            createTestData_EqItems($this->DB);
		}

		function tearDown() {
            removeTestData_EqGroups($this->DB);
            removeTestData_EqSubgroups($this->DB);
            removeTestData_EqItems($this->DB);
		}

		//###################################
		// basic db interaction

		function testEqSubgroupDBInsert(){
			$esg = new EqSubgroup(['eq_subgroup_id'=>50,'eq_group_id'=>1,'DB'=>$this->DB]);


			$esg->updateDb();


			$esg2 = EqSubgroup::getOneFromDb(['eq_subgroup_id'=>50], $this->DB);

			$this->assertTrue($esg2->matchesDb);
		}


		//#########################################################
        // static method tests

		public function TestOfCmp(){
			$esg1 = new EqSubgroup(['name'=>'s1','ordering'=>1, 'DB'=>$this->DB]);
            $esg2 = new EqSubgroup(['name'=>'s2','ordering'=>2, 'DB'=>$this->DB]);
            $esg3 = new EqSubgroup(['name'=>'s3','ordering'=>1, 'DB'=>$this->DB]);
            $esg4 = new EqSubgroup(['name'=>'s1','ordering'=>3, 'DB'=>$this->DB]);
            $esg5 = new EqSubgroup(['name'=>'sgB','eq_group_id'=>201,'ordering'=>5, 'DB'=>$this->DB]);
            $esg6 = new EqSubgroup(['name'=>'sgA','eq_group_id'=>202,'ordering'=>4, 'DB'=>$this->DB]);

            $c12 = EqSubgroup::cmp($esg1,$esg2);
            $c21 = EqSubgroup::cmp($esg2,$esg1);
            $c13 = EqSubgroup::cmp($esg1,$esg3); // same ordering, different names
            $c14 = EqSubgroup::cmp($esg1,$esg4); // same names, different ordering
            $c56 = EqSubgroup::cmp($esg5,$esg6); // different groups

            $this->assertEqual($c12, -1);
            $this->assertEqual($c21, 1);

            $this->assertEqual($c13, -1);
            $this->assertEqual($c14, -1);

//$this->dump($esg5);
//$this->dump($esg6);
            $this->assertEqual($c56, -1);

		}


        public function TestOfCmpAlphabetical(){
            $esg1 = new EqSubgroup(['name'=>'nLater','ordering'=>1, 'DB'=>$this->DB]);
            $esg2 = new EqSubgroup(['name'=>'nEarlier','ordering'=>2, 'DB'=>$this->DB]);

            $cmp = EqSubgroup::cmpAlphabetical($esg1,$esg2);

            $this->assertNotNull($cmp);
            $this->assertEqual($cmp, 1);

            $cmp = EqSubgroup::cmpAlphabetical($esg2,$esg1);
            $this->assertEqual($cmp, -1);
        }

        //#########################################################
        // instance tests
        
        public function TestOfLoadEqGroup() {
            $esg = EqSubgroup::getOneFromDb(['eq_subgroup_id'=>301],$this->DB);
            $this->assertEqual($esg->name,'testSubgroup1');
            $this->assertNull($esg->eq_group);

            // testing this
            $esg->loadEqGroup();

            $this->assertTrue(is_object($esg->eq_group));
            $this->assertEqual(get_class($esg->eq_group),'EqGroup');
            $this->assertEqual($esg->eq_group->name,'testEqGroup1');
        }

        public function TestOfLoadEqItems() {
            $esg1 = EqSubgroup::getOneFromDb(['eq_subgroup_id'=>301],$this->DB);
            $esg2 = EqSubgroup::getOneFromDb(['eq_subgroup_id'=>302],$this->DB);
            $esg3 = EqSubgroup::getOneFromDb(['eq_subgroup_id'=>304],$this->DB);

            $this->assertEqual($esg1->name,'testSubgroup1');
            $this->assertNull($esg1->eq_items);
            $this->assertEqual($esg2->name,'testSubgroup2');
            $this->assertNull($esg2->eq_items);
            $this->assertEqual($esg3->name,'testSubgroup4');
            $this->assertNull($esg3->eq_items);


            // testing this
            $esg1->loadEqItems();
            $esg2->loadEqItems();
            $esg3->loadEqItems();


            $this->assertTrue(is_array($esg1->eq_items));
            $this->assertEqual(count($esg1->eq_items),4);

            usort($esg1->eq_items,'EqItem::cmp');

            $this->assertEqual($esg1->eq_items[0]->eq_item_id,401);
            $this->assertEqual($esg1->eq_items[1]->eq_item_id,402);
            $this->assertEqual($esg1->eq_items[2]->eq_item_id,403);
            $this->assertEqual($esg1->eq_items[3]->eq_item_id,404);

            $this->assertTrue(is_array($esg2->eq_items));
            $this->assertEqual(count($esg2->eq_items),1);

            $this->assertTrue(is_array($esg3->eq_items));
            $this->assertEqual(count($esg3->eq_items),0);

        }

	}

?>