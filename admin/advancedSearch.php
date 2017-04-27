<?php
/**
 * settings page for advanced search
 */

//wp_enqueue_script("wp_ajax_response");

/**
 * we will have save and reset button for the simple admin page.
 * Define the label for those actions here.
 */
$label_save_action = "Save Settings";
$label_reset_action = "Reset to Default";

if (isset($_POST['livesearch_settings_form_submit']) &&
    $_POST['livesearch_settings_form_submit'] == 'Y') {

    $action = $_POST['action'];
    switch($action) {
    case $label_save_action:
        // save settings submit. save user input to database.
        update_option('livesearch_input_id',
                stripslashes($_POST['livesearch_input_id']));
        update_option('livesearch_filter_options',
                stripslashes($_POST['livesearch_filter_options']));

        $msg = 'Setting Updated Successfully!';
        break;
    case $label_reset_action:
        // get default options.
        update_option('livesearch_input_id',
                      st_livesearch_default_input_id());
        update_option('livesearch_filter_options',
                      st_livesearch_default_options());

        $msg = 'Setting Reseted Successfully!';
        break;
    default:
        break;
    }

    // show the message.
    echo '<div class="updated"><p><strong>' . $msg .
         '</strong></p></div>';
}

$options = get_option('livesearch_filter_options');
if($options === false) {
    // no options set up yet, set the default.
    // This is a sample default filter options, based on the 
    // solr syntax.
    $options = st_livesearch_default_options();
}

$input_id = get_option('livesearch_input_id');
if($input_id === false) {
    // the default input id will be livesearch.
    $input_id = st_livesearch_default_input_id();
}
?>

<div class="wrap">
  <h2>Search Toolkit - LiveSearch Settings</h2>
  <p>General settings for live searchb box.</p>

  <form name="livesearch_settings_form" method="post">
    <input type="hidden" name="livesearch_settings_form_submit"
           value="Y"/>
    <table class="form-table"><tbody>
      <tr>
        <th scope="row">Live Search Input Box ID:</th>
        <td>
          <input name="livesearch_input_id" size="80"
                 value="<?php echo $input_id;?>"
          >
        </td>
      </tr>
      <tr>
        <th scope="row">Live Search Filter Options: <br/>
        (One Filter Each Line)
        </th>
        <td>
          <textarea name="livesearch_filter_options"
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
          <input type="submit" name="action" 
                 class="button" 
                 value="<?php echo $label_reset_action;?>" />
        </th>
      </tr>
    </tbody></table>
  </form>
</div>
