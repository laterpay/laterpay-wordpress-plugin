<?php

class PostModule extends BaseModule {

    //pages
    public static $pagePostNew = '/wp-admin/post-new.php';
    public static $pagePostList = '/wp-admin/edit.php';
    public static $pagePostEdit = '/wp-admin/post.php?post={post}&action=edit';
    public static $pagePostFrontView = '/?p={post}';
    //fields
    public static $fieldTitle = '#title';
    public static $fieldContent = '#content_ifr';
    public static $fieldTeaser = '#laterpay_teaser_content';
    public static $fieldPrice = '#post-price';
    public static $contentId = '#content';
    public static $teaserContentId = '#postcueeditor';
    //mcetabs
    public static $contentText = '#content-html';
    public static $teaserContentText = '#postcueeditor-html';
    //links

    public static $linkGlobalDefaultPrice = '#lp_use-global-default-price';
    public static $linkIndividualPrice = '#lp_use-individual-price';
    public static $linkAddMedia = '#insert-media-button';
    public static $linkPublish = '#publish';
    public static $linkViewPost = '#view-post-btn a';
    public static $linkPreviewSwitcher = '.switch-handle';
    public static $linkPreviewSwitcherElement = 'preview_post_checkbox';
    //should be visible
    public static $visibleLaterpayWidgetContainer = '#laterpay-widget-container';
    public static $visibleLaterpayStatistics = '.lp_post-statistics-details';
    public static $visibleLaterpayPurchaseButton = 'a[class="lp_purchase-link lp_button"]';
    public static $visibleLaterpayPurchaseLink = 'lp_purchase-link';
    public static $visibleLaterpayPurchaseBenefits = '.lp_benefits';
    public static $visibleLaterpayTeaserContent = '.lp_teaser-content';
    public static $visibleLaterpayContent = '.entry-content';
    public static $visibleInTablePostTitle = '.post-title';
    public static $visibleInTablePostPrice = '.post-price';
    public static $pageListPriceCol = 'td[class="post_price column-post_price"]';
    public static $pageListPricetypeCol = 'td[class="post_price_type column-post_price_type"]';
    //messages
    public static $messageShortcodeError = 'Shortcode error message';

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

        $content = str_replace(array("\r", "\n"), '', $content);

        $I->amGoingTo('Set post content');
        $I->click(PostModule::$contentText);
        $I->fillField(PostModule::$contentId, $content);

        //create teaser content
        if ($teaser) {
            $teaser_content = $this->_createTeaserContent($content, $teaser);

            $I->amGoingTo('Set teaser content');
            $I->click(PostModule::$teaserContentText);
            $I->fillField(PostModule::$teaserContentId, $teaser_content);
        }

        if ($categories) {
            $I->amGoingTo('Set categories to post');
            if (is_array($categories)) {
                foreach ($categories as $category_name) {
                    $this->assignPostToCategory($category_name);
                }
            } else {
                $this->assignPostToCategory($categories);
            }
        }

        switch ($price_type) {

            case 'global default price':
                $I->amGoingTo('Choose global default price type');
                $I->tryClick($I, PostModule::$linkGlobalDefaultPrice);
                break;

            case 'category default price':
                $I->amGoingTo('Choose category default price type');
                $I->tryClick($I, PostModule::$linkCategoryPrice);
                break;

            case 'individual price':
                $I->amGoingTo('Choose individual price type');
                $I->click(PostModule::$linkIndividualPrice);
                BackendModule::of($I)
                        ->validatePrice(PostModule::$fieldPrice);
                //we can change only individual price
                if ($price) {
                    $I->amGoingTo('Set price');
                    $I->fillField(PostModule::$fieldPrice, $price);
                }
                break;

            case 'individual dynamic price':
                $I->amGoingTo('Choose individual dynamic price type');
                break;

            default:
                break;
        }

        if ($files) {
            $I->amGoingTo('Attach files to post');
            //TODO: implement multiply files insertion and correct upload
            $I->attachFile(PostModule::$linkAddMedia, $files);
        }

        $I->amGoingTo('Publish post');
        $I->click(PostModule::$linkPublish);
        $I->wait(PostModule::$veryShortTimeout);

        $this->_storeCreatedPostId();

        $I->amOnPage(PostModule::$pagePostList);

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

        $I->amGoingTo('Edit post');
        if ((int) $post > 0) {

            $I->amOnPage(str_replace('{post}', $post, PostModule::$pagePostEdit));
        } elseif ($title) {

            $I->amOnPage(PostModule::$pagePostList);
            $I->click($title);
        };

        if ($title)
            $I->seeInField(PostModule::$fieldTitle, $title);

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

