<?php

class LaterPay_Model_BulkOperation
{
    /**
     * Name of bulk operations table.
     *
     * @var string
     */
    public $table_bulk_operations;

    /**
     * Constructor for class LaterPay_Currency_Model, load table names.
     */
    function __construct() {
        global $wpdb;
        $this->table_bulk_operations = $wpdb->prefix . 'laterpay_bulk_operations';
    }

    /**
     * Get all bulk operations.
     *
     * @return array $bulk_operations
     */
    public function get_bulk_operations( ) {
        global $wpdb;
        $sql = "
            SELECT
                *
            FROM
                {$this->table_bulk_operations}
        ";

        $bulk_operations = $wpdb->get_results( $sql );

        return $bulk_operations;
    }

    /**
     * Get bulk operation data by id
     *
     * @param  int    $id   bulk operation id
     *
     * @return string|null  bulk operation data
     */
    public function get_bulk_operation_data_by_id( $id ) {
        global $wpdb;

        $sql = "
            SELECT
                data
            FROM
                {$this->table_bulk_operations}
            WHERE
                id = %d
            ;
        ";
        $result = $wpdb->get_row( $wpdb->prepare( $sql, $id ) );

        if ( empty( $result ) ) {
            return null;
        }

        return $result->data;
    }

    /**
     * Set bulk operation.
     *
     * @param  string    $data    serialized bulk data
     *
     * @return int|false number of rows affected / selected or false on error
     */
    public function save_bulk_operation( $data ) {
        global $wpdb;

        $success = false;

        if ( $data ) {
            $success = $wpdb->insert(
                $this->table_bulk_operations,
                array(
                    'data' => $data,
                ),
                array(
                    '%s',
                )
            );
        }

        return $success;
    }

    /**
     * Delete bulk operation by id.
     *
     * @param integer $id bulk operation id
     *
     * @return int|false the number of rows updated, or false on error
     */
    public function delete_bulk_operation_by_id( $id ) {
        global $wpdb;

        $where = array(
            'id' => (int) $id,
        );

        $success = $wpdb->delete( $this->table_bulk_operations, $where, '%d' );
        LaterPay_Helper_Cache::purge_cache();
        return $success;
    }
}