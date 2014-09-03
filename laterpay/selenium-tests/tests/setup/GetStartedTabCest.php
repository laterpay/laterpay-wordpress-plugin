<?php

use \SetupTester;

/**
 * C1 - Go through Get Started Tab
 * @group GetStartedTab
 */
class GetStartedTabCest {

    /**
     * @param \SetupTester $I
     * @group GetStartedTab
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function stepReinstallPlugin(SetupTester $I) {

        SetupPage::Reinstall($I, false);
    }

    /**
     * @param \SetupTester $I
     * @group GetStartedTab
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function stepEmptyConfigFields(SetupTester $I) {

        $I->amOnPage(SetupPage::$pluginBackLink);
        $I->fillField(SetupPage::$laterpaySandboxMerchantField, '');
        $I->fillField(SetupPage::$laterpaySandboxApiKeyField, '');
        $I->click(SetupPage::$pluginActivateFormButton);
    }

    /**
     * @param \SetupTester $I
     * @group GetStartedTab
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function assertNoLaterPayApiKey(SetupTester $I) {

        $I->see(SetupPage::$assertNoLaterPayApiKey);
    }

    /**
     * @param \SetupTester $I
     * @group GetStartedTab
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function stepSetInvalidMerchantId(SetupTester $I) {

        $I->amOnPage(SetupPage::$pluginBackLink);
        $I->fillField(SetupPage::$laterpaySandboxMerchantField, SetupPage::$laterpaySandboxMerchantInvalidValue);
        $I->click(SetupPage::$pluginActivateFormButton);
    }

    /**
     * @param \SetupTester $I
     * @group GetStartedTab
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function assertInvalidMerchantId(SetupTester $I) {

        $I->see(SetupPage::$assertNoLaterPayApiKey);
    }

    /**
     * @param \SetupTester $I
     * @group GetStartedTab
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function stepSetEmptyDemoKey(SetupTester $I) {

        $I->amOnPage(SetupPage::$pluginBackLink);
        $I->fillField(SetupPage::$laterpaySandboxMerchantField, SetupPage::$laterpaySandboxMerchantSandboxValue);
        $I->fillField(SetupPage::$laterpaySandboxApiKeyField, '');
        $I->click(SetupPage::$pluginActivateFormButton);
    }

    /**
     * @param \SetupTester $I
     * @group GetStartedTab
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function assertEmptyDemoKey(SetupTester $I) {

        $I->see(SetupPage::$assertEmptyDemoMerchantId);
    }

    /**
     * @param \SetupTester $I
     * @group GetStartedTab
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function stepSetInvalidDemoKey(SetupTester $I) {

        $I->amOnPage(SetupPage::$pluginBackLink);
        $I->fillField(SetupPage::$laterpaySandboxMerchantField, SetupPage::$laterpaySandboxMerchantSandboxValue);
        $I->fillField(SetupPage::$laterpaySandboxApiKeyField, SetupPage::$laterpaySandboxApiKeyInvalidValue);
        $I->click(SetupPage::$pluginActivateFormButton);
    }

    /**
     * @param \SetupTester $I
     * @group GetStartedTab
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function assertInvalidDemoKey(SetupTester $I) {

        $I->seeInPageSource(SetupPage::$assertInvalidDemoMerchantId);
    }

    /**
     * @param \SetupTester $I
     * @group GetStartedTab
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function stepSetValidDemoKey(SetupTester $I) {

        $I->fillField(SetupPage::$laterpaySandboxMerchantField, SetupPage::$laterpaySandboxMerchantSandboxValue);
        $I->fillField(SetupPage::$laterpaySandboxApiKeyField, SetupPage::$laterpaySandboxApiKeyValidValue);
    }

    /**
     * @param \SetupTester $I
     * @group GetStartedTab
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function assertValidDemoKey(SetupTester $I) {

        $I->seeElement(SetupPage::$assertFieldStepOneDone);
    }

    /**
     * @param \SetupTester $I
     * @group GetStartedTab
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function stepPriceValidation(SetupTester $I) {

        $I->fillField(SetupPage::$globalDefaultPriceField, SetupPage::$globalDefaultPrice);
        $I->selectOption(SetupPage::$globalDefaultCurrencyField, SetupPage::$globalDefaultCurrency);

        $I->click(SetupPage::$pluginActivateFormButton);
        $I->wait(SetupPage::$pluginActivationDelay);
    }

    /**
     * @param \SetupTester $I
     * @group GetStartedTab
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function assertPriceValidation(SetupTester $I) {

        $I->seeCurrentUrlEquals(SetupPage::$pluginActivateSuccesRedirectUrl);
    }

}

