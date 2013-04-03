<?php
require_once dirname(__FILE__) . '/../simpletest/unit_tester.php';
require_once dirname(__FILE__) . '/../../util.php';


class TestOfUtil extends UnitTestCase {

	function setUp() {
	}
	
	function tearDown() {
	}


    function testGenRandomIdString() {
        
        $randomId = util_genRandomIdString(24);

        $this->assertEqual(24,strlen($randomId));
    }   

    function testWipeSession() {
        //session_start();
        $_SESSION['isAuthenticated'] = 'foo';
        $_SESSION['fingerprint'] = 'bar';
        $_SESSION['userdata'] = array('baz');

		$this->expectError("Cannot modify header information - headers already sent");

        util_wipeSession();

        $this->assertFalse(isset($_SESSION['isAuthenticated']));
        $this->assertFalse(isset($_SESSION['fingerprint']));
        $this->assertFalse(isset($_SESSION['userdata']));
    }   

    function testCheckAuthentication() {
        $this->assertFalse(util_checkAuthentication());

        $_SESSION['isAuthenticated'] = false;

        $this->assertFalse(util_checkAuthentication());

        $_SESSION['isAuthenticated'] = true;

        $this->assertTrue(util_checkAuthentication());
    }   

    function testCreateDbConnection() {
        
        $dbConn = util_createDbConnection(24);

        $this->assertEqual(get_class($dbConn),'PDO');
    }   
}
?>