        if ((int) $price > 0) {

            $I->amGoingTo('Switch Preview toggle to “Visitor”');
            $I->click(PostModule::$linkViewPost);
            if (!$I->tryCheckbox($I, PostModule::$linkPreviewSwitcherElement))
                $I->click(PostModule::$linkPreviewSwitcher);
            $I->seeElementInDOM(PostModule::$visibleLaterpayStatistics); /* It`s not a best way to check, such as hidden elements will pass the test too. But used iframe doesn`t has a name attribute, so there`s no way to switch to it (see $I->switchToIFrame usage). */
            $I->see($currency, PostModule::$visibleLaterpayPurchaseButton);
            $I->see($price, PostModule::$visibleLaterpayPurchaseButton);
            //$I->see(substr($content, 0, 60), PostModule::$visibleLaterpayTeaserContent);

            $I->amGoingTo('Switch Preview toggle to “Admin”');
            if ($I->tryCheckbox($I, PostModule::$linkPreviewSwitcherElement))
                $I->click(PostModule::$linkPreviewSwitcher);
            $I->seeElementInDOM(PostModule::$visibleLaterpayStatistics); /* It`s not a best way to check, such as hidden elements will pass the test too. But used iframe doesn`t has a name attribute, so there`s no way to switch to it (see $I->switchToIFrame usage). */
            $I->cantSee(PostModule::$visibleLaterpayPurchaseButton);
            //$I->see(substr($content, 0, 255)); //while in admin mode text filled with amount <br\> inside, there`s no way to have text comparsion
            $I->cantSee(substr($content, 0, 60), PostModule::$visibleLaterpayTeaserContent);

            $I->amGoingTo('Go to the Post Overview page');
            $I->amOnPage(PostModule::$pagePostList);
            $I->see($price, PostModule::$pageListPriceCol);
            $I->see($price_type, PostModule::$pageListPricetypeCol);

            $I->amGoingTo('Check If plugin is tested in live mode');
            if (!ModesModule::of($I)->checkIsTestMode()) {

                $previewModeTeaserOnly = ModesModule::of($I)->checkPreviewMode();
                $I->amGoingTo('Check post on front');
                BackendModule::of($I)->logout();
                $I->amOnPage(str_replace('{post}', $post, PostModule::$pagePostFrontView));
                $I->cantSeeElementInDOM(PostModule::$visibleLaterpayStatistics);
                $I->see($currency, PostModule::$visibleLaterpayPurchaseButton);
                $I->see($price, PostModule::$visibleLaterpayPurchaseButton);
                if ($previewModeTeaserOnly) {

                    $I->seeElement(PostModule::$visibleLaterpayPurchaseLink);
                    $I->see($price, 'a');
                    $I->see($currency, 'a');
                } else {

                    $I->seeElement(PostModule::$visibleLaterpayPurchaseBenefits);
                    $I->see(substr($content, 0, 60), PostModule::$visibleLaterpayTeaserContent);
                    $I->see($price, 'a');
                    $I->see($currency, 'a');
                };
            };
        } else {

            $I->amGoingTo('
                Skip because of empty price:
                Switch Preview toggle to “Visitor”.
                Switch Preview toggle to “Admin”.
                Go to the Post Overview page.
                Check If plugin is tested in live mode.
                ');
        };

        return $this;
    }

    /**
     * P.23
     * @param $post
     * @return $this
     */
    public function purchasePost($post, $price = null, $currency = null, $title = null, $content = null) {

        $I = $this->BackendTester;

        $url = $I->grabFromCurrentUrl();

        $previewModeTeaserOnly = ModesModule::of($I)->checkPreviewMode();

        BackendModule::of($I)->logout();

        $I->amGoingTo('Open the respective post');

        $I->amOnPage(str_replace('{post}', $post, PostModule::$pagePostFrontView));

        $I->cantSeeElementInDOM(PostModule::$visibleLaterpayStatistics); /* It`s not a best way to check, such as hidden elements will pass the test too. But used iframe doesn`t has a name attribute, so there`s no way to switch to it (see $I->switchToIFrame usage). */

        if ($currency)
            $I->see($currency, PostModule::$visibleLaterpayPurchaseButton);

        if ($price)
            $I->see($price, PostModule::$visibleLaterpayPurchaseButton);

        if ($previewModeTeaserOnly) {

            $I->seeElement(PostModule::$visibleLaterpayPurchaseLink);
            $I->see($price, 'a');
            $I->see($currency, 'a');
        } else {

            $I->seeElement(PostModule::$visibleLaterpayPurchaseBenefits);
            $I->see(substr($content, 0, 60), PostModule::$visibleLaterpayTeaserContent);
            $I->see($price, 'a');
            $I->see($currency, 'a');
        };

        $I->see(substr($content, 0, 60), PostModule::$visibleLaterpayTeaserContent);

        $I->amGoingTo('Click the LaterPay Purchase Button and purchase the content');

        $I->click(PostModule::$visibleLaterpayPurchaseButton);

        $I->cantSeeElementInDOM(PostModule::$visibleLaterpayStatistics); /* It`s not a best way to check, such as hidden elements will pass the test too. But used iframe doesn`t has a name attribute, so there`s no way to switch to it (see $I->switchToIFrame usage). */

        $I->cantSeeElement(PostModule::$visibleLaterpayPurchaseButton);

        $I->cantSeeElement(PostModule::$visibleLaterpayPurchaseLink);

        $I->cantSeeElement(PostModule::$visibleLaterpayPurchaseBenefits);

        $I->see($content, PostModule::$visibleLaterpayTeaserContent);

        $I->see($content, PostModule::$visibleLaterpayContent);

        BackendModule::of($I)->login();

        $I->amOnPage($url);

        return $this;
    }

