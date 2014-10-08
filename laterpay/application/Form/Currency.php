<?php

/**
 * LaterPay plugin mode form class
 */
class LaterPay_Form_Currency extends LaterPay_Form_Abstract
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
                            'eq' => 'currency_form'
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
            'laterpay_currency',
            array(
                'validators' => array(
                    'in_array' => array( 'USD', 'EUR' )
                )
            )
        );
    }
}