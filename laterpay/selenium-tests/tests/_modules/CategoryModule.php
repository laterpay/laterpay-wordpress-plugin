<?php

class CategoryModule extends BaseModule {

    //in
    public static $URL = '/wp-admin/edit-tags.php?taxonomy=category';
    public static $elementCategoryNameId = '#tag-name';
    public static $elementSubmitId = '#submit';
    public static $elementCategoriesTableSelector = 'table[class="wp-list-table widefat fixed tags"]';
    //expected
    public static $expectedMessage = 'The Categories Table on this page now contains a category named';

    public function createTestCategory($category_name)
    {
        $I = $this->BackendTester;
        $I->amOnPage(self::$URL);
        $I->fillField(self::$elementCategoryNameId, $category_name);
        $I->click(self::$elementSubmitId);
        $I->see($category_name, self::$elementCategoriesTableSelector);

        return $this;
    }
}