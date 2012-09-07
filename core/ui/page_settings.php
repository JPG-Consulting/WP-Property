<?php
/**
 * WP-Property Settings
 *
 */

  //** WP Properties Global */
  global $wp_properties;

  //** Check if premium folder is writable */
  $wp_messages = WPP_F::check_premium_folder_permissions();

  if( isset( $_REQUEST[ 'message' ] ) ) {
    switch( $_REQUEST[ 'message' ] ) {
      case 'updated':
      $wp_messages[ 'notice' ][] = __( "Settings updated.", 'wpp' );
      break;
    }
  }

  //** Default wrapper class */
  $wrapper_classes = array( 'wpp_settings_page' );

  //** Additional wrapper classes */
  if( get_option( 'permalink_structure' ) == '' ) {
    $wrapper_classes[] = 'no_permalinks';
  } else {
    $wrapper_classes[] = 'have_permalinks';
  }

?>

<div class="wrap wpp-ui-wrap <?php echo implode( ' ', $wrapper_classes ); ?>">

  <div class="wpp-ui-header wpp-collapsed">

    <div class="alignleft">
      <?php screen_icon(); ?>
      <h1 class="wpp-title"><?php _e( 'Settings', 'wpp' ); ?></h1>
    </div>
  </div>

  <?php if( isset( $wp_messages[ 'error' ] ) && $wp_messages[ 'error' ] ): ?>
  <div class="wpxi_core_notice wpp_error">
  <?php foreach( $wp_messages[ 'error' ] as $error_message ): ?>
    <?php echo $error_message; ?>
  <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if( isset( $wp_messages[ 'notice' ] ) && $wp_messages[ 'notice' ] ): ?>
  <div class="wpxi_core_notice">
  <?php foreach( $wp_messages[ 'notice' ] as $notice_message ): ?>
    <?php echo $notice_message; ?>
  <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <h2 class="wpp_fake"></h2>

<div class="wpp_ui_wrapper">

  <form class="wpp_settings" method="post" action="<?php echo admin_url( 'edit.php?post_type=property&page=property_settings' ); ?>" enctype="multipart/form-data">
    <?php wp_nonce_field( 'wpp_setting_save' ); ?>

    <table class="form-table">
      <tbody>

        <tr>
          <td>
            <div class="wpxi-ajax-container"><?php echo WPP_UI::main_settings(); ?></div>
          </td>

          <td class="wpp_ui_sidebar">
            <div class="wpp_sidebar_wrapper">

              <div class="wpp_sidebar_options">
                <?php $wpp_plugin_settings_nav = apply_filters( 'wpp_settings_nav', array() ); ?>
                <ul class="wpp_settings">
                  <li><span class="wpp_link wpp_active" data-wpp_section_class="self" data-wpp_toggle_ui="main"><?php _e( 'Main', 'wpp' ); ?></span></li>
                  <li><span class="wpp_link" data-wpp_section_class="self" data-wpp_toggle_ui="display"><?php _e( 'Display', 'wpp' ); ?></span></li>
                  <li><span class="wpp_link" data-wpp_section_class="self" data-wpp_toggle_ui="maps"><?php _e( 'Maps', 'wpp' ); ?></span></li>
                  <?php
                    if( is_array( $wp_properties[ 'available_features' ] ) ) {

                      $wpp_plugin_settings_nav = apply_filters( 'wpp_settings_nav', array() );

                      foreach( $wp_properties[ 'available_features' ] as $plugin ) {
                        if( @$plugin[ 'status' ] == 'disabled' ) {
                          unset( $wpp_plugin_settings_nav[$plugin] );
                        }
                      }

                      if( is_array( $wpp_plugin_settings_nav ) ) {
                        foreach( $wpp_plugin_settings_nav as $nav ) {
                          echo "<li><span class=\"wpp_link\" data-wpp_toggle_ui=\"settings_page\" data-wpp_section_class=\"".$nav["slug"]."\">".$nav[ "title" ]."</a></li>\n";
                        }
                      }
                    }
                  ?>
                  <li><span class="wpp_link" data-wpp_section_class="self" data-wpp_toggle_ui="plugins"><?php _e( 'Premium Features', 'wpp' ); ?></span></li>
                  <li><span class="wpp_link" data-wpp_section_class="self" data-wpp_toggle_ui="help"><?php _e( 'Help', 'wpp' ); ?></span></li>
                </ul>
              </div>

              <div class="wpp_actions_bar">
                <div class="wpp_save_wrapper">
                  <input type="submit" class="wpp_button wpp_red wpp_save_settings" value="<?php _e( 'Save Settings', 'wpp' ); ?>" />
                  <div class="wpp-ui wpp_sidebar_response"></div>
                </div>
              </div>

            </div>
          </td>
        </tr>

      </tbody>
    </table>

  </form>
  </div>

</div>