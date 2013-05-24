<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class CommandLineBlockedTest extends WMSWebTestCase {

    function setUp() {
        createAllTestData($this->DB);
    }

    function tearDown() {
        removeAllTestData($this->DB);
    }

    //############################################################

    function testNoAccessToCommandLineScripts() {
        $this->get('http://localhost/eqreserve/command_line_scripts/cl_head.php');
        $this->assertEqual('no web access to this script',$this->getBrowser()->getContent());


    }
}