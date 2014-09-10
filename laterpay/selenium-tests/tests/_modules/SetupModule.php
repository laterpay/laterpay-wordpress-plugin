<?php

class SetupModule extends BaseModule {

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
    public static $globalDefaultPrice = '400';
    public static $globalDefaultCurrencyField = 'get_started[laterpay_currency]';
    public static $globalDefaultCurrency = 'EUR';
    //expected

    public static $assertPluginName = 'laterpay';
    public static $assertInstalled = 'Plugin installed successfully';
    public static $assertNoLaterPayApiKey = 'Please enter your LaterPay API key to activate LaterPay on this site.';
    public static $assertInvalidMerchantId = 'The Merchant ID you entered is not a valid LaterPay Sandbox Merchant ID!';
    public static $assertEmptyDemoMerchantId = 'Please enter your LaterPay API key to activate LaterPay on this site.';
    public static $assertInvalidDemoMerchantId = 'The API key you entered is not a valid LaterPay Sandbox API key!';
    public static $assertFieldStepOneDone = 'span[class="lp_step-1 lp_step-done"]';
    public static $pluginActivateSuccesRedirectUrl = '/wp-admin/post-new.php';
    //second
    public static $pluginPricingTab = 'a[text="Pricing"]';
    public static $adminMenuPluginButton = '#toplevel_page_laterpay-plugin';
    public static $pricingAddCategoryButton = '#add_category_button';
    public static $pricingCategorySelect = '#select2-drop-mask';
    public static $pricingSaveLink = ".edit-link .laterpay-save-link";
    public static $pricingCancelLink = ".edit-link .laterpay-cancel-link";
    public static $laterpayChangeLink = 'a[class="edit-link laterpay-change-link"]';
    public static $globalDefaultPriceField = '#lp_global-default-price';
    public static $laterpaySaveLink = '.laterpay-save-link';
    public static $laterpayCancelLink = '.laterpay-cancel-link';
    public static $globalPriceText = '#laterpay-global-price-text';
    public static $newGlobalDefaultPrice = '3';
    //expected
    public static $assertPluginListed = 'LaterPay';
    public static $assertNewPriceSet = 'Every post costs ';
    public static $assertNewPriceConfirmation = 'The global default price for all posts is ';
    public static $priceValidationArray = array(
        '0.15' => array('0,15', '0.15', '0,15 EUR', '0,15EUR'),
        '5.00' => array('0;89', '550', '8,00', '9.00', '10EUR', '10 EUR')
    );

    /**
     * Uninstall
     */
    public function uninstallPlugin() {

        $I = $this->BackendTester;

        $I->amOnPage(SetupModule::$url_plugin_list);
        if ($I->trySee($I, SetupModule::$assertPluginName)) {

            $I->amGoingTo('Remove plugin before install');
            $I->tryClick($I, SetupModule::$pluginDeactivateLink);
            $I->tryClick($I, SetupModule::$pluginDeleteLink);
            $I->tryClick($I, SetupModule::$pluginDeleteConfirmLink);
        };

        return $this;
    }

    /**
     * P.16-17
     * Installation {version}
     */
    public function installPlugin($version = null) {

        $I = $this->BackendTester;

        $I->amGoingTo('Install plugin');
        $I->amOnPage(SetupModule::$url_plugin_add);
        $I->amOnPage(SetupModule::$url_plugin_upload);
        $I->attachFile(SetupModule::$pluginUploadField, SetupModule::$pluginUploadFilename);
        $I->click(SetupModule::$pluginUploadSubmitField);
        $I->see(SetupModule::$assertInstalled);

        $I->amGoingTo('Check plugin listed');
        $I->amOnPage(SetupModule::$url_plugin_list);
        $I->see(SetupModule::$assertPluginListed);

        return $this;
    }

    /**
     * P.18
     * Can the user activate the plugin successfully?
     */
    public function activatePlugin() {

        $I = $this->BackendTester;

        $I->amGoingTo('Activate plugin');
        $I->amOnPage(SetupModule::$url_plugin_list);
        $I->click(SetupModule::$pluginActivateLink);
        $I->waitForElement(SetupModule::$pluginDeactivateLink);

        $I->amGoingTo('Plugin link into navigation tab');
        $I->amOnPage(SetupModule::$url_plugin_list);
        $I->see(SetupModule::$pluginNavigationLabel, SetupModule::$backNavigateTab);

        //floated popup
        if ($I->trySee($I, '.wp-pointer-content'))
            $I->tryClick($I, '.wp-pointer-content .close');
        return $this;
    }

