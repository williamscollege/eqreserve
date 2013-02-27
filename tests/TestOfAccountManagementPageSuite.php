<?php
require_once('simpletest/autorun.php');
require_once('simpletest/web_tester_WMS.php');
SimpleTest::prefer(new TextReporter());

require_once('../institution.cfg.php');

class TestOfAccountManagementSuite extends TestSuite {
    function TestOfAccountManagementSuite() {
        $this->TestSuite('Account management page tests');
        $this->addFile('account_management_page_tests/AcctMgtTest.php');
     }
}
?>
