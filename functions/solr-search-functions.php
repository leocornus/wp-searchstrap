<?php
/**
 * PHP functions to perform search against Solr server.
 * it depends on Solarium PHP lib.
 */

/**
 * this function will provide a simple interface to Solr REST APIs.
 */
function searchstrap_solr_search($search_term = '*:*', $start = 0 , 
                                 $limit = 10, $sort = null, $fq = null, 
                                 $fl = null, $facet = null) {

    // Creating a Solr Service Instance
    $solr = searchstrap_solr_instance();

    if($solr === false) {
        // failed to get Solarium client.
        throw new Exception();
    }

    // we should have a Solr client now.

    // preparing the result sets from Solr
    $raw_results = false;

    try{
        $query = $solr->createSelect();
        $query->setQuery($search_term);

        // preparing facet.
        if(!empty($facet)) {
            $facetSet = $query->getFacetSet();
            foreach($facet as $fkey => $fvalue) {
                foreach ($fvalue as $fstring) {
                    $facetSet->createFacetField($fstring)->setField($fstring);
                }
            }
        }

        // preparing field query.
        if(!empty($fq)) {

            foreach($fq as $filkey => $filter) {

                if($colPos=strpos($filter, ":")) {
                    // This removes all the characters other than 
                    // letters and numbers from the filter, 
                    // leaving us with a better key to cache
                    $reference = str_replace(' ', '' ,$filter);
                    $reference = str_replace(':', '' ,$reference);
                    $reference = str_replace('(', '' ,$reference);
                    $reference = str_replace(')', '' ,$reference);
                    $reference = str_replace('"', '' ,$reference);
                    $query->createFilterQuery($reference)->setQuery($filter);
                }
            }
        }

        // preparing the return fields list
        if(!empty($fl)) {
            $fields=explode(",", $fl);
            $query->setFields($fields);
        }
        
        // get ready the sort by.
        if(!empty($sort)) {
            if($sortPos=strpos($sort, " ")) {
                $sortField=trim(substr($sort, 0,$sortPos));
                $sortDirection=trim(substr($sort, -(strlen($sort)-$sortPos-1)));
                switch(strtolower($sortDirection)) {
                    case 'asc':
                        $query->addSort($sortField, $query::SORT_ASC);
                        break;
                    case 'desc':
                        $query->addSort($sortField, $query::SORT_DESC);
                        break;
                }
            }
        }

        // set the quality for return docs.
        $query->setStart($start)->setRows($limit);

        // perform search.
        $raw_results = $solr->select($query);
    } catch(Exception $e) {
        throw new Exception($e);
    }
    
    //get the search term
    $search_term = searchstrap_get_search_term($search_term, $fq);
    
    //get the search results as an array
    $results = searchstrap_parse_search_results($search_term, $start, $raw_results);
    return $results;
}

/**
 * Function Name:   searchstrap_parse_search_results
 * Description:     Takes raw search data and parses it into an associative array.
 *                  
 * @see searchstrap_search
 * @param   string  $search_term.       The search term
 * @param   object  $raw_results.       The raw data from solr search response
 *
 * @return  array   $parsed_results.    The search results parsed into a filtered 
 *                                      array.
 */
function searchstrap_parse_search_results($search_term, $start, $raw_results){
    $parsed_results = array();
    // If the $raw_result is not empty, take its response.
        if($raw_results){ 
            $parsed_results['search_term'] = $search_term;
            $parsed_results['total_items'] = $raw_results->getNumFound();
            $parsed_results['facet'] = searchstrap_convert_facet($raw_results->getFacetSet());
            $docs = array();
            $adoc = array();
            //create a array of values from docs
            $rank=$start;
            foreach($raw_results as $doc) {
                $rank++;
                foreach($doc as $key=>$value){
                    $adoc[$key] = $value;
                    $adoc['rank'] = $rank;
                }
                //add $adoc to $docs array
               	array_push($docs, $adoc);
   	        }
            $parsed_results['docs'] = $docs;
        } else {
            $parsed_results['total_items'] = 0;
            $parsed_results['docs'] = array();
        }
    return $parsed_results;
}

/**
 * Function Name:   searchstrap_get_search_term
 * Description:     Checks if search term is generic search, '*:*'
 *                  If it is generic, it checks for any filter queries and 
 *                  uses them as the search term
 * 
 * @param   string  $term.          The search term
 * @param   array   $search_list    The list of filter queries    
 *
 * @return  string  $term.          The new search term
 */