    /**
     * P.18-20
     * Go through Get Started Tab {global default price, currency}
     * Can the user successfully complete the “Get Started”
     */
    public function goThroughGetStartedTab($price = null, $currency = null) {

        $I = $this->BackendTester;

        if (!$price)
            $price = SetupModule::$globalDefaultPrice;

        if (!$currency)
            $currency = SetupModule::$globalDefaultCurrency;

        $I->amGoingTo('Empty Merchant ID and API Key fields');
        $I->amOnPage(SetupModule::$pluginBackLink);
        $I->fillField(SetupModule::$laterpaySandboxMerchantField, '');
        $I->fillField(SetupModule::$laterpaySandboxApiKeyField, '');
        $I->click(SetupModule::$pluginActivateFormButton);
        $I->wait(BaseModule::$veryShortTimeout);
        $I->see(SetupModule::$assertNoLaterPayApiKey);

        $I->amGoingTo('Set wrong Merchant ID');
        $I->fillField(SetupModule::$laterpaySandboxMerchantField, SetupModule::$laterpaySandboxMerchantInvalidValue);
        $I->click(SetupModule::$pluginActivateFormButton);
        $I->wait(BaseModule::$veryShortTimeout);
        $I->see(SetupModule::$assertInvalidMerchantId);

        $I->amGoingTo('Set Sandbox Merchant ID');
        $I->fillField(SetupModule::$laterpaySandboxMerchantField, SetupModule::$laterpaySandboxMerchantSandboxValue);
        $I->click(SetupModule::$pluginActivateFormButton);
        $I->wait(BaseModule::$veryShortTimeout);
        $I->see(SetupModule::$assertEmptyDemoMerchantId);

        $I->amGoingTo('Set Sandbox Merchant ID and invalid API Key');
        $I->fillField(SetupModule::$laterpaySandboxMerchantField, SetupModule::$laterpaySandboxMerchantSandboxValue);
        $I->fillField(SetupModule::$laterpaySandboxApiKeyField, SetupModule::$laterpaySandboxApiKeyInvalidValue);
        $I->click(SetupModule::$pluginActivateFormButton);
        $I->wait(BaseModule::$veryShortTimeout);
        $I->seeInPageSource(SetupModule::$assertInvalidDemoMerchantId);

        $I->amGoingTo('Set Sandbox Merchant ID and valid API Key');
        $I->fillField(SetupModule::$laterpaySandboxMerchantField, SetupModule::$laterpaySandboxMerchantSandboxValue);
        $I->fillField(SetupModule::$laterpaySandboxApiKeyField, SetupModule::$laterpaySandboxApiKeyValidValue);
        //$I->seeElement(SetupModule::$assertFieldStepOneDone);

        $I->amGoingTo('Activate LaterPay');
        $I->fillField(SetupModule::$globalDefaultPriceField, $price);
        $I->selectOption(SetupModule::$globalDefaultCurrencyField, $currency);
        $I->click(SetupModule::$pluginActivateFormButton);

        $I->amGoingTo('Check redirect to New Post page');
        $I->wait(BaseModule::$shortTimeout);
        $I->seeCurrentUrlEquals(SetupModule::$pluginActivateSuccesRedirectUrl);

        return $this;
    }

    /**
     * P.21-22
     * Change Global Default Price {new global default price}
     * Can the user successfully change the global default price?
     */
    public function changeGlobalDefaultPrice($price = null) {

        $I = $this->BackendTester;

        if (!$price)
            $price = SetupModule::$newGlobalDefaultPrice;

        $I->amGoingTo('Click on the “Change” link next to the global default price');
        $I->amOnPage(SetupModule::$pluginBackLink);
        $I->click(SetupModule::$laterpayChangeLink);
        $I->seeElement(SetupModule::$globalDefaultPriceField);
        $I->seeElement(SetupModule::$laterpaySaveLink);
        $I->seeElement(SetupModule::$laterpayCancelLink);

        $I->amGoingTo('Price Validation');
        BackendModule::of($I)->validatePrice();

        $I->amGoingTo('Click on the “Cancel”');
        $I->amOnPage(SetupModule::$pluginBackLink);
        $I->click(SetupModule::$laterpayChangeLink);
        $I->click(SetupModule::$laterpayCancelLink);
        $I->seeElement(SetupModule::$laterpayChangeLink);
        $I->cantSeeElement(SetupModule::$laterpaySaveLink);
        $I->cantSeeElement(SetupModule::$laterpayCancelLink);

        $I->amGoingTo('Click on the “Change””');
        $I->amOnPage(SetupModule::$pluginBackLink);
        $I->click(SetupModule::$laterpayChangeLink);
        $I->cantSeeElement(SetupModule::$laterpayChangeLink);
        $I->seeElement(SetupModule::$laterpaySaveLink);
        $I->seeElement(SetupModule::$laterpayCancelLink);

        $I->amGoingTo('Set new price');
        $I->amOnPage(SetupModule::$pluginBackLink);
        $I->click(SetupModule::$laterpayChangeLink);
        $I->fillField(SetupModule::$globalDefaultPriceField, $price);
        $I->click(SetupModule::$laterpaySaveLink);
        $I->seeElement(SetupModule::$laterpayChangeLink);
        $I->cantSeeElement(SetupModule::$laterpaySaveLink);
        $I->cantSeeElement(SetupModule::$laterpayCancelLink);
        $I->see(SetupModule::$newGlobalDefaultPrice, SetupModule::$globalPriceText);
        $I->seeInPageSource(SetupModule::$assertNewPriceConfirmation . SetupModule::$newGlobalDefaultPrice);

        return $this;
    }

}

