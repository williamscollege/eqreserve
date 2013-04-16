<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';


	class TestOfEqGroup extends WMSUnitTestCaseDB
	{

		function setUp() {
            createTestData_InstGroups($this->DB);
            createTestData_EqGroups($this->DB);
            createTestData_EqSubgroups($this->DB);
            createTestData_EqItems($this->DB);
            createTestData_Permissions($this->DB);
            createTestData_InstMemberships($this->DB);

		}

		function tearDown() {
            removeTestData_InstGroups($this->DB);
            removeTestData_EqGroups($this->DB);
            removeTestData_EqSubgroups($this->DB);
            removeTestData_EqItems($this->DB);
            removeTestData_Permissions($this->DB);
            removeTestData_InstMemberships($this->DB);
		}

		//###################################
		// basic db interaction

		function testEqGroupDBInsert(){
			$eg = new EqGroup(['eq_group_id'=>50,'DB'=>$this->DB]);


			$eg->updateDb();


			$eg2 = EqGroup::getOneFromDb(['eq_group_id'=>50], $this->DB);

			$this->assertTrue($eg2->matchesDb);
		}


        //############################################################################
        // static method tests

		public function TestOfEqGroupCmpAlphabetical()
		{
			$g1 = new EqGroup(['name' => 'alpha', 'DB' => $this->DB]);
			$g2 = new EqGroup(['name' => 'beta', 'DB' => $this->DB]);
			$g3 = new EqGroup(['name' => 'gamma', 'DB' => $this->DB]);
			$g4 = new EqGroup(['name' => 'alpha', 'DB' => $this->DB]);

			$c12 = EqGroup::cmpAlphabetical($g1, $g2);
			$c32 = EqGroup::cmpAlphabetical($g3, $g2);
			$c14 = EqGroup::cmpAlphabetical($g1, $g4);

			$this->assertEqual($c12, -1);
			$this->assertEqual($c32, 1);
			$this->assertEqual($c14, 0);
		}

		public function TestOfGetEqGroupsForInstGroup(){
			$ig = new InstGroup(['inst_group_id' => 501, 'DB' => $this->DB]);
			

            $egs = EqGroup::getEqGroupsForInstGroup($ig);


			$this->assertNotNull($egs);
			$this->assertTrue(is_array($egs));
			$this->assertEqual(count($egs), 4);
			$this->assertEqual(get_class($egs[0]), 'EqGroup');

			usort($egs, "EqGroup::cmpAlphabetical");

            $this->assertEqual($egs[0]->name, 'testEqGroup1');
            $this->assertEqual($egs[1]->name, 'testEqGroup2');
            $this->assertEqual($egs[2]->name, 'testEqGroup3');
            $this->assertEqual($egs[3]->name, 'testEqGroup6');

			$this->assertEqual($egs[0]->permission->role->role_id, 1);
			$this->assertEqual($egs[1]->permission->role->role_id, 2);
            $this->assertEqual($egs[2]->permission->role->role_id, 2);
            $this->assertEqual($egs[3]->permission->role->role_id, 2);
		}

		public function TestOfGetEqGroupsForUser() {
			$user = new User(['user_id' => 1101, 'DB' => $this->DB]);
			

            $egs = EqGroup::getEqGroupsForUser($user);


			$this->assertNotNull($egs);
			$this->assertTrue(is_array($egs));
//$this->dump($egs);
			$this->assertEqual(count($egs), 4);
			$this->assertEqual(get_class($egs[0]), 'EqGroup');

			usort($egs, "EqGroup::cmpAlphabetical");

            $this->assertEqual($egs[0]->name, 'testEqGroup1');
            $this->assertEqual($egs[1]->name, 'testEqGroup2');
            $this->assertEqual($egs[2]->name, 'testEqGroup3');
            $this->assertEqual($egs[3]->name, 'testEqGroup7');

			$this->assertEqual($egs[0]->permission->role->role_id, 2);
			$this->assertEqual($egs[1]->permission->role->role_id, 2);
			$this->assertEqual($egs[2]->permission->role->role_id, 1);
			$this->assertEqual($egs[3]->permission->role->role_id, 2);
		}

		public function TestOfGetUnifiedEqGroupList() {
			$user = new User(['user_id' => 1, 'DB' => $this->DB]);

			$u_igs = InstGroup::getInstGroupsForUser($user);
			$u_egs = EqGroup::getEqGroupsForUser($user);

			$results = array();
			$results = EqGroup::getUnifiedEqGroupList($u_igs, $u_egs);
			// This test is completed below. See also: TestOfGetAllEqGroupsForNonAdminUser
		}

		public function TestOfGetAllEqGroupsForNonAdminUser()
		{

			$user = new User(['user_id' => 1101, 'DB' => $this->DB]);


			$egs = EqGroup::getAllEqGroupsForNonAdminUser($user);


			$this->assertNotNull($egs);
			$this->assertTrue(is_array($egs));
			$this->assertEqual(count($egs), 5);

			$this->assertEqual(get_class($egs[1]), 'EqGroup');

			usort($egs, "EqGroup::cmpAlphabetical");

            $this->assertEqual($egs[0]->name, 'testEqGroup1');
            $this->assertEqual($egs[1]->name, 'testEqGroup2');
            $this->assertEqual($egs[2]->name, 'testEqGroup3');
            $this->assertEqual($egs[3]->name, 'testEqGroup6');
            $this->assertEqual($egs[4]->name, 'testEqGroup7');

			$this->assertEqual($egs[0]->permission->role->role_id, 1);
			$this->assertEqual($egs[1]->permission->role->role_id, 2);
			$this->assertEqual($egs[2]->permission->role->role_id, 1);
            $this->assertEqual($egs[3]->permission->role->role_id, 2);
            $this->assertEqual($egs[4]->permission->role->role_id, 2);

//			exit;
		}


        //############################################################################
        // instance method tests

        public function TestOfLoadSubgroups()
        {
            $eg = EqGroup::getOneFromDb(['eq_group_id'=>201],$this->DB);
            $this->assertEqual($eg->name,'testEqGroup1');
            $this->assertNull($eg->eq_subgroups);


            // testing this
            $eg->loadEqSubgroups();

//exit;

            $this->assertTrue(is_array($eg->eq_subgroups));
            $this->assertEqual(count($eg->eq_subgroups),4);

            usort($eg->eq_subgroups,'EqSubgroup::cmp');
            $this->assertEqual($eg->eq_subgroups[0]->name,'testSubgroup1');
            $this->assertEqual($eg->eq_subgroups[1]->name,'testSubgroup2');
            $this->assertEqual($eg->eq_subgroups[2]->name,'testSubgroup3');
            $this->assertEqual($eg->eq_subgroups[3]->name,'testSubgroup4');

            $this->assertEqual($eg->eq_subgroups[0]->eq_group->eq_group_id,201);
            $this->assertEqual($eg->eq_subgroups[1]->eq_group->eq_group_id,201);
            $this->assertEqual($eg->eq_subgroups[2]->eq_group->eq_group_id,201);
            $this->assertEqual($eg->eq_subgroups[3]->eq_group->eq_group_id,201);
            
        }

        public function TestOfLoadItems()
        {
            $eg = EqGroup::getOneFromDb(['eq_group_id'=>201],$this->DB);
            $this->assertEqual($eg->name,'testEqGroup1');
            $this->assertNull($eg->eq_subgroups);
            $this->assertNull($eg->eq_items);


            // testing this
            $eg->loadEqItems();


            $this->assertTrue(is_array($eg->eq_subgroups));
            $this->assertEqual(count($eg->eq_subgroups),4);

            $this->assertTrue(is_array($eg->eq_items));
            $this->assertEqual(count($eg->eq_items),5);

            usort($eg->eq_items,'EqItem::cmp');

//$this->dump($eg->eq_items);

            $this->assertEqual($eg->eq_items[0]->eq_item_id,401);
            $this->assertEqual($eg->eq_items[1]->eq_item_id,402);
            $this->assertEqual($eg->eq_items[2]->eq_item_id,403);
            $this->assertEqual($eg->eq_items[3]->eq_item_id,404);
            $this->assertEqual($eg->eq_items[4]->eq_item_id,406);

            $this->assertEqual($eg->eq_items[0]->eq_subgroup->eq_subgroup_id,301);            
            $this->assertEqual($eg->eq_items[1]->eq_subgroup->eq_subgroup_id,301);            
            $this->assertEqual($eg->eq_items[2]->eq_subgroup->eq_subgroup_id,301);            
            $this->assertEqual($eg->eq_items[3]->eq_subgroup->eq_subgroup_id,301);            
            $this->assertEqual($eg->eq_items[4]->eq_subgroup->eq_subgroup_id,302);            

            $this->assertEqual($eg->eq_items[0]->eq_group->eq_group_id,201);            
            $this->assertEqual($eg->eq_items[1]->eq_group->eq_group_id,201);            
            $this->assertEqual($eg->eq_items[2]->eq_group->eq_group_id,201);            
            $this->assertEqual($eg->eq_items[3]->eq_group->eq_group_id,201);            
            $this->assertEqual($eg->eq_items[4]->eq_group->eq_group_id,201);            
        }

		public function TestOfLoadPermissions(){
			$eg = EqGroup::getOneFromDb(['eq_group_id'=>201],$this->DB);
			$this->assertEqual($eg->name,'testEqGroup1');
			$this->assertNull($eg->permissions);

			$eg->loadPermissions();

			$this->assertTrue(is_array($eg->permissions));
			$this->assertEqual(count($eg->permissions), 4);

		}

        public function TestOfToListItemLinked(){
            $eg = EqGroup::getOneFromDb(['eq_group_id'=>201],$this->DB);

            $this->assertEqual($eg->toListItemLinked(),'<li><a href="equipment_group.php?eid=201" title="testEqGroup1">testEqGroup1</a>: on the 1/4 hour with 15 minute min and 1 hour max by 15 minute intervals</li>');

            $eg->permission = Permission::getOneFromDb(['permission_id'=>707],$this->DB);
            $eg->permission->loadRole();
            $this->assertEqual($eg->toListItemLinked(),'<li><a href="equipment_group.php?eid=201" title="testEqGroup1">testEqGroup1</a>: on the 1/4 hour with 15 minute min and 1 hour max by 15 minute intervals <b>(manager)</b></li>');
        }

    }


?>