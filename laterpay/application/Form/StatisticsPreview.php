<?php

/**
 * LaterPay api key form class
 */
class LaterPay_Form_StatisticsPreview extends LaterPay_Form_Abstract
{

    /**
     * Implementation of abstract method
     *
     * @return void
     */
    public function init() {
        $this->set_field(
            'preview_post',
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

