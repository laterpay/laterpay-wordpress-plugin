<?php

class TimepassModule extends BaseModule {
    //selectors
    public static $selectorAddTimePassButton           = '#lp_js_addTimePass';
    public static $selectorAddTimePassSection          = '.lp_js_addTimePassWrapper';
    public static $selectorTimePassTitle               = 'input[name=title]';
    public static $selectorTimePassDescription         = 'input[name=description]';
    public static $selectorTimePassDuration            = 'input[name=duration]';
    public static $selectorTimePassAccessTo            = 'input[name=access_to]';
    public static $selectorTimePassPeriod              = 'input[name=period]';
    public static $selectorTimePassRevenueModel        = 'input[name=revenue_model]';
    public static $selectorTimePassPrice               = 'input[name=price]';
    public static $selectorTimePassSaveButton          = '.lp_js_saveTimePass';
    public static $selectorFlashMessageUpdated         = '.lp_flash-message.updated';
    public static $selectorFrontTimepassTitle          = '.lp_js_timePassPreviewTitle';
    public static $selectorFrontTimepassWidget         = '#lp_js_timePassWidget';
    public static $selectorFrontYuiIframe              = '.yui3-widget-bd > iframe';
    public static $selectorFrontTimepassPurchaseButton = '.lp_time-pass__actions .lp_js_purchaseLink';
    public static $selectorIframeAgreeCheckbox         = 'input[name=agree]';
    public static $selectorIframeProceedButton         = '#nextbuttons';
    public static $selectorIframeMessage               = '.flash-message';

    //js
    public static $jsGetMainIframeName          = " var name = jQuery('.yui3-widget-bd').find('iframe').attr('name'); return name; ";

    //defaults
    public static $c_time_pass_title            = "Test Time Pass";
    public static $c_time_pass_description      = "This is a test time pass";
    public static $c_time_pass_access           = '0';
    public static $c_time_pass_validity_period  = '1';
    public static $c_time_pass_validity_unit    = '1';

    /**
     * Create timepass
     *
     * @param array $args
     *
     * @return $this
     */
    public function createTimepass( $args = array() ) {
        $I = $this->BackendTester;

        $default_args = array(
            'title'       => self::$c_time_pass_title,
            'description' => self::$c_time_pass_description,
            'period'      => self::$c_time_pass_validity_period,
            'unit'        => self::$c_time_pass_validity_unit,
            'access_to'   => self::$c_time_pass_access,
        );

        $args = array_merge( $default_args, array_filter( $args ) );

        $I->amOnPage( self::$linkAdminPricingTab );
        $I->click( self::$selectorAddTimePassButton );
        $I->seeElement( self::$selectorAddTimePassSection );

        //Set timepass title
        $I->fillField( self::$selectorTimePassTitle, $args['title'] );
        //Set timepass description
        $I->fillField( self::$selectorTimePassDescription, $args['description'] );
        //Set timepass access option
        $I->selectOption( self::$c_time_pass_access, '0' );
        //Set timepass unit
        $I->selectOption( self::$c_time_pass_validity_unit, '1' );
        //Set timepass period
        $I->selectOption( self::$c_time_pass_validity_period, '1' );
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
        $I->fillField( self::$selectorTimePassPrice, $args['price'] / 100 );

        if ( $args['revenue_model'] === self::$c_revenue_model_sis ) {
            $I->checkOption( self::$selectorTimePassRevenueModel );
        }

        $I->click( self::$selectorTimePassSaveButton );
        $I->waitForElement( self::$selectorFlashMessageUpdated );

        return $this;
    }

    public function purchaseTimepass( $post_id, $time_pass_title = null ) {
        $I = $this->BackendTester;

        if ( ! isset( $time_pass_title ) ) {
            $time_pass_title = self::$c_time_pass_title;
        }

        //Check post title
        $I->amOnPage( str_replace( '{post}', $post_id, self::$linkPostViewPage ) );
        $I->see( $time_pass_title, self::$selectorFrontTimepassTitle );

        //Start purchase process
        $I->click( self::$selectorFrontTimepassPurchaseButton );
        $I->switchToIFrame( (string) $I->executeJS( self::$jsGetMainIframeName ) );
        $I->switchToIFrame( 'wrapper' );
        $I->checkOption( self::$selectorIframeAgreeCheckbox );
        $I->click( self::$selectorIframeProceedButton );
        $I->waitForElementVisible( self::$selectorIframeMessage );

        return $this;
    }
}
