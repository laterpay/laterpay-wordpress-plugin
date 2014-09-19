<?php

class ModesModule extends BaseModule {

    //link
    public static $linkPreviewModeSwitcher = 'input[name="teaser_content_only"]';
    public static $linkPluginModeToggle = '#lp_plugin-mode-toggle';
    public static $url_plugin_account = '/wp-admin/admin.php?page=laterpay-account-tab';
    public static $url_plugin_appearence = '/wp-admin/admin.php?page=laterpay-appearance-tab';
    //data
    public static $dataValidLiveMerchantId = 'UKQwS7eSQVtkt8F8PmarM5';
    public static $dataValidLiveApiKey = '3ee4cf26cbe64ce7acc9f98001dc30e8';
    public static $testData1 = 'a1b2c3d4e5f6g7h8i9j0';
    public static $testData2 = 'a1b2c3d4e5f6g7h8i9j0k1';
    public static $testData3 = 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5';
    public static $testData4 = 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6';
    public static $pluginSandobxLiveSwitcher = '.switch-text';
    //fields
    public static $fieldLaterpayLiveMerchantId = '#lp_live-merchant-id';
    public static $fieldLaterpayLiveApiKey = '#lp_live-api-key';
    public static $btnLaterpayRequestLive = 'Request Live API Credentials';
    public static $fieldTeaserContentChecked = "#teaser_content_only input[type='radio']:checked";
    public static $pluginModeCheckbox = 'plugin_is_in_live_mode_checkbox';
    public static $pluginModeHidden = 'plugin_is_in_live_mode';
    //message
    public static $messageTeaserOnly = 'Visitors will now see only the teaser content of paid posts.';
    public static $messageOverlay = 'Visitors will now see the teaser content of paid posts plus an excerpt of the real content under an overlay.';
    public static $messageTestMode = 'The LaterPay plugin is in TEST mode now. Payments are only simulated and not actually booked.';
    public static $messageErrorLiveMode = 'The LaterPay plugin needs valid API credentials to work.';
    public static $messageLiveMode = 'The LaterPay plugin is in LIVE mode now. All payments are actually booked and credited to your account.';
    public static $messageMerchantIdNotValid = 'The Merchant ID you entered is not a valid LaterPay Merchant ID!';
    public static $messageMerchantIdVerified = 'Live Merchant ID verified and saved.';
    public static $messageMerchantIdRemoved = 'Merchant ID has been removed';
    public static $messageLiveMerchantIdRemoved = 'The Live Merchant ID has been removed.';
    public static $messageApiKeyNotValid = 'The API key you entered is not a valid';
    public static $messageApiKeyVerified = 'API key verified and saved.';
    public static $messageApiKeyRemoved = 'API key has been removed';
    public static $messageliveApiKeyRemoved = 'The Live API key has been removed.';
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

        $I->amGoingTo('Change Preview Mode');

        $I->amOnPage(self::$baseUrl);
        $I->click(self::$adminMenuPluginButton);
        $I->click(self::$pluginAppearanceTab);

        switch ($preview_mode) {
            case 'teaser only':
                if ($I->tryOption($I, self::$linkPreviewModeSwitcher, '0')) {

                    $I->selectOption(self::$linkPreviewModeSwitcher, '1');
                    $I->wait(BaseModule::$shortTimeout);
                    $I->seeInPageSource(self::$messageTeaserOnly);
                } else {

                    $I->comment('Teaser mode already set');
                };
                break;
            case 'overlay':
                if ($I->tryOption($I, self::$linkPreviewModeSwitcher, '1')) {

                    $I->selectOption(self::$linkPreviewModeSwitcher, '0');
                    $I->wait(BaseModule::$shortTimeout);
                    $I->seeInPageSource(self::$messageOverlay);
                } else {

                    $I->comment('Overview mode already set');
                };
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

        $I->amOnPage(ModesModule::$url_plugin_appearence);

        if ($I->trySeeInField($I, 'input[name="teaser_content_only"]:checked', 0)) {

            return 'overlay';
        } else {

            return 'teaser_only';
        };
    }

