<?php

/**
 * LaterPay api key form class
 */
class LaterPay_Form_ApiKeySandbox extends LaterPay_Form_Abstract
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
                'eq' => 'laterpay_sandbox_api_key'
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
            'laterpay_sandbox_api_key',
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