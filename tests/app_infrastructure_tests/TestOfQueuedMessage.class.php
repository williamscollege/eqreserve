<?php
	require_once dirname(__FILE__) . '/../simpletest/WMS_unit_tester_DB.php';
//	require_once dirname(__FILE__) . '/../../classes/role.class.php';


	class TestOfQueuedMessage extends WMSUnitTestCaseDB
	{

		function setUp() {
            createAllTestData($this->DB);
		}

		function tearDown() {
            removeAllTestData($this->DB);
		}


		public function testQueuedMessageBasic(){
			// placeholder - basically, the fact that there's a test means various stuff gets loaded, which means checks for various low level bugs happen
		}

	}

?>