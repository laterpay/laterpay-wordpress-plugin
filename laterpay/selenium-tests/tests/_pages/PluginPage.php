<?php

class PluginPage {

    public static $pluginPricingTab = 'a[text="Pricing"]';
    public static $adminMenuPluginButton = '#toplevel_page_laterpay-plugin';
    public static $pricingAddCategoryButton = '#add_category_button';
    public static $pricingCategorySelect = '#select2-drop-mask';
    public static $pricingSaveLink = ".edit-link .laterpay-save-link";
    public static $pricingCancelLink = ".edit-link .laterpay-cancel-link";
    public static $pluginBackLink = '/wp-admin/admin.php?page=laterpay-plugin';
    public static $laterpayChangeLink = 'a[class="edit-link laterpay-change-link"]';
    public static $globalDefaultPriceField = '#global-default-price';
    public static $laterpaySaveLink = '.laterpay-save-link';
    public static $laterpayCancelLink = '.laterpay-cancel-link';
    public static $globalPriceText = '#laterpay-global-price-text';
    public static $newGlobalDefaultPrice = '3';
    //expected
    public static $assertPluginListed = 'LaterPay';
    public static $assertNewPriceSet = 'Every post costs ';
    public static $assertNewPriceConfirmation = 'The global default price for all posts is ';
    public static $priceValidationArray = array(
        '0.15' => array('0,15', '0.15', '0,15 EUR', '0,15EUR'),
        '5.00' => array('0;89', '550', '8,00', '9.00', '10EUR', '10 EUR')
    );

    /**
     * Declare UI map for this page here. CSS or XPath allowed.
     * public static $usernameField = '#username';
     * public static $formSubmitButton = "#mainForm input[type=submit]";
     */

    /**
     * Basic route example for your current URL
     * You can append any additional parameter to URL
     * and use it in tests like: EditPage::route('/123-post');
     */
    public static function route($param) {
        return static::$URL . $param;
    }

}

