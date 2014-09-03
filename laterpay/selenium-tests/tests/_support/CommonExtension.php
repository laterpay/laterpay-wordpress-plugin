<?php

class CommonExtension extends \Codeception\Platform\Extension {

    // list events to listen to
    public static $events = array(
        'suite.after' => 'afterSuite',
        'test.before' => 'beforeTest',
        'step.before' => 'beforeStep',
        'test.fail' => 'testFailed',
        'result.print.after' => 'resultPrintAfter',
    );

    // methods that handle events

    public function afterSuite(\Codeception\Event\SuiteEvent $e) {

    }

    public function beforeTest(\Codeception\Event\TestEvent $e) {

    }

    public function beforeStep(\Codeception\Event\StepEvent $e) {

    }

    public function testFailed(\Codeception\Event\FailEvent $e) {

    }

    public function resultPrintAfter(\Codeception\Event\PrintResultEvent $e) {

    }

}

?>