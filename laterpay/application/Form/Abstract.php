<?php

/**
 * LaterPay abstract form class
 */
abstract class LaterPay_Form_Abstract
{

    /**
     * Form fields
     *
     * @var
     */
    protected $_fields;

    /**
     * Array of no strict names
     *
     * @var
     */
    protected $_nostrict;

    /**
     * Default filters set
     *
     * @var array
     */
    public static $filters = array(
        'text'       => 'sanitize_text_field',
        'email'      => 'sanitize_email',
        'xml'        => 'ent2ncr',
        'url'        => 'esc_url',
        'js'         => 'esc_js',
        'attr'       => 'esc_attr',
        'sql'        => 'esc_sql',
        'to_int'     => 'absint',
        'to_string'  => 'strval',
        'to_float'   => 'floatval',
        'replace'    => 'LaterPay_Form_Abstract::replace_filter',
        'format_num' => 'LaterPay_Helper_View::format_number'
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
     * @param array    $validation_options
     * @param array    $filter_options
     * @param bool     $not_strict_name
     * @return bool    field was created or already exists
     */
    public function set_field( $name, $validation_options = array(), $filter_options = array(), $not_strict_name = false ) {

        // Check if field already exists
        if ( isset( $this->_fields[$name]) ) {
            return false;
        } else {
            $current_field               = $this->_fields[$name];
            $current_field['validators'] = $validation_options;
            $current_field['filters']    = $filter_options;
            $current_field['value']      = null;

            if ($not_strict_name) {
                $this->_set_nostrict( $name );
            }
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
                    $is_valid = $this->validate_value( $field['value'], $validator_option, $validator_value);
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
            foreach ( $this->_fields as $field ) {
                $filters = $field['filters'];
                foreach ( $filters as $filter_key => $filter_value ) {
                    $filter_option = is_int( $filter_key ) ? $filter_value : $filter_key;
                    $field['value'] = $this->sanitize_value( $field['value'], $filter_option, $filter_value );
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
                $value = $sanitizer( $value, $filter_params );
            } else {
                $value = $sanitizer( $value );
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
            case 'ne':
            case 'eq':
            case 'gt':
            case 'gte':
            case 'th':
            case 'the':
                if ( $validator_params ) {
                    if ( is_array( $validator_params ) ) {
                        foreach ( $validator_params as $param) {
                            $is_valid = $this->compare_values( $validator, $value, $param );
                            if ( ! $is_valid ) {
                                break;
                            }
                        }
                    } else {
                        $is_valid = $this->compare_values( $validator, $value, $validator_params );
                    }
                }
                break;
            case 'is_int':
                $is_valid = is_int( $value );
                break;
            case 'is_string':
                $is_valid = is_string( $value );
                break;
            case 'is_float':
                $is_valid = is_float( $value );
                break;
            case 'strlen':
                if ( $validator_params ) {
                    if ( is_array( $validator_params ) ) {
                        foreach ( $validator_params as $comparison => $param) {
                            $is_valid = $this->validate_value( $comparison, strlen( $value ), $param );
                            if ( ! $is_valid ) {
                                break;
                            }
                        }
                    }
                }
                break;
            case 'in_array':
                if ( $validator_params ) {
                    if ( is_array( $validator_params ) ) {
                        $is_valid = in_array( $value, $validator_params );
                    }
                }
                break;
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
            case 'eq':
                $result = ( $first_value == $second_value );
                break;
            case 'ne':
                $result = ( $first_value != $second_value );
                break;
            case 'gt':
                $result = ( $first_value > $second_value );
                break;
            case 'gte':
                $result = ( $first_value >= $second_value );
                break;
            case 'lt':
                $result = ( $first_value < $second_value );
                break;
            case 'lte':
                $result = ( $first_value <= $second_value );
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
                    $this->_fields[$name]['value'] = $value;
                    continue;
                } elseif ( isset( $this->_nostrict ) && is_array( $this->_nostrict ) ) {
                    // If field name no strict
                    foreach ( $this->_nostrict as $field_name ) {
                        if ( strpos( $name, $field_name ) !== false ) {
                            $this->_fields[$field_name]['value'] = $value;
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
     * Get form data
     *
     * @return array
     */
    public function get_data() {

        $data = array();

        foreach ( $this->_fields as $name => $field_data) {
            $data[$name] = $field_data['value'];
        }

        return $data;
    }
}