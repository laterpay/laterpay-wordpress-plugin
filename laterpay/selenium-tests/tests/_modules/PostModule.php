<?php

class PostModule extends BaseModule {

    //page
    public static $pagePostNew                    = '/wp-admin/post-new.php';
    public static $pagePostList                   = '/wp-admin/edit.php';

    //fields
    public static $fieldTitle                     = '#title';
    public static $fieldContent                   = '#content_ifr';
    public static $fieldTeaser                    = '#laterpay_teaser_content';
    public static $fieldPrice                     = '#post-price';

    //links
    public static $linkGlobalDefaultPrice         = '#use-global-default-price';
    public static $linkIndividualPrice            = '#use-individual-price';
    public static $linkAddMedia                   = '#insert-media-button';
    public static $linkPublish                    = '#publish';
    public static $linkViewPost                   = '.ab-item';
    public static $linkPreviewSwitcher            = '#preview-post-toggle';

    //should be visible
    public static $visibleLaterpayWidgetContainer = '#laterpay-widget-container';
    public static $visibleLaterpayStatistics      = '#statistics';
    public static $visibleLaterpayPurchaseButton  = '.laterpay-purchase-link.laterpay-purchase-button';
    public static $visibleLaterpayTeaserContent   = '.laterpay-teaser-content';
    public static $visibleLaterpayContent         = '.entry-content';
    public static $visibleInTablePostTitle        = '.post-title';
    public static $visibleInTablePostPrice        = '.post-price';

    /**
     * @param $title
     * @param $content
     * @param null $categories
     * @param null $price_type
     * @param null $price
     * @param null $teaser
     * @param null $files
     * @return $this
     */
    public function createTestPost($title, $content, $categories = null,
                                   $price_type = null, $price = null, $teaser = null, $files = null) {
        $I = $this->BackendTester;

        $I->amOnPage(self::$pagePostNew);
        $I->amGoingTo('Set post title');
        $I->fillField(self::$fieldTitle, $title);

        $I->amGoingTo('Set post content');
        $I->click(self::$fieldContent);
        $I->executeJS(" tinymce.activeEditor.selection.setContent('$content'); ");

        //create teaser content
        if ($teaser) {
            $I->amGoingTo('Set teaser content');
            $teaser_content = $this->_createTeaserContent($content, $teaser);
            $I->fillField(self::$fieldTitle, $teaser_content);
        }

        //TODO: probably move it into separate method
        switch ($price_type) {

            case 'global default price':
                $I->amGoingTo('Choose global default price type');
                $I->click(self::$linkGlobalDefaultPrice);
                break;

            case 'category default price':
                $I->amGoingTo('Choose category default price type');
                break;

            case 'individual price':
                $I->amGoingTo('Choose individual price type');
                $I->click(self::$linkIndividualPrice);
                BackendModule::of($I)
                    ->priceValidation(self::$fieldPrice);
                break;

            case 'individual dynamic price':
                $I->amGoingTo('Choose individual dynamic price type');
                break;

            default:
                break;
        }

        if ($price) {
            $I->amGoingTo('Set price');
            $I->fillField(self::$fieldPrice, $price);
        }

        //TODO: need to check this logic
        if ($categories) {
            $I->amGoingTo('Set categories to post');
            if (!is_array($categories))
                $categories[] = $categories;

            foreach ($categories as $category_name) {
                $this->assignPostToCategory($title, $category_name);
            }
        }

        if ($files) {
            $I->amGoingTo('Attach files to post');
            //TODO: implement multiply files insertion and correct upload
            $I->attachFile(self::$linkAddMedia, $files);
        }

        $I->amGoingTo('Publish post');
        $I->click(self::$linkPublish);
        $I->wait(self::$veryShortTimeout);

        $I->amOnPage(self::$pagePostList);

        //TODO: Same names can present on post page
        $I->see($title, PostModule::$visibleInTablePostTitle);

        if ($price)
            $I->see($price, PostModule::$visibleInTablePostPrice);

        $I->see($title);

        return $this;
    }

    public function checkTestPostForLaterPayElements($post, $price_type, $price, $currency, $title, $content, $teaser) {
        $I = $this->BackendTester;

        //TODO: implement open post for editing

        switch ($price_type) {

            case 'global default price':
                $I->click(self::$linkGlobalDefaultPrice);
                break;

            case 'category default price':
                break;

            case 'individual price':
                $I->click(self::$linkIndividualPrice);
                break;

            case 'individual dynamic price':
                $I->click(self::$linkIndividualPrice);
                $I->see(self::$visibleLaterpayWidgetContainer);
                break;

            default:
                break;
        }

        $I->click(self::$linkViewPost);
        //TODO: need to check if toggle works on click
        $I->click(self::$linkPreviewSwitcher);

        $I->seeElement(self::$visibleLaterpayStatistics);
        //TODO: Implement check for correct price and currency
        $I->see($currency, self::$visibleLaterpayPurchaseButton);
        $I->see($price, self::$visibleLaterpayPurchaseButton);
        //TODO: clarify Preview Mode params check and implement

        //create teaser content
        $teaser_content = $this->_createTeaserContent($content, $teaser);
        $I->see($teaser_content, self::$visibleLaterpayTeaserContent);

        $I->click(self::$linkPreviewSwitcher);
        $I->seeElement(self::$visibleLaterpayStatistics);
        $I->dontSeeElement(self::$visibleLaterpayPurchaseButton);
        //TODO: clarify Preview Mode params check and implement
        $I->see($content, self::$visibleLaterpayContent);
        $I->dontSee($teaser_content, self::$visibleLaterpayTeaserContent);

        $I->amOnPage(self::$pagePostList);
        //TODO: Implement find price and price type for post in table
        $I->see($price, $post);
        $I->see($price_type, $post);

        //TODO: Implement logic if plugin tested in LIVE mode

        return $this;
    }

    /**
     * @param $post
     * @param $category
     * @return $this
     */
    public function unassignPostFromCategory($post, $category) {
        $I = $this->BackendTester;

        //TODO: implement category unassign from category

        return $this;
    }

    /**
     * @param $post
     * @param $category
     * @return $this
     */
    public function assignPostToCategory($post, $category) {
        $I = $this->BackendTester;

        //TODO: implement category assign to category

        return $this;
    }

    private function _createTeaserContent($content, $teaser)
    {
        $teaser_content = explode(' ', strip_tags($content), $teaser);
        array_pop($teaser_content);
        return join(' ', $teaser_content) . '...';
    }
}