function searchstrap_get_search_term($term = '*:*', $search_list = null){
    
    if($term === '*:*'){
        $match = false;
        if(!empty($search_list)){
            foreach($search_list as $search){
                if(preg_match('/^keywords:/', $search)){
                    $term =  preg_replace('/keywords:|"/',' ',$search);
                    $match = true;
                }
            }
        }
        if(!$match){ 
            $term = ''; 
        }
    }
    return $term;
}

/**
 * Function Name:   searchstrap_convert_facet
 * Description:     Converts facet_counts object from solr response
 *                  into an array
 * 
 * @param   object  $facet_counts.  The facet_counts object from solr search response
 * @return  array   $facet.         The facet_counts as an array
 */
function searchstrap_convert_facet($facet_counts){
    $facet = array();
    if($facet_counts){
         foreach($facet_counts as $key=>$value){
            foreach($facet_counts->getFacet($key) as $k=>$v){
	    $k=trim($k);
                if (!empty($k))
                {
                    $facet[$key][$k] = $v;
                }
            }
         }
     }
    return $facet;
}


/**
 * Function Name:   searchstrap_build_search_params
 * Description:     Takes all other search parameters and puts into one array.
 *                  SolrPhpClient needs these values as a single array
 * 
 * @param   string  $sort.              The desired sort order parameter
 * @param   array   $filter_queries.    The filter queries parameter
 * @param   string  $field_list.        The field list parameter
 * @param   array   $facet.             The facet counts parameter
 * @return  array   $search_params.     The search parameters
 */
function searchstrap_build_search_params($sort = null, $filter_queries = null, $field_list = null, $facet = null){
    
    $search_params = array();
    $search_params['sort'] = $sort;
    $search_params['fq'] = $filter_queries;
    $search_params['fl'] =  $field_list;
           
    if($facet){
        $search_params['facet'] = 'true';
        
        foreach($facet as $key=>$value){
            $search_params[$key] = $value;
        }
    }
    return $search_params;
}

/**
 * Function Name:   searchstrap_filter_query_string
 * Description:     Builds a filter query string in a format suitable for 
 *                  solrPhpClient $additional_parameters
 * 
 * @param   string  $fq_type.               The specific filter_query field
 * @param   string  $filter_query.          The filter query values
 * @return  string  $filter_query_string.   The formatted filter_query string
 */
function searchstrap_filter_query_string($fq_type, $filter_query){
    $filter_query_string = null;
    if(!empty($filter_query))
    {
        //check if $filter_query starts with @SOLR@ prefix
        if(preg_match("/^@SOLR@/",$filter_query)){
            //remove @SOLR@ prefix
            $filter_query_string = preg_replace("/^@SOLR@/", "", $filter_query);
            $filter_query_string = stripslashes($filter_query_string);
        }
        else{//build string
            $filter_query=html_entity_decode($filter_query);
            $filter_query_string = $fq_type . ':"';
            $flag_bracket=false;
            if((substr_count($filter_query, "&") > 0 ) || (substr_count($filter_query, "|") > 0 ))
            {
                $filter_query_string = $fq_type . ':("' . $filter_query . '")';
            }   else    {
                $filter_query_string = $fq_type . ':"' . $filter_query . '"';
            }
            $filter_query_string = str_replace("&", '" AND "', $filter_query_string);
            $filter_query_string = str_replace("|", '" OR "', $filter_query_string);
        }//end if
    }//end if
    return $filter_query_string;
}

/**
 * Function Name:   searchstrap_build_filter_query_parameter
 * Description:     Builds the filter query parameter array for 
 *                  solrPhpClient $additional_parameters.  Can take 
 *                  any number of arguments and construct an array
 * 
 * @param   string  Can accept unlimited number of parameters as strings to create array
 * @return  array   $filter_query_array.   The filter_query array parameter
 */
function searchstrap_build_filter_query_parameter(){
    $filter_query_array = func_get_args();
    return $filter_query_array;
}

function searchstrap_solr_instance() {

    try{
        $config = array(
            'endpoint' => array(
                'localhost' => array(
                    'host' => '10.77.8.198',
                    'port' => 80,
                    'path' => '/search/',
                )
            )
        );

        $solr = new Solarium\Client($config);
        $solr->setAdapter('Solarium\Core\Client\Adapter\Http');

        $ping = $solr->createPing();
            //Check if solr server is available
            if ($solr->ping($ping)!=false)
                return $solr;
            else
                return false;
    } catch(Exception $e){
        echo 'Failed to create solr instance'.
            $e->getMessage() . "\n";
        return false;
    }//end catch
}
