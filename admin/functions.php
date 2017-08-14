<?php
/**
 * some utility functions for shortcodes.
 */

/**
 * the clean way to get a http request parameter's value.
 */
function searchstrap_get_request_param($param) {

    // try to find the selected theme name
    if (array_key_exists($param, $_POST)) {
        $value = $_POST[$param];
    } elseif (array_key_exists($param, $_GET)) {
        $value = $_GET[$param];
    } elseif (array_key_exists($param, $_COOKIE)) {
        // cookie is one of the request in PHP.
        // check manuel $_REQUEST for details.
        $value = $_COOKIE[$param];
    } else {
        $value = '';
    }

    if(is_string($value)) {
        $value = str_replace("\r\n", "\n", stripslashes($value));
    }

    return $value;
}

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
        $ssdb = new SearchstrapDb();
        $options = $ssdb->get_advanced_option($key);
        // we whill get the options from database.
        $options = $options['wpss_option'];
    }

    return $options;
}

/**
 * build the table list for all options
 */
function searchstrap_build_options_list() {

    $ssdb = new SearchstrapDb();
    $options = $ssdb->get_all_advances_options();

    $rows = array();
    foreach($options as $option) {

        $label = <<<EOT
<strong>{$option['wpss_key']}</strong><br/>
<a href="?page={$_REQUEST['page']}&key={$option['wpss_key']}&action=edit">
Edit</a> | 
<a href="?page={$_REQUEST['page']}&key={$option['wpss_key']}&action=delete">
Delete</a> 
EOT;

        // one tr for each row.
        $tr = <<<EOT
<tr>
  <td>{$option['wpss_id']}</td>
  <td>{$label}</td>
  <td><pre style="white-space: pre-wrap; white-space: -moz-pre-wrap; white-space: -pre-wrap; white-space: -o-pre-wrap;">{$option['wpss_option']}</pre></td>
</tr>
EOT;
        $rows[] = $tr;
    }

    $trs = implode("\n", $rows);
    $table_id = "advanced-options";

    // here is the data table.
    $dt = <<<EOT
<table class="widefat wp-list-table" id="{$table_id}">
<thead>
  <th width="18px">ID</th>
  <th width="120px">Key</th>
  <th>Options</th>
</thead>
<tbody>
  {$trs}
</tbody>
<tfoot>
  <th>ID</th>
  <th>Key</th>
  <th>Options</th>
</tfoot>
</table>
{$dt_js}
EOT;

    return $dt;
}
