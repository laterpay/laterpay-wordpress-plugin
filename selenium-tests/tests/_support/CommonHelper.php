<?php

namespace Codeception\Module;

class CommonHelper extends \Codeception\Module {

    /**
     * For test development purposes.
     * Usage examples:
     * mod($I,'BackendModule','login');
     * amOnPage(str_replace('{post}', 134, PostModule::$pagePostEdit));
     * makeScreenshot(2);
     * @param \BackendTester $I
     */
    public function mod($I, $module, $method) {

        $module::of($I)->$method($I);
    }

    /**
     * Helper to have ability if-then-else condition for seeInField method
     * @param \BackendTester $I
     * @param String $I
     */
    public function trySeeInField($I, $string, $value) {

        try {

            $I->seeInField($string, $value);
            return true;
        } catch (\PHPUnit_Framework_AssertionFailedError $f) {

            return false;
        };
    }

    /**
     * Helper to have ability if-then-else condition for see method
     * @param \BackendTester $I
     * @param String $I
     */
    public function trySee($I, $string) {

        try {

            $I->see($string);
            return true;
        } catch (\PHPUnit_Framework_AssertionFailedError $f) {

            return false;
        };
    }

    /**
     * Helper to have ability non mandatory mouse click for seeOptionIsSelected method
     * @param \BackendTester $I
     * @param String $I
     */
    public function tryOption($I, $select, $option) {

        try {

            $I->seeOptionIsSelected($select, $option);
            return true;
        } catch (\PHPUnit_Framework_AssertionFailedError $f) {

            return false;
        };

        return true;
    }

    /**
     * Helper to have ability non mandatory mouse click for click method
     * @param \BackendTester $I
     * @param String $I
     */
    public function tryClick($I, $string) {

        try {

            $I->click($string);
        } catch (\PHPUnit_Framework_AssertionFailedError $f) {

        };
    }

    /**
     * Helper to have ability non mandatory mouse click for seeCheckboxIsChecked method
     * @param \BackendTester $I
     * @param String $I
     */
    public function tryCheckbox($I, $string) {

        try {

            $I->seeCheckboxIsChecked($string);
            return true;
        } catch (\PHPUnit_Framework_AssertionFailedError $f) {

            return false;
        };
    }

    /**
     * set webdriver domain
     * @param null $url
     * @return void
     */
    public function setDomain($url = null) {

        if ($url) {

            $this->getModule('WebDriver')->_reconfigure(array('url' => $url));
        } else {

            $this->getModule('WebDriver')->_resetConfig();
        };
    }

    /**
     * Save variable
     * @param string $url
     * @param mixed $url
     * @return void
     */
    public function setVar($k, $v) {

        $this->k = $v;
    }

    /**
     * Get variable
     * @param null $url
     * @return mixed
     */
    public function getVar($k) {

        return $this->k;
    }

}

