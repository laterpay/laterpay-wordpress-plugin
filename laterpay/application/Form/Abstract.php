<?php

/**
 * LaterPay abstract form class
 */
abstract class LaterPay_Form_Abstract
{

    /**
     * Form fields
     *
     * @var array
     */
    protected $_fields;

    /**
     * Array of no strict names
     *
     * @var array
     */
    protected $_nostrict;

    /**
     * Default filters set
     *
     * @var array
     */
    public static $filters = array(
        // sanitize string value
        'text'       => 'sanitize_text_field',
        // sanitize email
        'email'      => 'sanitize_email',
        // sanitize xml
        'xml'        => 'ent2ncr',
        // sanitize url
        'url'        => 'esc_url',
        // sanitize js
        'js'         => 'esc_js',
        // sanitize sql
        'sql'        => 'esc_sql',
        // convert to int, abs
        'to_int'     => 'absint',
        // convert to string
        'to_string'  => 'strval',
        // convert to float
        'to_float'   => 'floatval',
        // replace part of value with other
        // params:
        // type    - replace type (str_replace, preg_replace)
        // search  - searched value or pattern
        // replace - replacement
        'replace'    => array('LaterPay_Form_Abstract', 'replace_filter'),
        // format number to passed amount of numbers after point
        'format_num' => array('LaterPay_Helper_View', 'format_number')
    );

    /**
     * Default validators set
     *
     * @var array
     */
    public static $validators = array(

    );

    /**
     * Constructor
     *
     * @param array $data
     *
     * @return void
     */
    public final function __construct( $data = array() ) {

        // Call init method from child class
        $this->init();

        // Set data into form if it specified
        if ( ! empty( $data ) ) {
            $this->set_data( $data );
        }
    }

    /**
     * Init form
     *
     * @return void
     */
    abstract protected function init();

    /**
     * Set new field, options for its validation and filter options (sanitizer)
     *
     * @param          $name
     * @param array    $options
     * @return bool    field was created or already exists
     */
    public function set_field( $name, $options = array() ) {

        // Check if field already exists
        if ( isset( $this->_fields[$name]) ) {
            return false;
        } else {
            // field name
            $current_field                    = array();
            // validators
            $current_field['validators']      = isset( $options['validators'] ) ? $options['validators'] : array();
            // filters (sanitize)
            $current_field['filters']         = isset( $options['filters'] ) ? $options['filters'] : array();
            // default value
            $current_field['value']           = isset( $options['default_value'] ) ? $options['default_value'] : null;
            // do not apply filters to null value
            $current_field['can_be_null']     = isset( $options['can_be_null'] ) ? $options['can_be_null'] : false;

            // name not strict, value searched in data by part of the name (for dynamic params)
            if ( isset( $options['not_strict_name'] ) && $options['not_strict_name'] ) {
                $this->_set_nostrict( $name );
            }

            $this->_fields[$name] = $current_field;
        }

        return true;
    }

    /**
     * Get all fields
     *
     * @return array
     */
    public function get_fields() {

        return $this->_fields;
    }

    /**
     * Get field value
     *
     * @param $field_name
     * @return mixed
     */
    public function get_field_value( $field_name ) {

        $fields = $this->get_fields();

        if ( isset( $fields[$field_name] ) ) {
            return $fields[$field_name]['value'];
        }

        return null;
    }

    /**
     * Set field value
     *
     * @param $field_name
     * @param $value
     * @return void
     */
    protected function _set_field_value( $field_name, $value ) {

        $this->_fields[$field_name]['value'] = $value;
    }

    /**
     * Add field name to nostrict array
     *
     * @param $name
     * @return void
     */
    protected function _set_nostrict( $name ) {

        if ( ! isset( $this->_nostrict ) ) {
            $this->_nostrict = array();
        }

        array_push( $this->_nostrict, $name );
    }

    /**
     * Validate data in fields
     *
     * @param $data
     *
     * @return bool is data valid
     */
    public function is_valid( $data = array() ) {

        // If data passed set data to the form
        if ( ! empty( $data ) ) {
            $this->set_data( $data );
        }

        // Set to false by default, probably data missed
        $is_valid = false;

        // Validation logic
        if ( is_array( $this->_fields ) ) {
            foreach ( $this->_fields as $field ) {
                $validators = $field['validators'];
                foreach ( $validators as $validator_key => $validator_value ) {
                    $validator_option = is_int( $validator_key ) ? $validator_value : $validator_key;
                    $validator_params = is_int( $validator_key ) ? null : $validator_value;
                    $is_valid = $this->validate_value( $field['value'], $validator_option, $validator_params);
//                    var_dump($field['value']);
//                    var_dump($validator_option, $is_valid);
                    if ( ! $is_valid ) {
                        // Data not valid
                        return false;
                    }
                }
            }
        }

        return $is_valid;
    }

    /**
     * Apply filters to form data
     *
     * @return void
     */
    protected function _sanitize() {

        // get all form filters
        if ( is_array( $this->_fields ) ) {
            foreach ( $this->_fields as $name => $field ) {
                $filters = $field['filters'];
                foreach ( $filters as $filter_key => $filter_value ) {
                    $filter_option = is_int( $filter_key ) ? $filter_value : $filter_key;
                    $filter_params = is_int( $filter_key ) ? null : $filter_value;
                    $this->_set_field_value( $name, $this->sanitize_value( $this->_fields[$name]['value'], $filter_option, $filter_params ));
                }
            }
        }
    }

