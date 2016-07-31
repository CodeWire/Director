<?php
/*
Plugin Name: Infusionsoft Tags
Plugin URI: http://feature.effectiveness.io
Description: Infusionsoft Tags
Version: 1.0
Author: Infusionsoft Tags
Author URI: http://feature.effectiveness.io/
*/
// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
  die( "Aren't you supposed to come here via WP-Admin?" );
}
/**
 * Holds the filesystem directory path.
 */
define( 'INFUSIONSOFT_TAGS', dirname( __FILE__ ) );
// Set the global variables for Better Search path and URL
$ticket_system_path = plugin_dir_path( __FILE__ );
$ticket_system_url  = plugins_url() . '/' . plugin_basename( dirname( __FILE__ ) );
add_action('admin_menu', 'infusionsoftSettings');
add_action( 'admin_enqueue_scripts', 'loadAdminscripts' );
add_action( 'wp_ajax_mytagactions', 'ajaxTags' );
add_action( 'wp_ajax_mytagdeleteactions', 'ajaxDeltetags' );
//add_action( 'add_meta_boxes', 'c3m_sponsor_meta' );

 // function to create the DB / Options         
function infusionsoftTagsinstall() {
  global $wpdb;
  $tablename = $wpdb->prefix . 'infusionsoft_tags';
  if($wpdb->get_var("show tables like '$tablename'") != $tablename){
    $sql = "CREATE TABLE IF NOT EXISTS ".$tablename." (
      `id` bigint(40) NOT NULL AUTO_INCREMENT,
      `title` varchar(255) NOT NULL,
      `contents` text NOT NULL,
      `tags` text NOT NULL,
      `display_tags` text NOT NULL,
      `created_at` datetime NOT NULL,
      `modified_at` datetime NOT NULL,
      PRIMARY KEY (`id`)
      );";
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);
}
}
// run the install scripts upon plugin activation
register_activation_hook(__FILE__,'infusionsoftTagsinstall');

function c3m_sponsor_meta() {
  add_meta_box( 'c3m_meta', 'Infusionsoft Tags', 'infusionsoftCats', 'post', 'normal', 'high' );
  add_meta_box( 'c3m_meta', 'Infusionsoft Tags', 'infusionsoftCats', 'page', 'normal', 'high' );
}
//add_action( 'save_post', 'c3m_save_project_meta' );
function c3m_save_project_meta( $post_ID ) {
  global $post;
  if( $post->post_type == "post" || $post->post_type == "page") {
    if (isset( $_POST ) ) {
      $tagEncode  = json_encode($_POST['getAlltags']);
      $tagOptcode = json_encode($_POST['getAllopt']);
      update_post_meta( $post_ID, '_infusion_tags', $tagEncode);
      update_post_meta( $post_ID, '_infusion_opt_id', $tagOptcode);
    }
  }
}

function infusionsoftSettings(){
  add_menu_page("Infusionsoft Tags", "Infusionsoft Settings", 0, "infusionsoft-app", "infusionsoftSettingsinit");
  add_submenu_page("infusionsoft-app", "All Tags", "All Tags", 0, "infusionsoft-lists", "infusionsoftListcontents");
  if($_REQUEST['id']!=''){ $title = 'Edit'; } else { $title = 'Add'; }
  add_submenu_page("infusionsoft-app", $title." Tags", $title." Tags", 0, "infusionsoft-add", "infusionsoftAddnewcontents");
  
  
}

