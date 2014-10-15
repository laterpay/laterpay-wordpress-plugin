<?php

/**
 * LaterPay api key form class
 */
class LaterPay_Form_StatisticsVisibility extends LaterPay_Form_Abstract
{

    /**
     * Implementation of abstract method
     *
     * @return void
     */
    public function init() {
        $this->set_field(
            'hide_statistics_pane',
            array(
                'validators' => array(
                    'is_int',
                ),
                'filters' => array(
                    'to_int',
                ),
                'can_be_null',
            )
        );
    }
}

