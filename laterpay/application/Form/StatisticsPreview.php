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
            'action',
            array(
                'validators' => array(
                    'is_string',
                    'cmp' => array(
                        array(
                            'eq' => 'laterpay_post_statistic_toggle_preview',
                        ),
                    ),
                ),
            )
        );

        $this->set_field(
            '_wpnonce',
            array(
                'validators' => array(
                    'is_string',
                    'cmp' => array(
                        array(
                            'ne' => null,
                        ),
                    ),
                ),
            )
        );

        $this->set_field(
            'preview_post',
            array(
                'validators' => array(
                    'is_int',
                ),
                'filters' => array(
                    'to_int',
                ),
            )
        );
    }
}