    /**
     * P.30
     * Switch to Live Mode
     * Changes of specification.
     * 1. "The toggle text is still “TEST”" replaced with "checkbox is checked test".
     * @return $this
     */
    public function switchToLiveMode() {

        $I = $this->BackendTester;

        $I->amOnPage(ModesModule::$url_plugin_account);

        $I->amGoingTo('Reset plugin Live credentials');
        $I->fillField(self::$fieldLaterpayLiveMerchantId, '');
        $I->fillField(self::$fieldLaterpayLiveApiKey, '');
        if ($I->tryCheckbox($I, self::$linkPluginModeToggle))
            $I->click(self::$linkPluginModeToggle);

        $I->amGoingTo('Start verify plugin switch to Live mode');
        $I->click(self::$linkPluginModeToggle);
        $I->wait(BaseModule::$shortTimeout);
        $I->seeInPageSource(self::$messageErrorLiveMode);
        $I->cantSeeCheckboxIsChecked(self::$linkPluginModeToggle);

        $this->validateAPICredentials(self::$fieldLaterpayLiveMerchantId, self::$fieldLaterpayLiveApiKey);

        $I->amGoingTo('Set invalid merchant');
        $I->fillField(self::$fieldLaterpayLiveMerchantId, self::$dataValidLiveMerchantId);
        $I->click(self::$linkPluginModeToggle);
        $I->wait(BaseModule::$shortTimeout);
        $I->seeInPageSource(self::$messageErrorLiveMode);
        $I->cantSeeCheckboxIsChecked(self::$linkPluginModeToggle);

        $I->amGoingTo('Set invalid key');
        $I->fillField(self::$fieldLaterpayLiveApiKey, self::$testData2);
        $I->click(self::$linkPluginModeToggle);
        $I->wait(BaseModule::$shortTimeout);
        $I->seeInPageSource(self::$messageErrorLiveMode);
        $I->cantSeeCheckboxIsChecked(self::$linkPluginModeToggle);

        $I->amGoingTo('Set valid merchant and key');
        $I->fillField(self::$fieldLaterpayLiveMerchantId, self::$dataValidLiveMerchantId);
        $I->fillField(self::$fieldLaterpayLiveApiKey, self::$dataValidLiveApiKey);
        $I->click(self::$linkPluginModeToggle);
        $I->wait(BaseModule::$shortTimeout);
        $I->seeInPageSource(self::$messageLiveMode);
        $I->seeCheckboxIsChecked(self::$linkPluginModeToggle);

        return $this;
    }

    /**
     * P.32
     * Switch to Test Mode
     * @return $this
     */
    public function switchToTestMode() {

        $I = $this->BackendTester;

        if ($this->checkIsTestMode())
            return $this;

        $I->amOnPage(ModesModule::$url_plugin_account);

        $I->fillField(self::$fieldLaterpayLiveMerchantId, self::$testData2);
        $I->fillField(self::$fieldLaterpayLiveApiKey, self::$testData4);
        $I->click(self::$pluginSandobxLiveSwitcher);
        $I->wait(BaseModule::$shortTimeout);

        //Commented, because this message can`t be shown, until there no valid live credentials. Because of checkbox already set to test mode and can`t be changed to trigger the message.
        //$I->seeInPageSource(self::$messageTestMode);

        $I->cantSeeCheckboxIsChecked(self::$linkPluginModeToggle);

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

        $I->amOnPage(ModesModule::$url_plugin_account);

        $I->amGoingTo('Reset plugin Live credentials if it is.');
        $I->fillField(self::$fieldLaterpayLiveMerchantId, '');
        $I->fillField(self::$fieldLaterpayLiveApiKey, '');
        if ($I->tryCheckbox($I, self::$linkPluginModeToggle))
            $I->click(self::$linkPluginModeToggle);

        $I->fillField($merchant_id_input, self::$testData1);
        $I->wait(BaseModule::$shortTimeout);
        $I->seeInPageSource(self::$messageMerchantIdNotValid);

        $I->fillField($merchant_id_input, self::$testData2);
        $I->wait(BaseModule::$shortTimeout);
        $I->seeInPageSource(self::$messageMerchantIdVerified);

        $I->fillField($api_key_input, self::$testData3);
        $I->wait(BaseModule::$shortTimeout);
        $I->seeInPageSource(self::$messageApiKeyNotValid);

        $I->fillField($api_key_input, self::$testData4);
        $I->wait(BaseModule::$shortTimeout);
        $I->seeInPageSource(self::$messageApiKeyVerified);

        $I->executeJS(" jQuery('$merchant_id_input').val('').trigger('input'); ");
        $I->wait(BaseModule::$shortTimeout);
        $I->seeInPageSource(self::$messageLiveMerchantIdRemoved);

        $I->executeJS(" jQuery('$api_key_input').val('').trigger('input'); ");
        $I->wait(BaseModule::$shortTimeout);
        $I->seeInPageSource(self::$messageliveApiKeyRemoved);

        return $this;
    }

    /**
     * Ruturn true in case of test mode
     */
    public function checkIsTestMode() {

        $I = $this->BackendTester;

        $returnUrl = $I->grabFromCurrentUrl();

        $I->amOnPage(ModesModule::$url_plugin_account);

        $testMode = (int) $I->grabValueFrom(ModesModule::$pluginModeHidden);

        $I->amGoingTo($returnUrl);

        return !$testMode;
    }

}

