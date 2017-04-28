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

    // web components folder.
    $resources_folder = 'wp-searchstrap/resources';

    // search strap.
    wp_register_script('wp-searchstrap',
        plugins_url("{$resources_folder}/searchstrap.js"),
        //'https://rawgit.com/leocornus/leocornus-nodejs-sandbox/master/src/search/searchStrap.js',
        array(),
        '0.1.0', true);
}
