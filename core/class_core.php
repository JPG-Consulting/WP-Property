<?php
/**
 * WP-Property Core Framework
 *
 * @version 1.08
 * @author Usability Dynamics, Inc. <info@usabilitydynamics.com>
 * @package WP-Property
 */


/**
 * WP-Property Core Framework Class
 *
 * Contains primary functions for setting up the framework of the plugin.
 *
 * @version 1.08
 * @package WP-Property
 * @subpackage Main
 */
class WPP_Core {

  /**
   * Highest-level function initialized on plugin load
   *
   * @since 1.11
   */
  function WPP_Core() {
    global $wp_properties;

    //** Determine if memory limit is low and increase it */
    if( ( int ) ini_get( 'memory_limit' ) < 128 ) {
      ini_set( 'memory_limit', '128M' );
    }

    add_action( 'wpp_init', array( 'WPP_F', 'wpp_handle_upgrade' ) );

    //** Load premium features */
    WPP_F::load_premium();

    //** Check if Facebook tries to request site */
    add_action( 'init', array( 'WPP_F', 'check_facebook_tabs' ) );

    //** Hook in upper init */
    add_action( 'init', array( 'WPP_Core',  'init_upper' ), 0 );

    //** Hook in lower init */
    add_action( 'init', array( 'WPP_Core',  'init_lower' ), 100 );

    //** Setup template_redirect */
    add_action( "template_redirect", array( 'WPP_Core',  'template_redirect' ) );

    //** Pre-init action hook */
    do_action( 'wpp_pre_init' );

    //* set WPP capabilities */
    $this->set_capabilities();

    // Check settings data on accord with existing wp_properties data before option updates
    add_filter( 'wpp_settings_save', array( 'WPP_Core', 'check_wp_settings_data' ), 0, 2 );

    WPP_F::wp_enqueue_scripts();

  }


  /**
   * Called on init, as early as possible.
   *
   * @since 1.11
   * @uses $wp_properties WP-Property configuration array
   * @uses $wp_rewrite WordPress rewrite object
   * @access public
   *
   */
  function init_upper() {
    global $wpdb, $wpp_settings, $wp_properties, $wp_rewrite;

    //** Init action hook */
    do_action( 'wpp_init' );

    //** Load languages */
    load_plugin_textdomain( 'wpp', WPP_Path . false, 'wp-property/langs' );

    /** Making template-functions global but load after the premium features, giving the premium features priority. */
    include_once WPP_Templates . '/template-functions.php';

    //** Load settings into $wp_properties and save settings if nonce exists */
    WPP_F::settings_action();

    //** Set up our custom object and taxonomyies */
    WPP_F::register_post_type_and_taxonomies();

    //** Load all widgets and register widget areas */
    add_action( 'widgets_init', array( 'WPP_F', 'widgets_init' ) );

    //** Has to be called everytime, or else the custom slug will not work */
    $wp_rewrite->flush_rules();

    add_filter( 'nav_menu_css_class', array('WPP_F','nav_menu_css_class' ), 5, 3);

    $wpdb->wpp_log = $wpdb->prefix . 'ud_log';

    $wpp_settings[ 'server_capabilities' ] = WPP_F::get_server_capabilities();

    $wpp_settings[ 'primary_keys' ] = array(
      'post_title' => sprintf(__( '%1$s Title', 'wpp' ), ucfirst(WPP_F::property_label('singular'))),
      'post_type' => __( 'Post Type' ),
      'post_content' => sprintf(__( '%1$s Content', 'wpp' ), ucfirst(WPP_F::property_label('singular'))),
      'post_excerpt' => sprintf(__( '%1$s Excerpt', 'wpp' ), ucfirst(WPP_F::property_label('singular'))),
      'post_status' => sprintf(__( '%1$s Status', 'wpp' ), ucfirst(WPP_F::property_label('singular'))),
      'menu_order' => sprintf(__( '%1$s Order', 'wpp' ), ucfirst(WPP_F::property_label('singular'))),
      'post_date' => sprintf(__( '%1$s Date', 'wpp' ), ucfirst(WPP_F::property_label('singular'))),
      'post_author' => sprintf(__( '%1$s Author', 'wpp' ), ucfirst(WPP_F::property_label('singular'))),
      'post_date_gmt' => '',
      'post_parent' => '',
      'ping_status' => '',
      'comment_status' => '',
      'post_password' => ''
   );

   //die( '<pre>' . print_r( $wpp_settings ,true) . '</pre>' );

  }


  /**
   * Secondary WPP Initialization ran towards the end of init()
   *
   * Loads things that we want make accessible for modification via other plugins.
   *
   * @since 1.31.0
   * @uses $wp_properties WP-Property configuration array
   * @uses $wp_rewrite WordPress rewrite object
   * @access public
   *
   */
  function init_lower() {
    global $wp_properties, $wp_rewrite;

    /** Ajax functions */
    add_action( 'wp_ajax_wpp_ajax_max_set_property_type', create_function( "",' die( WPP_F::mass_set_property_type( $_REQUEST["property_type"] ) );' ) );
    add_action( 'wp_ajax_wpp_ajax_property_query', create_function( "",' $class = WPP_F::get_property( trim( $_REQUEST["property_id"] ) ); if( $class ) { echo "WPP_F::get_property() output: \n\n"; print_r( $class ); echo "\nAfter prepare_property_for_display() filter:\n\n"; print_r( prepare_property_for_display( $class ) );  } else { echo __( "No property found.","wpp" ); } die();' ) );
    add_action( 'wp_ajax_wpp_ajax_image_query', create_function( "",' $class = WPP_F::get_property_image_data( $_REQUEST["image_id"] ); if( $class )  print_r( $class ); else echo __( "No image found.","wpp" ); die();' ) );
    add_action( 'wp_ajax_wpp_ajax_check_plugin_updates', create_function( "",'  echo WPP_F::check_plugin_updates(); die();' ) );
    add_action( 'wp_ajax_wpp_ajax_clear_cache', create_function( "",'  echo WPP_F::clear_cache(); die();' ) );
    add_action( 'wp_ajax_wpp_ajax_revalidate_all_addresses', create_function( "",'  echo WPP_F::revalidate_all_addresses(); die();' ) );
    add_action( 'wp_ajax_wpp_ajax_list_table', create_function( "", ' die( WPP_F::list_table() );' ) );
    add_action( 'wp_ajax_wpp_get_ui', create_function( '', ' WPP_F::json_response( WPP_UI::get_ui( $_POST[args] ) ); ' ));
    add_action( 'wp_ajax_wpp_get_log', create_function( '', ' WPP_F::json_response( WPP_F::get_log( $_POST[args] ) ); ' ));

    /** Ajax pagination for property_overview */
    add_action( "wp_ajax_wpp_property_overview_pagination", array( 'WPP_Core',  "ajax_property_overview" ) );
    add_action( "wp_ajax_nopriv_wpp_property_overview_pagination", array( 'WPP_Core',  "ajax_property_overview" ) );

    /** API Service Call via AJAX */
    add_action( 'wp_ajax_wpp_get_api_service', create_function( '', ' WPP_F::json_response( WPP_F::get_service( $_POST[args][service], $_POST[args][get], $_POST[args][args] )); ' ));
    add_action( 'wp_ajax_nopriv_wpp_get_api_service', create_function( '', ' WPP_F::json_response( WPP_F::get_service( $_POST[args][service], $_POST[args][get], $_POST[args][args] )); ' ));

    /** Called in setup_postdata().  We add property values here to make available in global $post variable on frontend */
    add_action( 'the_post', array( 'WPP_F','the_post' ) );

    add_action( "the_content", array( 'WPP_Core' , "the_content" ) );

    add_action( "admin_menu", array( 'WPP_Core', 'admin_menu_all_properties' ) );
    add_action( "admin_menu", array( 'WPP_Core', 'add_admin_pages' ), 100 );

    add_action( "admin_init", array( 'WPP_Core' , "admin_init" ) );

    add_action( "post_submitbox_misc_actions", array( 'WPP_Core', "post_submitbox_misc_actions" ) );
    add_action( 'save_post', array( 'WPP_Core',  'save_property' ), 10, 2 );
    add_action( 'before_delete_post', array( 'WPP_F', 'before_delete_post' ) );
    add_filter( 'post_updated_messages', array( 'WPP_Core', 'property_updated_messages' ), 5 );

    /** Fix toggale row actions -> get rid of "Quick Edit" on property rows */
    add_filter( 'page_row_actions', array( 'WPP_Core', 'property_row_actions' ),0,2 );

    /** Disables meta cache for property obejcts if enabled */
    add_action( 'pre_get_posts', array( 'WPP_F', 'pre_get_posts' ) );

    /** Fix 404 errors */
    add_filter( "parse_request", array( 'WPP_Core',  "parse_request" ) );

    //** Determines if current request is for a child property */
    add_filter( "posts_results", array( 'WPP_F', "posts_results" ) );

    //** Hack. Used to avoid issues of some WPP capabilities */
    add_filter( 'current_screen', array( 'WPP_Core',  'current_screen' ) );

    //** Check premium feature availability */
    add_action( 'wpp_premium_feature_check', array( 'WPP_F', 'feature_check' ) );

    //** Contextual Help */
    add_action( 'wpproperty_contextual_help', array( 'WPP_Core',  'wpp_contextual_help' ) );

    //** check for unsolved standard_attributes and display admin notice */
    add_action('admin_notices', array( 'WPP_F', 'standard_attributes_notice'));

    //** Page loading handlers */
    add_action( 'load-property_page_all_properties', array( 'WPP_F', 'property_page_all_properties_load' ) );
    add_action( 'load-property_page_property_settings', array( 'WPP_F', 'property_page_property_settings_load' ) );

    add_filter( "manage_property_page_all_properties_columns", array( 'WPP_F', 'overview_columns' ) );
    add_filter( "wpp_overview_columns", array( 'WPP_F', 'custom_attribute_columns' ) );
    add_filter( "wpp_attribute_filter", array( 'WPP_F', 'attribute_filter' ), 10, 2 );
    add_action( 'admin_enqueue_scripts', array( 'WPP_F', 'admin_enqueue_scripts' ) );

    //** Modify admin body class */
    add_filter( 'admin_body_class', array( 'WPP_Core', 'admin_body_class' ), 5 );

    /** Shortcodes: Single Listing */
    WPP_F::add_shortcodes();

    //** Make Property Featured Via AJAX */
    if( isset( $_REQUEST[ '_wpnonce' ] ) && wp_verify_nonce( $_REQUEST[ '_wpnonce' ], "wpp_make_featured_" . $_REQUEST[ 'post_id' ] ) ) {
      add_action( 'wp_ajax_wpp_make_featured', create_function( "",' die( json_encode( WPP_F::toggle_featured( $_REQUEST[post_id] ) ) );' ) );
    }

    //** Add custom image sizes */
    foreach( (array) $wp_properties[ 'image_sizes' ] as $image_name => $image_sizes ) {
      add_image_size( $image_name, $image_sizes[ 'width' ], $image_sizes[ 'height' ], true );
    }

    //** Post-init action hook */
    do_action( 'wpp_post_init' );

  }


