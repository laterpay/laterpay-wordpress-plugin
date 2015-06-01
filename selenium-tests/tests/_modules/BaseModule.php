<?php

abstract class BaseModule {
    //admin tabs links
    public static $linkAdminMainPage               = 'wp-admin/admin.php?page=laterpay-plugin';
    public static $linkAdminPricingTab             = 'wp-admin/admin.php?page=laterpay-pricing-tab';
    public static $linkAdminAccountTab             = 'wp-admin/admin.php?page=laterpay-account-tab';
    public static $linkPostViewPage                = '/?p={post}';

    //timeouts
    public static $shortTimeout                    = 2;

    //defaults
    public static $c_revenue_model_ppu             = 'PPU';
    public static $c_revenue_model_sis             = 'SIS';
    public static $c_price_ppu                     = 29;
    public static $c_price_sis                     = 259;
    public static $c_price_type_individual         = 'Individual';
    public static $c_price_type_individual_dynamic = 'Individual Dynamic';
    public static $c_price_type_category           = 'Category Default';
    public static $c_price_type_global             = 'Global Default';

    /**
     * @var BackendTester
     */
    protected $BackendTester;

    /**
     * Constructor
     * @param \BackendTester $I
     */
    public function __construct($I) {
        $this->BackendTester = $I;
    }

    /**
     * Create new instance
     * @param \BackendTester $I
     * @return static
     */
    static public function of($I) {
        return new static($I);
    }
}

