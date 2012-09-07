<?php
/**
 * Settings Page Section - Maps Options
 *
 * @version 1.37.0
 * @package WP-Property
 * @author  team@UD
 * @copyright 2010-2012 Usability Dyanmics, Inc.
 */

//** Required globals */
global $wp_properties;
?>
<script>
  jQuery(document).ready(function(){
    // Bind ( Set ) ColorPicker
    bindColorPicker();
  });
</script>
<table class="form-table wpp_option_table">
  <tr class="wpp_location_related wpp_first">
    <th>
      <strong><?php _e( 'Google Maps','wpp' ) ?></strong>
      <div class="description"></div>
    </th>
    <td>
      <ul>
        <li><?php _e( 'Map thumbnail size','wpp' ) ?><?php WPP_F::image_sizes_dropdown( "name=wpp_settings[configuration][single_property_view][map_image_type]&selected=" . $wp_properties[ 'configuration' ][ 'single_property_view' ][ 'map_image_type' ] ); ?><?php _e( 'and zoom level:','wpp' ) ?> <?php echo WPP_F::input( "name=wpp_settings[configuration][gm_zoom_level]&style=width: 30px;",$wp_properties[ 'configuration' ][ 'gm_zoom_level' ] ); ?></li>
        <li><?php echo WPP_F::checkbox( "name=wpp_settings[configuration][google_maps][show_true_as_image]&label=". sprintf( __( 'Show Checkboxed Image instead of "%s" and hide "%s" for %s/%s values','wpp' ), __( 'Yes', 'wpp' ),__( 'No', 'wpp' ),__( 'Yes', 'wpp' ),__( 'No', 'wpp' ) ), $wp_properties[ 'configuration' ][ 'google_maps' ][ 'show_true_as_image' ] ); ?></li>
      </ul>
      <p><?php echo sprintf(__( 'Attributes to display in popup after a %1$s on a map is clicked.', 'wpp' ), WPP_F::property_label('singular')); ?></p>
      <div class="wp-tab-panel">
        <ul>
          <li><?php echo WPP_F::checkbox( "name=wpp_settings[configuration][google_maps][infobox_settings][show_property_title]&label=" . sprintf(__( 'Show %1$s Title', 'wpp' ), ucfirst(WPP_F::property_label('singular'))), $wp_properties[ 'configuration' ][ 'google_maps' ][ 'infobox_settings' ][ 'show_property_title' ] ); ?></li>

          <?php foreach( $wp_properties[ 'property_stats' ] as $attrib_slug => $attrib_title ): ?>
          <li><?php
          $checked = ( in_array( $attrib_slug, $wp_properties[ 'configuration' ][ 'google_maps' ][ 'infobox_attributes' ] ) ? true : false );
          echo WPP_F::checkbox( "id=google_maps_attributes_{$attrib_title}&name=wpp_settings[configuration][google_maps][infobox_attributes][]&label=$attrib_title&value={$attrib_slug}", $checked );
          ?></li>
          <?php endforeach; ?>

          <li><?php echo WPP_F::checkbox( "name=wpp_settings[configuration][google_maps][infobox_settings][show_direction_link]&label=". __( 'Show Directions Link', 'wpp' ), $wp_properties[ 'configuration' ][ 'google_maps' ][ 'infobox_settings' ][ 'show_direction_link' ] ); ?></li>
          <li><?php echo WPP_F::checkbox( "name=wpp_settings[configuration][google_maps][infobox_settings][do_not_show_child_properties]&label=". sprintf(__( 'Do not show a list of child %1$s in Infobox. ', 'wpp' ), WPP_F::property_label('plural')), $wp_properties[ 'configuration' ][ 'google_maps' ][ 'infobox_settings' ][ 'do_not_show_child_properties' ] ); ?></li>
        </ul>
      </div>
    </td>
  </tr>

  <?php do_action( 'wpp::settings::maps_bottom', $wp_properties ); ?>

</table>