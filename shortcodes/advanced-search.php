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
        //fq: 'site: wiki AND keywords: Acronyms',
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

/**
 * build the Acronyms list, which will have 6 columns
 */
var buildAcronymsList = function(docs, currentQuery, total,
                            currentPage, totalPages, pagination) {

    var resultSummary = '';
    if(total > 0) {
        var end = currentQuery.start +
                  currentQuery.perPage - 1;
        end = end > total ? total : end;
        resultSummary =
            'Page <strong>' + currentPage + '</strong>' +
            ' Showing [<strong>' + currentQuery.start +
            '</strong> - <strong>' + end +
            '</strong>] of <strong>' +
            total + '</strong> total results';
    } else {
        // no result found
        resultSummary =
            '<strong>No results containing ' +
            'all your search terms were found.</strong>';
    }
    jQuery('#search-info').html(resultSummary);

    // build a 6 columns to show
    var result = jQuery("#result-list");
    result.html("");

    //result.append('<div>' + pagination + '</div>');
    var colQueue =[];
    for(i = 0; i < docs.length; i++) {
        var acronym = docs[i];
        var panel = acronymPanelStripper(acronym);
        colQueue.push(panel);
        // i count from 0
        // 6 acronyms for a row
        var ready2Row = (i + 1) % 4;
        if(ready2Row == 0) {
            result.append('<div class="row">' +
                colQueue.join("") + '</div>');
            // reset the queue.
            colQueue = [];
        }
    }

    // check if we missed anything...
    if(colQueue.length > 0) {

        // append to the last row.
        result.append('<div class="row">' +
            colQueue.join(" ") +
            '</div>');
    }

    // add the pagination at the bottom too.
    result.append('<div>' + pagination + '</div>');

    return result;
};

/**
 * builder function to strip out all wiki syntax.
 */
var acronymPanelStripper = function(acronym) {

    // try to remove some wiki markups.
    var desc = acronym['description'];
    // replace wiki syntax.
    var text = desc
       .replace(/.*may refer to:/g, '')
       .replace(/\[http.*/g, '')
       .replace(/\[\[Category:.*/g, '')
       .replace(/[\]\[\']/g, '')
       .replace(/\*/, '<li class="list-group-item">')
       .replace(/\*/g, '</li><li class="list-group-item">');

    var panel = '<div class="col-sm-3">' +
        '<h2 class="text-center">' +
        '<a href="' + acronym['url'] + '">' +
        acronym['title'] + '</a></h2>' +
        '<p><ul class="list-group">' + text + '</li></ul></p>' +
        '</div>';
    return panel;
};
</script>
EOT;

    return $ret;
}

// advanced-search will be the shortcode tag.
add_shortcode('advanced-search', 'searchstrap_advanced_search_func');
