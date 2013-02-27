<?php
require_once('simpletest/autorun.php');
require_once('simpletest/web_tester_WMS.php');
SimpleTest::prefer(new TextReporter());

require_once('../institution.cfg.php');

class TestOfIndexSuite extends TestSuite {
    function TestOfIndexSuite() {
        $this->TestSuite('Index page tests');
        $this->addFile('index_page_tests/IndexPageLoadTest.php');
        $this->addFile('index_page_tests/IndexPageAuthTest.php');
     }
}
?>
