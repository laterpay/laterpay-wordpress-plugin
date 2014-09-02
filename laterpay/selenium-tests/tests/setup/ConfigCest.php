<?php

use \SetupTester;

class ConfigCest {

    /**
     * @param \SetupTester $I
     * @group Setup
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function stepLoginBackend(SetupTester $I) {

        \LoginPage::login($I);
    }

    /**
     * @param \SetupTester $I
     * @group Setup
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function stepEmptyConfigFields(SetupTester $I) {

        $I->amOnPage(PluginPage::$pluginBackLink);
        $I->fillField(PluginPage::$laterpaySandboxMerchantField, '');
        $I->fillField(PluginPage::$laterpaySandboxApiKeyField, '');
        $I->click(PluginPage::$pluginActivateFormButton);
    }

    /**
     * @param \SetupTester $I
     * @group Setup
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function assertNoLaterPayApiKey(SetupTester $I) {

        $I->see(PluginPage::$assertNoLaterPayApiKey);
    }

    /**
     * @param \SetupTester $I
     * @group Setup
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function stepSetInvalidMerchantId(SetupTester $I) {

        $I->fillField(PluginPage::$laterpaySandboxMerchantField, PluginPage::$laterpaySandboxMerchantInvalidValue);
    }

    /**
     * @param \SetupTester $I
     * @group Setup
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function assertInvalidMerchantId(SetupTester $I) {

        $I->fillField(PluginPage::$laterpaySandboxMerchantField, PluginPage::$laterpaySandboxMerchantInvalidValue);
        $I->see(PluginPage::$assertNoLaterPayApiKey);
    }

}