function infusionsoftListcontents(){
  global $wpdb;
  global $infusion;
  $sql      = "SELECT * FROM ".$wpdb->prefix ."infusionsoft_tags ORDER BY `id` DESC";
  $results  = $wpdb->get_results($sql);
  if (!empty($_REQUEST['del']) && $_REQUEST['del']=='0') { 
    echo '<div id="message" class="error fade"><p>' . __( 'Sorry! Error occurred please try again later', 'Infusionsoft-Error-Message' ) . '</p></div>';
  }elseif (!empty($_REQUEST['del']) && $_REQUEST['del']=='1') {
    echo '<div id="message" class="updated fade"><p>' . __( 'Content has been deleted successfully.', 'Infusionsoft-add-Message' ) . '</p></div>';
  }
  ?>
  <div class="wrap">
    <h2>Infusionsoft Tags <a class="add-new-h2" href="<?php echo get_site_url(); ?>/wp-admin/admin.php?page=infusionsoft-add">Add New</a></h2>
    <table class="widefat fixed" cellspacing="0">
      <thead>
        <tr>            
          <th width="25" id="cb" class="manage-column column-cb" scope="col">No</th> 
          <th id="title" class="manage-column column-columnname" scope="col">Title</th>
          <th class="manage-column column-columnname" scope="col">Display If Tags</th>
          <th class="manage-column column-columnname" scope="col">Don't Display If Tags</th>
          <th id="shortcode" class="manage-column column-columnname" scope="col">Shortcode</th>
          <th id="date" class="manage-column column-columnname" scope="col">Date</th>
        </tr>
      </thead>
      <tfoot>
        <tr>
          <th width="25" class="manage-column column-cb" scope="col">No</th>
          <th class="manage-column column-columnname" scope="col">Title</th>
          <th class="manage-column column-columnname" scope="col">Display If Tags</th>
          <th class="manage-column column-columnname" scope="col">Don't Display If Tags</th>
          <th class="manage-column column-columnname" scope="col">Shortcode</th>
          <th class="manage-column column-columnname" scope="col">Date</th>
        </tr>
      </tfoot>
      <tbody>
        <?php 
        if(count($results)>0){
          $i=0;
          $y=1;
          foreach ($results as $key => $value) { if($i%2==0){$cls='alternate';}else{$cls='';} 
          $tagId      = $value->tags;
          $decodeTags = json_decode($tagId);
          $tagtypeId  = $value->display_tags;
          $decodeTagtype = json_decode($tagtypeId);
          $displaytagname = array();
          $dntdisplaytagname = array();
          if (!isConnect()) { 
            return false; 
          }else{
            $k=0;
            foreach ($decodeTags as $isvalue) {
              $tagResults = $infusion->dsQuery('ContactGroup', 1, 0, array('Id' => $isvalue), array(
                'GroupCategoryId',
                'GroupDescription',
                'GroupName',
                'Id'
                ));
              if($decodeTagtype[$k]=='1'){ 
                $displaytagname[].=$tagResults['0']['GroupName'];
              }else{
                $dntdisplaytagname[].=$tagResults['0']['GroupName'];
              }
              $k++;
            }
          }
          $displayTags ='';
          if(count($displaytagname)>0){
            $displayTags = implode(', ', $displaytagname);
          }
          $dntdisplayTags ='';
          if(count($dntdisplaytagname)>0){
            $dntdisplayTags = implode(', ', $dntdisplaytagname);
          }
          ?>

          <tr class="<?php echo $cls; ?>" valign="top"> 
            <th class="check-column" scope="row"><?php echo $y; ?></th>
            <td class="column-columnname"><a href="<?php echo get_site_url().'/wp-admin/admin.php?page=infusionsoft-add&id='.$value->id; ?>"><?php echo $value->title; ?></a>
              <div class="row-actions">
                <span><a href="<?php echo get_site_url().'/wp-admin/admin.php?page=infusionsoft-add&id='.$value->id; ?>">Edit</a> | </span><span class="trash"><a href="javascript:void(0);" data-id="<?php echo $value->id; ?>" class="deleterow submitdelete">Delete</a></span>
              </div>
            </td>
            <td class="column-columnname"><?php echo $displayTags; ?></td>
            <td class="column-columnname"><?php echo $dntdisplayTags; ?></td>
            <td class="column-columnname">[istags tag_id=<?php echo $value->id;?>]</td>
            <td class="column-columnname"><?php echo date('Y/m/d',strtotime($value->created_at)); ?></td>
          </tr>
          <?php $i++; $y++; }
        }?>
      </tbody>
    </table>
  </div>
  <?php
}