  /**
   * Sets up additional pages and loads their scripts
   *
   * @since 0.5
   */
  function admin_menu_all_properties() {
    global $wp_properties, $submenu;

    do_action( 'wpp_admin_menu' );

    $all_properties = add_submenu_page( 'edit.php?post_type=property', $wp_properties[ 'labels' ][ 'all_items' ], $wp_properties[ 'labels' ][ 'all_items' ], 'edit_wpp_properties', 'all_properties', create_function( '','global $wp_properties, $screen_layout_columns; include "ui/page_all_properties.php";' ) );

    /**
     * Next used to add custom submenu page 'All Properties' with Javascript dataTable
     * @author Anton K
     */
    if( !empty( $submenu[ 'edit.php?post_type=property' ] ) ) {

      //** Comment next line if you want to get back old Property list page. */
      array_shift( $submenu[ 'edit.php?post_type=property' ] );

      foreach ( $submenu[ 'edit.php?post_type=property' ] as $key => $page ) {
        if ( $page[2] == 'all_properties' ) {
          unset( $submenu[ 'edit.php?post_type=property' ][ $key ] );
          array_unshift( $submenu[ 'edit.php?post_type=property' ], $page );
        }
      }

    }

  }


  /**
   * Sets up additional pages and loads their scripts
   *
   * @since 0.5
   */
  function add_admin_pages() {
    global $wp_properties, $submenu;


    //** Development. Once complete, replace legacy settings */
    add_submenu_page( 'edit.php?post_type=' . WPP_Object, __( 'Settings','wpp' ), __( 'Settings','wpp' ), 'manage_wpp_settings', 'property_settings', create_function( '', 'global $wp_properties; include "ui/page_settings.php";' ) );

    if( isset( $wp_properties[ 'configuration' ][ 'show_ud_log' ] ) && $wp_properties[ 'configuration' ][ 'show_ud_log' ] == 'true' ) {
      add_submenu_page( 'edit.php?post_type=' . WPP_Object, __( 'Log','wpp' ), __( 'Log','wpp' ), 'manage_wpp_settings', 'wpp_log', create_function( '', 'global $wp_properties; include "ui/page_log.php";' ) );
    }

  }


  /**
   * Modify admin body class on property pages for CSS
   *
   * @return string|$request a modified request to query listings
   * @since 0.5
   *
   */
   function admin_body_class( $content ) {
    global $current_screen;

    if( $current_screen->id == 'edit-' . WPP_Object ) {
      return 'wp-list-table ';
    }

    if( $current_screen->id == WPP_Object ) {
      return 'wpp_property_edit';
    }

   }


  /**
   * Fixed property pages being seen as 404 pages
   *
   * Ran on parse_request;
   *
   * WP handle_404() function decides if current request should be a 404 page
   * Marking the global variable $wp_query->is_search to true makes the function
   * assume that the request is a search.
    *
   * @return string|$request a modified request to query listings
   * @since 0.5
   *
   */
  function parse_request( $query ) {
    global $wp, $wp_query, $wp_properties, $wpdb;

    //** If we don't have permalinks, our base slug is always default */
    if( get_option( 'permalink_structure' ) == '' ) {
      $wp_properties[ 'configuration' ][ 'base_slug' ] = WPP_Object;
    }

    //** If we are displaying search results, we can assume this is the default property page */
    if( is_array( $_REQUEST[ 'wpp_search' ] ) ) {

      if( isset( $_POST[ 'wpp_search' ] ) ) {
        $query = array( 'wpp_search' => $_REQUEST[ 'wpp_search' ] );
        wp_redirect( WPP_F::base_url( $wp_properties[ 'configuration' ][ 'base_slug' ], $query ) );
        die();
      }

      $wp_query->wpp_root_property_page = true;
      $wp_query->wpp_search_page = true;
    }

    //** Determine if this is the Default Property Page */
    if( $wp->request == $wp_properties[ 'configuration' ][ 'base_slug' ] ) {
      $wp_query->wpp_root_property_page = true;
    }

    if( $wp->query_string == "p=" . $wp_properties[ 'configuration' ][ 'base_slug' ] ) {
      $wp_query->wpp_root_property_page = true;
    }

    if( $query->query_vars[ 'name' ] == $wp_properties[ 'configuration' ][ 'base_slug' ] ) {
      $wp_query->wpp_root_property_page = true;
    }

    if( $query->query_vars[ 'pagename' ] == $wp_properties[ 'configuration' ][ 'base_slug' ] ) {
      $wp_query->wpp_root_property_page = true;
    }

    if( $query->query_vars[ 'category_name' ] == $wp_properties[ 'configuration' ][ 'base_slug' ] ) {
      $wp_query->wpp_root_property_page = true;
    }

    //** If this is a the root property page, and the Dynamic Default Property page is used */
    if( $wp_query->wpp_root_property_page && $wp_properties[ 'configuration' ][ 'base_slug' ] == WPP_Object ) {
      $wp_query->wpp_default_property_page = true;

      WPP_F::console_log( 'Overriding default 404 page status.' );

      /** Set to override the 404 status */
      add_action( 'wp', create_function( '', 'status_header( 200 );' ) );

      //** Prevent is_404() in template files from returning true */
      add_action( 'template_redirect', create_function( '', ' global $wp_query; $wp_query->is_404 = false;' ), 0, 10 );
    }

    if( $wp_query->wpp_search_page ) {
      $wpp_pages[] = 'Search Page';
    }

    if( $wp_query->wpp_default_property_page ) {
      $wpp_pages[] = sprintf(__('Default %1$s Page','wpp'), ucfirst( WPP_F::property_label( 'singular' ) ));
    }

    if( $wp_query->wpp_root_property_page ) {
      $wpp_pages[] = sprintf(__('Root %1$s Page','wpp'), ucfirst( WPP_F::property_label( 'singular' ) ));
    }

    if( is_array( $wpp_pages ) ) {
      WPP_F::console_log( 'WPP_F::parse_request() ran, determined that request is for: ' . implode( ', ', $wpp_pages ) );
    }


   }


