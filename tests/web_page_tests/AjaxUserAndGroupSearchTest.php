<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class AjaxUserAndGroupSearchTest extends WMSWebTestCase {

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
    function testSearchUserAngGroupAccess() {
        $this->signIn();

        $this->get('http://localhost/eqreserve/ajax_user_and_group_search.php?action=find&searchTerm=test');

        $this->assertPattern('/"status":"success"/');
    }
}