<?php

/**
 * LaterPay API key form class
 */
class LaterPay_Form_MerchantId extends LaterPay_Form_Abstract
{

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
                            'like' => 'merchant_id',
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
                            'eq' => 'laterpay_account',
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
            'merchant_id',
            array(
                'validators' => array(
                    'is_string',
                    'match' => '/[a-zA-Z0-9\-]{22}/',
                ),
                'filters' => array(
                    'to_string',
                    'text',
                ),
                'not_strict_name' => true,
            )
        );
    }
}

