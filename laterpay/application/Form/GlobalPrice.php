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
                'eq' => 'global_price_form'
            )
        );

        $this->set_field(
            'action',
            array(
                'eq' => 'laterpay_pricing'
            )
        );

        $this->set_field(
            '_wpnonce',
            array(
                'ne' => null
            )
        );

        $this->set_field(
            'laterpay_global_price_revenue_model',
            array(
                'in_array' => array( 'ppu', 'sis' )
            )
        );

        $this->set_field(
            'laterpay_global_price',
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