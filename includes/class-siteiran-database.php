<?php
class SiteIran_Database {
    private $wpdb;
    private $bulk_orders_table;
    private $status_table;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->bulk_orders_table = $wpdb->prefix . 'bulk_orders';
        $this->status_table = $wpdb->prefix . 'bulk_order_statuses';
    }

    public function activate() {
        error_log("Running SiteIran_Database::activate");

        $charset_collate = $this->wpdb->get_charset_collate();

        // جدول سفارشات عمده
        $sql = "CREATE TABLE {$this->bulk_orders_table} (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            order_data TEXT NOT NULL,
            details TEXT,
            status VARCHAR(50) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $result = dbDelta($sql);
        if (empty($result)) {
            error_log("Failed to create table {$this->bulk_orders_table}. Last DB error: " . $this->wpdb->last_error);
        } else {
            error_log("Successfully created table {$this->bulk_orders_table}");
        }

        // جدول وضعیت‌ها
        $sql = "CREATE TABLE {$this->status_table} (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            status_name VARCHAR(100) NOT NULL UNIQUE
        ) $charset_collate;";
        $result = dbDelta($sql);
        if (empty($result)) {
            error_log("Failed to create table {$this->status_table}. Last DB error: " . $this->wpdb->last_error);
        } else {
            error_log("Successfully created table {$this->status_table}");
        }

        $this->insert_default_statuses();
    }

    private function insert_default_statuses() {
        $existing_statuses = $this->wpdb->get_col("SELECT status_name FROM {$this->status_table}");
        $default_statuses = ['pending', 'processing', 'completed'];

        foreach ($default_statuses as $status) {
            if (!in_array($status, $existing_statuses)) {
                $this->wpdb->insert($this->status_table, ['status_name' => $status]);
                if ($this->wpdb->last_error) {
                    error_log("Failed to insert status $status: " . $this->wpdb->last_error);
                } else {
                    error_log("Inserted default status: " . $status);
                }
            }
        }
    }

    public function get_bulk_orders_table() {
        return $this->bulk_orders_table;
    }

    public function get_status_table() {
        return $this->status_table;
    }

    public function get_wpdb() {
        return $this->wpdb;
    }
}