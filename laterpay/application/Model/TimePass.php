<?php

/**
 * LaterPay time pass model.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Model_TimePass
{

    /**
     * Name of PostViews table.
     *
     * @var string
     *
     * @access public
     */
    public $time_passes_table;

    /**
     * Constructor for class LaterPay_Model_TimePass, load table name.
     */
    function __construct() {
        global $wpdb;

        $this->passes_table = $wpdb->prefix . 'laterpay_passes';
    }

    /**
     * Get time pass data.
     *
     * @param int $time_pass_id time pass id
     *
     * @return array $time_pass array of time pass data
     */
    public function get_pass_data( $time_pass_id ) {
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
        $time_pass = $wpdb->get_row( $wpdb->prepare( $sql, (int) $time_pass_id ) );

        return $time_pass;
    }

    /**
     * Update or create new time pass.
     *
     * @param array $data payment data
     *
     * @return array $data array of saved/updated time pass data
     */
    public function update_time_pass( $data ) {
        global $wpdb;

        // leave only the required keys
        $data = array_intersect_key( $data, LaterPay_Helper_TimePass::get_default_options() );

        // fill values that weren't set from defaults
        $data = array_merge( LaterPay_Helper_TimePass::get_default_options(), $data );

        // pass_id is a primary key, set by autoincrement
        $time_pass_id = $data['pass_id'];
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

        if ( empty( $time_pass_id ) ) {
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
                    array( 'pass_id' => $time_pass_id ),
                    $format,
                    array( '%d' ) // pass_id
            );
            $data['pass_id'] = $time_pass_id;
        }

        return $data;
    }

    /**
     * Get all time passes.
     *
     * @return array $time_passes list of time passes
     */
    public function get_all_time_passes() {
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

        $time_passes = $wpdb->get_results( $sql );

        return $time_passes;
    }

    /**
     * Get all time passes that apply to a given post by its category ids.
     *
     * @param null $term_ids array of category ids
     * @param bool $exclude  categories to be excluded from the list
     *
     * @return array $time_passes list of time passes
     */
    public function get_time_passes_by_category_ids( $term_ids = null, $exclude = null ) {
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

        $time_passes = $wpdb->get_results( $sql );

        return $time_passes;
    }

    /**
     * Delete time pass by id.
     *
     * @param integer $time_pass_id time pass id
     *
     * @return int|false the number of rows updated, or false on error
     */
    public function delete_time_pass_by_id( $time_pass_id ) {
        global $wpdb;

        $where = array(
            'pass_id' => (int) $time_pass_id,
        );

        $success = $wpdb->delete( $this->passes_table, $where, '%d' );

        return $success;
    }

    /**
     * Get count of existing time passes.
     *
     * @return int number of defined time passes
     */
    public function get_time_passes_count() {
        global $wpdb;

        $sql = "
            SELECT
                count(*) AS c_passes
            FROM
                {$this->passes_table}
            ;
        ";

        $list = $wpdb->get_results( $sql );

        return $list[0]->c_passes;
    }
}
