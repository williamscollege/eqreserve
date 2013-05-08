<?php
	require_once('simpletest/autorun.php');
	require_once('simpletest/WMS_web_tester.php');
	SimpleTest::prefer(new TextReporter());

	require_once('../institution.cfg.php');
	require_once('../lang.cfg.php');

	class TestOfWebSuite extends TestSuite {
		function TestOfWebSuite() {
			$this->TestSuite('Web page tests');

			# Tests: Index page
			$this->addFile('web_page_tests/IndexPageLoadTest.php');
			$this->addFile('web_page_tests/IndexPageAuthTest.php');
			$this->addFile('web_page_tests/IndexPageDBTest.php');
			$this->addFile('web_page_tests/IndexPageFormAddEqGroupTest.php');

			# Tests: Equipment Group
			$this->addFile('web_page_tests/EqGroupPageEditGroupTest.php');
			$this->addFile('web_page_tests/EqGroupPageReservationsTest.php');
			$this->addFile('web_page_tests/AjaxEqGroupTest.php');

			# Tests: Account management
			$this->addFile('web_page_tests/AcctMgtTest.php');

            # Tests: user search / find
            $this->addFile('web_page_tests/AjaxUserAndGroupSearchTest.php');

            # Tests: Institutional Group
            $this->addFile('web_page_tests/InstGroupTest.php');


			# Tests: Schedules / Reservations
			$this->addFile('web_page_tests/ScheduleTest.php');
			$this->addFile('web_page_tests/AjaxScheduleTest.php');

			# Tests: Communication Preferences
			$this->addFile('web_page_tests/AjaxCommPrefTest.php');

			# Sound Effect
			$this->addFile('soundForTesting.php');
		}
	}

?>