<?php

class IndexPageLoadTest extends WebTestCase {
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