function infusionsoftAddnewcontents(){
 global $infusion;
 global $wpdb;
 $getEditid = $_REQUEST['id'];
 
 if ( isset( $_POST['addnew_save'] )) {
   $tagEncode  = json_encode($_POST['getAlltags']);
   $tagOptcode = json_encode($_POST['getAllopt']);
   $title      = $_POST['title'];
   $content    = $_POST['content'];
   if($getEditid!=''){
    $sql = "UPDATE ".$wpdb->prefix ."infusionsoft_tags SET `title` = '".$title."', `contents` = '".$content."', `tags`='".$tagEncode."', `display_tags`='".$tagOptcode."', `modified_at` = NOW() WHERE `id` = '".$getEditid."';";
  }else{
    $sql = "INSERT INTO ".$wpdb->prefix ."infusionsoft_tags (`id`, `title`, `contents`, `tags`, `display_tags`, `created_at`) VALUES (NULL, '".$title."', '".$content."', '".$tagEncode."', '".$tagOptcode."', NOW());";
  }
  if (!$wpdb->query($sql)) { 
    echo '<div id="message" class="error fade"><p>' . __( 'Sorry! Error occurred please try again later', 'Infusionsoft-Error-Message' ) . '</p></div>';
  }else{
    echo '<div id="message" class="updated fade"><p>' . __( 'Content has been saved successfully.', 'Infusionsoft-add-Message' ) . '</p></div>';
  }
}
if($getEditid!=''){
  $title    = 'Edit';
  $sql      = "SELECT * FROM ".$wpdb->prefix ."infusionsoft_tags WHERE `id`='".$getEditid."'";
  $results  = $wpdb->get_results($sql);
  $dtitle   = $results['0']->title;
  $dcontent = $results['0']->contents;
  $tagId      = $results['0']->tags;
  $decodeTags = json_decode($tagId);
  $tagtypeId  = $results['0']->display_tags;
  $decodeTagtype = json_decode($tagtypeId);
}else{
  $title    = 'Add';
}
$addcls   = '';
if(count($decodeTags)=='0'){
  $addcls = 'hideTable';
}

if (!isConnect()) { 
  return false; 
}else{

 $results = $infusion->dsQuery('ContactGroupCategory', 1000, 0, array('Id' => '%'), array(
  'CategoryDescription',
  'CategoryName',
  'Id'
  ));

 if(count($results)>0){
  ?>

  <div class="wrap">
    <h2><?php _e( $title." Tags", 'Infusionsoft Titles2' ) ?></h2>
    <div id="poststuff">
      <div id="post-body" class="metabox-holder columns-1">
        <div id="post-body-content">
          <form method="post" id="adminticketForm" name="adminticketForm">
            <div id="titlediv">
              <div id="titlewrap">
                <input type="text" autocomplete="off" spellcheck="true" id="title" value="<?php echo ($dtitle!='' ? stripslashes( $dtitle ) : $_POST['title']); ?>" size="30" placeholder="Enter title here" name="title">
              </div>
              <?php the_editor(($dcontent!='' ? $dcontent : $_POST['content']), $id = 'content', $prev_id = 'title', $media_buttons = true, $tab_index = 2); ?>

            </div>
            <h2>Select Type and Tags</h2>
            <table id="tagListtableid" class="wp-list-table widefat fixed pages <?php echo $addcls; ?>" border="0">
              <thead><th>Type</th><th>Categories</th><th>Tags</th><th>&nbsp;</th></thead>
              <?php
              if(count($decodeTags)>0){
                $z=0;
                foreach ($decodeTags as $value) {
                  $tagResults = $infusion->dsQuery('ContactGroup', 1, 0, array('Id' => $value), array(
                    'GroupCategoryId',
                    'GroupDescription',
                    'GroupName',
                    'Id'
                    ));
                  $tagId      = $tagResults['0']['Id'];
                  $tagName    = $tagResults['0']['GroupName'];
                  $tagCatid   = $tagResults['0']['GroupCategoryId'];

                  $tagCat = $infusion->dsQuery('ContactGroupCategory', 1, 0, array('Id' => $tagCatid), array('CategoryName'));
                  $tagCatname = $tagCat['0']['CategoryName'];
                  if($decodeTagtype[$z]=='1'){
                    $dType = 'Display if';
                  }else{
                    $dType = 'Don\'t display if';
                  }

                  ?>
                  <tr><td><?php echo $dType; ?></td><td><?php echo $tagCatname; ?></td><td><?php echo $tagName; ?></td><td><input type="hidden" name="getAlltags[]" value="<?php echo $tagId; ?>"><input type="hidden" name="getAllopt[]" value="<?php echo $decodeTagtype[$z]; ?>"><a href="javascript:void(0);" class="button removeRows">Delete</td></tr>
                  <?php
                  $z++;
                }
              }
              ?>
            </table>
            <br/>
            <select name="isType" id="isType" class="select-field"><option value="">Choose Type</option> <option value="1">Display if</option><option value="2">Don't display if</option></select>
            <select name="isCat" id="isCat" class="select-field"><option value="">Choose Categories</option>
              <?php 
              for ($i=0; $i <count($results); $i++) { 
                echo '<option value="'.$results[$i]['Id'].'">'.$results[$i]['CategoryName'].'</option>';
              }
              ?>
            </select>
            <span id="isTagsspan"><select name="isTags" id="isTags" class="select-field"><option value="">Choose Tags</option></select></span><input type="hidden" name="hiddenIscatid" id="hiddenIscatid" value=""><input type="hidden" name="hiddenIscatename" id="hiddenIscat" value=""><a href="javascript:void(0);" class="button" id="plussign">Add</a><span id="spinloader"></span>

            <?php
          }
        }?>


        <p>
          <input type="hidden" name="actionid" value="<?php echo ($getEditid!='' ? $getEditid : ''); ?>">
          <input type="submit" name="addnew_save" id="addnew_save" value="<?php _e( 'Save', 'effectiveness-save' ); ?>" class="button button-primary" />
        </p>
      </form>
    </div><!-- /post-body-content -->
  </div><!-- /post-body -->
  <br class="clear" />
</div><!-- /poststuff -->
</div><!-- /wrap -->

<?php

}

