<?php

/**
 * LaterPay api key form class
 */
class LaterPay_Form_PaidContentPreview extends LaterPay_Form_Abstract
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
                'eq' => 'paid_content_preview'
            )
        );

        $this->set_field(
            'action',
            array(
                'eq' => 'laterpay_appearance'
            )
        );

        $this->set_field(
            '_wpnonce',
            array(
                'ne' => null
            )
        );

        $this->set_field(
            'paid_content_preview',
            array(
                'is_int',
                'in_array' => array( 0, 1 )
            ),
            array(
                'to_int'
            )
        );
    }
}