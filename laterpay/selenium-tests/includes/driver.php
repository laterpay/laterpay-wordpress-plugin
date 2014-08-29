<?php

class Driver extends PHPUnit_Extensions_Selenium2TestCase {

    public static $browsers = array();

    protected function setUp() {

        /*
          $browsers = getenv('browsers');
          if (!empty($browsers)) {

          $browsers = explode(',', $browsers);
          foreach ($browsers as $browser)
          if (!empty($browser))
          self::$browsers[] = array(
          'name' => $browser,
          'browser' => $browser,
          );
          };
         */

        if ($this->getBrowser() == '')
            $this->setBrowser(SELENIUM_BROWSER);

        if ($this->getBrowserUrl() == '')
            $this->setBrowserUrl(wp_front);

        $this->shareSession(true);

        parent::setUp();
    }

    protected function tearDown() {

        $status = $this->getStatus();

        if ($status == \PHPUnit_Runner_BaseTestRunner::STATUS_ERROR || $status == \PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE)
            $this->takeSnapshot();

        parent::tearDown(__METHOD__);
    }

    protected function takeSnapshot($name = '') {

        $name = preg_replace("/[^a-zA-Z0-9]+/", "_", trim($name));

        if (empty($name))
            $name = 'Snapshot';

        $browser = $this->getBrowser();

        $name = $name . '_' . $browser . '_' . date('His');

        $file = phpunit_snapshot_path . $name . '.png';

        try {

            $img = $this->currentScreenshot();

            file_put_contents($file, $img);
        } catch (Exception $e) {

        };
    }

}

