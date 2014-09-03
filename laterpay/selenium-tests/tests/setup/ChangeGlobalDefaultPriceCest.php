<?php

use \SetupTester;

/**
 * C2 - Change Global Default Price
 * @group ChangeGlobalDefaultPrice
 */
class ChangeGlobalDefaultPriceCest {

    /**
     * @param \SetupTester $I
     * @group GetStartedTab
     * @ticket https://github.com/laterpay/laterpay-wordpress-plugin/issues/285
     */
    public function stepReinstallPlugin(SetupTester $I) {

        SetupPage::Reinstall($I, false);
        SetupPage::Reconfigure($I, false);
    }

}

