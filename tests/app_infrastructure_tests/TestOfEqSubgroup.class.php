<?php
	require_once dirname(__FILE__) . '/../simpletest/unit_tester_DB.php';
    require_once dirname(__FILE__) . '/dataForTesting.php';


	class TestOfEqSubgroup extends UnitTestCaseDB
	{

		function setUp() {
            createTestData_EqGroups($this->DB);
            createTestData_EqSubgroups($this->DB);
		}

		function tearDown() {
            removeTestData_EqGroups($this->DB);
            removeTestData_EqSubgroups($this->DB);
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
			$esg1 = new EqSubgroup(['name'=>'nLater','ordering'=>1, 'DB'=>$this->DB]);
            $esg2 = new EqSubgroup(['name'=>'nEarlier','ordering'=>2, 'DB'=>$this->DB]);

            $cmp = EqSubgroup::cmp($esg1,$esg2);

			$this->assertNotNull($cmp);
			$this->assertEqual($cmp, -1);

            $cmp = EqSubgroup::cmp($esg2,$esg1);
			$this->assertEqual($cmp, 1);
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
        
        public function TestOfLoadEgGroup() {
            $esg = EqSubgroup::getOneFromDb(['eq_subgroup_id'=>301],$this->DB);
            $this->assertEqual($esg->name,'testSubgroup1');
            $this->assertNull($esg->eq_group);

            // testing this
            $esg->loadEqGroup();

            $this->assertTrue(is_object($esg->eq_group));
            $this->assertEqual(get_class($esg->eq_group),'EqGroup');
            $this->assertEqual($esg->eq_group->name,'testEqGroup1');
        }

	}

?>