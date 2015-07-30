<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class AjaxEqSubgroupItemTest extends WMSWebTestCase
{

    function setUp()
    {
        createAllTestData($this->DB);
    }

    function tearDown()
    {
        removeAllTestData($this->DB);
    }

    //############################################################

    function signIn()
    {
        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/');
        $this->setField('username', TESTINGUSER);
        $this->setField('password', TESTINGPASSWORD);
        $this->click('Sign in');
    }

    //############################################################

    // TODO: create tests for base eq group actions (create, delete, update)

    //----------------------------------------------------------
    // basic access checking

    function testEqGroupAjaxNoAccessWhenNotLoggedIn()
    {
        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/ajax_actions/ajax_eq_subgroup_item.php?eq_group=201&ajaxVal_Action=null');

        $this->assertPattern('/not authenticated/i');
    }

    function testEqGroupAjaxInvalidAction()
    {
        $this->signIn();
        $base_eg = EqGroup::getOneFromDb(['eq_group_id' => 201], $this->DB);
        $this->assertTrue($base_eg->matchesDb);

        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/ajax_actions/ajax_eq_subgroup_item.php?ajaxVal_GroupID=201&ajaxVal_Action=blah');

        $this->assertPattern('/"status":"failure"/');
    }

    function testEqGroupAjaxManagerGetsAccess()
    {
        $this->signIn();

        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/ajax_actions/ajax_eq_subgroup_item.php?eq_group=201&ajaxVal_Action=null');

        $this->assertPattern('/"status":"success"/');
        $eg = EqGroup::getOneFromDb(['eq_group_id' => 201], $this->DB);
        $this->assertTrue($eg->matchesDb);
    }

    function testEqGroupAjaxNonManagerGetsNoAccess()
    {
        $this->signIn();

        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/ajax_actions/ajax_eq_subgroup_item.php?eq_group=202&ajaxVal_Action=null');

        $this->assertPattern('/"status":"failure"/');
        $eg = EqGroup::getOneFromDb(['eq_group_id' => 202], $this->DB);
        $this->assertTrue($eg->matchesDb);
    }

    function testEqGroupAjaxSystemAdminGetsAccess()
    {
        $this->signIn();
        makeAuthedTestUserAdmin($this->DB);

        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/ajax_actions/ajax_eq_subgroup_item.php?eq_group=202&ajaxVal_Action=null');

        $this->assertPattern('/"status":"success"/');
        $eg = EqGroup::getOneFromDb(['eq_group_id' => 202], $this->DB);
        $this->assertTrue($eg->matchesDb);
    }

    //----------------------------------------------------------
    // checks that given the proper url, the ajax handler moves the subgroup of an item
    function testChangeSubgroupOfItem() {
        $this->signIn();
        $this->get('http://localhost' . LOCAL_WEBSERVER_PORT_SPEC . '/eqreserve/ajax_actions/ajax_eq_subgroup_item.php?ajaxVal_Action=edit-item&ajaxVal_Description=normal&ajaxVal_ImageFileName=none&ajaxVal_ItemID=401&ajaxVal_ItemSubGroup=testSubgroup1&ajaxVal_Name=testItem1&ajaxVal_SubgroupID=303');

        $ei = EqItem::getOneFromDb(['eq_item_id' => 401], $this->DB);
        $subG = EqSubgroup::getOneFromDb(['eq_subgroup_id' => $ei->eq_subgroup_id, 'flag_delete' => FALSE], $this->DB);
        $this->assertEqual(303, $subG->eq_subgroup_id);
    }
}