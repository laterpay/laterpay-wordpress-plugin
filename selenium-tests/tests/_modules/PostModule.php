<?php

class PostModule extends BaseModule {
    //links
    public static $linkPostListPage                = 'wp-admin/edit.php';
    public static $linkAddNewPostPage              = 'wp-admin/post-new.php';
    public static $linkPostEditPage                = 'wp-admin/post.php?post={post}&action=edit';

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
    public static $selectorFrontYuiIframe          = '.yui3-widget-bd > iframe';
    public static $selectorIframeAgreeCheckbox     = 'input[name=agree]';
    public static $selectorIframeProceedButton     = '#nextbuttons';
    public static $selectorIframeMessage           = '.flash-message';

    //js
    public static $jsGetMainIframeName             = " var name = jQuery('.yui3-widget-bd').find('iframe').attr('name'); return name; ";

    //defaults
    public static $c_post_title                    = 'Test Post';
    public static $c_teaser                        = 200;
    public static $c_fulltext                      = 1000;
    public static $c_post_check_options            = array(
                                                        'fulltext_visible'        => false,
                                                        'teaser_visible'          => true,
                                                        'purchase_button_visible' => true,
                                                        'overlay_visible'         => false,
                                                        'purchase_link_visible'   => true,
                                                        'timepasses_visible'      => false,
                                                     );

    protected $options;

    /**
     * Create post
     *
     * @param array $args
     *
     * @return $this
     */
    public function createPost( $args = array() ) {
        $I = $this->BackendTester;

        $I->amOnPage( self::$linkAddNewPostPage );
        //Set post title
        $I->fillField( self::$selectorPostTitleInput, isset( $args['post_title'] ) ? $args['post_title'] : self::$c_post_title );

        //Prepare fulltext
        if ( ! isset( $args['fulltext'] ) ) {
            //Get content from file
            $args['fulltext'] = file_get_contents( './_data/content.txt' );
            $args['fulltext'] = str_replace( array( "\r", "\n" ), '', $args['fulltext'] );
        }

        //Set post content
        $I->click( self::$selectorContentTypeSwitcher );
        $I->fillField( self::$selectorContentInput, $args['fulltext'] );

        //Prepare teaser
        if ( ! isset( $args['teaser'] ) ) {
            $args['teaser'] = $this->_createTeaserContent( $args['fulltext'], self::$c_teaser );
        }

        //Set teaser content
        $I->click( self::$selectorTeaserTypeSwitcher );
        $I->fillField( self::$selectorTeaserInput, $args['teaser'] );

        if ( isset( $args['category'] ) ) {
            //Set categories to post
            if ( is_array( $args['category'] ) ) {
                foreach ( $args['category'] as $category_id) {
                    $this->assignPostToCategory( $category_id );
                }
            } else {
                $this->assignPostToCategory( $args['category'] );
            }
        }

        //Set revenue model
        if ( ! isset( $args['revenue_model'] ) ) {
            if ( ! isset( $args['price'] ) ) {
                $args['revenue_model'] = self::$c_revenue_model_ppu;
            } else {
                $args['revenue_model'] = ( $args['price'] < 5 ) ? self::$c_revenue_model_ppu : self::$c_revenue_model_sis;
            }
        }

        //Set price
        if ( ! isset( $args['price'] ) ) {
            $args['price'] = ( $args['revenue_model'] === self::$c_revenue_model_ppu ) ? self::$c_price_ppu : self::$c_price_sis;
        }

        //Select price type, price and revenue model if possible
        switch ( $args['price'] ) {
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
                $I->fillField( self::$selectorPostPrice, $args['price'] );
                $I->click( self::$selectorRevenueModel . ' > input[value=' . strtolower( $args['revenue_model'] ) . ']' );
                break;

            default:
                break;
        }

        //Publish post
        $I->click( self::$selectorPublishButton );
        $I->wait( self::$shortTimeout );

        $this->_storeCreatedPostId();

        $I->amOnPage( self::$linkPostListPage );
        $I->see( $args['post_title'], self::$selectorTitleRows );

        return $this;
    }

    /**
     * Check post
     *
     * @param int         $post_id
     * @param null|string $post_title
     * @param array       $options    post check options
     *
     * @return $this
     */
    public function checkPost( $post_id, $post_title = null, $options = array() ) {
        $I = $this->BackendTester;

        if ( ! isset( $post_title ) ) {
            $post_title = self::$c_post_title;
        }

        //Check post title
        $I->amOnPage( str_replace( '{post}', $post_id, self::$linkPostViewPage ) );
        $I->see( $post_title, self::$selectorFrontTitleEntry );

        if ( ! isset( $options ) ) {
            $options = self::$c_post_check_options;
        }

        // init options
        $this->options = $options;

        //Check visibilities
        $this->_checkVisibility( 'fulltext_visible', self::$selectorFrontContentEntry );
        $this->_checkVisibility( 'teaser_visible', self::$selectorFrontTeaserContent );
        $this->_checkVisibility( 'purchase_button_visible', self::$selectorFrontPurchaseButton );
        $this->_checkVisibility( 'purchase_link_visible', self::$selectorFrontPurchaseLink );
        $this->_checkVisibility( 'overlay_visible', self::$selectorFrontOverlay );
        $this->_checkVisibility( 'timepasses_visible', self::$selectorFrontTimepasses );

        return $this;
    }

    /**
     * Purchase post
     *
     * @param int         $post_id
     * @param null|string $post_title
     *
     * @return $this
     */
    public function purchasePost( $post_id, $post_title = null ) {
        $I = $this->BackendTester;

        if ( ! isset( $p_post_title ) ) {
            $post_title = self::$c_post_title;
        }

        //Check post title
        $I->amOnPage( str_replace( '{post}', $post_id, self::$linkPostViewPage ) );
        $I->see( $post_title, self::$selectorFrontTitleEntry );

        //Start purchase process
        $I->click( self::$selectorFrontPurchaseButton );
        $I->switchToIFrame( (string) $I->executeJS( self::$jsGetMainIframeName ) );
        $I->switchToIFrame( 'wrapper' );
        $I->checkOption( self::$selectorIframeAgreeCheckbox );
        $I->click( self::$selectorIframeProceedButton );
        $I->seeElement( self::$selectorIframeMessage );

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

    /**
     * Check visibility of element
     *
     * @param string        $option_name
     * @param string        $selector
     *
     * @return void
     */
    private function _checkVisibility( $option, $selector ) {
        $I = $this->BackendTester;

        if ( isset( $this->options[ $option ] ) && $this->options[ $option ] ) {
            // check if fulltext visible
            $I->seeElement( $selector );
        } else {
            // check if fulltext invisible
            $I->dontSeeElement( $selector );
        }
    }
}
