<?php

/**
 * LaterPay plugin mode form class
 */
class LaterPay_Form_Post extends LaterPay_Form_Abstract
{

    /**
     * Implementation of abstract method
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
                            'ne' => null
                        )
                    )
                )
            )
        );

        $this->set_field(
            'laterpay_pricing_post_content_box_nonce',
            array(
                'validators' => array(
                    'is_string',
                    'cmp' => array(
                        array(
                            'ne' => null
                        )
                    )
                )
            )
        );

        $this->set_field(
            'post-price',
            array(
                'validators' => array(
                    'is_float'
                ),
                'filters' => array(
                    'replace' => array(
                        'type'    => 'str_replace',
                        'search'  => ',',
                        'replace' => '.'
                    ),
                    'format_num' => 2,
                    'to_float'
                )
            )
        );

        $this->set_field(
            'post_revenue_model',
            array(
                'validators' => array(
                    'in_array' => array( 'ppu', 'sis' )
                )
            )
        );

        $this->set_field(
            'post_price_type',
            array(
                'validators' => array(
                    'in_array' => array( 'individual price', 'category default price', 'global default price' )
                )
            )
        );

        $this->set_field(
            'laterpay_post_default_category',
            array(
                'validators' => array(
                    'is_int'
                ),
                'filters' => array(
                    'to_int'
                )
            )
        );
    }
}