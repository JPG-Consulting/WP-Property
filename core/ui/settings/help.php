<?php
/**
 * Settings Page Section - Help
 *
 * @version 1.37.0
 * @package WP-Property
 * @author  team@UD
 * @copyright 2010-2012 Usability Dyanmics, Inc.
 */

//** Required globals */
global $wp_properties;

$parseUrl = parse_url( trim( get_bloginfo( 'url' ) ) );
$this_domain = trim( $parseUrl[ 'host' ] ? $parseUrl[ 'host' ] : array_shift( explode( '/', $parseUrl[ 'path' ], 2 ) ) );
?>

<div class="wpp_inner_tab">
  <script type="text/javascript">

    jQuery(document).ready(function(){
      // Show settings array
      jQuery( "#wpp_show_settings_array" ).click( function() {
        jQuery( "#wpp_show_settings_array_cancel" ).show();
        jQuery( "#wpp_show_settings_array_result" ).show();
      });

      // Hide settings array
      jQuery( "#wpp_show_settings_array_cancel" ).click( function() {
        jQuery( "#wpp_show_settings_array_result" ).hide();
        jQuery( this ).hide();
      });

      // Revalidate all addresses
      jQuery( "#wpp_ajax_revalidate_all_addresses" ).click( function() {

        jQuery( this ).val( 'Processing...' );
        jQuery( this ).attr( 'disabled', true );
        jQuery( '.address_revalidation_status' ).remove();

        jQuery.post( ajaxurl, {
            action: 'wpp_ajax_revalidate_all_addresses'
            }, function( data ) {

            jQuery( "#wpp_ajax_revalidate_all_addresses" ).val( 'Revalidate again' );
            jQuery( "#wpp_ajax_revalidate_all_addresses" ).attr( 'disabled', false );

            if( data.success == 'true' )
              message = "<div class='address_revalidation_status updated fade'><p>" + data.message + "</p></div>";
            else
              message = "<div class='address_revalidation_status error fade'><p>" + data.message + "</p></div>";

            jQuery( message ).insertAfter( "h2" );
          }, 'json' );
      });

      // Show property query
      jQuery( "#wpp_ajax_property_query" ).click( function() {

        var property_id = jQuery( "#wpp_property_class_id" ).val();

        jQuery( "#wpp_ajax_property_result" ).html( "" );

        jQuery.post( ajaxurl, {
            action: 'wpp_ajax_property_query',
            property_id: property_id
          }, function( data ) {
            jQuery( "#wpp_ajax_property_result" ).show();
            jQuery( "#wpp_ajax_property_result" ).html( data );
            jQuery( "#wpp_ajax_property_query_cancel" ).show();

          });

      });

      // Show image data
      jQuery( "#wpp_ajax_image_query" ).click( function() {

        var image_id = jQuery( "#wpp_image_id" ).val();

        jQuery( "#wpp_ajax_image_result" ).html( "" );

        jQuery.post( ajaxurl, {
            action: 'wpp_ajax_image_query',
            image_id: image_id
          }, function( data ) {
            jQuery( "#wpp_ajax_image_result" ).show();
            jQuery( "#wpp_ajax_image_result" ).html( data );
            jQuery( "#wpp_ajax_image_query_cancel" ).show();

          });

      });

      // Hide property query
      jQuery( "#wpp_ajax_property_query_cancel" ).click( function() {
        jQuery( "#wpp_ajax_property_result" ).hide();
        jQuery( this ).hide();
      });

      // Hide image query
      jQuery( "#wpp_ajax_image_query_cancel" ).click( function() {
        jQuery( "#wpp_ajax_image_result" ).hide();
        jQuery( this ).hide();
      });

      /** Clear Cache */
      jQuery( "#wpp_clear_cache" ).click( function() {
        jQuery( '.clear_cache_status' ).remove();
        jQuery.post( ajaxurl, {
            action: 'wpp_ajax_clear_cache'
          }, function( data ) {
            message = "<div class='clear_cache_status updated fade'><p>" + data + "</p></div>";
            jQuery( message ).insertAfter( "h2" );
          });
      });

      //** Mass set property type */
      jQuery( "#wpp_ajax_max_set_property_type" ).click( function() {

        if( !confirm( "<?php echo sprintf(__( 'You are about to set ALL your %2$s to the selected %1$s type. Are you sure?', 'wpp' ), WPP_F::property_label('singular'),WPP_F::property_label('plural')); ?>" ) ) {
          return;
        }

        var property_type = jQuery( "#wpp_ajax_max_set_property_type_type" ).val();

        jQuery.post( ajaxurl, {
          action: 'wpp_ajax_max_set_property_type',
          property_type: property_type
          }, function( data ) {
            jQuery( "#wpp_ajax_max_set_property_type_result" ).show();
            jQuery( "#wpp_ajax_max_set_property_type_result" ).html( data );
          });

      });
    });

  </script>
  <div class="wpp_settings_block">
    <label>
    <?php _e( 'If prompted for your domain name during a premium feature purchase, enter as appears here:','wpp' ); ?>
    <input type="text" readonly="true" value="<?php echo $this_domain; ?>" size="<?php echo strlen( $this_domain ) + 10; ?>" />
    </label>
  </div>

  <div class="wpp_settings_block">
    <?php _e( 'Restore backup of WP-Property configuration', 'wpp' ); ?>: <input name="wpp_settings[settings_from_backup]" type="file" />
    <?php _e( 'Download current WP-Property configuration:', 'wpp' );?>
    <a class="button" href="<?php echo wp_nonce_url( "edit.php?post_type=property&page=property_settings&wpp_action=download-wpp-backup", 'download-wpp-backup' ); ?>"><?php echo sanitize_key( 'wpp-' . get_bloginfo( 'name' ) ) . '-' . date( 'y-m-d' ) . '.json';  ?></a>
  </div>

  <div class="wpp_settings_block">
    <?php $google_map_localizations = WPP_F::draw_localization_dropdown( 'return_array=true' ); ?>
    <?php _e( 'Revalidate all addresses using', 'wpp' ); ?> <b><?php echo $google_map_localizations[$wp_properties[ 'configuration' ][ 'google_maps_localization' ]]; ?></b> <?php _e( 'localization', 'wpp' ); ?>.
      <input type="button" value="<?php _e( 'Revalidate','wpp' );?>" id="wpp_ajax_revalidate_all_addresses">
      <?php if (!UD_API::available_address_validation()): ?>
      <span class="address_validation_unavailable">Be aware, Google's Geocoding Service right now is unavailable because query limit was exceeded. Try again later.</span>
      <?php endif; ?>
  </div>

  <div class="wpp_settings_block"><?php echo sprintf(__( 'Enter in the ID of the %1$s you want to look up, and the class will be displayed below.','wpp' ), WPP_F::property_label('singular')) ?>
    <input type="text" id="wpp_property_class_id" />
    <input type="button" value="<?php _e( 'Lookup','wpp' ) ?>" id="wpp_ajax_property_query"> <span id="wpp_ajax_property_query_cancel" class="wpp_link hidden"><?php _e( 'Cancel','wpp' ) ?></span>
    <pre id="wpp_ajax_property_result" class="wpp_class_pre hidden"></pre>
  </div>

  <div class="wpp_settings_block"><?php echo sprintf(__( 'Get %1$s image data.','wpp' ), WPP_F::property_label('singular')); ?>
    <label for="wpp_image_id"><?php echo sprintf(__( '%1$s ID:','wpp' ), ucfirst(WPP_F::property_label('singular'))); ?></label>
    <input type="text" id="wpp_image_id" />
    <input type="button" value="<?php _e( 'Lookup','wpp' ) ?>" id="wpp_ajax_image_query"> <span id="wpp_ajax_image_query_cancel" class="wpp_link hidden"><?php _e( 'Cancel','wpp' ) ?></span>
    <pre id="wpp_ajax_image_result" class="wpp_class_pre hidden"></pre>
  </div>

  <div class="wpp_settings_block">
    <?php _e( 'Look up the <b>$wp_properties</b> global settings array.  This array stores all the default settings, which are overwritten by database settings, and custom filters.','wpp' ) ?>
    <input type="button" value="<?php _e( 'Show $wp_properties','wpp' ) ?>" id="wpp_show_settings_array"> <span id="wpp_show_settings_array_cancel" class="wpp_link hidden"><?php _e( 'Cancel','wpp' ) ?></span>
    <pre id="wpp_show_settings_array_result" class="wpp_class_pre hidden"><?php print_r( $wp_properties ); ?></pre>
  </div>

  <div class="wpp_settings_block">
    <?php _e( 'Clear WPP Cache. Some shortcodes and widgets use cache, so the good practice is clear it after widget, shortcode changes.','wpp' ) ?>
    <input type="button" value="<?php _e( 'Clear Cache','wpp' ) ?>" id="wpp_clear_cache">
  </div>

  <div class="wpp_settings_block"><?php echo sprintf(__( 'Set all %2$s to same %1$s type:','wpp' ), WPP_F::property_label('singular'), WPP_F::property_label('plural')); ?>
    <select id="wpp_ajax_max_set_property_type_type">
    <?php foreach( $wp_properties[ 'property_types' ] as $p_slug => $p_label ) { ?>
    <option value="<?php echo $p_slug; ?>"><?php echo $p_label; ?></option>
    <?php } ?>
    <input type="button" value="<?php _e( 'Set','wpp' ) ?>" id="wpp_ajax_max_set_property_type">
    <pre id="wpp_ajax_max_set_property_type_result" class="wpp_class_pre hidden"></pre>
  </div>

  <div class="wpp_settings_block">
    <?php if( function_exists( 'memory_get_usage' ) ): ?>
    <?php _e( 'Memory Usage:', 'wpp' ); ?> <?php echo round( ( memory_get_usage() / 1048576 ), 2 ); ?> megabytes.
    <?php endif; ?>
    <?php if( function_exists( 'memory_get_peak_usage' ) ): ?>
    <?php _e( 'Peak Memory Usage:', 'wpp' ); ?> <?php echo round( ( memory_get_peak_usage() / 1048576 ), 2 ); ?> megabytes.
    <?php endif; ?>
  </div>

  <?php do_action( 'wpp_settings_help_tab' ); ?>
</div>