<?php

use \BackendTester;

/**
 * Class CategoryPricesCest
 */
class PostCheckCest {

    /**
     * @param \BackendTester $I
     * @group UI13
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/296
     */
    public function testCreatePriceWithZeroIndividualPrice(BackendTester $I)
    {
        $I->wantToTest('Can I create a free post, i.e. a post with an individual price of 0.00?');

        BackendModule::of($I)
            ->login();

        SetupModule::of($I)
            ->uninstallPlugin()
            ->installPlugin()
            ->activatePlugin()
            ->goThroughGetStartedTab(0.35, 'USD');

        PostModule::of($I)
            ->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'individual price', '0.00', 60)
            ->checkTestPostForLaterPayElements($I->getVar('post'), 'individual price', '0.00', 'USD',
                                               BaseModule::$T1, BaseModule::$C1, 60);

        BackendModule::of($I)
            ->logout();
    }

    /**
     * @param \BackendTester $I
     * @group UI14
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/297
     */
    public function testCreatePaidPostWithIndividualPrice(BackendTester $I)
    {
        $I->wantToTest('Can I create a paid post with individual price,
                        i.e. a post with an individual price of > 0.00?');

        BackendModule::of($I)
            ->login();

        SetupModule::of($I)
            ->uninstallPlugin()
            ->installPlugin()
            ->activatePlugin()
            ->goThroughGetStartedTab(0.35, 'USD');

        PostModule::of($I)
            ->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'individual price', '0.40', 60)
            ->checkTestPostForLaterPayElements($I->getVar('post'), 'individual price', '0.40', 'USD',
                                               BaseModule::$T1, BaseModule::$C1, 60);

        BackendModule::of($I)
            ->logout();
    }

    /**
     * @param \BackendTester $I
     * @group UI15
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/298
     */
    public function testIfTeaserContentAutomaticallyGeneratedForPosts(BackendTester $I)
    {
        $I->wantToTest('Is the teaser content automatically generated both for existing and new posts?');

        BackendModule::of($I)
            ->login();

        SetupModule::of($I)
            ->uninstallPlugin();

        PostModule::of($I)
            ->createTestPost(BaseModule::$T1, BaseModule::$C1);
        $testPost1 = $I->getVar('post');

        SetupModule::of($I)
            ->installPlugin()
            ->activatePlugin()
            ->goThroughGetStartedTab(0.35, 'USD');

        PostModule::of($I)
            ->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'individual price', '0.40', null);
        $testPost2 = $I->getVar('post');

        PostModule::of($I)
            ->checkTestPostForLaterPayElements($testPost1, 'individual price', '0.35', 'USD',
                BaseModule::$T1, BaseModule::$C1, 60)
            ->checkTestPostForLaterPayElements($testPost2, 'individual price', '0.40', 'USD',
                BaseModule::$T1, BaseModule::$C1, 60);

        BackendModule::of($I)
            ->logout();
    }

    /**
     * @param \BackendTester $I
     * @group UI16
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/299
     */
    public function testCreatePaidPostWithDynamicPricing(BackendTester $I)
    {
        $I->wantToTest('Can I create a paid post with dynamic pricing?');

        BackendModule::of($I)
            ->login();

        SetupModule::of($I)
            ->uninstallPlugin()
            ->installPlugin()
            ->activatePlugin()
            ->goThroughGetStartedTab(0.35, 'USD');

        //TODO: Probably individual dynamic price and we need to implement this
        PostModule::of($I)
            ->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'individual price',
                             'starts at 0.85 and goes to 0.05 after 5 days', null)
            ->checkTestPostForLaterPayElements($I->getVar('post'), 'individual price', 0.85, 'USD',
                                               BaseModule::$T1, BaseModule::$C1, 60);

        BackendModule::of($I)
            ->logout();
    }

    /**
     * @param \BackendTester $I
     * @group UI17
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/300
     */
    public function testCreatePaidPostWithGlobalDefaultPrice(BackendTester $I)
    {
        $I->wantToTest('Can I create a paid post with global default price?');

        BackendModule::of($I)
            ->login();

        CategoryModule::of($I)
            ->createTestCategory(BaseModule::$CAT1);

        SetupModule::of($I)
            ->uninstallPlugin()
            ->installPlugin()
            ->activatePlugin()
            ->goThroughGetStartedTab(0.35, 'USD');

        PostModule::of($I)
            ->createTestPost(BaseModule::$T1, BaseModule::$C1, 'category-2', 'global default price', 0.35)
            ->checkTestPostForLaterPayElements($I->getVar('post'), 'global default price', 0.35, 'USD',
                                               BaseModule::$T1, BaseModule::$C1, 60);

        BackendModule::of($I)
            ->logout();
    }

    /**
     * @param \BackendTester $I
     * @group UI18
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/301
     */
    public function testCreatePaidPostWithCategoryDefaultPrice(BackendTester $I)
    {
        $I->wantToTest('Can I create a paid post with category default price?');

        BackendModule::of($I)
            ->login();

        SetupModule::of($I)
            ->uninstallPlugin()
            ->installPlugin()
            ->activatePlugin()
            ->goThroughGetStartedTab(0.35, 'USD');

        CategoryModule::of($I)
            ->createTestCategory(BaseModule::$CAT1);

        CategoryDefaultPriceModule::of($I)
            ->createCategoryDefaultPrice(BaseModule::$CAT1, 0.49);

        PostModule::of($I)
            ->createTestPost(BaseModule::$T1, BaseModule::$C1, 'category-2', 'category default price', 0.49, 60)
            ->checkTestPostForLaterPayElements($I->getVar('post'), 'category default price', 0.49, 'USD',
                                                BaseModule::$T1, BaseModule::$C1, 60);

        BackendModule::of($I)
            ->logout();
    }

    /**
     * @param BackendTester $I
     * @group UI19
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/302
     */
    public function testCheckPluginProtectFilesInPaidPost(BackendTester $I)
    {
        $I->wantToTest('Does the plugin protect files in a paid post?');

        BackendModule::of($I)
            ->login();

        SetupModule::of($I)
            ->uninstallPlugin()
            ->installPlugin()
            ->activatePlugin()
            ->goThroughGetStartedTab(0.35, 'USD');

        PostModule::of($I)
            ->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'global default price', 0.35, 60, SetupModule::$pluginUploadFilename)
            ->checkTestPostForLaterPayElements($I->getVar('post'), 'global default price', 0.35, 'USD',
                                                BaseModule::$T1, BaseModule::$C1, 60)
            ->checkIfFilesAreProtected($I->getVar('post'), 0.35, SetupModule::$pluginUploadFilename);

        BackendModule::of($I)
            ->logout();
    }

    /**
     * @param BackendTester $I
     * @group UI20
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/303
     */
    public function testChangeIndividualPrice(BackendTester $I)
    {
        $I->wantToTest('Can I change the individual price?');

        BackendModule::of($I)
            ->login();

        SetupModule::of($I)
            ->uninstallPlugin()
            ->installPlugin()
            ->activatePlugin()
            ->goThroughGetStartedTab(0.35, 'USD');

        PostModule::of($I)
            ->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'global default price', 0.35, 60)
            ->changeIndividualPrice($I->getVar('post'), 0.69)
            ->checkTestPostForLaterPayElements($I->getVar('post'), 'individual price', 0.69, 'USD',
                                                BaseModule::$T1, BaseModule::$C1, 60);

        BackendModule::of($I)
            ->logout();
    }

    /**
     * @param BackendTester $I
     * @group UI21
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/304
     */
    public function testChangeModeToTeaserOnly(BackendTester $I)
    {
        $I->wantToTest('Can I change the preview mode to “teaser only”?');

        BackendModule::of($I)
            ->login();

        SetupModule::of($I)
            ->uninstallPlugin()
            ->installPlugin()
            ->activatePlugin()
            ->goThroughGetStartedTab(0.35, 'USD');

        PostModule::of($I)
            ->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'global default price', 0.35, 60);

        ModesModule::of($I)
            ->changePreviewMode('teaser_only');

        PostModule::of($I)
            ->checkTestPostForLaterPayElements(1, 'global default price', 0.35, 'USD',
                                                BaseModule::$T1, BaseModule::$C1, 60);

        BackendModule::of($I)
            ->logout();
    }

    /**
     * @param BackendTester $I
     * @group UI22
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/304
     */
    public function testChangeModeToOverlay(BackendTester $I)
    {
        $I->wantToTest('Can I change the preview mode to “overlay”?');

        BackendModule::of($I)
            ->login();

        SetupModule::of($I)
            ->uninstallPlugin()
            ->installPlugin()
            ->activatePlugin()
            ->goThroughGetStartedTab(0.35, 'USD');

        PostModule::of($I)
            ->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'global default price', 0.35, 60);

        ModesModule::of($I)
            ->changePreviewMode('overlay');

        PostModule::of($I)
            ->checkTestPostForLaterPayElements(1, 'global default price', 0.69, 'USD',
                BaseModule::$T1, BaseModule::$C1, 60);

        BackendModule::of($I)
            ->logout();
    }

    /**
     * @param BackendTester $I
     * @group UI23
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/305
     */
    public function testCorrectShortcodesRenderedProperlyFreePost(BackendTester $I)
    {
        $I->wantToTest('Are correct shortcodes rendered properly within a free post?');

        BackendModule::of($I)
            ->login();

        SetupModule::of($I)
            ->uninstallPlugin()
            ->installPlugin()
            ->activatePlugin()
            ->goThroughGetStartedTab(0.35, 'USD');

        PostModule::of($I)
            ->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'individual price', 0.00, 60)
            ->createTestPost(BaseModule::$T2, BaseModule::$C2, null, 'individual price', 0.55, 60)
            ->checkTestPostForLaterPayElements(1, 'individual price', 0.00, 'USD',
                                                BaseModule::$T1, BaseModule::$C1, 60)
            ->checkTestPostForLaterPayElements(2, 'individual price', 0.55, 'USD',
                                                BaseModule::$T2, BaseModule::$C2, 60)
            ->checkIfCorrectShortcodeIsDisplayedCorrectly('post-1', 0.55);

        BackendModule::of($I)
            ->logout();
    }

    /**
     * @param BackendTester $I
     * @group UI24
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/306
     */
    public function testCorrectShortcodesRenderedProperlyPaidPost(BackendTester $I)
    {
        $I->wantToTest('Are correct shortcodes rendered properly within a paid post?');

        BackendModule::of($I)
            ->login();

        SetupModule::of($I)
            ->uninstallPlugin()
            ->installPlugin()
            ->activatePlugin()
            ->goThroughGetStartedTab(0.35, 'USD');

        PostModule::of($I)
            ->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'individual price', 0.10, 60)
            ->createTestPost(BaseModule::$T2, BaseModule::$C2, null, 'individual price', 0.55, 60)
            ->checkTestPostForLaterPayElements(1, 'individual price', 0.10, 'USD',
                BaseModule::$T1, BaseModule::$C1, 60)
            ->checkTestPostForLaterPayElements(2, 'individual price', 0.55, 'USD',
                BaseModule::$T2, BaseModule::$C2, 60)
            ->checkIfCorrectShortcodeIsDisplayedCorrectly('post-1', 0.55);

        BackendModule::of($I)
            ->logout();
    }

    /**
     * @param BackendTester $I
     * @group UI25
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/307
     */
    public function testWrongShortcodesRenderedProperlyFreePost(BackendTester $I)
    {
        $I->wantToTest('Are wrong shortcodes rendered properly within a free post?');

        BackendModule::of($I)
            ->login();

        SetupModule::of($I)
            ->uninstallPlugin()
            ->installPlugin()
            ->activatePlugin()
            ->goThroughGetStartedTab(0.35, 'USD');

        PostModule::of($I)
            ->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'individual price', 0.00, 60)
            ->createTestPost(BaseModule::$T3, BaseModule::$C3, null, 'individual price', 0.55, 60)
            ->checkTestPostForLaterPayElements(1, 'individual price', 0.00, 'USD',
                BaseModule::$T1, BaseModule::$C1, 60)
            ->checkTestPostForLaterPayElements(2, 'individual price', 0.55, 'USD',
                BaseModule::$T3, BaseModule::$C3, 60)
            ->checkIfWrongShortcodeIsDisplayedCorrectly('post-1', 0.55);

        BackendModule::of($I)
            ->logout();
    }

    /**
     * @param BackendTester $I
     * @group UI26
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/308
     */
    public function testWrongShortcodesRenderedProperlyPaidPost(BackendTester $I)
    {
        $I->wantToTest('Are wrong shortcodes rendered properly within a paid post?');

        BackendModule::of($I)
            ->login();

        SetupModule::of($I)
            ->uninstallPlugin()
            ->installPlugin()
            ->activatePlugin()
            ->goThroughGetStartedTab(0.35, 'USD');

        PostModule::of($I)
            ->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'individual price', 0.10, 60)
            ->createTestPost(BaseModule::$T3, BaseModule::$C3, null, 'individual price', 0.55, 60)
            ->checkTestPostForLaterPayElements(1, 'individual price', 0.10, 'USD',
                BaseModule::$T1, BaseModule::$C1, 60)
            ->checkTestPostForLaterPayElements(2, 'individual price', 0.55, 'USD',
                BaseModule::$T3, BaseModule::$C3, 60)
            ->checkIfWrongShortcodeIsDisplayedCorrectly('post-1', 0.55);

        BackendModule::of($I)
            ->logout();
    }

    /**
     * @param BackendTester $I
     * @group UI27
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/309
     */
    public function testSwitchToLiveMode(BackendTester $I)
    {
        $I->wantToTest('Can I switch to live mode?');

        BackendModule::of($I)
            ->login();

        SetupModule::of($I)
            ->uninstallPlugin()
            ->installPlugin()
            ->activatePlugin()
            ->goThroughGetStartedTab(0.35, 'USD');

        PostModule::of($I)
            ->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'individual price', 0.10, 60);

        ModesModule::of($I)
            ->switchToLiveMode();

        PostModule::of($I)
            ->checkTestPostForLaterPayElements(1, 'individual price', 0.10, 'USD',
                                                BaseModule::$T1, BaseModule::$C1, 60);

        BackendModule::of($I)
            ->logout();
    }

    /**
     * @param BackendTester $I
     * @group UI28
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/310
     */
    public function testSwitchToTestMode(BackendTester $I)
    {
        $I->wantToTest('Can I switch to test mode?');

        BackendModule::of($I)
            ->login();

        SetupModule::of($I)
            ->uninstallPlugin()
            ->installPlugin()
            ->activatePlugin()
            ->goThroughGetStartedTab(0.35, 'USD');

        PostModule::of($I)
            ->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'individual price', 0.10, 60);

        ModesModule::of($I)
            ->switchToTestMode();

        PostModule::of($I)
            ->checkTestPostForLaterPayElements(1, 'individual price', 0.10, 'USD',
                                                BaseModule::$T1, BaseModule::$C1, 60);

        BackendModule::of($I)
            ->logout();
    }
}