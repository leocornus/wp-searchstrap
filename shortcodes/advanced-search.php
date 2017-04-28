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
<!-- Search bar -->
  <div class="row" id="search-bar">
    <div class="col-md-2 col-sm-2">
    </div>

    <div class="col-md-8 col-sm-8">
      <div class="input-group input-group-lg"
           role="group" aria-label="...">
        <input type="text" class="form-control"
               placeholder="Find Acronyms"
               id="search-input"
               aria-describedby="sizing-addon"/>
        <span class="input-group-addon">
          <i class="fa fa-search text-primary"></i>
        </span>
      </div>
      <div class="text-muted text-center h4" id="search-info">
        <h2>Loading...</h2>
      </div>
    </div>

    <div class="col-md-2 col-sm-2">
    </div>
  </div>

<!-- Example row of columns -->
<div class="" id="result-list">
Loading
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {

    $('#search-input').searchStrap({
        searchUrl: '{$search_url}',
        resultSelector: '#result-list',
        {$options}
    });
});

</script>
EOT;

    return $ret;
}

// advanced-search will be the shortcode tag.
add_shortcode('advanced-search', 'searchstrap_advanced_search_func');
