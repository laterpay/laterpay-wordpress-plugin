<?php

use \BackendTester;

class CheckPricesApplyingWithCategoryPriceDeletedCest {

    //Install

    /**
     * @param \BackendTester $I
     * @group Backend
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/294
     */
    public function stepReinstallPlugin(BackendTester $I) {
        SetupPage::Reinstall($I, false);
    }

    //Get started tab

    /**
     * @param \BackendTester $I
     * @group Backend
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/294
     */
    public function stepEmptyConfigFields(BackendTester $I) {
        $I->amOnPage(LoginPage::$URL);
        $I->click(PluginPage::$adminMenuPluginButton);
        $I->fillField(SetupPage::$laterpaySandboxMerchantField, '');
        $I->fillField(SetupPage::$laterpaySandboxApiKeyField, '');
        $I->click(SetupPage::$pluginActivateFormButton);
    }

    /**
     * @param \BackendTester $I
     * @group Backend
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function assertNoLaterPayApiKey(BackendTester $I) {
        $I->see(SetupPage::$assertNoLaterPayApiKey);
    }

    /**
     * @param \BackendTester $I
     * @group Backend
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function stepSetInvalidMerchantId(BackendTester $I) {
        $I->fillField(SetupPage::$laterpaySandboxMerchantField, SetupPage::$laterpaySandboxMerchantInvalidValue);
        $I->click(SetupPage::$pluginActivateFormButton);
    }

    /**
     * @param \BackendTester $I
     * @group Backend
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function assertInvalidMerchantId(BackendTester $I) {
        $I->see(SetupPage::$assertNoLaterPayApiKey);
    }

    /**
     * @param \BackendTester $I
     * @group Backend
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function stepSetEmptyDemoKey(BackendTester $I) {
        $I->fillField(SetupPage::$laterpaySandboxMerchantField, SetupPage::$laterpaySandboxMerchantSandboxValue);
        $I->fillField(SetupPage::$laterpaySandboxApiKeyField, '');
        $I->click(SetupPage::$pluginActivateFormButton);
    }

    /**
     * @param \BackendTester $I
     * @group Backend
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function assertEmptyDemoKey(BackendTester $I) {
        $I->see(SetupPage::$assertEmptyDemoMerchantId);
    }

    /**
     * @param \BackendTester $I
     * @group Backend
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function stepSetInvalidDemoKey(BackendTester $I) {
        $I->fillField(SetupPage::$laterpaySandboxMerchantField, SetupPage::$laterpaySandboxMerchantSandboxValue);
        $I->fillField(SetupPage::$laterpaySandboxApiKeyField, SetupPage::$laterpaySandboxApiKeyInvalidValue);
        $I->click(SetupPage::$pluginActivateFormButton);
    }

    /**
     * @param \BackendTester $I
     * @group Backend
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function assertInvalidDemoKey(BackendTester $I) {
        $I->seeInPageSource(SetupPage::$assertInvalidDemoMerchantId);
    }

    /**
     * @param \BackendTester $I
     * @group Backend
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function stepSetValidDemoKey(BackendTester $I) {
        $I->fillField(SetupPage::$laterpaySandboxMerchantField, SetupPage::$laterpaySandboxMerchantSandboxValue);
        $I->fillField(SetupPage::$laterpaySandboxApiKeyField, SetupPage::$laterpaySandboxApiKeyValidValue);
    }

    /**
     * @param \BackendTester $I
     * @group Backend
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function assertValidDemoKey(BackendTester $I) {
        $I->seeElement(SetupPage::$assertFieldStepOneDone);
    }

    //TODO: validate price and complete get started

    //Create Test Category

    /**
     * @param \BackendTester $I
     * @group Backend
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/294
     */
    public function stepCreateCategory1(BackendTester $I) {
        \CategoryPage::create($I, \CommonPage::$CAT1);
    }

    /**
     * @param \BackendTester $I
     * @group Backend
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/294
     */
    public function assertCreatedCategory1(BackendTester $I) {
        $I->see(\CommonPage::$CAT1, CategoryPage::$categories_table_selector);
    }

    /**
     * @param \BackendTester $I
     * @group Backend
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/294
     */
    public function stepCreateCategory2(BackendTester $I) {
        \CategoryPage::create($I, \CommonPage::$CAT2);
    }

    /**
     * @param \BackendTester $I
     * @group Backend
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/294
     */
    public function assertCreatedCategory2(BackendTester $I) {
        $I->see(\CommonPage::$CAT2, CategoryPage::$categories_table_selector);
    }

    /**
     * @param \BackendTester $I
     * @group Backend
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/294
     */
    public function stepCreateCategory3(BackendTester $I) {
        \CategoryPage::create($I, \CommonPage::$CAT3);
    }

    /**
     * @param \BackendTester $I
     * @group Backend
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/294
     */
    public function assertCreatedCategory3(BackendTester $I) {
        $I->see(\CommonPage::$CAT3, CategoryPage::$categories_table_selector);
    }

    //Change category default price

    /**
     * @param \BackendTester $I
     * @group Backend
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/294
     */
    public function stepCreateCategoryDefaultPrice1(BackendTester $I) {
        $I->amOnPage(LoginPage::$URL);
        $I->click(PluginPage::$adminMenuPluginButton);
        $I->click(PluginPage::$pluginPricingTab);
        $I->click(PluginPage::$pricingAddCategoryButton);
    }

    /**
     * @param \BackendTester $I
     * @group Backend
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/294
     */
    public function assertCategorySelectIsShown(BackendTester $I) {
        $I->seeElement(PluginPage::$pricingCategorySelect);
    }

    /**
     * @param \BackendTester $I
     * @group Backend
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/294
     */
    public function assertSaveLinkIsShown(BackendTester $I) {
        $I->seeElement(PluginPage::$pricingSaveLink);
    }

    /**
     * @param \BackendTester $I
     * @group Backend
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/294
     */
    public function assertCancelLinkIsShown(BackendTester $I) {
        $I->seeElement(PluginPage::$pricingCancelLink);
    }

    //TODO: Price validation

    /**
     * @param \BackendTester $I
     * @group Backend
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/294
     */
    public function stepClickCancelLinkIsShown(BackendTester $I) {
        $I->click(PluginPage::$pricingAddCategoryButton);
    }

    /**
     * @param \BackendTester $I
     * @group Backend
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/294
     */
    public function assertSetDefaultPriceForAnotherCategoryLinkIsShown(BackendTester $I) {
        $I->seeElement(PluginPage::$pricingAddCategoryButton);
    }

    /**
     * @param \BackendTester $I
     * @group Backend
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/294
     */
    public function assertCancelLinkIsNotShown(BackendTester $I) {
        $I->cantSeeElement(PluginPage::$pricingCancelLink);
    }

    /**
     * @param \BackendTester $I
     * @group Backend
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/294
     */
    public function assertSaveLinkIsNotShown(BackendTester $I) {
        $I->cantSeeElement(PluginPage::$pricingSaveLink);
    }

    /**
     * @param \BackendTester $I
     * @group Backend
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/294
     */
    public function stepClickCancelLinkIsShownAgain(BackendTester $I) {
        $I->click(PluginPage::$pricingAddCategoryButton);
        $I->selectOption()
    }


}

