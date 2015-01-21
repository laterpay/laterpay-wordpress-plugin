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
    public $passes_table;

    /**
     * Constructor for class LaterPay_Model_Pass, load table name.
     */
    function __construct() {
        global $wpdb;

        $this->passes_table = $wpdb->prefix . 'laterpay_passes';
    }

    /**
     * Get time pass data.
     *
     * @param int $pass_id time pass id
     *
     * @return array $pass array of pass data
     */
    public function get_pass_data( $pass_id ) {
        global $wpdb;

        $sql = "
            SELECT
                *
            FROM
                {$this->passes_table}
            WHERE
                pass_id = %d
            ;
        ";
        $pass = $wpdb->get_row( $wpdb->prepare( $sql, (int) $pass_id ) );

        return $pass;
    }

    /**
     * Update or create new pass.
     *
     * @param array $data payment data
     *
     * @return array $data array of saved/updated pass data
     */
    public function update_pass( $data ) {
        global $wpdb;

        // leave only the required keys
        $data = array_intersect_key( $data, LaterPay_Helper_Passes::get_default_options() );

        // fill values that weren't set from defaults
        $data = array_merge( LaterPay_Helper_Passes::get_default_options(), $data );

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
            '%s', // description
        );

        if ( empty($pass_id) ) {
            $wpdb->insert(
                $this->passes_table,
                $data,
                $format
            );
            $data['pass_id'] = $wpdb->insert_id;
        } else {
            $wpdb->update(
                    $this->passes_table,
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
     * Get all passes
     *
     * @return array $list list of passes
     */
    public function get_all_passes() {
        global $wpdb;

        $sql = "
            SELECT
                *
            FROM
                {$this->passes_table}
            ORDER
                BY title
            ;
        ";

        $list = $wpdb->get_results( $sql );

        return $list;
    }

    /**
     * Get post passes by category ids
     *
     * @param null $term_ids array of category ids
     * @param bool $exclude  exclude categories from list
     *
     * @return array $list list of passes
     */
    public function get_post_passes( $term_ids = null, $exclude = null ) {
        global $wpdb;

        $sql = "
            SELECT
                *
            FROM
                {$this->passes_table} AS pt
            WHERE
        ";

        if ( $term_ids ) {
            $prepared_ids = implode( ',', $term_ids );
            if ( $exclude ) {
                $sql .= " pt.access_category NOT IN ( {$prepared_ids} ) AND pt.access_to = 1";
            } else {
                $sql .= " pt.access_category IN ( {$prepared_ids} ) AND pt.access_to <> 1";
            }
            $sql .= " OR ";
        }

        $sql .= "
                pt.access_to = 0
            ORDER BY
                pt.access_to DESC,
                pt.price ASC
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

        $success = $wpdb->delete( $this->passes_table, $where, '%d' );

        return $success;
    }
}
