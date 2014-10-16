<?php

/**
 * LaterPay api key form class
 */
class LaterPay_Form_Statistics extends LaterPay_Form_Abstract
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
                            'eq' => 'laterpay_post_statistic_render',
                        ),
                    ),
                ),
            )
        );

        $this->set_field(
            'nonce',
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
            'post_id',
            array(
                'validators' => array(
                    'is_int',
                    'post_exist',
                ),
                'filters' => array(
                    'to_int',
                ),
            )
        );
    }
}

