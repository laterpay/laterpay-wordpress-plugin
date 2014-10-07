<?php

/**
 * LaterPay plugin mode form class
 */
class LaterPay_Form_EnabledPostTypes extends LaterPay_Form_Abstract
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
                'eq' => 'enabled_post_types'
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
            'enabled_post_types',
            array(
                'is_array'
            )
        );
    }
}