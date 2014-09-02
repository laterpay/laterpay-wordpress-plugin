<?php

use \SetupTester;

/**
 * @group basic
 */
class InstallCest {

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
    public function stepPluginRemoveIfExist(SetupTester $I) {

        $I->amOnPage(PluginPage::$url_plugin_list);
        if ($I->hSee($I, PluginPage::$assertPluginName)) {

            $I->hClick($I, PluginPage::$pluginDeactivateLink);

            $I->hClick($I, PluginPage::$pluginDeleteLink);

            $I->hClick($I, PluginPage::$pluginDeleteConfirmLink);
        };
    }

    /**
     * @param \SetupTester $I
     * @group Setup
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function stepPluginUpload(SetupTester $I) {

        $I->amOnPage(PluginPage::$url_plugin_add);
        $I->amOnPage(PluginPage::$url_plugin_upload);
        $I->attachFile(PluginPage::$pluginUploadField, PluginPage::$pluginUploadFilename);
        $I->click(PluginPage::$pluginUploadSubmitField);
    }

    /**
     * @param \SetupTester $I
     * @group Setup
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function assertPluginInstalled(SetupTester $I) {

        $I->see(PluginPage::$assertInstalled);
    }

    /**
     * @param \SetupTester $I
     * @group Setup
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function assertPluginListed(SetupTester $I) {

        $I->amOnPage(PluginPage::$url_plugin_list);
        $I->see(PluginPage::$assertPluginListed);
    }

    /**
     * @param \SetupTester $I
     * @group Setup
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     * @after assertPluginIntoNavigationTab
     */
    public function stepPluginActivate(SetupTester $I) {

        $I->amOnPage(PluginPage::$url_plugin_list);

        $I->click(PluginPage::$pluginActivateLink);

        $I->waitForElement(PluginPage::$pluginDeactivateLink);
    }

    /**
     * @param \SetupTester $I
     * @group Setup
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function assertPluginIntoNavigationTab(SetupTester $I) {

        $I->amOnPage(PluginPage::$url_plugin_list);

        $I->see(PluginPage::$pluginNavigationLabel, PluginPage::$backNavigateTab);
    }

}

