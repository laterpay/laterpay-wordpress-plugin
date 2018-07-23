<?php

/**
 * LaterPay subscription model to work with custom tables in older versions.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Compatibility_Subscription
{

    /**
     * Name of PostViews table.
     *
     * @var string
     *
     * @access public
     */
    public $table;

    private static $_instance;

    /**
     * Constructor for class LaterPay_Compatibility_Subscription, load table name.
     */
    private function __construct() {
        global $wpdb;

        $this->table = $wpdb->prefix . 'laterpay_subscriptions';
    }

    /**
     * Returns a instance of itself.
     * This method is needed to make class singleton.
     *
     * @return LaterPay_Compatibility_Subscription
     */
    public static function get_instance() {
        if ( ! self::$_instance ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Get subscription data.
     *
     * @param int  $id subscription id
     * @param bool $ignore_deleted ignore deleted subscriptions
     *
     * @return array $subscription array of subscriptions data
     */
    public function get_subscription( $id, $ignore_deleted = false ) {
        global $wpdb;

        $sql = "
            SELECT
                *
            FROM
                {$this->table}
            WHERE
                id = %d
        ";

        if ( $ignore_deleted ) {
            $sql .= '
                AND is_deleted = 0
            ';
        }

        $sql .= ';';

        return $wpdb->get_row( $wpdb->prepare( $sql, (int) $id ), ARRAY_A );
    }

    /**
     * Update or create new subscription.
     *
     * @param array $data payment data
     *
     * @return array $data array of saved/updated subscription data
     */
    public function update_subscription( $data ) {
        global $wpdb;

        // leave only the required keys
        $data = array_intersect_key( $data, LaterPay_Helper_Subscription::get_default_options() );

        // fill values that weren't set from defaults
        $data = array_merge( LaterPay_Helper_Subscription::get_default_options(), $data );

        unset( $data['lp_id'] ); // unset key ( used in WP schema ) before inserting to custom table.

        // pass_id is a primary key, set by autoincrement
        $id = $data['id'];
        unset( $data['id'] );

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

        if ( empty( $id ) ) {
            $wpdb->insert(
                $this->table,
                $data,
                $format
            );
            $data['id'] = $wpdb->insert_id;
        } else {
            $wpdb->update(
                $this->table,
                $data,
                array( 'id' => $id ),
                $format,
                array( '%d' ) // pass_id
            );
            $data['id'] = $id;
        }

        // purge cache
        LaterPay_Helper_Cache::purge_cache();

        return $data;
    }

    /**
     * Get all active subscriptions.
     *
     * @return array of subscriptions
     */
    public function get_active_subscriptions() {
        return $this->get_all_subscriptions( true );
    }

    /**
     * Get all subscriptions.
     *
     * @param bool $ignore_deleted ignore deleted subscriptions
     *
     * @return array list of subscriptions
     */
    public function get_all_subscriptions( $ignore_deleted = false ) {
        global $wpdb;

        $sql = "
            SELECT
                *
            FROM
                {$this->table}";

        if ( $ignore_deleted ) {
            $sql .= '
            WHERE
                is_deleted = 0
            ';
        }

        $sql .= '
            ORDER
                BY title
            ;
        ';

        return $wpdb->get_results( $sql, ARRAY_A );
    }

    /**
     * Get all subscriptions that apply to a given post by its category ids.
     *
     * @param null $term_ids array of category ids
     * @param bool $exclude  categories to be excluded from the list
     * @param bool $ignore_deleted ignore deleted subscriptions
     * @param bool $include_all include all subscriptions
     *
     * @return array $subscriptions list of subscriptions
     */
    public function get_subscriptions_by_category_ids( $term_ids = null, $exclude = false, $ignore_deleted = false, $include_all = false ) {
        global $wpdb;

        $sql = "
            SELECT
                *
            FROM
                {$this->table} AS subs
            WHERE
        ";

        if ( $ignore_deleted ) {
            $sql .= '
                is_deleted = 0 AND (
            ';
        }

        if ( $term_ids ) {
            $prepared_ids = implode( ',', $term_ids );

            $access_to_except = " subs.access_category NOT IN ( {$prepared_ids} ) AND subs.access_to = 1";

            $access_to_include = " subs.access_category IN ( {$prepared_ids} ) AND subs.access_to <> 1";

            if ( $include_all ) {
                $sql .= $access_to_except . " OR " . $access_to_include;
            } else {

                if ( $exclude ) {
                    $sql .= $access_to_except;
                } else {
                    $sql .= $access_to_include;
                }
            }

            $sql .= ' OR ';
        }

        $sql .= '
                subs.access_to = 0
            ';

        if ( $ignore_deleted ) {
            $sql .= ' ) ';
        }

        $sql .= '
            ORDER BY
                subs.access_to DESC,
                subs.price ASC
            ;
        ';

        $subscriptions = $wpdb->get_results( $sql, ARRAY_A );

        return $subscriptions;
    }

    /**
     * Delete subscription by id.
     *
     * @param integer $id subscription id
     *
     * @return int|false the number of rows updated, or false on error
     */
    public function delete_subscription_by_id( $id ) {
        global $wpdb;

        $where = array(
            'id' => (int) $id,
        );

        $result = $wpdb->update( $this->table, array( 'is_deleted' => 1 ), $where, array( '%d' ), array( '%d' ) );

        // purge cache
        LaterPay_Helper_Cache::purge_cache();

        return $result;
    }

    /**
     * Get count of existing subscriptions.
     *
     * @param boolean $ignore_deleted Should deleted posts be ignored or not
     *
     * @return int number of defined subscriptions
     */
    public function get_subscriptions_count( $ignore_deleted = false ) {
        global $wpdb;

        $sql = "
            SELECT
                count(*) AS subs
            FROM
                {$this->table}";

        $ignore_deleted === false ? $sql .= ';' : $sql .= ' WHERE is_deleted = 0;';

        $list = $wpdb->get_results( $sql );

        return $list[0]->subs;
    }
}
