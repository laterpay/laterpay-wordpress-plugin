<?php

use \SetupTester;

/**
 * C1 - Installation, Activation
 * @group Install
 */
class InstallCest {

    /**
     * @param \SetupTester $I
     * @group Install
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function stepLoginBackend(SetupTester $I) {

        \LoginPage::login($I);
    }

    /**
     * @param \SetupTester $I
     * @group Install
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function stepPluginRemoveIfExist(SetupTester $I) {

        $I->amOnPage(SetupPage::$url_plugin_list);
        if ($I->hSee($I, SetupPage::$assertPluginName)) {

            $I->hClick($I, SetupPage::$pluginDeactivateLink);

            $I->hClick($I, SetupPage::$pluginDeleteLink);

            $I->hClick($I, SetupPage::$pluginDeleteConfirmLink);
        };
    }

    /**
     * @param \SetupTester $I
     * @group Install
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function stepPluginUpload(SetupTester $I) {

        $I->amOnPage(SetupPage::$url_plugin_add);
        $I->amOnPage(SetupPage::$url_plugin_upload);
        $I->attachFile(SetupPage::$pluginUploadField, SetupPage::$pluginUploadFilename);
        $I->click(SetupPage::$pluginUploadSubmitField);
    }

    /**
     * @param \SetupTester $I
     * @group Install
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function assertPluginInstalled(SetupTester $I) {

        $I->see(SetupPage::$assertInstalled);
    }

    /**
     * @param \SetupTester $I
     * @group Install
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function assertPluginListed(SetupTester $I) {

        $I->amOnPage(SetupPage::$url_plugin_list);
        $I->see(SetupPage::$assertPluginListed);
    }

    /**
     * @param \SetupTester $I
     * @group Install
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     * @after assertPluginIntoNavigationTab
     */
    public function stepPluginActivate(SetupTester $I) {

        $I->amOnPage(SetupPage::$url_plugin_list);

        $I->click(SetupPage::$pluginActivateLink);

        $I->waitForElement(SetupPage::$pluginDeactivateLink);
    }

    /**
     * @param \SetupTester $I
     * @group Install
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function assertPluginIntoNavigationTab(SetupTester $I) {

        $I->amOnPage(SetupPage::$url_plugin_list);

        $I->see(SetupPage::$pluginNavigationLabel, SetupPage::$backNavigateTab);
    }

}