function infusionsoftSettingsinit(){
  global $wpdb;
  global $infusion;
  
  $poststable = $wpdb->posts;
  $infusionTags = infusionsofttags_read_options();
  if ( isset( $_POST['analytics_save'] )) {
    $infusionTags['appdomain'] = $_POST['appdomain'];
    $infusionTags['appkey']    = $_POST['appkey'];
    update_option( 'infusionsoft_tags_settings', $infusionTags );
    if (!isConnect()) { 
      echo '<div id="message" class="error fade"><p>' . __( 'Unable to connect to Infusionsoft!', 'Infusionsoft-Error-Message' ) . '</p></div>';
    }else{
      echo '<div id="message" class="updated fade"><p>' . __( 'Options saved successfully.', 'Infusionsoft-Update-Message' ) . '</p></div>';
    }
  }
  
  load_template( dirname( __FILE__ ) . '/templates/appform.php');
}

function infusionsoft_tags_shortcode( $atts ) {
  global $wpdb;
  global $infusion;
  extract(shortcode_atts( array(
    'tag_id' => 'sss'
    ), $atts ));
  $tagid = $atts['tag_id'];
  $sql      = "SELECT * FROM ".$wpdb->prefix ."infusionsoft_tags WHERE `id`='".$tagid."'";
  $results  = $wpdb->get_results($sql);
  if(count($results)>0){ 
    $tagId      = $results['0']->tags;
    $decodeTags = json_decode($tagId);
    $tagtypeId  = $results['0']->display_tags;
    $decodeTagtype = json_decode($tagtypeId);
    $addcls   = '';
    if(count($decodeTags)=='0'){
      $addcls = 'hideTable';
    }

    ?>
    <p>Title <?php echo $results['0']->title; ?></p>
    <p>Contents <?php echo $results['0']->contents; ?></p>
    <?php
    if (!isConnect()) { 
      return false; 
    }else{
      ?>
      <table id="tagListtableid" class="wp-list-table widefat fixed pages <?php echo $addcls; ?>" border="0">
        <thead><th>Categories</th><th>Tags</th></thead>
        <?php
        if(count($decodeTags)>0){
          $z=0;
          foreach ($decodeTags as $key => $value) {
            $tagResults = $infusion->dsQuery('ContactGroup', 1, 0, array('Id' => $value), array(
              'GroupCategoryId',
              'GroupDescription',
              'GroupName',
              'Id'
              ));
            $tagId      = $tagResults['0']['Id'];
            $tagName    = $tagResults['0']['GroupName'];
            $tagCatid   = $tagResults['0']['GroupCategoryId'];

            $tagCat = $infusion->dsQuery('ContactGroupCategory', 1, 0, array('Id' => $tagCatid), array('CategoryName'));
            $tagCatname = $tagCat['0']['CategoryName'];
            if($decodeTagtype[$z]=='1'){        
              ?>
              <tr><td><?php echo $tagCatname; ?></td><td><?php echo $tagName; ?></td></tr>
              <?php
            }
            $z++;
          }
        }
      }
      ?>
    </table>
    <?php
  }
}
function infusionsoftCats() {
  global $infusion;
  global $post;
  if (!isConnect()) { 
    return false; 
  }else{

   $results = $infusion->dsQuery('ContactGroupCategory', 1000, 0, array('Id' => '%'), array(
    'CategoryDescription',
    'CategoryName',
    'Id'
    ));

   if(count($results)>0){
    $tagId      = get_post_meta($post->ID, '_infusion_tags');
    $decodeTags = json_decode($tagId['0']);
    $tagtypeId  = get_post_meta($post->ID, '_infusion_opt_id');
    $decodeTagtype = json_decode($tagtypeId['0']);
    $addcls   = '';
    if(count($decodeTags)=='0'){
      $addcls = 'hideTable';
    }
    ?>
    <table id="tagListtableid" class="wp-list-table widefat fixed pages <?php echo $addcls; ?>" border="0">
      <thead><th>Type</th><th>Categories</th><th>Tags</th><th>&nbsp;</th></thead>
      <?php
      if(count($decodeTags)>0){
        $z=0;
        foreach ($decodeTags as $key => $value) {
          $tagResults = $infusion->dsQuery('ContactGroup', 1, 0, array('Id' => $value), array(
            'GroupCategoryId',
            'GroupDescription',
            'GroupName',
            'Id'
            ));
          $tagId      = $tagResults['0']['Id'];
          $tagName    = $tagResults['0']['GroupName'];
          $tagCatid   = $tagResults['0']['GroupCategoryId'];

          $tagCat = $infusion->dsQuery('ContactGroupCategory', 1, 0, array('Id' => $tagCatid), array('CategoryName'));
          $tagCatname = $tagCat['0']['CategoryName'];
          if($decodeTagtype[$z]=='1'){
            $dType = 'Display if';
          }else{
            $dType = 'Don\'t display if';
          }

          ?>
          <tr><td><?php echo $dType; ?></td><td><?php echo $tagCatname; ?></td><td><?php echo $tagName; ?></td><td><input type="hidden" name="getAlltags[]" value="<?php echo $tagId; ?>"><input type="hidden" name="getAllopt[]" value="<?php echo $decodeTagtype[$z]; ?>"><a href="javascript:void(0);" class="button removeRows">Delete</td></tr>
          <?php
          $z++;
        }
      }
      ?>
    </table>

    <div class="form-style-2">
      <form action="" method="post">
        <label for="field1"><span>Type  </span><select name="isType" id="isType" class="select-field"><option value="">Choose Type</option> <option value="1">Display if</option><option value="2">Don't display if</option></select></label>
        <label for="field2"><span>Categories </span><select name="isCat" id="isCat" class="select-field"><option value="">Choose Categories</option>
          <?php 
          for ($i=0; $i <count($results); $i++) { 
            echo '<option value="'.$results[$i]['Id'].'">'.$results[$i]['CategoryName'].'</option>';
          }
          ?>
        </select><div id="spinloader"></div></label>
        <label for="field3"><span>Tags </span><div id="isTagsspan"><select name="isTags" id="isTags" class="select-field"><option value="">Choose Tags</option></select></div></label>

        <label><span>&nbsp;</span><input type="hidden" name="hiddenIscatid" id="hiddenIscatid" value=""><input type="hidden" name="hiddenIscatename" id="hiddenIscat" value=""><a href="javascript:void(0);" class="button" id="plussign">Add</a></label></form> </div>

        <?php
      }
    }
  }

  function ajaxTags(){
    global $infusion;
    if (!isConnect()) { 
      return false; 
    }else{
      $postId  = $_POST['catid'];
      $results = $infusion->dsQuery('ContactGroup', 1000, 0, array('GroupCategoryId' => $postId), array(
        'GroupCategoryId',
        'GroupDescription',
        'GroupName',
        'Id'
        ));
      echo '<select name="isTags" id="isTags" class="select-field"><option value="">Choose Tags</option>';
      if(count($results)>0){
        for ($i=0; $i <count($results); $i++) { 
          echo '<option value="'.$results[$i]['Id'].'">'.$results[$i]['GroupName'].'</option>';
        }
      }
      echo '</select>';
    }
wp_die(); // ajax call must die to avoid trailing 0 in your response
}

