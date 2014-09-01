<?php

class PluginPage {

    public static $url_plugins = '/wp-admin/plugins.php';
    public static $url_plugin_add = '/wp-admin/plugin-install.php';
    public static $pluginSearchField = 's';
    public static $pluginSearchForm = '.search-form';
    public static $pluginSearchValue = 'laterpay';
    //expected
    public static $expectedModule = 'laterpay';

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

