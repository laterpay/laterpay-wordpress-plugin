<?php

class PostModule extends BaseModule {
    //links
    public static $linkPostListPage                = 'wp-admin/edit.php';
    public static $linkAddNewPostPage              = 'wp-admin/post-new.php';

    //selectors
    public static $selectorPostTitleInput          = 'input[name=post_title]';
    public static $selectorPostPrice               = 'input[name=post-price]';
    public static $selectorPublishButton           = '#publish';
    public static $selectorTeaserInput             = '#postcueeditor';
    public static $selectorTeaserTypeSwitcher      = '#postcueeditor-html';
    public static $selectorContentTypeSwitcher     = '#content-html';
    public static $selectorContentInput            = '#content';
    public static $selectorCategories              = '#categorychecklist';
    public static $selectorIndividualPrice         = '#lp_js_useIndividualPrice';
    public static $selectorRevenueModel            = '#lp_js_postPriceRevenueModel';

    //defaults
    public static $c_post_title                    = 'Test Post';
    public static $c_teaser                        = 200;
    public static $c_fulltext                      = 1000;
    public static $c_revenue_model_ppu             = 'PPU';
    public static $c_revenue_model_sis             = 'SIS';
    public static $c_price_ppu                     = 29;
    public static $c_price_sis                     = 259;
    public static $c_price_type_individual         = 'Individual';
    public static $c_price_type_individual_dynamic = 'Individual Dynamic';
    public static $c_price_type_category           = 'Category Default';
    public static $c_price_type_global             = 'Global Default';

