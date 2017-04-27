<?php
/**
 * manage site admin dashboard settings pages.
 */

// register the deactivation script.
register_deactivation_hook($searchstrap_file, 
                           'deactivate_searchstrap');

/**
 * the deactivation script will simplely remove all options for now.
 */
function deactivate_searchstrap() {

    delete_option('searchstrap_keys');
    delete_option('searchstrap_options');
}

// adding the dashboard page to manage
add_action('admin_menu', 'searchstrap_admin_init');

function searchstrap_admin_init() {

    $main_page = 'wp-searchstrap/admin/info.php';
    // the general settings page.
    add_menu_page('SearchStrap', 'SearchStrap',
                  'manage_options',
                  $main_page,
                  '');

    add_submenu_page($main_page,
                     'SearchStrap General Settings',
                     'General',
                     'manage_options',
                     $main_page);

    add_submenu_page($main_page,
                     'SearchStrap Advanced Search Settings',
                     'Advanced Search',
                     'manage_options',
                     '/wp-searchstrap/admin/advancedSearch.php');
}