  /**
   * Modifies post content
   *
   * @since 1.04
   *
   */
  function the_content( $content ) {
    global $post, $wp_properties, $wp_query;

    if( !isset( $wp_query->is_property_overview ) ) {
      return $content;
    }

    //** Handle automatic PO inserting for non-search root page */
    if( !$wp_query->wpp_search_page && $wp_query->wpp_root_property_page && $wp_properties[ 'configuration' ][ 'automatically_insert_overview' ] == 'true' ) {
      WPP_F::console_log( 'Automatically inserted property overview shortcode into page content.' );
      return WPP_Core::shortcode_property_overview();
    }

    //** Handle automatic PO inserting for search pages */
    if( $wp_query->wpp_search_page && $wp_properties[ 'configuration' ][ 'do_not_override_search_result_page' ] != 'true' ) {
      WPP_F::console_log( 'Automatically inserted property overview shortcode into search page content.' );
      return WPP_Core::shortcode_property_overview();
    }

    return $content;
  }


  /**
   * Hooks into save_post function and saves additional property data
   *
   *
   * @todo Add some sort of custom capability so not only admins can make properties as featured. i.e. Agents can make their own properties featured.
   * @since 1.04
   *
   */
  function save_property( $post_id = false, $post = false ) {
    global $wp_rewrite, $wp_properties;

    if( !$post_id ) {
      return false;
    }

    if ( !wp_verify_nonce( $_POST[ '_wpnonce' ],'update-property_' . $post_id ) ) {
      return $post_id;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
      return $post_id;
    }

    if ( $post && wp_is_post_revision( $post ) ) {
      return $post_id;
    }

    //* Delete cache files of search values for search widget's form */
    $directory = WPP_Path . 'cache/searchwidget';

    if( is_dir( $directory ) ) {
      $dir = opendir( $directory );
      while( ( $cachefile = readdir( $dir ) ) ){
        if ( is_file ( $directory."/".$cachefile ) ) {
          unlink ( $directory."/".$cachefile );
        }
      }
    }

    /* get old coordinates */
    $old_lat = get_post_meta( $post_id,'latitude', true );
    $old_lng = get_post_meta( $post_id,'longitude', true );
    $old_coordinates = ( ( empty( $old_lat ) ) || ( empty( $old_lng ) ) ) ? "" : array( 'lat'=>$old_lat,'lng'=>$old_lng );
    $old_location = get_post_meta( $post_id, $wp_properties[ 'configuration' ][ 'address_attribute' ], true );


    /**
     * Cycle through all meta attributes, clean up values, and commit to database. Geolocated attributes are saved again later.
     *
     */

    $update_data = $_REQUEST['wpp_data']['meta'];
    foreach( (array)$update_data as $meta_key => $meta_values ) {

      delete_post_meta( $post_id, $meta_key );

      $attribute_data = WPP_F::get_attribute_data( $meta_key );

      foreach( (array) $meta_values as $count => $value ) {

        if( is_callable( $wp_properties[ '_formatting_callback' ][ $attribute_data['type'] ][ 'system' ] ) ) {
          $value = call_user_func($wp_properties[ '_formatting_callback' ][ $attribute_data['type'] ][ 'system' ], $value);

        }

        add_post_meta( $post_id, $meta_key, $formatted[ $meta_key ][ $count ] = WPP_F::encode_mysql_input( $value, $meta_key ) );

      }

      if ($meta_key==$wp_properties[ 'configuration' ][ 'address_attribute' ]){
        $new_location = $update_data[$wp_properties[ 'configuration' ][ 'address_attribute' ]][$count];
      }

    }




    //** if location was cleared and manual coordinates is not set then we  */
    if (!empty($old_location) && empty($new_location) && $update_data[ 'manual_coordinates' ]!='true'){
      $update_data[ 'latitude' ] = $update_data[ 'longitude' ] = '';
    }

    $latitude = $update_data[ 'latitude' ];
    $longitude = $update_data[ 'longitude' ];
    $coordinates = ( empty( $latitude ) || empty( $longitude ) ) ? "" : array( 'lat'=>$latitude,'lng'=>$longitude );

    /* will be true if address is empty and used manual_coordinates and coordinates is not empty */
    $address_by_coordinates = ( !empty( $coordinates ) && $update_data[ 'manual_coordinates' ]=='true' );

    // Update Coordinates ( skip if old address matches new address ) or if $address_by_coordinates==true , but always do if no coordinates set
    if( ( empty( $coordinates ) ) ||                                        //always do if no coordinates set
        ( empty( $new_location ) && $address_by_coordinates ) ||            //if coordinates are set and use manual and empty address
        ( $old_coordinates != $coordinates && $address_by_coordinates ) ||  //if changed coordinates
        ( $old_location != $new_location && !empty( $new_location ) )       //or changed location
    ) {

      foreach( (array) $wp_properties[ 'geo_type_attributes' ] + array( 'display_address' ) as $meta_key ) {
        delete_post_meta( $post_id, $meta_key );
      }

      /** if is set address we check it*/
      if ( !empty( $new_location ) ){
        $geo_data = WPP_F::geo_locate_address( $new_location, $wp_properties[ 'configuration' ][ 'google_maps_localization' ], true );
      }

      /** if not empty coordinates and used manual_coordinates */
      if ( !empty( $coordinates ) && ($update_data[ 'manual_coordinates' ]=='true' || empty($geo_data) ) ){
        $geo_data_coordinates = WPP_F::geo_locate_address( false, $wp_properties[ 'configuration' ][ 'google_maps_localization' ], true, $coordinates );
      }

      /** if Address was invalid or empty but we have valid $coordinates then we use them */
      if ( empty( $geo_data->formatted_address ) && !empty( $geo_data_coordinates->formatted_address ) ){
        $geo_data = $geo_data_coordinates;
      }

      if( !empty( $geo_data->formatted_address ) ) {
        update_post_meta( $post_id, 'address_is_formatted', true );

        if( !empty( $wp_properties[ 'configuration' ][ 'address_attribute' ] ) ) {
          update_post_meta( $post_id, $wp_properties[ 'configuration' ][ 'address_attribute' ], WPP_F::encode_mysql_input( $geo_data->formatted_address, $wp_properties[ 'configuration' ][ 'address_attribute' ] ) );
        }

        foreach( (array) $geo_data as $geo_type => $this_data ) {
          update_post_meta( $post_id, $geo_type, WPP_F::encode_mysql_input( $this_data, $geo_type ) );
        }

        if ( $address_by_coordinates ){
          update_post_meta( $post_id, $wp_properties[ 'configuration' ][ 'address_attribute' ], WPP_F::encode_mysql_input( $geo_data->formatted_address, $wp_properties[ 'configuration' ][ 'address_attribute' ] ) );
        }

      } else {

        if ($geo_data->status=='OVER_QUERY_LIMIT' || $geo_data->status=="REQUEST_DENIED"){
          //** Do nothing */
        }else{
          // Try to figure out why it failed
          update_post_meta($post_id, 'address_is_formatted', false);
        }

      }
    }

    if( $geo_data->status == 'OVER_QUERY_LIMIT' ) {
      //** Could add some sort of user notification that over limit */
    }

    /**
     * Write any data to children properties that are supposed to inherit things
     *
     * Determine child property_type
     * Check if child's property type has inheritence rules, and if meta_key exists in inheritance array
     *
     */
    foreach( (array) get_children("post_parent={$post_id}&post_type=property") as $child_id => $child_data ) {
      foreach( (array) $wp_properties[ 'property_inheritance' ][ get_post_meta( $child_id, 'property_type', true ) ] as $i_meta_key ) {
        update_post_meta( $child_id, $i_meta_key, get_post_meta( $post_id, $i_meta_key, true ) );
      }
    }

    WPP_F::maybe_set_gpid( $post_id );

    if( $_REQUEST[ 'parent_id' ] ) {
      update_post_meta( $post_id, 'parent_gpid', WPP_F::maybe_set_gpid( $_REQUEST[ 'parent_id' ] ) );
    }

    do_action( 'save_property',$post_id, $_REQUEST, $geo_data );

    $wp_rewrite->flush_rules();

    return true;
  }


