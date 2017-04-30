<?php
/**
 * some utility functions for shortcodes.
 */

/**
 * return the advanced options for the given key.
 * The key is given on advanced search setting dashboard page.
 */ 
function searchstrap_get_advances_options($key) {

    $options = '';

    if(empty($key)) {

        // return default options.
        $options = <<<EOT
itemsPerPage: 16,
// we don't need search button here.
fq: 'site: wiki AND keywords: Acronyms',
//fq: 'keywords: "User Profile"',
//searchButton: 'sizing-addon',
facet: {
    facetField: ['authors']
},
resultTemplate: buildAcronymsList,
autoReload: true,
EOT;
    } else {

        // TODO: get the options for the key.
        // we whill get the options from database.
        $options = get_option('searchstrap_options');
    }

    return $options;
}

