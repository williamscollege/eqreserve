<?php
require_once('simpletest/autorun.php');
require_once('simpletest/WMS_web_tester.php');
SimpleTest::prefer(new TextReporter());

require_once('../institution.cfg.php');
require_once('../lang.cfg.php');

class TestOfEqGroupSuite extends TestSuite {
	function TestOfEqGroupSuite() {
		$this->TestSuite('Web EqGroup page tests');

		# TEMPORARY AND FAST TESTING
		# TODO: REMOVE THIS TEST FILE (it's already included in: "TestOfWebPageSuite.php")

		# Tests: Equipment Group
		$this->addFile('web_page_tests/EqGroupPageEditGroupTest.php');

	}
}
?>
