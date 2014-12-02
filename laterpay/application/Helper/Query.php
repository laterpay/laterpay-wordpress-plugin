<?php

class LaterPay_Helper_Query
{

    /**
     * @var array
     */
    protected $query_args = array();

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
    public function build_from() {
        $sql = ' FROM ' . $this->table;
        if ( $this->table_short !== '' ) {
            $sql .= ' AS ' . $this->table_short;
        }

        return $sql;
    }

    /**
     *
     * @return string $suffix
     */
    public function get_row_suffix() {
        $suffix = '';
        if ( ! empty( $this->short_from ) ) {
            $suffix = $this->short_from . '.';
        } else if ( ! empty( $this->from ) ) {
            $suffix = $this->from . '.';
        }

        return $suffix;
    }

    /**
     * Add a INNER/LEFT/RIGHT JOIN clause to a query.
     *
     * @param array $joins array(
     *                      array(
     *                          'type'  => 'INNER',
     *                          'fields'=> array(),
     *                          'table' => '',
     *                          'on'    => array(
     *                              'field'     => '',
     *                              'join_field'=> '',
     *                              'compare'   => '='
     *                          )
     *                      )
     *                      ...
     *                    )
     *
     * @return string $sql
     */
    public function build_join( $joins ){
        $sql = '';

        if ( empty( $joins )  ) {
            return $sql;
        }

        foreach ( $joins as $index => $join ) {

            if ( ! is_array( $join ) ) {
               continue;
            }

            $table = $join[ 'table' ] . '_' . $index;

            $sql .= ' ' . strtoupper( $join[ 'type' ] ) . ' JOIN ' . $join[ 'table' ] . ' AS ' . $table;
            $sql .= $this->build_join_on( $join, $table );

            $this->query_args[ 'fields' ] = wp_parse_args(
                $this->query_args[ 'fields' ],
                $this->build_join_fields( $join, $table )
            );

        }

        return $sql;
    }

    /**
     * Builds the join "ON"-Statement.
     * @param array $join
     * @param string $table
     * @return string $sql
     */
    protected function build_join_on( $join, $table ) {

        $field_1    = $table . '.' . $join[ 'on' ][ 'field' ];
        $compare    = $join[ 'on' ][ 'compare' ];
        $field_2    = ( $this->table_short !== '' ) ? $this->table_short : $this->table;
        $field_2    .=  '.' . $join[ 'on' ][ 'join_field' ];

        return ' ON ' . $field_1 . ' ' . $compare . ' ' . $field_2;
    }

    /**
     * Builds the join fields with table-prefix.
     *
     * @param array $join
     * @param string $table
     * @return array $fields
     */
    protected function build_join_fields( $join, $table ){
        $fields = array();
        if ( empty( $join[ 'fields' ] ) ) {
            $fields[] = $table . '.*';
        } else {
            foreach ( $join['fields'] as $field ) {
                $fields[] = $table . '.' . $field;
            }
        }
        return $fields;
    }

    /**
     * Add a LIMIT clause to a query.
     *
     * @param int $limit
     * @return string $sql
     */
    public static function build_limit( $limit ) {
        if ( empty( $limit ) ) {
            return '';
        }

        return ' LIMIT ' . absint( $limit ) . ' ';
    }

    /**
     * Add a ORDER BY clause to a query.
     *
     * @param string $order_by
     * @param string $order
     *
     * @return string $sql
     */
    public function build_order_by( $order_by, $order = 'ASC' ) {
        if ( empty( $order_by ) ) {
            return '';
        }
        $sql = ' ORDER BY ' . $this->get_row_suffix() . $order_by;
        if ( ! in_array( $order, array( 'ASC', 'DESC' ) ) ) {
            $order = 'ASC';
        }

        return $sql . ' ' . $order . ' ';
    }

    /**
     * Add a GROUP BY clause to a query.
     *
     * @param string $group
     *
     * @return string $sql
     */
    public function build_group_by( $group ) {
        if ( empty( $group ) ) {
            return '';
        }

        return ' GROUP BY ' . $group;
    }

    /**
     * Add a SELECT clause to a query.
     *
     * @param array $fields
     *
     * @return string $sql
     */
    public function build_select( $fields = array() ) {
        if ( empty( $fields ) ) {
            return ' SELECT * ';
        }

        return ' SELECT ' . implode( ', ', $fields );
    }

    /**
     * Add a WHERE clause to a query.
     *
     * @param array $where
     *
     * @return string $sql
     */
    public function build_where( $where = array() ) {
        global $wpdb;

        $sql = ' WHERE 1=1 ';

        foreach ( $where as $key => $value ) {
            $type = ( array_key_exists( $key, $this->field_types ) ) ? $this->field_types[ $key ] : '%s';
            if ( $type === 'date' ) {
                $date_query = new WP_Date_Query( $value, $this->get_row_suffix() . $key );
                $sql .= $date_query->get_sql();
            } else {
                $sql .= ' AND ' . $this->get_row_suffix() . $key . ' = ' . $wpdb->prepare( $type, $value ) . ' ';
            }
        }
        return $sql;
    }

    /**
     * Get the results of a query.
     *
     * @param array $args
     *
     * @return array $results
     */
    public function get_results( $args = array() ) {
        global $wpdb;

        $query              = $this->create_query( $args );
        $this->last_query   = $query;
        $results            = $wpdb->get_results( $query );

        $logger = laterpay_get_logger();
        $logger->info(
            __METHOD__,
            array(
                'args'      => $this->query_args,
                'query'     => $query,
                'results'   => $results
            )
        );

        return $results;
    }

    /**
     * Get a single row-result of a query.
     *
     * @param array $args
     *
     * @return array $result
     */
    public function get_row( $args = array() ) {
        global $wpdb;

        $query              = $this->create_query( $args );
        $this->last_query   = $query;
        $result             = $wpdb->get_row( $query );

        $logger = laterpay_get_logger();
        $logger->info(
            __METHOD__,
            array(
                'args'      => $this->query_args,
                'query'     => $query,
                'results'   => $result
            )
        );

        return $result;
    }

    /**
     * Creating a query.
     *
     * @param array $args
     * @return string $query
     */
    public function create_query( $args = array() ) {
        $default_args = array(
            'fields'    => array('*'),
            'limit'     => '',
            'group_by'  => '',
            'order_by'  => '',
            'order'     => '',
            'join'      => array(),
            'where'     => array()
        );
        $this->query_args = wp_parse_args( $args, $default_args );

        $join   = $this->build_join( $this->query_args[ 'join' ] );

        $where  = $this->build_where( $this->query_args[ 'where' ] );
        $from   = $this->build_from( );
        $select = $this->build_select( $this->query_args[ 'fields' ] );
        $group  = $this->build_group_by( $this->query_args[ 'group_by' ] );
        $order  = $this->build_order_by( $this->query_args[ 'order_by' ],  $this->query_args[ 'order' ] );
        $limit  = $this->build_limit( $this->query_args[ 'limit' ] );

        $query = '';
        $query .= $select;
        $query .= $from;
        $query .= $join;
        $query .= $where;
        $query .= $group;
        $query .= $order;
        $query .= $limit;
        return $query;
    }

    /**
     * @return string $query
     */
    public function get_last_query() {
        return $this->last_query;
    }

}
