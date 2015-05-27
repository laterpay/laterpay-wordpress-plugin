<?php

class AuthModule extends BaseModule {
    //links
    public static $linkAdminArea        = 'wp-admin/';

    //selectors
    public static $selectorUserName     = '#user_login';
    public static $selectorUserPassword = '#user_pass';
    public static $selectorSubmitButton = '#wp-submit';
    public static $selectorAccountMenu  = '#wp-admin-bar-my-account';
    public static $selectorLogoutButton = '#wp-admin-bar-logout';
    public static $selectorLoginForm    = '#loginform';
    public static $selectorAdminBar     = '#wpadminbar';

    //defaults
    public static $c_url_test_system    = 'http://127.0.0.1/';
    public static $c_user               = 'admin';
    public static $c_password           = 'admin';

    /**
     * Login into system
     *
     * @param null|string $url_test_system
     * @param null|string $name
     * @param null|string $password
     *
     * @return $this
     */
    public function login( $url_test_system = null, $name = null, $password = null ) {
        $I = $this->BackendTester;

        // login url
        if ( ! isset( $url_test_system ) ) {
            $url_test_system = self::$c_url_test_system;
        }
        $url_test_system .= self::$linkAdminArea;
        // user name
        if ( ! isset( $name ) ) {
            $name = self::$c_user;
        }
        // password
        if ( ! isset( $password ) ) {
            $password = self::$c_password;
        }

        // login in backend
        $I->amOnPage( $url_test_system );
        $I->fillField( self::$selectorUserName, $name );
        $I->fillField( self::$selectorUserPassword, $password );
        $I->click( self::$selectorSubmitButton );
        $I->waitForElement( self::$selectorAdminBar );

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
        $I->waitForElement( self::$selectorLoginForm );

        return $this;
    }
}
