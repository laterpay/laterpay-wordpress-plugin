<?php

class LocalTest extends PHPUnit_Extensions_Selenium2TestCase {

    const host_front = 'http://src.wordpress-develop.dev/';
    const host_back = 'http://src.wordpress-develop.dev/wp-admin/';

    protected $captureScreenshotOnFailure = TRUE;
    protected $screenshotPath = '/vagrant/laterpay-wordpress-plugin/laterpay/selenium-tests/img/';

    protected function setUp() {
        $this->setBrowser('firefox');
        $this->setBrowserUrl(self::host_front);
    }

    public function testTitle() {
        $this->url(self::host_front);
        $this->assertEquals('Example WWW Page', $this->title());
        echo $this->title();
    }

    public function testBtn() {
        $this->url(self::host_front);
        $this->assertEquals('Example WWW Page', $this->title());
    }

}

?>