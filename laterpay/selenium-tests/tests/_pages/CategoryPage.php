<?php

class CategoryPage {

    //in
    public static $URL = '/wp-admin/edit-tags.php?taxonomy=category';
    public static $new_category_name = '#tag-name';
    public static $submit = '#submit';
    public static $categories_table_selector = 'table[class="wp-list-table widefat fixed tags"]';
    //expected
    public static $expectedMessage = 'The Categories Table on this page now contains a category named';

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

    public static function create($I, $name) {
        $I->amOnPage(self::$URL);
        $I->fillField(self::$new_category_name, $name);
        $I->click(self::$submit);
    }
}