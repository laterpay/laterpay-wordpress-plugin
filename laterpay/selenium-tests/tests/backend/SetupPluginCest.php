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
    public function testCanIsetCurrencyAndGlobalDefaultPrice(BackendTester $I) {

        $I->wantToTest('UI2: Can I set the currency and global default price in the get started tab and is it applied to existing posts?');

        BackendModule::of($I)->login();

        SetupModule::of($I)->uninstallPlugin();

        PostModule::of($I)->createTestPost(BaseModule::$T1, BaseModule::$C1);

        SetupModule::of($I)->installPlugin()->activatePlugin();

        SetupModule::of($I)->goThroughGetStartedTab('0.35', 'USD');

        PostModule::of($I)->checkTestPostForLaterPayElements($I->getVar('post'), 'global default price', 0.35, 'USD', BaseModule::$T1, BaseModule::$C1);
    }

    /**
     * @param \BackendTester $I
     * @group UI3
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/286
     */
    public function testCanChangeCurrency(BackendTester $I) {

        $I->wantToTest('UI3: Can I change the currency and is it applied to existing posts?');

        BackendModule::of($I)->login();

        SetupModule::of($I)->uninstallPlugin();

        PostModule::of($I)->createTestPost(BaseModule::$T1, BaseModule::$C1);

        SetupModule::of($I)->installPlugin()->activatePlugin();

        SetupModule::of($I)->goThroughGetStartedTab('0.35', 'USD');

        SetupModule::of($I)->changeCurrency('EUR');

        PostModule::of($I)->checkTestPostForLaterPayElements($I->getVar('post'), 'global default price', 0.35, 'EUR', BaseModule::$T1, BaseModule::$C1);
    }

    /**
     * @param \BackendTester $I
     * @group UI4
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/287
     */
    public function testCanPurchasePost(BackendTester $I) {

        $I->wantToTest('UI4: Can I purchase a post?');

        BackendModule::of($I)->login();

        SetupModule::of($I)
                ->uninstallPlugin()
                ->installPlugin()
                ->activatePlugin()
                ->goThroughGetStartedTab('0.35', 'EUR');

        ModesModule::of($I)->switchToLiveMode();

        PostModule::of($I)->createTestPost(BaseModule::$T1, BaseModule::$C1);

        PostModule::of($I)->checkTestPostForLaterPayElements($I->getVar('post'), 'global default price', 0.35, 'EUR', BaseModule::$T1, BaseModule::$C1, 60);

        PostModule::of($I)->purchasePost($I->getVar('post'), '0.35', 'EUR', BaseModule::$T1, BaseModule::$C1);
    }

    /**
     * @param \BackendTester $I
     * @group UI5
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/288
     */
    public function testCanChangeDefaultPriceAndAapplyIt(BackendTester $I) {

        $I->wantToTest('UI5: Can I change the global default price to 0.00 and is it applied to existing and new posts?');

        BackendModule::of($I)->login();

        SetupModule::of($I)->uninstallPlugin();

        PostModule::of($I)->createTestPost(BaseModule::$T1, BaseModule::$C1);
        $_testPost1 = $I->getVar('post');

        SetupModule::of($I)
                ->installPlugin()
                ->activatePlugin()
                ->goThroughGetStartedTab('0.35', 'USD');

        SetupModule::of($I)->changeGlobalDefaultPrice('0.00');

        PostModule::of($I)->createTestPost(BaseModule::$T1, BaseModule::$C1);
        $_testPost2 = $I->getVar('post');

        PostModule::of($I)->checkTestPostForLaterPayElements($_testPost1, 'global default price', 0, 'USD', BaseModule::$T1, BaseModule::$C1);

        PostModule::of($I)->checkTestPostForLaterPayElements($_testPost2, 'global default price', 0, 'USD', BaseModule::$T1, BaseModule::$C1);
    }

    /**
     * @param \BackendTester $I
     * @group dev
     */
    public function dev(BackendTester $I) {

        $I->wantToTest('Dev');

        BackendModule::of($I)->login();

        SetupModule::of($I)->changeGlobalDefaultPrice('0.00');
    }

}

