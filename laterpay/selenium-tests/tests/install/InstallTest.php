<?php

class InstallTest extends Driver {

    protected function setUp() {

        parent::setUp();
        $this->setBrowserUrl(wp_back);
    }

    public function testLogin() {

        $this->url(wp_back);

        $element = $this->byName('log');
        $element->value(wp_admin_login);

        $element = $this->byName('pwd');
        $element->value(wp_admin_pass);

        $element = $this->byName('wp-submit');
        $element->click();

        $this->assertContains('Dashboard', $this->title());

        $this->takeSnapshot(__METHOD__);
    }

    public function testLogout() {

        $this->url(wp_back);
        //$this->byClassName('ab-item')->click();
        //$this->byClassName($value);
        //$items = $this->elements($this->using('css selector')->value('table.default-table input.text'));

        $this->takeSnapshot(__METHOD__);
    }

}

?>
