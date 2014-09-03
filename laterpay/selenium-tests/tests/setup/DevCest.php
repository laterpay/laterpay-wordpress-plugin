<?php

use \SetupTester;

/**
 * @group Dev
 */
class DevCest {

    /**
     * @param \SetupTester $I
     * @group Dev
     */
    public function stepReinstallPlugin(SetupTester $I) {

        SetupPage::Reinstall($I, false);
        SetupPage::Reconfigure($I, false);
    }

}

