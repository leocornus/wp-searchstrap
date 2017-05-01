<?php
/**
 * settings page for advanced search
 */

/**
 * we will have save and reset button for the simple admin page.
 * Define the label for those actions here.
 */
$label_save_action = "Save Settings";

if (isset($_POST['searchstrap_settings_form_submit']) &&
    $_POST['searchstrap_settings_form_submit'] == 'Y') {

    $action = $_POST['action'];
    $ssdb = new SearchstrapDb();

    switch($action) {
    case $label_save_action:
        $id = $ssdb->replace_advanced_option(
            stripslashes($_POST['searchstrap_key']),
            stripslashes($_POST['searchstrap_options']));
        $msg = "Setting Updated Successfully! $id";
        break;
    default:
        break;
    }

    // show the message.
    echo '<div class="updated"><p><strong>' . $msg .
         '</strong></p></div>';
}

//$options = get_option('searchstrap_options');
//if($options === false) {
//    // no options set up yet, set the default.
//    // This is a sample default filter options, based on the 
//    // solr syntax.
//    //$options = st_livesearch_default_options();
//}
//
//$key = get_option('searchstrap_key');
//if($input_id === false) {
//    // the default input id will be livesearch.
//    //$input_id = st_livesearch_default_input_id();
//}
?>

<div class="wrap">
  <h2>SearchStrap - Advanced Search Settings</h2>
  <p>Manage options for advances search.</p>

  <form name="advancedsearch_settings_form" method="post">
    <input type="hidden" name="searchstrap_settings_form_submit"
           value="Y"/>
    <table class="form-table"><tbody>
      <tr>
        <th scope="row">Advanced Search Key:</th>
        <td>
          <input name="searchstrap_key" size="80"
                 value="<?php echo $key;?>"
          >
        </td>
      </tr>
      <tr>
        <th scope="row">Advanded Search Options: <br/>
        (One option each line, add comma at the end of the line)
        </th>
        <td>
          <textarea name="searchstrap_options"
                    rows="6" cols="98"
          ><?php echo $options;?></textarea>
        </td>
      </tr>
      <tr>
        <td></td>
        <th scope="row">
          <input type="submit" name="action" 
                 class="button-primary" 
                 value="<?php echo $label_save_action;?>" />
        </th>
      </tr>
    </tbody></table>
  </form>

  <?php
    echo '<h3>Available Key Options</h3>';
    // show all active git repos in jQuery DataTables.
    echo searchstrap_build_options_list();
  ?>
</div>
