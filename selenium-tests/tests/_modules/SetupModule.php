<?php

class SetupModule extends BaseModule {
    //defaults
    public static $c_current_plugin_version  = '0.9.14';
    public static $c_previous_plugin_version = '0.9.13';


    /**
     * Uninstall Laterpay plugin
     * @return $this
     */
    public function uninstallPlugin() {

        $I = $this->BackendTester;

        $I->amOnPage(SetupModule::$url_plugin_list);
        $I->see($I, SetupModule::$assertPluginName);
        //Remove plugin before install
        $I->click(SetupModule::$pluginDeactivateLink);
        $I->click(SetupModule::$pluginDeleteLink);
        $I->click(SetupModule::$pluginDeleteConfirmLink);

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
        $I->see($I, '.wp-pointer-content');
        $I->click('.wp-pointer-content .close');

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
        $I->click(SetupModule::$linkDismissWPMessage);
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
        $I->selectOption(SetupModule::$globalDefaultCurrencySelect, $currency);
        $I->wait(BaseModule::$veryShortTimeout);
        $I->seeInPageSource(str_replace('{currency}', $currency, SetupModule::$assertCurrencySelected));
        $I->seeOptionIsSelected(SetupModule::$globalDefaultCurrencySelect, $currency);

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

