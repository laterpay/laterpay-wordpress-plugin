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
    public static $globalDefaultCurrencySelect = '#lp_currency-select';
    public static $globalDefaultCurrency = 'EUR';
    //expected
    public static $linkDismissWPMessage = '.wp-pointer-content .close';
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
    public static $laterpayChangeLink = 'Change';
    public static $globalDefaultPriceField = '#lp_global-default-price';
    public static $laterpaySaveLink = 'Save';
    public static $laterpayCancelLink = 'Cancel';
    public static $globalPriceText = '#lp_global-price-text';
    //expected
    public static $assertPluginListed = 'LaterPay';
    public static $assertNewPriceSet = 'Every post costs ';
    public static $assertNewPriceConfirmation = 'The global default price for all posts is ';
    public static $assertFreePriceConfirmation = 'All posts are free by default now.';
    public static $priceValidationArray = array(
        '0.15' => array('0,15', '0.15', '0,15 EUR', '0,15EUR'),
        '5.00' => array('0;89', '550', '8,00', '9.00', '10EUR', '10 EUR')
    );
    public static $assertCurrencySelected = 'The currency for this website is {currency} now.';

    /**
     * Uninstall Laterpay plugin
     * @return $this
     */
    public function uninstallPlugin() {

        $I = $this->BackendTester;

        $I->amOnPage(SetupModule::$url_plugin_list);
        if ($I->trySee($I, SetupModule::$assertPluginName)) {

            //Remove plugin before install
            $I->tryClick($I, SetupModule::$pluginDeactivateLink);
            $I->tryClick($I, SetupModule::$pluginDeleteLink);
            $I->tryClick($I, SetupModule::$pluginDeleteConfirmLink);
        };

        return $this;
    }

    /**
     * Install Laterpay plugin
     * @param null $version
     * @return $this
     */
    public function installPlugin($version = null) {

        $I = $this->BackendTester;

        //Install plugin
        $I->amOnPage(SetupModule::$url_plugin_add);
        $I->amOnPage(SetupModule::$url_plugin_upload);
        $I->attachFile(SetupModule::$pluginUploadField, SetupModule::$pluginUploadFilename);
        $I->click(SetupModule::$pluginUploadSubmitField);
        $I->see(SetupModule::$assertInstalled);

        //Check plugin listed
        $I->amOnPage(SetupModule::$url_plugin_list);
        $I->see(SetupModule::$assertPluginListed);

        return $this;
    }

    /**
     * Activate Laterpay plugin
     * @return $this
     */
    public function activatePlugin() {

        $I = $this->BackendTester;

        //Activate plugin
        $I->amOnPage(SetupModule::$url_plugin_list);
        $I->click(SetupModule::$pluginActivateLink);
        $I->waitForElement(SetupModule::$pluginDeactivateLink);

        //Plugin link into navigation tab
        $I->amOnPage(SetupModule::$url_plugin_list);
        $I->see(SetupModule::$pluginNavigationLabel, SetupModule::$backNavigateTab);

        //floated popup
        if ($I->trySee($I, '.wp-pointer-content'))
            $I->tryClick($I, '.wp-pointer-content .close');

        return $this;
    }

    /**
     * Go through Get Started Tab {global default price, currency}
     * Can the user successfully complete the “Get Started”
     * @param null $price
     * @param null $currency
     * @return $this
     */
    public function goThroughGetStartedTab($price = null, $currency = null) {

        $I = $this->BackendTester;

        if (!$price)
            $price = SetupModule::$globalDefaultPrice;

        if (!$currency)
            $currency = SetupModule::$globalDefaultCurrency;

        //Empty Merchant ID and API Key fields
        $I->amOnPage(SetupModule::$pluginBackLink);
        $I->tryClick($I, SetupModule::$linkDismissWPMessage);
        $I->fillField(SetupModule::$laterpaySandboxMerchantField, '');
        $I->fillField(SetupModule::$laterpaySandboxApiKeyField, '');
        $I->click(SetupModule::$pluginActivateFormButton);
        $I->wait(BaseModule::$veryShortTimeout);
        $I->see(SetupModule::$assertNoLaterPayApiKey);

        //Set wrong Merchant ID
        $I->fillField(SetupModule::$laterpaySandboxMerchantField, SetupModule::$laterpaySandboxMerchantInvalidValue);
        $I->click(SetupModule::$pluginActivateFormButton);
        $I->wait(BaseModule::$veryShortTimeout);
        $I->see(SetupModule::$assertInvalidMerchantId);

        //Set Sandbox Merchant ID
        $I->fillField(SetupModule::$laterpaySandboxMerchantField, SetupModule::$laterpaySandboxMerchantSandboxValue);
        $I->click(SetupModule::$pluginActivateFormButton);
        $I->wait(BaseModule::$veryShortTimeout);
        $I->see(SetupModule::$assertEmptyDemoMerchantId);

        //Set Sandbox Merchant ID and invalid API Key
        $I->fillField(SetupModule::$laterpaySandboxMerchantField, SetupModule::$laterpaySandboxMerchantSandboxValue);
        $I->fillField(SetupModule::$laterpaySandboxApiKeyField, SetupModule::$laterpaySandboxApiKeyInvalidValue);
        $I->click(SetupModule::$pluginActivateFormButton);
        $I->wait(BaseModule::$veryShortTimeout);
        $I->seeInPageSource(SetupModule::$assertInvalidDemoMerchantId);

        //Set Sandbox Merchant ID and valid API Key
        $I->fillField(SetupModule::$laterpaySandboxMerchantField, SetupModule::$laterpaySandboxMerchantSandboxValue);
        $I->fillField(SetupModule::$laterpaySandboxApiKeyField, SetupModule::$laterpaySandboxApiKeyValidValue);
        //$I->seeElement(SetupModule::$assertFieldStepOneDone);

        //Activate LaterPay
        $I->fillField(SetupModule::$globalDefaultPriceField, $price);
        $I->selectOption(SetupModule::$globalDefaultCurrencyField, $currency);
        $I->click(SetupModule::$pluginActivateFormButton);

        //Check redirect to New Post page
        $I->wait(BaseModule::$shortTimeout);
        $I->seeCurrentUrlEquals(SetupModule::$pluginActivateSuccesRedirectUrl);

        return $this;
    }

    /**
     * Change Global Default Price {new global default price}
     * Can the user successfully change the global default price?
     * @param null $price
     * @return $this
     */
    public function changeGlobalDefaultPrice($price = null) {

        $I = $this->BackendTester;

        //Click on the “Change” link next to the global default price
        $I->amOnPage(SetupModule::$pluginBackLink);
        $I->click(SetupModule::$laterpayChangeLink);
        $I->seeElement(SetupModule::$globalDefaultPriceField);
        $I->seeLink(SetupModule::$laterpaySaveLink);
        $I->seeLink(SetupModule::$laterpayCancelLink);

        //Click on the “Cancel”
        $I->amOnPage(SetupModule::$pluginBackLink);
        $I->click(SetupModule::$laterpayChangeLink);
        $I->click(SetupModule::$laterpayCancelLink);
        $I->seeLink(SetupModule::$laterpayChangeLink);
        $I->cantSeeLink(SetupModule::$laterpaySaveLink);
        $I->cantSeeLink(SetupModule::$laterpayCancelLink);

        //Click on the “Change”
        $I->amOnPage(SetupModule::$pluginBackLink);
        $I->click(SetupModule::$laterpayChangeLink);
        $I->cantSeeLink(SetupModule::$laterpayChangeLink);
        $I->seeLink(SetupModule::$laterpaySaveLink);
        $I->seeLink(SetupModule::$laterpayCancelLink);

        //Set new price
        $I->amOnPage(SetupModule::$pluginBackLink);
        $I->click(SetupModule::$laterpayChangeLink);
        $I->fillField(SetupModule::$globalDefaultPriceField, $price);
        $I->click(SetupModule::$laterpaySaveLink);
        $I->seeLink(SetupModule::$laterpayChangeLink);
        $I->cantSeeLink(SetupModule::$laterpaySaveLink);
        $I->cantSeeLink(SetupModule::$laterpayCancelLink);
        $I->see($price, SetupModule::$globalPriceText);

        if ($price == '0.00')
            $I->seeInPageSource(SetupModule::$assertFreePriceConfirmation);
        else
            $I->seeInPageSource(SetupModule::$assertNewPriceConfirmation . $price);

        return $this;
    }

    /**
     * Change Currency {new currency}
     * {currency} = string
     * @param string $currency
     * @return $this
     */
    public function changeCurrency($currency = 'USD') {

        $I = $this->BackendTester;

        //Change Currency

        $I->amOnPage(SetupModule::$pluginBackLink);

        if (!$I->tryOption($I, SetupModule::$globalDefaultCurrencySelect, $currency)) {

            $I->selectOption(SetupModule::$globalDefaultCurrencySelect, $currency);
            $I->wait(BaseModule::$veryShortTimeout);
            $I->seeInPageSource(str_replace('{currency}', $currency, SetupModule::$assertCurrencySelected));
            $I->seeOptionIsSelected(SetupModule::$globalDefaultCurrencySelect, $currency);
        };

        return $this;
    }

    /**
     * Validate Global Price
     * @return $this
     */
    public function validateGlobalPrice() {
        $I = $this->BackendTester;

        $I->amOnPage(SetupModule::$pluginBackLink);

        //Validate global price
        BackendModule::of($I)->validatePrice(SetupModule::$globalDefaultPriceField, SetupModule::$laterpayChangeLink, SetupModule::$laterpaySaveLink);

        return $this;
    }

}

