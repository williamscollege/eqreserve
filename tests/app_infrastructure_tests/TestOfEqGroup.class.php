<?php
	require_once dirname(__FILE__) . '/../simpletest/unit_tester_DB.php';
    require_once dirname(__FILE__) . '/dataForTesting.php';
    require_once dirname(__FILE__) . '/../../classes/eq_group.class.php';
    require_once dirname(__FILE__) . '/../../classes/eq_subgroup.class.php';
    require_once dirname(__FILE__) . '/../../classes/inst_group.class.php';
    require_once dirname(__FILE__) . '/../../classes/inst_membership.class.php';
    require_once dirname(__FILE__) . '/../../classes/permission.class.php';
    require_once dirname(__FILE__) . '/../../classes/role.class.php';
    require_once dirname(__FILE__) . '/../../classes/user.class.php';


	class TestOfEqGroup extends UnitTestCaseDB
	{

		function setUp() {
            createTestData_InstGroups($this->DB);
            createTestData_EqGroups($this->DB);
            createTestData_EqSubgroups($this->DB);
            createTestData_Permissions($this->DB);
            createTestData_InstMemberships($this->DB);

		}

		function tearDown() {
            removeTestData_InstGroups($this->DB);
            removeTestData_EqGroups($this->DB);
            removeTestData_EqSubgroups($this->DB);
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
	}


?>