    /**
     * @param $category
     * @param null $post
     * @return $this
     */
    public function unassignPostFromCategory($category, $post = null) {

        $I = $this->BackendTester;

        if ((int) $post > 0) {
            $I->amOnPage(str_replace('{post}', $post, PostModule::$pagePostEdit));

            $option = '#in-' . $category;
            $I->uncheckOption($option);

            $I->click(PostModule::$linkPublish);
            $I->wait(PostModule::$veryShortTimeout);
        } else {
            $option = '#in-' . $category;
            $I->uncheckOption($option);
        }

        return $this;
    }

    /**
     * @param $category
     * @param null $post
     * @return $this
     */
    public function assignPostToCategory($category, $post = null) {

        $I = $this->BackendTester;

        if ((int) $post > 0) {
            $I->amOnPage(str_replace('{post}', $post, PostModule::$pagePostEdit));

            $option = '#in-' . $category;
            $I->checkOption($option);

            $I->click(PostModule::$linkPublish);
            $I->wait(PostModule::$veryShortTimeout);
        } else {
            $option = '#in-' . $category;
            $I->checkOption($option);
        }

        return $this;
    }

    /**
     * P.23
     * @param $post
     * @return $this
     */
    private function _storeCreatedPostId() {

        $I = $this->BackendTester;

        $postId = null;

        $url = $I->grabFromCurrentUrl();

        $url = substr($url, strpos($url, '?') + 1);

        parse_str($url, $array);

        if (isset($array['post']))
            $postId = $array['post'];

        $I->setVar('post', $postId);

        return $postId;
    }

    /**
     * @param $post
     * @param $price
     * @return $this
     */
    public function changeIndividualPrice($post, $price) {
        $I = $this->BackendTester;

        $I->amGoingTo('Open post for edit');
        $I->amOnPage(str_replace('{post}', $post, PostModule::$pagePostEdit));

        $I->amGoingTo('Change individual price');
        $I->click(PostModule::$linkIndividualPrice);
        $I->fillField(PostModule::$fieldPrice, $price);

        $I->amGoingTo('Update post');
        $I->click(PostModule::$linkPublish);
        $I->wait(PostModule::$veryShortTimeout);

        return $this;
    }

    /**
     * P.40
     * Check if Files are Protected
     * @param $post
     * @param $price
     * @param $files
     * @return $this
     */
    public function checkIfFilesAreProtected($post, $price, $files) {
        $I = $this->BackendTester;

        $I->amGoingTo('Open post for edit');
        $I->amOnPage(str_replace('{post}', $post, PostModule::$pagePostEdit));

        $I->click(self::$linkViewPost);
        $I->click(self::$linkPreviewSwitcher);

        return $this;
    }

    /**
     * P. 41
     * Check if a Correct Shortcode is Displayed Correctly
     * @param $post
     * @param $price
     * @return $this
     */
    public function checkIfCorrectShortcodeIsDisplayedCorrectly($post, $price) {
        $I = $this->BackendTester;


        if ($price > 0) {
            $I->amGoingTo('Open post for edit');
            $I->amOnPage(str_replace('{post}', $post, PostModule::$pagePostEdit));

            $I->click(self::$linkViewPost);
            $I->click(self::$linkPreviewSwitcher);
            $I->see(self::$messageShortcodeError, self::$messageShortcodeError);
        }


        return $this;
    }

    /**
     * P.42
     * Check if a Wrong Shortcode is Displayed Correctly
     * @param $post
     * @param $price
     * @return $this
     */
    public function checkIfWrongShortcodeIsDisplayedCorrectly($post, $price) {
        $I = $this->BackendTester;

        if ($price > 0) {
            $I->amGoingTo('Open post for edit');
            $I->amOnPage(str_replace('{post}', $post, PostModule::$pagePostEdit));

            $I->click(self::$linkViewPost);
            $I->click(self::$linkPreviewSwitcher);
            $I->see(self::$messageShortcodeError, self::$messageShortcodeError);
        }


        return $this;
    }

    /**
     * @param $content
     * @param $teaser
     * @return string
     */
    private function _createTeaserContent($content, $teaser) {
        $teaser_content = explode(' ', strip_tags($content), $teaser);
        array_pop($teaser_content);
        return join(' ', $teaser_content) . '...';
    }

}