  /**
   * Inserts content into the "Publish" metabox on property pages
   *
   * @since 1.04
   *
   */
  function post_submitbox_misc_actions() {
    global $post, $action;

    if( $post->post_type == WPP_Object ) {

      ?>
      <div class="misc-pub-section ">

      <ul>
        <li><?php _e( 'Menu Sort Order:','wpp' )?> <?php echo WPP_F::input( "name=menu_order&special=size=4", $post->menu_order ); ?></li>

        <?php if( current_user_can( 'manage_options' ) && $wp_properties[ 'configuration' ][ 'do_not_use' ][ 'featured' ] != 'true' ) { ?>
        <li><?php echo WPP_F::checkbox( "name=wpp_data[meta][featured]&label=" . __( 'Display in featured listings.','wpp' ), get_post_meta( $post->ID, 'featured', true ) ); ?></li>
        <?php } ?>

        <?php do_action( 'wpp_publish_box_options', $post ); ?>
      </ul>

      </div>
      <?php

    }

    return;

  }


  /**
   * Removes "quick edit" link on property type objects
   *
   * Called in via page_row_actions filter
   *
   * @since 0.5
   *
   */
    function property_row_actions( $actions, $post ) {

      if( $post->post_type != WPP_Object ) {
          return $actions;
      }

      unset( $actions[ 'inline' ] );

      return $actions;
    }


