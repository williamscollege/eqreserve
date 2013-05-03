<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class AjaxEqGroupTest extends WMSWebTestCase {

    function setUp() {
        createAllTestData($this->DB);
    }

    function tearDown() {
        removeAllTestData($this->DB);
    }

    //############################################################

    function signIn() {
        $this->get('http://localhost/eqreserve/');
        $this->setField('username', TESTINGUSER);
        $this->setField('password', TESTINGPASSWORD);
        $this->click('Sign in');
    }

    //############################################################

    // TODO: create tests for base eq group actions (create, delete, update)

    function testEqGroupAjaxNoAccessWhenNotLoggedIn() {
        $this->get('http://localhost/eqreserve/ajax_eq_group.php?eq_group=201&ajaxVal_action=null');

        $this->assertPattern('/not authenticated/i');
    }

    function testEqGroupAjaxInvalidAction() {
        $this->signIn();
        $base_eg = EqGroup::getOneFromDb(['eq_group_id'=>201],$this->DB);
        $this->assertTrue($base_eg->matchesDb);

        $this->get('http://localhost/eqreserve/ajax_eq_group.php?ajaxVal_ID=201&ajaxVal_action=blah');

        $this->assertPattern('/"status":"failure"/');
    }

    function testEqGroupAjaxManagerGetsAccess() {
        $this->signIn();

        $this->get('http://localhost/eqreserve/ajax_eq_group.php?eq_group=201&ajaxVal_action=null');

        $this->assertPattern('/"status":"success"/');
        $eg = EqGroup::getOneFromDb(['eq_group_id'=>201],$this->DB);
        $this->assertTrue($eg->matchesDb);
    }

    function testEqGroupAjaxNonManagerGetsNoAccess() {
        $this->signIn();

        $this->get('http://localhost/eqreserve/ajax_eq_group.php?eq_group=202&ajaxVal_action=null');

        $this->assertPattern('/"status":"failure"/');
        $eg = EqGroup::getOneFromDb(['eq_group_id'=>202],$this->DB);
        $this->assertTrue($eg->matchesDb);
    }

    function testEqGroupAjaxSystemAdminGetsAccess() {
        $this->signIn();
        makeAuthedTestUserAdmin($this->DB);

        $this->get('http://localhost/eqreserve/ajax_eq_group.php?eq_group=202&ajaxVal_action=null');

        $this->assertPattern('/"status":"success"/');
        $eg = EqGroup::getOneFromDb(['eq_group_id'=>202],$this->DB);
        $this->assertTrue($eg->matchesDb);
    }

    function testEqGroupAjaxRemoveConsumerAccess() {
        $this->signIn();

        // other user access
        $this->get('http://localhost/eqreserve/ajax_eq_group.php?eq_group=201&ajaxVal_action=removePermission&permission_id=718');
        $this->assertPattern('/"status":"success"/');
        $p = Permission::getOneFromDb(['permission_id'=>718],$this->DB);
        $this->assertFalse($p->matchesDb);

        // self, but have manager access so OK to remove consumer access
        $this->get('http://localhost/eqreserve/ajax_eq_group.php?eq_group=201&ajaxVal_action=removePermission&permission_id=702');
        $this->assertPattern('/"status":"success"/');
        $p = Permission::getOneFromDb(['permission_id'=>702],$this->DB);
        $this->assertFalse($p->matchesDb);

        // inst group access
        $this->get('http://localhost/eqreserve/ajax_eq_group.php?eq_group=201&ajaxVal_action=removePermission&permission_id=711');
        $this->assertPattern('/"status":"success"/');
        $p = Permission::getOneFromDb(['permission_id'=>711],$this->DB);
        $this->assertFalse($p->matchesDb);
    }

    function testEqGroupAjaxRemoveManagerAccess() {
        $this->signIn();

        // other user access
        $this->get('http://localhost/eqreserve/ajax_eq_group.php?eq_group=201&ajaxVal_action=removePermission&permission_id=719');
        $this->assertPattern('/"status":"success"/');
        $p = Permission::getOneFromDb(['permission_id'=>719],$this->DB);
        $this->assertFalse($p->matchesDb);
    }

    function testEqGroupAjaxNonAdminCannotRemoveTheirOwnManagerAccess() {
        $this->signIn();
        $base_eg = EqGroup::getOneFromDb(['eq_group_id'=>201],$this->DB);
        $this->assertTrue($base_eg->matchesDb);

        // direct
        $this->get('http://localhost/eqreserve/ajax_eq_group.php?eq_group=203&ajaxVal_action=removePermission&permission_id=703');
        $this->assertPattern('/"status":"failure"/');
        $p = Permission::getOneFromDb(['permission_id'=>703],$this->DB);
        $this->assertTrue($p->matchesDb);

        // indirect, via inst group
        $this->get('http://localhost/eqreserve/ajax_eq_group.php?eq_group=201&ajaxVal_action=removePermission&permission_id=707');
        $this->assertPattern('/"status":"failure"/');
        $p = Permission::getOneFromDb(['permission_id'=>707],$this->DB);
        $this->assertTrue($p->matchesDb);
    }
}