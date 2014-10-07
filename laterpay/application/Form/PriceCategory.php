<?php

/**
 * LaterPay plugin mode form class
 */
class LaterPay_Form_PriceCategory extends LaterPay_Form_Abstract
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
                'eq' => 'price_category_form'
            )
        );

        $this->set_field(
            'action',
            array(
                'eq' => 'laterpay_pricing'
            )
        );

        $this->set_field(
            'category_id',
            array(
                'is_int'
            ),
            array(
                'to_int'
            )
        );

        $this->set_field(
            '_wpnonce',
            array(
                'ne' => null
            )
        );

        $this->set_field(
            'laterpay_category_price_revenue_model',
            array(
                'in_array' => array( 'ppu', 'sis' )
            ),
            array(
                'to_string'
            ),
            true
        );

        $this->set_field(
            'category',
            array(
                'is_string'
            ),
            array(
                'to_string',
                'text'
            )
        );

        $this->set_field(
            'price',
            array(
                'is_float',
            ),
            array(
                'to_float',
                'format_num' => 2
            )
        );
    }
}