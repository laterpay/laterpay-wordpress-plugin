<?php

class PostModule extends BaseModule {

    //page
    public static $pagePostNew = '/wp-admin/post-new.php';
    public static $pagePostList = '/wp-admin/edit.php';
    //fields
    public static $fieldTitle = '#title';
    public static $fieldContent = '#content_ifr';
    public static $fieldTeaser = '#laterpay_teaser_content';
    public static $fieldPrice = '#post-price';
    //links
    public static $linkGlobalDefaultPrice = '#lp_use-global-default-price';
    public static $linkIndividualPrice = '#lp_use-individual-price';
    public static $linkAddMedia = '#insert-media-button';
    public static $linkPublish = '#publish';
    public static $linkViewPost = '#view-post-btn a';
    public static $linkPreviewSwitcher = 'span[class="switch-handle"]';
    public static $linkPreviewSwitcherElement = 'preview_post_checkbox';
    //should be visible
    public static $visibleLaterpayWidgetContainer = '#laterpay-widget-container';
    public static $visibleLaterpayStatistics = 'div[class="lp_post-statistics"]';
    public static $visibleLaterpayPurchaseButton = 'a[class="lp_purchase-link lp_button"]';
    public static $visibleLaterpayTeaserContent = '.lp_teaser-content';
    public static $visibleLaterpayContent = '.entry-content';
    public static $visibleInTablePostTitle = '.post-title';
    public static $visibleInTablePostPrice = '.post-price';
    public static $pageListPriceCol = 'td[class="post_price.column-post_price"]';
    public static $pageListPricetypeCol = 'td[class="post_price_type column-post_price_type"]';

    /**
     * P.26
     * @param $title
     * @param $content
     * @param null $categories
     * @param null $price_type
     * @param null $price
     * @param null $teaser
     * @param null $files
     * @return $this
     */
    public function createTestPost($title, $content, $categories = null, $price_type = null, $price = null, $teaser = null, $files = null) {

        $I = $this->BackendTester;

        $I->amOnPage(PostModule::$pagePostNew);
        $I->amGoingTo('Set post title');
        $I->fillField(PostModule::$fieldTitle, $title);

        $I->amGoingTo('Set post content');
        $I->click(PostModule::$fieldContent);
        $content = substr($content, 0, 60);
        $I->executeJS(" tinymce.activeEditor.selection.setContent('$content'); ");

        //create teaser content
        if ($teaser) {
            $I->amGoingTo('Set teaser content');
            $teaser_content = $this->_createTeaserContent($content, $teaser);
            $I->fillField(PostModule::$fieldTitle, $teaser_content);
        }

        //TODO: probably move it into separate method
        switch ($price_type) {

            case 'global default price':
                $I->amGoingTo('Choose global default price type');
                $I->click($I, PostModule::$linkGlobalDefaultPrice);
                break;

            case 'category default price':
                $I->amGoingTo('Choose category default price type');
                break;

            case 'individual price':
                $I->amGoingTo('Choose individual price type');
                $I->click(PostModule::$linkIndividualPrice);
                BackendModule::of($I)
                        ->priceValidation(PostModule::$fieldPrice);
                break;

            case 'individual dynamic price':
                $I->amGoingTo('Choose individual dynamic price type');
                break;

            default:
                break;
        }

        if ($price) {
            $I->amGoingTo('Set price');
            $I->fillField(PostModule::$fieldPrice, $price);
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
            $I->attachFile(PostModule::$linkAddMedia, $files);
        }

        $I->amGoingTo('Publish post');
        $I->click(PostModule::$linkPublish);
        $I->wait(PostModule::$veryShortTimeout);

        $I->amOnPage(PostModule::$pagePostList);

        //TODO: Same names can present on post page
        $I->see($title, PostModule::$visibleInTablePostTitle);

        if ($price)
            $I->see($price, PostModule::$visibleInTablePostPrice);

        $I->see($title);

        return $this;
    }

    /**
     * P.38-29
     * Check Post for LaterPay Elements
     * @param $post
     * @param null $price_type
     * @param null $price
     * @param null $currency
     * @param null $categories
     * @param $title
     * @param null $content
     * @param $teaser
     *
     * @return $this
     */
    public function checkTestPostForLaterPayElements($post, $price_type = null, $price = null, $currency = null, $title = null, $content = null, $teaser = null) {

        $I = $this->BackendTester;

        $I->amGoingTo('Open post from list');
        $I->amOnPage(PostModule::$pagePostList);
        $I->click($post, PostModule::$visibleInTablePostTitle);
        switch ($price_type) {

            case 'global default price':
                $I->click(PostModule::$linkGlobalDefaultPrice);
                break;

            case 'category default price':
                break;

            case 'individual price':
                $I->click(PostModule::$linkIndividualPrice);
                break;

            case 'individual dynamic price':
                $I->click(PostModule::$linkIndividualPrice);
                $I->see(PostModule::$visibleLaterpayWidgetContainer);
                break;

            default:
                break;
        }

        $I->amGoingTo('Switch Preview toggle to “Visitor”');
        $I->click(PostModule::$linkViewPost);
        if (!$I->hCheckbox($I, PostModule::$linkPreviewSwitcherElement))
            $I->click(PostModule::$linkPreviewSwitcher);
        $I->seeElementInDOM(PostModule::$visibleLaterpayStatistics);
        $I->see($currency, PostModule::$visibleLaterpayPurchaseButton);
        $I->see($price, PostModule::$visibleLaterpayPurchaseButton);
        $I->see(substr($content, 0, 60), PostModule::$visibleLaterpayTeaserContent);

        $I->amGoingTo('Switch Preview toggle to “Admin”');
        if ($I->hCheckbox($I, PostModule::$linkPreviewSwitcherElement))
            $I->click(PostModule::$linkPreviewSwitcher);
        $I->seeElement(PostModule::$visibleLaterpayStatistics);
        $I->dontSeeElement(PostModule::$visibleLaterpayPurchaseButton);
        $I->see($content, PostModule::$visibleLaterpayContent);
        $I->dontSee(substr($content, 0, 60), PostModule::$visibleLaterpayTeaserContent);

        $I->amGoingTo('Go to the Post Overview page');
        $I->amOnPage(PostModule::$pagePostList);
        $I->see($price, PostModule::$pageListPriceCol);
        $I->see($price_type, PostModule::$pageListPricetypeCol);

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
     * P.23
     * @param $post
     * @return $this
     */
    public function purchasePost($post) {

        $I = $this->BackendTester;

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

    private function _createTeaserContent($content, $teaser) {
        $teaser_content = explode(' ', strip_tags($content), $teaser);
        array_pop($teaser_content);
        return join(' ', $teaser_content) . '...';
    }

}

