<?php

/**
 * Event is the base class for classes containing event data.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Core_Event {

    /**
     * Event name.
     *
     * @var string Event name.
     */
    protected $name;

    /**
     * Should be event result output
     */
    protected $echo = true;

    /**
     * Event result
     * @var mixed
     */
    protected $result = null;

    /**
     * Array of arguments.
     *
     * @var array
     */
    protected $arguments;

    /**
     * @var bool Whether no further event listeners should be triggered
     */
    private $propagations_stopped = false;

    /**
     * @var bool who has stopped event
     */
    private $propagations_stopped_by = '';

    /**
     * Encapsulate an event with $args.
     *
     * @param array $arguments Arguments to store in the event.
     */
    public function __construct( array $arguments = array() ) {
        $this->arguments = $arguments;
    }

    /**
     * Returns whether further event listeners should be triggered.
     *
     * @return bool Whether propagation was already stopped for this event.
     */
    public function is_propagation_stopped() {
        return $this->propagations_stopped;
    }

    public function set_propagations_stopped_by( $listener ) {
        if ( is_array( $listener ) && is_object( $listener[0] ) ) {
            $name = '[[object] (' . get_class( $listener[0] ) .  ': {}),"' . ( isset( $listener[1] ) ? $listener[1] : '__invoke' ) . '"]';
        } else {
            $name = (string) $listener;
        }
        $this->propagations_stopped_by = $name;
    }

    /**
     * Stops the propagation of the event to further event listeners.
     *
     * @return null
     */
    public function stop_propagation() {
        $this->propagations_stopped = true;
    }

    /**
     * Getter for all arguments.
     *
     * @return array
     */
    public function get_arguments() {
        return $this->arguments;
    }

    /**
     * Set args property.
     *
     * @param array $args Arguments.
     *
     * @return LaterPay_Core_Event
     */
    public function set_arguments( array $args = array() ) {
        $this->arguments = $args;

        return $this;
    }

    /**
     * Get argument by key.
     *
     * @param string $key Key.
     *
     * @throws InvalidArgumentException If key is not found.
     *
     * @return mixed Contents of array key.
     */
    public function get_argument( $key ) {
        if ( $this->has_argument( $key ) ) {
            return $this->arguments[ $key ];
        }
        throw new InvalidArgumentException( sprintf( 'Argument "%s" not found.', $key ) );
    }

    /**
     * Has argument.
     *
     * @param string $key Key of arguments array.
     *
     * @return bool
     */
    public function has_argument( $key ) {
        return array_key_exists( $key, $this->arguments );
    }

    /**
     * Add argument to event.
     *
     * @param string $key Argument name.
     * @param mixed $value Value.
     *
     * @return LaterPay_Core_Event
     */
    public function set_argument( $key, $value ) {
        $this->arguments[ $key ] = $value;

        return $this;
    }

    /**
     * Safety adds arguments to event. if such argument is already present appends new one
     *
     * @param string $key Argument name.
     * @param mixed $value Value.
     *
     * @return LaterPay_Core_Event
     */
    public function add_argument( $key, $value ) {
        if ( $this->has_argument( $key ) ) {
            $argument = $this->get_argument( $key );
            if ( ! is_array( $argument ) ) {
                $argument = array( $argument );
            }
            if ( ! is_array( $value ) ) {
                $value = array( $value );
            }
            $this->set_argument( $key, array_merge( $argument, $value ) );
        } else {
            $this->set_argument( $key, $value );
        }

        return $this;
    }

    /**
     * Get event result.
     *
     * @return mixed
     */
    public function get_result() {
        return $this->result;
    }

    /**
     * Set event result.
     *
     * @param mixed $value Value.
     *
     * @return LaterPay_Core_Event
     */
    public function set_result( $value ) {
        $this->result = $value;
        return $this;
    }

    /**
     * Return flag if we should output event result.
     *
     * @return bool
     */
    public function is_echo_enabled() {
        return $this->echo;
    }

    /**
     * Set flag that we should output event result.
     *
     * @param bool $echo
     * @return LaterPay_Core_Event
     */
    public function set_echo( $echo ) {
        $this->echo = $echo;

        return $this;
    }

    /**
     * Gets debug information
     *
     * @return array
     */
    public function get_debug() {
        return array(
            'is_echo_enabled'           => $this->is_echo_enabled() ? 'true' : 'false',
            'is_propagation_stopped'    => $this->is_propagation_stopped() ? 'true' : 'false',
            'propagation_stopped_by'    => $this->propagations_stopped_by,
            'arguments'                 => $this->get_arguments(),
            'result'                    => $this->get_result(),
        );
    }

    /**
     * Set event name.
     *
     * @return string
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * Get event name.
     *
     * @param string $name
     */
    public function set_name( $name ) {
        $this->name = $name;
    }

}
