<?php
/**
 * Settings Page Section - Main Options
 *
 * @version 1.37.0
 * @package WP-Property
 * @author  team@UD
 * @copyright 2010-2012 Usability Dyanmics, Inc.
 */

//** Required globals */
global $wp_properties;

/** Check if custom css exists */
if ( file_exists( STYLESHEETPATH . '/wp_properties.css' ) || file_exists( TEMPLATEPATH . '/wp_properties.css' ) ) {
  $using_custom_css = true;
}
?>
<script type="text/javascript">
  jQuery(document).ready(function(){
    wpp_setup_default_property_page();

    jQuery( "#wpp_settings_base_slug" ).change( function() {
      wpp_setup_default_property_page();
    });
  });

  /* Modifies UI to reflect Default Property Page selection */
  function wpp_setup_default_property_page() {
    var selection = jQuery( "#wpp_settings_base_slug" ).val();

    /* Default Property Page is dynamic. */
    if( selection == "property" ) {
      jQuery( ".wpp_non_property_page_settings" ).hide();
      jQuery( ".wpp_non_property_page_settings input[type=checkbox]" ).attr( "checked", false );
      jQuery( ".wpp_non_property_page_settings input[type=checkbox]" ).attr( "disabled", true );
    } else {
      jQuery( ".wpp_non_property_page_settings" ).show();
      jQuery( ".wpp_non_property_page_settings input[type=checkbox]" ).attr( "disabled", false );
    }

  }
 </script>
 <table class="form-table wpp_option_table">

  <tr class="wpp_first">
    <th>
      <strong><?php _e( 'Options', 'wpp' ); ?></strong>
      <div class="description"></div>
    </th>
    <td>
      <ul>
        <li><?php echo WPP_F::checkbox( "name=wpp_settings[configuration][include_in_regular_search_results]&label=" . sprintf( __( 'Include %1s in regular search results.', 'wpp' ), strtolower( WPP_F::property_label( 'plural' ) ) ) , $wp_properties[ 'configuration' ][ 'include_in_regular_search_results' ] ); ?></li>
        <li><?php echo WPP_UD_UI::checkbox( "name=wpp_settings[configuration][enable_post_excerpt]&label=" . sprintf( __( 'Enable excerpts for %1s.', 'wpp' ), strtolower( WPP_F::property_label( 'plural' ) ) ), $wp_properties[ 'configuration' ][ 'enable_post_excerpt' ] ); ?></li>
        <li><?php echo $using_custom_css ? WPP_F::checkbox( "name=wpp_settings[configuration][autoload_css]&label=" . __( 'Load default CSS. If unchecked, the wp-properties.css in your theme folder will not be loaded.','wpp' ), $wp_properties[ 'configuration' ][ 'autoload_css' ] ) : WPP_F::checkbox( "name=wpp_settings[configuration][autoload_css]&label=" . __( 'Load default CSS bundled with WP-Property.','wpp' ), $wp_properties[ 'configuration' ][ 'autoload_css' ] ); ?></li>
        <li><?php echo WPP_F::checkbox( "name=wpp_settings[configuration][completely_hide_hidden_attributes_in_admin_ui]&label=" . sprintf(__( 'Completely hide hidden attributes when editing %1$s.', 'wpp' ), ucfirst(WPP_F::property_label('plural'))), $wp_properties[ 'configuration' ][ 'completely_hide_hidden_attributes_in_admin_ui' ] ); ?></li>
        <li><?php echo sprintf(__( 'Image size for thumbnails displayed on the the backend All %1$s page: ','wpp' ), ucfirst(WPP_F::property_label('plural'))); ?> <?php WPP_F::image_sizes_dropdown( "name=wpp_settings[configuration][admin_ui][overview_table_thumbnail_size]&selected=" . $wp_properties[ 'configuration' ][ 'admin_ui' ][ 'overview_table_thumbnail_size' ] ); ?></li>
        <li>
          <?php _e( 'Default Phone Number','wpp' ); ?>: <?php echo WPP_F::input( "name=phone_number&group=wpp_settings[configuration]&style=width: 200px;", $wp_properties[ 'configuration' ][ 'phone_number' ] ); ?>
          <div class="description"><p><?php printf( __( 'Phone number to use when a %1s-specific phone number is not specified.', 'wpp' ), ucfirst( WPP_F::property_label( 'singular' ) ) ); ?></p></div>
        </li>
      </ul>
    </td>
  </tr>

  <tr>
    <th>
      <strong><?php printf( __( '%1s Overview', 'wpp' ), ucfirst( WPP_F::property_label( 'singular' ) ) ); ?></strong>
      <div class="description"><p><?php _e('This shortcode is used to display search results and custom queries.  It can be inserted anywhere a shortcode can go.', 'wpp'); ?></p></div>
    </th>
    <td>

      <ul>
        <li><?php echo WPP_F::checkbox( 'name=wpp_settings[configuration][property_overview][fancybox_preview]&label=' . __( 'Use Fancybox to enlarge thumbnails to their full size when clicked.','wpp' ) , $wp_properties[ 'configuration' ][ 'property_overview' ][ 'fancybox_preview' ] ); ?></li>
        <li><?php echo WPP_F::checkbox( "name=wpp_settings[configuration][bottom_insert_pagenation]&label=" . __( 'Show pagination on bottom of results.','wpp' ), $wp_properties[ 'configuration' ][ 'bottom_insert_pagenation' ] ); ?></li>
        <li><?php echo WPP_F::checkbox( 'name=wpp_settings[configuration][property_overview][show_children]&label=' . sprintf(__( 'Show children %1$s.','wpp' ), WPP_F::property_label('plural')), $wp_properties[ 'configuration' ][ 'property_overview' ][ 'show_children' ] ); ?></li>
        <li><?php _e( 'Thumbnail size:','wpp' ) ?> <?php WPP_F::image_sizes_dropdown( "name=wpp_settings[configuration][property_overview][thumbnail_size]&selected=" . $wp_properties[ 'configuration' ][ 'property_overview' ][ 'thumbnail_size' ] ); ?></li>
        <li><?php _e( 'Default Sorter Type:','wpp' ) ?> <?php WPP_F::render_dropdown( array( 'buttons' => __( 'Buttons', 'wpp' ), 'dropdown' => __( 'Dropdown', 'wpp' ) ), $wp_properties[ 'configuration' ][ 'property_overview' ][ 'sorter_type' ], array( 'name' => 'wpp_settings[configuration][property_overview][sorter_type]' ) ); ?></li>
      </ul>

      <div class="must_not_have_permalinks description"><?php printf( __( 'You must have permalinks enabled to change the Default %1s page.', 'wpp' ), $wp_properties[ 'labels' ][ 'name' ] ); ?></div>

      <div class="must_have_permalinks">
        <label>
          <?php printf( __( 'Root %1s Page:', 'wpp' ), WPP_F::property_label( 'singular' ) ); ?>
          <select name="wpp_settings[configuration][base_slug]" id="wpp_settings_base_slug">
            <option <?php selected( $wp_properties[ 'configuration' ][ 'base_slug' ], 'property' ); ?> value="property"><?php echo sprintf(__( '%1$s (Default)','wpp' ), ucfirst(WPP_F::property_label('singular'))); ?></option>
            <?php foreach( get_pages() as $page ): ?>
              <option <?php selected( $wp_properties[ 'configuration' ][ 'base_slug' ],$page->post_name ); ?> value="<?php echo $page->post_name; ?>"><?php echo $page->post_title; ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <span wpp_scroll_to="h3.default_property_page" class="wpp_link wpp_toggle_contextual_help"><?php _e( 'What is this?', 'wpp' ); ?></span>
        <div class="description"><?php printf( __( 'Used to display %1s search results, as well as be the base for %1s URLs.', 'wpp' ), WPP_F::property_label( 'singular' ), WPP_F::property_label( 'singular' ) ); ?></div>
      </div>

      <ul class="wpp_non_property_page_settings hidden">
        <li><?php echo WPP_F::checkbox( 'name=wpp_settings[configuration][automatically_insert_overview]&label='. __( 'Automatically overwrite this page\'s content with [property_overview].','wpp' ), $wp_properties[ 'configuration' ][ 'automatically_insert_overview' ] ); ?></li>
        <li class="wpp_wpp_settings_configuration_do_not_override_search_result_page_row <?php if( $wp_properties[ 'configuration' ][ 'automatically_insert_overview' ] == 'true' ) echo " hidden ";?>">
          <?php echo WPP_F::checkbox( "name=wpp_settings[configuration][do_not_override_search_result_page]&label=" . __( 'When showing property search results, don\'t override the page content with [property_overview].', 'wpp' ), $wp_properties[ 'configuration' ][ 'do_not_override_search_result_page' ] ); ?>
          <div class="description"><?php echo sprintf(__( 'If checked, be sure to include [property_overview] somewhere in the content, or no %1$s will be displayed.','wpp' ), ucfirst(WPP_F::property_label('plural'))); ?></div>
        </li>
      </ul>

      <label>
        <?php printf( __( '%1s Listing Template:', 'wpp' ), WPP_F::property_label( 'singular' ) ); ?>
        <select name="wpp_settings[configuration][overview_template]">
          <option> - </option>
          <?php foreach( WPP_F::get_available_theme_templates() as $file_name => $_detail ) { ?>
            <option <?php selected( $wp_properties[ 'configuration' ][ 'overview_template' ], $file_name ); ?> value="<?php echo $file_name; ?>"><?php echo $_detail[ 'name' ]; ?> (<?php echo $file_name; ?>)</option>
          <?php } ?>
        </select>
      </label>

      <div class="description"><?php printf( __( 'The default template can be any WordPress template used for displaying single posts.', 'wpp' ), $wp_properties[ 'labels' ][ 'name' ] ); ?></div>

      <?php do_action( 'wpp_settings_overview_bottom', $wp_properties ); ?>

    </td>
  </tr>

  <tr>
    <th>
      <strong><?php printf( __( 'Single %1s Page', 'wpp' ),WPP_F::property_label( 'singular' ) );  ?></strong>
      <div class="description"><p><?php printf( __('The single %1s page displays a variety of information such as general description, attributes, a map, etc.', 'wpp' ), strtolower( WPP_F::property_label( 'singular' ) ) ); ?></p></div>
    </th>
    <td>

      <ul>
        <li><?php echo WPP_F::checkbox( "name=wpp_settings[configuration][property_overview][sort_stats_by_groups]&label=" .__( 'Display attributes organized by their groups.','wpp' ), $wp_properties[ 'configuration' ][ 'property_overview' ][ 'sort_stats_by_groups' ] ); ?></li>
        <li><?php echo WPP_F::checkbox( "name=wpp_settings[configuration][property_overview][show_true_as_image]&label=". sprintf( __( 'When display attributes, display checkbox icons instead of "%s" and hide "%s" for %s/%s values','wpp' ), __( 'Yes', 'wpp' ),__( 'No', 'wpp' ),__( 'Yes', 'wpp' ),__( 'No', 'wpp' ) ), $wp_properties[ 'configuration' ][ 'property_overview' ][ 'show_true_as_image' ] ); ?></li>
        <?php do_action( 'wpp_settings_page_property_page' );?>
      </ul>

      <label>
        <?php printf( __( 'Single Listing Template', 'wpp' ), WPP_F::property_label( 'singular' ) ); ?>
        <select name="wpp_settings[configuration][single_listing_template]">
          <option> - </option>
          <?php foreach( WPP_F::get_available_theme_templates() as $file_name => $_detail ) { ?>
            <option <?php selected( $wp_properties[ 'configuration' ][ 'single_listing_template' ], $file_name ); ?> value="<?php echo $file_name; ?>"><?php echo $_detail[ 'name' ]; ?> (<?php echo $file_name; ?>)</option>
          <?php } ?>
        </select>
      </label>

      <div class="description"><?php printf( __( 'The default template can be any WordPress template used for displaying single posts.', 'wpp' ), $wp_properties[ 'labels' ][ 'name' ] ); ?></div>
    </td>
  </tr>

  <tr class="wpp_something_advanced_wrapper" data-wpp-feature="physical_locations">
    <th>
      <strong><?php printf( __( '%1s Geolocation','wpp' ), ucfirst( WPP_F::property_label( 'singular' ) ) ); ?></strong>
      <div class="description"><p><?php printf( __( '%1s location information is automatically updated when created or edited.', 'wpp' ), ucfirst( WPP_F::property_label( 'singular' ) ) ); ?></p></div>
    </th>
    <td>
      <ul>
        <li><?php _e( 'Physical addresses is stored in the','wpp' ); ?> <?php echo WPP_F::draw_attribute_dropdown( "name=wpp_settings[configuration][address_attribute]&selected={$wp_properties[ 'configuration' ][ 'address_attribute' ]}" ); ?><?php _e( 'attribute. After geolocation, apply the following format:','wpp' ) ?></li>
        <li><textarea name="wpp_settings[configuration][display_address_format]" style="height: 50px; width: 550px;"><?php echo $wp_properties[ 'configuration' ][ 'display_address_format' ]; ?></textarea></li>
        <li class="description"><?php _e( 'Available location tags:','wpp' ) ?> [street_number] [street_name], [city], [state], [state_code], [county],  [country], [zip_code].</li>
      </li>
      </ul>
    </td>
  </tr>

  <tr>
    <th>
      <strong><?php _e( 'Advanced Options', 'wpp' ); ?></strong>
      <div class="description"><p></p></div>
    </th>
    <td>
      <ul>
        <li><?php echo WPP_F::checkbox( "name=wpp_settings[configuration][do_not_automatically_regenerate_thumbnails]&label=" . __( 'Disable "on-the-fly" image regeneration.', 'wpp' ), $wp_properties[ 'configuration' ][ 'do_not_automatically_regenerate_thumbnails' ] ); ?></li>
        <li><?php echo WPP_F::checkbox( "name=wpp_settings[configuration][load_scripts_everywhere]&label=" . __( 'Load WP-Property scripts on all front-end pages.','wpp' ), $wp_properties[ 'configuration' ][ 'load_scripts_everywhere' ] ); ?></li>

        <?php if( WPP_F::has_theme_specific_stylesheet() ) { ?>
        <li>
          <?php echo WPP_F::checkbox( "name=wpp_settings[configuration][do_not_load_theme_specific_css]&label=" .  __( 'Do not load theme-specific stylesheet.','wpp' ), $wp_properties[ 'configuration' ][ 'do_not_load_theme_specific_css' ] ); ?>
          <div class="description"><?php _e( 'This version of WP-Property has a stylesheet made specifically for the theme you are using.', 'wpp' ); ?></div>
        </li>
        <?php } ?>

        <li>
          <?php  echo WPP_F::checkbox( "name=wpp_settings[configuration][developer_mode]&label=" . __( 'Enable developer mode.','wpp' ), $wp_properties['configuration']['developer_mode'] ); ?>
          <div class="description"><?php _e( 'If you using Google Chrome or have Firefox Firebug, you will see debugging information in the browser console log.','wpp' ); ?></div>
        </li>
        <li>
          <?php  echo WPP_F::checkbox( "name=wpp_settings[configuration][show_ud_log]&label=" . __( 'Show debugging log.','wpp' ), $wp_properties['configuration']['show_ud_log'] ); ?> <br />
          <div class="description"><?php _e( 'The log is always active, but the UI is hidden.  If enabled, it will be visible in the admin sidebar.','wpp' ); ?></div>
        </li>
        <li>
          <?php  echo WPP_F::checkbox( "name=wpp_settings[configuration][disable_automatic_feature_update]&label=" . __( 'Disable automatic feature updates.','wpp' ), $wp_properties['configuration']['disable_automatic_feature_update'] ); ?> <br />
          <div class="description"><?php _e( 'If disabled, feature updates will not be downloaded automatically.','wpp' ); ?></div>
        </li>
        <li>
          <?php  echo WPP_F::checkbox( "name=wpp_settings[configuration][disable_wordpress_postmeta_cache]&label=" . __( 'Disable WordPress update_post_caches() function.','wpp' ), $wp_properties['configuration']['disable_wordpress_postmeta_cache'] ); ?> <br />
          <div class="description"><?php _e( 'This may solve "out of memory" issues if you have a lot of listings.','wpp' ); ?></div>
        </li>

        <li>
          <?php echo WPP_F::checkbox( "name=wpp_settings[configuration][disable_legacy_detailed]&label=" . sprintf(__( 'Disable legacy support for %1$s Meta attributes on frontend','wpp' ), ucfirst( WPP_F::property_label( 'singular' ) ) ), $wp_properties[ 'configuration' ][ 'disable_legacy_detailed' ] ); ?> <br />
          <div class="description"><p><?php printf( __( 'If not checked then it copies %1$s Meta attributes into $wp_properties[\'property_meta\'] for theme support, if needed.', 'wpp' ), ucfirst( WPP_F::property_label( 'singular' ) ) ); ?></p></div>
        </li>
      </ul>

    </td>
  </tr>

  <?php do_action( 'wpp_settings_main_tab_bottom', $wp_properties ); ?>

  <tr class="wpp_last">
    <th>
      <strong><?php _e( 'User Key','wpp' ); ?></strong>
      <div class="description"><p><?php _e( 'Certain premium features require a User Key.','wpp' );?></p></div>
    </th>
    <td>

      <div class="wpp_settings_block">
        <label><?php _e( 'My User Key:','wpp' );?>
        <input class="wpp_ud_customer_key regular-text" type="text" name="ud_customer_key" value="<?php echo esc_attr( get_option( '_ud::customer_key' ) ); ?>" />
        </label>
      </div>

      <div class="description"><?php printf( __( 'For more WP-Property information, visit the Usability Dynamics to <a href="%1s">view help tutorials</a>, participate in the <a href="%1s">community forum</a> and check out the current <a href="%1s">premium features</a>.', 'wpp' ), 'https://usabilitydynamics.com/tutorials/wp-property-help/', 'https://usabilitydynamics.com/forums/', 'https://usabilitydynamics.com/products/wp-property/premium-features/' ); ?></div>

    </td>
  </tr>


</table>