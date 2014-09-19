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
    public static $fieldPrice = 'input[name="post-price"]';
    public static $contentId = '#content';
    public static $teaserContentId = '#postcueeditor';
    public static $fileInput = 'input[type="file"]';
    //mcetabs
    public static $contentText = '#content-html';
    public static $teaserContentText = '#postcueeditor-html';
    //links
    public static $linkGlobalDefaultPrice = '#lp_use-global-default-price';
    public static $linkIndividualPrice = '#lp_use-individual-price';
    public static $linkDynamicPricing = '#lp_use-dynamic-pricing';
    public static $linkCategoryPrice = '#lp_use-category-default-price';
    public static $linkAddMedia = '#insert-media-button';
    public static $linkMediaRouter = '.media-router';
    public static $linkAttachFile = '#__wp-uploader-id-1';
    public static $linkAddFileLinkToContent = '.media-toolbar-primary .media-button-insert';
    public static $linkPublish = '#publish';
    public static $linkViewPost = '#view-post-btn a';
    public static $linkPreviewSwitcher = '.switch-handle';
    public static $linkPreviewSwitcherElement = 'preview_post_checkbox';
    public static $linkShortCode = 'a[class="lp_purchase-link-without-function lp_button"]';
    public static $linkFileLink = 'a[href*="wp-admin/admin-ajax.php?action=laterpay_load_files"]';
    //should be visible
    public static $visibleLaterpayWidgetContainer = '#lp_dynamic-pricing-widget-container';
    public static $visibleLaterpayStatistics = '.lp_post-statistics-details';
    public static $visibleLaterpayPurchaseButton = 'a[class="lp_purchase-link lp_button"]';
    public static $visibleLaterpayPurchaseLink = '.lp_purchase-link';
    public static $visibleLaterpayPurchaseBenefits = '.lp_benefits';
    public static $visibleLaterpayTeaserContent = '.lp_teaser-content';
    public static $visibleLaterpayContent = '.entry-content';
    public static $visibleInTablePostTitle = '.post-title';
    public static $visibleInTablePostPrice = '.post-price';
    public static $pageListPriceCol = 'td[class="post_price column-post_price"]';
    public static $pageListPricetypeCol = 'td[class="post_price_type column-post_price_type"]';
    //messages
    public static $messageShortcodeError = '.laterpay-shortcode-error';
    //purschase at LaterPay server
    public static $lpServerLinkJsGetter = " var str = jQuery('a[class=\"lp_purchase-link lp_button\"]').last().attr('data-laterpay'); return str; ";
    public static $lpServerVisitorLoginLink = 'Log in to LaterPay';
    public static $lpServerVisitorLoginFrameName = 'wrapper';
    public static $lpServerVisitorEmailField = 'username';
    public static $lpServerVisitorEmailValue = 'atsumarov@scnsoft.com';
    public static $lpServerVisitorPasswordField = 'password';
    public static $lpServerVisitorPasswordValue = 'atsumarov@scnsoft.com1';
    public static $lpServerVisitorLoginBtn = 'Log In';
    public static $lpServerVisitorBuyBtn = '#nextbuttons';
    //file
    public static $samplePdfFile = 'pdf-sample.pdf';

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
                foreach ($categories as $category_id) {
                    $this->assignPostToCategory($category_id);
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
                /* disable price validation for now
                  BackendModule::of($I)
                  ->validatePrice(PostModule::$fieldPrice);
                 */
                //we can change only individual price
                if ($price) {
                    $I->amGoingTo('Set price');
                    $I->fillField(PostModule::$fieldPrice, $price);
                }
                break;

            case 'dynamic individual price':
                $I->amGoingTo('Choose individual dynamic price type');

                if (is_array($price)) {
                    $start_price = $price['start_price'];
                    $period = $price['period'];
                    $end_price = $price['end_price'];

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
            $I->amGoingTo('Attach files to post');
            $I->click(PostModule::$linkAddMedia);
            $I->click('Upload Files', PostModule::$linkMediaRouter);
            $I->click(PostModule::$linkAttachFile);
            $I->attachFile(PostModule::$fileInput, $files);
            $I->click(PostModule::$linkAddFileLinkToContent);
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

        $content = str_replace("\r\n", '', $content);

        $I->amGoingTo('Check Post For LaterPay Elements');
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
                $I->tryClick($I, PostModule::$linkCategoryPrice);
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

        $I->amGoingTo('Publish post');
        $I->click(PostModule::$linkPublish);
        $I->wait(PostModule::$veryShortTimeout);

        $I->amGoingTo('Edit post');

        if ((int) $post > 0) {
            $I->amOnPage(str_replace('{post}', $post, PostModule::$pagePostEdit));
        } elseif ($title) {
            $I->amOnPage(PostModule::$pagePostList);
            $I->click($title);
        };

        if ($title)
            $I->seeInField(PostModule::$fieldTitle, $title);

        if ($price != '0.00') {
            $I->amGoingTo('Switch Preview toggle to “Visitor”');
            $I->click(PostModule::$linkViewPost);
            if (!$I->tryCheckbox($I, PostModule::$linkPreviewSwitcherElement))
                $I->click(PostModule::$linkPreviewSwitcher);
            $I->seeElementInDOM(PostModule::$visibleLaterpayStatistics); /* It`s not a best way to check, such as hidden elements will pass the test too. But used iframe doesn`t has a name attribute, so there`s no way to switch to it (see $I->switchToIFrame usage). */
            $I->see($currency, PostModule::$visibleLaterpayPurchaseButton);
            $I->see($price, PostModule::$visibleLaterpayPurchaseButton);
            $teaser_content = null;
            if ($teaser) {
                $teaser_content = $this->_createTeaserContent($content, $teaser);
                $I->see($teaser_content, PostModule::$visibleLaterpayTeaserContent);
            }

            $I->amGoingTo('Switch Preview toggle to “Admin”');
            if ($I->tryCheckbox($I, PostModule::$linkPreviewSwitcherElement))
                $I->click(PostModule::$linkPreviewSwitcher);
            $I->seeElementInDOM(PostModule::$visibleLaterpayStatistics);
            $I->cantSee(PostModule::$visibleLaterpayPurchaseButton);

            //Must be there, such as contect with short codes can`t be checked
            if ($content)
                $I->seeInPageSource($content);

            if ($teaser)
                $I->cantSee($teaser_content, PostModule::$visibleLaterpayTeaserContent);

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

        $I->amGoingTo('purchase post');

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
                ////CHECK TEASER HERE //$I->see(substr($content, 0, 60), PostModule::$visibleLaterpayTeaserContent);
                $I->see($price, 'a');
                $I->see($currency, 'a');
            };
        };

        //$I->see(substr($content, 0, 60), PostModule::$visibleLaterpayTeaserContent);

        if ($price != '0.00') {

            $I->amGoingTo('Click the LaterPay Purchase Button and purchase the content');

            $this->purschaseAtServer($post);

            $I->cantSeeElementInDOM(PostModule::$visibleLaterpayStatistics);

            $I->cantSeeElement(PostModule::$visibleLaterpayPurchaseButton);

            $I->cantSeeElement(PostModule::$visibleLaterpayPurchaseLink);

            $I->cantSeeElement(PostModule::$visibleLaterpayPurchaseBenefits);

            $I->seeInPageSource($content);
        } else {

            $I->amGoingTo('
                Skip because of empty price:
                Click the LaterPay Purchase Button and purchase the content.');
        };

        BackendModule::of($I)->login();

        $I->amOnPage($url);

        return $this;
    }

    /**
     * @param $category_id
     * @param null $post
     * Descriptoin:
     * Proceed with post purschase throught LaterPay Server
     * Can`t get iframe content with codeception. The iframe has no name attribute and target iframe placed into child iframe (document->iframe->iframe)
     * Can`t use javascript while error "Blocked a frame from accessing a cross-origin frame."
     * So used switching "WebDriver config url"
     * As note: $I->executeJS(" document.getElementsByTagName('iframe')[0].contentDocument.getElementById('id_username').value = 'atsumarov@scnsoft.com'; ");
     * @return $this
     */
    public function purschaseAtServer($post) {

        $I = $this->BackendTester;

        BackendModule::of($I)->logout();

        $I->amGoingTo('Purshase the post');

        //It must be there. Cause of switching domain issue.
        $I->amOnPage(str_replace('{post}', $post, PostModule::$pagePostFrontView));

        $laterpayPath = (string) $I->executeJS(PostModule::$lpServerLinkJsGetter);
        $laterpayPathArray = (array) parse_url($laterpayPath);
        $laterpayDomain = "{$laterpayPathArray['scheme']}://{$laterpayPathArray['host']}/";
        $laterpayPage = str_replace($laterpayDomain, '', $laterpayPath);

        $I->setDomain($laterpayDomain);
        $I->amOnPage($laterpayPage);
        $I->wait(PostModule::$averageTimeout);

        $I->tryClick($I, PostModule::$lpServerVisitorLoginLink);
        $I->switchToIFrame(PostModule::$lpServerVisitorLoginFrameName);
        $I->fillField(PostModule::$lpServerVisitorEmailField, PostModule::$lpServerVisitorEmailValue);
        $I->fillField(PostModule::$lpServerVisitorPasswordField, PostModule::$lpServerVisitorPasswordValue);
        $I->click(PostModule::$lpServerVisitorLoginBtn);

        $I->wait(PostModule::$shortTimeout);
        $I->tryClick($I, PostModule::$lpServerVisitorBuyBtn);

        $I->setDomain();
        $I->amOnPage(str_replace('{post}', $post, PostModule::$pagePostFrontView));

        return $this;
    }

    /**
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
     * @param $file_name
     * @return $this
     */
    public function checkIfFilesAreProtected($post, $file_name) {
        $I = $this->BackendTester;

        $I->amGoingTo('Open post for edit');
        $I->amOnPage(str_replace('{post}', $post, PostModule::$pagePostEdit));

        $I->click(self::$linkViewPost);

        //selected admin
        $I->seeElement(self::$linkFileLink);
        $parsed = explode('.', $file_name);
        $link = $I->executeJS("var link = jQuery('a:contains(" . $parsed[0] . ")').attr('href'); return link;");

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
     * P. 41
     * Check if a Correct Shortcode is Displayed Correctly
     * @param $post
     * @param $price
     * @return $this
     */
    public function checkIfCorrectShortcodeIsDisplayedCorrectly($post, $price) {
        $I = $this->BackendTester;

        $I->amGoingTo('Check how correct shortcode displayed');

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
     * P.42
     * Check if a Wrong Shortcode is Displayed Correctly
     * @param $post
     * @param $price
     * @return $this
     */
    public function checkIfWrongShortcodeIsDisplayedCorrectly($post, $price) {

        $I = $this->BackendTester;

        $I->amGoingTo('Check how wrong shortcode displayed');

        if ($price > 0) {

            $I->amOnPage(str_replace('{post}', $post, PostModule::$pagePostEdit));

            $I->click(self::$linkViewPost);
            $I->click(self::$linkPreviewSwitcher);
            $I->seeElement(self::$messageShortcodeError);
        }

        return $this;
    }

    /**
     * @param $content
     * @param $teaser
     * @return string
     */
    private function _createTeaserContent($content, $teaser) {
        //original $teaser_content = explode(' ', strip_tags($content), $teaser + 1);
        $teaser_content = explode(' ', strip_tags($content), $teaser);
        array_pop($teaser_content);
        //original join(' ', $teaser_content) . '...'
        return join(' ', $teaser_content);
    }

}