function ajaxDeltetags(){
  global $wpdb;
  $getid    = $_POST['id'];
  if($getid!=''){
    $sqlTags      = "SELECT * FROM ".$wpdb->prefix ."infusionsoft_tags WHERE `id` = '".$getid."'";
    $resultsTags  = $wpdb->get_results($sqlTags);
    if(count($resultsTags)>0){
      $sql = "DELETE FROM ".$wpdb->prefix ."infusionsoft_tags WHERE `id` = '".$getid."'";
      if (!$wpdb->query($sql)) { 
        echo '0';
      }else{
        echo '1';
      }
    }
  }
  wp_die();
}

/**
* Adding WordPress plugin action links.
*
* @param array $links
* @return array
*/
function infusionsoftActionlinks( $links ) {

  return array_merge(
    array(
      'settings' => '<a href="' . admin_url( 'admin.php?page=infusionsoft-app' ) . '">' . __( 'Settings', 'infusionsoft_tags_settings' ) . '</a>'
      ),
    $links
    );

}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'infusionsoftActionlinks' );

/**
* Default options.
*
* @return array Array of default options
*/
function infusionsoft_tags_default_options() {
  $infusionTagsettings = array (
'enable_plugin' => false, // Enable plugin switch
'disable_notice' => false,  // // Disable notice that is displayed when enable_plugin is false
'analytics_content' => '',    // Analytics code
);
  return apply_filters( 'infusionsoft_tags_default_options', $infusionTagsettings );
}

