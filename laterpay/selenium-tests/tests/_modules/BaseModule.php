<?php

abstract class BaseModule {

    public static $T1 = 'LaterPay WordPress Plugin Test Post';
    public static $T2 = 'LaterPay WordPress Plugin Shortcode Test';
    public static $T3 = 'LaterPay WordPress Plugin Shortcode Test Wrong Title';
    public static $CAT1 = 'LaterPay Test Category 1';
    public static $CAT2 = 'LaterPay Test Category 2';
    public static $CAT3 = 'LaterPay Test Category 3';
    public static $C1 = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam
erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est
Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore
et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no
sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod
tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.
Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Duis autem vel eum iriure dolor in hendrerit in
vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui
blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. Lorem ipsum dolor sit amet, consectetuer adipiscing elit,
sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud
exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in
vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui
blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. Nam liber tempor cum soluta nobis eleifend option
congue nihil imperdiet doming id quod mazim placerat facer possim assum. Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed
diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci
tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit
esse molestie consequat, vel illum dolore eu feugiat nulla facilisis. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd
gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr sed diam
nonumy eirmod tempor invidunt ut labore.';
    public static $C2 = '[laterpay_premium_download target_page_title="LaterPay WordPress Plugin Shortcode Test" target_page_id={ID of the respective post}
heading_text="Shortcode Heading" description_text="Shortcode Description" content_type="gallery"]
[laterpay_premium_download target_page_title="LaterPay WordPress Plugin Shortcode Test Wrong Title" target_page_id={ID of the
respective post} heading_text="Shortcode Heading" description_text="Shortcode Description" content_type="gallery"]
[laterpay_premium_download target_page_title="LaterPay WordPress Plugin Shortcode Test" target_page_id={Some non-existing ID}
heading_text="Shortcode Heading" description_text="Shortcode Description" content_type="gallery"]';
    public static $C3 = 'Both wrong
[laterpay_premium_download target_page_title="LaterPay WordPress Plugin Shortcode Test Wrong Title" target_page_id={Some nonexisting
ID} heading_text="Shortcode Heading" description_text="Shortcode Description" content_type="gallery"]';
    //shared data
    public static $baseUrl = '/wp-admin/';
    public static $adminMenuPluginButton = '#toplevel_page_laterpay-plugin';
    public static $messageArea = "#message";
    //plugin tabs
    public static $pluginPricingTab = 'a[href$="laterpay-plugin"]';
    public static $pluginAppearanceTab = 'a[href$="laterpay-appearance-tab"]';
    public static $pluginAccountTab = 'a[href$="laterpay-account-tab"]';
    //timeouts
    public static $veryShortTimeout = 1;
    public static $shortTimeout = 5;
    public static $averageTimeout = 15;
    public static $longTimeout = 30;
    public static $veryLongTimeout = 60;

    /**
     * @var BackendTester
     */
    protected $BackendTester;

    /**
     * @param \BackendTester $I
     */
    public function __construct($I) {
        $this->BackendTester = $I;
    }

    /**
     * @param \BackendTester $I
     * @return static
     */
    static public function of($I) {
        return new static($I);
    }

}

