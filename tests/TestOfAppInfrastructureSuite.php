<?php
	require_once('simpletest/autorun.php');
	require_once('simpletest/WMS_unit_tester_DB.php');
	SimpleTest::prefer(new TextReporter());

	require_once('../institution.cfg.php');

	class TestOfAppInfrastructureSuite extends TestSuite {
		function TestOfAppInfrastructureSuite() {
			$this->TestSuite('App Infrastructure tests');
			$this->addFile('app_infrastructure_tests/TestOfCalendarUtil.php');

			$this->addFile('app_infrastructure_tests/TestOfUtil.php');

			$this->addFile('app_infrastructure_tests/TestOfDB_Linked.class.php');
			$this->addFile('app_infrastructure_tests/TestOfAuth_Base.class.php');
			$this->addFile('app_infrastructure_tests/TestOfAuth_LDAP.class.php');
			$this->addFile('app_infrastructure_tests/TestOfInstGroup.class.php');
			$this->addFile('app_infrastructure_tests/TestOfInstMembership.class.php');
			$this->addFile('app_infrastructure_tests/TestOfUser.class.php');
			$this->addFile('app_infrastructure_tests/TestOfCommPref.class.php');
			$this->addFile('app_infrastructure_tests/TestOfRole.class.php');
			$this->addFile('app_infrastructure_tests/TestOfEqGroup.class.php');
			$this->addFile('app_infrastructure_tests/TestOfEqSubgroup.class.php');

			$this->addFile('app_infrastructure_tests/TestOfEqItem.class.php');

			$this->addFile('app_infrastructure_tests/TestOfPermission.class.php');
			$this->addFile('app_infrastructure_tests/TestOfReservation.class.php');

			$this->addFile('app_infrastructure_tests/TestOfTimeBlock.class.php');
			$this->addFile('app_infrastructure_tests/TestOfSchedule.class.php');


			# Sound Effect
			$this->addFile('soundForTesting.php');
		}
	}

?>