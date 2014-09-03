<?php

class DevPage {

    public static function start($I) {

        \LoginPage::login($I);

        $I->amOnPage(SetupPage::$pluginBackLink);

        $I->fillField(SetupPage::$laterpaySandboxMerchantField, SetupPage::$laterpaySandboxMerchantSandboxValue);
        $I->fillField(SetupPage::$laterpaySandboxApiKeyField, SetupPage::$laterpaySandboxApiKeyValidValue);

        $I->fillField(SetupPage::$globalDefaultPriceField, SetupPage::$globalDefaultPrice);
        $I->selectOption(SetupPage::$globalDefaultCurrencyField, SetupPage::$globalDefaultCurrency);

        $I->seeElement(SetupPage::$assertFieldStepOneDone);
        $I->makeScreenshot(1);

        \LoginPage::logout($I);
    }

}

