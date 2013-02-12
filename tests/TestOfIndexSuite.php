<?php
require_once('simpletest/autorun.php');
require_once('simpletest/web_tester.php');
SimpleTest::prefer(new TextReporter());

class TestOfIndexSuite extends TestSuite {
    function TestOfIndexSuite() {
        $this->TestSuite('Index page tests');
        $this->addFile('IndexPageLoadTest.php');
     }
}
?>
