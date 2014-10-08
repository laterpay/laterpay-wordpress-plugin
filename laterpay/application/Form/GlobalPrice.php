<?php

/**
 * LaterPay plugin mode form class
 */
class LaterPay_Form_GlobalPrice extends LaterPay_Form_Abstract
{

    /**
     * Implementation of abstract method
     *
     * @return void
     */
    public function init() {

        $this->set_field(
            'form',
            array(
                'validators' => array(
                    'cmp' => array(
                        array(
                            'eq' => 'global_price_form'
                        )
                    )
                )
            )
        );

        $this->set_field(
            'action',
            array(
                'validators' => array(
                    'cmp' => array(
                        array(
                            'eq' => 'laterpay_pricing'
                        )
                    )
                )
            )
        );

        $this->set_field(
            '_wpnonce',
            array(
                'validators' => array(
                    'cmp' => array(
                        array(
                            'ne' => null
                        )
                    )
                )
            )
        );

        $this->set_field(
            'laterpay_global_price_revenue_model',
            array(
                'validators' => array(
                    'in_array' => array( 'ppu', 'sis' )
                ),
                'filters' => array(
                    'to_string'
                )
            )
        );

        $this->set_field(
            'laterpay_global_price',
            array(
                'validators' => array(
                    'is_float',
                    // TODO: this is just a dirty hack to allow saving Single Sale prices
                    'cmp' => array(
                        array(
                            'lte'  => 149.99,
                            'gt'   => 0
                        )
                    )
                ),
                'filters' => array(
                    'replace' => array(
                        'type'    => 'str_replace',
                        'search'  => ',',
                        'replace' => '.'
                    ),
                    'to_float',
                    'format_num' => 2
                )
            )
        );
    }
}