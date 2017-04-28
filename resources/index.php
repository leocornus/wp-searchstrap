<?php
// if the file is called directly, abort!
if(!defined('WPINC')) {
    die;
}

/**
 * register web resources.
 */
add_action('init', 'register_searchstrap_resources');
function register_searchstrap_resources() {

    // resources folder.
    $resources_folder = 'wp-searchstrap/resources';

    // searchstrap JavaScript library
    wp_register_script('wp-searchstrap',
        plugins_url("{$resources_folder}/searchstrap.js"),
        array(),
        '0.1.0', true);

    // some default templates for searchstrap
    wp_register_script('wp-searchstrap-default',
        plugins_url("{$resources_folder}/templates/defaultTemplates.js"),
        array(),
        '0.1.0', true);
}
