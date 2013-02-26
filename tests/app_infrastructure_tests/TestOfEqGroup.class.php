<?php
	require_once dirname(__FILE__) . '/../simpletest/unit_tester_DB.php';
	require_once dirname(__FILE__) . '/../../classes/eq_group.class.php';


	class TestOfEqGroup extends UnitTestCaseDB
	{

		function setUp() {
			# Role: role_id, name, descr
			$addTestRolesSql  = "INSERT INTO " . Role::$dbTable . " VALUES (1,'admin','system admins'),
																			(2,'manager','midlevels'),
																			(3,'consumer','peons')
																			";
			$addTestRolesStmt = $this->DB->prepare($addTestRolesSql);
			$addTestRolesStmt->execute();

			# InstGroup: inst_group_id, name, flag_delete
			$addTestInstGroupsSql  = "INSERT INTO " . InstGroup::$dbTable . " VALUES (1,'STAFF',0),
																					(2,'STUDENTS',0),
																					(3,'12F-PHYS-101',0),
																					(4,'13S-ECON-305',1)
																					(5,'JESUP-STC',0)
																					";
			$addTestInstGroupsStmt = $this->DB->prepare($addTestInstGroupsSql);
			$addTestInstGroupsStmt->execute();

			# EqGroup: eq_group_id, name, descr, start_minute, min_duration_minutes, max_duration_minutes, duration_chunk_minutes, flag_delete
			$addTestEqGroupsSql  = "INSERT INTO " . EqGroup::$dbTable . " VALUES (1,'Nanomajigs','The investigation of really small stuff','0,15,30,45',15,60,15,0),
 	                                                                    		(2,'3D Printers','3dp descr','0,30',30,300,30,0),
		                                                                		(3,'Spectrometers','spectrothingies','0,15,30,45',15,60,15,0),
		                                                                		(4,'Nucular Toyz','nuks','0,15,30,45',15,60,15,0),
		                                                                		(5,'Biostuff','blobs','0,15,30,45',15,60,15,1)
		                                                                		(6,'Outdoor Educ Equipment Rental','outdoor stuff avail to all students','0,15,30,45',15,1800,15,0)
		                                                                		";
			$addTestEqGroupsStmt = $this->DB->prepare($addTestEqGroupsSql);
			$addTestEqGroupsStmt->execute();

			# Permissions[user]: permission_id, entity_id, entity_type, role_id, eq_group_id, flag_delete
			$addTestPermissionsSql  = "INSERT INTO " . Permissions::$dbTable . " VALUES (1,1,'user',2,2,0),
																						(2,1,'user',3,1,0),
																						(3,1,'user',3,3,0),
																						(4,1,'user',3,4,1),
																						(5,1,'user',3,5,0)
																						";
			$addTestPermissionsStmt = $this->DB->prepare($addTestPermissionsSql);
			$addTestPermissionsStmt->execute();

			// TODO: set up and check for indirect access via inst_group membership and permissions where entity_type == 'inst_group'
			# Permissions[inst_group]: permission_id, entity_id, entity_type, role_id, eq_group_id, flag_delete
			$addTestPermissionsSql  = "INSERT INTO " . Permissions::$dbTable . " VALUES (6,1,'inst_group',1,1,0),
																						(7,1,'inst_group',1,2,0),
																						(8,1,'inst_group',1,3,0),
																						(9,1,'inst_group',1,4,0),
																						(10,1,'inst_group',1,5,0),
																						(11,2,'inst_group',3,6,0),
																						(12,3,'inst_group',3,3,0),
																						(13,3,'inst_group',3,1,0)
																						";
			$addTestPermissionsStmt = $this->DB->prepare($addTestPermissionsSql);
			$addTestPermissionsStmt->execute();
		}

		function tearDown() {
			$rmTestRolesSql = "DELETE FROM ".Role::$dbTable;
			$rmTestRolesStmt = $this->DB->prepare($rmTestRolesSql);
			$rmTestRolesStmt->execute();

			$rmTestInstGroupsSql = "DELETE FROM ".InstGroup::$dbTable;
			$rmTestInstGroupsStmt = $this->DB->prepare($rmTestInstGroupsSql);
			$rmTestInstGroupsStmt->execute();

			$rmTestEqGroupsSql = "DELETE FROM ".EqGroup::$dbTable;
			$rmTestEqGroupsStmt = $this->DB->prepare($rmTestEqGroupsSql);
			$rmTestEqGroupsStmt->execute();

			$rmTestPermissionsSql = "DELETE FROM ".Permissions::$dbTable;
			$rmTestPermissionsStmt = $this->DB->prepare($rmTestPermissionsSql);
			$rmTestPermissionsStmt->execute();

			$rmTestPermissionsSql = "DELETE FROM ".Permissions::$dbTable;
			$rmTestPermissionsStmt = $this->DB->prepare($rmTestPermissionsSql);
			$rmTestPermissionsStmt->execute();
		}

		public function TestOfEqGroupCmp()
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

		public function TestOfGetAllEqGroupsForNonAdminUser()
		{
			$user = new User(['user_id' => 1, 'DB' => $this->DB]);

			// obsolete; exists within eq_group class: $insts = EqGroup::getAllInstGroupsForUser($user);

			$eqs = EqGroup::getAllEqGroupsForUser($user);

# problem on Line 115: I'm returning an array, instead of the expected EqGroup object
print_r($eqs);
			usort($eqs, "EqGroup::cmpAlphabetical");

			$this->assertTrue(is_array($eqs));
			$this->assertEqual(get_class($eqs[0]), 'EqGroup');
			$this->assertEqual(count($eqs), 3);
			$this->assertEqual($eqs[0]->name, '3D Printers');
			$this->assertEqual($eqs[1]->name, 'Nanomajigs');
			$this->assertEqual($eqs[2]->name, 'Spectrometers');

			$this->assertEqual($eqs[0]->role->name, 'manager');
			$this->assertEqual($eqs[1]->role->name, 'consumer');
			$this->assertEqual($eqs[2]->role->name, 'consumer');
		}


		# TODO: Create TEST_DB that mimics live db with purpose of independently running TESTS on it w/o effecting live db

	}


?>