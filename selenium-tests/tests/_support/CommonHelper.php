<?php

namespace Codeception\Module;

class CommonHelper extends \Codeception\Module {

    /**
     * @var array
     */
    public $var = array();

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
     * set webdriver domain
     * @param null $url
     * @return void
     */
    public function setDomain( $url = null ) {
        if ( $url ) {
            $this->getModule( 'WebDriver' )->_reconfigure( array( 'url' => $url ) );
        } else {
            $this->getModule( 'WebDriver' )->_resetConfig();
        };
    }

    /**
     * Helper to have ability non mandatory mouse click for seeOptionIsSelected method
     * @param \BackendTester $I
     * @param string         $select
     * @param string         $option
     */
    public function tryOption($I, $select, $option) {
        try {
            $I->seeOptionIsSelected($select, $option);
        } catch (\PHPUnit_Framework_AssertionFailedError $f) {
            return false;
        }

        return true;
    }

    /**
     * Save variable
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return void
     */
    public function setVar( $key, $value ) {
        $this->var[ $key ] = $value;
    }

    /**
     * Get variable
     *
     * @param  mixed $key
     *
     * @return mixed
     */
    public function getVar( $key ) {
        return $this->var[ $key ];
    }
}