  /**
   * Adds property-relevant messages to the property post type object
   *
   *
   * @since 0.5
   *
   */
  function property_updated_messages( $messages ) {
    global $post_id, $post;

    $messages[ WPP_Object ] = array(
      0 => '', // Unused. Messages start at index 1.
      1 => sprintf( __( '%2$s updated. <a href="%1$s">view %2$s</a>','wpp' ), esc_url( get_permalink( $post_id ) ),ucfirst( WPP_F::property_label( 'singular' )) ),
      2 => __( 'Custom field updated.','wpp' ),
      3 => __( 'Custom field deleted.','wpp' ),
      4 => sprintf( __( '%1$s updated.','wpp' ),ucfirst( WPP_F::property_label( 'singular' )) ),
      5 => isset( $_GET[ 'revision' ] ) ? sprintf( __( '%1$s restored to revision from %s','wpp' ), wp_post_revision_title( ( int ) $_GET[ 'revision' ], false ),ucfirst( WPP_F::property_label( 'singular' )) ) : false,
      6 => sprintf( __( '%2$s published. <a href="%1$s">View %2$s</a>','wpp' ), esc_url( get_permalink( $post_id ) ),ucfirst( WPP_F::property_label( 'singular' )) ),
      7 => sprintf( __( '%1$s saved.','wpp' ),ucfirst( WPP_F::property_label( 'singular' )) ),
      8 => sprintf( __( '%1$s submitted. <a target="_blank" href="%1$s">Preview %2$s</a>','wpp' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_id ) ) ),WPP_F::property_label( 'singular' ) ),
      9 => sprintf( __( '%3$s scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview %4$s</a>','wpp' ), date_i18n( __( 'M j, Y @ G:i','wpp' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_id ) ),ucfirst( WPP_F::property_label( 'singular' ) ), WPP_F::property_label( 'singular' ) ),
      10 => sprintf( __( '%2$s draft updated. <a target="_blank" href="%1$s">Preview %2$s</a>','wpp' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_id ) ) ),ucfirst( WPP_F::property_label( 'singular' ) ) ),
    );

    $messages = apply_filters( 'wpp_updated_messages', $messages );

    return $messages;
  }


  /**
   * Performs front-end pre-header functionality
   *
   * This function is not called on amdin side
   * Loads conditional CSS styles
   *
   * @since 1.11
   */
  function template_redirect() {
    global $post, $property, $wp_query, $wp_properties, $wp_styles, $wpp_query, $wp_taxonomies;

    //** Modify Front-end property body class */
    add_filter( 'body_class', array( 'WPP_F', 'properties_body_class' ) );

    add_action( 'wp_footer' , array( 'WPP_F', 'wp_footer' ), 100 );

    add_action( 'get_template_part_content', array( 'WPP_F', 'get_template_part_handler' ), 10, 2 );

    if( $wp_properties[ 'configuration' ][ 'do_not_enable_text_widget_shortcodes' ] != 'true' ) {
      add_filter( 'widget_text', 'do_shortcode' );
    }

    do_action( 'wpp_template_redirect' );

    //** Handle single property page previews */
    if ( !empty( $wp_query->query_vars[ 'preview' ] ) && $post->post_type == "property" && $post->post_status == "publish" ) {
      die( wp_redirect( get_permalink( $post->ID ) ) );
    }

    //** Load scripts and styles if option is enabled to load them globally */
    if( $wp_properties[ 'configuration' ][ 'load_scripts_everywhere' ] == 'true' ) {
      add_action( 'wp_enqueue_scripts', array( 'WPP_F', 'wp_enqueue_scripts' ) );
    }

    if( count( $wp_query->posts ) < 2 && ( $post->post_type == "property" || $wp_query->is_child_property ) ) {
      $wp_query->single_property_page = true;

      //** This is a hack and should be done better */
      if( !$post ) {
        $post = get_post( $wp_query->queried_object_id );
        $wp_query->posts[0] = $post;
        $wp_query->post = $post;
      }
    }

    //** Monitor taxonomy archive queries */
    if( is_tax() && in_array( $wp_query->query_vars[ 'taxonomy' ], array_keys( $wp_taxonomies ) ) ) {
      //** Once get_properties(); can accept taxonomy searches, we can inject a search request in here */
    }

    //** If viewing root property page that is the default dynamic page. */
    if( $wp_query->wpp_default_property_page ) {
      $wp_query->is_property_overview = true;
    }

    //** If this is the root page with a manually inserted shortcode, or any page with a PO shortcode */
    if( strpos( $post->post_content, 'property_overview' ) ) {
      $wp_query->is_property_overview = true;
    }

    //** If this is the root page and the shortcode is automatically inserted */
    if( $wp_query->wpp_root_property_page && $wp_properties[ 'configuration' ][ 'automatically_insert_overview' ] == 'true' ) {
      $wp_query->is_property_overview = true;
    }

    //** If search result page, and system not explicitly configured to not include PO on search result page automatically */
    if( $wp_query->wpp_search_page && $wp_properties[ 'configuration' ][ 'do_not_override_search_result_page' ] != 'true' ) {
      $wp_query->is_property_overview = true;
    }

    //** Scripts and styles to load on all overview and signle listing pages */
    if ( $wp_query->single_property_page || $wp_query->is_property_overview ) {
      add_action( 'wp_enqueue_scripts', array( 'WPP_F', 'wp_enqueue_scripts' ) );
    }

    do_action( 'wpp_template_redirect_post_scripts' );


    /**
     * Scripts loaded only on single property pages
     *
     */
    if ( $wp_query->single_property_page && !post_password_required( $post ) ) {

      WPP_F::console_log( 'Including scripts for all single property pages.' );

      add_action( 'wp_enqueue_scripts', array( 'WPP_F', 'wp_enqueue_scripts' ) );

      do_action( 'template_redirect_single_property' );

      $property = WPP_F::get_property( $post->ID, "load_gallery=true" );

      $property = prepare_property_for_display( $property );

      $type = $property[ 'property_type' ];

      //** Make certain variables available to be used within the single listing page */
      $single_page_vars = apply_filters( 'wpp_property_page_vars', array(
        'property' => $property,
        'wp_properties' => $wp_properties
      ) );

      //** By merging our extra variables into $wp_query->query_vars they will be extracted in load_template() */
      if( is_array( $single_page_vars ) ) {
        $wp_query->query_vars = array_merge( $wp_query->query_vars, $single_page_vars );
      }

      $template_found = WPP_F::get_template_part( array(
        "property-{$type}",
        "property",
      ), array( WPP_Templates ) );

      //** Load the first found template */
      if( $template_found ) {
        WPP_F::console_log( 'Found single property page template:' . $template_found );
        die( load_template( $template_found ) );
      }

    }


    /**
     * Current requests includes a property overview.  PO may be via shortcode, search result, or due to this being the Default Dynamic Property page
     *
     */
    if( $wp_query->is_property_overview ) {

      //** By merging our extra variables into $wp_query->query_vars they will be extracted in load_template() */
      $wp_query->query_vars = array_merge( $wp_query->query_vars, (array) apply_filters( 'wpp_overview_page_vars', array(
        'wp_properties' => $wp_properties,
        'wpp_query' => $wpp_query
      )));

      do_action( 'template_redirect_property_overview' );

      //** If using Dynamic Property Root page, we must load a template */
      if( $wp_query->wpp_default_property_page ) {

        $template_found = WPP_F::get_template_part( array(
          $wp_properties[ 'configuration' ][ 'overview_template' ],
          'page.php',
          'single.php'
        ), array( WPP_Templates ) );

        //** Load the first found template */
        if( $template_found ) {

          WPP_F::console_log( 'Found Default property overview page template:' . $template_found );

          $post = (object) array_merge( WPP_F::setup_default_property_page( array( 'return_defaults' => true ) ), array( 'ID' => '99999' ) );

          //** Create a fake WP Listings / Search Page */
          $wp_query->is_404 = false;
          $wp_query->post_count = 1;
          $wp_query->posts[0] = $post;

          die( load_template( $template_found ) );
        }

      }

    }

  }


  /**
   * Runs pre-header functions on admin-side only
   *
   * Checks if plugin has been updated.
   *
   * @since 1.10
   *
   */
  function admin_init() {
    global $wp_rewrite, $wp_properties, $post;

    WPP_F::fix_screen_options();

    add_filter( 'plugin_action_links', array( 'WPP_F', 'plugin_action_links' ), 10, 2 );

    //** Print  admin JavaScript */
    add_action( 'admin_print_footer_scripts', array( 'WPP_F', 'admin_print_footer_scripts' ) );

    //* Adds metabox 'General Information' to Property Edit Page */
    add_meta_box( 'wpp_property_meta', __( 'General Information','wpp' ), array( 'WPP_UI','metabox_meta' ), WPP_Object, 'normal', 'high' );

    //* Adds 'Group' metaboxes to Property Edit Page */
    foreach( ( array ) $wp_properties[ 'property_groups' ] as $slug => $group ) {
      //* There is no sense to add metabox if no one attribute assigned to group */
      if( !in_array( $slug, $wp_properties[ 'property_stats_groups' ] ) ) {
        continue;
      }

      //* Determine if Group name is empty we add 'NO NAME', other way metabox will not be added */
      if( empty( $group[ 'name' ] ) ) {
        $group[ 'name' ] = __( 'NO NAME','wpp' );
      }

      add_meta_box( $slug , __( $group[ 'name' ],'wpp' ), array( 'WPP_UI', 'metabox_meta' ), WPP_Object, 'normal', 'high', array( 'group' => $slug ) );
    }

    add_meta_box( 'propetry_filter',  $wp_properties[ 'labels' ][ 'name' ] . ' ' . __( 'Search','wpp' ), array( 'WPP_UI','metabox_property_filter' ), 'property_page_all_properties', 'normal' );

    // Add metaboxes
    do_action( 'wpp_metaboxes' );

    WPP_F::manual_activation();

    //** Output WP-Property backup of configuration */
    if( $_GET[ 'page' ] == 'property_settings' && $_GET[ 'wpp_action' ] == 'download-wpp-backup' && wp_verify_nonce( $_REQUEST[ '_wpnonce' ], 'download-wpp-backup' ) ) {

      header( 'Cache-Control: public' );
      header( 'Content-Description: File Transfer' );
      header( 'Content-Transfer-Encoding: binary' );

      if( $_GET[ 'type' ] == 'data_structure' ) {
        $_data = json_encode( WPP_F::get_data_structure() );
      } else {
        $_data = json_encode( $wp_properties );
      }

      if( $_GET[ 'format' ] == 'xml' ) {
        header( 'Content-Disposition: attachment; filename=' . sanitize_key( 'wpp-' . get_bloginfo( 'name' ) ) . '-' . date( 'y-m-d' ) . '.xml' );
        header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );
        die( WPP_F::json_to_xml( $_data, array( 'root_name' => 'wp_property_configuration' ) ) );
      } else {
        header( 'Content-Disposition: attachment; filename=' . sanitize_key( 'wpp-' . get_bloginfo( 'name' ) ) . '-' . date( 'y-m-d' ) . '.json' );
        header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
        die( $_data );
      }

    }

    //** Clear Logs table: remove all 'wpp' logs. */
    if( $_GET[ 'page' ] == 'wpp_log' && $_GET[ 'wpp_action' ] == 'clear' && wp_verify_nonce( $_REQUEST[ '_wpnonce' ], 'wpp_clear_log' ) ) {
      WPP_F::clear_log();
      wp_redirect( WPP_F::current_url( false, array( 'wpp_action', '_wpnonce' ) ) );
      die();
    }

  }


  /**
   * Displays featured properties
   *
   * Performs searching/filtering functions, provides template with $properties file
   * Retirms html content to be displayed after location attribute on property edit page
   *
   * @todo Consider making this function depend on shortcode_property_overview() more so pagination and sorting functions work.
   *
   * @since 0.60
   * @param string $listing_id Listing ID must be passed
   *
   * @uses WPP_F::get_properties()
   *
   */
  function shortcode_featured_properties( $atts = false ) {
    global $wp_properties, $wpp_query, $post;

    $default_property_type = WPP_F::get_most_common_property_type();

    if( !$atts ) {
      $atts = array();
    }

    $defaults = array(
      'property_type' => '',
      'type' => '',
      'class' => 'shortcode_featured_properties',
      'per_page' => '6',
      'sorter_type' => 'none',
      'show_children' => 'false',
      'hide_count' => true,
      'fancybox_preview' => 'false',
      'bottom_pagination_flag' => 'false',
      'pagination' => 'off',
      'stats' => '',
      'image_type' => 'thumbnail',
      'thumbnail_size' => 'thumbnail'
    );

    $args = array_merge( $defaults, $atts );

    //** Using "image_type" is obsolete */
    if( empty( $args[ 'thumbnail_size' ] ) &&  !empty( $args[ 'image_type' ] ) ) {
      $args[ 'thumbnail_size' ] = $args[ 'image_type' ];
    }

    //** Using "type" is obsolete. If property_type is not set, but type is, we set property_type from type */
    if( !empty( $args[ 'type' ] ) && empty( $args[ 'property_type' ] ) ) {
      $args[ 'property_type' ] = $args[ 'type' ];
    }

    if( empty( $args[ 'property_type' ] ) ) {
      $args[ 'property_type' ] = $default_property_type;
    }

    // Convert shortcode multi-property-type string to array
    if( !empty( $args[ 'stats' ] ) ) {

      if( strpos( $args[ 'stats' ], "," ) ) {
        $args[ 'stats' ] = explode( ",", $args[ 'stats' ] );
      }

      if( !is_array( $args[ 'stats' ] ) ) {
        $args[ 'stats' ] = array( $args[ 'stats' ] );
      }

      foreach( (array) $args[ 'stats' ] as $key => $stat ) {
        $args[ 'stats' ][$key] = trim( $stat );
      }

    }

    $args[ 'thumbnail_size' ] = $args[ 'image_type' ];
    $args[ 'disable_wrapper' ] = 'true';
    $args[ 'featured' ] = 'true';
    $args[ 'template' ] = 'featured-shortcode';

    unset( $args[ 'image_type' ] );
    unset( $args[ 'type' ] );

    $result = WPP_Core::shortcode_property_overview( $args );

    return $result;
  }


  /**
   * Returns the property search widget
   *
   *
    * @since 1.04
   *
   */
  function shortcode_property_search( $atts = "" )  {
    global $post, $wp_properties;

    extract( shortcode_atts( array(
      'searchable_attributes' => '',
      'searchable_property_types' => '',
      'pagination' => 'on',
      'group_attributes' => 'off',
      'per_page' => '10'
    ),$atts ) );

    if( empty( $searchable_attributes ) ) {

      //** get first 3 attributes to prevent people from accidentally loading them all ( long query ) */
      $searchable_attributes = array_slice( $wp_properties[ 'searchable_attributes' ], 0, 5 );

    } else {
      $searchable_attributes = explode( ",", $searchable_attributes );
    }

    $searchable_attributes = array_unique( $searchable_attributes );

    if( empty( $searchable_property_types ) ) {
      $searchable_property_types = $wp_properties[ 'searchable_property_types' ];
    } else {
      $searchable_property_types = explode( ",", $searchable_property_types );
    }

    $widget_id = $post->ID . "_search";

    ob_start();
    echo '<div class="wpp_shortcode_search">';

    $search_args[ 'searchable_attributes' ] = $searchable_attributes;
    $search_args[ 'searchable_property_types' ] = $searchable_property_types;
    $search_args[ 'group_attributes' ] = ( $group_attributes == 'on' || $group_attributes == 'true'  ? true : false );
    $search_args[ 'per_page' ] = $per_page;
    $search_args[ 'pagination' ] = $pagination;
    $search_args[ 'instance_id' ] = $widget_id;

    draw_property_search_form( $search_args );

    echo "</div>";
    $content = ob_get_contents();
    ob_end_clean();

    return $content;


  }


  /**
     * Displays property overview
     *
     * Performs searching/filtering functions, provides template with $properties file
     * Retirms html content to be displayed after location attribute on property edit page
     *
     * @since 1.081
     * @param string $listing_id Listing ID must be passed
     * @return string $result
     *
     * @uses WPP_F::get_properties()
     *
     */
  function shortcode_property_overview( $atts = "" )  {
    global $wp_properties, $wpp_query, $property, $post, $wp_query;

    WPP_F::wp_enqueue_script( 'jquery-ui-widget' );
    WPP_F::wp_enqueue_script( 'jquery-ui-mouse' );
    WPP_F::wp_enqueue_script( 'jquery-ui-slider' );
    WPP_F::wp_enqueue_script( 'jquery-address' );
    WPP_F::wp_enqueue_script( 'jquery-scrollTo' );
    WPP_F::wp_enqueue_script( 'jquery-fancybox' );
    WPP_F::wp_enqueue_script( 'wp-property-frontend' );

    //** This needs to be done because a key has to exist in the $deafult array for shortcode_atts() to load passed value */
    foreach( WPP_F::get_queryable_keys() as $key ) {
      $queryable_keys[$key] = false;
    }

    //** Allow the shorthand of "type" as long as there is not a custom attribute of "type". If "type" does exist as an attribute, then users need to use the full "property_type" query tag. **/
    if ( !array_key_exists( 'type', $queryable_keys ) && ( is_array( $atts ) && array_key_exists( 'type', $atts ) ) ) {
      $atts[ 'property_type' ] = $atts[ 'type' ];
      unset( $atts[ 'type' ] );
    }

    //** Get ALL allowed attributes that may be passed via shortcode ( to include property attributes ) */
    $defaults[ 'child_properties_title' ] = __( 'Floor plans at location:','wpp' );
    $defaults[ 'per_page' ] =  get_option( 'posts_per_page' ) ? get_option( 'posts_per_page' ) : 10;

    $defaults[ 'fancybox_preview' ] = $wp_properties[ 'configuration' ][ 'property_overview' ][ 'fancybox_preview' ];
    $defaults[ 'thumbnail_size' ] = $wp_properties[ 'configuration' ][ 'property_overview' ][ 'thumbnail_size' ];

    $defaults[ 'show_children' ] = ( isset( $wp_properties[ 'configuration' ][ 'property_overview' ][ 'show_children' ] ) ? $wp_properties[ 'configuration' ][ 'property_overview' ][ 'show_children' ] : 'true' );
    $defaults[ 'bottom_pagination_flag' ] = ( $wp_properties[ 'configuration' ][ 'bottom_insert_pagenation' ] == 'true' ? true : false );
    $defaults[ 'sorter_type' ] = $wp_properties[ 'configuration' ][ 'property_overview' ][ 'sorter_type' ] ? $wp_properties[ 'configuration' ][ 'property_overview' ][ 'sorter_type' ] : 'buttons';

    $defaults[ 'sort_by_text' ] = __( 'Sort By:', 'wpp' );
    $defaults[ 'sort_by' ] = 'menu_order';
    $defaults[ 'sort_order' ] = 'ASC';
    $defaults[ 'template' ] = false;
    $defaults[ 'ajax_call' ] = false;
    $defaults[ 'disable_wrapper' ] = false;
    $defaults[ 'pagination' ] = 'on';
    $defaults[ 'hide_count' ] = false;
    $defaults[ 'starting_row' ] = 0;
    $defaults[ 'unique_hash' ] = rand( 10000,99900 );
    $defaults[ 'detail_button' ] = false;
    $defaults[ 'stats' ] = '';
    $defaults[ 'class' ] = 'wpp_property_overview_shortcode';
    $defaults[ 'in_new_window' ] = false;

    $defaults = apply_filters( 'shortcode_property_overview_allowed_args', $defaults, $atts );

    if( $atts[ 'ajax_call' ] ) {
      //** If AJAX call then the passed args have all the data we need */
      $wpp_query = $atts;

      //* Fix ajax data. Boolean value false is returned as string 'false'. */
      foreach( (array) $wpp_query as $key => $value ) {
        if( $value == 'false' ) {
          $wpp_query[$key] = false;
        }
      }

      $wpp_query[ 'ajax_call' ] =  true;

      //** Everything stays the same except for sort order and page */
      $wpp_query[ 'starting_row' ]  =  ( ( $wpp_query[ 'requested_page' ] - 1 ) * $wpp_query[ 'per_page' ] );

      //** Figure out current page */
      $wpp_query[ 'current_page' ] =  $wpp_query[ 'requested_page' ];

    } else {

      //** Merge defaults with passed arguments */
      $wpp_query = shortcode_atts( $defaults, $atts );
      $wpp_query[ 'query' ] = shortcode_atts( $queryable_keys, $atts );

      //** Handle search */
      if( $wpp_search = $_REQUEST[ 'wpp_search' ] ) {
        $wpp_query[ 'query' ] = shortcode_atts( $wpp_query[ 'query' ], $wpp_search );
        $wpp_query[ 'query' ] = WPP_F::prepare_search_attributes( $wpp_query[ 'query' ] );

        if( isset( $_REQUEST[ 'wpp_search' ][ 'sort_by' ] ) ) {
          $wpp_query[ 'sort_by' ] = $_REQUEST[ 'wpp_search' ][ 'sort_by' ];
        }

        if( isset( $_REQUEST[ 'wpp_search' ][ 'sort_order' ] ) ) {
          $wpp_query[ 'sort_order' ] = $_REQUEST[ 'wpp_search' ][ 'sort_order' ];
        }

        if( isset( $_REQUEST[ 'wpp_search' ][ 'pagination' ] ) ) {
          $wpp_query[ 'pagination' ] = $_REQUEST[ 'wpp_search' ][ 'pagination' ];
        }

        if( isset( $_REQUEST[ 'wpp_search' ][ 'per_page' ] ) ) {
          $wpp_query[ 'per_page' ] = $_REQUEST[ 'wpp_search' ][ 'per_page' ];
        }
      }

    }

    //** Load certain settings into query for get_properties() to use */
    $wpp_query[ 'query' ][ 'sort_by' ] = $wpp_query[ 'sort_by' ];
    $wpp_query[ 'query' ][ 'sort_order' ] = $wpp_query[ 'sort_order' ];

    $wpp_query[ 'query' ][ 'pagi' ] = $wpp_query[ 'starting_row' ] . '--' . $wpp_query[ 'per_page' ];

    if( !isset( $wpp_query[ 'current_page' ] ) ) {
      $wpp_query[ 'current_page' ] =  ( $wpp_query[ 'starting_row' ] / $wpp_query[ 'per_page' ] ) + 1;
    }

    //** Load settings that are not passed via shortcode atts */
    $wpp_query[ 'sortable_attrs' ] = WPP_F::get_sortable_keys();

    //** Detect currently property for conditional in-shortcode usage that will be replaced from values */
    if( isset( $post ) ) {

      $dynamic_fields[ 'post_id' ] = $post->ID;
      $dynamic_fields[ 'post_parent' ] = $post->parent_id;
      $dynamic_fields[ 'property_type' ] = $post->property_type;

      $dynamic_fields = apply_filters( 'shortcode_property_overview_dynamic_fields', $dynamic_fields );

      if( is_array( $dynamic_fields ) ) {
        foreach( (array) $wpp_query[ 'query' ] as $query_key => $query_value ) {
          if( !empty( $dynamic_fields[$query_value] ) ) {
            $wpp_query[ 'query' ][$query_key] = $dynamic_fields[$query_value];
          }
        }
      }
    }

    //** Remove all blank values */
    $wpp_query[ 'query' ] = array_filter( $wpp_query[ 'query' ] );

    //** Unset this because it gets passed with query ( for back-button support ) but not used by get_properties() */
    unset( $wpp_query[ 'query' ][ 'per_page' ] );
    unset( $wpp_query[ 'query' ][ 'pagination' ] );
    unset( $wpp_query[ 'query' ][ 'requested_page' ] );

    //** Load the results */
    $wpp_query[ 'properties' ] = WPP_F::get_properties( $wpp_query[ 'query' ], true );

    //** Calculate number of pages */
    if( $wpp_query[ 'pagination' ] == 'on' ) {
      $wpp_query[ 'pages' ] = ceil( $wpp_query[ 'properties' ][ 'total' ] / $wpp_query[ 'per_page' ] );
    }

    //** Set for quick access ( for templates */
    $property_type = $wpp_query[ 'query' ][ 'property_type' ];

    if ( !empty( $property_type ) ){
      foreach ( ( array )$wp_properties[ 'hidden_attributes' ][$property_type] as $attr_key ){
        unset( $wpp_query[ 'sortable_attrs' ][$attr_key] );
      }
    }

    //** Legacy Support - include variables so old templates still work */
    $properties = $wpp_query[ 'properties' ][ 'results' ];
    $thumbnail_sizes = WPP_F::image_sizes( $wpp_query[ 'thumbnail_size' ] );
    $child_properties_title = $wpp_query[ 'child_properties_title' ];
    $unique = $wpp_query[ 'unique_hash' ];
    $thumbnail_size = $wpp_query[ 'thumbnail_size' ];

    //* Debugger */
    if( $wp_properties[ 'configuration' ][ 'developer_mode' ] == 'true' && !$wpp_query[ 'ajax_call' ] ) {
     echo '<script type="text/javascript">console.log( ' .json_encode( $wpp_query ) . ' ); </script>';
    }

    ob_start();

    //** Make certain variables available to be used within the single listing page */
    $wpp_overview_shortcode_vars = apply_filters( 'wpp_overview_shortcode_vars', array(
      'wp_properties' => $wp_properties,
      'wpp_query' => $wpp_query
    ) );

    //** By merging our extra variables into $wp_query->query_vars they will be extracted in load_template() */
    if( is_array( $wpp_overview_shortcode_vars ) ) {
      $wp_query->query_vars = array_merge( $wp_query->query_vars, $wpp_overview_shortcode_vars );
    }

    $template = $wpp_query[ 'template' ];
    $fancybox_preview = $wpp_query[ 'fancybox_preview' ];
    $show_children = $wpp_query[ 'show_children' ];
    $class = $wpp_query[ 'class' ];
    $stats = $wpp_query[ 'stats' ];
    $in_new_window = ( !empty( $wpp_query[ 'in_new_window' ] ) ? " target=\"_blank\" " : "" );

    //** Make query_vars available to emulate WP template loading */
    extract( $wp_query->query_vars, EXTR_SKIP );

    //** Try find custom template */
    $template_found = WPP_F::get_template_part( array(
      "property-overview-{$template}",
      "property-overview-{$property_type}",
      "property-{$template}",
      "property-overview",
    ), array( WPP_Templates ) );

    if( $template_found ) {
      include $template_found;
    }

    $ob_get_contents = ob_get_contents();
    ob_end_clean();

    $ob_get_contents = apply_filters( 'shortcode_property_overview_content', $ob_get_contents, $wpp_query );

    // Initialize result ( content which will be shown ) and open wrap ( div ) with unique id
    if( $wpp_query[ 'disable_wrapper' ] != 'true' ) {
      $result[ 'top' ] = '<div id="wpp_shortcode_'. $defaults[ 'unique_hash' ] .'" class="wpp_ui '.$wpp_query[ 'class' ].'">';
    }

    $result[ 'top_pagination' ] = wpi_draw_pagination( array( 'return' => true, 'class' => 'wpp_top_pagination', 'sorter_type' => $wpp_query[ 'sorter_type' ], 'hide_count' => $hide_count, 'sort_by_text' => $wpp_query[ 'sort_by_text' ] ) );
    $result[ 'result' ] = $ob_get_contents;

    if( $wpp_query[ 'bottom_pagination_flag' ] == 'true' ) {
      $result[ 'bottom_pagination' ] = wpi_draw_pagination( array( 'return' => true, 'class' => 'wpp_bottom_pagination', 'sorter_type' => $wpp_query[ 'sorter_type' ], 'hide_count' => $hide_count, 'sort_by_text' => $wpp_query[ 'sort_by_text' ] ) );
    }

    if( $wpp_query[ 'disable_wrapper' ] != 'true' ) {
      $result[ 'bottom' ] = '</div>';
    }

    $result = apply_filters( 'wpp_property_overview_render', $result );

    if( $wpp_query[ 'ajax_call' ] ) {
      return json_encode( array( 'wpp_query' => $wpp_query, 'display' => implode( '', $result ) ) );
    } else {
      return implode( '', $result );
    }
  }


  /**
   * Get terms from all property taxonomies, grouped by taxonomy
   *
   * @todo Make sure the label/title is rendered correctly when grouped and ungrouped. - potanin@UD
   * @todo Improve so shortcode arguments are passed to draw_stats - potanin@UD 5/24/12
   * @since 1.35.0
   */
  function shortcode_property_attributes( $atts = false ) {
    global $wp_properties, $property;

    if( is_admin() && !DOING_AJAX ) {
      return sprintf(__( '%1$s Attributes', 'wpp' ), WPP_F::property_label('singular'));
    }

    $atts = shortcode_atts( array(
      'property_id' => $property[ 'ID' ],
      'title' => false,
      'group' => false,
      'sort_by_groups' => !empty( $wp_properties[ 'property_groups' ] ) && $wp_properties[ 'configuration' ][ 'property_overview' ][ 'sort_stats_by_groups' ] == 'true' ? true : false
     ), $atts );

    $html[] = draw_stats( "return=true&make_link=true&group={$atts[group]}&title={$atts[ 'title' ]}&sort_by_groups={$atts[ 'sort_by_groups' ]}", $property );

    return implode( '', (array) $html );

  }


  /**
   * Get terms from all property taxonomies, grouped by taxonomy
   *
   * @todo Add support to recognize requested taxonomy and default to all, as well as some other shortcode-configured settings - potanin@UD 5/24/12
   * @since 1.35.0
   */
  function shortcode_taxonomy_terms( $atts = false ) {
    global $wp_properties, $post, $property;

    if( is_admin() && !DOING_AJAX ) {
      return sprintf(__( '%1$s Taxonomy Terms', 'wpp' ), WPP_F::property_label('singular'));
    }

    $atts = shortcode_atts( array(
      'property_id' => $property[ 'ID' ],
      'title' => false,
      'taxonomy' => ''
     ), $atts );

    foreach( ( array ) $wp_properties[ 'taxonomies' ] as $tax_slug => $tax_data ) {

      $terms = get_features( "property_id={$atts[ 'property_id' ]}&type={$tax_slug}&format=list&links=true&return=true" );

      if( !empty( $terms ) ) {

        $html[] = '<div class="' . wpp_css( 'attribute_list::list_item', 'wpp_attributes', true ) . '">';

        if( $atts[ 'title' ] ) {
          $html[] = '<h2 class="wpp_list_title">' . $tax_data[ 'labels' ][ 'name' ] . '</h2>';
        }

        $html[] = '<ul class="' . wpp_css( 'attribute_list::list_item', 'wpp_feature_list wpp_attribute_list wpp_taxonomy_terms', true ) .  '">';
        $html[] = $terms;
        $html[] = '</ul>';

        $html[] = '</div>'; /* .wpp_attributes */

      }

    }

    return implode( '', ( array ) $html );

  }


  /**
   * Retrevie property attribute using shortcode.
   *
   * @since 1.26.0
   */
  function shortcode_property_attribute( $atts = array() ) {
    global $post, $property;

    if( is_admin() && !DOING_AJAX ) {
      return sprintf(__( '%1$s Attribute', 'wpp' ), WPP_F::property_label('singular'));
    }

    $this_property = $property;

    if( empty( $this_property ) && $post->post_type == WPP_Object ) {
      $this_property = $post;
    }

    $this_property = ( array ) $this_property;

    $args = shortcode_atts( array(
      'property_id' => $this_property[ 'ID' ],
      'attribute' => '',
      'before' => '',
      'after' => '',
      'if_empty' => '',
      'do_not_format' => '',
      'make_terms_links' => 'false',
      'separator' => ' ',
      'strip_tags' => ''
    ), $atts );

    if( empty( $args[ 'attribute' ] ) ) {
      return false;
    }

    $attribute = $args[ 'attribute' ];

    if( $args[ 'property_id' ] != $this_property[ 'ID' ] ) {

      $this_property = WPP_F::get_property( $args[ 'property_id' ] );

      if( $args[ 'do_not_format' ] != "true" ) {
        $this_property = prepare_property_for_display( $this_property );
      }

    } else {
      $this_property = $this_property;
    }

    if( is_taxonomy( $attribute ) && is_object_in_taxonomy( WPP_Object, $attribute ) ) {
      foreach( wp_get_object_terms( $this_property[ 'ID' ], $attribute ) as $term_data ) {

        if( $args[ 'make_terms_links' ] == 'true' ) {
          $terms[] = '<a class="wpp_term_link" href="'. get_term_link( $term_data, $attribute ) . '"><span class="wpp_term">' . $term_data->name . '</span></a>';
        } else {
          $terms[] = '<span class="wpp_term">' . $term_data->name . '</span>';
        }
      }

      if( is_array( $terms ) && !empty( $terms ) ) {
        $value = implode( $args[ 'separator' ], $terms );
      }

    }

    //** Try to get value using get get_attribute() function */
    if( !$value && function_exists( 'get_attribute' ) ) {
      $value = get_attribute( $attribute, array(
        'return' => 'true',
        'property_object' => $this_property
        ) );
    }

    if( !empty( $args[ 'before' ] ) ) {
      $return[ 'before' ] = html_entity_decode( $args[ 'before' ] );
    }

    $return[ 'value' ] = apply_filters( 'wpp_property_attribute_shortcode', $value, $the_property );

    if( $args[ 'strip_tags' ] == "true" && !empty( $return[ 'value' ] ) ) {
      $return[ 'value' ] = strip_tags( $return[ 'value' ] );
    }

    if( !empty( $args[ 'after' ] ) ) {
      $return[ 'after' ] =  html_entity_decode( $args[ 'after' ] );
    }

    //** When no value is found */
    if( empty( $value[ 'value' ] ) ) {

      if( !empty( $args[ 'if_empty' ] ) ) {
        return $args[ 'if_empty' ];
      } else {
        return false;
      }
    }


    if( is_array( $return ) ) {
      return implode( '', $return );
    }

    return false;

  }


  /**
   * Displays a map for the current property.
   *
   * Must be used on a property page, or within a property loop where the global $post or $property variable is for a property object.
   *
   * @since 1.26.0
   *
   */
  function shortcode_property_map( $atts = false ) {
    global $post, $property;

    if( is_admin() && !DOING_AJAX ) {
      return sprintf(__( '%1$s Map', 'wpp' ), WPP_F::property_label('singular'));
    }

    $atts = shortcode_atts( array(
      'width' => '100%',
      'height' => '450px',
      'zoom_level' => '13',
      'hide_infobox' => 'false',
      'property_id' => $property->ID
    ) , $atts );


    //** Try to get property if an ID is passed */
    if( is_numeric( $atts[ 'property_id' ] ) ) {
      $property = WPP_F::get_property( $atts[ 'property_id' ] );
    }

    //** Load into $property object */
    if( !isset( $property ) ) {
      $property = $post;
    }

    //** Convert to object */
    $property = ( object ) $property;

    //** Force map to be enabled here */
    $skip_default_google_map_check = true;

    $map_width = $atts[ 'width' ];
    $map_height = $atts[ 'height' ];
    $hide_infobox = ( $atts[ 'hide_infobox' ] == 'true' ? true : false );

    //** Find most appropriate template */
    $template_found = WPP_F::get_template_part( array(
      'content-single-property-map',
      'property-map'
    ), array( WPP_Templates ) );

    if( !$template_found ) {
      return false;
    }

    ob_start();
    include $template_found;
    $html = ob_get_contents();
    ob_end_clean();

    $ob_get_contents = apply_filters( 'wpp::property_map_content' , $ob_get_contents, $atts );

    return $html;
  }


  /**
   *
   * @since 0.723
   *
   * @uses WPP_Core::shortcode_property_overview()
   *
   */
  function ajax_property_overview()  {

    $params = $_REQUEST[ 'wpp_ajax_query' ];

    if( !empty( $params[ 'action' ] ) ) {
      unset( $params[ 'action' ] );
    }

    $params[ 'ajax_call' ] = true;

    $data = WPP_Core::shortcode_property_overview( $params );

    die( $data );

  }


  /**
   * Checks settings data on accord with existing wp_properties data ( before option updates )
   * @param array $wpp_settings New wpp settings data
   * @param array $wp_properties Old wpp settings data
   * @return array $wpp_settings
   */
  function check_wp_settings_data ( $wpp_settings, $wp_properties ) {
    if( is_array( $wpp_settings ) && is_array( $wp_properties ) ) {
        foreach( (array) $wp_properties as $key => $value ) {
            if( !isset( $wpp_settings[$key] ) ) {
                switch( $key ) {
                    case 'hidden_attributes':
                    case 'property_inheritance':
                        $wpp_settings[$key] = array();
                        break;
                }
            }
        }
    }

    return $wpp_settings;
  }


  /*
   * Hack to avoid issues with capabilities and views.
   *
   */
  function current_screen( $screen ){

    switch( $screen->id ){
      case "edit-property":
        wp_redirect( 'edit.php?post_type=property&page=all_properties' );
        exit();
        break;
    }

    return $screen;
  }


  /*
   * Adds all WPP custom capabilities to administrator role.
   * Premium feature capabilities are added by filter in this function, see below.
   *
   * @author Maxim Peshkov
   */
  function set_capabilities() {
    global $wpp_capabilities;

    //* Get Administrator role for adding custom capabilities */
    $role =& get_role( 'administrator' );

    //* General WPP capabilities */
    $wpp_capabilities = array(
      //* Manage WPP Properties Capabilities */
      'edit_wpp_property' => sprintf(__( 'Edit %1$s','wpp' ), ucfirst( WPP_F::property_label( 'singular' ) )),
      'read_wpp_property' => sprintf(__( 'Read %1$s','wpp' ), ucfirst( WPP_F::property_label( 'singular' ) )),
      'delete_wpp_property' => sprintf(__( 'Delete %1$s','wpp' ), ucfirst( WPP_F::property_label( 'singular' ) )),
      'edit_wpp_properties' => sprintf(__( 'Edit %1$s','wpp' ), ucfirst( WPP_F::property_label( 'plural' ) )),
      'edit_others_wpp_properties' => sprintf(__( 'Edit Other %1$s','wpp' ), ucfirst( WPP_F::property_label( 'plural' ) )),
      'publish_wpp_properties' => sprintf(__( 'Publish %1$s','wpp' ), ucfirst( WPP_F::property_label( 'plural' ) )),
      'read_private_wpp_properties' => sprintf(__( 'Read Private %1$s','wpp' ), ucfirst( WPP_F::property_label( 'plural' ) )),
      //* WPP Settings capability */
      'manage_wpp_settings' => __( 'Manage Settings','wpp' ),
      //* WPP Taxonomies capability */
      'manage_wpp_categories' => __( 'Manage Categories','wpp' )
    );

    //* Adds Premium Feature Capabilities */
    $wpp_capabilities = apply_filters( 'wpp_capabilities', $wpp_capabilities );

    if( !is_object( $role ) ) {
      return;
    }

    foreach( (array) $wpp_capabilities as $cap => $value ){
      if ( empty( $role->capabilities[$cap] ) ) {
        $role->add_cap( $cap );
      }
    }
  }


  /**
   * WPP Contextual Help
   *
   * @global type $current_screen
   * @param type $args
   * @author korotkov@ud
   */
  function wpp_contextual_help( $args = array() ) {

    $defaults = array(
      'contextual_help' => array()
    );

    extract( wp_parse_args( $args, $defaults ) );

    //** If method exists add_help_tab in WP_Screen */
    if( is_callable( array( 'WP_Screen','add_help_tab' ) ) ) {

      //** Loop through help items and build tabs */
      foreach ( ( array ) $contextual_help as $help_tab_title => $help ) {

        //** Add tab with current info */
        get_current_screen()->add_help_tab(
       array(
            'id'      => sanitize_title( $help_tab_title ),
            'title'   => __( $help_tab_title, 'wpp' ),
            'content' => implode( "\n",( array )$contextual_help[$help_tab_title] ),
          )
        );

      }

      //** Add help sidebar with More Links */
      get_current_screen()->set_help_sidebar(
        '<p><strong>' . __( 'For more information:', 'wpp' ) . '</strong></p>' .
        '<p>' . __( '<a href="https://usabilitydynamics.com/products/wp-property/" target="_blank">WP-Property Product Page</a>', 'wpp' ) . '</p>' .
        '<p>' . __( '<a href="https://usabilitydynamics.com/products/wp-property/forum/" target="_blank">WP-Property Forums</a>', 'wpp' ) . '</p>' .
        '<p>' . __( '<a href="https://usabilitydynamics.com/help/" target="_blank">WP-Property Tutorials</a>', 'wpp' ) . '</p>'
      );

    } else {
      //** If WP is out of date */
      global $current_screen;
      add_contextual_help( $current_screen->id, '<p>'.__( 'Please upgrade Wordpress to the latest version for detailed help.', 'wpp' ).'</p><p>'.__( 'Or visit <a href="https://usabilitydynamics.com/tutorials/wp-property-help/" target="_blank">WP-Property Help Page</a> on UsabilityDynamics.com', 'wpp' ).'</p>' );
    }
  }

}

