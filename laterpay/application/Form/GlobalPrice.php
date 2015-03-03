<?php

/**
 * LaterPay global price class.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Form_GlobalPrice extends LaterPay_Form_Abstract
{

    /**
     * Implementation of abstract method.
     *
     * @return void
     */
    public function init() {
        $this->set_field(
            'form',
            array(
                'validators' => array(
                    'is_string',
                    'cmp' => array(
                        array(
                            'eq' => 'global_price_form',
                        ),
                    ),
                ),
            )
        );

        $this->set_field(
            'action',
            array(
                'validators' => array(
                    'is_string',
                    'cmp' => array(
                        array(
                            'eq' => 'laterpay_pricing',
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
            'laterpay_global_price_revenue_model',
            array(
                'validators' => array(
                    'is_string',
                    'in_array' => array( 'ppu', 'sis' ),
                    'depends' => array(
                        array(
                            'field' => 'laterpay_global_price',
                            'value' => 'sis',
                            'conditions' => array(
                                'cmp' => array(
                                    array(
                                        'lte' => 149.99,
                                        'gte' => 1.49,
                                    ),
                                ),
                            ),
                        ),
                        array(
                            'field' => 'laterpay_global_price',
                            'value' => 'ppu',
                            'conditions' => array(
                                'cmp' => array(
                                    array(
                                        'lte' => 5.00,
                                        'gte' => 0.05,
                                    ),
                                    array(
                                         'eq' => 0.00,
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                'filters' => array(
                    'to_string'
                ),
            )
        );

        $this->set_field(
            'laterpay_global_price',
            array(
                'validators' => array(
                    'is_float',
                    'cmp' => array(
                        array(
                            'lte' => 149.99,
                            'gte' => 0.05,
                        ),
                        array(
                            'eq' => 0.00,
                        ),
                    ),
                ),
                'filters' => array(
                    'replace' => array(
                        'type'    => 'str_replace',
                        'search'  => ',',
                        'replace' => '.',
                    ),
                    'format_num' => 2,
                    'to_float',
                ),
            )
        );
    }
}

