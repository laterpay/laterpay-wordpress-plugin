<?php

class ModesModule extends BaseModule {

    //link
    public static $linkPreviewModeSwitcher = 'input[name="teaser_content_only"]';
    public static $linkPluginModeToggle = '#plugin-mode-toggle';
    public static $url_plugin_account = '/wp-admin/admin.php?page=laterpay-account-tab';
    //data
    public static $dataValidLiveMerchantId = 'Valid Live Merchant Id';
    public static $dataValidLiveApiKey = 'Valid Live API Key';
    public static $testData1 = 'a1b2c3d4e5f6g7h8i9j0';
    public static $testData2 = 'a1b2c3d4e5f6g7h8i9j0k1';
    public static $testData3 = 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5';
    public static $testData4 = 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6';
    //fields
    public static $fieldLaterpayLiveMerchantId = '#laterpay_live_merchant_id';
    public static $fieldLaterpayLiveApiKey = '#laterpay_live_api_key';
    public static $fieldTeaserContentChecked = "#teaser_content_only input[type='radio']:checked";
    public static $pluginModeCheckbox = 'plugin_is_in_live_mode_checkbox';
    //message
    public static $messageTeaserOnly = 'Visitors will now see only the teaser content of paid posts.';
    public static $messageOverlay = 'Visitors will now see the teaser content of paid posts plus an
    excerpt of the real content under an overlay.';
    public static $messageTestMode = 'The LaterPay plugin is in TEST mode now.
    Payments are only simulated and not actually booked.';
    public static $messageErrorLiveMode = 'Switching into Live mode requires a valid Live
    Merchant ID and Live API Key.';
    public static $messageLiveMode = 'The LaterPay plugin is in LIVE mode now. All
payments are actually booked and credited to your account.';
    public static $messageMerchantIdNotValid = 'The Merchant ID you entered is not a valid';
    public static $messageMerchantIdVerified = 'Merchant ID verified and saved.';
    public static $messageMerchantIdRemoved = 'Merchant ID has been removed';
    public static $messageApiKeyNotValid = 'The API key you entered is not a valid';
    public static $messageApiKeyVerified = 'API key verified and saved.';
    public static $messageApiKeyRemoved = 'API key has been removed';
    //labels
    public static $labelTest = 'TEST';
    public static $labelLive = 'LIVE';

    /**
     * P.25
     * Change Preview Mode {preview mode}
     * @param $preview_mode
     * @return $this
     */
    public function changePreviewMode($preview_mode) {
        $I = $this->BackendTester;
        $I->amOnPage(self::$baseUrl);
        $I->click(self::$adminMenuPluginButton);
        $I->click(self::$pluginAppearanceTab);

        switch ($preview_mode) {
            case 'teaser only':
                $I->selectOption(self::$linkPreviewModeSwitcher, '1');
                $I->see(self::$messageTeaserOnly, self::$messageArea);
                break;
            case 'overlay':
                $I->selectOption(self::$linkPreviewModeSwitcher, '0');
                $I->see(self::$messageOverlay, self::$messageArea);
                break;
            default:
                break;
        }

        return $this;
    }

    /**
     * P.30
     * Switch to Live Mode
     * @return $this
     */
    public function checkPreviewMode() {

        $I = $this->BackendTester;

        $I->amOnPage(self::$baseUrl);
        $I->click(self::$adminMenuPluginButton);
        $I->click(self::$pluginAccountTab);

        $preview_mode = $I->executeJS('jQuery("' . self::$fieldTeaserContentChecked . '").val()');

        switch ($preview_mode) {
            case '0':
                return 'overlay';
            case '1':
                return 'teaser_only';
            default: break;
        }

        return false;
    }

    /**
     * P.30
     * Switch to Live Mode
     * @return $this
     */
    public function switchToLiveMode() {
        $I = $this->BackendTester;
        $I->amOnPage(self::$baseUrl);
        $I->click(self::$adminMenuPluginButton);
        $I->click(self::$pluginAccountTab);

        $I->click(self::$labelTest, self::$linkPluginModeToggle);
        $I->see(self::$messageArea, self::$messageErrorLiveMode);
        $I->see(self::$labelTest, self::$linkPluginModeToggle);

        $this->validateAPICredentials(self::$fieldLaterpayLiveMerchantId, self::$fieldLaterpayLiveApiKey);

        $I->fillField(self::$fieldLaterpayLiveMerchantId, self::$dataValidLiveMerchantId);
        $I->click(self::$labelTest, self::$linkPluginModeToggle);
        $I->see(self::$messageArea, self::$messageErrorLiveMode);
        $I->see(self::$labelTest, self::$linkPluginModeToggle);

        $I->fillField(self::$fieldLaterpayLiveApiKey, self::$testData2);
        $I->click(self::$labelTest, self::$linkPluginModeToggle);
        $I->see(self::$messageArea, self::$messageErrorLiveMode);
        $I->see(self::$labelTest, self::$linkPluginModeToggle);

        $I->fillField(self::$fieldLaterpayLiveMerchantId, self::$dataValidLiveMerchantId);
        $I->fillField(self::$fieldLaterpayLiveApiKey, self::$dataValidLiveApiKey);
        $I->click(self::$labelTest, self::$linkPluginModeToggle);
        $I->see(self::$messageArea, self::$messageLiveMode);
        $I->see(self::$labelLive, self::$linkPluginModeToggle);

        return $this;
    }

    /**
     * P.32
     * Switch to Test Mode
     * @return $this
     */
    public function switchToTestMode() {
        $I = $this->BackendTester;
        $I->amOnPage(self::$baseUrl);
        $I->click(self::$adminMenuPluginButton);
        $I->click(self::$pluginAccountTab);
        $I->click(self::$labelLive, self::$linkPluginModeToggle);

        $I->see(self::$messageTestMode, self::$messageArea);
        $I->see(self::$labelTest, self::$linkPluginModeToggle);

        return $this;
    }

    /**
     * P.45
     * Validate API Credentials
     * @param $merchant_id_input
     * @param $api_key_input
     * @return $this
     */
    public function validateAPICredentials($merchant_id_input, $api_key_input) {
        $I = $this->BackendTester;

        $I->fillField($merchant_id_input, self::$testData1);
        $I->see(self::$messageMerchantIdNotValid);

        $I->fillField($merchant_id_input, self::$testData2);
        $I->see(self::$messageMerchantIdVerified);

        $I->fillField($api_key_input, self::$testData3);
        $I->see(self::$messageApiKeyNotValid);

        $I->fillField($api_key_input, self::$testData4);
        $I->see(self::$messageApiKeyVerified);

        $I->fillField($merchant_id_input, '');
        $I->see(self::$messageMerchantIdRemoved);

        $I->fillField($api_key_input, '');
        $I->see(self::$messageApiKeyRemoved);

        return $this;
    }

    /**
     * Ruturn true in case of test mode
     */
    public function checkIsTestMode() {

        $testMode = false;

        $I = $this->BackendTester;

        $returnUrl = $I->grabFromCurrentUrl();

        $I->amGoingTo(ModesModule::$url_plugin_account);

        if ($I->tryCheckbox($I, ModesModule::$pluginModeCheckbox))
            $testMode = true;

        $I->amGoingTo($returnUrl);

        return $testMode;
    }

}

