<?php
/**
 *  base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage UnitTester
 *  @version    $Id: unit_tester.php 1882 2009-07-01 14:30:05Z lastcraft $
 */

/**#@+
 *  include other SimpleTest class files
 */
require_once dirname(__FILE__) . '/unit_tester.php';
/**#@-*/

/**
 *    Standard unit test class for day to day testing
 *    of PHP code XP style. Adds some useful standard
 *    assertions.
 *    @package  SimpleTest
 *    @subpackage   UnitTester
 */
class UnitTestCaseDB extends UnitTestCase {
    //public $DB = 'foo';
    public $DB;

    function __construct($label = false) {
        parent::__construct($label);
        
        $this->DB = new PDO("mysql:host=127.0.0.1;dbname=eqreserve_test;port=3306",'eqr_tester','ELF71jollyP)rcuoak');    
    }
}
?>