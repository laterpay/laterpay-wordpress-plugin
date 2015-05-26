<?php

class PostModule extends BaseModule {
    //links
    public static $linkPostListPage                = 'wp-admin/edit.php';
    public static $linkAddNewPostPage              = 'wp-admin/post-new.php';
    public static $linkPostEditPage                = 'wp-admin/post.php?post={post}&action=edit';
    public static $linkPostViewPage                = '/?p={post}';

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
    public static $selectorGlobalPrice             = '#lp_js_useGlobalDefaultPrice';
    public static $selectorCategoryPrice           = '#lp_js_useCategoryDefaultPrice';
    public static $selectorRevenueModel            = '#lp_js_postPriceRevenueModel';
    public static $selectorTitleRows               = '.post-title';
    public static $selectorFrontTitleEntry         = '.entry-title';
    public static $selectorFrontContentEntry       = '.entry-content';
    public static $selectorFrontTeaserContent      = '.lp_teaser-content';
    public static $selectorFrontOverlay            = '.lp_benefits';
    public static $selectorFrontPurchaseButton     = '.lp_purchase-button';
    public static $selectorFrontPurchaseLink       = '.lp_purchase-link';
    public static $selectorFrontTimepasses         = '#lp_js_timePassWidget';

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
    public static $c_post_check_options            = array(
                                                        'fulltext_visible'        => false,
                                                        'teaser_visible'          => true,
                                                        'purchase_button_visible' => true,
                                                        'overlay_visible'         => false,
                                                        'purchase_link_visible'   => true,
                                                        'timepasses_visible'      => false,
                                                     );

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

        //Prepare teaser
        if ( ! isset( $p_teaser ) ) {
            //Set teaser content
            $I->click( self::$selectorTeaserTypeSwitcher );
            $I->fillField( self::$selectorTeaserInput, $this->_createTeaserContent( $p_fulltext, self::$c_teaser ) );
        }

        if ( isset( $p_category ) ) {
            //Set categories to post
            if ( is_array( $p_category ) ) {
                foreach ($p_category as $category_id) {
                    $this->assignPostToCategory( $category_id );
                }
            } else {
                $this->assignPostToCategory( $p_category );
            }
        }

        //Set revenue model
        if ( ! isset( $p_revenue_model ) ) {
            if ( ! isset( $p_price ) ) {
                $p_revenue_model = self::$c_revenue_model_ppu;
            } else {
                $p_revenue_model = ( $p_price < 5 ) ? self::$c_revenue_model_ppu : self::$c_revenue_model_sis;
            }
        }

        //Set price
        if ( ! isset( $p_price ) ) {
            $p_price = ( $p_revenue_model === self::$c_revenue_model_ppu ) ? self::$c_price_ppu : self::$c_price_sis;
        }

        //Select price type, price and revenue model if possible
        switch ( $p_price_type ) {
            case self::$c_price_type_global:
                //Choose global default price type
                $I->click( self::$selectorGlobalPrice );
                break;

            case self::$c_price_type_category:
                //Choose category default price typ
                $I->click( self::$selectorCategoryPrice );
                break;

            case self::$c_price_type_individual:
                //Choose individual price type
                $I->click( self::$selectorIndividualPrice );
                $I->fillField( self::$selectorPostPrice, $p_price );
                $I->click( self::$selectorRevenueModel . ' > input[value=' . strtolower( $p_revenue_model ) . ']' );
                break;

            default:
                break;
        }

        //Publish post
        $I->click( self::$selectorPublishButton );
        $I->wait( self::$shortTimeout );

        $this->_storeCreatedPostId();

        $I->amOnPage( self::$linkPostListPage );
        $I->see( $p_post_title, self::$selectorTitleRows );

