<?php

class SetupPage {

    public static $url_plugin_list = '/wp-admin/plugins.php';
    public static $url_plugin_add = '/wp-admin/plugin-install.php';
    public static $url_plugin_upload = '/wp-admin/plugin-install.php?tab=upload';
    public static $pluginSearchField = 's';
    public static $pluginSearchForm = '.search-form';
    public static $pluginSearchValue = 'laterpay';
    public static $pluginUploadField = 'pluginzip';
    public static $pluginUploadFilename = 'laterpay.zip';
    public static $pluginUploadSubmitField = 'install-plugin-submit';
    public static $pluginDeactivateLink = '#laterpay .deactivate > a';
    public static $pluginDeleteLink = '#laterpay .delete > a';
    public static $pluginDeleteConfirmLink = '#submit';
    public static $pluginActivateLink = '#laterpay .activate > a';
    public static $pluginNavigationLabel = 'LaterPay';
    public static $backNavigateTab = '#adminmenuwrap';
    public static $pluginBackLink = '/wp-admin/admin.php?page=laterpay-plugin';
    public static $laterpaySandboxMerchantField = 'get_started[laterpay_sandbox_merchant_id]';
    public static $laterpaySandboxMerchantInvalidValue = 'a1b2c3d4e5f6g7h8i9j0';
    public static $laterpaySandboxMerchantSandboxValue = 'LaterPay-WordPressDemo';
    public static $laterpaySandboxApiKeyField = 'get_started[laterpay_sandbox_api_key]';
    public static $laterpaySandboxApiKeyInitValue = 'a1b2c3d4e5f6g7h8i9j0';
    public static $laterpaySandboxApiKeyInvalidValue = 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5';
    public static $laterpaySandboxApiKeyValidValue = 'decafbaddecafbaddecafbaddecafbad';
    public static $pluginActivateFormButton = '.lp_activate-plugin-button';
    public static $globalDefaultPriceField = 'get_started[laterpay_global_price]';
    public static $globalDefaultPrice = '400';
    public static $globalDefaultCurrencyField = 'get_started[laterpay_currency]';
    public static $globalDefaultCurrency = 'EUR';
    public static $pluginActivationDelay = 10;
    //expected
    public static $assertPluginName = 'laterpay';
    public static $assertInstalled = 'Plugin installed successfully';
    public static $assertPluginListed = 'LaterPay';
    public static $assertNoLaterPayApiKey = 'Please enter your LaterPay API key to activate LaterPay on this site.';
    public static $assertInvalidMerchantId = 'The Merchant ID you entered is not a valid LaterPay Sandbox Merchant ID!';
    public static $assertEmptyDemoMerchantId = 'Please enter your LaterPay API key to activate LaterPay on this site.';
    public static $assertInvalidDemoMerchantId = 'The API key you entered is not a valid LaterPay Sandbox API key!';
    public static $assertFieldStepOneDone = 'span[class="lp_step-1 lp_step-done"]';
    public static $pluginActivateSuccesRedirectUrl = '/wp-admin/post-new.php';

    //public static $assertInvalidMerchantId = 'The API key you entered is not a valid LaterPay Sandbox API key!';

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

