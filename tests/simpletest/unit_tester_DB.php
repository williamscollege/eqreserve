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

require_once dirname(__FILE__).'/../../institution.cfg.php';

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

        $this->DB = new PDO("mysql:host=".DB_SERVER.";dbname=".DB_NAME.";port=3306",DB_USER,DB_PASS);    

    }
}
?>