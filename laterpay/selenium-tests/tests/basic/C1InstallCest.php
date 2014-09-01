<?php

use \BasicTester;

class C1InstallCest {

    public function login(BasicTester $I) {

        $I->amOnPage(LoginPage::$URL);
        $I->fillField(LoginPage::$usernameField, LoginPage::$usernameValue);
        $I->fillField(LoginPage::$passwordField, LoginPage::$passwordValue);
        $I->click(LoginPage::$loginButton);
        $I->see(LoginPage::$expectedTitle);
    }

    public function findPlugin(BasicTester $I) {

        $I->amOnPage(PluginPage::$url_plugin_add);
        $I->fillField(PluginPage::$pluginSearchField, PluginPage::$pluginSearchValue);
        $I->submitForm(PluginPage::$pluginSearchForm, array());
        $I->see(PluginPage::$expectedModule);
    }

    public function uploadPlugin(BasicTester $I) {

        $I->amOnPage(PluginPage::$url_plugin_upload);
        $I->attachFile('pluginzip', 'laterpay.zip');
        $I->click("install-plugin-submit");
        $I->see(PluginPage::$assertionInstalled);
    }

    public function checkInstalledPlugin(BasicTester $I) {

        $I->amOnPage(PluginPage::$url_plugin_list);
        $I->see(PluginPage::$assertionPluginListed);
    }

    private function installAndActivatePluginByManager(BasicTester $I) {

        $I->click('.install-now');
        $I->acceptPopup();
        $I->click("OK");
        $I->click('Activate Plugin');
    }

    private function installPluginByManager(BasicTester $I) {

        $I->click('.install-now');
        $I->acceptPopup();
        $I->click("OK");
    }

}

