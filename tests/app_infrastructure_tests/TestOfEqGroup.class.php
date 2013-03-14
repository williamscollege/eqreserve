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
/*
			# InstGroup: inst_group_id, name, flag_delete
			$addTestInstGroupsSql  = "INSERT INTO " . InstGroup::$dbTable . " VALUES	(1,'STAFF',0),
																						(2,'STUDENTS',0),
																						(3,'12F-PHYS-101',0),
																						(4,'13S-ECON-305',1),
																						(5,'JESUP-STC',0)
																					";
			$addTestInstGroupsStmt = $this->DB->prepare($addTestInstGroupsSql);
			$addTestInstGroupsStmt->execute();
*/
            createTestData_InstGroups($this->DB);

/*
			# EqGroup: eq_group_id, name, descr, start_minute, min_duration_minutes, max_duration_minutes, duration_chunk_minutes, flag_delete
			$addTestEqGroupsSql  = "INSERT INTO " . EqGroup::$dbTable . " VALUES 	(1,'Nanomajigs','The investigation of really small stuff','0,15,30,45',15,60,15,0),
 	                                                        	            		(2,'3D Printers','3dp descr','0,30',30,300,30,0),
		                                                	                		(3,'Spectrometers','spectrothingies','0,15,30,45',15,60,15,0),
		                                            	                    		(4,'Nucular Toyz','nuks','0,15,30,45',15,60,15,0),
		                                          	            	          		(5,'Biostuff','blobs','0,15,30,45',15,60,15,1),
		                                                                			(6,'Outdoor Educ Equipment Rental','outdoor stuff avail to all students','0,15,30,45',15,1800,15,0)
		                                                                		";
			$addTestEqGroupsStmt = $this->DB->prepare($addTestEqGroupsSql);
			$addTestEqGroupsStmt->execute();
*/
            createTestData_EqGroups($this->DB);

/*
            # EqSubgroup: eq_subgroup_id', 'eq_group_id', 'name','descr','ordering','flag_delete'
            $addTestEqSubgroupsSql  = "INSERT INTO " . EqSubgroup::$dbTable . " VALUES 
                                                                                    (1,1,'mini','mostly smallish',1,0),
                                                                                    (2,1,'micro','more smallish',2,0),
                                                                                    (3,1,'nano','really smallish',3,0),
                                                                                    (4,1,'femto','not kidding, very very small',4,0)
                                                                                ";
            $addTestEqSubgroupsStmt = $this->DB->prepare($addTestEqSubgroupsSql);
            $addTestEqSubgroupsStmt->execute();
//print_r($addTestEqSubgroupsStmt->errorInfo());
//exit;
*/
            createTestData_EqSubgroups($this->DB);


			// TODO: set up and check for indirect access via inst_group membership and permissions where entity_type == 'inst_group'
			# Permission[user|inst_group]: permission_id, entity_id, entity_type, role_id, eq_group_id, flag_delete
/*
			$addTestPermissionSql  = "INSERT INTO " . Permission::$dbTable . " VALUES	(1,1,'user',2,2,0),
																						(2,1,'user',3,1,0),
																						(3,1,'user',3,3,0),
																						(4,1,'user',3,4,1),
																						(5,1,'user',1,4,0),
																						(6,2,'user',3,5,0),
																						(7,2,'user',3,5,0),
																						(8,1,'inst_group',1,1,0),
																						(9,1,'inst_group',1,2,0),
																						(10,1,'inst_group',1,3,0),
																						(11,2,'inst_group',1,4,0),
																						(12,2,'inst_group',1,5,0),
																						(13,2,'inst_group',3,6,0),
																						(14,3,'inst_group',3,3,0),
																						(15,3,'inst_group',3,1,0)
																						";
			$addTestPermissionStmt = $this->DB->prepare($addTestPermissionSql);
			$addTestPermissionStmt->execute();
*/
            createTestData_Permissions($this->DB);

/*
			# inst_memberships: inst_membership_id,user_id,inst_group_id,flag_delete
			$insertTestInstMembershipSql = "INSERT INTO ". InstMembership::$dbTable ." VALUES	(1,1,1,0),
																								(2,1,3,0),
																								(3,1,6,0),
																								(4,2,4,0),
																								(5,3,5,1)
																							";
			$insertTestInstMembershipStmt = $this->DB->prepare($insertTestInstMembershipSql);
			$insertTestInstMembershipStmt->execute();
*/
            createTestData_InstMemberships($this->DB);

		}

		function tearDown() {
            removeTestData_InstGroups($this->DB);
            removeTestData_EqGroups($this->DB);
            removeTestData_EqSubgroups($this->DB);
            removeTestData_Permissions($this->DB);
            removeTestData_InstMemberships($this->DB);
/*
			$rmTestInstGroupsSql = "DELETE FROM ".InstGroup::$dbTable;
			$rmTestInstGroupsStmt = $this->DB->prepare($rmTestInstGroupsSql);
			$rmTestInstGroupsStmt->execute();

			$rmTestEqGroupsSql = "DELETE FROM ".EqGroup::$dbTable;
			$rmTestEqGroupsStmt = $this->DB->prepare($rmTestEqGroupsSql);
			$rmTestEqGroupsStmt->execute();

            $rmTestEqSubgroupsSql = "DELETE FROM ".EqSubgroup::$dbTable;
            $rmTestEqSubgroupsStmt = $this->DB->prepare($rmTestEqSubgroupsSql);
            $rmTestEqSubgroupsStmt->execute();

			$rmTestPermissionSql = "DELETE FROM ".Permission::$dbTable;
			$rmTestPermissionStmt = $this->DB->prepare($rmTestPermissionSql);
			$rmTestPermissionStmt->execute();

			$rmTestPermissionSql = "DELETE FROM ".Permission::$dbTable;
			$rmTestPermissionStmt = $this->DB->prepare($rmTestPermissionSql);
			$rmTestPermissionStmt->execute();

			$rmTestInstMembershipSql = "DELETE FROM ".InstMembership::$dbTable;
			$rmTestInstMembershipStmt = $this->DB->prepare($rmTestInstMembershipSql);
			$rmTestInstMembershipStmt->execute();
*/
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