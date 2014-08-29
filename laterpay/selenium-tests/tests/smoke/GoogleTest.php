<?php

class GoogleTest extends Driver {

    protected function setUp() {

        parent::setUp();
        $this->setBrowserUrl('http://www.google.com/');
    }

    protected function tearDown() {

        parent::tearDown();
    }

    public function testTitle() {

        $this->url('http://google.com/');

        $this->assertEquals('Google', $this->title(), 'OK!!');

        $this->takeSnapshot(__METHOD__);
    }

    public function testFake() {

        $this->url('http://google.com/');
        $this->assertEquals('Fake', $this->title(), 'fake!!');
    }

}

?>
