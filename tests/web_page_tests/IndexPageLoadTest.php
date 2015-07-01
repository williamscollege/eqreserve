<?php
require_once dirname(__FILE__) . '/../simpletest/WMS_web_tester.php';

class IndexPageLoadTest extends WMSWebTestCase {
    function testIndexPageLoad() {
//        ERROR - Expecting response in [200] got [404]
        //cannot find index.php
        $this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/');
//        util_prePrintR($this);
//        exit;

        $this->assertResponse(200);
    }

    function testIndexPageLoadsErrorAndWarningFree() {
        $this->get('http://localhost'.LOCAL_WEBSERVER_PORT_SPEC.'/eqreserve/');
        $this->assertNoPattern('/error/i');
        $this->assertNoPattern('/warning/i');
        $this->assertTitle(new PatternExpectation('/'.LANG_APP_NAME.': /'));
    }
}
?>