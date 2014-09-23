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
     * @author Alex Tsumarov <atsumarov@scnsoft.com>
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
     * @author Alex Tsumarov <atsumarov@scnsoft.com>
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
     * @author Alex Tsumarov <atsumarov@scnsoft.com>
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
     * @author Alex Tsumarov <atsumarov@scnsoft.com>
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
     * @author Alex Tsumarov <atsumarov@scnsoft.com>
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
     * @author Alex Tsumarov <atsumarov@scnsoft.com>
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
     * @group UI7
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/290
     * @author Alex Vahura <avahura@scnsoft.com>
     */
    public function testCheckCategoryPriceAutoAppliedIfNewCategoryPriceCreatedCest(BackendTester $I) {
        $I->wantToTest('UI7: Is a category default price automatically applied to a post with global default price, if a
                        new category default price is created?');

        BackendModule::of($I)
                ->login();

        SetupModule::of($I)
                ->uninstallPlugin()
                ->installPlugin()
                ->activatePlugin()
                ->goThroughGetStartedTab(0.35, 'USD');

        CategoryModule::of($I)
                ->createTestCategory(BaseModule::$CAT1);

        PostModule::of($I)
                ->createTestPost(BaseModule::$T1, BaseModule::$C1, $I->getVar('category_id'), 'global default price', 0.35, 60);

        CategoryDefaultPriceModule::of($I)
                ->createCategoryDefaultPrice(BaseModule::$CAT1, 0.28);

        PostModule::of($I)
                ->checkTestPostForLaterPayElements($I->getVar('post'), 'category default price', 0.28, 'USD', BaseModule::$T1, BaseModule::$C1, 60);

        BackendModule::of($I)
                ->logout();
    }

    /**
     * @param \BackendTester $I
     * @group UI8
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/291
     * @author Alex Vahura <avahura@scnsoft.com>
     */
    public function testCheckIfCategoryPriceAppliedToPostIfChangedCest(BackendTester $I) {
        $I->wantToTest('UI8: Can I change a category default price and is it applied to existing posts?');

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
                ->createCategoryDefaultPrice(BaseModule::$CAT1, 0.28);

        PostModule::of($I)
                ->createTestPost(BaseModule::$T1, BaseModule::$C1, $I->getVar('category_id'), 'category default price', null, 60);

        CategoryDefaultPriceModule::of($I)
                ->changeCategoryDefaultPrice(BaseModule::$CAT1, '0.10');

        PostModule::of($I)
                ->checkTestPostForLaterPayElements($I->getVar('post'), 'category default price', '0.10', 'USD', BaseModule::$T1, BaseModule::$C1, 60);

        BackendModule::of($I)
                ->logout();
    }

    /**
     * @param \BackendTester $I
     * @group UI9
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/292
     * @author Alex Vahura <avahura@scnsoft.com>
     */
    public function testCheckIfCategoryPriceAppliedToPostWithGlobalDefaultPriceCest(BackendTester $I) {
        $I->wantToTest('UI9: Is a category default price automatically applied to a post with global default price, if a
                        a post is assigned to a category with global default price?');

        BackendModule::of($I)
                ->login();

        SetupModule::of($I)
                ->uninstallPlugin()
                ->installPlugin()
                ->activatePlugin()
                ->goThroughGetStartedTab(0.35, 'USD');

        CategoryModule::of($I)
                ->createTestCategory(BaseModule::$CAT1);

        PostModule::of($I)
                ->createTestPost(BaseModule::$T1, BaseModule::$C1, $I->getVar('category_id'), 'global default price', 0.35, 60);

        CategoryDefaultPriceModule::of($I)
                ->createCategoryDefaultPrice(BaseModule::$CAT1, 0.28);

        PostModule::of($I)
                ->checkTestPostForLaterPayElements($I->getVar('post'), 'category default price', 0.28, 'USD', BaseModule::$T1, BaseModule::$C1, 60);

        BackendModule::of($I)
                ->logout();
    }

    /**
     * @param \BackendTester $I
     * @group UI10
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/293
     * @author Alex Vahura <avahura@scnsoft.com>
     */
    public function testCheckPricesApplyingWithCategoryPriceDeletedCest(BackendTester $I) {
        $I->wantToTest('UI10: Is the higher of two remaining category default prices automatically applied to a post
                        with a category default price, if the currently applied category default price is deleted?');

        BackendModule::of($I)
                ->login();

        SetupModule::of($I)
                ->uninstallPlugin()
                ->installPlugin()
                ->activatePlugin()
                ->goThroughGetStartedTab(0.35, 'USD');

        CategoryModule::of($I)
                ->createTestCategory(BaseModule::$CAT1);
        $category1 = $I->getVar('category_id');

        CategoryModule::of($I)
                ->createTestCategory(BaseModule::$CAT2);
        $category2 = $I->getVar('category_id');

        CategoryModule::of($I)
                ->createTestCategory(BaseModule::$CAT3);
        $category3 = $I->getVar('category_id');

        CategoryDefaultPriceModule::of($I)
                ->createCategoryDefaultPrice(BaseModule::$CAT1, 0.49)
                ->createCategoryDefaultPrice(BaseModule::$CAT2, 0.69)
                ->createCategoryDefaultPrice(BaseModule::$CAT3, 0.89);

        PostModule::of($I)
                ->createTestPost(BaseModule::$T1, BaseModule::$C1, array($category1, $category2, $category3), 'category default price', 0.49, 60);

        CategoryDefaultPriceModule::of($I)
                ->deleteCategoryDefaultPrice(BaseModule::$CAT1);

        PostModule::of($I)
                ->checkTestPostForLaterPayElements($I->getVar('post'), 'category default price', 0.69, 'USD', BaseModule::$T1, BaseModule::$C1, 60);

        BackendModule::of($I)
                ->logout();
    }

    /**
     * @param \BackendTester $I
     * @group UI11
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/294
     * @author Alex Vahura <avahura@scnsoft.com>
     */
    public function testCheckPricesApplyingWithPostUnassignedFromCategoryCest(BackendTester $I) {
        $I->wantToTest('UI11: Is the higher of two remaining category default prices automatically applied to a post
                        with a category default price, if the post is unassigned from the category whose
                        category default price is currently applied?');

        BackendModule::of($I)
                ->login();

        SetupModule::of($I)
                ->uninstallPlugin()
                ->installPlugin()
                ->activatePlugin()
                ->goThroughGetStartedTab(0.35, 'USD');

        CategoryModule::of($I)
                ->createTestCategory(BaseModule::$CAT1);
        $category1 = $I->getVar('category_id');

        CategoryModule::of($I)
                ->createTestCategory(BaseModule::$CAT2);
        $category2 = $I->getVar('category_id');

        CategoryModule::of($I)
                ->createTestCategory(BaseModule::$CAT3);
        $category3 = $I->getVar('category_id');

        CategoryDefaultPriceModule::of($I)
                ->createCategoryDefaultPrice(BaseModule::$CAT1, 0.49)
                ->createCategoryDefaultPrice(BaseModule::$CAT2, 0.69)
                ->createCategoryDefaultPrice(BaseModule::$CAT3, 0.89);

        PostModule::of($I)
                ->createTestPost(BaseModule::$T1, BaseModule::$C1, array($category1, $category2, $category3), 'category default price', null, 60)
                ->unassignPostFromCategory($category1, $I->getVar('post'))
                ->checkTestPostForLaterPayElements($I->getVar('post'), 'category default price', 0.69, 'USD', BaseModule::$T1, BaseModule::$C1, 60);

        BackendModule::of($I)
                ->logout();
    }

    /**
     * @param \BackendTester $I
     * @group UI12
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/295
     * @author Alex Vahura <avahura@scnsoft.com>
     */
    public function testCheckGlobalPriceApplyingIfUsedCategoryPriceDeletedCest(BackendTester $I) {
        $I->wantToTest('UI12: Is the global default price automatically applied to a post with category default price, if
                        the currently used category default price is deleted and the post is not assigned to any
                        other category with category default price?');

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
                ->createTestPost(BaseModule::$T1, BaseModule::$C1, $I->getVar('category_id'), 'category default price', null, 60);

        CategoryDefaultPriceModule::of($I)
                ->deleteCategoryDefaultPrice(BaseModule::$CAT1);

        PostModule::of($I)
                ->checkTestPostForLaterPayElements($I->getVar('post'), 'global default price', 0.35, 'USD', BaseModule::$T1, BaseModule::$C1, 60);

        BackendModule::of($I)
                ->logout();
    }

    /**
     * @param \BackendTester $I
     * @group UI13
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/296
     * @author Alex Vahura <avahura@scnsoft.com>
     */
    public function testCreatePriceWithZeroIndividualPrice(BackendTester $I) {
        $I->wantToTest('UI13: Can I create a free post, i.e. a post with an individual price of 0.00?');

        BackendModule::of($I)
                ->login();

        SetupModule::of($I)
                ->uninstallPlugin()
                ->installPlugin()
                ->activatePlugin()
                ->goThroughGetStartedTab(0.35, 'USD');

        PostModule::of($I)
                ->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'individual price', '0.00', 60)
                ->checkTestPostForLaterPayElements($I->getVar('post'), 'individual price', '0.00', 'USD', BaseModule::$T1, BaseModule::$C1, 60);

        BackendModule::of($I)
                ->logout();
    }

    /**
     * @param \BackendTester $I
     * @group UI14
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/297
     * @author Alex Vahura <avahura@scnsoft.com>
     */
    public function testCreatePaidPostWithIndividualPrice(BackendTester $I) {
        $I->wantToTest('UI14: Can I create a paid post with individual price,
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
                ->checkTestPostForLaterPayElements($I->getVar('post'), 'individual price', '0.40', 'USD', BaseModule::$T1, BaseModule::$C1, 60);

        BackendModule::of($I)
                ->logout();
    }

    /**
     * @param \BackendTester $I
     * @group UI15
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/298
     * @author Alex Vahura <avahura@scnsoft.com>
     */
    public function testIfTeaserContentAutomaticallyGeneratedForPosts(BackendTester $I) {
        $I->wantToTest('UI15: Is the teaser content automatically generated both for existing and new posts?');

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
                ->checkTestPostForLaterPayElements($testPost1, 'individual price', '0.35', 'USD', BaseModule::$T1, BaseModule::$C1, 60)
                ->checkTestPostForLaterPayElements($testPost2, 'individual price', '0.40', 'USD', BaseModule::$T1, BaseModule::$C1, 60);

        BackendModule::of($I)
                ->logout();
    }

    /**
     * @param \BackendTester $I
     * @group UI16
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/299
     * @author Alex Vahura <avahura@scnsoft.com>
     */
    public function testCreatePaidPostWithDynamicPricing(BackendTester $I) {
        $I->wantToTest('UI16: Can I create a paid post with dynamic pricing?');

        BackendModule::of($I)
                ->login();

        SetupModule::of($I)
                ->uninstallPlugin()
                ->installPlugin()
                ->activatePlugin()
                ->goThroughGetStartedTab(0.35, 'USD');

        $dynamic_price = array(
            'start_price' => 0.85,
            'period' => 5,
            'end_price' => 0.05
        );

        PostModule::of($I)
                ->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'dynamic individual price', $dynamic_price, null)
                ->checkTestPostForLaterPayElements($I->getVar('post'), 'dynamic individual price', 0.85, 'USD', BaseModule::$T1, BaseModule::$C1, 60);

        BackendModule::of($I)
                ->logout();
    }

    /**
     * @param \BackendTester $I
     * @group UI17
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/300
     * @author Alex Vahura <avahura@scnsoft.com>
     */
    public function testCreatePaidPostWithGlobalDefaultPrice(BackendTester $I) {
        $I->wantToTest('UI17: Can I create a paid post with global default price?');

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
                ->createTestPost(BaseModule::$T1, BaseModule::$C1, $I->getVar('category_id'), 'global default price', 0.35)
                ->checkTestPostForLaterPayElements($I->getVar('post'), 'global default price', 0.35, 'USD', BaseModule::$T1, BaseModule::$C1, 60);

        BackendModule::of($I)
                ->logout();
    }

    /**
     * @param \BackendTester $I
     * @group UI18
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/301
     * @author Alex Vahura <avahura@scnsoft.com>
     */
    public function testCreatePaidPostWithCategoryDefaultPrice(BackendTester $I) {
        $I->wantToTest('UI18: Can I create a paid post with category default price?');

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
                ->createTestPost(BaseModule::$T1, BaseModule::$C1, $I->getVar('category_id'), 'category default price', 0.49, 60)
                ->checkTestPostForLaterPayElements($I->getVar('post'), 'category default price', 0.49, 'USD', BaseModule::$T1, BaseModule::$C1, 60);

        BackendModule::of($I)
                ->logout();
    }

    /**
     * @param BackendTester $I
     * @group UI19
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/302
     * @author Alex Vahura <avahura@scnsoft.com>
     */
    public function testCheckPluginProtectFilesInPaidPost(BackendTester $I) {
        $I->wantToTest('UI19: Does the plugin protect files in a paid post?');

        BackendModule::of($I)
                ->login();

        SetupModule::of($I)
                ->uninstallPlugin()
                ->installPlugin()
                ->activatePlugin()
                ->goThroughGetStartedTab(0.35, 'USD');

        PostModule::of($I)
                ->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'global default price', 0.35, 60, PostModule::$samplePdfFile)
                ->checkTestPostForLaterPayElements($I->getVar('post'), 'global default price', 0.35, 'USD', BaseModule::$T1, BaseModule::$C1, 60)
                ->checkIfFilesAreProtected($I->getVar('post'), PostModule::$samplePdfFile);
    }

    /**
     * @param BackendTester $I
     * @group UI20
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/303
     * @author Alex Vahura <avahura@scnsoft.com>
     */
    public function testChangeIndividualPrice(BackendTester $I) {
        $I->wantToTest('UI20: Can I change the individual price?');

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
                ->checkTestPostForLaterPayElements($I->getVar('post'), 'individual price', 0.69, 'USD', BaseModule::$T1, BaseModule::$C1, 60);

        BackendModule::of($I)
                ->logout();
    }

    /**
     * @param \BackendTester $I
     * @group UI21
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/304
     * @author Alex Tsumarov <atsumarov@scnsoft.com>
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
     * @author Alex Tsumarov <atsumarov@scnsoft.com>
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
     * @author Alex Tsumarov <atsumarov@scnsoft.com>
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

        PostModule::of($I)->createTestPost(BaseModule::$T2, BaseModule::$C2, null, 'individual price', $_priceOne);
        $_testPost1 = $I->getVar('post');

        PostModule::of($I)->createTestPost(BaseModule::$T2, BaseModule::$C2, null, 'individual price', $_priceTwo);
        $_testPost2 = $I->getVar('post');

        PostModule::of($I)->checkTestPostForLaterPayElements($_testPost1, 'individual price', $_priceOne, $_currency, BaseModule::$T2);

        PostModule::of($I)->checkTestPostForLaterPayElements($_testPost2, 'individual price', $_priceTwo, $_currency, BaseModule::$T2);

        PostModule::of($I)->checkIfCorrectShortcodeIsDisplayedCorrectly($_testPost1, $_priceOne);
    }

    /**
     * @param \BackendTester $I
     * @group UI24
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/307
     * @author Alex Tsumarov <atsumarov@scnsoft.com>
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
     * @author Alex Tsumarov <atsumarov@scnsoft.com>
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

        PostModule::of($I)->createTestPost(BaseModule::$T3, BaseModule::$C3, null, 'individual price', $_priceOne);
        $_testPost1 = $I->getVar('post');

        PostModule::of($I)->createTestPost(BaseModule::$T3, BaseModule::$C3, null, 'individual price', $_priceTwo);
        $_testPost2 = $I->getVar('post');

        PostModule::of($I)->checkTestPostForLaterPayElements($_testPost1, 'individual price', $_priceOne, $_currency, BaseModule::$T3);

        PostModule::of($I)->checkTestPostForLaterPayElements($_testPost2, 'individual price', $_priceTwo, $_currency, BaseModule::$T3);

        PostModule::of($I)->checkIfWrongShortcodeIsDisplayedCorrectly($_testPost1, $_priceOne);
    }

    /**
     * @param \BackendTester $I
     * @group UI26
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/309
     * @author Alex Tsumarov <atsumarov@scnsoft.com>
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

        PostModule::of($I)->checkIfCorrectShortcodeIsDisplayedCorrectly($_testPost2, $_priceTwo);
    }

    /**
     * @param BackendTester $I
     * @group UI27
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/310
     * @author Alex Tsumarov <atsumarov@scnsoft.com>
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
     * @author Alex Tsumarov <atsumarov@scnsoft.com>
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
     * @param BackendTester $I
     * @group UI29
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/312
     * @author Alex Vahura <avahura@scnsoft.com>
     */
    public function testPriceInputsValidated(BackendTester $I) {
        $I->wantToTest('UI29: Are the price inputs validated?');

        BackendModule::of($I)
            ->login();

        SetupModule::of($I)
            ->uninstallPlugin()
            ->installPlugin()
            ->activatePlugin()
            ->goThroughGetStartedTab(0.35, 'USD');

        CategoryModule::of($I)
            ->createTestCategory(BaseModule::$CAT1);

        SetupModule::of($I)->validateGlobalPrice();

        CategoryDefaultPriceModule::of($I)
            ->createCategoryDefaultPrice(BaseModule::$CAT1, 0.35);

        CategoryDefaultPriceModule::of($I)->validateCategoryPrice();

        PostModule::of($I)->createTestPost(BaseModule::$T1, BaseModule::$C1, null, 'individual price', '0.35');

        PostModule::of($I)->validateIndividualPrice($I->getVar('post'));

        BackendModule::of($I)
            ->logout();
    }

    /**
     * @param \BackendTester $I
     * @group dev
     * @author Alex Tsumarov <atsumarov@scnsoft.com>
     */
    public function dev(BackendTester $I) {

        BackendModule::of($I)->login();

        ModesModule::of($I)->switchToTestMode();
    }

}

