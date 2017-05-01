<?php
/**
 * settings page for advanced search
 */

/**
 * we will have save and reset button for the simple admin page.
 * Define the label for those actions here.
 */
$label_save_action = "Save Options";

// current option, default is empty for create new options..
$current_option = array();

if (isset($_POST['searchstrap_settings_form_submit']) &&
    $_POST['searchstrap_settings_form_submit'] == 'Y') {

    // handle form submit.
    $submit_action = $_POST['submit-action'];
    $ssdb = new SearchstrapDb();

    switch($submit_action) {
    case $label_save_action:
        $id = $ssdb->replace_advanced_option(
            stripslashes($_POST['searchstrap_key']),
            stripslashes($_POST['searchstrap_options']),
            (int) $_POST['searchstrap_id']);
        $msg = "Successfully updated option with id: $id!";
        break;
    default:
        break;
    }

    // show the message.
    echo '<div class="updated"><p><strong>' . $msg .
         '</strong></p></div>';
} else {

    // normal page load.
    $param_key = searchstrap_get_request_param('key');
    if($param_key === "") {
        // create new options.
    } else {
        // working on a existing key/option,
        // get the action.
        $action = searchstrap_get_request_param('action');
        $ssdb = new SearchstrapDb();
        switch($action) {
            case "edit":
                // load the selected options.
                $current_option = 
                    $ssdb->get_advanced_option($param_key);
                break;
            case "delete":
                // TODO: handle the delete options.
                $count = $ssdb->delete_advanced_option($param_key);
                // set message.
                echo '<div class="updated"><p><strong>' .
                     'Successfully removed ' . $count . ' options' .
                     '</strong></p></div>';
                break;
        }
    }
}
?>

<div class="wrap">
  <h2>SearchStrap - Advanced Search Settings</h2>
  <p>Manage options for advances search.</p>

  <?php
    echo searchstrap_advancedsearch_settings_form($current_option);
    echo '<h3>Available Key Options</h3>';
    // show all active git repos in jQuery DataTables.
    echo searchstrap_build_options_list();
  ?>
</div>

<?php
/**
 * utility function to build the advanced search settings form.
 */
function searchstrap_advancedsearch_settings_form($current_option) {

   if(!empty($current_option)) {
       // load the current options.
       $the_id = $current_option['wpss_id'];
       $the_key = $current_option['wpss_key'];
       $the_option = $current_option['wpss_option'];
   } else {
       // this will be the case to create new options.
       $the_id = 0;
   }

   $form = <<<EOT
<form name="advancedsearch_settings_form" method="post">
  <input type="hidden" name="searchstrap_settings_form_submit"
         value="Y"/>
  <input type="hidden" name="searchstrap_id" value="{$the_id}"/>
  <table class="form-table"><tbody>
    <tr>
      <th scope="row">Advanced Search Key:</th>
      <td>
        <input name="searchstrap_key" size="80"
               value="{$the_key}"
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
        >{$the_option}</textarea>
      </td>
    </tr>
    <tr>
      <td></td>
      <th scope="row">
        <input type="submit" name="submit-action" 
               class="button-primary" 
               value="Save Options" />
      </th>
    </tr>
  </tbody></table>
</form>
EOT;

    return $form;
}
