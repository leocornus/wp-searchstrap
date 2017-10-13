<?php
add_action('wp_ajax_solr_advanced_search', 
           'solr_advanced_search_callback');
add_action('wp_ajax_nopriv_solr_advanced_search', 
           'solr_advanced_search_callback');
add_action('wp_ajax_advanced_search', 
           'solr_advanced_search_callback');
add_action('wp_ajax_nopriv_advanced_search', 
           'solr_advanced_search_callback');
/**
 * AJAX callback function for advances search.
 * NOTE: This callback function depends on Solr server backend.
 *
 */
function solr_advanced_search_callback() {

    // search term. 
    $query = $_REQUEST['term'];
    // set the sort param.
    $sort = $_REQUEST['sort'];

    // the array for filter query.
    $filter_query = array();

    // preparing filter query.
    $fq = '';
    if(isset($_REQUEST['fq'])) {
        $fq = trim(stripslashes($_REQUEST['fq']));
        if(empty($fq)) {
            $fq = null;
        } else {
            // check is fq is more than 2 parameter
            // change the ' to " to avoid syntax error
            // preg_replace 
            $fq=preg_replace('/\'/', '"', $fq); 	
            array_push($filter_query,$fq);
        }
    } 

    // decide the return field list.
    if(isset($_REQUEST['fl'])) {

        $fl = trim(stripslashes($_REQUEST['fl']));
        if(empty($fl)) {
            // TODO: this should be configurable.
            // the default field list.
            $fl = "id,title,site,description,url,content,keywords,lastModifiedDate";
        }
    } else {
        // TODO: this should be configurable.
        $fl = "id,title,site,description,url,content,keywords,lastModifiedDate";
    } 

    $start = intval($_REQUEST['start']); 
    $per_page = intval($_REQUEST['perPage']);

    $facet = json_decode(stripslashes($_REQUEST['facet']));

    // build the current query object.
    $currentQuery = array( 
        'term' => $query,
        'start' => $start,
        'fq' => $fq,
        'perPage' => $per_page,
        'facet' => $facet
    );

    // prepare the result sets.
    $results = false;
    // execute the Solr search.
    try {
        $results = searchstrap_solr_search($query, $start - 1, $per_page, 
                                           $sort, $filter_query, $fl, $facet);
    } catch (Exception $e) {
        die("Solr Search Error: Solr server exception".
        "<br>Check input fields and try again! ");
    }


    if($results){
        // NOTE: post query process...
        // we could tweak each return doc before return to
        // JavaScript client.

        // get ready the document list.
        $res = array( 
            'currentQuery' => $currentQuery,
            'total' => $results['total_items'],
            'docs' => $results['docs'],
            'facet' => $results['facet']
        );

    } else {
        // get ready the document list with empty result
        $res = array(
            'currentQuery' => $currentQuery,
            'total' => 0,
            'docs' => null
        );
    }

    $response = $_REQUEST["callback"] . "(" . json_encode($res) . ")";
    //$respons = json_encode($res);

    header("Content-Type: application/javascript");
    echo $response;

    die(); // this is required to return a proper result
}
