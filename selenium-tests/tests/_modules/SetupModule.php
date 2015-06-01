<?php

class SetupModule extends BaseModule {
    //links
    public static $linkPluginsMainPage        = 'wp-admin/plugins.php';
    public static $linkPluginsInstallPage     = 'wp-admin/plugin-install.php?tab=upload';

    //selectors
    public static $selectorUploadedFile       = '#pluginzip';
    public static $selectorInstallButton      = '#install-plugin-submit';
    public static $selectorLpPlugin           = '#laterpay';
    public static $selectorActivateLpPlugin   = '.activate > a';
    public static $selectorDeactivateLpPlugin = '.deactivate > a';
    public static $selectorDeleteLpPlugin     = '.delete > a';
    public static $selectorConfirmDelete      = 'form[action*=delete-selected] > #submit';
    public static $selectorUploadArea         = '.upload-plugin';
    public static $selectorVisibilityToggle   = '#lp_js_toggleVisibilityInTestMode';
    public static $selectorFlashMessagesArea  = '#lp_js_flashMessage';

    //defaults
    public static $c_current_plugin_version   = '0.9.11.4';
    public static $c_previous_plugin_version  = '0.9.11.3';

    /**
     * Install Laterpay plugin
     *
     * @param null|string $plugin_version
     *
     * @return $this
     */
    public function installPlugin( $plugin_version = null ) {
        $I = $this->BackendTester;

        //init plugin version and create file name
        if ( ! isset( $plugin_version ) ) {
            $plugin_version = self::$c_current_plugin_version;
        }
        $file_name = 'laterpay' . '.v' . $plugin_version . '.zip';

        //Install plugin
        $I->amOnPage( self::$linkPluginsInstallPage );
        $I->attachFile( self::$selectorUploadedFile, $file_name );
        $I->click( self::$selectorInstallButton );
        $I->waitForElementNotVisible( self::$selectorUploadArea );

        //Check plugin listed
        $I->amOnPage( self::$linkPluginsMainPage );
        $I->seeElement( self::$selectorLpPlugin );

        return $this;
    }

    /**
     * Activate Laterpay plugin
     *
     * @return $this
     */
    public function activatePlugin() {
        $I = $this->BackendTester;

        //Check plugin listed
        $I->amOnPage( self::$linkPluginsMainPage );
        $I->seeElement( self::$selectorLpPlugin );

        //Activate plugin
        $I->click( self::$selectorActivateLpPlugin, self::$selectorLpPlugin );
        $I->waitForElement( self::$selectorLpPlugin . ' ' . self::$selectorDeactivateLpPlugin );

        //Enable plugin test visible mode by default
        $I->amOnPage( self::$linkAdminAccountTab );
        $I->click( self::$selectorVisibilityToggle );
        $I->waitForElementVisible( self::$selectorFlashMessagesArea );

        return $this;
    }

    /**
     * Deactivate Laterpay plugin
     *
     * @return $this
     */
    public function deactivatePlugin() {
        $I = $this->BackendTester;

        //Check plugin listed
        $I->amOnPage( self::$linkPluginsMainPage );
        $I->seeElement( self::$selectorLpPlugin );

        //Deactivate plugin
        $I->click( self::$selectorDeactivateLpPlugin, self::$selectorLpPlugin );
        $I->waitForElement( self::$selectorLpPlugin . ' ' . self::$selectorActivateLpPlugin );

        return $this;
    }

    /**
     * Delete Laterpay plugin
     *
     * @return $this
     */
    public function deletePlugin() {
        $I = $this->BackendTester;

        //Check plugin listed
        $I->amOnPage( self::$linkPluginsMainPage );
        $I->seeElement( self::$selectorLpPlugin );

        //Delete plugin
        $I->click( self::$selectorDeleteLpPlugin, self::$selectorLpPlugin );
        $I->click( self::$selectorConfirmDelete );
        $I->waitForElementNotVisible( self::$selectorConfirmDelete );

        //Check plugin not listed
        $I->dontSeeElement( self::$selectorLpPlugin );

        return $this;
    }
}

