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
     * Get pass data.
     *
     * @access public
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
     * Save payment to payment history.
     *
     * @param array $data payment data
     */
    public function update_pass( $data ) {
        global $wpdb;

        $defaults = array(
            'pass_id'
        );
        
        $data = array_merge( $defaults, $data );
        
        $wpdb->insert(
                    $this->table,
                    $data
            );                
        $sql = $wpdb->prepare(
            $sql,
            $data['pass_id'],
            $data['status'],
            $data['valid_term'],
            $data['valid_period'],
            $data['access_to'],
            $data['access_category'],
            $data['price'],
            $data['pay_type'],
            $data['title'],
            $data['title_color'],
            $data['description'],
            $data['description_color'],
            $data['background_path'],
            $data['background_color']
        );

        return $wpdb->get_results( $sql );

    }
    /**
     * Get today's history by post id.
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
                BY status
            ;
        ";

        $list = $wpdb->get_results(
            $wpdb->prepare(
                $sql,
                '*'
            )
        );

        return $list;
    }

}
