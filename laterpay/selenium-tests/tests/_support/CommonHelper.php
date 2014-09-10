<?php

namespace Codeception\Module;

class CommonHelper extends \Codeception\Module {

    /**
     * For test development purposes. Runs DevPage::start()
     * @param \Tester $I
     * Usage examples:
      mod($I,'BackendModule','login');
      amOnPage(PostModule::$pagePostNew);
      amOnPage(PostModule::$pagePostList);
      click('a[class=".lp_activate-plugin-button"]');
      click(PostModule::$linkGlobalDefaultPrice);

      makeScreenshot(1);

      fillField(PostModule::$fieldContent,1);

      see('USD', PostModule::$visibleLaterpayPurchaseButton);

      executeJS(" tinymce.activeEditor.selection.setContent('".str_replace(array("\r","\n"),'',BaseModule::$C1)."'); ");

      click('LaterPay WordPress Plugin Test Post');
      see(0.35, PostModule::$visibleLaterpayPurchaseButton);
      see('USD', PostModule::$visibleLaterpayPurchaseButton);
      acceptPopup();
      see('0.35', 'a[class="lp_purchase-link lp_button"]');
     *
     *
      click('Uncategorized');
     *
     * //
      fillField(PostModule::$fieldTitle, BaseModule::$T1);
      executeJS(" tinymce.activeEditor.selection.setContent('".BaseModule::$C1."'); ");
     * executeJS(" tinymce.activeEditor.selection.setContent('Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam
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
      nonumy eirmod tempor invidunt ut labore.'); ");
      makeScreenshot('console');
     */
    public function mod($I, $module, $method) {

        $module::of($I)->$method($I);
    }

    /**
     * Helper to have ability if-then-else condition
     * @param \SetupTester $I
     * @param String $I
     */
    public function trySee($I, $string) {

        try {

            $I->see($string);
            return true;
        } catch (\PHPUnit_Framework_AssertionFailedError $f) {

            return false;
        };
    }

    /**
     * Helper to have ability non mandatory mouse click
     * @param \SetupTester $I
     * @param String $I
     */
    public function tryClick($I, $string) {

        try {

            $I->click($string);
        } catch (\PHPUnit_Framework_AssertionFailedError $f) {

        };
    }

    /**
     * Helper to have ability non mandatory mouse click
     * @param \SetupTester $I
     * @param String $I
     */
    public function tryCheckbox($I, $string) {

        try {

            $I->seeCheckboxIsChecked($string);
            return true;
        } catch (\PHPUnit_Framework_AssertionFailedError $f) {

            return false;
        };
    }

    public function setVar($k, $v) {

        $this->k = $v;
    }

    public function getVar($k) {

        return $this->k;
    }

    /**
     * @param String $I
     * @param String $I
     */
    public function _failed(\Codeception\TestCase $test, $fail) {

        file_put_contents(\Codeception\Configuration::logDir() . basename($test->getFileName()) . '.page.debug.html', $fail);
    }

    /**
     * @param String $I
     */
    public function log($message) {

        file_put_contents(\Codeception\Configuration::logDir() . 'debug.html', $message);
    }

}

