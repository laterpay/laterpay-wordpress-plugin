<?php

namespace Codeception\Module;

class CommonHelper extends \Codeception\Module {

    /**
     * @param \SetupTester $I
     */
    public function hLogin($I) {

        \LoginPage::login($I);
    }

    /**
     * @param \SetupTester $I
     * @param String $I
     */
    public function hSee($I, $string) {

        try {

            $I->see($string);
            return true;
        } catch (\PHPUnit_Framework_AssertionFailedError $f) {

            return false;
        };
    }

    /**
     * @param \SetupTester $I
     * @param String $I
     */
    public function hClick($I, $string) {

        try {

            $I->click($string);
        } catch (\PHPUnit_Framework_AssertionFailedError $f) {

        };
    }

    /**
     * @param String $I
     * @param String $I
     */
    public function _failed(\Codeception\TestCase $test, $fail) {

        file_put_contents(\Codeception\Configuration::logDir() . basename($test->getFileName()) . '.page.debug.html', $fail);
    }

    /**
     * @param String $I
     */
    public function log($message) {

        file_put_contents(\Codeception\Configuration::logDir() . 'debug.html', $message);
    }

}

