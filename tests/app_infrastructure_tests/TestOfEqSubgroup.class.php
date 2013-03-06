<?php
	require_once dirname(__FILE__) . '/../simpletest/unit_tester_DB.php';
	require_once dirname(__FILE__) . '/../../classes/eq_subgroup.class.php';


	class TestOfEqSubgroup extends UnitTestCaseDB
	{

		function setUp() {
            /*
                eq group Nanomajigs has 2 subgroups, sgA and sgB
                eq group 3D Printers (deleted) has 1 subgroups, sgC
                eq group Spectrometers has 2 subgroups, sgD and sgE (deleted)
            */

            # EqGroup: eq_group_id, name, descr, start_minute, min_duration_minutes, max_duration_minutes, duration_chunk_minutes, flag_delete
            $addTestEqGroupsSql  = "INSERT INTO " . EqGroup::$dbTable . " VALUES    (1,'Nanomajigs','The investigation of really small stuff','0,15,30,45',15,60,15,0),
                                                                                    (2,'3D Printers','3dp descr','0,30',30,300,30,1),
                                                                                    (3,'Spectrometers','spectrothingies','0,15,30,45',15,60,15,0)
                                                                                ";
            $addTestEqGroupsStmt = $this->DB->prepare($addTestEqGroupsSql);
            $addTestEqGroupsStmt->execute();


            # EqSubgroup: eq_subgroup_id', 'eq_group_id', 'name','descr','ordering','flag_delete'
            $addTestEqSubgroupsSql  = "INSERT INTO " . EqSubgroup::$dbTable . " VALUES 
                                                                                    (1,1,'sgA','sg A',1,0),
                                                                                    (2,1,'sgB','sg B',1,0),
                                                                                    (3,2,'sgC','sg C',2,0),
                                                                                    (4,3,'sgD','sg D',3,0),
                                                                                    (5,3,'sgE','sg E',4,1)
                                                                                ";
            $addTestEqSubgroupsStmt = $this->DB->prepare($addTestEqSubgroupsSql);
            $addTestEqSubgroupsStmt->execute();
		}

		function tearDown() {
            $rmTestEqGroupsSql = "DELETE FROM ".EqGroup::$dbTable;
            $rmTestEqGroupsStmt = $this->DB->prepare($rmTestEqGroupsSql);
            $rmTestEqGroupsStmt->execute();

            $rmTestEqSubgroupsSql = "DELETE FROM ".EqSubgroup::$dbTable;
            $rmTestEqSubgroupsStmt = $this->DB->prepare($rmTestEqSubgroupsSql);
            $rmTestEqSubgroupsStmt->execute();

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
            $esg = EqSubgroup::getOneFromDb(['eq_subgroup_id'=>1],$this->DB);
            $this->assertEqual($esg->name,'sgA');
            $this->assertNull($esg->eq_group);

            // testing this
            $esg->loadEqGroup();

            $this->assertTrue(is_object($esg->eq_group));
            $this->assertEqual(get_class($esg->eq_group),'EqGroup');
            $this->assertEqual($esg->eq_group->name,'Nanomajigs');
        }

	}

?>