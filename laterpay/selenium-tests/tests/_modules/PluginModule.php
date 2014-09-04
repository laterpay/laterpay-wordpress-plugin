<?php

class PluginModule extends BaseModule {

    //admin area elements
    public static $adminMenuPluginButton    = '#toplevel_page_laterpay-plugin';

    //plugin tabs
    public static $pluginPricingTab         = 'a[text="Pricing"]';

    //pricing tab elements
    public static $pricingAddCategoryButton = '#add_category_button';
    public static $pricingCategorySelect = '#select2-drop-mask';
    public static $pricingCategorySelectOption = '#select2-result-label';
    public static $pricingSaveLink = ".edit-link .laterpay-save-link";
    public static $pricingCancelLink = ".edit-link .laterpay-cancel-link";

    public function goThroughGetStartedTab()
    {

    }

    public function createCategoryDefaultPrice($category_name, $category_default_price)
    {
        $I = $this->webguy;
        $I->amOnPage(self::$BaseUrl);
        $I->click(self::$adminMenuPluginButton);
        $I->click(self::$pluginPricingTab);

        $I->click(self::$pricingAddCategoryButton);
        $I->seeElement(self::$pricingCategorySelect);
        $I->seeElement(self::$pricingSaveLink);
        $I->seeElement(self::$pricingCancelLink);

        PriceModule::of($I)
            ->validatePrice();

        $I->click(self::$pricingAddCategoryButton);
        $I->seeElement(self::$pricingAddCategoryButton);
        $I->dontSeeElement(self::$pricingCancelLink);
        $I->dontSeeElement(self::$pricingSaveLink);

        $I->click(self::$pricingAddCategoryButton);
        $I->seeElement(self::$pricingCategorySelect);
        $I->seeElement(self::$pricingSaveLink);
        $I->seeElement(self::$pricingCancelLink);

        $I->click(self::$pricingCategorySelect);
        $I->click(self::$pricingCategorySelectOption);

        return $this;
    }
}