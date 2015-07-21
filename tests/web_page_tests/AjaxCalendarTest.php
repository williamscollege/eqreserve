<?php
    require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

    class AjaxCalendarTest extends WMSWebTestCase
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
    }