        return $this;
    }

    /**
     * Check post
     *
     * @param int         $post_id
     * @param null|string $p_post_title
     * @param array       $p_options    post check options
     *
     * @return $this
     */
    public function checkPost( $post_id, $p_post_title = null, $p_options = array() ) {
        $I = $this->BackendTester;

        if ( ! isset( $p_options ) ) {
            $p_options = self::$c_post_check_options;
        }

        if ( ! isset( $p_post_title ) ) {
            $p_post_title = self::$c_post_title;
        }

        $I->amOnPage( str_replace( '{post}', $post_id, self::$linkPostViewPage ) );
        $I->see( $p_post_title, self::$selectorFrontTitleEntry );

        //Go through options
        //Fulltext visibility
        if ( isset( $p_options['fulltext_visible'] ) && $p_options['fulltext_visible'] ) {
            // check if fulltext visible
            $I->seeElement( self::$selectorFrontContentEntry );
        } else {
            // check if fulltext invisible
            $I->dontSeeElement( self::$selectorFrontContentEntry );
        }

        //Teaser visibility
        if ( isset( $p_options['teaser_visible'] ) && $p_options['teaser_visible'] ) {
            // check if fulltext visible
            $I->seeElement( self::$selectorFrontTeaserContent);
        } else {
            // check if fulltext invisible
            $I->dontSeeElement( self::$selectorFrontTeaserContent );
        }

        //Purchase button visibility
        if ( isset( $p_options['purchase_button_visible'] ) && $p_options['purchase_button_visible'] ) {
            // check if fulltext visible
            $I->seeElement( self::$selectorFrontPurchaseButton );
        } else {
            // check if fulltext invisible
            $I->dontSeeElement( self::$selectorFrontPurchaseButton );
        }

        //Purchase link visibility
        if ( isset( $p_options['purchase_link_visible'] ) && $p_options['purchase_link_visible'] ) {
            // check if fulltext visible
            $I->seeElement( self::$selectorFrontPurchaseLink );
        } else {
            // check if fulltext invisible
            $I->dontSeeElement( self::$selectorFrontPurchaseLink );
        }

        //Overlay visibility
        if ( isset( $p_options['overlay_visible'] ) && $p_options['overlay_visible'] ) {
            // check if fulltext visible
            $I->seeElement( self::$selectorFrontOverlay );
        } else {
            // check if fulltext invisible
            $I->dontSeeElement( self::$selectorFrontOverlay );
        }

        //Timepasses visibility
        if ( isset( $p_options['timepasses_visible'] ) && $p_options['timepasses_visible'] ) {
            // check if fulltext visible
            $I->seeElement( self::$selectorFrontTimepasses );
        } else {
            // check if fulltext invisible
            $I->dontSeeElement( self::$selectorFrontTimepasses );
        }

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
     *
     * @param int      $category_id
     * @param null|int $post_id
     *
     * @return $this
     */
    public function unassignPostFromCategory( $category_id, $post_id = null ) {
        $I = $this->BackendTester;

        if ( $post_id > 0 ) {
            $I->amOnPage( str_replace( '{post}', $post_id, PostModule::$linkPostEditPage ) );

            $option = '#in-category-' . $category_id;
            $I->uncheckOption( $option );

            $I->click( self::$selectorPublishButton );
            $I->wait( self::$shortTimeout );
        } else {
            $option = '#in-category-' . $category_id;
            $I->uncheckOption( $option );
        }

        return $this;
    }

    /**
     * Assign post to category
     *
     * @param int      $category_id
     * @param null|int $post_id
     *
     * @return $this
     */
    public function assignPostToCategory( $category_id, $post_id = null ) {
        $I = $this->BackendTester;

        if ( $post_id > 0 ) {
            $I->amOnPage( str_replace( '{post}', $post_id, self::$linkPostEditPage ) );

            $option = '#in-category-' . $category_id;
            $I->checkOption($option);

            $I->click( self::$selectorPublishButton );
            $I->wait( self::$shortTimeout );
        } else {
            $option = '#in-category-' . $category_id;
            $I->checkOption( $option );
        }

        return $this;
    }

    /**
     * Store created post ID
     *
     * @param $post
     *
     * @return $this
     */
    private function _storeCreatedPostId() {
        $I = $this->BackendTester;

        $postId = null;
        $url = parse_url( $I->grabFromCurrentUrl() );
        parse_str( $url['query'], $array );

        if ( isset( $array['post'] ) ) {
            $postId = $array['post'];
        }

        $I->setVar( 'post', $postId );

        return $postId;
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
