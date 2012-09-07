<?php
  //** Check if this content should be loaded at all */

  if( !$coords = WPP_F::get_coordinates() ) { return; }

  $_property = $property ? (array) $property : (array) $post;


  $_property[ 'map_dom_id' ] = 'property_map_' . rand( 10000,99999 );

  if( !$skip_default_google_map_check && get_post_meta( $_property[ 'ID' ], 'hide_default_google_map', true ) == 'true' ) { return; }

  if( !isset( $map_width ) ) {
    $map_width = '100%';
  }

  if( !isset( $map_height ) ) {
    $map_height = '450px';
  }

  if( !isset( $zoom_level ) ) {
    $zoom_level = ( !empty( $wp_properties[ 'configuration' ][ 'gm_zoom_level' ] ) ? $wp_properties[ 'configuration' ][ 'gm_zoom_level' ] : 13 );
  }

  if( !isset( $zoom_level ) ) {
    $zoom_level = ( !empty( $wp_properties[ 'configuration' ][ 'gm_zoom_level' ] ) ? $wp_properties[ 'configuration' ][ 'gm_zoom_level' ] : 13 );
  }

  if( !isset( $hide_infobox ) ) {
    $hide_infobox = false;
  }

?>

<div class="<?php wpp_css( 'property_map::wrapper', 'property_map_wrapper' ); ?>">
  <div id="<?php echo $_property[ 'map_dom_id' ]; ?>" class="<?php wpp_css( 'property_map::dom_id' ); ?>" style="width:<?php echo $map_width; ?>; height:<?php echo $map_height; ?>"></div>
</div>

<script type='text/javascript'>

  jQuery( document ).ready( function() {

    if( typeof wpp === 'object' && typeof wpp.render_map === 'function' ) {
      wpp.render_map( '#<?php echo $_property[ 'map_dom_id' ]; ?>', {
        infowindow: {
          title: '<?php echo addslashes( $property->post_title ); ?>',
          content: '<?php echo WPP_F::google_maps_infobox( $post ); ?>',
          icon: '<?php echo apply_filters( 'wpp_supermap_marker', '', $post->ID ); ?>'
        }
     });
    }

 });

</script>
