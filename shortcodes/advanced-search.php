<?php

/**
 * PHP function for advanced search.
 *
 * shortcode could be used in following format:
 *
 *  - [advanced-search]
 *  - [advanced-search key='abc']
 */
function searchstrap_advanced_search_func( $atts, $content=null ){

    // TODO: v2.0 will allow user to config the search url.
    // the default search url is a AJAX callback.
    $search_url = 
        '/wp-admin/admin-ajax.php?callback=?&action=advanced_search';

    $normalized_array = shortcode_atts(
        array(
            //Default attributes - Key and values stores in an array
            'key' => '', // default key is empty string 
        ),
        $atts );

    // extract the parameters from user input.
    extract( $normalized_array );

    // get the options for the given key.
    $options = searchstrap_get_advances_options($key);

    $ret = <<<EOT
<div class="row" id="search-bar">
  <div class="col-md-4" style="padding-right:20px; border-right: 2px solid #ccc;">
    <div id="searchstrap"></div>
  </div>

  <div class="col-md-8">
    <div class="text-muted h4" id="search-info"></div>
    <div id="result-list"></div>
  </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {

    $('#searchstrap').searchStrap({
        searchUrl: '{$search_url}',
        resultSelector: '#result-list',
        {$options}
    });
});
</script>
<style>
/**
 * for the input clear button.
 */
::-ms-clear {
  display: none;
}

.form-control-clear {
  z-index: 10;
  pointer-events: auto;
  cursor: pointer;
}
</style>
EOT;

    return $ret;
}

// advanced-search will be the shortcode tag.
add_shortcode('advanced-search', 'searchstrap_advanced_search_func');
