<?php

/**
 * LaterPay enabled post types form class.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Form_CustomPurchaseOverlayOptions extends LaterPay_Form_Abstract {

    /**
     * Implementation of abstract method.
     *
     * @return void
     */
    public function init() {

        $this->set_field(
            'form',
            array(
                'validators' => array(
                    'is_string',
                    'cmp' => array(
                        array(
                            'like' => 'update_custom_overlay_options',
                        ),
                    ),
                ),
            )
        );

        $this->set_field(
            'action',
            array(
                'validators' => array(
                    'is_string',
                    'cmp' => array(
                        array(
                            'eq' => 'laterpay_pricing',
                        ),
                    ),
                ),
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
                ),
            )
        );

        $this->set_field(
            'lp_overlay_option_order',
            array(
                'can_be_null' => true,
            )
        );

        $this->set_field(
            'lp_overlay_default_selection',
            array(
                'can_be_null' => true,
            )
        );

    }
}

