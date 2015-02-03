<?php

/**
 * LaterPay plugin mode form class
 */
class LaterPay_Form_PriceCategory extends LaterPay_Form_Abstract
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
                            'like' => 'price_category_form',
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
            'category_id',
            array(
                'validators' => array(
                    'is_int',
                ),
                'filters' => array(
                    'to_int',
                ),
                'can_be_null' => true,
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
            'laterpay_category_price_revenue_model',
            array(
                'validators' => array(
                    'is_string',
                    'in_array'  => array( 'ppu', 'sis' ),
                    'depends'   => array(
                        array(
                            'field' => 'price',
                            'value' => 'sis',
                            'conditions' => array(
                                'cmp' => array(
                                    array(
                                        'lte' => 149.99,
                                        'gte' => 1.49,
                                    ),
                                ),
                            ),
                        ),
                        array(
                            'field' => 'price',
                            'value' => 'ppu',
                            'conditions' => array(
                                'cmp' => array(
                                    array(
                                        'lte' => 5.00,
                                        'gte' => 0.05,
                                    ),
                                    array(
                                        'eq' => 0.00,
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                'filters' => array(
                    'to_string',
                ),
                'not_strict_name' => true,
            )
        );

        $this->set_field(
            'category',
            array(
                'validators'    => array(
                    'is_string',
                ),
                'filters' => array(
                    'to_string',
                    'text',
                ),
            )
        );

        $this->set_field(
            'price',
            array(
                'validators' => array(
                    'is_float',
                    'cmp' => array(
                        array(
                            'lte' => 149.99,
                            'gte' => 0.05,
                        ),
                        array(
                            'eq'  => 0.00,
                        ),
                    ),
                ),
                'filters' => array(
                    'replace' => array(
                        'type'    => 'str_replace',
                        'search'  => ',',
                        'replace' => '.',
                    ),
                    'format_num' => 2,
                    'to_float',
                ),
            )
        );
    }
}

