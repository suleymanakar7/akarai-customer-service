<?php
if (!defined('ABSPATH')) exit;

class AI_MH_DB {
    private $wpdb;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    public function create_tables() {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $charset_collate = $this->wpdb->get_charset_collate();

        // Leads Table
        $table_leads = $this->wpdb->prefix . 'ai_mh_leads';
        $sql_leads = "CREATE TABLE $table_leads (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            surname varchar(100) NOT NULL,
            gender varchar(20) DEFAULT '' NOT NULL,
            phone varchar(20) NOT NULL,
            is_kvkk_accepted tinyint(1) DEFAULT 0 NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // Conversations Table
        $table_convs = $this->wpdb->prefix . 'ai_mh_conversations';
        $sql_convs = "CREATE TABLE $table_convs (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            lead_id mediumint(9) NOT NULL,
            status varchar(20) DEFAULT 'active' NOT NULL,
            started_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // Messages Table
        $table_msgs = $this->wpdb->prefix . 'ai_mh_messages';
        $sql_msgs = "CREATE TABLE $table_msgs (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            conversation_id mediumint(9) NOT NULL,
            role varchar(20) NOT NULL,
            content text NOT NULL,
            prompt_tokens int DEFAULT 0 NOT NULL,
            completion_tokens int DEFAULT 0 NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        // Knowledge Table (Indexed Site Content)
        $table_knowledge = $this->wpdb->prefix . 'ai_mh_knowledge';
        $sql_knowledge = "CREATE TABLE $table_knowledge (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            post_title varchar(255) NOT NULL,
            content text NOT NULL,
            embedding LONGTEXT DEFAULT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        dbDelta($sql_leads);
        dbDelta($sql_convs);
        dbDelta($sql_msgs);
        dbDelta($sql_knowledge);

        // Ensure columns exist (dbDelta sometimes misses them if schema is complex)
        $this->maybe_add_column($table_leads, 'gender', "varchar(20) DEFAULT '' NOT NULL AFTER surname");
        $this->maybe_add_column($table_leads, 'is_kvkk_accepted', "tinyint(1) DEFAULT 0 NOT NULL AFTER phone");
        $this->maybe_add_column($table_msgs, 'prompt_tokens', "int DEFAULT 0 NOT NULL AFTER content");
        $this->maybe_add_column($table_msgs, 'completion_tokens', "int DEFAULT 0 NOT NULL AFTER prompt_tokens");
        $this->maybe_add_column($table_knowledge, 'embedding', "LONGTEXT DEFAULT NULL AFTER content");
    }

    private function maybe_add_column($table, $column, $definition) {
        $check = $this->wpdb->get_results("SHOW COLUMNS FROM `$table` LIKE '$column'");
        if (empty($check)) {
            $this->wpdb->query("ALTER TABLE `$table` ADD `$column` $definition");
        }
    }
}