/**
* Function to read options from the database and add any new ones.
*
* @return array Options from the database
*/
function infusionsofttags_read_options() {
  $infusionTagsettings_changed = false;

  $defaults = infusionsoft_tags_default_options();

  $infusionTagsettings = array_map( 'stripslashes', (array) get_option( 'infusionsoft_tags_settings' ) );
unset( $infusionTagsettings[0] ); // produced by the (array) casting when there's nothing in the DB
// If there are any new options added to the Default Options array, let's add them
foreach ( $defaults as $k=>$v ) {
  if ( ! isset( $infusionTagsettings[ $k ] ) ) {
    $infusionTagsettings[ $k ] = $v;
  }
  $infusionTagsettings_changed = true;
}
if ( true == $infusionTagsettings_changed ) {
  update_option( 'infusionsoft_tags_settings', $infusionTagsettings );
}

return apply_filters( 'infusionsofttags_read_options', $infusionTagsettings );
}
function isConnect() {
  $infusionTags = infusionsofttags_read_options();
  global $infusion;

  if ($infusion) { return true; }

  include_once plugin_dir_path( __FILE__ ) . 'isdk.php';
  $infusion = new iSDK($infusionTags['appdomain'], 'infusion', $infusionTags['appkey']);

  if(!$infusion->errorCode && $aff = $infusion->dsFind('Affiliate', 1, 0, 'Id', '%', array('Id')))
  {
    return true;
  } else {
if(1) { # In the future see if we're an admin or such
if ($infusion->errorCode == 2) {
  $error = "Your Infusionsoft API Key is incorrect. Please update it.";
} else {
  $error = "Unable to connect to Infusionsoft! ".$infusion->error;
}
} # admin?

$infusion = false;
return false;
}

}
function loadAdminscripts() {
  wp_register_style( 'infusionsoftassets', plugins_url('css/style.css', __FILE__) );
  wp_enqueue_style( 'infusionsoftassets' );
  wp_enqueue_script( 'loadAdminscripts', plugin_dir_url( __FILE__ ) . 'js/tags.js' );
  wp_localize_script( 'loadAdminscripts', 'ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}

add_shortcode( 'istags', 'infusionsoft_tags_shortcode' );