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
                'ne' => null
            )
        );

        $this->set_field(
            'laterpay_pricing_post_content_box_nonce',
            array(
                'ne' => null
            )
        );

        $this->set_field(
            'post-price',
            array(
                'is_float',
            ),
            array(
                'to_float',
                'format_num' => 2
            )
        );

        $this->set_field(
            'post_revenue_model',
            array(
                'in_array' => array( 'ppu', 'sis' )
            )
        );

        $this->set_field(
            'post_price_type',
            array(
                'in_array' => array( 'individual price', 'category default price', 'global default price' )
            )
        );

        $this->set_field(
            'laterpay_post_default_category',
            array(
                'is_int'
            ),
            array(
                'to_int'
            )
        );
    }
}