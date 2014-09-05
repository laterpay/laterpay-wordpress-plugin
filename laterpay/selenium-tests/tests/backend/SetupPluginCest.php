<?php

use \BackendTester;

/**
 * Installation
 * @group C1
 */
class SetupPluginCest {

    /**
     * @param \BackendTester $I
     * @group UI1
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/284
     */
    public function installPlugin(BackendTester $I) {

        $I->wantToTest('UI1: Can the plugin be installed and activated?');

        BackendModule::of($I)->login();

        SetupModule::of($I)
                ->uninstallPlugin()
                ->installPlugin()
                ->activatePlugin();
    }

    /**
     * @param \BackendTester $I
     * @group UI2
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/285
     */
    public function setPrice(BackendTester $I) {

        $I->wantToTest('UI2: Can I set the currency and global default price in the get started tab and is it applied to existing posts?');

        BackendModule::of($I)->login();

        SetupModule::of($I)->uninstallPlugin()->installPlugin();

        PostModule::of($I)->createTestPost('T1', 'C1');

        SetupModule::of($I)->activatePlugin();

        SetupModule::of($I)->goThroughGetStartedTab('0.35', 'USD');

        PostModule::of($I)
                ->checkTestPostForLaterPayElements('test post 1', 'global default price', 0.35, 'USD', BaseModule::$T1,
                                                    BaseModule::$C1, 60);
    }

}

