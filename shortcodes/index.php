<?php
// load individual shortcode files.
require_once(SEARCHSTRAP_PLUGIN_PATH . 
             '/shortcodes/advanced-search.php');

/**
 * enqueue all necessary resources.
 */
function load_searchstrap_resources() {

    // enqueue the searchstrap.js
    wp_enqueue_script('wp-searchstrap');
    wp_enqueue_style('wp-searchstrap-css');
    wp_enqueue_script('wp-searchstrap-default');
}

add_action( 'wp_enqueue_scripts', 'load_searchstrap_resources' );

