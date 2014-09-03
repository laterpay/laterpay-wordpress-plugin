<?php

namespace Codeception\Module;

class CommonHelper extends \Codeception\Module {

    /**
     * For test development purposes. Runs DevPage::start()
     * @param \Tester $I
     */
    public function hDev($I) {

        \DevPage::start($I);
    }

    /**
     * Login to backend
     * @param \Tester $I
     */
    public function hLogin($I) {

        \LoginPage::Login($I);
    }

    /**
     * Logout from backend
     * @param \Tester $I
     */
    public function hLogout($I) {

        \LoginPage::Logout($I);
    }

    /**
     * Remove, Install, activate and configure LaterPay plugin in wordpress throught SetupPage::reinstall
     * @param \Tester $I
     */
    public function hReinstall($I) {

        try {

            \SetupPage::Reinstall($I);
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
    public function hSee($I, $string) {

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

