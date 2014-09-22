<?php

class CategoryModule extends BaseModule {

    //in
    public static $URL = '/wp-admin/edit-tags.php?taxonomy=category';
    public static $elementCategoryNameId = '#tag-name';
    public static $elementSubmitId = '#submit';
    public static $elementCategoriesTableSelector = '.wp-list-table.widefat.fixed.tags';
    //expected
    public static $expectedMessage = 'The Categories Table on this page now contains a category named';

    /**
     * P.36
     * Create Test Category
     * @param $category_name
     * @return $this
     * @author Alex Vahura <avahura@scnsoft.com>
     */
    public function createTestCategory($category_name) {
        $I = $this->BackendTester;

        $I->amOnPage(self::$URL);
        $I->fillField(self::$elementCategoryNameId, $category_name);
        $I->click(self::$elementSubmitId);
        $I->see($category_name, self::$elementCategoriesTableSelector);
        $this->_storeCreatedCategoryId();

        return $this;
    }

    /**
     * @param $post
     * @return $this
     */
    private function _storeCreatedCategoryId() {
        $I = $this->BackendTester;

        $I->click('.row-title');

        $tagId = null;

        $url = $I->grabFromCurrentUrl();

        $url = substr($url, strpos($url, '?') + 1);

        parse_str($url, $array);

        if (isset($array['tag_ID']))
            $tagId = $array['tag_ID'];

        $I->setVar('category_id', $tagId);

        return $tagId;
    }

}

