<?php

class ModesModule extends BaseModule {

    //links
    public static $linkPreviewModeSwitcher  = 'input[name="teaser_content_only"]';
    public static $linkPluginModeToggle     = '#plugin-mode-toggle';

    //messages
    public static $messageTeaserOnly    = 'Visitors will now see only the teaser content of paid posts.';
    public static $messageOverlay       = 'Visitors will now see the teaser content of paid posts plus an
    excerpt of the real content under an overlay.';
    public static $messageTestMode      = 'The LaterPay plugin is in TEST mode now.
    Payments are only simulated and not actually booked.';
    public static $messageErrorLiveMode = 'Switching into Live mode requires a valid Live
    Merchant ID and Live API Key.';

    //labels
    public static $labelTest            = 'TEST';
    public static $labelLive            = 'LIVE';

    /**
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

    public function checkPreviewMode() {
        //TODO: return mode selected
    }

    /**
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

        BackendModule::of($I)
                    ->validateAPICredentials();

        //TODO: end this method

        return $this;
    }

    /**
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
}