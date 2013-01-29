<?php
require_once dirname(__FILE__) . '/simpletest/unit_tester_DB.php';
require_once(dirname(__FILE__) . '/simpletest/autorun.php');
require_once('../classes/db_linked.class.php');

class TestOfDB_Linked extends UnitTestCaseDB {
    function testConnectedToDatabase() {
        //$DB = new PDO("mysql:dbname=$DB_NAME;host=$DB_SERVER",$DB_USER,$DB_PASS);
        $this->assertNotNull($this->DB);
    }
}
?>