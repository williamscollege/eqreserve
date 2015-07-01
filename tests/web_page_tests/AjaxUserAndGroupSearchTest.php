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
        $this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/');
        $this->setField('username', TESTINGUSER);
        $this->setField('password', TESTINGPASSWORD);
        $this->click('Sign in');
    }

    //############################################################
    function testSearchUserAndGroupAccess() {
        $this->signIn();

        $this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/ajax_actions/ajax_user_and_group_search.php?action=find&searchTerm=test');

        $this->assertPattern('/"status":"success"/');

        $res_data_structure = json_decode($this->getBrowser()->getContent());
        $expected_number = 10;

        if (strpos(TESTINGUSER,'test')) {
            $expected_number++;
        }

        $this->assertEqual(count($res_data_structure->searchResults),$expected_number);

        //$this->dump($res_data_structure);
    }


    //############################################################
    function testSearchUserAndGroupLDAPFindOne() {
        $this->signIn();

        $this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/ajax_actions/ajax_user_and_group_search.php?action=find&searchTerm=cwarren');

        $this->assertPattern('/"status":"success"/');

        $res_data_structure = json_decode($this->getBrowser()->getContent());
        $this->assertEqual(count($res_data_structure->searchResults),1);

        //$this->dump($res_data_structure);
    }

    //############################################################
    function testSearchUserAndGroupLDAPFindSeveral() {
        $this->signIn();

        $this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/ajax_actions/ajax_user_and_group_search.php?action=find&searchTerm=warren');

        $this->assertPattern('/"status":"success"/');

        $res_data_structure = json_decode($this->getBrowser()->getContent());
        $this->assertEqual(count($res_data_structure->searchResults),2);

        //$this->dump($res_data_structure);
    }

}