<?php

class AuthModule extends BaseModule {
    public static $page = 'wp-admin/';

    //selectors
    public static $selectorUserName     = '#user_login';
    public static $selectorUserPassword = '#user_pass';
    public static $selectorSubmitButton = '#wp-submit';
    public static $selectorAccountMenu  = '#wp-admin-bar-my-account';
    public static $selectorLogoutButton = '#wp-admin-bar-logout';

    //defaults
    public static $c_url_test_system    = 'http://127.0.0.1/';
    public static $c_user               = 'admin';
    public static $c_password           = 'admin';

    /**
     * Login into system
     *
     * @param null|string $p_url_test_system
     * @param null|string $p_name
     * @param null|string $p_password
     *
     * @return $this
     */
    public function login( $p_url_test_system = null, $p_name = null, $p_password = null ) {
        $I = $this->BackendTester;

        // login url
        if ( ! isset( $p_url_test_system ) ) {
            $p_url_test_system = self::$c_url_test_system;
        }
        $p_url_test_system .= self::$page;
        // user name
        if ( ! isset( $p_name ) ) {
            $p_name = self::$c_user;
        }
        // password
        if ( ! isset( $p_password ) ) {
            $p_password = self::$c_password;
        }

        // login in backend
        $I->amOnPage( $p_url_test_system );
        $I->fillField( self::$selectorUserName, $p_name );
        $I->fillField( self::$selectorUserPassword, $p_password );
        $I->click( self::$selectorSubmitButton );

        return $this;
    }

    /**
     * Logout from system
     *
     * @return $this
     */
    public function logout() {
        $I = $this->BackendTester;

        // mouse over account menu and logout
        $I->moveMouseOver( self::$selectorAccountMenu );
        $I->click( self::$selectorLogoutButton );

        return $this;
    }
}
