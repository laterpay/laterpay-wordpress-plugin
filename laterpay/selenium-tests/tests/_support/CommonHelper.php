<?php

namespace Codeception\Module;

class CommonHelper extends \Codeception\Module {

    /**
     * For test development purposes. Runs DevPage::start()
     * @param \Tester $I
     * Usage examples:
      mod($I,'BackendModule','login');
      amOnPage(str_replace('{post}', 134, PostModule::$pagePostEdit));
      makeScreenshot(2);
     */
    public function mod($I, $module, $method) {

        $module::of($I)->$method($I);
    }

    /**
     * Helper to have ability if-then-else condition
     * @param \SetupTester $I
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
     * Helper to have ability if-then-else condition
     * @param \SetupTester $I
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
     * Helper to have ability non mandatory mouse click
     * @param \SetupTester $I
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
     * Helper to have ability non mandatory mouse click
     * @param \SetupTester $I
     * @param String $I
     */
    public function tryClick($I, $string) {

        try {

            $I->click($string);
        } catch (\PHPUnit_Framework_AssertionFailedError $f) {

        };
    }

    /**
     * Helper to have ability non mandatory mouse click
     * @param \SetupTester $I
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

    public function setVar($k, $v) {

        $this->k = $v;
    }

    public function getVar($k) {

        return $this->k;
    }

    public function setDomain($url = null) {

        if ($url) {

            $this->getModule('WebDriver')->_reconfigure(array('url' => $url));
        } else {

            $this->getModule('WebDriver')->_resetConfig();
        };
    }

}

