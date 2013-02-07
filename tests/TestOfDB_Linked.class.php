<?php
require_once dirname(__FILE__) . '/simpletest/unit_tester_DB.php';
require_once(dirname(__FILE__) . '/simpletest/autorun.php');
require_once('../classes/db_linked.class.php');

class TestOfDB_Linked extends UnitTestCaseDB {
    function testConnectedToDatabase() {
        $this->assertNotNull($this->DB);
    }
}
?>