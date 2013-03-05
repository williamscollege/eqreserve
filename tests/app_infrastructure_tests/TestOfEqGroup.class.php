<?php
	require_once dirname(__FILE__) . '/../simpletest/unit_tester_DB.php';
	require_once dirname(__FILE__) . '/../../classes/eq_group.class.php';


	class TestOfEqGroup extends UnitTestCaseDB
	{

		function setUp() {
			# InstGroup: inst_group_id, name, flag_delete
			$addTestInstGroupsSql  = "INSERT INTO " . InstGroup::$dbTable . " VALUES	(1,'STAFF',0),
																						(2,'STUDENTS',0),
																						(3,'12F-PHYS-101',0),
																						(4,'13S-ECON-305',1),
																						(5,'JESUP-STC',0)
																					";
			$addTestInstGroupsStmt = $this->DB->prepare($addTestInstGroupsSql);
			$addTestInstGroupsStmt->execute();

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

			// TODO: set up and check for indirect access via inst_group membership and permissions where entity_type == 'inst_group'
			# Permission[user|inst_group]: permission_id, entity_id, entity_type, role_id, eq_group_id, flag_delete
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


			# inst_memberships: inst_membership_id,user_id,inst_group_id,flag_delete
			$insertTestInstMembershipSql = "INSERT INTO ". InstMembership::$dbTable ." VALUES	(1,1,1,0),
																								(2,1,3,0),
																								(3,1,6,0),
																								(4,2,4,0),
																								(5,3,5,1)
																							";
			$insertTestInstMembershipStmt = $this->DB->prepare($insertTestInstMembershipSql);
			$insertTestInstMembershipStmt->execute();
		}

		function tearDown() {
			$rmTestInstGroupsSql = "DELETE FROM ".InstGroup::$dbTable;
			$rmTestInstGroupsStmt = $this->DB->prepare($rmTestInstGroupsSql);
			$rmTestInstGroupsStmt->execute();

			$rmTestEqGroupsSql = "DELETE FROM ".EqGroup::$dbTable;
			$rmTestEqGroupsStmt = $this->DB->prepare($rmTestEqGroupsSql);
			$rmTestEqGroupsStmt->execute();

			$rmTestPermissionSql = "DELETE FROM ".Permission::$dbTable;
			$rmTestPermissionStmt = $this->DB->prepare($rmTestPermissionSql);
			$rmTestPermissionStmt->execute();

			$rmTestPermissionSql = "DELETE FROM ".Permission::$dbTable;
			$rmTestPermissionStmt = $this->DB->prepare($rmTestPermissionSql);
			$rmTestPermissionStmt->execute();

			$rmTestInstMembershipSql = "DELETE FROM ".InstMembership::$dbTable;
			$rmTestInstMembershipStmt = $this->DB->prepare($rmTestInstMembershipSql);
			$rmTestInstMembershipStmt->execute();
		}

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
			$ig = new InstGroup(['inst_group_id' => 1, 'DB' => $this->DB]);
			$egs = EqGroup::getEqGroupsForInstGroup($ig);

			$this->assertNotNull($egs);
			$this->assertTrue(is_array($egs));
			$this->assertEqual(count($egs), 3);
			$this->assertEqual(get_class($egs[0]), 'EqGroup');

			usort($egs, "EqGroup::cmpAlphabetical");

			$this->assertEqual($egs[0]->name, '3D Printers');
			$this->assertEqual($egs[1]->name, 'Nanomajigs');
			$this->assertEqual($egs[2]->name, 'Spectrometers');

			$this->assertEqual($egs[0]->eq_group_id, 2);
			$this->assertEqual($egs[1]->eq_group_id, 1);
			$this->assertEqual($egs[2]->eq_group_id, 3);

			$this->assertEqual($egs[0]->permission->role->role_id, 1);
			$this->assertEqual($egs[1]->permission->role->role_id, 1);
			$this->assertEqual($egs[2]->permission->role->role_id, 1);
		}

		public function TestOfGetEqGroupsForUser() {
			$user = new User(['user_id' => 1, 'DB' => $this->DB]);
			$egs = EqGroup::getEqGroupsForUser($user);

			$this->assertNotNull($egs);
			$this->assertTrue(is_array($egs));
			$this->assertEqual(count($egs), 4);
			$this->assertEqual(get_class($egs[0]), 'EqGroup');

			usort($egs, "EqGroup::cmpAlphabetical");

			$this->assertEqual($egs[0]->name, '3D Printers');
			$this->assertEqual($egs[1]->name, 'Nanomajigs');
			$this->assertEqual($egs[2]->name, 'Nucular Toyz');
			$this->assertEqual($egs[3]->name, 'Spectrometers');

			$this->assertEqual($egs[0]->eq_group_id, 2);
			$this->assertEqual($egs[1]->eq_group_id, 1);
			$this->assertEqual($egs[2]->eq_group_id, 4);
			$this->assertEqual($egs[3]->eq_group_id, 3);

			$this->assertEqual($egs[0]->permission->role->role_id, 2);
			$this->assertEqual($egs[1]->permission->role->role_id, 3);
			$this->assertEqual($egs[2]->permission->role->role_id, 1);
			$this->assertEqual($egs[3]->permission->role->role_id, 3);
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

			$user = new User(['user_id' => 1, 'DB' => $this->DB]);

			$egs = EqGroup::getAllEqGroupsForNonAdminUser($user);

			$this->assertNotNull($egs);
			$this->assertTrue(is_array($egs));
			$this->assertEqual(count($egs), 4);
			$this->assertEqual(get_class($egs[1]), 'EqGroup');

			usort($egs, "EqGroup::cmpAlphabetical");

//			echo "getAllEqGroupsForNonAdminUser<br />";
//			$this->dump($egs);

			$this->assertEqual($egs[0]->name, '3D Printers');
			$this->assertEqual($egs[1]->name, 'Nanomajigs');
			$this->assertEqual($egs[2]->name, 'Nucular Toyz');
			$this->assertEqual($egs[3]->name, 'Spectrometers');

			$this->assertEqual($egs[0]->eq_group_id, 2);
			$this->assertEqual($egs[1]->eq_group_id, 1);
			$this->assertEqual($egs[2]->eq_group_id, 4);
			$this->assertEqual($egs[3]->eq_group_id, 3);

			$this->assertEqual($egs[0]->permission->role->priority, 1);
			$this->assertEqual($egs[1]->permission->role->priority, 1);
			$this->assertEqual($egs[2]->permission->role->priority, 1);
			$this->assertEqual($egs[3]->permission->role->priority, 1);

			$this->assertEqual($egs[0]->permission->role->role_id, 1);
			$this->assertEqual($egs[1]->permission->role->role_id, 1);
			$this->assertEqual($egs[2]->permission->role->role_id, 1);
			$this->assertEqual($egs[3]->permission->role->role_id, 1);

//			exit;
		}

	}


?>