<?php

/**
 * LaterPay core class
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Core_Event_Dispatcher implements LaterPay_Core_Event_DispatcherInterface {
    /**
     * @var LaterPay_Core_Event_Dispatcher
     */
    private static $dispatcher = null;
    public $listeners = array();
    public $sorted = array();
    public $wp_actions = array();
    private $wp_filters = array();

    /**
     * Singleton to get only one event dispatcher
     *
     * @return LaterPay_Core_Event_Dispatcher
     */
    public static function get_dispatcher() {
        if ( ! isset( self::$dispatcher ) ) {
            self::$dispatcher = new self();
        }

        return self::$dispatcher;
    }

    public function __call( $name, $args ) {
        $method = substr( $name, 0, 10 );
        laterpay_get_logger()->debug( __METHOD__, array( $name, $method, $args ) );
        switch ( $method ) {
            case 'wp_action_':
                $action = substr( $name, 10 );
                $this->run_wp_action( $action, $args );
                break;
            default:
                throw new RuntimeException( sprintf( 'Method "%s" is not found within LaterPay_Core_Event_Dispatcher class.', $name ) );
        }
    }

    /**
     * TODO
     *
     * @param $action
     */
    protected function add_wp_action( $action ) {
        add_action( $action, array( $this, 'wp_action_' . $action ) );
    }

    /**
     * TODO
     *
     * @param $action
     * @param array $args
     */
    protected function run_wp_action( $action, $args = array() ) {
        laterpay_get_logger()->debug( __METHOD__, array( $action, $args ) );
        try {
            laterpay_event_dispatcher()->dispatch( $action, $args );
        } catch ( Exception $e ) {
            // TODO: #612 handle exceptions
        }
    }

    /**
     * @see LaterPay_Core_Event_DispatcherInterface::dispatch()
     */
    public function dispatch( $event_name, $args = null ) {
        if ( is_array( $args ) ) {
            $event = new LaterPay_Core_Event( $args );
        } elseif ( $args instanceof LaterPay_Core_Event ) {
            $event = $args;
        } else {
            $event = new LaterPay_Core_Event();
        }

        if ( ! isset( $this->listeners[ $event_name ] ) ) {
            return $event;
        }

        $this->do_dispatch( $this->get_listeners( $event_name ), $event );
        if ( ! $event->is_propagation_stopped() ) {
            // apply registered in wordpress filters for the event result
            $result = apply_filters( $event_name . '_filter', $event->get_result() );
            $event->set_result( $result );
            if ( $event->is_echo_enabled() ) {
                echo laterpay_sanitized( $event->get_result() );
            }
        }
        laterpay_get_logger()->debug( $event_name, $event->get_debug() );
        return $event;
    }

    /**
     * Triggers the listeners of an event.
     *
     * @param callable[] $listeners The event listeners.
     * @param LaterPay_Core_Event $event The event object to pass to the event handlers/listeners.
     *
     * @return null
     */
    protected function do_dispatch( $listeners, LaterPay_Core_Event $event ) {
        foreach ( $listeners as $listener ) {
            $arguments = $this->get_arguments( $listener, $event );
            call_user_func_array( $listener, $arguments );
            if ( $event->is_propagation_stopped() ) {
                break;
            }
        }
    }

    /**
     * @param callable|array|object $callback The event listener.
     * @param LaterPay_Core_Event $event The event object.
     * @param array $attributes The context to get attributes.
     *
     * @return array
     */
    protected function get_arguments( $callback, LaterPay_Core_Event $event, $attributes = array() ) {
        $arguments = array();
        if ( is_array( $callback ) ) {
            $callbackReflection = new ReflectionMethod( $callback[0], $callback[1] );
        } elseif ( is_object( $callback ) ) {
            $callbackReflection = new ReflectionObject( $callback );
            $callbackReflection = $callbackReflection->getMethod( '__invoke' );
        } else {
            $callbackReflection = new ReflectionFunction( $callback );
        }

        if ( $callbackReflection->getNumberOfParameters() > 0 ) {
            $parameters = $callbackReflection->getParameters();
            foreach ( $parameters as $param ) {
                if ( array_key_exists( $param->name, $attributes ) ) {
                    $arguments[] = $attributes[ $param->name ];
                } elseif ( $param->getClass() && $param->getClass()->isInstance( $event ) ) {
                    $arguments[] = $event;
                } elseif ( $param->isDefaultValueAvailable() ) {
                    $arguments[] = $param->getDefaultValue();
                } else {
                    if ( is_array( $callback ) ) {
                        $repr = sprintf( '%s::%s()', get_class( $callback[0] ), $callback[1] );
                    } elseif ( is_object( $callback ) ) {
                        $repr = get_class( $callback );
                    } else {
                        $repr = $callback;
                    }

                    throw new RuntimeException( sprintf( 'Callback "%s" requires that you provide a value for the "$%s" argument (because there is no default value or because there is a non optional argument after this one).', $repr, $param->name ) );
                }
            }
        }

        return (array) $arguments;
    }

    /**
     * @see LaterPay_Core_Event_DispatcherInterface::get_listeners()
     */
    public function get_listeners( $event_name = null ) {
        if ( null !== $event_name ) {
            if ( ! isset( $this->sorted[ $event_name ] ) ) {
                $this->sort_listeners( $event_name );
            }

            return $this->sorted[ $event_name ];
        }

        foreach ( $this->listeners as $event_name => $event_listeners ) {
            if ( ! isset( $this->sorted[ $event_name ] ) ) {
                $this->sort_listeners( $event_name );
            }
        }

        return array_filter( $this->sorted );
    }

    /**
     * Sorts the internal list of listeners for the given event by priority.
     *
     * @param string $event_name The name of the event.
     *
     * @return null
     */
    private function sort_listeners( $event_name ) {
        $this->sorted[ $event_name ] = array();

        if ( isset( $this->listeners[ $event_name ] ) ) {
            krsort( $this->listeners[ $event_name ] );
            $this->sorted[ $event_name ] = call_user_func_array( 'array_merge', $this->listeners[ $event_name ] );
        }
    }

    /**
     * @see LaterPay_Core_Event_DispatcherInterface::has_listeners()
     */
    public function has_listeners( $event_name = null ) {
        return (bool) count( $this->get_listeners( $event_name ) );
    }

    /**
     * @see LaterPay_Core_Event_DispatcherInterface::add_subscriber()
     *
     * @api
     */
    public function add_subscriber( LaterPay_Core_Event_SubscriberInterface $subscriber ) {
        foreach ( $subscriber->get_subscribed_events() as $event_name => $params ) {
            if ( is_string( $params ) ) {
                $this->add_listener( $event_name, array( $subscriber, $params ) );
            } elseif ( is_string( $params[0] ) ) {
                $this->add_listener( $event_name, array( $subscriber, $params[0] ), isset( $params[1] ) ? $params[1] : 0 );
            } else {
                foreach ( $params as $listener ) {
                    $this->add_listener( $event_name, array( $subscriber, $listener[0] ), isset( $listener[1] ) ? $listener[1] : 0 );
                }
            }
        }
    }

    /**
     * @see LaterPay_Core_Event_DispatcherInterface::add_listener()
     */
    public function add_listener( $event_name, $listener, $priority = 0 ) {
        if ( ! in_array( $event_name, $this->wp_actions ) ) {
            $this->add_wp_action( $event_name );
            $this->wp_actions[] = $event_name;
        }

        $this->listeners[ $event_name ][ $priority ][] = $listener;
        unset( $this->sorted[ $event_name ] );
    }

    /**
     * @see LaterPay_Core_Event_DispatcherInterface::remove_subscriber()
     */
    public function remove_subscriber( LaterPay_Core_Event_SubscriberInterface $subscriber ) {
        foreach ( $subscriber->get_subscribed_events() as $event_name => $params ) {
            if ( is_array( $params ) && is_array( $params[0] ) ) {
                foreach ( $params as $listener ) {
                    $this->remove_listener( $event_name, array( $subscriber, $listener[0] ) );
                }
            } else {
                $this->remove_listener( $event_name, array( $subscriber, is_string( $params ) ? $params : $params[0] ) );
            }
        }
    }

    /**
     * @see LaterPay_Core_Event_DispatcherInterface::remove_listener()
     */
    public function remove_listener( $event_name, $listener ) {
        if ( ! isset( $this->listeners[ $event_name ] ) ) {
            return;
        }

        foreach ( $this->listeners[ $event_name ] as $priority => $listeners ) {
            if ( false !== ( $key = array_search( $listener, $listeners, true ) ) ) {
                unset( $this->listeners[ $event_name ][ $priority ][ $key ], $this->sorted[ $event_name ] );
            }
        }
    }
}
