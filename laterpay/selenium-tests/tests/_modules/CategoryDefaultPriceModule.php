<?php

class CategoryDefaultPriceModule extends BaseModule {

    //pricing tab elements
    public static $pricingAddCategoryButton = '#lp_add-category-link';
    public static $pricingCategorySelect = '#lp_category-prices .select2-choice';
    public static $pricingCategoryValue = '#lp_category-prices .lp_category-title';
    public static $pricingSaveLink = "#lp_category-prices .unsaved .lp_save-link";
    public static $pricingSaveLinkSaved = "#lp_category-prices .lp_save-link";
    public static $pricingCancelLink = "#lp_category-prices .unsaved .lp_cancel-link";
    public static $pricingChangeLink = "#lp_category-prices .lp_change-link";
    public static $pricingDeleteLink = "#lp_category-prices .lp_delete-link";
    public static $pricingPriceInput = "#lp_category-prices .unsaved .lp_number-input";
    public static $pricingPriceInputSaved = "#lp_category-prices .lp_number-input";
    //messages
    public static $messageCategoryPriceSave = "All posts in category {category_name} have a default price of {category_price}";
    public static $messageCategoryPriceDeleted = "The default price for category {category_name} was deleted.";
    public static $messageCategoryPriceChanged = "All posts in category {category_name} have a default price of {category_price}";

    /**
     * P.33
     * Create Category Default Price
     * @param $category_name
     * @param $category_default_price
     * @return $this
     * @author Alex Vahura <avahura@scnsoft.com>
     */
    public function createCategoryDefaultPrice($category_name, $category_default_price) {
        $I = $this->BackendTester;

        $I->amGoingTo('Open LaterPay plugin page pricing tab');
        $I->amOnPage(self::$baseUrl);
        $I->click(self::$adminMenuPluginButton);
        $I->click(self::$pluginPricingTab);

        $I->amGoingTo('Add category default price');
        $I->click(self::$pricingAddCategoryButton);
        $I->seeElement(self::$pricingCategorySelect);
        $I->seeElement(self::$pricingSaveLink);
        $I->seeElement(self::$pricingCancelLink);

        $I->amGoingTo('Cancel category default price');
        $I->click(self::$pricingCancelLink);
        $I->seeElement(self::$pricingAddCategoryButton);
        $I->dontSeeElement(self::$pricingCancelLink);
        $I->dontSeeElement(self::$pricingSaveLink);

        $I->amGoingTo('Add category default price');
        $I->click(self::$pricingAddCategoryButton);
        $I->seeElement(self::$pricingCategorySelect);
        $I->seeElement(self::$pricingSaveLink);
        $I->seeElement(self::$pricingCancelLink);

        $messageCategoryPriceSaveText = str_replace(
                array('{category_name}', '{category_price}'), array($category_name, $category_default_price), self::$messageCategoryPriceSave
        );

        $I->amGoingTo('Fill and save category default price');
        $I->click(self::$pricingCategorySelect);
        $I->wait(self::$veryShortTimeout);
        $I->click('.select2-results .select2-result');
        $I->fillField(self::$pricingPriceInput, $category_default_price);
        $I->click(self::$pricingSaveLink);
        $I->waitForText($messageCategoryPriceSaveText, self::$shortTimeout, self::$messageArea);
        $I->seeElement(self::$pricingChangeLink);
        $I->seeElement(self::$pricingDeleteLink);
        $I->dontSeeElement(self::$pricingCancelLink);
        $I->dontSeeElement(self::$pricingSaveLink);

        return $this;
    }

    /**
     * P.37
     * Delete category default price
     * @param $category_name
     * @return $this
     * @author Alex Vahura <avahura@scnsoft.com>
     */
    public function deleteCategoryDefaultPrice($category_name) {
        $I = $this->BackendTester;

        $I->amGoingTo('Open LaterPay plugin page pricing tab');
        $I->amOnPage(self::$baseUrl);
        $I->click(self::$adminMenuPluginButton);
        $I->click(self::$pluginPricingTab);

        $messageCategoryPriceDeleteText = str_replace(
                '{category_name}', $category_name, self::$messageCategoryPriceDeleted
        );

        $I->amGoingTo('Delete category default price');
        //TODO: implement deletion of concrete category
        $I->click(self::$pricingDeleteLink);
        $I->waitForText($messageCategoryPriceDeleteText, self::$shortTimeout, self::$messageArea);

        return $this;
    }

    /**
     * P.35
     * Change category default price
     * @param $category_name
     * @param $new_category_default_price
     * @return $this
     * @author Alex Vahura <avahura@scnsoft.com>
     */
    public function changeCategoryDefaultPrice($category_name, $new_category_default_price) {
        $I = $this->BackendTester;

        $I->amGoingTo('Open LaterPay plugin page pricing tab');
        $I->amOnPage(self::$baseUrl);
        $I->click(self::$adminMenuPluginButton);
        $I->click(self::$pluginPricingTab);

        $I->amGoingTo('Change category default price');
        //TODO: implement change of concrete category
        $I->click(self::$pricingChangeLink);
        $I->seeElement(self::$pricingCategorySelect);
        $I->seeElement(self::$pricingSaveLink);
        $I->seeElement(self::$pricingCancelLink);

        $I->amGoingTo('Fill and save category default price');
        $I->fillField(self::$pricingPriceInput, $new_category_default_price);
        $I->click(self::$pricingSaveLink);
        $I->seeElement(self::$pricingChangeLink);
        $I->seeElement(self::$pricingDeleteLink);
        $I->dontSeeElement(self::$pricingCancelLink);
        $I->dontSeeElement(self::$pricingSaveLink);
        $messageCategoryPriceChangeText = str_replace(
                array('{category_name}', '{category_price}'), array($category_name, $new_category_default_price), self::$messageCategoryPriceSave
        );
        $I->waitForText($messageCategoryPriceChangeText, self::$shortTimeout, self::$messageArea);

        return $this;
    }

    /**
     * To UI29
     * @author Alex Vahura <avahura@scnsoft.com>
     */
    public function validateCategoryPrice() {
        $I = $this->BackendTester;

        $I->amOnPage(self::$baseUrl);
        $I->click(self::$adminMenuPluginButton);
        $I->click(self::$pluginPricingTab);

        $I->amGoingTo('Validate Category Price');
        BackendModule::of($I)
                ->validatePrice(self::$pricingPriceInputSaved, self::$pricingChangeLink, self::$pricingSaveLinkSaved);
    }

}

