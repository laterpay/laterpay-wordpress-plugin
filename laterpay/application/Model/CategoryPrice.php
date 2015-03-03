<?php

/**
 * LaterPay category price model.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Model_CategoryPrice
{

    /**
     * Name of terms table.
     *
     * @var string
     *
     * @access public
     */
    public $term_table;

    /**
     * Name of prices table.
     *
     * @var string
     *
     * @access public
     */
    public $table_prices;

    /**
     * Constructor for class LaterPay_Currency_Model, load table names.
     */
    function __construct() {
        global $wpdb;
        $this->term_table = $wpdb->terms;
        $this->term_table_prices = $wpdb->prefix . 'laterpay_terms_price';
    }

    /**
     * Get all categories with a defined category default price.
     *
     * @return array categories
     */
    public function get_categories_with_defined_price() {
        global $wpdb;
        $sql = "
            SELECT
                tp.id AS id,
                tm.name AS category_name,
                tm.term_id AS category_id,
                tp.price AS category_price,
                tp.revenue_model AS revenue_model
            FROM
                {$this->term_table} AS tm
                LEFT JOIN
                    {$this->term_table_prices} AS tp
                ON
                    tp.term_id = tm.term_id
            WHERE
                tp.term_id IS NOT NULL
            ORDER BY
                name
            ;
        ";

        $categories = $wpdb->get_results( $sql );

        return $categories;
    }

    /**
     * Get categories with defined category default prices by list of category ids.
     *
     * @param array $ids
     *
     * @return array category_price_data
     */
    public function get_category_price_data_by_category_ids( $ids ) {
        global $wpdb;

        $placeholders   = array_fill( 0, count( $ids ), '%d' );
        $format         = implode( ', ', $placeholders );
        $sql = "
            SELECT
                tm.name AS category_name,
                tm.term_id AS category_id,
                tp.price AS category_price,
                tp.revenue_model AS revenue_model
            FROM
                {$this->term_table} AS tm
                LEFT JOIN
                    {$this->term_table_prices} AS tp
                ON
                    tp.term_id = tm.term_id
            WHERE
                tm.term_id IN ( {$format} )
                AND tp.term_id IS NOT NULL
            ORDER BY
                name
            ;
        ";
        $category_price_data = $wpdb->get_results( $wpdb->prepare( $sql, $ids ) );

        return $category_price_data;
    }

    /**
     * Get categories without defined category default prices by search term.
     *
     * @param array $args       query args for get_categories
     *
     * @return array $categories
     */
    public function get_categories_without_price_by_term( $args ) {
        $default_args = array(
            'hide_empty'    => false,
            'number'        => 10,
        );

        $args = wp_parse_args(
            $args,
            $default_args
        );

        add_filter( 'terms_clauses', array( $this, 'filter_terms_clauses_for_categories_without_price' ) );
        $categories = get_categories( $args );
        remove_filter( 'terms_clauses', array( $this, 'filter_terms_clauses_for_categories_without_price' ) );

        return $categories;
    }

    /**
     * Filter for get_categories_without_price_by_term(), to load all categories without a price.
     *
     * @wp-hook terms_clauses
     *
     * @param array $clauses
     *
     * @return array $clauses
     */
    public function filter_terms_clauses_for_categories_without_price( $clauses ) {
        $clauses['join']  .= ' LEFT JOIN ' . $this->term_table_prices . ' AS tp ON tp.term_id = t.term_id ';
        $clauses['where'] .= ' AND tp.term_id IS NULL ';

        return $clauses;
    }

    /**
     * Get categories by search term.
     *
     * @param string $term  term string to find categories
     * @param int    $limit limit categories
     *
     * @deprecated please use get_terms( 'category', array( 'name__like' => '$term', 'number' => $limit, 'fields' => 'id=>name' ) );
     *
     * @return array categories
     */
    public function get_categories_by_term( $term, $limit ) {
        global $wpdb, $wp_version;

        if ( version_compare( $wp_version, '4.0', '>=' ) ) {
            $term = $wpdb->esc_like( $term );
        } else {
            $term = like_escape( $term );
        }

        $term = esc_sql( $term ) . '%';
        $sql = "
            SELECT
                tm.term_id AS id,
                tm.name AS text
            FROM
                {$this->term_table} AS tm
            INNER JOIN
                {$wpdb->term_taxonomy} as tt
            ON
                tt.term_id = tm.term_id
            WHERE
                tm.name LIKE %s
            AND
                tt.taxonomy = 'category'
            ORDER BY
                name
            LIMIT
                %d
            ;
        ";
        $categories = $wpdb->get_results( $wpdb->prepare( $sql, $term, $limit ) );

        return $categories;
    }

    /**
     * Set category default price.
     *
     * @param integer $id_category      id category
     * @param float   $price            price for category
     * @param string  $revenue_model    revenue model of category
     * @param integer $id               id price for category
     *
     * @return int|false number of rows affected / selected or false on error
     */
    public function set_category_price( $id_category, $price, $revenue_model, $id = 0 ) {
        global $wpdb;

        if ( ! empty( $id ) ) {
            $success = $wpdb->update(
                $this->term_table_prices,
                array(
                    'term_id'       => $id_category,
                    'price'         => $price,
                    'revenue_model' => $revenue_model,
                ),
                array( 'ID' => $id ),
                array(
                    '%d',
                    '%f',
                    '%s',
                ),
                array( '%d' )
            );
        } else {
            $success = $wpdb->insert(
                $this->term_table_prices,
                array(
                    'term_id'       => $id_category,
                    'price'         => $price,
                    'revenue_model' => $revenue_model,
                ),
                array(
                    '%d',
                    '%f',
                    '%s',
                )
            );
        }

        LaterPay_Helper_Cache::purge_cache();
        return $success;
    }

    /**
     * Get price id by category id.
     *
     * @param integer $id id category
     *
     * @return integer id price
     */
    public function get_price_id_by_category_id( $id ) {
        global $wpdb;

        $sql = "
            SELECT
                id
            FROM
                {$this->term_table_prices}
            WHERE
                term_id = %d
            ;
        ";
        $price = $wpdb->get_row( $wpdb->prepare( $sql, $id ) );

        if ( empty( $price ) ) {
            return null;
        }

        return $price->id;
    }

    /**
     * Get price by category id.
     *
     * @param integer $id category id
     *
     * @return float|null price category
     */
    public function get_price_by_category_id( $id ) {
        global $wpdb;

        $sql = "
            SELECT
                price
            FROM
                {$this->term_table_prices}
            WHERE
                term_id = %d
            ;
        ";
        $price = $wpdb->get_row( $wpdb->prepare( $sql, $id ) );

        if ( empty( $price ) ) {
            return null;
        }

        return $price->price;
    }

    /**
     * Get revenue model by category id.
     *
     * @param integer $id  category id
     *
     * @return string|null category renevue model
     */
    public function get_revenue_model_by_category_id( $id ) {
        global $wpdb;

        $sql = "
            SELECT
                revenue_model
            FROM
                {$this->term_table_prices}
            WHERE
                term_id = %d
            ;
        ";
        $revenue_model = $wpdb->get_row( $wpdb->prepare( $sql, $id ) );

        if ( empty( $revenue_model ) ) {
            return null;
        }

        return $revenue_model->revenue_model;
    }

    /**
     * Check, if category exists by getting the category id by category name.
     *
     * @param string $name name category
     *
     * @return integer category_id
     */
    public function check_existence_of_category_by_name( $name ) {
        global $wpdb;

        $sql = "
            SELECT
                tm.term_id AS id
            FROM
                {$this->term_table} AS tm
                RIGHT JOIN
                    {$this->term_table_prices} AS tp
                ON
                    tm.term_id = tp.term_id
            WHERE
                name = %s
            ;
        ";
        $category = $wpdb->get_row( $wpdb->prepare( $sql, $name ) );

        if ( empty( $category ) ) {
            return null;
        }

        return $category->id;
    }

    /**
     * Delete price by category id.
     *
     * @param integer $id category id
     *
     * @return int|false the number of rows updated, or false on error
     */
    public function delete_prices_by_category_id( $id ) {
        global $wpdb;

        $where = array(
            'term_id' => (int) $id,
        );

        $success = $wpdb->delete( $this->term_table_prices, $where, '%d' );
        LaterPay_Helper_Cache::purge_cache();
        return $success;
    }

    /**
     * Delete all category prices from table.
     *
     * @return int|false the number of rows updated, or false on error
     */
    public function delete_all_category_prices() {
        global $wpdb;

        $success = $wpdb->query( "TRUNCATE TABLE " . $this->term_table_prices );

        return $success;
    }
}
