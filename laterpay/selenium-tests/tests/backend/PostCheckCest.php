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
            ->activatePlugin();

        PostModule::of($I)
            ->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'individual price', 0.00, 60);

        PostModule::of($I)
            ->checkTestPostForLaterPayElements('post-1', 'individual price', 0.00, 'USD',
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
            ->activatePlugin();

        PostModule::of($I)
            ->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'individual price', 0.40, 60);

        PostModule::of($I)
            ->checkTestPostForLaterPayElements('post-1', 'individual price', 0.40, 'USD',
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

        SetupModule::of($I)
            ->installPlugin()
            ->activatePlugin();

        PostModule::of($I)
            ->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'individual price', 0.40, null);

        PostModule::of($I)
            ->checkTestPostForLaterPayElements('post-1', 'individual price', 0.40, 'USD',
                BaseModule::$T1, BaseModule::$C1, 60);

        PostModule::of($I)
            ->checkTestPostForLaterPayElements('post-2', 'individual price', 0.40, 'USD',
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
            ->activatePlugin();

        SetupModule::of($I)
            ->goThroughGetStartedTab(0.35, 'USD');

        //TODO: Probably individual dynamic price
        PostModule::of($I)
            ->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'individual price',
                             'starts at 0.85 and goes to 0.05 after 5 days', null);

        PostModule::of($I)
            ->checkTestPostForLaterPayElements('post-1', 'individual price', 0.85, 'USD',
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

        SetupModule::of($I)
            ->uninstallPlugin()
            ->installPlugin()
            ->activatePlugin();

        SetupModule::of($I)
            ->goThroughGetStartedTab(0.35, 'USD');

        PostModule::of($I)
            ->createTestPost(BaseModule::$T1, BaseModule::$C1, BaseModule::$CAT1, 'global default price', 0.35, null);

        PostModule::of($I)
            ->checkTestPostForLaterPayElements('post-1', 'global default price', 0.35, 'USD',
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
            ->activatePlugin();

        SetupModule::of($I)
            ->goThroughGetStartedTab(0.35, 'USD');

        CategoryModule::of($I)
            ->createTestCategory(BaseModule::$CAT1);

        CategoryDefaultPriceModule::of($I)
            ->createCategoryDefaultPrice(BaseModule::$CAT1, 0.49);

        PostModule::of($I)
            ->createTestPost('post-1', BaseModule::$C1, BaseModule::$CAT1, 'category default price', 0.49, 60);

        BackendModule::of($I)
            ->logout();
    }
}