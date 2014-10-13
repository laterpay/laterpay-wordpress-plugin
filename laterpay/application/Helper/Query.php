<?php

class LaterPay_Helper_Query
{

    /**
     * @var string
     */
    protected $last_query = '';

    /**
     * @var string
     */
    protected $table = '';

    /**
     * @var string
     */
    protected $table_short = '';

    /**
     * @var array
     */
    protected $field_types = array();

    /**
     * @return string $sql
     */
    public function build_from( ){
        $sql = ' FROM ' . $this->table;
        if( $this->table_short !== '' ){
            $sql .= ' AS ' . $this->table_short;
        }
        return $sql;
    }

    /**
     *
     * @return string $suffix
     */
    public function get_row_suffix(){
        $suffix = '';
        if( !empty( $this->short_from ) ){
            $suffix = $this->short_from . '.';
        }
        else if( !empty( $this->from ) ){
            $suffix = $this->from . '.';
        }
        return $suffix;
    }

    /**
     *
     * @param int $limit
     * @return string $sql
     */
    public static function build_limit( $limit ){
        if ( empty( $limit ) ){
            return '';
        }
        return ' LIMIT ' . absint( $limit ) . ' ';
    }

    /**
     *
     * @param string $order_by
     * @param string $order
     *
     * @return string $sql
     */
    public function build_order_by( $order_by, $order = 'ASC' ){
        if ( empty( $order_by ) ) {
            return '';
        }
        $sql = ' ORDER BY ' . $this->get_row_suffix() . $order_by;
        if ( ! in_array( $order, array( 'ASC', 'DESC' ) ) ) {
            $order = 'ASC';
        }
        return $sql . ' ' . $order . ' ' ;
    }

    /**
     *
     * @param string $group
     * @return string $sql
     */
    public function build_group_by( $group ){
        if ( empty( $group ) ) {
            return '';
        }
        return ' GROUP BY ' . $group;
    }

    /**
     *
     * @param array $fields
     * @return string $sql
     */
    public function build_select( $fields = array() ){
        if ( empty( $fields ) ) {
            return ' SELECT * ';
        }
        return ' SELECT ' . implode( ', ', $fields );
    }

    /**
     *
     * @param array $where
     * @return string $sql
     */
    public function build_where( $where = array() ){
        global $wpdb;

        $sql = ' WHERE 1=1 ';

        foreach( $where as $key => $value ){
            $type = ( array_key_exists( $key, $this->field_types ) ) ? $this->field_types[ $key ] : '%s';
            if( $type === 'date' ) {
                $date_query = new WP_Date_Query( $value, $this->get_row_suffix() . $key );
                $sql .= $date_query->get_sql();
            }
            else {
                $sql .= ' AND ' . $this->get_row_suffix() . $key . " = " . $wpdb->prepare( $type, $value ) . " ";
            }
        }
        return $sql;
    }



    /**
     * Returns the post view-quantity.
     *
     * @param array $args
     * @return array $results
     */
    public function get_results( $args = array() ) {
        global $wpdb;

        $default_args = array(
            'fields'    => array('*'),
            'limit'     => '',
            'group_by'  => '',
            'order_by'  => '',
            'order'     => '',
            'where'     => array()
        );
        $args = wp_parse_args( $args, $default_args );

        $where  = $this->build_where( $args[ 'where' ] );
        $from   = $this->build_from( );
        $select = $this->build_select( $args[ 'fields' ] );
        $group  = $this->build_group_by( $args[ 'group_by' ] );
        $order  = $this->build_order_by( $args[ 'order_by' ], $args[ 'order' ] );
        $limit  = $this->build_limit( $args[ 'limit' ] );

        $query = '';
        $query .= $select;
        $query .= $from;
        $query .= $where;
        $query .= $group;
        $query .= $order;
        $query .= $limit;

        $this->last_query = $query;

        $results = $wpdb->get_results( $query );

        $logger = laterpay_get_logger();
        $logger->info(
            __METHOD__,
            array(
                'args'      => $args,
                'query'     => $query,
                'results'   => $results
            )
        );

        return $results;
    }

    /**
     * @return string $query
     */
    public function get_last_query(){
        return $this->last_query;
    }

    /**
     * Returns the Sparkline for the given $post_id, for x days.
     *
     * @param int $post_id
     * @param int $days
     *
     * @return array $sparkline
     */
    public function get_sparkline( $post_id, $days ) {
        $sparkline = array();

        for ($i = 1; $i <= (int) $days; $i ++) {

            $args = array(
                'fields'    => array( 'COUNT(*) AS quantity' ),
                'where' => array(
                    'date' => array(
                        array(
                            'after'     => LaterPay_Helper_Date::get_date_query_after_start_of_day( $i ),
                            'before'    => LaterPay_Helper_Date::get_date_query_before_end_of_day( $i ),
                        )
                    ),
                    'post_id' => (int) $post_id
                )
            );

            $day_post_views = $this->get_results( $args );

            if ( empty( $day_post_views ) ) {
                $sparkline[] = 0;
            } else {
                $sparkline[] = $day_post_views[0]->quantity;
            }

        }
        // reverse the sorting of $sparkline, to start with today-$days
        $sparkline = array_reverse( $sparkline );
        return $sparkline;
    }


}
