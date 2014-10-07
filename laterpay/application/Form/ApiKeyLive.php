<?php

/**
 * LaterPay api key form class
 */
class LaterPay_Form_MerchantIdSandbox extends LaterPay_Form_Abstract
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
                'eq' => 'laterpay_live_api_key'
            )
        );

        $this->set_field(
            'action',
            array(
                'eq' => 'laterpay_account'
            )
        );

        $this->set_field(
            '_wpnonce',
            array(
                'ne' => null
            )
        );

        $this->set_field(
            'laterpay_live_api_key',
            array(
                'is_string',
                'strlen' => array( 'lte' => 32 )
            ),
            array(
                'to_string',
                'text'
            )
        );
    }
}