    /**
     * Apply filter to the value
     *
     * @param $value
     * @param $filter
     * @param null $filter_params
     * @return mixed
     */
    public function sanitize_value( $value, $filter, $filter_params = null ) {

        // sanitize value according to selected filter
        $sanitizer = isset( self::$filters[$filter] ) ? self::$filters[$filter] : '';

        if ( $sanitizer && is_callable( $sanitizer ) ) {
            if ( $filter_params ) {
                $value = call_user_func( $sanitizer, $value, $filter_params );
            } else {
                $value = call_user_func( $sanitizer, $value );
            }
        }

        return $value;
    }

    /**
     * Call str_replace with array of options
     *
     * @param $value
     * @param $options
     * @return mixed
     */
    public static function replace_filter( $value, $options ) {

        if ( is_array( $options ) && isset( $options['type'] ) && is_callable( $options['type'] ) ) {
            $value = $options['type']( $options['search'], $options['replace'], $value );
        }

        return $value;
    }

    /**
     * Validate value by selected validator and its value optionally
     *
     * @param $value
     * @param $validator
     * @param null $validator_param
     * @return bool
     */
    public function validate_value( $value, $validator, $validator_params = null ) {

        $is_valid = false;

        switch( $validator ) {
            // compare value with set
            case 'cmp':
                if ( $validator_params ) {
                    if ( is_array( $validator_params ) ) {
                        // OR realization, all validators inside validators set used like AND
                        // if at least one set correct then validation passed
                        foreach ( $validator_params as $validators_set) {
                            foreach ($validators_set as $operator => $param ) {
                                $is_valid = $this->compare_values( $operator, $value, $param );
                                // if comparison not valid break the loop and go to the next validation set
                                if ( ! $is_valid ) {
                                    break;
                                }
                            }

                            // if comparison valid after full validation set check then do not need to check others
                            if ( $is_valid ) {
                                break;
                            }
                        }
                    }
                }
                break;
            // check if value is int
            case 'is_int':
                $is_valid = is_int( $value );
                break;
            // check if value is string
            case 'is_string':
                $is_valid = is_string( $value );
                break;
            // check if value is float
            case 'is_float':
                $is_valid = is_float( $value );
                break;
            // check string length
            case 'strlen':
                if ( $validator_params ) {
                    if ( is_array( $validator_params ) ) {
                        foreach ( $validator_params as $extra_validator => $validator_data ) {
                            // recursively call extra validator
                            $is_valid = $this->validate_value( strlen( $value ), $extra_validator, $validator_data );
                            // break loop if something not valid
                            if ( ! $is_valid ) {
                                break;
                            }
                        }
                    }
                }
                break;
            // check if value in array
            case 'in_array':
                if ( $validator_params ) {
                    if ( is_array( $validator_params ) ) {
                        $is_valid = in_array( $value, $validator_params );
                    }
                }
                break;
            // check if value is array
            case 'is_array':
                $is_valid = is_array( $value );
                break;
            default:
                // Incorrect validator specified, do nothing
                break;
        }

        return $is_valid;
    }

    /**
     * Compare 2 values
     *
     * @param $comparison_operator
     * @param $first_value
     * @param $second_value
     * @return bool
     */
    protected function compare_values($comparison_operator, $first_value, $second_value) {

        $result = false;

        switch( $comparison_operator ) {
            // equal ==
            case 'eq':
                $result = ( $first_value == $second_value );
                break;
            // not equal !=
            case 'ne':
                $result = ( $first_value != $second_value );
                break;
            // greater than >
            case 'gt':
                $result = ( $first_value > $second_value );
                break;
            // greater than or equal >=
            case 'gte':
                $result = ( $first_value >= $second_value );
                break;
            // less than <
            case 'lt':
                $result = ( $first_value < $second_value );
                break;
            // less than or equal <=
            case 'lte':
                $result = ( $first_value <= $second_value );
                break;
            // search if string present in value
            case 'like':
                $result = ( strpos($first_value, $second_value ) !== false );
                break;
            default:
                // Incorrect comparison operator, do nothing
            break;
        }

        return $result;
    }

    /**
     * Set data into fields and sanitize
     *
     * @param $data
     *
     * @return $this
     */
    public function set_data( $data ) {

        // Set data and sanitize it
        if ( is_array( $data ) ) {
            foreach ($data as $name => $value) {
                // Set only if name field was created
                if ( isset( $this->_fields[$name] ) ) {
                    $this->_set_field_value( $name, $value );
                    continue;
                } elseif ( isset( $this->_nostrict ) && is_array( $this->_nostrict ) ) {
                    // If field name no strict
                    foreach ( $this->_nostrict as $field_name ) {
                        if ( strpos( $name, $field_name ) !== false ) {
                            $this->_set_field_value( $field_name, $value );
                            break;
                        }
                    }
                }
            }

            // Sanitize data if filters was specified
            $this->_sanitize();
        }

        return $this;
    }

    /**
     * Get form values
     *
     * @return array
     */
    public function get_form_values() {

        $data = array();

        foreach ( $this->_fields as $name => $field_data) {
            $data[$name] = $field_data['value'];
        }

        return $data;
    }
}