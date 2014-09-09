<?php

class BackendModule extends BaseModule {

    /**
     * Login
     */
    public function login() {

        $I = $this->BackendTester;

        $I->amGoingTo('Login backend');

        $I->amOnPage(LoginPage::$URL);

        if (!$I->hSee($I, LoginPage::$expectedBackTitle)) {

            $I->fillField(LoginPage::$usernameField, LoginPage::$usernameValue);
            $I->fillField(LoginPage::$passwordField, LoginPage::$passwordValue);
            $I->click(LoginPage::$loginButton);
        };

        return $this;
    }

    /**
     * Logout
     */
    public function logout() {

        $I = $this->BackendTester;

        if ($I->hSee($I, LoginPage::$expectedBackTitle)) {

            $I->moveMouseOver(LoginPage::$logoutMenu);

            $I->click(LoginPage::$logoutButton);
        };

        return $this;
    }

    /**
     * P.43-44
     * Price Validation {price input, confirmation link, change link}
     * Is a price validated successfully?
     */
    public function validatePrice($price_input = 0, $change_link = null, $confirmation_link = null) {

        $I = $this->BackendTester;

        foreach (PluginPage::$priceValidationArray as $expectedValue => $arrayOfInputValues)
            foreach ($arrayOfInputValues as $InputValue) {

                if ($change_link)
                    $I->hClick($I, $change_link);

                $I->fillField($price_input, $InputValue);

                if ($confirmation_link)
                    $I->click($confirmation_link);

                $I->see($expectedValue, PluginPage::$globalPriceText);
            };
    }
}

