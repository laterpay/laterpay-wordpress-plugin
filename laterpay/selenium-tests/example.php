<?php
//Use composer to insall phpunit-selenium
require 'vendor/autoload.php';
define('BROWSERSTACK_USER', 'alexts1');
define('BROWSERSTACK_KEY', 'aHVAz8tkHya2v7FUyqHP');
class WebTest extends PHPUnit_Extensions_Selenium2TestCase
{
  public static $browsers = array(
    array(
      'browserName' => 'chrome',
      'host' => 'hub.browserstack.com',
      'port' => 80,
      'desiredCapabilities' => array(
        'version' => '30',
        'browserstack.user' => BROWSERSTACK_USER,
        'browserstack.key' => BROWSERSTACK_KEY,
        'os' => 'OS X',
        'os_version' => 'Mountain Lion'   
      )
    ),
    array(
      'browserName' => 'chrome',
      'host' => 'hub.browserstack.com',
      'port' => 80,
      'desiredCapabilities' => array(
        'version' => '30',
        'browserstack.user' => BROWSERSTACK_USER,
        'browserstack.key' => BROWSERSTACK_KEY,
        'os' => 'Windows',
        'os_version' => '8.1'   
      )
    )
    );   
    protected function setUp()
    {
        parent::setUp();
        $this->setBrowserUrl('http://www.example.com/');
    }
 
    public function testTitle()
    {
        $this->url('http://www.example.com/');
        $this->assertEquals('Example Domain', $this->title());
    }
    public function testGoogle()
    {
        $this->url('https://www.google.com/');
        $element = $this->byName('q');
        $element->click();
        $this->keys('Browserstack');
        $button = $this->byName('btnG');
        $button->click();
        $this->assertEquals('Browserstack - Google zoeken', $this->title());
    }
}
?>