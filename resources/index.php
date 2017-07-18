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

    // the moment.js 
    wp_register_script('wp-searchstrap-moment',
        plugins_url("{$resources_folder}/moment.js"),
        array(),
        '2.8.1', true);
    // searchstrap JavaScript library
    wp_register_script('wp-searchstrap',
        plugins_url("{$resources_folder}/searchstrap.js"),
        // using this for development testing.
        //'https://rawgit.com/leocornus/searchstrap.js/master/src/searchstrap.js',
        array(),
        '0.1.6', true);
    // searchstrap stylesheet 
    wp_register_style('wp-searchstrap-css',
        plugins_url("{$resources_folder}/searchstrap.css"),
        array(),
        '0.1.1', 'all');

    // some default templates for searchstrap
    wp_register_script('wp-searchstrap-default',
        plugins_url("{$resources_folder}/templates/defaultTemplates.js"),
        array(),
        '0.1.0', true);
}
