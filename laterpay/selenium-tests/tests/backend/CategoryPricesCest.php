<?php

use \BackendTester;

class CategoryPricesCest {

    /**
     * @param \BackendTester $I
     * @group UI10
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/294
     */
    public function testCheckPricesApplyingWithCategoryPriceDeletedCest(BackendTester $I) {

        SetupModule::of($I)
            ->installPlugin()
            ->activatePlugin();

        PluginModule::of($I)
            ->goThroughGetStartedTab($param1, $param2);

        CategoryModule::of($I)
            ->createTestCategory(BaseModule::$CAT1)
            ->createTestCategory(BaseModule::$CAT2)
            ->createTestCategory(BaseModule::$CAT3);

        PluginModule::of($I)
            ->createCategoryDefaultPrice(BaseModule::$CAT1, 0.49)
            ->createCategoryDefaultPrice(BaseModule::$CAT2, 0.69)
            ->createCategoryDefaultPrice(BaseModule::$CAT3, 0.89);

    }
}