<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class AcctMgtTest extends WMSWebTestCase {

    function setUp() {
        $addTestUserSql = "INSERT INTO ".User::$dbTable." VALUES (1,'".Auth_Base::$TEST_USERNAME."','".Auth_Base::$TEST_FNAME."','".Auth_Base::$TEST_LNAME."','".Auth_Base::$TEST_SORTNAME."','".Auth_Base::$TEST_EMAIL."','David Keiser-Clark','some important notes',0,0,0)";
        $addTestUserStmt = $this->DB->prepare($addTestUserSql);
        $addTestUserStmt->execute();

        $addTestInstGroupSql = "INSERT INTO ".InstGroup::$dbTable." VALUES (1,'".Auth_Base::$TEST_INST_GROUPS[0]."',0)";
        // normal, normal, deleted, normal
        $addTestInstGroupStmt = $this->DB->prepare($addTestInstGroupSql);
        $addTestInstGroupStmt->execute();

        $linkUserToInstGroupSql = "INSERT INTO ".InstMembership::$dbTable."  VALUES (1,1,1,0)";
        $linkUserToInstGroupStmt = $this->DB->prepare($linkUserToInstGroupSql);
        $linkUserToInstGroupStmt->execute();

        # EqGroup: eq_group_id, name, descr, start_minute, min_duration_minutes, max_duration_minutes, duration_chunk_minutes, flag_delete
        $addTestEqGroupsSql  = "INSERT INTO " . EqGroup::$dbTable . " VALUES    (1,'Nanomajigs','The investigation of really small stuff','0,15,30,45',15,60,15,0),
                                                                                (2,'3D Printers','3dp descr','0,30',30,300,30,0),
                                                                                (3,'Spectrometers','spectrothingies','0,15,30,45',15,60,15,0)
                                                                            ";
        $addTestEqGroupsStmt = $this->DB->prepare($addTestEqGroupsSql);
        $addTestEqGroupsStmt->execute();

        // TODO: set up and check for indirect access via inst_group membership and permissions where entity_type == 'inst_group'
        # Permission[user|inst_group]: permission_id, entity_id, entity_type, role_id, eq_group_id, flag_delete
        $addTestPermissionSql  = "INSERT INTO " . Permission::$dbTable . " VALUES   
                                                                                    (1,1,'user',       3,1,0),
                                                                                    (2,1,'inst_group', 1,2,0),
                                                                                    (3,1,'inst_group', 2,3,0),
                                                                                    (4,1,'user',       3,3,0)
                                                                                    ";
        $addTestPermissionStmt = $this->DB->prepare($addTestPermissionSql);
        $addTestPermissionStmt->execute();
    }

    function tearDown() {
        $rmTestUserSql = "DELETE FROM ".User::$dbTable;
        $rmTestUserStmt = $this->DB->prepare($rmTestUserSql);
        $rmTestUserStmt->execute();

        $rmTestInstGroupSql = "DELETE FROM ".InstGroup::$dbTable;
        $rmTestInstGroupStmt = $this->DB->prepare($rmTestInstGroupSql);
        $rmTestInstGroupStmt->execute();

        $rmLinkUserInstGroupSql = "DELETE FROM ".InstMembership::$dbTable;
        $rmLinkUserInstGroupStmt = $this->DB->prepare($rmLinkUserInstGroupSql);
        $rmLinkUserInstGroupStmt->execute();

        $rmTestEqGroupsSql = "DELETE FROM ".EqGroup::$dbTable;
        $rmTestEqGroupsStmt = $this->DB->prepare($rmTestEqGroupsSql);
        $rmTestEqGroupsStmt->execute();

        $rmTestPermissionSql = "DELETE FROM ".Permission::$dbTable;
        $rmTestPermissionStmt = $this->DB->prepare($rmTestPermissionSql);
        $rmTestPermissionStmt->execute();
    }

    //############################################################

	function getToAcctMgtPage() {
        $this->get('http://localhost/eqreserve/');
        $this->setField('username', TESTINGUSER);
        $this->setField('password', TESTINGPASSWORD);
        $this->click('Sign in');
        $this->clickLink(TESTINGUSER);
	}

    function testAccessAcctMgt() {
        $this->getToAcctMgtPage();
        $this->assertResponse(200);
        $this->assertPattern('/Signed in: \<a[^\>]*\>'.TESTINGUSER.'\<\/a\>/');

        $this->assertFieldValue("accountName",Auth_Base::$TEST_FNAME.' '.Auth_Base::$TEST_LNAME, "Violet Bovine", "missing name" );
        $this->assertFieldValue("accountUsername",Auth_Base::$TEST_USERNAME, "mockUser", "missing username" );
        $this->assertFieldValue("accountEmail",Auth_Base::$TEST_EMAIL, "vbovine@institution.edu", "missing email" );

        $this->assertText(Auth_Base::$TEST_INST_GROUPS[0]);
        $this->assertText(Auth_Base::$TEST_INST_GROUPS[1]);
        $this->assertText(Auth_Base::$TEST_INST_GROUPS[2]);
        $this->assertText(Auth_Base::$TEST_INST_GROUPS[3]);
        $this->assertText(Auth_Base::$TEST_INST_GROUPS[4]);

        $this->assertText('Nanomajigs');
        $this->assertText('3D Printers');
        $this->assertText('Spectrometers');
    }

}
