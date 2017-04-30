<?php
class SearchstrapDb {

    // table name for advanced search options.
    var $tn_advanced_options;

    public function __construct() {

        if ( ! defined('ABSPATH') ) {
            die('You are not allowed to call this page directly.');
        }

        global $wpdb;

        $this->tn_advanced_options = $wpdb->prefix . 'wpss_advanced_options';
    }

    /**
     * create tables for wp-searchstrap plugin.
     */
    function create_tables($force=false) {
    
        global $wpdb;
    
        // create table for advanced search settions.
        $sql = "CREATE TABLE $this->tn_advanced_options (
              wpss_id mediumint(9) NOT NULL AUTO_INCREMENT,
              wpss_key varchar(128) NOT NULL DEFAULT '',
              wpss_option varchar(4096) NOT NULL DEFAULT '',
              PRIMARY KEY (wpss_id),
              UNIQUE KEY wpss_key (wpss_key)
            );";

        $wpdb->query($sql);
    }

    /**
     * get all advances search settings / options.
     */
    function get_all_advances_options() {
    
        global $wpdb;
    
        $query = "SELECT * FROM $this->tn_advanced_options";
        $options = $wpdb->get_results($query, ARRAY_A);
    
        return $options;
    }
    
    /**
     * get advanced search option for the given key.
     */
    function get_advanced_option($key) {
    
        global $wpdb;
        $query = "SELECT * FROM $this->tn_advanced_options 
            WHERE wpss_key = %s";
        $query = $wpdb->prepare($query, $key);
        $option = $wpdb->getRow($query, ARRAY_A);

        return $option;
    }

    /**
     * add a new option or replace the existing one.
     */
    function replcae_advanced_option($key, $option, $id=0) {

        global $wpdb;

        $success = $wpdb->replace(
            $this->tn_advanced_options,
            array(
                'wpss_id' => $id,
                'wpss_key' => $key,
                'wpss_option' => $option
            ),
            array('%d', '%s', '%s')
        );

        if($success) {
            return $wpdb->insert_id;
        } else {
            return -1;
        }
    }
}
