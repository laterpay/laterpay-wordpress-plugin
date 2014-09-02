<?php

class LoginPage {

    //in
    public static $URL = '/wp-admin/';
    public static $usernameField = 'log';
    public static $passwordField = 'pwd';
    public static $usernameValue = 'admin';
    public static $passwordValue = 'password';
    public static $loginButton = 'wp-submit';
    //expected
    public static $expectedTitle = 'Dashboard';

    /**
     * Declare UI map for this page here. CSS or XPath allowed.
     * public static $usernameField = '#username';
     * public static $formSubmitButton = "#mainForm input[type=submit]";
     */

    /**
     * Basic route example for your current URL
     * You can append any additional parameter to URL
     * and use it in tests like: EditPage::route('/123-post');
     */
    public static function route($param) {
        return static::$URL . $param;
    }

    public static function login($I) {

        $I->amOnPage(self::$URL);
        $I->fillField(self::$usernameField, self::$usernameValue);
        $I->fillField(self::$passwordField, self::$passwordValue);
        $I->click(self::$loginButton);
        $I->see(self::$expectedTitle);
    }

}

