<?php

/**
 * LaterPay time pass form class.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Form_Pass extends LaterPay_Form_Abstract
{

    /**
     * Implementation of abstract method.
     *
     * @return void
     */
    public function init() {
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
            'pass_id',
            array(
                'validators' => array(
                    'is_int',
                ),
                'filters' => array(
                    'to_int',
                    'unslash',
                )
            )
        );

        $this->set_field(
            'duration',
            array(
                'validators' => array(
                    'is_int',
                ),
                'filters' => array(
                    'to_int',
                    'unslash',
                )
            )
        );

        $this->set_field(
            'period',
            array(
                'validators' => array(
                    'is_int',
                    'in_array' => array_keys( LaterPay_Helper_TimePass::get_period_options() ),
                ),
                'filters'    => array(
                    'to_int',
                    'unslash',
                ),
                'can_be_null' => false,
            )
        );

        $this->set_field(
            'access_to',
            array(
                'validators' => array(
                    'is_int',
                    'in_array' => array_keys( LaterPay_Helper_TimePass::get_access_options() ),
                ),
                'filters'    => array(
                    'to_int',
                    'unslash',
                ),
                'can_be_null' => false,
            )
        );

        $this->set_field(
            'access_category',
            array(
                'validators' => array(
                    'is_int',
                ),
                'filters' => array(
                    'to_int',
                    'unslash',
                )
            )
        );

        $this->set_field(
            'price',
            array(
                'validators' => array(
                    'is_float',
                ),
                'filters' => array(
                    'delocalize',
                    'format_num' => 2,
                    'to_float',
                ),
            )
        );

        $this->set_field(
            'revenue_model',
            array(
                'validators' => array(
                    'is_string',
                    'in_array'  => array( 'sis' ),
                ),
                'filters' => array(
                    'to_string',
                ),
                'can_be_null' => true,
            )
        );

        $this->set_field(
            'title',
            array(
                'validators' => array(
                    'is_string',
                ),
                'filters' => array(
                    'to_string',
                    'unslash',
                )
            )
        );

        $this->set_field(
            'description',
            array(
                'validators' => array(
                    'is_string',
                ),
                'filters' => array(
                    'to_string',
                    'unslash',
                )
            )
        );

        $this->set_field(
            'voucher',
            array(
                'validators' => array(
                    'is_array',
                ),
                'can_be_null' => true,
            )
        );
    }
}
