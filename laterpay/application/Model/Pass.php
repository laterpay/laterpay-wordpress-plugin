<?php

class LaterPay_Model_Pass
{

    /**
     * Name of PostViews table.
     *
     * @var string
     *
     * @access public
     */
    public $table;

    /**
     * Constructor for class LaterPay_Post_Views_Model, load table name.
     */
    function __construct() {
        global $wpdb;

        $this->table = $wpdb->prefix . 'laterpay_passes';
    }

    /**
     * FIXME: #196 add comment
     *
     * @param FIXME: #196 add comment
     *
     * @return array views
     */
    public function get_pass_data( $post_id ) {
        global $wpdb;

        $sql = "
            SELECT
                *
            FROM
                {$this->table}
            WHERE
                pass_id = %d
            ;
        ";
        $views = $wpdb->get_results( $wpdb->prepare( $sql, (int) $post_id ) );

        return $views;
    }

    /**
     * FIXME: #196 add comment
     *
     * @param array $data payment data
     *
     * @return FIXME: #196 add comment
     */
    public function update_pass( $data ) {
        global $wpdb;

        // leave only the required keys
        $data = array_intersect_key( $data, LaterPay_Helper_Passes::$defaults );

        // fill values that weren't set from defaults
        $data = array_merge( LaterPay_Helper_Passes::$defaults, $data );

        // pass_id is a primary key, set by autoincrement
        $pass_id = $data['pass_id'];
        unset( $data['pass_id'] );

        // format for insert and update statement
        $format = array(
            '%d', // duration
            '%d', // period
            '%d', // access_to
            '%d', // access_category
            '%f', // price
            '%s', // revenue_model
            '%s', // title
            '%s', // title_color
            '%s', // description
            '%s', // description_color
            '%s', // background_path
            '%s', // background_color
        );

        if ( empty($pass_id) ) {
            $wpdb->insert(
                $this->table,
                $data,
                $format
            );
            $data['pass_id'] = $wpdb->insert_id;
        } else {
            $wpdb->update(
                    $this->table,
                    $data,
                    array( 'pass_id' => $pass_id ),
                    $format,
                    array( '%d' ) // pass_id
            );
            $data['pass_id'] = $pass_id;
        }
        
        return $data;
    }

    /**
     * FIXME: #196 add comment
     *
     * @return array list of passes
     */
    public function get_all_passes() {
        global $wpdb;

        $sql = "
            SELECT
                *
            FROM
                {$this->table}
            ORDER
                BY title
            ;
        ";

        $list = $wpdb->get_results( $sql );

        return $list;
    }

    /**
     * Delete pass by id.
     *
     * @param integer $id pass id
     *
     * @return int|false the number of rows updated, or false on error
     */
    public function delete_pass_by_id( $id ) {
        global $wpdb;

        $where = array(
            'pass_id' => (int) $id,
        );

        $success = $wpdb->delete( $this->table, $where, '%d' );

        return $success;
    }
    
    }
