<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class IndexPageLoadTest extends WMSWebTestCase {
    function testIndexPageLoad() {
        $this->get('http://localhost/eqreserve/');
        $this->assertResponse(200);
    }

    function testIndexPageLoadsErrorAndWarningFree() {
        $this->get('http://localhost/eqreserve/');
        $this->assertNoPattern('/error/i');
        $this->assertNoPattern('/warning/i');
        $this->assertTitle(new PatternExpectation('/'.LANG_APP_NAME.': /'));
    }
}
