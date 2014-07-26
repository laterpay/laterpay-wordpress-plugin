<?php

class LaterPayModelCategory {

    /**
     * Name of terms table
     *
     * @var string
     *
     * @access public
     */
    public $table;

    /**
     * Name of prices table
     *
     * @var string
     *
     * @access public
     */
    public $table_prices;

    /**
     * Constructor for class LaterPayModelCurrency, load table names
     */
    function __construct() {
        global $wpdb;

        $this->table = $wpdb->prefix . 'terms';
        $this->table_prices = $wpdb->prefix . 'laterpay_terms_price';
    }

    /**
     * Get categories with defined category default prices
     *
     * @access public
     *
     * @return array categories
     */
    public function getCategoriesPrices() {
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

        $categories = $wpdb->get_results($sql);

        return $categories;
    }

    /**
     * Get categories with defined category default prices by list of category IDs
     *
     * @access public
     *
     * @return array category_price_data
     */
    public function getCategoryPriceDataByCategoryIds( $ids ) {
        global $wpdb;

        $placeholders = array_fill(0, count($ids), '%d');
        $format = implode(', ', $placeholders);
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
                tm.term_id IN ({$format})
                AND tp.term_id IS NOT NULL
            ORDER BY
                name
            ;
        ";
        $category_price_data = $wpdb->get_results($wpdb->prepare($sql, $ids), ARRAY_A);

        return $category_price_data;
    }

    /**
     * Get categories with no defined category default prices by search term
     *
     * @param string $term         term string to find categories
     * @param int    $limit        limit categories
     * @param int    $excluding_id excluding id
     *
     * @access public
     *
     * @return array categories
     */
    public function get_categories_without_price_by_term( $term, $limit, $excluding_id = 0 ) {
        global $wpdb;

        if ( $excluding_id == '' ) {
            $excluding_id = 0;
        }

        $term = esc_sql($term);
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
                    AND name LIKE '$term%'
                ) OR (
                    tp.term_id = $excluding_id
                    AND name LIKE '$term%'
                )
            ORDER BY
                name
            LIMIT
                $limit
            ;
        ";
        $categories = $wpdb->get_results($sql);

        return (array) $categories;
    }

    /**
     * Get categories by search term
     *
     * @param string $term  term string to find categories
     * @param int    $limit limit categories
     *
     * @access public
     *
     * @return array categories
     */
    public function getCategoriesByTerm( $term, $limit ) {
        global $wpdb;

        $term = esc_sql($term);
        $sql = "
            SELECT
                tm.term_id AS id,
                tm.name AS text
            FROM
                {$this->table} AS tm
            WHERE
                tm.name LIKE '$term%'
            ORDER BY
                name
            LIMIT
                $limit
            ;
        ";
        $categories = $wpdb->get_results($sql);

        return (array) $categories;
    }

    /**
     * Set category default price
     *
     * @param integer $id_category id category
     * @param float   $price       price for category
     * @param integer $id          id price for category
     *
     * @access public
     */
    public function setCategoryPrice( $id_category, $price, $id = 0 ) {
        global $wpdb;

        if ( !empty($id) ) {
            $wpdb->update(
                $this->table_prices,
                array(
                    'term_id'   => $id_category,
                    'price'     => $price
                ),
                array( 'ID' => $id ),
                array(
                    '%d',
                    '%f'
                ),
                array( '%d' )
            );
        } else {
            $wpdb->insert(
                $this->table_prices,
                array(
                    'term_id'   => $id_category,
                    'price'     => $price
                ),
                array(
                    '%d',
                    '%f'
                )
            );
        }
    }

    /**
     * Get price id by category id
     *
     * @param integer $id id category
     *
     * @access public
     *
     * @return integer id price
     */
    public function getPriceIdsByCategoryId( $id ) {
        global $wpdb;

        $sql = "
            SELECT
                id
            FROM
                {$this->table_prices}
            WHERE
                term_id = '$id'
            LIMIT
                1
            ;
        ";
        $price = $wpdb->get_row($sql);

        if ( empty($price) ) {
            return null;
        }

        return $price->id;
    }

    /**
     * Get category id by category name
     *
     * @param string @name name category
     *
     * @access public
     *
     * @return integer category id
     */
    public function getCategoryIdByName( $name ) {
        global $wpdb;

        $name = esc_sql($name);
        $sql = "
            SELECT
                term_id AS id
            FROM
                {$this->table}
            WHERE
                name = '$name'
            LIMIT
                1
            ;
        ";
        $category = $wpdb->get_row($sql);

        if ( empty($category) ) {
            return null;
        }

        return $category->id;
    }

    /**
     * Check if category exists: get category id by category name
     *
     * @param string $name name category
     *
     * @access public
     *
     * @return integer category id
     */
    public function checkAvailableCategoryByName( $name ) {
        global $wpdb;

        $name = esc_sql($name);
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
                name = '$name'
            LIMIT
                1
            ;
        ";
        $category = $wpdb->get_row($sql);

        if ( empty($category) ) {
            return null;
        }

        return $category->id;
    }

    /**
     * Delete price by category id
     *
     * @param integer $id id category
     *
     * @access public
     */
    public function deletePricesByCategoryId( $id ) {
        global $wpdb;

        $wpdb->delete($this->table_prices, array('term_id' => $id));
    }

    /**
     * Get price by category id
     *
     * @param integer $id category id
     *
     * @access public
     *
     * @return float|null price category
     */
    public function getPriceByCategoryId( $id ) {
        global $wpdb;

        $sql = "
            SELECT
                price
            FROM
                {$this->table_prices}
            WHERE
                term_id = '$id'
            LIMIT
                1
            ;
        ";
        $price = $wpdb->get_row($sql);

        if ( empty($price) ) {
            return null;
        }

        return $price->price;
    }

}
