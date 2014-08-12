<?php

class LaterPay_Model_CategoryPrice
{

    /**
     * Name of terms table.
     *
     * @var string
     *
     * @access public
     */
    public $table;

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

        $this->table = $wpdb->prefix . 'terms';
        $this->table_prices = $wpdb->prefix . 'laterpay_terms_price';
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
                tm.name AS category_name,
                tm.term_id AS category_id,
                tp.price AS category_price
            FROM
                {$this->table} AS tm
                LEFT JOIN
                    {$this->table_prices} AS tp
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
	 * @param   array $ids
     *
	 * @return  array category_price_data
	 */
    public function get_category_price_data_by_category_ids( $ids ) {
        global $wpdb;

        $placeholders   = array_fill( 0, count( $ids ), '%d' );
        $format         = implode( ', ', $placeholders );
        $sql = "
            SELECT
                tm.name AS category_name,
                tm.term_id AS category_id,
                tp.price AS category_price
            FROM
                {$this->table} AS tm
                LEFT JOIN
                    {$this->table_prices} AS tp
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
     * @param string $term         term string to find categories
     * @param int    $limit        limit categories
     * @param int    $excluding_id excluding id
     *
     * @return array categories
     */
    public function get_categories_without_price_by_term( $term, $limit, $excluding_id = 0 ) {
        global $wpdb;

        if ( $excluding_id == '' ) {
            $excluding_id = 0;
        }

        $term = like_escape( esc_sql( $term ) ) . '%';
        $sql = "
            SELECT
                tp.term_id AS id,
                tm.name AS text
            FROM
                {$this->table} AS tm
                LEFT JOIN
                    {$this->table_prices} AS tp
                ON
                    tp.term_id = tm.term_id
            WHERE
                (
                    tp.term_id IS NULL
                    AND name LIKE %s
                ) OR (
                    tp.term_id = %d
                    AND name LIKE %s
                )
            ORDER BY
                name
            LIMIT
                %d
            ;
        ";
        $categories = $wpdb->get_results( $wpdb->prepare( $sql, $term, $excluding_id, $term, $limit ) );

        return $categories;
    }

    /**
     * Get categories by search term.
     *
     * @param string $term  term string to find categories
     * @param int    $limit limit categories
     *
     * @return array categories
     */
    public function get_categories_by_term( $term, $limit ) {
        global $wpdb;

        $term = like_escape( esc_sql( $term ) ) . '%';
        $sql = "
            SELECT
                tm.term_id AS id,
                tm.name AS text
            FROM
                {$this->table} AS tm
            WHERE
                tm.name LIKE %s
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
     * @param integer $id_category id category
     * @param float   $price       price for category
     * @param integer $id          id price for category
     *
     * @return  int|false Number of rows affected/selected or false on error
     */
    public function set_category_price( $id_category, $price, $id = 0 ) {
        global $wpdb;

        if ( ! empty( $id ) ) {
            return $wpdb->update(
                $this->table_prices,
                array(
                    'term_id'   => $id_category,
                    'price'     => $price,
                ),
                array( 'ID' => $id ),
                array(
                    '%d',
                    '%f',
                ),
                array( '%d' )
            );
        } else {
	        return $wpdb->insert(
                $this->table_prices,
                array(
                    'term_id'   => $id_category,
                    'price'     => $price,
                ),
                array(
                    '%d',
                    '%f',
                )
            );
        }
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
                {$this->table_prices}
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
                {$this->table_prices}
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
     * Get category id by category name.
     *
     * @param string @name name category
     *
     * @return integer category id
     */
    public function get_category_id_by_name( $name ) {
        global $wpdb;

        $sql = "
            SELECT
                term_id AS id
            FROM
                {$this->table}
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
     * Check if category exists by getting the category id by category name.
     *
     * @param string $name name category
     *
     * @return integer category id
     */
    public function check_existence_of_category_by_name( $name ) {
        global $wpdb;

        $sql = "
            SELECT
                tm.term_id AS id
            FROM
                {$this->table} AS tm
                RIGHT JOIN
                    {$this->table_prices} AS tp
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
     * @param   integer $id category id
     *
     * @return  int|false The number of rows updated, or false on error.
     */
    public function delete_prices_by_category_id( $id ) {
        global $wpdb;

	    $where = array(
		    'term_id' => (int) $id
	    );

	    return $wpdb->delete( $this->table_prices, $where, '%d' );
    }

}
