<?php $infusionTags = infusionsofttags_read_options(); ?>
<div class="wrap">
  <h2><?php _e( "Infusionsoft SDK Settings", 'Infusionsoft Titles1' ) ?></h2>
  <div id="poststuff">
    <div id="post-body" class="metabox-holder columns-1">
      <div id="post-body-content">
        <form method="post" id="adminticketForm" name="adminticketForm" >
          <div id="genopdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', 'Effectiveness-analytics' ); ?>"><br /></div>
          <h3 class='hndle'><span><?php _e( 'Infusionsoft App', 'Infusionsoft Titles2' ); ?></span></h3>
          <div class="inside">
            <table class="form-table">
              <tr>
                <th scope="row"><label for="appdomain"><?php _e( 'Infusionsoft @ App Name:', 'IS APP' ); ?></label></th>
                <td scope="row" colspan="2">https://<input type="text" name="appdomain" value="<?php echo stripslashes( $infusionTags['appdomain'] ); ?>" size="20">.infusionsoft.com<br/><span>Your app name is the URL you use to access Infusionsoft.</span></td>
              </tr>
              <tr>
                <th scope="row"><label for="appkey"><?php _e( 'Infusionsoft @ Api Key:', 'IS KEY' ); ?></label></th>
                <td scope="row" colspan="2"><input type="text" name="appkey" value="<?php echo stripslashes( $infusionTags['appkey'] ); ?>" size="30"><br/><span>Your app key for API access.</span></td>
              </tr>
            </table>
          </div>
        </div>
        <p>
          <input type="submit" name="settings_save" id="settings_save" value="<?php _e( 'Save Options', 'effectiveness-save' ); ?>" class="button button-primary" />
        </p>
      </form>
    </div><!-- /post-body-content -->
  </div><!-- /post-body -->
  <br class="clear" />
</div><!-- /poststuff -->
</div><!-- /wrap -->
