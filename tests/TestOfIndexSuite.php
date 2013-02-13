<?php
require_once('simpletest/autorun.php');
require_once('simpletest/web_tester.php');
SimpleTest::prefer(new TextReporter());

require_once('../institution.cfg.php');

class TestOfIndexSuite extends TestSuite {
    function TestOfIndexSuite() {
        $this->TestSuite('Index page tests');
        $this->addFile('index_tests/IndexPageLoadTest.php');
        $this->addFile('index_tests/IndexPageAuthTest.php');
     }
}
?>
