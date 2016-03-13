<?php

namespace Codeception\Module;

class CommonHelper extends \Codeception\Module {

    /**
     * @var array
     */
    public $var = array();

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

    /**
     * Helper to have ability if-then-else condition for see method
     *
     * @param  \BackendTester $I
     * @param  string         $selector
     * @return bool
     */
    public function trySeeElement( $I, $selector ) {
        try {
            $I->seeElement( $selector );
            return true;
        } catch ( \PHPUnit_Framework_AssertionFailedError $e ) {
            return false;
        }
    }
}