    /**
     * Create post
     *
     * @param null|string $p_post_title
     * @param null|string $p_fulltext
     * @param null|string $p_teaser
     * @param null|string $p_revenue_model
     * @param null|int    $p_price
     * @param null|string $p_price_type
     * @param null|string $p_category
     *
     * @return $this
     */
    public function createPost( $p_post_title = null, $p_fulltext = null, $p_teaser = null, $p_revenue_model = null, $p_price = null, $p_price_type = null, $p_category = null ) {
        $I = $this->BackendTester;

        $I->amOnPage( self::$linkAddNewPostPage );
        //Set post title
        $I->fillField( self::$selectorPostTitleInput, $p_post_title ? $p_post_title : self::$c_post_title );

        //Prepare fulltext
        if ( ! isset( $p_fulltext ) ) {
            //Get content from file
            $p_fulltext = file_get_contents( './_data/content.txt' );
            $p_fulltext = str_replace( array( "\r", "\n" ), '', $p_fulltext );
        }

        //Set post content
        $I->click( self::$selectorContentTypeSwitcher );
        $I->fillField( self::$selectorContentInput, $p_fulltext );

        //$this->_createTeaserContent

        //create teaser content
        if ($teaser) {
            $teaser_content = $this->_createTeaserContent($content, $teaser);

            //Set teaser content
            $I->click(PostModule::$teaserContentText);
            $I->fillField(PostModule::$teaserContentId, $teaser_content);
        }

        if ($categories) {
            //Set categories to post
            if (is_array($categories)) {
                foreach ($categories as $category_id) {
                    $this->assignPostToCategory($category_id);
                }
            } else {
                $this->assignPostToCategory($categories);
            }
        }

        switch ($price_type) {

            case 'global default price':
                //Choose global default price type
                $I->click(PostModule::$linkGlobalDefaultPrice);
                break;

            case 'category default price':
                //Choose category default price typ
                $I->click(PostModule::$linkCategoryPrice);
                break;

            case 'individual price':
                //Choose individual price type
                $I->click(PostModule::$linkIndividualPrice);
                if ($price) {
                    //Set price
                    $I->fillField(PostModule::$fieldPrice, $price);
                }
                break;

            case 'dynamic individual price':
                //Choose individual dynamic price type

                if (is_array($price)) {
                    $start_price = $price['start_price'];
                    $period      = $price['period'];
                    $end_price   = $price['end_price'];

                    $I->click(PostModule::$linkIndividualPrice);
                    $I->click(PostModule::$linkDynamicPricing);
                    $I->executeJS("
                        var new_data = [
                            {x:0, y:$start_price},
                            {x:1, y:$start_price},
                            {x:$period, y:$end_price},
                            {x:30, y:$end_price}
                        ];
                        window.lpc.set_data(new_data);
                    ");
                }

                break;

            default:
                break;
        }

        if ($files) {
            //Attach files to post
            $I->click(PostModule::$linkAddMedia);
            $I->click('Upload Files', PostModule::$linkMediaRouter);
            $I->click(PostModule::$linkAttachFile);
            $I->attachFile(PostModule::$fileInput, $files);
            $I->click(PostModule::$linkAddFileLinkToContent);
        }

        //Publish post
        $I->click(PostModule::$linkPublish);
        $I->wait(PostModule::$veryShortTimeout);

        $this->_storeCreatedPostId();

        $I->amOnPage(PostModule::$pagePostList);

        $I->see($title);

        return $this;
    }

    /**
     * Check Post for LaterPay Elements
     * @param $post
     * @param null $price_type
     * @param null $price
     * @param null $currency
     * @param null $categories
     * @param $title
     * @param null $content
     * @param $teaser
     * @return $this
     */
    public function checkTestPostForLaterPayElements($post, $price_type = null, $price = null, $currency = null, $title = null, $content = null, $teaser = null) {

        $I = $this->BackendTester;

        $content = str_replace("\r\n", '', $content);

        //Check Post For LaterPay Elements
        if ((int) $post > 0) {
            $I->amOnPage(str_replace('{post}', $post, PostModule::$pagePostEdit));
        } elseif ($title) {
            $I->amOnPage(PostModule::$pagePostList);
            $I->click($title);
        };

        if ($title)
            $I->seeInField(PostModule::$fieldTitle, $title);

        $content = str_replace(array("\r", "\n"), '', $content);

        switch ($price_type) {

            case 'global default price':
                $I->click(PostModule::$linkGlobalDefaultPrice);
                break;

            case 'category default price':
                $I->click(PostModule::$linkCategoryPrice);
                break;

            case 'individual price':
                $I->click(PostModule::$linkIndividualPrice);
                break;

            case 'dynamic individual price':
                $I->click(PostModule::$linkIndividualPrice);
                $I->seeElement(PostModule::$visibleLaterpayWidgetContainer);
                break;

            default:
                break;
        }

        //Publish post
        $I->click(PostModule::$linkPublish);
        $I->wait(PostModule::$veryShortTimeout);

        //Edit post
        if ((int) $post > 0) {
            $I->amOnPage(str_replace('{post}', $post, PostModule::$pagePostEdit));
        } elseif ($title) {
            $I->amOnPage(PostModule::$pagePostList);
            $I->click($title);
        };

        if ($title)
            $I->seeInField(PostModule::$fieldTitle, $title);

        if ($price != '0.00') {

            $I->comment('Switch Preview toggle to “Visitor”');
            $I->click(PostModule::$linkViewPost);
            if ( $I->dontSeeCheckboxIsChecked(PostModule::$linkPreviewSwitcherElement) ) {
                $I->click(PostModule::$linkPreviewSwitcher);
            }
            $I->seeElementInDOM(PostModule::$visibleLaterpayStatistics);
            $I->seeInPageSource($currency);
            $I->seeInPageSource($price);
            $teaser_content = null;
            if ($teaser) {
                $teaser_content = $this->_createTeaserContent($content, $teaser);
                $I->see($teaser_content, PostModule::$visibleLaterpayTeaserContent);
            }

            $I->comment('Switch Preview toggle to “Admin”');
            if ( $I->seeCheckboxIsChecked($I, PostModule::$linkPreviewSwitcherElement) ) {
                $I->click(PostModule::$linkPreviewSwitcher);
            }
            $I->seeElementInDOM(PostModule::$visibleLaterpayStatistics);
            $I->waitForElementNotVisible(PostModule::$visibleLaterpayPurchaseButton, BaseModule::$shortTimeout);

            //Must be there, such as contect with short codes can`t be checked
            if ($content)
                $I->seeInPageSource($content);

            if ($teaser)
                $I->cantSee($teaser_content, PostModule::$visibleLaterpayTeaserContent);

            $I->comment('Go to the Post Overview page');
            $I->amOnPage(PostModule::$pagePostList);
            $I->see($price, PostModule::$pageListPriceCol);
            $I->see($price_type, PostModule::$pageListPricetypeCol);

            //Check If plugin is tested in live mode
            if (!ModesModule::of($I)->checkIsTestMode()) {

                $previewModeTeaserOnly = ModesModule::of($I)->checkPreviewMode();
                //Check post on front
                BackendModule::of($I)->logout();
                $I->amOnPage(str_replace('{post}', $post, PostModule::$pagePostFrontView));
                $I->cantSeeElementInDOM(PostModule::$visibleLaterpayStatistics);
                $I->see($currency, PostModule::$visibleLaterpayPurchaseButton);
                $I->see($price, PostModule::$visibleLaterpayPurchaseButton);
                if ($previewModeTeaserOnly) {

                    $I->comment('Teaser mode');
                    $I->seeElement(PostModule::$visibleLaterpayPurchaseLink);
                    $I->see($price, 'a');
                    $I->see($currency, 'a');
                } else {

                    $I->comment('Overlay mode');
                    $I->seeElement(PostModule::$visibleLaterpayPurchaseBenefits);
                    if ($teaser) {
                        $I->see($teaser_content, PostModule::$visibleLaterpayTeaserContent);
                    }
                    $I->see($price, 'a');
                    $I->see($currency, 'a');
                };
            };
        } else {
            //Skip because of empty price:
            //Switch Preview toggle to “Visitor”.
            //Switch Preview toggle to “Admin”.
            //Go to the Post Overview page.
            //Check If plugin is tested in live mode.
        };

        return $this;
    }

    /**
     * Purchase post
     * @param $post
     * @return $this
     */
    public function purchasePost($post, $price = null, $currency = null, $title = null, $content = null) {

        $I = $this->BackendTester;

        //purchase post

        $content = str_replace("\r\n", '', $content);

        $url = $I->grabFromCurrentUrl();

        $previewMode = ModesModule::of($I)->checkPreviewMode();

        BackendModule::of($I)->logout();

        $I->amOnPage(str_replace('{post}', $post, PostModule::$pagePostFrontView));

        if ($currency)
            $I->see($currency, PostModule::$visibleLaterpayPurchaseButton);

        if ($price != '0.00') {

            $I->cantSeeElementInDOM(PostModule::$visibleLaterpayStatistics);

            if ($price)
                $I->see($price, PostModule::$visibleLaterpayPurchaseButton);

            if ($previewMode == 'teaser_only') {

                $I->seeElement(PostModule::$visibleLaterpayPurchaseLink);
                $I->see($price, 'a');
                $I->see($currency, 'a');
            } elseif ($previewMode == 'overlay') {

                $I->seeElement(PostModule::$visibleLaterpayPurchaseBenefits);
                ////CHECK TEASER HERE
                $I->see($price, 'a');
                $I->see($currency, 'a');
            };

            //Click the LaterPay Purchase Button and purchase the content
            $this->purschaseAtServer($post);
            $I->cantSeeElementInDOM(PostModule::$visibleLaterpayStatistics);
            $I->cantSeeElement(PostModule::$visibleLaterpayPurchaseButton);
            $I->cantSeeElement(PostModule::$visibleLaterpayPurchaseLink);
            $I->cantSeeElement(PostModule::$visibleLaterpayPurchaseBenefits);
            $I->seeInPageSource($content);
        } else {
            //Skip because of empty price: Click the LaterPay Purchase Button and purchase the content.
        };

        BackendModule::of($I)->login();

        $I->amOnPage($url);

        return $this;
    }

    /**
     * Proceed with post purschase throught LaterPay Server
     * Can`t get iframe content with codeception. The iframe has no name attribute and target iframe placed into child iframe (document->iframe->iframe)
     * Can`t use javascript while error "Blocked a frame from accessing a cross-origin frame."
     * So used switching "WebDriver config url"
     * As note: $I->executeJS(" document.getElementsByTagName('iframe')[0].contentDocument.getElementById('id_username').value = 'atsumarov@scnsoft.com'; ");
     * @param $category_id
     * @param null $post
     * @return $this
     */
    public function purschaseAtServer($post) {

        $I = $this->BackendTester;

        BackendModule::of($I)->logout();

        //Purshase the post
        //It must be there. Cause of switching domain issue.
        $I->amOnPage(str_replace('{post}', $post, PostModule::$pagePostFrontView));

        $laterpayPath      = (string) $I->executeJS(PostModule::$lpServerLinkJsGetter);
        $laterpayPathArray = (array) parse_url($laterpayPath);
        $laterpayDomain    = "{$laterpayPathArray['scheme']}://{$laterpayPathArray['host']}/";
        $laterpayPage      = str_replace($laterpayDomain, '', $laterpayPath);

        $I->setDomain($laterpayDomain);
        $I->amOnPage($laterpayPage);
        $I->wait(PostModule::$averageTimeout);

        $I->click(PostModule::$lpServerVisitorLoginClass);
        $I->click(PostModule::$lpServerVisitorLoginLink);
        $I->wait(BaseModule::$averageTimeout);

        $I->switchToIFrame(PostModule::$lpServerVisitorLoginFrameName);
        $I->waitForElement(PostModule::$lpServerVisitorEmailField, BaseModule::$averageTimeout);
        $I->fillField(PostModule::$lpServerVisitorEmailField, PostModule::$lpServerVisitorEmailValue);
        $I->fillField(PostModule::$lpServerVisitorPasswordField, PostModule::$lpServerVisitorPasswordValue);
        $I->click(PostModule::$lpServerVisitorLoginBtn);

        $I->wait(PostModule::$shortTimeout);
        $I->click(PostModule::$lpServerVisitorBuyBtn);

        $I->setDomain();
        $I->amOnPage(str_replace('{post}', $post, PostModule::$pagePostFrontView));

        return $this;
    }

    /**
     * Unassign post from category
     * @param $category
     * @param null $post
     * @return $this
     */
    public function unassignPostFromCategory($category_id, $post = null) {

        $I = $this->BackendTester;

        if ((int) $post > 0) {
            $I->amOnPage(str_replace('{post}', $post, PostModule::$pagePostEdit));

            $option = '#in-category-' . $category_id;
            $I->uncheckOption($option);

            $I->click(PostModule::$linkPublish);
            $I->wait(PostModule::$veryShortTimeout);
        } else {
            $option = '#in-category-' . $category_id;
            $I->uncheckOption($option);
        }

        return $this;
    }

    /**
     * Assign post to category
     * @param $category_id
     * @param null $post
     * @return $this
     */
    public function assignPostToCategory($category_id, $post = null) {

        $I = $this->BackendTester;

        if ((int) $post > 0) {
            $I->amOnPage(str_replace('{post}', $post, PostModule::$pagePostEdit));

            $option = '#in-category-' . $category_id;
            $I->checkOption($option);

            $I->click(PostModule::$linkPublish);
            $I->wait(PostModule::$veryShortTimeout);
        } else {
            $option = '#in-category-' . $category_id;
            $I->checkOption($option);
        }

        return $this;
    }

    /**
     * Store created post ID
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
     * Change Individual Price
     * @param $post
     * @param $price
     * @return $this
     */
    public function changeIndividualPrice($post, $price) {
        $I = $this->BackendTester;

        //Open post for edit
        $I->amOnPage(str_replace('{post}', $post, PostModule::$pagePostEdit));

        //Change individual price
        $I->click(PostModule::$linkIndividualPrice);
        $I->fillField(PostModule::$fieldPrice, $price);

        //Update post
        $I->click(PostModule::$linkPublish);
        $I->wait(PostModule::$veryShortTimeout);

        return $this;
    }

    /**
     * Check if Files are Protected
     * @param $post
     * @param $file_name
     * @return $this
     */
    public function checkIfFilesAreProtected($post, $file_name) {
        $I = $this->BackendTester;

        //Open post for edit
        $I->amOnPage(str_replace('{post}', $post, PostModule::$pagePostEdit));

        $I->click(self::$linkViewPost);

        //selected admin
        $I->seeElement(self::$linkFileLink);
        $parsed = explode('.', $file_name);
        $link   = $I->executeJS("var link = jQuery('a:contains(" . $parsed[0] . ")').attr('href'); return link;");

        $I->amOnPage($link);
        $I->wait(1);
        //TODO: check that this is PDF file

        BackendModule::of($I)
            ->logout();

        $I->amOnPage($link);
        $I->wait(1);
        $I->see('0');

        return $this;
    }

    /**
     * Check if a Correct Shortcode is Displayed Correctly
     * @param $post
     * @param $price
     * @return $this
     */
    public function checkIfCorrectShortcodeIsDisplayedCorrectly($post, $price) {
        $I = $this->BackendTester;

        //Check how correct shortcode displayed
        if ($price > 0) {

            $I->amOnPage(str_replace('{post}', $post, PostModule::$pagePostEdit));

            $I->click(PostModule::$linkViewPost);
            if ((int) $price > 0)
                $I->click(PostModule::$linkPreviewSwitcher); //In case of zero price stat tab not displayed

            $I->see($price, PostModule::$linkShortCode);
            $I->seeInPageSource('?p=' . $post, PostModule::$linkShortCode);
        }

        return $this;
    }

    /**
     * Check if a Wrong Shortcode is Displayed Correctly
     * @param $post
     * @param $price
     * @return $this
     */
    public function checkIfWrongShortcodeIsDisplayedCorrectly($post, $price) {

        $I = $this->BackendTester;

        //Check how wrong shortcode displayed

        if ($price > 0) {

            $I->amOnPage(str_replace('{post}', $post, PostModule::$pagePostEdit));

            $I->click(self::$linkViewPost);
            $I->click(self::$linkPreviewSwitcher);
            $I->seeElement(self::$messageShortcodeError);
        }

        return $this;
    }

    /**
     * Create teaser content
     *
     * @param string $content
     * @param int    $teaser  number or words in teaser
     *
     * @return string
     */
    private function _createTeaserContent( $content, $teaser ) {
        if ( ! $content || $teaser < 1 ) {
            return '';
        }
        $teaser_content = explode( ' ', strip_tags( $content ), $teaser - 1 );
        return join( ' ', $teaser_content ) . '...';
    }
}
