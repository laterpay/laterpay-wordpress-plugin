<?php

abstract class PluginModule {

    /**
     * public static $elementPathContainer           = '.test_elementPathContainer';   // '#current_path'
     * Example, for <element id='id' name='name' value='value' class='class'>text</element>
     * With codeception path used as: '#id', 'name', 'text', '.class'
     */
    //BaseModule
    public static $adminMenuPluginButton           = '.test_adminMenuPluginButton';   // '#toplevel_page_laterpay-plugin'
    public static $pluginPricingTab                = '.test_pluginPricingTab';        // 'a[href$="laterpay-plugin"]'
    public static $pluginAppearanceTab             = '.test_pluginAppearanceTab';     // 'a[href$="laterpay-appearance-tab"]'
    public static $pluginAccountTab                = '.test_pluginAccountTab';        // a[href$="laterpay-account-tab"]
    //BackendModule
    public static $loginButton                     = '.test_loginButton'; //wp-submit
    public static $logoutMenu                      = '.test_logoutMenu'; //#wp-admin-bar-my-account
    public static $logoutButton                    = '.test_logoutButton'; //#wp-admin-bar-logout>a
    //CategoryDefaultPriceModule
    public static $pricingAddCategoryButton        = '.test_pricingAddCategoryButton'; // #lp_add-category-link
    public static $pricingCategorySelect           = '.test_pricingCategorySelect'; // #lp_category-prices .select2-choice
    public static $pricingCategoryValue            = '.test_pricingCategoryValue'; // #lp_category-prices .lp_category-title
    public static $pricingSaveLink                 = '.test_pricingSaveLink'; // #lp_category-prices .unsaved .lp_save-link
    public static $pricingSaveLinkSaved            = '.test_pricingSaveLinkSaved'; // #lp_category-prices .lp_save-link
    public static $pricingCancelLink               = '.test_pricingCancelLink'; // #lp_category-prices .unsaved .lp_cancel-link
    public static $pricingChangeLink               = '.test_pricingChangeLink'; // #lp_category-prices .lp_change-link
    public static $pricingDeleteLink               = '.test_pricingDeleteLink'; // #lp_category-prices .lp_delete-link
    public static $pricingPriceInput               = '.test_pricingPriceInput'; // #lp_category-prices .unsaved .lp_number-input
    public static $pricingPriceInputSaved          = '.test_pricingPriceInputSaved'; // #lp_category-prices .lp_number-input
    //CategoryModule
    public static $elementCategoryNameId           = '.test_elementCategoryNameId'; // #tag-name
    public static $elementSubmitId                 = '.test_elementSubmitId'; // #submit
    public static $elementCategoriesTableSelector  = '.test_elementCategoriesTableSelector'; // .wp-list-table.widefat.fixed.tags
    //ModexModule
    public static $pluginSandobxLiveSwitcher       = '.test_pluginSandobxLiveSwitcher'; //.switch-text
    public static $fieldLaterpayLiveMerchantId     = '.test_fieldLaterpayLiveMerchantId'; // #lp_live-merchant-id
    public static $fieldLaterpayLiveApiKey         = '.test_fieldLaterpayLiveApiKey'; // #lp_live-api-key
    public static $fieldTeaserContentChecked       = '.test_fieldTeaserContentChecked'; // #teaser_content_only input[type='radio']:checked
    public static $pluginModeCheckbox              = '.test_pluginModeCheckbox'; // plugin_is_in_live_mode_checkbox
    public static $pluginModeHidden                = '.test_pluginModeHidden'; // plugin_is_in_live_mode
    //PostModule
    public static $fieldTitle                      = '.test_fieldTitle'; //#title
    public static $fieldContent                    = '.test_fieldContent'; //#content_ifr
    public static $fieldTeaser                     = '.test_fieldTeaser'; //#laterpay_teaser_content
    public static $fieldPrice                      = '.test_fieldPrice'; //input[name="post-price"]
    public static $contentId                       = '.test_contentId'; //#content
    public static $teaserContentId                 = '.test_teaserContentId'; //#postcueeditor
    public static $fileInput                       = '.test_fileInput'; //input[type="file"]
    public static $contentText                     = '.test_contentText'; //#content-html
    public static $teaserContentText               = '.test_teaserContentText'; //#postcueeditor-html
    public static $linkGlobalDefaultPrice          = '.test_linkGlobalDefaultPrice'; //#lp_use-global-default-price
    public static $linkIndividualPrice             = '.test_linkIndividualPrice'; //#lp_use-individual-price
    public static $linkDynamicPricing              = '.test_linkDynamicPricing'; //#lp_use-dynamic-pricing
    public static $linkCategoryPrice               = '.test_linkCategoryPrice'; //#lp_use-category-default-price
    public static $linkAddMedia                    = '.test_linkAddMedia'; //#insert-media-button
    public static $linkMediaRouter                 = '.test_linkMediaRouter'; //.media-router
    public static $linkAttachFile                  = '.test_linkAttachFile'; //#__wp-uploader-id-1
    public static $linkAddFileLinkToContent        = '.test_linkAddFileLinkToContent'; //.media-toolbar-primary .media-button-insert
    public static $linkPublish                     = '.test_linkPublish'; //#publish
    public static $linkViewPost                    = '.test_linkViewPost'; //#view-post-btn a
    public static $linkPreviewSwitcher             = '.test_linkPreviewSwitcher'; //.switch-handle
    public static $linkPreviewSwitcherElement      = '.test_linkPreviewSwitcherElement'; //preview_post_checkbox
    public static $linkShortCode                   = '.test_linkShortCode'; //a[class="lp_purchase-link-without-function lp_button"]
    public static $linkFileLink                    = '.test_linkFileLink'; //a[href*="wp-admin/admin-ajax.php?action=laterpay_load_files"]
    public static $visibleLaterpayWidgetContainer  = '.test_visibleLaterpayWidgetContainer'; //#lp_dynamic-pricing-widget-container
    public static $visibleLaterpayStatistics       = '.test_visibleLaterpayStatistics'; //.lp_post-statistics-details
    public static $visibleLaterpayPurchaseButton   = '.test_visibleLaterpayPurchaseButton'; //a[class="lp_purchase-link lp_button"]
    public static $visibleLaterpayPurchaseLink     = '.test_visibleLaterpayPurchaseLink'; //.lp_purchase-link
    public static $visibleLaterpayPurchaseBenefits = '.test_visibleLaterpayPurchaseBenefits'; //.lp_benefits
    public static $visibleLaterpayTeaserContent    = '.test_visibleLaterpayTeaserContent'; //.lp_teaser-content
    public static $visibleLaterpayContent          = '.test_visibleLaterpayContent'; //.entry-content
    public static $visibleInTablePostTitle         = '.test_visibleInTablePostTitle'; //.post-title
    public static $visibleInTablePostPrice         = '.test_visibleInTablePostPrice'; //.post-price
    public static $pageListPriceCol                = '.test_pageListPriceCol'; //td[class="post_price column-post_price"]
    public static $pageListPricetypeCol            = '.test_pageListPricetypeCol'; //td[class="post_price_type column-post_price_type"]
    public static $messageShortcodeError           = '.test_messageShortcodeError'; //.laterpay-shortcode-error
    public static $lpServerVisitorLoginLink        = '.test_lpServerVisitorLoginLink'; //Log in to LaterPay
    public static $lpServerVisitorLoginClass       = '.test_lpServerVisitorLoginClass'; //.selen-button-login
    public static $lpServerVisitorLoginFrameName   = '.test_lpServerVisitorLoginFrameName'; //wrapper
    public static $lpServerVisitorEmailField       = '.test_lpServerVisitorEmailField'; //#id_username
    public static $lpServerVisitorPasswordField    = '.test_lpServerVisitorPasswordField'; //#id_password
    public static $lpServerVisitorBuyBtn           = '.test_lpServerVisitorBuyBtn'; //#nextbuttons
    //SetupModule
    public static $pluginSearchField               = '.test_pluginSearchField'; //s
    public static $pluginSearchForm                = '.test_pluginSearchForm'; //.search-form
    public static $pluginSearchValue               = '.test_pluginSearchValue'; //laterpay
    public static $pluginUploadField               = '.test_pluginUploadField'; //pluginzip
    public static $pluginUploadSubmitField         = '.test_pluginUploadSubmitField'; //install-plugin-submit
    public static $pluginDeactivateLink            = '.test_pluginDeactivateLink'; //#laterpay .deactivate > a
    public static $pluginDeleteLink                = '.test_pluginDeleteLink'; //#laterpay .delete > a
    public static $pluginDeleteConfirmLink         = '.test_pluginDeleteConfirmLink'; //#submit
    public static $pluginActivateLink              = '.test_pluginActivateLink'; //#laterpay .activate > a
    public static $pluginNavigationLabel           = '.test_pluginNavigationLabel'; //LaterPay
    public static $backNavigateTab                 = '.test_backNavigateTab'; //#adminmenuwrap
    public static $laterpaySandboxMerchantField    = '.test_laterpaySandboxMerchantField'; //get_started[laterpay_sandbox_merchant_id]
    public static $laterpaySandboxApiKeyField      = '.test_laterpaySandboxApiKeyField'; //get_started[laterpay_sandbox_api_key]
    public static $pluginActivateFormButton        = '.test_pluginActivateFormButton'; //.lp_activate-plugin-button
    public static $globalDefaultCurrencyField      = '.test_globalDefaultCurrencyField'; //get_started[laterpay_currency]
    public static $globalDefaultCurrencySelect     = '.test_globalDefaultCurrencySelect'; //#lp_currency-select
    public static $linkDismissWPMessage            = '.test_linkDismissWPMessage'; //.wp-pointer-content .close
    public static $assertPluginName                = '.test_assertPluginName'; //laterpay
    public static $assertFieldStepOneDone          = '.test_assertFieldStepOneDone'; //span[class="lp_step-1 lp_step-done"]
    public static $laterpayChangeLink              = '.test_laterpayChangeLink'; //Change
    public static $globalDefaultPriceField         = '.test_globalDefaultPriceField'; //#lp_global-default-price
    public static $laterpaySaveLink                = '.test_laterpaySaveLink'; //Save
    public static $laterpayCancelLink              = '.test_laterpayCancelLink'; //Cancel
    public static $globalPriceText                 = '.test_globalPriceText'; //#lp_global-price-text
    public static $assertPluginListed              = '.test_assertPluginListed'; //LaterPay

}

