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

        $_price = '0.35';
        $_currency = 'EUR';
        $I->wantToTest('UI2: Can I set the currency and global default price in the get started tab and is it applied to existing posts?');

        BackendModule::of($I)->login();

        SetupModule::of($I)->uninstallPlugin();

        PostModule::of($I)->createTestPost(BaseModule::$T1, BaseModule::$C1);

        SetupModule::of($I)->installPlugin()->activatePlugin();

        SetupModule::of($I)->goThroughGetStartedTab($_price, $_currency);

        PostModule::of($I)->checkTestPostForLaterPayElements($I->getVar('post'), 'global default price', $_price, $_currency, BaseModule::$T1, BaseModule::$C1);
    }

    /**
     * @param \BackendTester $I
     * @group UI3
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/286
     */
    public function testCanChangeCurrency(BackendTester $I) {

        $I->comment('Leave out this test for the moment, as we support only one currency at the moment');
        return;

        $_price = '0.35';
        $_currencyBefore = 'USD';
        $_currencyAfter = 'EUR';
        $I->wantToTest('UI3: Can I change the currency and is it applied to existing posts?');

        BackendModule::of($I)->login();

        SetupModule::of($I)->uninstallPlugin();

        SetupModule::of($I)->installPlugin()->activatePlugin();

        SetupModule::of($I)->goThroughGetStartedTab($_price, $_currencyBefore);

        SetupModule::of($I)->changeCurrency($_currencyAfter);

        PostModule::of($I)->checkTestPostForLaterPayElements($I->getVar('post'), 'global default price', $_price, $_currencyAfter, BaseModule::$T1, BaseModule::$C1);
    }

    /**
     * @param \BackendTester $I
     * @group UI4
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/287
     */
    public function testCanPurchasePost(BackendTester $I) {

        $_price = '0.35';
        $_currency = 'EUR';
        $I->wantToTest('UI4: Can I purchase a post?');

        BackendModule::of($I)->login();

        SetupModule::of($I)
                ->uninstallPlugin()
                ->installPlugin()
                ->activatePlugin()
                ->goThroughGetStartedTab($_price, $_currency);

        ModesModule::of($I)->switchToLiveMode();

        PostModule::of($I)->createTestPost(BaseModule::$T1, BaseModule::$C1);

        PostModule::of($I)->checkTestPostForLaterPayElements($I->getVar('post'), 'global default price', $_price, $_currency, BaseModule::$T1, BaseModule::$C1);

        PostModule::of($I)->purchasePost($I->getVar('post'), $_price, $_currency, BaseModule::$T1, BaseModule::$C1);
    }

    /**
     * @param \BackendTester $I
     * @group UI5
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/288
     */
    public function testCanRemoveDefaultPriceAndAapplyIt(BackendTester $I) {

        $_priceBefore = '0.35';
        $_priceAfter = '0.00';
        $_currency = 'EUR';
        $I->wantToTest('UI5: Can I change the global default price to 0.00 and is it applied to existing and new posts?');

        BackendModule::of($I)->login();

        SetupModule::of($I)->uninstallPlugin();

        PostModule::of($I)->createTestPost(BaseModule::$T1, BaseModule::$C1);
        $_testPost1 = $I->getVar('post');

        SetupModule::of($I)
                ->installPlugin()
                ->activatePlugin()
                ->goThroughGetStartedTab($_priceBefore, $_currency);

        SetupModule::of($I)->changeGlobalDefaultPrice($_priceAfter);

        PostModule::of($I)->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'global default price', $_priceAfter);
        $_testPost2 = $I->getVar('post');

        PostModule::of($I)->checkTestPostForLaterPayElements($_testPost1, 'global default price', $_priceAfter, $_currency, BaseModule::$T1, BaseModule::$C1);

        PostModule::of($I)->checkTestPostForLaterPayElements($_testPost2, 'global default price', $_priceAfter, $_currency, BaseModule::$T1, BaseModule::$C1);
    }

    /**
     * @param \BackendTester $I
     * @group UI6
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/289
     */
    public function testCanChangeDefaultPriceAndAapplyIt(BackendTester $I) {

        $_priceBefore = '0.35';
        $_priceAfter = '0.28';
        $_currency = 'EUR';
        $I->wantToTest('UI6: Can I change the global default price > 0 and is it applied to existing and new posts?');

        BackendModule::of($I)->login();

        SetupModule::of($I)->uninstallPlugin();

        PostModule::of($I)->createTestPost(BaseModule::$T1, BaseModule::$C1);
        $_testPost1 = $I->getVar('post');

        SetupModule::of($I)
                ->installPlugin()
                ->activatePlugin()
                ->goThroughGetStartedTab($_priceBefore, $_currency);

        SetupModule::of($I)->changeGlobalDefaultPrice($_priceAfter);

        PostModule::of($I)->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'global default price', $_priceAfter);
        $_testPost2 = $I->getVar('post');

        PostModule::of($I)->checkTestPostForLaterPayElements($_testPost1, 'global default price', $_priceAfter, $_currency, BaseModule::$T1, BaseModule::$C1);

        PostModule::of($I)->checkTestPostForLaterPayElements($_testPost2, 'global default price', $_priceAfter, $_currency, BaseModule::$T1, BaseModule::$C1);
    }

    /**
     * @param \BackendTester $I
     * @group UI21
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/304
     */
    public function testCanChangePreviewToTeaser(BackendTester $I) {

        $_price = '0.35';
        $_currency = 'EUR';
        $I->wantToTest('UI21: Can I change the preview mode to “teaser only”?');

        BackendModule::of($I)->login();

        SetupModule::of($I)
                ->uninstallPlugin()
                ->installPlugin()
                ->activatePlugin()
                ->goThroughGetStartedTab($_price, $_currency);

        PostModule::of($I)->createTestPost(BaseModule::$T1, BaseModule::$C1);

        ModesModule::of($I)->changePreviewMode('teaser only');

        PostModule::of($I)->checkTestPostForLaterPayElements($I->getVar('post'), 'global default price', $_price, $_currency, BaseModule::$T1, BaseModule::$C1);
    }

    /**
     * @param \BackendTester $I
     * @group UI22
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/305
     */
    public function testCanChangePreviewToOverlay(BackendTester $I) {

        $_price = '0.35';
        $_currency = 'EUR';
        $I->wantToTest('UI22: Can I change the preview mode to “overlay”?');

        BackendModule::of($I)->login();

        SetupModule::of($I)
                ->uninstallPlugin()
                ->installPlugin()
                ->activatePlugin()
                ->goThroughGetStartedTab($_price, $_currency);

        PostModule::of($I)->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'global default price', $_price);
        $_testPost = $I->getVar('post');

        ModesModule::of($I)->changePreviewMode('overlay');

        PostModule::of($I)->checkTestPostForLaterPayElements($_testPost, 'global default price', $_price, $_currency, BaseModule::$T1, BaseModule::$C1);
    }

    /**
     * @param \BackendTester $I
     * @group UI23
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/306
     */
    public function testCorrectShortcodesRenderedProperlyWithinFreePost(BackendTester $I) {

        $_priceOne = '0.00';
        $_priceTwo = '0.55';
        $_currency = 'EUR';
        $I->wantToTest('UI23: Are correct shortcodes rendered properly within a free post?');

        BackendModule::of($I)->login();

        SetupModule::of($I)
                ->uninstallPlugin()
                ->installPlugin()
                ->activatePlugin()
                ->goThroughGetStartedTab($_priceOne, $_currency);

        PostModule::of($I)->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'individual price', $_priceOne);
        $_testPost1 = $I->getVar('post');

        PostModule::of($I)->createTestPost(BaseModule::$T2, BaseModule::$C2, null, 'individual price', $_priceTwo);
        $_testPost2 = $I->getVar('post');

        PostModule::of($I)->checkTestPostForLaterPayElements($_testPost1, 'individual price', $_priceOne, $_currency, BaseModule::$T1, BaseModule::$C1);

        PostModule::of($I)->checkTestPostForLaterPayElements($_testPost2, 'individual price', $_priceTwo, $_currency, BaseModule::$T2);

        PostModule::of($I)->checkIfCorrectShortcodeIsDisplayedCorrectly($_testPost1, $_priceOne);
    }

    /**
     * @param \BackendTester $I
     * @group UI24
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/307
     */
    public function testCorrectShortcodesRenderedProperlyWithinPaidPost(BackendTester $I) {


        $_priceOne = '0.00';
        $_priceTwo = '0.55';
        $_currency = 'EUR';
        $I->wantToTest('UI24: Are correct shortcodes rendered properly within a paid post?');

        BackendModule::of($I)->login();

        SetupModule::of($I)
                ->uninstallPlugin()
                ->installPlugin()
                ->activatePlugin()
                ->goThroughGetStartedTab($_priceOne, $_currency);

        PostModule::of($I)->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'individual price', $_priceOne);
        $_testPost1 = $I->getVar('post');

        PostModule::of($I)->createTestPost(BaseModule::$T2, BaseModule::$C2, null, 'individual price', $_priceTwo);
        $_testPost2 = $I->getVar('post');

        PostModule::of($I)->checkTestPostForLaterPayElements($_testPost1, 'individual price', $_priceOne, $_currency, BaseModule::$T1, BaseModule::$C1);

        PostModule::of($I)->checkTestPostForLaterPayElements($_testPost2, 'individual price', $_priceTwo, $_currency, BaseModule::$T2);

        PostModule::of($I)->checkIfCorrectShortcodeIsDisplayedCorrectly($_testPost2, $_priceTwo);
    }

    /**
     * @param \BackendTester $I
     * @group UI25
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/308
     */
    public function testWrongShortcodesRenderedProperlyWithinFreePost(BackendTester $I) {

        $_priceOne = '0.00';
        $_priceTwo = '0.55';
        $_currency = 'EUR';
        $I->wantToTest('UI25: Are wrong shortcodes rendered properly within a free post?');

        BackendModule::of($I)->login();

        SetupModule::of($I)
                ->uninstallPlugin()
                ->installPlugin()
                ->activatePlugin()
                ->goThroughGetStartedTab($_priceOne, $_currency);

        PostModule::of($I)->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'individual price', $_priceOne);
        $_testPost1 = $I->getVar('post');

        PostModule::of($I)->createTestPost(BaseModule::$T3, BaseModule::$C3, null, 'individual price', $_priceTwo);
        $_testPost2 = $I->getVar('post');

        PostModule::of($I)->checkTestPostForLaterPayElements($_testPost1, 'individual price', $_priceOne, $_currency, BaseModule::$T1, BaseModule::$C1);

        PostModule::of($I)->checkTestPostForLaterPayElements($_testPost2, 'individual price', $_priceTwo, $_currency, BaseModule::$T3);

        PostModule::of($I)->checkIfWrongShortcodeIsDisplayedCorrectly($_testPost1, $_priceOne);
    }

    /**
     * @param \BackendTester $I
     * @group UI26
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/309
     */
    public function testWrongShortcodesRenderedProperlyWithinPaidPost(BackendTester $I) {

        $_priceOne = '0.00';
        $_priceTwo = '0.55';
        $_currency = 'EUR';
        $I->wantToTest('UI26: Are wrong shortcodes rendered properly within a paid post?');

        BackendModule::of($I)->login();

        SetupModule::of($I)
                ->uninstallPlugin()
                ->installPlugin()
                ->activatePlugin()
                ->goThroughGetStartedTab($_priceOne, $_currency);

        PostModule::of($I)->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'individual price', $_priceOne);
        $_testPost1 = $I->getVar('post');

        PostModule::of($I)->createTestPost(BaseModule::$T3, BaseModule::$C3, null, 'individual price', $_priceTwo);
        $_testPost2 = $I->getVar('post');

        PostModule::of($I)->checkTestPostForLaterPayElements($_testPost1, 'individual price', $_priceOne, $_currency, BaseModule::$T1, BaseModule::$C1);

        PostModule::of($I)->checkTestPostForLaterPayElements($_testPost2, 'individual price', $_priceTwo, $_currency, BaseModule::$T3);

        PostModule::of($I)->checkIfCorrectShortcodeIsDisplayedCorrectly($_testPost1, $_priceOne);
    }

    /**
     * @param BackendTester $I
     * @group UI27
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/310
     */
    public function testSwitchToLiveMode(BackendTester $I) {

        $_priceOne = '0.35';
        $_priceTwo = '0.10';
        $_currency = 'EUR';
        $I->wantToTest('UI27: Can I switch to live mode?');

        BackendModule::of($I)
                ->login();

        SetupModule::of($I)
                ->uninstallPlugin()
                ->installPlugin()
                ->activatePlugin()
                ->goThroughGetStartedTab($_priceOne, $_currency);

        PostModule::of($I)
                ->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'individual price', $_priceTwo, 60);

        ModesModule::of($I)
                ->switchToLiveMode();

        PostModule::of($I)
                ->checkTestPostForLaterPayElements($I->getVar('post'), 'individual price', $_priceTwo, $_currency, BaseModule::$T1, BaseModule::$C1, 60);
    }

    /**
     * @param BackendTester $I
     * @group UI28
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/311
     */
    public function testSwitchToTestMode(BackendTester $I) {

        $_priceOne = '0.35';
        $_priceTwo = '0.10';
        $_currency = 'EUR';
        $I->wantToTest('UI28: Can I switch to test mode?');

        BackendModule::of($I)
                ->login();

        SetupModule::of($I)
                ->uninstallPlugin()
                ->installPlugin()
                ->activatePlugin()
                ->goThroughGetStartedTab($_priceOne, $_currency);

        PostModule::of($I)
                ->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'individual price', $_priceTwo, 60);

        ModesModule::of($I)
                ->switchToTestMode();

        PostModule::of($I)
                ->checkTestPostForLaterPayElements($I->getVar('post'), 'individual price', $_priceTwo, $_currency, BaseModule::$T1, BaseModule::$C1, 60);
    }

    /**
     * @param \BackendTester $I
     * @group dev
     */
    public function dev(BackendTester $I) {

        BackendModule::of($I)->login();

        ModesModule::of($I)->switchToTestMode();
    }

}

