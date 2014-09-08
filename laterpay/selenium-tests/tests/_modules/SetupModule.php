<?php

class SetupModule extends BaseModule {

    /**
     * Uninstall
     */
    public function uninstallPlugin() {

        $I = $this->BackendTester;

        $I->amOnPage(SetupPage::$url_plugin_list);
        if ($I->hSee($I, SetupPage::$assertPluginName)) {

            $I->amGoingTo('Remove plugin before install');
            $I->hClick($I, SetupPage::$pluginDeactivateLink);
            $I->hClick($I, SetupPage::$pluginDeleteLink);
            $I->hClick($I, SetupPage::$pluginDeleteConfirmLink);
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
        $I->amOnPage(SetupPage::$url_plugin_add);
        $I->amOnPage(SetupPage::$url_plugin_upload);
        $I->attachFile(SetupPage::$pluginUploadField, SetupPage::$pluginUploadFilename);
        $I->click(SetupPage::$pluginUploadSubmitField);
        $I->see(SetupPage::$assertInstalled);

        $I->amGoingTo('Check plugin listed');
        $I->amOnPage(SetupPage::$url_plugin_list);
        $I->see(SetupPage::$assertPluginListed);

        return $this;
    }

    /**
     * P.18
     * Can the user activate the plugin successfully?
     */
    public function activatePlugin() {

        $I = $this->BackendTester;

        $I->amGoingTo('Activate plugin');
        $I->amOnPage(SetupPage::$url_plugin_list);
        $I->click(SetupPage::$pluginActivateLink);
        $I->waitForElement(SetupPage::$pluginDeactivateLink);

        $I->amGoingTo('Plugin link into navigation tab');
        $I->amOnPage(SetupPage::$url_plugin_list);
        $I->see(SetupPage::$pluginNavigationLabel, SetupPage::$backNavigateTab);

        //floated popup
        if ($I->hSee('.wp-pointer-content'))
            $I->hClick('.wp-pointer-content .close');
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
            $price = SetupPage::$globalDefaultPrice;

        if (!$currency)
            $currency = SetupPage::$globalDefaultCurrency;

        $I->amGoingTo('Set empty Merchant ID and API Key');
        $I->amOnPage(SetupPage::$pluginBackLink);
        $I->fillField(SetupPage::$laterpaySandboxMerchantField, '');
        $I->fillField(SetupPage::$laterpaySandboxApiKeyField, '');
        $I->click(SetupPage::$pluginActivateFormButton);
        $I->see(SetupPage::$assertNoLaterPayApiKey);

        $I->amGoingTo('Set empty Merchant ID and API Key');
        $I->amOnPage(SetupPage::$pluginBackLink);
        $I->fillField(SetupPage::$laterpaySandboxMerchantField, SetupPage::$laterpaySandboxMerchantInvalidValue);
        $I->click(SetupPage::$pluginActivateFormButton);
        $I->see(SetupPage::$assertNoLaterPayApiKey);

        $I->amGoingTo('Set Sandbox Merchant ID and empty API Key');
        $I->amOnPage(SetupPage::$pluginBackLink);
        $I->fillField(SetupPage::$laterpaySandboxMerchantField, SetupPage::$laterpaySandboxMerchantSandboxValue);
        $I->fillField(SetupPage::$laterpaySandboxApiKeyField, '');
        $I->click(SetupPage::$pluginActivateFormButton);
        $I->see(SetupPage::$assertEmptyDemoMerchantId);

        $I->amGoingTo('Set Sandbox Merchant ID and invalid API Key');
        $I->amOnPage(SetupPage::$pluginBackLink);
        $I->fillField(SetupPage::$laterpaySandboxMerchantField, SetupPage::$laterpaySandboxMerchantSandboxValue);
        $I->fillField(SetupPage::$laterpaySandboxApiKeyField, SetupPage::$laterpaySandboxApiKeyInvalidValue);
        $I->click(SetupPage::$pluginActivateFormButton);
        $I->seeInPageSource(SetupPage::$assertInvalidDemoMerchantId);

        $I->amGoingTo('Set Sandbox Merchant ID and valid API Key');
        $I->fillField(SetupPage::$laterpaySandboxMerchantField, SetupPage::$laterpaySandboxMerchantSandboxValue);
        $I->fillField(SetupPage::$laterpaySandboxApiKeyField, SetupPage::$laterpaySandboxApiKeyValidValue);
        //$I->seeElement(SetupPage::$assertFieldStepOneDone);

        $I->amGoingTo('Activate LaterPay');
        $I->fillField(SetupPage::$globalDefaultPriceField, $price);
        $I->selectOption(SetupPage::$globalDefaultCurrencyField, $currency);
        $I->click(SetupPage::$pluginActivateFormButton);

        $I->amGoingTo('Check redirect to New Post page');
        $I->wait(SetupPage::$pluginActivationDelay);
        $I->seeCurrentUrlEquals(SetupPage::$pluginActivateSuccesRedirectUrl);

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
            $price = PluginPage::$newGlobalDefaultPrice;

        $I->amGoingTo('Click on the “Change” link next to the global default price');
        $I->amOnPage(PluginPage::$pluginBackLink);
        $I->click(PluginPage::$laterpayChangeLink);
        $I->seeElement(PluginPage::$globalDefaultPriceField);
        $I->seeElement(PluginPage::$laterpaySaveLink);
        $I->seeElement(PluginPage::$laterpayCancelLink);

        $I->amGoingTo('Price Validation');
        BackendModule::of($I)->priceValidation();

        $I->amGoingTo('Click on the “Cancel”');
        $I->amOnPage(PluginPage::$pluginBackLink);
        $I->click(PluginPage::$laterpayChangeLink);
        $I->click(PluginPage::$laterpayCancelLink);
        $I->seeElement(PluginPage::$laterpayChangeLink);
        $I->cantSeeElement(PluginPage::$laterpaySaveLink);
        $I->cantSeeElement(PluginPage::$laterpayCancelLink);

        $I->amGoingTo('Click on the “Change””');
        $I->amOnPage(PluginPage::$pluginBackLink);
        $I->click(PluginPage::$laterpayChangeLink);
        $I->cantSeeElement(PluginPage::$laterpayChangeLink);
        $I->seeElement(PluginPage::$laterpaySaveLink);
        $I->seeElement(PluginPage::$laterpayCancelLink);

        $I->amGoingTo('Set new price');
        $I->amOnPage(PluginPage::$pluginBackLink);
        $I->click(PluginPage::$laterpayChangeLink);
        $I->fillField(PluginPage::$globalDefaultPriceField, $price);
        $I->click(PluginPage::$laterpaySaveLink);
        $I->seeElement(PluginPage::$laterpayChangeLink);
        $I->cantSeeElement(PluginPage::$laterpaySaveLink);
        $I->cantSeeElement(PluginPage::$laterpayCancelLink);
        $I->see(PluginPage::$newGlobalDefaultPrice, PluginPage::$globalPriceText);
        $I->seeInPageSource(PluginPage::$assertNewPriceConfirmation . PluginPage::$newGlobalDefaultPrice);

        return $this;
    }

}

