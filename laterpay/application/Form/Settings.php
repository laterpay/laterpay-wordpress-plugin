<?php

/**
 * LaterPay backend advanced settings feature form class
 */
class LaterPay_Form_Settings extends LaterPay_Form_Abstract
{

    /**
     * Implementation of abstract method.
     *
     * @return void
     */
    public function init() {
        $this->set_field(
            'action',
            array(
                'validators' => array(
                    'is_string',
                    'cmp' => array(
                        array(
                            'eq' => 'laterpay_advanced_settings',
                        ),
                    ),
                )
            )
        );

        $this->set_field(
            '_wpnonce',
            array(
                'validators' => array(
                    'is_string',
                    'cmp' => array(
                        array(
                            'ne' => null,
                        ),
                    ),
                )
            )
        );

        $this->set_field(
            'unlimited_post_access',
            array(
                'validators' => array(
                    'is_array',
                ),
                'can_be_null' => true,
            )
        );
    }
}