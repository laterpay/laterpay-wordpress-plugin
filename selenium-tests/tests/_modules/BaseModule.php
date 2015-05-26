<?php

abstract class BaseModule {
    //shared data
    public static $baseUrl               = 'wp-admin/';
    public static $adminMenuPluginButton = '#toplevel_page_laterpay-plugin';
    public static $messageArea           = "#message";
    //plugin tabs
    public static $pluginPricingTab      = 'a[href$="laterpay-plugin"]';
    public static $pluginAppearanceTab   = 'a[href$="laterpay-appearance-tab"]';
    public static $pluginAccountTab      = 'a[href$="laterpay-account-tab"]';
    //timeouts
    public static $shortTimeout          = 2;

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

