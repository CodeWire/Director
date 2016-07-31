<?php //$infusionTags = infusionsofttags_read_options(); ?>
<div class="wrap">
  <h2><?php _e( "Add New", 'Infusionsoft Titles2' ) ?></h2>
  <div id="poststuff">
    <div id="post-body" class="metabox-holder columns-1">
      <div id="post-body-content">
        <form method="post" id="adminticketForm" name="adminticketForm">
          <div id="genopdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', 'Effectiveness-analytics' ); ?>"><br /></div>
          <div class="inside">
            <table class="form-table">
              <tr>
                <th scope="row"><label for="appkey"><?php _e( 'Title:', 'IS Title' ); ?></label></th>
                <td scope="row" colspan="2"><input type="text" name="appkey" value="<?php echo stripslashes( $infusionTags['appkey'] ); ?>" size="30"></td>
              </tr>
              <tr>
                <th scope="row"><label for="appkey"><?php _e( 'Contents:', 'IS Title' ); ?></label></th>
                <td scope="row" colspan="2"><?php the_editor($content, $id = 'content', $prev_id = 'title', $media_buttons = true, $tab_index = 2); ?></td>
              </tr>
            </table>
          </div>
        </div>
        <p>
          <input type="submit" name="addnew_save" id="addnew_save" value="<?php _e( 'Save', 'effectiveness-save' ); ?>" class="button button-primary" />
        </p>
      </form>
    </div><!-- /post-body-content -->
  </div><!-- /post-body -->
  <br class="clear" />
</div><!-- /poststuff -->
</div><!-- /wrap -->
