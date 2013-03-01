<?php
require_once('simpletest/autorun.php');
SimpleTest::prefer(new TextReporter());

require_once('../institution.cfg.php');

class TestOfAppInfrastructureSuite extends TestSuite {
    function TestOfAppInfrastructureSuite() {
        $this->TestSuite('App Infrastructure tests');
        $this->addFile('app_infrastructure_tests/TestOfDB_Linked.class.php');
        $this->addFile('app_infrastructure_tests/TestOfAuth_Base.class.php');
        $this->addFile('app_infrastructure_tests/TestOfAuth_LDAP.class.php');
        $this->addFile('app_infrastructure_tests/TestOfUser.class.php');
        $this->addFile('app_infrastructure_tests/TestOfEqGroup.class.php');
        $this->addFile('app_infrastructure_tests/TestOfRole.class.php');
     }
}
?>
