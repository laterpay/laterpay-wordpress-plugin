<?php

/**
 * LaterPay plugin mode form class
 */
class LaterPay_Form_PluginMode extends LaterPay_Form_Abstract
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
                'eq' => 'laterpay_plugin_mode'
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
            'plugin_is_in_live_mode',
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