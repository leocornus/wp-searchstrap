<?php
/*
Plugin Name: WP SearchStrap 
Plugin URI: http://www.github.com/leocornus/wp-searchstrap
Description: WordPress plugin to provide comprehensive search solution for a blog or site
Version: 0.1
Author: Sean Chen 
Author URI: http://github.com/seanchen
License: GPLv2
*/

// figure out the plugin path .This will work for symlink path too.
$searchstrap_file = __FILE__;
define('SEARCHSTRAP_PLUGIN_FILE', $searchstrap_file);
define('SEARCHSTRAP_PLUGIN_PATH', 
       WP_PLUGIN_DIR.'/'.basename(dirname($searchstrap_file)));

// load the 3rd party libs.
require_once(SEARCHSTRAP_PLUGIN_PATH . '/libs/index.php');

// dbDelta function is in this file.
require_once(SEARCHSTRAP_PLUGIN_PATH . '/classes/SearchstrapDb.php');

require_once(SEARCHSTRAP_PLUGIN_PATH . '/resources/index.php');
require_once(SEARCHSTRAP_PLUGIN_PATH . '/admin/index.php');
require_once(SEARCHSTRAP_PLUGIN_PATH . '/shortcodes/index.php');

/**
 * create tablses where install the plugin
 */
function searchstrap_install() {

    $ssdb = new SearchstrapDb();
    $ssdb->create_tables();
}
// the activation hook.
register_activation_hook(SEARCHSTRAP_PLUGIN_PATH . '/' . basename(__FILE__),
                         'searchstrap_install');
