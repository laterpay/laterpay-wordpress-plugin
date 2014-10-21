<?php

/**
 * LaterPay plugin mode form class
 */
class LaterPay_Form_BulkPrice extends LaterPay_Form_Abstract
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
                            'eq' => 'bulk_price_form',
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
            'bulk_action',
            array(
                'validators' => array(
                    'in_array' => array( 'set', 'increase', 'reduce', 'free' ),
                ),
                'filters' => array(
                    'to_string',
                ),
            )
        );

        $this->set_field(
            'bulk_selector',
            array(
                'validators' => array(
                    'in_array' => array( 'all', 'in_category', 'not_in_category' ),
                ),
                'filters' => array(
                    'to_string',
                ),
            )
        );

        $this->set_field(
            'bulk_category',
            array(
                'validators' => array(
                    'is_int'
                ),
                'filters' => array(
                    'to_int'
                ),
                'can_be_null' => true,
            )
        );

        $this->set_field(
            'bulk_price',
            array(
                'validators' => array(
                    'is_float',
                ),
                'filters' => array(
                    'replace' => array(
                        'type'    => 'str_replace',
                        'search'  => ',',
                        'replace' => '.',
                    ),
                    'format_num' => 2,
                    'to_float',
                )
            )
        );

        $this->set_field(
            'bulk_currency',
            array(
                'validators' => array(
                    'is_string',
                    'in_array' => array( 'EUR', 'percent' ),
                ),
                'filters' => array(
                    'to_string',
                ),
                'can_be_null',
            )
        );
    }
}
