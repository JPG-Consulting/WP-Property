<?php
/**
 * Settings Page Section - Plugins Options
 *
 * @version 1.37.0
 * @package WP-Property
 * @author  team@UD
 * @copyright 2010-2012 Usability Dyanmics, Inc.
 */

//** Required globals */
global $wp_properties;
?>

<script type="text/javascript">
  jQuery(document).ready(function(){
    // Check plugin updates
    jQuery( "#wpp_ajax_check_plugin_updates" ).click( function() {
      jQuery( '.plugin_status' ).remove();
      jQuery.post( ajaxurl, {
          action: 'wpp_ajax_check_plugin_updates'
        }, function( data ) {
          message = "<div class='plugin_status updated fade'><p>" + data + "</p></div>";
          jQuery( message ).insertAfter( "h2" );
        });
    });
  });
</script>

<table class="form-table wpp_option_table">
  <tr class="wpp_first">
    <th>
      <strong><?php _e( 'Feature Updates','wpp' ); ?></strong>
      <div class="description"><p></p></div>
    </th>
    <td>

      <p id="wpp_plugins_ajax_response" class="hidden"></p>

      <div class="wpp_settings_block">
        <input type="button" value="<?php _e( 'Check Updates','wpp' );?>" id="wpp_ajax_check_plugin_updates" />
        <?php _e( 'to download, or update, all premium features purchased for this domain.','wpp' );?>
      </div>

      <div class="description"><?php printf( __( 'For more WP-Property information, visit the Usability Dynamics to <a href="%1s">view help tutorials</a>, participate in the <a href="%1s">community forum</a> and check out the current <a href="%1s">premium features</a>.', 'wpp' ), 'https://usabilitydynamics.com/tutorials/wp-property-help/', 'https://usabilitydynamics.com/forums/', 'https://usabilitydynamics.com/products/wp-property/premium-features/' ); ?></div>

    </td>
  </tr>
</table>

<table id="wpp_premium_feature_table" cellpadding="0" cellspacing="0">
  <?php foreach ((array)$wp_properties['available_features'] as $plugin_slug => $plugin_data): ?>

    <input type="hidden" name="wpp_settings[available_features][<?php echo $plugin_slug; ?>][title]" value="<?php echo $plugin_data['title']; ?>" />
    <input type="hidden" name="wpp_settings[available_features][<?php echo $plugin_slug; ?>][tagline]" value="<?php echo $plugin_data['tagline']; ?>" />
    <input type="hidden" name="wpp_settings[available_features][<?php echo $plugin_slug; ?>][image]" value="<?php echo $plugin_data['image']; ?>" />
    <input type="hidden" name="wpp_settings[available_features][<?php echo $plugin_slug; ?>][description]" value="<?php echo $plugin_data['description']; ?>" />

    <?php $installed = WPP_F::check_premium($plugin_slug); ?>
    <?php $active = ( @$wp_properties['installed_features'][$plugin_slug]['disabled'] != 'false' ? true : false ); ?>

    <?php if ($installed): ?>
      <?php /* Do this to preserve settings after page save. */ ?>
      <input type="hidden" name="wpp_settings[installed_features][<?php echo $plugin_slug; ?>][disabled]" value="<?php echo $wp_properties['installed_features'][$plugin_slug]['disabled']; ?>" />
      <input type="hidden" name="wpp_settings[installed_features][<?php echo $plugin_slug; ?>][name]" value="<?php echo $wp_properties['installed_features'][$plugin_slug]['name']; ?>" />
      <input type="hidden" name="wpp_settings[installed_features][<?php echo $plugin_slug; ?>][version]" value="<?php echo $wp_properties['installed_features'][$plugin_slug]['version']; ?>" />
      <input type="hidden" name="wpp_settings[installed_features][<?php echo $plugin_slug; ?>][description]" value="<?php echo $wp_properties['installed_features'][$plugin_slug]['description']; ?>" />
    <?php endif; ?>

    <tr class="wpp_premium_feature_block">

      <td valign="top" class="wpp_premium_feature_image">
        <a href="http://usabilitydynamics.com/products/wp-property/"><img src="<?php echo $plugin_data['image']; ?>" /></a>
      </td>

      <td valign="top">
        <div class="wpp_box">
          <div class="wpp_box_header">
            <strong><?php echo $plugin_data['title']; ?></strong>
            <p><?php echo $plugin_data['tagline']; ?> <a href="https://usabilitydynamics.com/products/wp-property/premium/?wp_checkout_payment_domain=<?php echo $this_domain; ?>"><?php _e('[purchase feature]', 'wpp') ?></a>
            </p>
          </div>
          <div class="wpp_box_content">
            <p><?php echo $plugin_data['description']; ?></p>

          </div>

          <div class="wpp_box_footer clearfix">
            <?php if ($installed) { ?>

              <div class="alignleft">
                <?php
                if ($wp_properties['installed_features'][$plugin_slug]['needs_higher_wpp_version'] == 'true') {
                  printf(__('This feature is disabled because it requires WP-Property %1$s or higher.'), $wp_properties['installed_features'][$plugin_slug]['minimum_wpp_version']);
                } else {
                  echo WPP_F::checkbox("name=wpp_settings[installed_features][$plugin_slug][disabled]&label=" . __('Disable plugin.', 'wpp'), $wp_properties['installed_features'][$plugin_slug]['disabled']);
                  ?>
                </div>
                <div class="alignright"><?php _e('Feature installed, using version', 'wpp') ?> <?php echo $wp_properties['installed_features'][$plugin_slug]['version']; ?>.</div>
    <?php
    }
  } else {
    $pr_link = 'https://usabilitydynamics.com/products/wp-property/premium/';
    echo sprintf(__('Please visit <a href="%s">UsabilityDynamics.com</a> to purchase this feature.', 'wpp'), $pr_link);
  }
  ?>
          </div>
        </div>
      </td>
    </tr>
<?php endforeach; ?>
</table>