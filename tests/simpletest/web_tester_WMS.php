<?php
/**
 *  base include file for WebTest
 *  @package    SimpleTest
 *  @subpackage WebUnitTester
 *  @version    $Id: unit_tester.php 1882 2009-07-01 14:30:05Z lastcraft $
 */

/**#@+
 *  include other SimpleTest class files
 */
require_once dirname(__FILE__) . '/web_tester.php';
/**#@-*/

/**
 *    Standard unit test class for day to day testing
 *    of PHP code XP style. Adds some useful standard
 *    assertions.
 *    WMS extension adds some handle assertions
 *    @package  SimpleTest
 *    @subpackage   WebTester
 */
abstract class WebTestCaseWMS extends WebTestCase {

    function __construct($label = false) {
        parent::__construct($label);
    }

    function assertEltByIdHasAttrOfValue($eltId,$attrName,$attrValueExpected = true) {
        $matches = array();
        $haystack = $this->getBrowser()->getContent();

//        preg_match('/(\<[^\>]\s+id\s*=\s*"'.$eltId.'"\s+[^\>]*\>)/',$this->getBrowser()->getContent(),$matches);
        preg_match('/(\<[^\>]*\s+id\s*=\s*"'.$eltId.'"\s+[^\>]*\>)/',$haystack,$matches);

        //echo $matches[1];
        
        if (! $this->assertTrue(isset($matches[1]),"Element with id [$eltId] should exist")) {
            return false;
        }

        $haystack = $matches[1];
        $matches = array();
        preg_match('/\s+('.$attrName.')\s*=\s*"([^"]*)"/',$haystack,$matches);
        if (! $this->assertTrue(isset($matches[1]) && isset($matches[2]),"Element with id [$eltId] should have attribute of [$attrName]")) {
            return false;
        }

        if ($attrValueExpected === true) {
            return true;
        }

        if (! SimpleExpectation::isExpectation($attrValueExpected)) {
            $attrValueExpected = new IdenticalExpectation($attrValueExpected);
        }
        $haystack = $matches[2];
        if ($attrValueExpected->test($haystack)) {
            return true;
        }

        return $this->assert($attrValueExpected, $haystack, "Element with id [$eltId] attribute [$attrName] value does not match- ".$attrValueExpected->testMessage($haystack));
    }
}
?>

