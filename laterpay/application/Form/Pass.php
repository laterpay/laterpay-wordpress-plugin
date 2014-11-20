<?php

/**
 * LaterPay plugin mode form class
 */
class LaterPay_Form_Pass extends LaterPay_Form_Abstract
{

    /**
     * Implementation of abstract method.
     *
     * @return void
     */
    public function init() {
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
            'pass_id',
            array(
                'validators' => array(
                    'is_string',
                ),
                'filters' => array(
                    'to_string',
                    'unslash',
                )
            )
        );

        $this->set_field(
            'duration',
            array(
                'validators' => array(
                    'is_int',
                ),
                'filters' => array(
                    'to_int',
                    'unslash',
                )
            )
        );

        $this->set_field(
            'period',
            array(
                'validators' => array(
                    'is_int',
                    'in_array' => LaterPay_Helper_Passes::$periods
                ),
                'filters'    => array(
                    'to_int',
                    'unslash',
                ),
                'can_be_null' => false,
            )
        );

        $this->set_field(
            'access_to',
            array(
                'validators' => array(
                    'is_int',
                    'in_array' => LaterPay_Helper_Passes::$access_to
                ),
                'filters'    => array(
                    'to_int',
                    'unslash',
                ),
                'can_be_null' => false,
            )
        );

        $this->set_field(
            'access_category',
            array(
                'validators' => array(
                    'is_int',
                ),
                'filters' => array(
                    'to_int',
                    'unslash',
                )
            )
        );

        $this->set_field(
            'price',
            array(
                'validators' => array(
                    'is_string',
                ),
                'filters' => array(
                    'to_float',
                    'unslash',
                )
            )
        );

        $this->set_field(
            'revenue_model',
            array(
                'validators' => array(
                    'is_string',
                    'in_array' => LaterPay_Helper_Passes::$revenue_model
                ),
                'filters' => array(
                    'to_string',
                    'unslash',
                )
            )
        );

        $this->set_field(
            'title',
            array(
                'validators' => array(
                    'is_string',
                ),
                'filters' => array(
                    'to_string',
                    'unslash',
                )
            )
        );

        $this->set_field(
            'title_color',
            array(
                'validators' => array(
                    'is_string',
                ),
                'filters' => array(
                    'to_string',
                    'unslash',
                )
            )
        );

        $this->set_field(
            'description',
            array(
                'validators' => array(
                    'is_string',
                ),
                'filters' => array(
                    'to_string',
                    'unslash',
                )
            )
        );

        $this->set_field(
            'description_color',
            array(
                'validators' => array(
                    'is_string',
                ),
                'filters' => array(
                    'to_string',
                    'unslash',
                )
            )
        );

        $this->set_field(
            'background_path',
            array(
                'validators' => array(
                    'is_string',
                ),
                'filters' => array(
                    'to_string',
                    'unslash',
                )
            )
        );

        $this->set_field(
            'background_color',
            array(
                'validators' => array(
                    'is_string',
                ),
                'filters' => array(
                    'to_string',
                    'unslash',
                )
            )
        );
    }
}

