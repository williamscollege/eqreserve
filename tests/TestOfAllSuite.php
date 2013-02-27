<?php
require_once('simpletest/autorun.php');
SimpleTest::prefer(new TextReporter());

require_once('../institution.cfg.php');

class TestOfAllSuite extends TestSuite {
    function TestOfAllSuite() {
        $this->TestSuite('Full application test');
        $this->addFile('TestOfAppInfrastructureSuite.php');
        $this->addFile('TestOfAccountManagementPageSuite.php');
        $this->addFile('TestOfIndexPageSuite.php');
     }
}
?>