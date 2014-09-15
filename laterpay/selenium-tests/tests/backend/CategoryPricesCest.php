<?php

use \BackendTester;

/**
 * Class CategoryPricesCest
 */
class CategoryPricesCest {

    /**
     * @param \BackendTester $I
     * @group UI7
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/290
     */
    public function testCheckCategoryPriceAutoAppliedIfNewCategoryPriceCreatedCest(BackendTester $I) {
        $I->wantToTest('Is a category default price automatically applied to a post with global default price, if a
                        new category default price is created?');

        BackendModule::of($I)
            ->login();

        SetupModule::of($I)
            ->uninstallPlugin()
            ->installPlugin()
            ->activatePlugin()
            ->goThroughGetStartedTab(0.35, 'USD');

        CategoryModule::of($I)
            ->createTestCategory('Uncategorized')
            ->createTestCategory(BaseModule::$CAT1);

        PostModule::of($I)
            ->createTestPost(BaseModule::$T1, BaseModule::$C1, 'category-2', 'global default price', 0.35, 60);

        CategoryDefaultPriceModule::of($I)
            ->createCategoryDefaultPrice(BaseModule::$CAT1, 0.28);

        //TODO: Clarify post param 'test post 1'
        PostModule::of($I)
            ->checkTestPostForLaterPayElements($I->getVar('post')[0], 'category default price', 0.28, 'USD',
                                               BaseModule::$T1, BaseModule::$C1, 60);

        BackendModule::of($I)
            ->logout();
    }

    /**
     * @param \BackendTester $I
     * @group UI8
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/291
     */
    public function testCheckIfCategoryPriceAppliedToPostIfChangedCest(BackendTester $I) {
        $I->wantToTest('Can I change a category default price and is it applied to existing posts?');

        BackendModule::of($I)
            ->login();

        SetupModule::of($I)
            ->uninstallPlugin()
            ->installPlugin()
            ->activatePlugin()
            ->goThroughGetStartedTab(0.35, 'USD');

        CategoryModule::of($I)
            ->createTestCategory('Uncategorized')
            ->createTestCategory(BaseModule::$CAT1);

        CategoryDefaultPriceModule::of($I)
            ->createCategoryDefaultPrice(BaseModule::$CAT1, 0.28);

        PostModule::of($I)
            ->createTestPost(BaseModule::$T1, BaseModule::$C1, 'category-2', 'category default price', null, 60);

        CategoryDefaultPriceModule::of($I)
            ->changeCategoryDefaultPrice(BaseModule::$CAT1, '0.10');

        PostModule::of($I)
            ->checkTestPostForLaterPayElements($I->getVar('post')[0], 'category default price', '0.10', 'USD',
                                               BaseModule::$T1, BaseModule::$C1, 60);

        BackendModule::of($I)
            ->logout();
    }

    /**
     * @param \BackendTester $I
     * @group UI9
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/292
     */
    public function testCheckIfCategoryPriceAppliedToPostWithGlobalDefaultPriceCest(BackendTester $I) {
        $I->wantToTest('Is a category default price automatically applied to a post with global default price, if a
                        a post is assigned to a category with global default price?');

        BackendModule::of($I)
            ->login();

        SetupModule::of($I)
            ->uninstallPlugin()
            ->installPlugin()
            ->activatePlugin()
            ->goThroughGetStartedTab(0.35, 'USD');

        CategoryModule::of($I)
            ->createTestCategory('Uncategorized')
            ->createTestCategory(BaseModule::$CAT1);

        PostModule::of($I)
            ->createTestPost(BaseModule::$T1, BaseModule::$C1, 'category-2', 'global default price', 0.35, 60);

        CategoryDefaultPriceModule::of($I)
            ->createCategoryDefaultPrice(BaseModule::$CAT1, 0.28);

        PostModule::of($I)
            ->checkTestPostForLaterPayElements($I->getVar('post')[0], 'category default price', 0.28, 'USD',
                                               BaseModule::$T1, BaseModule::$C1, 60);

        BackendModule::of($I)
            ->logout();
    }


    /**
     * @param \BackendTester $I
     * @group UI10
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/293
     */
    public function testCheckPricesApplyingWithCategoryPriceDeletedCest(BackendTester $I) {
        $I->wantToTest('Is the higher of two remaining category default prices automatically applied to a post
                        with a category default price, if the currently applied category default price is deleted?');

        BackendModule::of($I)
            ->login();

        SetupModule::of($I)
            ->uninstallPlugin()
            ->installPlugin()
            ->activatePlugin()
            ->goThroughGetStartedTab(0.35, 'USD');

        CategoryModule::of($I)
            ->createTestCategory('Uncategorized')
            ->createTestCategory(BaseModule::$CAT1)
            ->createTestCategory(BaseModule::$CAT2)
            ->createTestCategory(BaseModule::$CAT3);

        CategoryDefaultPriceModule::of($I)
            ->createCategoryDefaultPrice(BaseModule::$CAT1, 0.49)
            ->createCategoryDefaultPrice(BaseModule::$CAT2, 0.69)
            ->createCategoryDefaultPrice(BaseModule::$CAT3, 0.89);

        PostModule::of($I)
            ->createTestPost(BaseModule::$T1, BaseModule::$C1,
                             array( BaseModule::$CAT1, BaseModule::$CAT2, BaseModule::$CAT3),
                             'category default price', 0.49, 60);

        CategoryDefaultPriceModule::of($I)
            ->deleteCategoryDefaultPrice(BaseModule::$CAT1);

        PostModule::of($I)
            ->checkTestPostForLaterPayElements($I->getVar('post')[0], 'category default price', 0.89, 'USD',
                                               BaseModule::$T1, BaseModule::$C1, 60);

        BackendModule::of($I)
            ->logout();
    }

    /**
     * @param \BackendTester $I
     * @group UI11
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/294
     */
    public function testCheckPricesApplyingWithPostUnassignedFromCategoryCest(BackendTester $I) {
        $I->wantToTest('Is the higher of two remaining category default prices automatically applied to a post
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
            ->createTestCategory('Uncategorized')
            ->createTestCategory(BaseModule::$CAT1)
            ->createTestCategory(BaseModule::$CAT2)
            ->createTestCategory(BaseModule::$CAT3);

        CategoryDefaultPriceModule::of($I)
            ->createCategoryDefaultPrice(BaseModule::$CAT1, 0.49)
            ->createCategoryDefaultPrice(BaseModule::$CAT2, 0.69)
            ->createCategoryDefaultPrice(BaseModule::$CAT3, 0.89);

        PostModule::of($I)
            ->createTestPost(BaseModule::$T1, BaseModule::$C1,
                             array( BaseModule::$CAT1, BaseModule::$CAT2, BaseModule::$CAT3),
                             'category default price', null, 60)
            ->unassignPostFromCategory(BaseModule::$T1, BaseModule::$CAT1)
            ->checkTestPostForLaterPayElements($I->getVar('post')[0], 'category default price', 0.89, 'USD',
                                               BaseModule::$T1, BaseModule::$C1, 60);

        BackendModule::of($I)
            ->logout();
    }

    /**
     * @param \BackendTester $I
     * @group UI12
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/295
     */
    public function testCheckGlobalPriceApplyingIfUsedCategoryPriceDeletedCest(BackendTester $I) {
        $I->wantToTest('Is the global default price automatically applied to a post with category default price, if
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
            ->createTestCategory('Uncategorized')
            ->createTestCategory(BaseModule::$CAT1);

        CategoryDefaultPriceModule::of($I)
            ->createCategoryDefaultPrice(BaseModule::$CAT1, 0.49);

        PostModule::of($I)
            ->createTestPost(BaseModule::$T1, BaseModule::$C1, 'category-2', 'category default price', null, 60);

        CategoryDefaultPriceModule::of($I)
            ->deleteCategoryDefaultPrice(BaseModule::$CAT1);

        PostModule::of($I)
            ->checkTestPostForLaterPayElements($I->getVar('post')[0], 'global default price', 0.35, 'USD',
                                               BaseModule::$T1, BaseModule::$C1, 60);

        BackendModule::of($I)
            ->logout();
    }
}