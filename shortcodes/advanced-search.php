<?php

/**
 * PHP function for advanced search.
 */
function searchstrap_advanced_search_func( $atts, $content=null ){

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
        searchUrl:
'/wp-admin/admin-ajax.php?callback=?&action=advanced_search',
        itemsPerPage: 16,
        // we don't need search button here.
        fq: 'site: wiki AND keywords: Acronyms',
        //fq: 'keywords: "User Profile"',
        //searchButton: 'sizing-addon',
        facet: {
            facetField: ['authors']
        },
        resultSelector: '#result-list',
        resultTemplate: buildAcronymsList,
        autoReload: false
    });
});

</script>
EOT;

    return $ret;
}

// advanced-search will be the shortcode tag.
add_shortcode('advanced-search', 'searchstrap_advanced_search_func');
