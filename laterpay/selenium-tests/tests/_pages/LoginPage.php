<?php

class LoginPage {

    //in
    public static $URL = '/wp-admin/';
    public static $usernameField = 'log';
    public static $passwordField = 'pwd';
    public static $usernameValue = 'admin';
    public static $passwordValue = 'password';
    public static $loginButton = 'wp-submit';
    public static $logoutMenu = '#wp-admin-bar-my-account';
    public static $logoutButton = '#wp-admin-bar-logout>a';
    //expected
    public static $expectedBackTitle = 'Dashboard';

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

}

