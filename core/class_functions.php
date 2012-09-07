<?php
/**
 * WP-Property General Functions
 *
 * Contains all the general functions used by the plugin.
 *
 * @version 1.00
 * @author Andy Potanin <andy.potanin@twincitiestech.com>
 * @package WP-Property
 * @subpackage Functions
 */

class WPP_F extends UD_API {

  /**
   * Return data for WPP Log
   *
   * @note This is a proof of concept, in future it should be able to support AJAX calls so can be displayed via Dynamic Filter.
   * @since 1.37.0
   * @author potanin@UD
   */
  static function get_log( $args = false ) {

    $_log = array();
    $_return = array(
      'success' => false,
      'start_id' => false,
    );

    $args = wp_parse_args( $args, array(
      'product' => 'wpp',
    ));

    //** Used by AJAX call */
    if( is_array( $args ) && isset( $args['shot'] ) && $args['shot'] == 1 ) {
      $sort_type = !empty( $args['sort_type'] ) ? $args['sort_type'] : false;
      $args['sort_type'] = 'DESC';
      $args['direction'] = 'less';
      $rows = parent::get_log( $args );
      if( !empty( $rows ) ) {
        $start_id = @$rows[ ( count($rows) - 1 ) ]->id;
        if( !empty( $start_id ) ) {
          $_return['start_id'] = $start_id;
          $rows = array_reverse( $rows );
        }
      }
    } else {
      $rows = parent::get_log( $args );
    }

    foreach( (array) $rows as $row ) {

      $_log[ $row->id ] = $row;
      $_log[ $row->id ]->time = human_time_diff( $_log[ $row->id ]->time ) . ' ago.';

      if( stripos( $row->type, 'wp_error' ) !== false ) {
        $_log[ $row->id ]->error = true;
        $_log[ $row->id ]->object = maybe_unserialize( $row->message );
        $_log[ $row->id ]->print = print_r( $_log[ $row->id ]->object, true );
        $_log[ $row->id ]->message = $_log[ $row->id ]->object->get_error_message();

      } else if ( stripos( $row->type, 'object' ) !== false || stripos( $row->type, 'array' ) !== false ) {
        //$_log[ $row->id ]->message = print_r( maybe_unserialize( $row->message ), true );
        $_log[ $row->id ]->object = maybe_unserialize( $row->message );
        $_log[ $row->id ]->print = print_r( $_log[ $row->id ]->object, true );
        $_log[ $row->id ]->message = __( 'Log\'s data:', 'wpp' );
      }

    }

    $_return['success'] = count( $_log ) ? true : false;
    $_return['log'] = $_log;

    return $_return;

  }


  /**
   * Removes WPP data from Logs table.
   *
   * @param mixed $args
   * @author peshkov@UD
   */
  static function clear_log( $args = array() ) {
    $args = wp_parse_args( array(
      'product' => 'wpp',
    ), $args );
    return parent::clear_log( $args );
  }


  /**
   * Converts a boolean value into a printable string
   *
   * @author potanin@UD
   * @since 1.37.0
   */
  static function from_boolean( $value = false ) {

    if ( $value === true || $value == 'true' ||  $value == 1 || $value == __( 'Yes', 'wpp' )) {
      $value = __( 'Yes', 'wpp' );
    }else{
      $value = __('No', 'wpp');
    }
    return $value;
  }


  /**
   * Converts a value into a true/false boolean
   *
   * @author potanin@UD
   * @since 1.37.0
   */
  static function to_boolean( $value = false ) {

    switch( true ) {

      case strtolower( $value ) == 'no':
      case strtolower( $value ) == strtolower( __( 'no' ) ):
      case strtolower( $value ) == 'false':
      case empty( $value ):
        return false;
      break;

      case strtolower( $value ) == 'yes':
      case strtolower( $value ) == strtolower( __( 'yes' ) ):
      case strtolower( $value ) == 'true':
      case $value == true:
        return true;
      break;

    }

    return $value;

  }


  /**
   * Handler for UD_API::image_dimensions() tha loads the rim class
   *
   * @since 1.37.0
   * @author potanin@UD
   */
  static function image_dimensions( $images = false, $args = array() ) {

    if( version_compare( PHP_VERSION, '5.3.0' ) >= 0 ) {
      include_once WPP_Path . 'third-party/rim.php';
    }

    return parent::image_dimensions( $images, $args );

  }


  /**
   * Extends UD_API::get_template_part() by handling global variables
   *
   * @author potanin@UD
   * @since 1.37.0
   */
  static function get_template_part( $templates, $path = array(), $load = false ) {
    global $wp_query, $property;

    $wp_query->query_vars[ 'property' ] = $property;

    return UD_API::get_template_part(  $templates, $path, $load );

  }


  /**
   * Return object of WPP attribute structure.
   *
   * @author potanin@UD
   */
  static function get_data_structure() {
    global $wp_properties;

    $return = array(
      'attributes' => array(),
      'groups' => array()
    );

    foreach( (array) self::get_total_attribute_array( array( 'uniqueness' => true ) ) as $meta_key => $label ) {
      $_data = WPP_F::get_attribute_data( $meta_key );

      $return[ 'attributes' ][ $meta_key ] = array_filter( array(
        'label' => $_data[ 'label' ],
        'standard' => $_data[ 'standard' ] ? true : false,
        'type' => $_data[ '_attribute_type' ],
        'group' => $wp_properties[ 'property_groups' ][ $_data[ 'group_key' ] ][ 'name' ],
        '_detail' => $_data
      ));

    }

    foreach( (array) $wp_properties[ 'property_groups' ] as $group_slug => $data ) {
      $return[ 'groups' ][ $group_slug ] = array(
        'label' => $data[ 'name' ]
      );
    }

    //get_attribute_data();

    /*
    'attributes' => $wp_properties[ 'taxonomies' ],
    'groups' => $wp_properties[ 'property_groups' ],
    'property_stats' => $wp_properties[ 'property_stats' ],
    'property_stats_groups' => $wp_properties[ 'property_stats_groups' ],
    'attribute_type' => $wp_properties[ 'attribute_type' ],
    'searchable_attributes' => $wp_properties[ 'searchable_attributes' ],
    'property_meta' => $wp_properties[ 'property_meta' ],
    'location_matters' => $wp_properties[ 'location_matters' ],
    'property_types' => $wp_properties[ 'property_types' ]
    */

    return (object) array_filter( (array) $return );

  }


  /**
   * Return an array of all available attributes and meta keys
   *
   * @updated 1.36.1
   */
  static function get_total_attribute_array( $args = '', $extra_values = array() ) {
    global $wp_properties, $wpdb;

    extract( wp_parse_args( $args, array(
      'use_optgroups' => 'false'
    )), EXTR_SKIP );

    $property_stats = $wp_properties['property_stats'];

    $property_groups = $wp_properties['property_groups'];
    $property_stats_groups = $wp_properties['property_stats_groups'];

    if($use_optgroups == 'true') {

      foreach( (array) $property_stats as $key => $attribute_label ) {

        if( $property_stats_groups[ $key ] ) {
          $_group_slug = $property_stats_groups[ $key ];
          $_group_label = $property_groups[ $_group_slug ][ 'name' ];
        }

        $_group_label = $_group_label ? $_group_label : 'Attributes';
        $attributes[ $_group_label ][ $key ] = $attribute_label;


      }

      $attributes['Other'] = $extra_values;

      $attributes = array_filter( (array) $attributes );

      foreach( (array) $attributes as $_group_label => $_attribute_data ) {
        asort( $attributes[ $_group_label ] );
      }

    } else {
      $attributes = $property_stats + $extra_values;
    }

    $attributes = apply_filters('wpp_total_attribute_array', $attributes);

    if(!is_array($attributes)) {
      $attributes = array();
    }

    return $attributes;

  }


  /**
   * {}
   *
   * @author potanin@UD
   * @since 1.37.0
   */
  static function add_shortcodes( $args = '' ) {
    global $wp_properties, $shortcode_tags;

    $wp_properties[ 'shortcodes' ] = array(
      'map' => 'shortcode_property_map',
      'attribute' => 'shortcode_property_attribute',
      'attributes' => 'shortcode_property_attributes',
      'taxonomy_terms' => 'shortcode_taxonomy_terms',
      'overview' => 'shortcode_property_overview',
      'search' => 'shortcode_property_search',
    );

    //** Add shortcodes with different variations */
    foreach( $wp_properties[ 'shortcodes' ] as $short_name => $function ) {
      add_shortcode( WPP_Object . '_' . $short_name , array( 'WPP_Core' , $function ) );
      add_shortcode( WPP_Object . '-' . $short_name , array( 'WPP_Core' , $function ) );
    }

    //** Non-dynamic Shortcodes */
    add_shortcode( 'featured_properties', array( 'WPP_Core' , 'shortcode_featured_properties' ) );
    add_shortcode( 'featured-properties', array( 'WPP_Core' , 'shortcode_featured_properties' ) );

    /** Shortcodes: Agents Feature Fallback */
    if( !class_exists( 'class_agents' ) ) {
      add_shortcode( 'agent_card', array( 'WPP_Core', 'shortcode_missing_feature' ) );
    }

    /** Shortcode: Supermap Feature Fallback */
    if( !class_exists( 'class_wpp_supermap' ) ) {
      add_shortcode( 'supermap', array( 'WPP_Core', 'shortcode_missing_feature' ) );
    }

    /** Shortcode: Slideshow Feature Fallback */
    if( !class_exists( 'class_wpp_slideshow' ) ) {
      add_shortcode( WPP_Object .  '_slideshow', array( 'WPP_Core', 'shortcode_missing_feature' ) );
      add_shortcode( WPP_Object . '_gallery', array( 'WPP_Core', 'shortcode_missing_feature' ) );
      add_shortcode( 'global_slideshow', array( 'WPP_Core', 'shortcode_missing_feature' ) );
    }

  }


  /**
   * Seek out templates than can be used as frames for WPP content
   *
   * @author potanin@UD
   * @since 1.37.0
   */
  static function get_available_theme_templates( $args = '' ) {
    global $wp_file_descriptions, $wp_properties;

    $args = wp_parse_args( $args, array(
      'templates' => array(
        'page.php',
        'single.php',
        'single.php',
        'single-post.php',
        'index.php'
      ),
      '_something' => 'nothing'
    ));

    foreach( (array) $args[ 'templates' ] as $file_name ) {
      if( $_path = locate_template( $file_name ) ) {
        $_found[ $file_name ] = array(
          'name' => $wp_file_descriptions[ $file_name ],
          'path' => $_path,
          'theme' => basename( dirname( $_path ) ),
          'theme_type' => strpos( $_path, STYLESHEETPATH ) !== false ? 'child' : 'parent'
        );
      }
    }

    return $_found;

  }


  /**
   * Loads WPP templates from their default locations if do not exist in the theme
   *
   * @author potanin@UD
   * @since 1.37.0
   */
  static function get_template_part_handler( $slug = '' , $name = '' ){

    switch ($name) {

      case 'property-listing':

        $template_found = WPP_F::get_template_part( array(
          'content-property-listing'
        ), array( WPP_Templates ), true );

        echo $template_found;

      break;

    }

  }


  /**
   * Adds wp-property-listing class in search results and property_overview pages
   *
   * @since 0.7260
   */
  static function properties_body_class( $classes ){
    global  $post, $wp_properties, $wp_scripts;

    if( strpos( $post->post_content, 'property_overview' ) || ( is_search() && isset( $_REQUEST[ 'wpp_search' ] ) ) || ( $wp_properties[ 'configuration' ][ 'base_slug' ] == $post->post_name ) ) {
        $classes[] = 'wp-property-listing';
    }

    foreach ((array)$wp_scripts->in_footer as $script){
      if (substr($script, 0, 9)=='jquery-ui'){
        $classes[] = 'wpp_ui';
      }
    }

    return $classes;

  }


  /**
   * Registers Scripts and Styles.
   *
   * Registration is necessary for these assets since they will be enqueued conditionally
   *
   * @action admin_enqueue_scripts (10)
   * @action wp_enqueue_scripts (10)
   * @since 1.37.0
   * @author potanin@UD
   */
  static function register_assets() {

    //** Register third-party scripts and styles */
    wp_register_script( 'jquery-fancybox', WPP_URL. 'third-party/fancybox/jquery.fancybox-1.3.4.pack.js', array( 'jquery' ), '1.7.3', true );
    wp_register_script( 'jquery-stickysidebar', WPP_URL. 'js/stickysidebar.jquery.min.js', array( 'wp-property-admin-global' ), '', true );
    wp_register_script( 'jquery-colorpicker', WPP_URL. 'third-party/colorpicker/colorpicker.js', array( 'jquery' ), WPP_Version, true  );
    wp_register_script( 'jquery-easing', WPP_URL. 'third-party/fancybox/jquery.easing-1.3.pack.js', array( 'jquery' ), '1.7.3', true );
    wp_register_script( 'jquery-cookie', WPP_URL. 'js/jquery.smookie.js', array( 'jquery' ), '1.7.3', true );
    wp_register_script( 'jquery-ajaxupload', WPP_URL. 'js/fileuploader.js', array( 'jquery' ), WPP_Version, true );
    wp_register_script( 'jquery-gmaps', WPP_URL. 'js/jquery.ui.map.min.js', array( 'jquery' ), WPP_Version, true );
    wp_register_script( 'google-maps', 'https://maps.google.com/maps/api/js?sensor=true', array(), WPP_Version, true );
    wp_register_script( 'jquery-nivo-slider', WPP_URL. 'third-party/jquery.nivo.slider.pack.js', array( 'jquery' ),  WPP_Version, true );
    wp_register_script( 'jquery-address', WPP_URL. 'js/jquery.address-1.3.2.js', array( 'jquery' ), WPP_Version, true );
    wp_register_script( 'jquery-scrollTo', WPP_URL. 'js/jquery.scrollTo-min.js', array( 'jquery' ), WPP_Version, true );
    wp_register_script( 'jquery-validate', WPP_URL. 'js/jquery.validate.js', array( 'jquery' ), WPP_Version, true );
    wp_register_script( 'jquery-number-format', WPP_URL. 'js/jquery.number.format.js', array( 'jquery' ), WPP_Version, true );
    wp_register_script( 'jquery-data-tables', WPP_URL . "third-party/dataTables/jquery.dataTables.min.js", array( 'jquery' ), WPP_Version, true );
    wp_register_script( 'wp-property-galleria', WPP_URL. 'third-party/galleria/galleria-1.2.7.min.js', array( 'jquery' ), WPP_Version, true );
    wp_register_style( 'jquery-fancybox-css', WPP_URL. 'third-party/fancybox/jquery.fancybox-1.3.4.css' );
    wp_register_style( 'jquery-colorpicker-css', WPP_URL. 'third-party/colorpicker/colorpicker.css' );
    wp_register_style( 'jquery-ui', WPP_URL. 'css/jquery-ui.css' );
    wp_register_style( 'jquery-data-tables', WPP_URL . "third-party/dataTables/wpp-data-tables.css" );

    //** Register WPP-specific Scripts and Styles */
    wp_register_script( 'wp-property-global-js', WPP_URL. 'js/wp-property-global.js', array( 'jquery' ), WPP_Version, true );

    wp_register_script( 'wp-property-admin-global', WPP_URL. 'js/wp-property-admin-global.js', array(
      'wp-property-global-js'
    ), WPP_Version, true );

    wp_register_script( 'wp-property-admin-overview', WPP_URL. 'js/wp-property-admin-overview.js', array(
      'wp-property-global-js',
      'postbox',
      'post' ,
      'jquery-data-tables'
    ) , WPP_Version, true );

    wp_register_script( 'wp-property-admin-settings', WPP_URL. 'js/wp-property-admin-settings.js', array(
      'wp-property-admin-global',
      'jquery-ui-tabs',
      'jquery-cookie',
      'jquery-ui-sortable',
      'jquery-colorpicker',
      'jquery-ui-datepicker'
    ), WPP_Version, true );

    wp_register_style( 'wp-property-admin-global' , WPP_URL . 'css/wp-property-admin-global.css', array(), WPP_Version, 'screen' );
  }


  /**
   * Enqueues Admin Scripts and Styles
   *
   * @since 1.37.0
   * @author potanin@UD
   */
  static function admin_enqueue_scripts() {
    global $current_screen, $wp_properties;

    self::register_assets();

    //** Enqueue Global Admin JS and CSS */
    wp_enqueue_script( 'wp-property-admin-global' );
    wp_enqueue_style( 'wp-property-admin-global' );

    //** Enqueue conditional Admin JS and CSS */
    switch( $current_screen->id ) {

      /**
      * Listing Overview Page
      */
      case 'property_page_all_properties':
        wp_enqueue_script( 'wp-property-admin-overview' );
        wp_enqueue_script( 'jquery-fancybox' );
        wp_enqueue_style( 'jquery-fancybox-css' );
        wp_enqueue_style( 'jquery-data-tables' );
      break;

      /**
      * WP-Property Settings Page
      *
      */
      case 'property_page_property_settings_new':
      case 'property_page_property_settings':
        wp_enqueue_script( 'jquery-ajaxupload' );
        wp_enqueue_script( 'wp-property-admin-settings' );
        wp_enqueue_script( 'jquery-stickysidebar' );
        wp_enqueue_style( 'jquery-colorpicker-css' );
      break;

      case 'property':

        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-ui');

        break;

      /**
      * Appearance -> Widgets
      *
      */
      case 'widgets':
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_style('jquery-ui');
      break;

    }

    add_action('admin_body_class', array('WPP_F', 'jquery_ui_body_class'));

    self::localize_scripts();

  }


  /**
   * Enqueue WPP Frontend Scripts.
   *
   * Called on Single Listing and Listing Overview Pages, unless frontend scripts are configured to load unconditionally
   *
   * @since 1.37.0
   * @author potanin@UD
   */
  static function wp_enqueue_scripts() {
    global $wp_query, $wp_styles, $wp_properties, $current_screen;

    WPP_F::console_log( 'Loading frontend scripts and styles.' );

    self::register_assets();

    //** Enqueue assets based on current request */
    switch (true) {

      /**
      * Single Listing Page
      *
      */
      case ( $wp_query->single_property_page && !post_password_required( $post ) ):

        if($wp_properties['configuration']['do_not_use']['physical_locations'] != 'true') {
          wp_enqueue_script('google-maps');
        }

        if( $wp_properties['configuration']['property_overview']['fancybox_preview'] == 'true' ) {
          wp_enqueue_script('jquery-fancybox');
          wp_enqueue_style('jquery-fancybox-css');
        }

      break;

      /**
      * Listing Overview / Search Result Page
      *
      */
      case ( $wp_query->is_property_overview ):

        wp_enqueue_script('jquery-ui-slider');
        wp_enqueue_script('jquery-ui-mouse');
        wp_enqueue_script('jquery-ui-widget');
        wp_enqueue_script('jquery-fancybox');
        wp_enqueue_script('jquery-address');
        wp_enqueue_script('jquery-scrollTo');

        wp_enqueue_style('jquery-fancybox-css');
        wp_enqueue_style('jquery-ui');

      break;

    }

    //** Find and enqueue global WPP front-end JavaScript file */
    if( $frontend_js_url = WPP_F::get_template_file_url( 'wp_properties.js' ) ) {
      wp_enqueue_script( 'wp-property-frontend', $frontend_js_url, array( 'wp-property-global-js', 'jquery-ui-core' ),WPP_Version, true );
    }

    //** Find and enqueue global WPP front-end CSS */
    if( $frontend_css_url = WPP_F::get_template_file_url( 'wp_properties.css' ) ) {
      wp_enqueue_style( 'wp-property-frontend', $frontend_css_url, array(), WPP_Version );
    }

    //** If enabled and exists, enqueue theme-specific CSS */
    if( !is_admin() && $wp_properties[ 'configuration' ][ 'do_not_load_theme_specific_css' ] != 'true' && WPP_F::has_theme_specific_stylesheet() ) {
      wp_enqueue_style( 'wp-property-theme-specific', WPP_URL . 'templates/theme-specific/' . get_option( 'template' ) . '.css', array( 'wp-property-frontend' ), WPP_Version );
    }

    //** Enqueue conditional CSS */
    foreach( (array) apply_filters( 'wpp::conditional_css_types', array( 'IE', 'IE 7', 'msie' ) ) as $type ) {

      // Fix slug for URL
      $url_slug = strtolower( str_replace( " ", "_", $type ));

      if ( file_exists( STYLESHEETPATH . "/wp_properties-{$url_slug}.css" ) ) {
        wp_enqueue_style( 'wp-property-frontend-'. $url_slug, get_bloginfo( 'stylesheet_directory' ) . "/wp_properties-{$url_slug}.css", array( 'wp-property-frontend' ),'1.13' );
      } elseif ( file_exists( TEMPLATEPATH . "/wp_properties-{$url_slug}.css" ) ) {
        wp_enqueue_style( 'wp-property-frontend-'. $url_slug, get_bloginfo( 'template_url' ) . "/wp_properties-{$url_slug}.css", array( 'wp-property-frontend' ),'1.13' );
      } elseif ( file_exists( WPP_Templates . "/wp_properties-{$url_slug}.css" ) && $wp_properties[ 'configuration' ][ 'autoload_css' ] == 'true' ) {
        wp_enqueue_style( 'wp-property-frontend-'. $url_slug, WPP_URL . "templates/wp_properties-{$url_slug}.css", array( 'wp-property-frontend' ),WPP_Version );
      }

      $wp_styles->add_data( 'wp-property-frontend-'. $url_slug, 'conditional', $type );

    }

    self::localize_scripts();

  }


  /**
   * Print admin JavaScript
   *
   * @since 1.37.0
   * @author potanin@UD
   */
  static function admin_print_footer_scripts() {
    global $current_screen, $wp_properties, $post, $wpdb, $wp_scripts, $current_user;

    //** JavaScript array for the wpp object */
    $wpp_js = array(
      'current_user' => array(
        'user_id' => $current_user->data->ID,
        'display_name' => $current_user->data->display_name
      ),
      'server' => self::get_server_capabilities(),
      'strings' => $wp_properties['l10n'],
      'wp_properties' => $wp_properties
    );

    $output = array();

    if( is_array( $wpp_js ) ) {
      $output[] = '<script type="text/javascript">if( typeof jQuery != "undefined" ) { var wpp = jQuery.extend( true, jQuery.parseJSON( ' . json_encode( json_encode( $wpp_js ) ). ' ), typeof wpp === "object" ? wpp : {} ); }</script>';
    };

    echo implode( '', (array) apply_filters( 'wpp:admin_footer_output', $output ));

  }


  /**
   * Seeks out a template file and returns the URL if it exists
   *
   * @since 1.37.0
   * @author potanin@UD
   */
  static function get_template_file_url( $file_name, $args = false ) {

    $file_names = array(
      str_replace( '_', '-', $file_name ),
      str_replace( '-', '_', $file_name )
    );

    foreach( $file_names as $file_name ) {

      if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $file_name ) ) {
        return trailingslashit( get_stylesheet_directory_uri() ) . $file_name;
      }

      if ( file_exists( trailingslashit( get_template_directory() ) . $file_name ) ) {
        return trailingslashit( get_template_directory_uri() ) . $file_name;
      }

      if ( file_exists( trailingslashit( WPP_Templates ) . $file_name ) ) {
        return trailingslashit( WPP_Templates_URL ) . $file_name;
      }

    }

  }


  /**
   * Frontend script handler, in footer
   *
   * @since 1.37.0
   * @author potanin@UD
   */
  static function wp_footer( $args = false ) {
    global $post, $property, $wp_query, $wp_properties, $wp_styles, $wpp_query, $wp_taxonomies;

    //** Create array of data to output to front-end */
    $wpp_js = array(

      'locale' => array(
        'thousands_sep' => $wp_properties[ 'configuration' ][ 'thousands_sep' ]
      ),

      //** Use shortcode_atts to specify which configuraiton keys to output */
      'configuration' => shortcode_atts( array( /* None as of now */ ), $wp_properties[ 'configuration' ] )

    );

    //** Listing Overview / Search Result Pages */
    if( $wp_query->is_property_overview ) {
      $wpp_js[ 'configuration' ][ 'single_property_view' ] = $wp_properties[ 'configuration' ][ 'single_property_view' ];
      add_action('wp_head', create_function('', "do_action('wp_head_property_overview'); "));
    }

    //** Single Listing Pages */
    if( $wp_query->single_property_page && !post_password_required( $post ) ) {
      $wpp_js[ 'configuration' ][ 'single_property_view' ] = $wp_properties[ 'configuration' ][ 'single_property_view' ];
      add_action('wp_head', create_function('', "do_action('wp_head_single_property'); "));
    }

    if( is_array( $wpp_js ) ) {
      $output[] = '<script type="text/javascript">if( typeof jQuery != "undefined" ) { var wpp = jQuery.extend( true, jQuery.parseJSON( ' . json_encode( json_encode( $wpp_js ) ) . ' ), typeof wpp === "object" ? wpp : {} ); }</script>';
    };

    echo implode( '', (array) apply_filters( 'wpp:wp_footer_output', $output ));

  }


  /**
   * Adds localization support to all WP-Property scripts.
   *
   * @since 1.37.0
   * @author potanin@UD
   */
  static function localize_scripts() {
    global $property, $wp_properties;

    $wp_properties['l10n']['maps_failure'] = __( 'Could not initialize map.', 'wpp' );
    $wp_properties['l10n']['remove_confirmation'] = __( 'Are you sure you want to remove it?', 'wpp' );
    $wp_properties['l10n']['internal_server_error'] = __( '500 Internal Server Error! Your hosting account is most likely running out of memory.', 'wpp' );
    $wp_properties['l10n']['server_timeout'] = __( 'Your server timed out during the request.', 'wpp' );
    $wp_properties['l10n']['server_response_error'] = __( 'The server did not respond with anything we were expecting - there seems to be an error.', 'wpp' );
    $wp_properties['l10n']['ajax_response_empty'] = __( 'AJAX response empty, or not in valid JSON.', 'wpp' );

    wp_localize_script( 'wp-property-global-js', 'l10n', $wp_properties['l10n'] );
    wp_localize_script( 'wp-property-frontend', 'l10n', $wp_properties['l10n'] );
    wp_localize_script( 'wp-property-admin-global', 'l10n', $wp_properties['l10n'] );
    wp_localize_script( 'wp-property-admin-settings', 'l10n', $wp_properties['l10n'] );
    wp_localize_script( 'wp-property-admin-overview', 'l10n', $wp_properties['l10n'] );

  }


  /**
   * Adds "Settings" link to the plugin overview page
   *
   * @updated 0.37.0 - Moved from WPP_Core
   * @since 0.60
   */
  static function plugin_action_links( $links, $file ) {

    if( basename ( $file ) == 'wp-property.php' ) {
      array_unshift( $links, '<a href="'.admin_url("edit.php?post_type=property&page=property_settings").'">' . __('Settings','wpp') . '</a>' );
      $links[] =  '<a href="' . wp_nonce_url( "edit.php?post_type=property&page=property_settings&wpp_action=download-wpp-backup", 'download-wpp-backup' ) . '">' .  __( 'Download Backup', 'wpp' ) . '</a>';
    }

    return $links;
  }


  /**
   * This function grabs the API key from UD's servers
   *
   * @updated 1.36.0
   */
  static function get_api_key( $args = false ) {

    $args = wp_parse_args( $args, array(
      'force_check' => false
    ));

    //** check if API key already exists */
    $ud_api_key = get_option('ud_api_key');

    //** if key exists, and we are not focing a check, return what we have */
    if($ud_api_key && !$args['force_check']) {
      return $ud_api_key;
    }

    $blogname = get_bloginfo('url');
    $blogname = urlencode(str_replace(array('http://', 'https://'), '', $blogname));
    $system = 'wpp';
    $wpp_version = get_option( "wpp_version" );

    $check_url = "http://updates.usabilitydynamics.com/key_generator.php?system=$system&site=$blogname&system_version=$wpp_version";

    $response = @wp_remote_get($check_url);

    if(!$response) {
      return false;
    }

    // Check for errors
    if(is_wp_error($response)) {
      WPP_F::log( 'API Check Error: ' . $response->get_error_message());
      return false;
    }

    // Quit if failture
    if($response['response']['code'] != '200') {
      return false;
    }

    $response['body'] = trim($response['body']);

    //** If return is not in MD5 format, it is an error */
    if(strlen($response['body']) != 40) {

      if($args['return']) {
        return $response['body'];
      } else {
        WPP_F::log("API Check Error: " . sprintf(__('An error occurred during API key request: <b>%s</b>.','wpp'), $response['body']));
        return false;
      }
    }

    //** update wpi_key is DB */
    update_option('ud_api_key', $response['body']);

    // Go ahead and return, it should just be the API key
    return $response['body'];

  }


  /**
   * Custom logging function for WPP Global Log table.
   *
   * <code>
   * WPP_F::log( 'General WP-Property related log message.', 'important' );
   * WPP_F::log( 'A detailed entry regarding some feature in regards to a specific property.', array( 'type' => 'notice', 'post_id' => 4570, 'feature' => 'gallery', 'method' => __METHOD__ ) );
   * WPP_F::log( new WP_Error( 'some_error', 'WP-Property error.' ), 'wp_error' );
   * </code>
   *
   * @param mixed $message
   * @param string $type
   * @param mixed $args
   * @author peshkov@UD
   * @return integer
   */
  static function log( $message = false, $type = false, $args = false ) {
    global $wpdb;

    //** We assume somebody did not want to bother with the $type */
    if( is_array( $type ) && !$args ) {
      $args = $type;
      unset( $type );
    }

    $args = wp_parse_args( $args, array(
      'post_id' => null,
      'product' => 'wpp',
      'feature' => 'core',
      'type' => $type ? $type : gettype( $message ),
      'action' => null,
      'method' => null
    ));

    return parent::log( $message, $args );

  }


  /**
   * Get the label for "Property"
   *
   * @since 1.10
   *
   */
  static function property_label($type = 'singular') {
    global $wp_post_types;

    if($type == 'plural') {
      return ($wp_post_types['property']->labels->name ? $wp_post_types['property']->labels->name : __('Properties'));
    }

    if($type == 'singular') {
      return ($wp_post_types['property']->labels->singular_name ? $wp_post_types['property']->labels->singular_name : __('Property'));
    }

  }


  /**
   * Add menu classes to menu ancestors of the current property
   *
   * @since 1.37.0
   * @author potanin@UD
   */
  static function nav_menu_css_class($classes, $item, $args) {
    global $wpdb, $post, $wp_properties, $property;

    if(!$property) {
      return $classes;
    }

    if($wp_properties['configuration']['base_slug']) {
      $property_root_page = $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_name = '{$wp_properties['configuration']['base_slug']}'");
    }

    //** Check if the currently rendered item is a child of this link */
    if($item->object_id == $property_root_page) {
      $classes[] = 'current-page-ancestor current-menu-ancestor current-menu-parent current-page-parent current_page_parent flawless_ad_hoc_menu_parent';
    }

    return $classes;

  }


  /**
   * Setup widgets and widget areas.
   *
   * @since 1.31.0
   */
  static function widgets_init() {
    global $wp_properties;

    /** Loads widgets */
    include_once WPP_Path . 'core/class_widgets.php';

    if(class_exists('Property_Attributes_Widget')) {
      register_widget("Property_Attributes_Widget");
    }

    if(class_exists('ChildPropertiesWidget')) {
      register_widget("ChildPropertiesWidget");
    }

    if(class_exists('SearchPropertiesWidget')) {
      register_widget("SearchPropertiesWidget");
    }

    if(class_exists('FeaturedPropertiesWidget')) {
      register_widget("FeaturedPropertiesWidget");
    }

    if(class_exists('GalleryPropertiesWidget')) {
      register_widget("GalleryPropertiesWidget");
    }

    if(class_exists('LatestPropertiesWidget')) {
      register_widget("LatestPropertiesWidget");
    }

    if(class_exists('OtherPropertiesWidget')) {
      register_widget("OtherPropertiesWidget");
    }

    //** Register a sidebar for each property type */
    if($wp_properties['configuration']['do_not_register_sidebars'] != 'true') {
      foreach($wp_properties['property_types'] as $property_slug => $property_title) {
        register_sidebar( array(
          'name'=> ucfirst(WPP_F::property_label('singular')) . ' ' . $property_title,
          'id' => "wpp_sidebar_{$property_slug}",
          'description' =>  sprintf(__('Sidebar located on the %s page.', 'wpp'), $property_title),
          'before_widget' => '<li id="%1$s"  class="wpp_widget %2$s">',
          'after_widget' => '</li>',
          'before_title' => '<h3 class="widget-title">',
          'after_title' => '</h3>',
        ));
      }
    }

  }


  /**
   * Setup widgets.
   *
   * @since 1.31.0
   *
   */
  static function register_post_type_and_taxonomies() {
    global $wp_properties, $_wp_post_type_features;

    // Setup taxonomies
    $wp_properties['taxonomies'] = apply_filters('wpp_taxonomies', $wp_properties['taxonomies']);

    $wp_properties['labels'] = apply_filters('wpp_object_labels', array(
      'name' => WPP_F::property_label( 'plural' ),
      'all_items' =>  sprintf(__( 'All %1$s', 'wpp'), ucfirst( WPP_F::property_label( 'plural' ) )),
      'singular_name' => WPP_F::property_label( 'singular' ),
      'add_new' => sprintf(__('Add %1$s', 'wpp'), ucfirst( WPP_F::property_label( 'singular' ) )),
      'add_new_item' => sprintf(__('Add New %1$s','wpp'), ucfirst( WPP_F::property_label( 'singular' ) )),
      'edit_item' => sprintf(__('Edit %1$s','wpp'), ucfirst( WPP_F::property_label( 'singular' ) )),
      'new_item' => sprintf(__('New %1$s','wpp'), ucfirst( WPP_F::property_label( 'singular' ) )),
      'view_item' => sprintf(__('View %1$s','wpp'), ucfirst( WPP_F::property_label( 'singular' ) )),
      'search_items' => sprintf(__('Search %1$s','wpp'), ucfirst( WPP_F::property_label( 'plural' ) )),
      'not_found' =>  sprintf(__('No %1$s found','wpp'),  WPP_F::property_label( 'plural' ) ),
      'not_found_in_trash' => sprintf(__('No %1$s found in Trash','wpp'),  WPP_F::property_label( 'plural' ) ),
      'parent_item_colon' => ''
    ));

    // Register custom post types
    register_post_type( WPP_Object , array(
      'labels' => $wp_properties['labels'],
      'public' => true,
      'exclude_from_search' => $wp_properties['configuration']['include_in_regular_search_results'] == 'true' ? false : true,
      'show_ui' => true,
      '_edit_link' => 'post.php?post=%d',
      'capability_type' => array('wpp_property','wpp_properties'),
      'hierarchical' => true,
      'rewrite' => array(
        'slug'=> $wp_properties['configuration']['base_slug']
      ),
      'query_var' => $wp_properties['configuration']['base_slug'],
      'supports' => array(
        'title',
        'editor',
        'thumbnail'
      ),
      'menu_icon' => WPP_URL . 'images/pp_menu-1.6.png'
    ));

    if( $wp_properties[ 'configuration' ][ 'enable_post_excerpt' ] == 'true' ) {
      add_post_type_support( WPP_Object, 'excerpt' );
    }

    add_post_type_support( WPP_Object, 'post-formats' );

    register_taxonomy_for_object_type( 'category', WPP_Object );

    //$_wp_post_type_features[ WPP_Object ]


    /**
    * Enable Taxonomies, excluding explicitly disabled ones
    *
    */
    foreach( (array) $wp_properties[ 'taxonomies' ] as $taxonomy => $taxonomy_data ) {

      if( !in_array( $taxonomy, (array) $wp_properties['configuration']['disabled_taxonomies'] ) ) {
        register_taxonomy( $taxonomy, WPP_Object, array(
          'hierarchical' => $taxonomy_data['hierarchical'],
          'label' => $taxonomy_data['label'],
          'labels' => $taxonomy_data['labels'],
          'query_var' => $taxonomy,
          'rewrite' => array(
            'slug' => str_replace( '_', '-', $taxonomy )
          ),
          'capabilities' => array('manage_terms' => 'manage_wpp_categories')
        ));

      }

    }

  }


  /**
   * Depreciated. Loads applicable WP-Property scripts and styles
   *
   * @action template_redirect (10)
   * @since 1.10
   */
  static function load_assets( $types = false ) {
    add_action( 'wp_enqueue_scripts', array( 'WPP_F', 'wp_enqueue_scripts' ));
  }


  /**
   * Checks if script or style have been loaded.
   *
   * @todo Add handler for styles.
   * @since 1.1.0
   *
   */
  static function is_asset_loaded($handle = false) {
    global $wp_properties, $wp_scripts;

    if(empty($handle)) {
      return;
    }

    $footer = (array) $wp_scripts->in_footer;
    $done = (array) $wp_scripts->done;

    $accepted = array_merge($footer, $done);

    if(!in_array($handle, $accepted)) {
      return false;
    }

    return true;

  }


  /**
   * PHP function to echoing a message to JS console
   *
   * @since 1.32.0
   */
  static function console_log($text = false) {
    global $wp_properties;

    if($wp_properties['configuration']['developer_mode'] != 'true') {
      return;
    }

    if(empty($text)) {
      return;
    }

    if(is_array($text) || is_object($text)) {
      $text = str_replace( "\n", '', print_r($text, true) );
    }

    //** Cannot use quotes */
    $text = str_replace('"', '-', $text);

    add_filter('wp_footer', create_function('$nothing,$echo_text = "'. $text .'"', 'echo \'<script type="text/javascript">if(typeof console == "object"){console.log("\' . $echo_text . \'");}</script>\'; '));
    add_filter('admin_footer', create_function('$nothing,$echo_text = "'. $text .'"', 'echo \'<script type="text/javascript">if(typeof console == "object"){console.log("\' . $echo_text . \'");}</script>\'; '));

  }


  /**
   * Tests if remote script or CSS file can be opened prior to sending it to browser
   *
   *
   * @version 1.26.0
   */
  static function can_get_script($url = false, $args = array()) {
    global $wp_properties;

    if(empty($url)) {
      return false;
    }

    $match = false;

    if(empty($args)){
      $args['timeout'] = 10;
    }

    $result = wp_remote_get($url, $args);


    if(is_wp_error($result)) {
      return false;
    }

    $type = $result['headers']['content-type'];

    if(strpos($type, 'javascript') !== false) {
      $match = true;
    }

    if(strpos($type, 'css') !== false) {
      $match = true;
    }

    if(!$match || $result['response']['code'] != 200) {

      if($wp_properties['configuration']['developer_mode'] == 'true') {
        WPP_F::console_log("Remote asset ($url) could not be loaded, content type returned: ". $result['headers']['content-type']);
      }

      return false;
    }

    return true;

  }

  /**
   * Gets complicated html entity e.g. Table and ou|ol
   * and removes whitespace characters include new line.
   * we should to do this before use nl2br
   *
   * @author odokienko@UD
   */
  function cleanup_extra_whitespace($content){

    $content = preg_replace_callback(
      '~<(?:table|ul|ol)[^>]*>.*?<\/(?:table|ul|ol)>~ims',
      create_function(
        '$matches',
        'return preg_replace(\'~>[\s]+<((?:t[rdh]|li|\/tr|/table|/ul))~ims\',\'><$1\',$matches[0]);'
      ),
      $content
    );

    return $content;
  }


  /**
  * Tests if remote image can be loaded, before sending to browser or TCPDF
  *
  * @version 1.26.0
  */
  static function can_get_image( $url = false ) {
    global $wp_properties;

    if(empty($url)) {
      return false;
    }

    $result = wp_remote_get($url, array( 'timeout' => 10));

    //** Image content types should always begin with 'image' (I hope) */
    if( (is_object($result) && get_class($result) == 'WP_Error') || strpos((string)$result['headers']['content-type'], 'image' ) === false ) {
      return false;
    }

    return true;

  }


/**
  * Remove non-XML characters
  *
  * @version 1.30.2
  */
  static function strip_invalid_xml($value) {

    $ret = "";
    $current;

    $bad_chars = array('\u000b');

    $value = str_replace($bad_chars, ' ', $value);

    if (empty($value)) {
      return $ret;
    }

    $length = strlen($value);

    for ($i=0; $i < $length; $i++) {

      $current = ord($value{$i});

      if (($current == 0x9) || ($current == 0xA) || ($current == 0xD) ||
          (($current >= 0x20) && ($current <= 0xD7FF)) ||
            (($current >= 0xE000) && ($current <= 0xFFFD)) ||
              (($current >= 0x10000) && ($current <= 0x10FFFF))) {

        $ret .= chr($current);

      } else {
        $ret .= " ";
      }
    }

    return $ret;
  }


  /**
  * Convert JSON data to XML if it is in JSON
  *
  * @version 1.26.0
  */
  static function json_to_xml( $json, $args = array() ) {

    if(empty($json)) {
      return false;
    }

    $args = wp_parse_args( $args, array(
      'root_name' => 'objects',
      'default_tag_name' => 'object'
    ));

    if(!class_exists('XML_Serializer')) {
      set_include_path(get_include_path() . PATH_SEPARATOR . WPP_Path.'third-party/XML/');
      @require_once 'Serializer.php';
    }

    //** If class still doesn't exist, for whatever reason, we fail */
    if(!class_exists('XML_Serializer')) {
      return false;
    }

    if( function_exists('mb_detect_encoding') ) {
      $encoding = mb_detect_encoding($json);
    } else {
      $encoding == 'UTF-8';
    }

    if($encoding == 'UTF-8') {
      $json = preg_replace('/[^(\x20-\x7F)]*/','', $json);
    }

    $json = WPP_F::strip_invalid_xml($json);

    $data = json_decode($json, true);

    //** If could not decode, return false so we presume with XML format */
    if(!is_array($data)) {
      return false;
    }

    $data[ 'objects' ] = $data;

    // An array of serializer options
    $serializer_options = array (
      'indent' => " ",
      'linebreak' => "\n",
      'addDecl' => true,
      'encoding' => 'ISO-8859-1',
      'rootName' => $args[ 'root_name'],
      'defaultTagName' => $args[ 'default_tag_name'],
      'mode' => 'simplexml'
    );

    $Serializer = &new XML_Serializer($serializer_options);

    $status = $Serializer->serialize($data);

    if ( PEAR::isError($status) ) {
      return false;
    }

    if($Serializer->getSerializedData()) {
      return $Serializer->getSerializedData();
    }

    return false;

  }



  /**
   * Add custom body class for jquery-ui
   *
   * @param array $classes
   * @return Array
   */
  function jquery_ui_body_class( $content ) {
    global $wp_scripts;

    foreach ((array)$wp_scripts->in_footer as $script){
      if (substr($script, 0, 9)=='jquery-ui'){
        return 'wpp_ui';
      }
    }

  }


  /**
   * Convert CSV to XML
   *
   * Function ported over from List Attachments Shortcode plugin.
   *
   * @version 1.32.0
   */
  static function detect_encoding($string) {

    $encoding[] = "UTF-8";
    $encoding[] = "windows-1251";
    $encoding[] = "ISO-8859-1";
    $encoding[] = "GBK";
    $encoding[] = "ASCII";
    $encoding[] = "JIS";
    $encoding[] = "EUC-JP";

    if( !function_exists('mb_detect_encoding') ) {
      return;
    }

    foreach($encoding as $single) {
       if(@mb_detect_encoding($string, $single, true)) {
        $matched = $single;
       }
    }

    return $matched ?  $matched : new WP_Error('encoding_error',__('Could not detect.', 'wpp'));


  }


  /**
   * Convert CSV to XML
   *
   * Function ported over from List Attachments Shortcode plugin.
   *
   * @version 1.32.0
   */
  static function csv_to_xml($string, $args = false) {

    $uploads = wp_upload_dir();

    $defaults = array(
      'delimiter' => ',',
      'enclosure' => '"',
      'escape' => "\\"
    );

    extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

    $temp_file = $uploads['path'] . time() . '.csv';

    file_put_contents($temp_file, $string);

    ini_set("auto_detect_line_endings", 1);
    $current_row = 1;

    $handle = fopen($temp_file, "r");
    while ( ($data = fgetcsv($handle, 10000, ",") ) !== FALSE )  {
      $number_of_fields = count($data);

      if ($current_row == 1) {
        for ($c=0; $c < $number_of_fields; $c++)  {
            $header_array[$c] = str_ireplace('-', '_', sanitize_key($data[$c]));
        }
      } else {

          $data_array = array();

          for ($c=0; $c < $number_of_fields; $c++) {

            //** Clean up values */
            $value = trim($data[$c]);
            $data_array[$header_array[$c]] = $value;

          }

          /** Removing - this removes empty values from the CSV, we want to leave them to make sure the associative array is consistant for the importer - $data_array = array_filter($data_array); */

          if(!empty($data_array)) {
            $csv[] = $data_array;
          }

      }
      $current_row++;
    }

    fclose($handle);

    unlink($temp_file);

    //** Get it into XML (We want to use json_to_xml because it does all the cleansing of weird characters) */
    $xml = WPP_F::json_to_xml(json_encode($csv));

    return $xml;

  }


  /**
   * Get filesize of a file.
   *
   * Function ported over from List Attachments Shortcode plugin.
   *
   * @version 1.25.0
   */
  static function get_filesize( $file ) {
      $bytes = filesize( $file );
      $s = array( 'b', 'Kb', 'Mb', 'Gb' );
      $e = floor( log( $bytes ) / log( 1024 ));
      return sprintf( '%.2f ' . $s[$e], ( $bytes / pow( 1024, floor( $e ) ) ));
    }


  /**
   * Set all existing property objects' property type
   *
   * @todo Add regex to check for opening and closing bracket.
   * @version 1.23.1
   */
  static function mass_set_property_type($property_type = false) {
      global $wpdb;

      if(!$property_type) {
        return false;
      }

      //** Get all properties */
      $ap = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = '" . WPP_Object . "' ");

      if(!$ap) {
        return false;
      }

      foreach($ap as $id) {

        if(update_post_meta($id, 'property_type', $property_type)) {
          $success[] = true;
        }

      }

      if(!$success) {
        return false;
      }

      return sprintf(__('Set %1$s %4$s to "%2$s" %3$s type', 'wpp'), count($success), $property_type, WPP_F::property_label( 'singular' ), WPP_F::property_label( 'plural' ));



    }


  /**
   * Attempts to detect if current page has a given shortcode
   *
   * @todo Add regex to check for opening and closing bracket.
   * @version 1.23.1
   */
  static function detect_shortcode($shortcode = false){
      global $post;

      if(!$post) {
        return false;
      }

      $shortcode = '[' . $shortcode;

      if(strpos($post->post_content, $shortcode) !== false) {
        return true;
      }

      return false;

    }


  /**
   * Reassemble address from parts
   *
   * @version 1.23.0
   */
  static function reassemble_address($property_id = false){

      if(!$property_id) {
        return false;
      }

      $address_part[] = get_post_meta($property_id, 'street_number', true);
      $address_part[] = get_post_meta($property_id, 'route',true);
      $address_part[] = get_post_meta($property_id, 'city', true);
      $address_part[] = get_post_meta($property_id, 'state',true);
      $address_part[] = get_post_meta($property_id, 'state_code', true);
      $address_part[] = get_post_meta($property_id, 'country', true);
      $address_part[] = get_post_meta($property_id, 'postal_code',true);

     $maybe_address = trim(implode(' ', $address_part));

      if(!empty($maybe_address)) {
        return $maybe_address;
      }

      return false;

    }


  /**
   * Creates a nonce, similar to wp_create_nonce() but does not depend on user being logged in
   *
   * @version 1.17.3
   */
  static function generate_nonce($action = -1){

      $user = wp_get_current_user();

      $uid = (int) $user->id;

      if(empty($uid)) {
        $uid = $_SERVER['REMOTE_ADDR'];
      }

      $i = wp_nonce_tick();

      return substr(wp_hash($i . $action . $uid, 'nonce'), -12, 10);


   }


   /**
   * Verifies nonce.
   *
   * @version 1.17.3
   */
  static function verify_nonce($nonce, $action = false){

      $user = wp_get_current_user();
      $uid = (int) $user->id;

      if(empty($uid)) {
        $uid = $_SERVER['REMOTE_ADDR'];
      }

      $i = wp_nonce_tick();

      // Nonce generated 0-12 hours ago
      if ( substr(wp_hash($i . $action . $uid, 'nonce'), -12, 10) == $nonce )
      return 1;
      // Nonce generated 12-24 hours ago
      if ( substr(wp_hash(($i - 1) . $action . $uid, 'nonce'), -12, 10) == $nonce )
      return 2;
      // Invalid nonce
      return false;

   }


  /**
   * Returns attribute information.
   *
   * Checks $wp_properties and returns a concise array of array-specific settings and attributes
   *
   * @todo Consider putting this into settings action, or somewhere, so it its only ran once, or adding caching
   * @version 1.17.3
   */
  static function get_attribute_data($attribute = false, $args = false) {
    global $wp_properties;

    if(!$attribute) {
      return;
    }

    $args = wp_parse_args( $args, array(
      'get_post_table_keys' => false,
      'uniqueness' => false
    ));

    if( wp_cache_get( $attribute, 'wpp_attribute_data' ) ) {
      return wp_cache_get( $attribute, 'wpp_attribute_data' );
    }

    $post_table_keys = array(
      'post_author',
      'post_date',
      'post_date_gmt',
      'post_content',
      'post_title',
      'post_excerpt',
      'post_status',
      'comment_status',
      'ping_status',
      'post_password',
      'post_name',
      'to_ping',
      'pinged',
      'post_modified',
      'post_modified_gmt',
      'post_content_filtered',
      'post_parent',
      'guid',
      'menu_order',
      'post_type',
      'post_mime_type',
      'comment_count'
    );

    if($args['get_post_table_keys']) {
      return $post_table_keys;
    }

    $ui_class = array( $attribute );

    if(in_array($attribute, $post_table_keys)) {
      $return['storage_type'] = 'post_table';
    }else{
      $return['storage_type'] = 'meta_key';
    }

    $return['slug'] = $attribute;

    $return['group_key'] = $_group_key = $wp_properties[ 'property_stats_groups' ][ $attribute ];
    $return['group_label'] = $_group_name = $wp_properties[ 'property_groups' ][ $_group_key ] ? $wp_properties[ 'property_groups' ][ $_group_key ][ 'name' ] : __( 'Other' , 'wpp' );

    if($wp_properties['property_stats'][$attribute]) {
      $return['label'] = $wp_properties['property_stats'][$attribute];
    }

    $return[ 'type' ] = !empty($wp_properties[ '_attribute_type' ][$attribute]) ? $wp_properties[ '_attribute_type' ][$attribute] : 'meta';

    $ui_class[] = $return[ 'type' ];

    $return['is_stat'] = (!empty($wp_properties['_attribute_type'][$attribute]) && $wp_properties['_attribute_type'][$attribute]!='detail') ? 'true' : 'false';

    if($return['is_stat']=='detail'){
      $return['input_type'] = 'textarea';
    }

    //** We've got rig of property_meta */
    /*if($wp_properties['property_meta'][$attribute]) {
      $return['is_meta'] = 'true';
      $return['storage_type'] = 'meta_key';
      $return['label'] = $wp_properties['property_meta'][$attribute];
      $return['input_type'] = 'textarea';
      $return['data_input_type'] = 'textarea';
    }*/

    if($wp_properties['searchable_attr_fields'][$attribute]) {
      $return['input_type'] = $wp_properties['searchable_attr_fields'][$attribute];
      $ui_class[] = 'search_'.$return['input_type'];
    }

    if($wp_properties['admin_attr_fields'][$attribute]) {
      $return['data_input_type'] = $wp_properties['admin_attr_fields'][$attribute];
      $ui_class[] = 'admin_'.$return['data_input_type'];
    }

    if($wp_properties['configuration']['address_attribute'] == $attribute) {
      $return['is_address_attribute'] = 'true';
      $ui_class[] = 'address_attribute';
    }

    if(is_array($wp_properties['property_inheritance'])) {
      foreach($wp_properties['property_inheritance'] as $property_type => $type_data) {
        if(in_array($attribute, $type_data)) {
          $return['inheritance'][] = $property_type;
        }
      }
    }

    $ui_class[] = $return['data_input_type'];
    if(is_array($wp_properties['predefined_values']) && ($predefined_values = $wp_properties['predefined_values'][$attribute]))  {
      $return['predefined_values'] = $predefined_values;
    }

    if(is_array($wp_properties['predefined_search_values']) && ($predefined_values = $wp_properties['predefined_search_values'][$attribute]))  {
      $return['predefined_search_values'] = $predefined_values;
    }

    if(is_array($wp_properties['sortable_attributes']) && in_array($attribute, $wp_properties['sortable_attributes']))  {
      $return['sortable'] = true;
      $ui_class[] = 'sortable';
    }

    if(is_array($wp_properties['hidden_frontend_attributes']) && in_array($attribute, $wp_properties['hidden_frontend_attributes'])) {
      $return['hidden_frontend_attribute'] = true;
      $ui_class[] = 'fe_hidden';
    }

    if(@$return[ 'type' ]=='currency') {
      $return['currency'] = true;
    }

    if(@$return[ 'type' ]=='numeric') {
      $return['numeric'] = true;
    }

    if(is_array($wp_properties['searchable_attributes']) && in_array($attribute, $wp_properties['searchable_attributes'])) {
      $return['searchable'] = true;
      $ui_class[] = 'searchable';
    }

    if ( in_array( $attribute,  array_keys((array)$wp_properties[ '_standard_attributes' ]) ) ){
      $return[ 'standard' ] = true;
      $ui_class[] = 'standard_attribute';
    }

    if(empty($return['title'])) {
      $return['title'] = WPP_F::de_slug($return['slug']);
    }

    $ui_class = array_filter(array_unique($ui_class));
    $ui_class = array_map(create_function('$class', 'return "wpp_{$class}";'),$ui_class);
    $return['ui_class'] = implode(' ',$ui_class);

    $return = apply_filters('wpp_attribute_data', array_filter( $return ) );

    wp_cache_add( $attribute, $return, 'wpp_attribute_data' );

    return $return;

  }


  /**
   * Makes sure the script is loaded, otherwise loads it
   *
   * @version 1.17.3
   */
  static function wp_enqueue_script( $handle = false ){
    global $wp_scripts;

    //** WP 3.3+ allows inline wp_enqueue_script(). Yay. */
    wp_enqueue_script($handle);

    if(!$handle) {
      return;
    }

    //** Check if already included */
    if(wp_script_is($handle, 'done')) {
      return true;
    }

    //** Check if script has dependancies that have not been loaded */
    foreach( (array) $wp_scripts->registered[$handle]->deps as $dep_handle) {
      if(!wp_script_is($dep_handle, 'done')) {
        $wp_scripts->in_footer[] = $dep_handle;
      }
    }

    //** Force script into footer */
    $wp_scripts->in_footer[] = $handle;

  }


  /**
   * Makes sure the style is loaded, otherwise loads it
   *
   * @param string $handle registered style's name
   * @author Maxim Peshkov
   */
  static function force_style_inclusion($handle = false) {
    global $wp_styles;
    static $printed_styles = array();

    if(!$handle) {
      return;
    }

    wp_enqueue_style($handle);

    //** Check if already included */
    if(wp_style_is($handle, 'done') || isset($printed_styles[$handle])) {
      return true;
    } elseif (headers_sent()) {
      $printed_styles[$handle] = true;
      wp_print_styles($handle);
    } else {
      return false;
    }

  }


  /**
   * Returns an array of all keys that can be queried using property_overview
   *
   * @version 1.17.3
   */
  static function get_queryable_keys(){
    global $wp_properties;

    $keys = array_keys($wp_properties['property_stats']);

    foreach($wp_properties['searchable_attributes'] as $attr){
      if(!in_array($attr, $keys)) {
        $keys[] = $attr;
      }
    }

    $keys[] = 'post_title';
    $keys[] = 'post_date';
    $keys[] = 'post_id';
    $keys[] = 'post_parent';
    $keys[] = 'property_type';
    $keys[] = 'featured';
    $keys[] = 'post_author';

    //* Adds filter for ability to apply custom queryable keys */
    $keys = apply_filters('get_queryable_keys', $keys);

    return $keys;
  }


    /**
    * Returns array of sortable attributes if set, or default
    *
    * @version 1.17.2
    */
  static function get_sortable_keys(){
      global $wp_properties;

      if (!empty($wp_properties['property_stats']) && $wp_properties['sortable_attributes']) {
        foreach ($wp_properties['property_stats'] as $slug => $label) {
          if(in_array($slug, $wp_properties['sortable_attributes']) ) {
            $sortable_attrs[$slug] = $label;
          }
        }
      }

      if(!empty($sortable_attrs)) {
        /* Add default 'Title' sort attribute */
        $sortable_attrs['post_title'] = __('Title', 'wpp');
        return $sortable_attrs;
      }

      //* If not set, menu_order will not be used at all if any of the attributes are marked as searchable */
      $sortable_attrs = array(
        'menu_order' => __('Default', 'wpp'),
        'post_title' => __('Title', 'wpp')
      );

      if(!empty($sortable_attrs)) {
        return $sortable_attrs;
      }
    }


  /**
   * Pre post query - for now mostly to disable caching
   *
   * Called in &get_posts() in query.php
   *
   * @version 1.26.0
   */
  static function posts_results($posts) {
    global $wpdb, $wp_query;

    //** Look for child properties */
    if(!empty($wp_query->query_vars['attachment'])) {
      $post_name = $wp_query->query_vars['attachment'];

      if($child = $wpdb->get_row("SELECT * FROM {$wpdb->posts} WHERE post_name = '{$post_name}' AND post_type = 'property' AND post_parent != '' LIMIT 0, 1")) {
        $posts[0] = $child;
        return $posts;
      }
    }

    //** Look for regular pages that are placed under base slug */
    if($wp_query->query_vars['post_type'] == 'property' && count($wpdb->get_row("SELECT * FROM {$wpdb->posts} WHERE post_name = '{$wp_query->query_vars['name']}' AND post_type = 'property'  LIMIT 0, 1")) == 0) {
      $posts[] = $wpdb->get_row("SELECT * FROM {$wpdb->posts} WHERE post_name = '{$wp_query->query_vars['name']}' AND post_type = 'page'  LIMIT 0, 1");
    }

    return $posts;

  }


  /**
   * Pre post query - for now mostly to disable caching
   *
   * @version 1.17.2
   */
  static function pre_get_posts($query){
    global $wp_properties;

    //** Make custom taxonomy work @author korotkov@ud */
    if ( $query->is_tax() ) {
      foreach( $wp_properties[ 'taxonomies' ] as $tax_key => $tax_value ) {
        if ( array_key_exists($tax_key, $query->query) ) {
          $query->query_vars['post_type'] = 'property';
        }
      }
    }

    if($wp_properties['configuration']['disable_wordpress_postmeta_cache'] != 'true') {
      return;
    }

    if($query->query_vars['post_type'] == 'property') {
      $query->query_vars['cache_results'] = false;
    }

  }


  /**
   * Format a number as numeric
   *
   * @todo Should this not be using number_format_i18n()? - potanin@UD 6/6/12
   * @version 1.16.3
   */
  static function format_numeric($content = '') {
    global $wp_properties;

    $content = trim($content);

    $dec_point  = (!empty($wp_properties['configuration']['dec_point']) ? $wp_properties['configuration']['dec_point'] : ".");
    $thousands_sep  = (!empty($wp_properties['configuration']['thousands_sep']) ? $wp_properties['configuration']['thousands_sep'] : ",");

    if(is_numeric($content)) {
      return number_format($content,0,$dec_point,$thousands_sep);
    } else {
      return $content;
    }

  }


  /**
   * Checks if an file exists in the uploads directory from a URL
   *
   * Only works for files in uploads folder.
   *
   * @todo update to handle images outside the uploads folder
   *
   * @version 1.16.3
   */
  static function file_in_uploads_exists_by_url($image_url = '') {

    if(empty($image_url)) {
      return false;
    }

    $upload_dir = wp_upload_dir();
    $image_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $image_url);

    if(file_exists($image_path)) {
      return true;
    }

    return false;

  }


  /**
   * Setup default property page. Not used by WPP, but used by third-party programs, such as Denali.
   *
   * @version 1.16.3
   */
  static function setup_default_property_page( $args = array() ) {
    global $wpdb, $wp_properties,  $user_ID;

    $args = wp_parse_args( $args, array(
      'return_defaults' => false,
    ));

    $defaults = array(
      'post_title' => ucfirst(WPP_F::property_label('plural')),
      'post_content' => '<p>[property_search]</p><p>[property_overview]</p>',
      'post_name' => 'properties',
      'post_type' => 'page',
      'post_status' => 'publish',
      'post_author' =>  $user_ID
    );

    if( $args[ 'return_defaults' ] ) {
      return $defaults;
    }

    $base_slug = $wp_properties['configuration']['base_slug'];

    //** Check if this page actually exists */
    $post_id = $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_name = '{$base_slug}'");

    //** Page already exists */
    if($post_id) {
      return $post_id;
    }

    //** Check if page with this post name already exists */
    if($post_id = $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_name = 'properties'")) {
      return array(
        'post_id' => $post_id,
        'post_name' => 'properties'
      );
    }

    $post_id = wp_insert_post( $defaults, true );

    //** If successful, get post_name of new page */
    if( !is_wp_error( $post_id ) ) {
      $post_name = $wpdb->get_var("SELECT post_name FROM {$wpdb->posts} WHERE ID = '{$post_id}'");

      return array(
        'post_id' => $post_id,
        'post_name' => $post_name
      );

    }

    return false;

  }


   /**
   * Perform WPP related things when a post is being deleted
   *
   * Makes sure all attached files and images get deleted.
   *
   *
   * @version 1.16.1
   */
  static function before_delete_post($post_id) {
    global $wpdb, $wp_properties;

    $uploads = wp_upload_dir();

    if($wp_properties['configuration']['auto_delete_attachments'] != 'true') {
      return;
    }

    //* Make sure this is a property */
    $is_property = $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE ID = {$post_id} AND post_type = 'property'");

    if(!$is_property) {
      return;
    }

    foreach( (array) $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_parent = {$post_id} AND post_type = 'attachment' ") as $attachment_id) {

      $file_path = $wpdb->get_var("SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = {$attachment_id} AND meta_key = '_wp_attached_file' ");

      wp_delete_attachment($attachment_id, true);

      if($file_path) {
        $attachment_directories[] = $uploads['basedir'] . '/' . dirname($file_path);
      }

    }

    if(is_array($attachment_directories)) {
      $attachment_directories = array_unique($attachment_directories);
      foreach($attachment_directories as $dir) {
        @rmdir($dir);
      }

    }

  }


  /**
   * Get advanced details about an image (mostly for troubleshooting)
   *
   * @todo add some sort of light validating that the the passed item here is in fact an image
   *
   */
  static function get_property_image_data($requested_id) {
    global $wpdb;

    if(empty($requested_id)) {
      return false;
    }

    ob_start();

    if(is_numeric($requested_id)) {

      $post_type = $wpdb->get_var("SELECT post_type FROM {$wpdb->posts} WHERE ID = '$requested_id'");
    } else {
      //** Try and image search */
      $image_id = $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_title LIKE '%{$requested_id}%' ");


      if($image_id) {
        $post_type = 'image';
        $requested_id = $image_id;
      }
    }

    if($post_type == 'property') {

      //** Get Property Images */
      $property = WPP_F::get_property($requested_id);

      echo sprintf(__('Requested %2$s: %1$s','wpp'),$property['post_title'], usfirst(WPP_F::property_label( 'singular' )));
      $data = get_children( array('post_parent' => $requested_id, 'post_type' => 'attachment', 'post_mime_type' => 'image',  'orderby' => 'menu_order ASC, ID', 'order' => 'DESC') );
      echo sprintf(__('%1$s%2$s has: %3$s images.'),PHP_EOL, ucfirst(WPP_F::property_label( 'singular' )),count($data));

      foreach($data as $img) {
        $image_data['ID'] = $img->ID;
        $image_data['post_title'] = $img->post_title;

        $img_meta = $wpdb->get_results("SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = '{$img->ID}'");

        foreach($img_meta as $i_m) {
          $image_data[$i_m->meta_key] = maybe_unserialize($i_m->meta_value);
        }
        print_r($image_data);

      }



    } else {

      $data = $wpdb->get_row("SELECT * FROM {$wpdb->posts} WHERE ID = '$requested_id'");
      $image_meta = $wpdb->get_results("SELECT meta_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = '$requested_id'");
      foreach($image_meta  as $m_data) {

        print_r($m_data->meta_id);
        echo "<br />";
        print_r($m_data->meta_key);
        echo "<br />";
        print_r(maybe_unserialize($m_data->meta_value));
      }

    }

    $return_data = ob_get_contents();
    ob_end_clean();

    return $return_data;

  }


  /**
   * Check if theme-specific stylesheet exists.
   *
   * get_option('template') seems better choice than get_option('stylesheet'), which returns the current theme's slug
   * which is a problem when a child theme is used. We want the parent theme's slug.
   *
   * @since 1.6
   *
   */
  static function has_theme_specific_stylesheet() {

    $theme_slug = get_option('template');

    if(file_exists( WPP_Templates . "/theme-specific/{$theme_slug}.css")) {
      return true;
    }

    return false;

  }


  /**
   * Check permissions and ownership of premium folder.
   *
   * @since 1.13
   *
   */
  static function check_premium_folder_permissions() {
    global $wp_messages;

    // If folder is writable, it's all good
    if(!is_writable(WPP_Premium . "/"))
      $writable_issue = true;
    else
      return;

    // If not writable, check if this is an ownerhsip issue
    if(function_exists('posix_getuid')) {
      if(fileowner(WPP_Path) != posix_getuid())
        $ownership_issue = true;
    } else {
      if($writable_issue)
        $wp_messages['error'][] = __('If you have problems automatically downloading premium features, it may be due to PHP not having ownership issues over the premium feature folder.','wpp');
    }

    // Attempt to take ownership -> most likely will not work
    if($ownership_issue) {
      if (@chown(WPP_Premium, posix_getuid())) {
        //$wp_messages['error'][] = __('Succesfully took permission over premium folder.','wpp');
        return;
      } else {
        $wp_messages['error'][] = __('There is an ownership issue with the premium folder, which means your site cannot download WP-Property premium features and receive updates.  Please contact your host to fix this - PHP needs ownership over the <b>wp-content/plugins/wp-property/core/premium</b> folder.  Be advised: changing the file permissions will not fix this.','wpp');
      }


    if(!$ownership_issue && $writable_issue)
      $wp_messages['error'][] = __('One of the folders that is necessary for downloading additional features for the WP-Property plugin is not writable.  This means features cannot be downloaded.  To fix this, you need to set the <b>wp-content/plugins/wp-property/core/premium</b> permissions to 0755.','wpp');

    if($wp_messages)
      return $wp_messages;

    return false;

    }

  }


  /**
   * Revalidate all addresses
   *
   * Revalidates addresses of all publishd properties.
   * If Google daily addres lookup is exceeded, breaks the function and notifies the user.
   *
   * @since 1.05
   *
   */
  static function revalidate_all_addresses($args = '') {
    global $wp_properties, $wpdb;

    set_time_limit(600);
    ob_start();

    $defaults = array(
      'property_ids' => false,
      'echo_result' => 'true',
      'skip_existing' => 'false',
      'return_geo_data' => false
    );

    extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

    if(is_array($property_ids)) {
      $all_properties = $property_ids;
    } else {
      $all_properties = $wpdb->get_col("
        SELECT ID FROM {$wpdb->posts} p
        left outer join {$wpdb->postmeta} pm on (pm.post_id=p.ID and pm.meta_key='last_address_validation')
        WHERE p.post_type = 'property' AND p.post_status = 'publish'
        ORDER by pm.meta_value DESC
        LIMIT 2490
      ");
    }

    $available_address_validation  = UD_API::available_address_validation();

    $google_map_localizations = WPP_F::draw_localization_dropdown( 'return_array=true' );

    foreach((array)$all_properties as $post_id) {
      $geo_data = false;
      $geo_data_coordinates = false;
      $current_coordinates = get_post_meta($post_id,'latitude', true) . get_post_meta($post_id,'longitude', true);

      if($skip_existing == 'true' && !empty($current_coordinates)) {
        continue;
      }

      $address = get_post_meta($post_id, $wp_properties['configuration']['address_attribute'], true);

      $coordinates = ($latitude == '0' || $longitude == '0') ? "" : array('lat'=>get_post_meta($post_id,'latitude', true),'lng'=>get_post_meta($post_id,'longitude', true));

      $manual_coordinates = get_post_meta($post_id, 'manual_coordinates', true);

      if (!empty($address)){
        $geo_data = WPP_F::geo_locate_address($address, $wp_properties['configuration']['google_maps_localization'], true);
      }

      if (!empty($coordinates) && ($manual_coordinates=='true' || empty($address))){
        $geo_data_coordinates = WPP_F::geo_locate_address($address, $wp_properties['configuration']['google_maps_localization'], true, $coordinates );
      }

      /** if Address was invalid or empty but we have valid $coordinates we use them */
      if (empty($geo_data->formatted_address) && !empty($geo_data_coordinates->formatted_address)){
        $geo_data = $geo_data_coordinates;
        /** clean up $address to remember that addres was empty or invalid*/
        $address = '';
      }

      if(!empty($geo_data->formatted_address)) {
        update_post_meta($post_id, 'address_is_formatted', true);
        update_post_meta($post_id, 'last_address_validation', time());
        update_post_meta($post_id, $wp_properties['configuration']['address_attribute'], $geo_data->formatted_address);
        update_post_meta($post_id, 'street_number', $geo_data->street_number);
        update_post_meta($post_id, 'route', $geo_data->route);
        update_post_meta($post_id, 'city', $geo_data->city);
        update_post_meta($post_id, 'county', $geo_data->county);
        update_post_meta($post_id, 'state', $geo_data->state);
        update_post_meta($post_id, 'state_code', $geo_data->state_code);
        update_post_meta($post_id, 'country', $geo_data->country);
        update_post_meta($post_id, 'country_code', $geo_data->country_code);
        update_post_meta($post_id, 'postal_code', $geo_data->postal_code);

        if (get_post_meta($post_id, 'manual_coordinates', true) != 'true' &&
          get_post_meta($post_id, 'manual_coordinates', true) != '1') {

          update_post_meta($post_id, 'latitude', $geo_data->latitude);
          update_post_meta($post_id, 'longitude', $geo_data->longitude);
        }

        if (empty($address)){
          update_post_meta($post_id, $wp_properties['configuration']['address_attribute'], WPP_F::encode_mysql_input( $geo_data->formatted_address, $wp_properties['configuration']['address_attribute']));
        }

        if($return_geo_data) {
          $return['geo_data'][$post_id] = $geo_data;
        }

        $updated[] = $post_id;

      } else {

        //** Try to figure out what went wrong */
        if ($geo_data->status=='OVER_QUERY_LIMIT' || $geo_data->status=="REQUEST_DENIED"){
          $over_query_limit[] = $post_id;
        }elseif(empty($address) && empty($geo_data)){
          $empty_address[] = $post_id;
          update_post_meta($post_id, 'address_is_formatted', false);
        }else{
          $failed[] = $post_id;
          update_post_meta($post_id, 'address_is_formatted', false);
        }
      }

    }


    $return['success'] = 'true';
    $return['message'] = sprintf(__('Updated %1$d %2$s using the %3$s localization.','wpp'),count($updated), WPP_F::property_label( 'plural' ),$google_map_localizations[$wp_properties['configuration']['google_maps_localization']]);

    if($empty_address) {
      $return['message'] .= "<br />" . sprintf(__('%1$d %2$s has empty address.','wpp'),count($empty_address),WPP_F::property_label( 'plural' ));
    }

    if($failed) {
      $return['message'] .= "<br />" . sprintf(__('%1$d %2$s could not be updated.','wpp'),count($failed),WPP_F::property_label( 'plural' ));
    }

    if($over_query_limit) {
      $return['message'] .= "<br />" . sprintf(__('%1$d %2$s was ignored because query limit was exceeded.','wpp'),count($over_query_limit),WPP_F::property_label( 'plural' ));
    }

    //** Warning Silincer */
    ob_end_clean();

    if($echo_result == 'true') {
      die(json_encode($return));
    } else {
      return $return;
    }

  }


  /**
   * Minify JavaScript
   *
   * Uses third-party JSMin if class isn't declared.
   * If WP3 is detected, class not loaded to avoid footer warning error.
   * If for some reason W3_Plugin is active, but JSMin is not found,
   * we load ours to avoid breaking property maps.
   *
   * @since 1.06
   *
   */
  static function minify_js($data) {

    if(!class_exists('W3_Plugin')) {
      include_once WPP_Path. 'third-party/jsmin.php';
    } elseif(file_exists(WP_PLUGIN_DIR . '/w3-total-cache/lib/Minify/JSMin.php')) {
      include_once WP_PLUGIN_DIR . '/w3-total-cache/lib/Minify/JSMin.php';
    } else {
      include_once WPP_Path. 'third-party/jsmin.php';
    }

    if(class_exists('JSMin')) {
      $data = JSMin::minify($data);
    }

    return $data;

  }


  /**
   * Gets image dimensions for WP-Property images.
   *
   * This function is no longer used, only here for legacy support.
   *
   * @since 1.0
   *
   */
  static function get_image_dimensions($type = false) {
    return WPP_F::image_sizes($type);
   }


  /**
   * Prevents all columns on the overview page from being enabled if nothing is configured
   *
   *
   * @since 0.721
   *
   */
  static function fix_screen_options() {
    global $current_user;

    $user_id = $current_user->data->ID;

    $current = get_user_meta($user_id, 'manageedit-propertycolumnshidden', true);

    $default_hidden[] = 'type';
    $default_hidden[] = 'price';
    $default_hidden[] = 'bedrooms';
    $default_hidden[] = 'bathrooms';
    $default_hidden[] = 'deposit';
    $default_hidden[] = 'area';
    $default_hidden[] = 'phone_number';
    $default_hidden[] = 'purchase_price';
    $default_hidden[] = 'for_sale';
    $default_hidden[] = 'for_rent';
    $default_hidden[] = 'city';
    $default_hidden[] = 'featured';
    $default_hidden[] = 'menu_order';

    if(empty($current)) {
      update_user_meta($user_id, 'manageedit-propertycolumnshidden', $default_hidden);
    }


  }


  /**
   * Determines most common property type (used for defaults when needed)
   *
   *
   * @since 0.55
   *
   */
  static function get_most_common_property_type($array = false) {
    global $wpdb, $wp_properties;

    $type_slugs = array_keys($wp_properties['property_types']);

    $top_property_type = $wpdb->get_col("
      SELECT DISTINCT(meta_value)
      FROM {$wpdb->postmeta}
      WHERE meta_key = 'property_type'
      GROUP BY meta_value
      ORDER BY  count(meta_value) DESC
    ");

    if(is_array($top_property_type)) {
      foreach($top_property_type as $slug) {
        if(isset($wp_properties['property_types'][$slug])) {
          return $slug;
        }
      }
    }

    //* No DB entries, return first property type in settings */
    return $type_slugs[0];

  }


  /**
   * Splits a query string properly, using preg_split to avoid conflicts with dashes and other special chars.
   * @param string $query string to split
   * @return Array
   */
  static function split_query_string($query) {
    /**
    * Split the string properly, so no interference with &ndash; which is used in user input.
    */
    //$data = preg_split( "/&(?!&ndash;)/", $query );
    //$data = preg_split( "/(&(?!.*;)|&&)/", $query );
    $data = preg_split( "/&(?!([a-zA-Z]+|#[0-9]+|#x[0-9a-fA-F]+);)/", $query );

    return $data;
  }


  /**
  * Handles user input, so a standard is created for supporting special characters.
  *
  * Added fix for PHP versions earlier than 4.3.0
  *
  * @updated 1.37.0 - Moved currency and numeric character removal into here form WPP_Core::save_property()
  * @param  string   $input to be converted
  * @return   string   $result
  */
  static function encode_mysql_input( $value, $meta_key = false) {

    if( $meta_key ) {

      if( $meta_key == 'latitude' || $meta_key == 'longitude' ) {
        return (float) $value;
      }

      if( $attribute_data = WPP_F::get_attribute_data( $meta_key ) ) {

        //* Remove certain characters */
        if( $attribute_data[ 'currency' ] || $attribute_data[ 'numeric' ] ) {
          $value = str_replace( array( "$", "," ), '', $value );
        }

      }

    }

    /* If PHP version is newer than 4.3.0, else apply fix. */
    if ( strnatcmp(phpversion(),'4.3.0' ) >= 0 ) {
      $value = str_replace( html_entity_decode('-', ENT_COMPAT, 'UTF-8'), '&ndash;', $value );
    } else {
      $value = str_replace( utf8_encode( html_entity_decode('-') ), '&ndash;', $value );
    }

    //** In case &ndash; is already converted and exists in its actual dash form */
    $value = str_replace('–', '&ndash;', $value);

    /* Uses WPs built in esc_html, works like a charm. */
    $value = esc_html( $value );

    return $value;

  }


  /**
   * Handles user input, so a standard is created for supporting special characters.
   *
   * @param  string   $string to be converted
   * @return   string   $result
   */
  static function decode_mysql_output( $output ) {

    $result = html_entity_decode( $output );

    return $result;

  }


  /**
   * Determines if all of the arrays values are numeric
   *
   *
   * @since 0.55
   *
   */
  static function is_numeric_range($array = false) {
    if(!is_array($array) || empty($array)) {
      return false;
    }
    foreach($array as $value) {
      if(!is_numeric($value)) {
        return false;
      }
    }
    return true;
  }


  /**
   *
   *
   */
  static function draw_property_type_dropdown($args = '') {
    global $wp_properties;

    $defaults = array('id' => 'wpp_property_type',  'name' => 'wpp_property_type',  'selected' => '');
    extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );


    if(!is_array($wp_properties['property_types']))
      return;

    $return = "<select id='$id' " . (!empty($name) ? " name='$name' " : '') . " >";
    foreach($wp_properties['property_types'] as $slug => $label)
      $return .= "<option value='$slug' " . ($selected == $slug ? " selected='true' " : "") . "'>$label</option>";
    $return .= "</select>";

    return $return;

  }


  /**
   *
   *
   */
  static function draw_property_dropdown($args = '') {
    global $wp_properties, $wpdb;

    $defaults = array('id' => 'wpp_properties',  'name' => 'wpp_properties',  'selected' => '');
    extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

    $all_properties = $wpdb->get_results("SELECT * FROM {$wpdb->posts} WHERE post_type = 'property' AND post_status = 'publish'");

    if(!is_array($all_properties))
      return;

    $return = "<select id='$id' " . (!empty($name) ? " name='$name' " : '') . " >";
    foreach($all_properties as $p_data)
      $return .= "<option value='$p_data->id' " . ($selected == $p_data->id ? " selected='true' " : "") . "'>{$p_data->post_title}</option>";
    $return .= "</select>";

    return $return;

  }


  /**
  * Render a dropdown of property attributes.
  *
  */
  static function draw_attribute_dropdown($args = '', $extra_values = false) {
    global $wp_properties, $wpdb;

    $defaults = array('id' => 'wpp_attribute',  'name' => 'wpp_attribute',  'selected' => '');
    extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

    $attributes = $wp_properties['property_stats'];

    if(is_array($extra_values)) {
     $attributes = array_merge($extra_values, $attributes);
    }

    if(!is_array($attributes))
      return;

    $return = "<select id='$id' " . (!empty($name) ? " name='$name' " : '') . " >";
      $return .= "<option value=''> - </option>";

    foreach($attributes as $slug => $label)
      $return .= "<option value='$slug' " . ($selected == $slug ? " selected='true' " : "") . ">$label ($slug)</option>";
    $return .= "</select>";

    return $return;

  }


  /**
   *
   */
  static function draw_localization_dropdown($args = '') {
    global $wp_properties, $wpdb;

    $defaults = array('id' => 'wpp_google_maps_localization',  'name' => 'wpp_google_maps_localization',  'selected' => '', 'return_array' => 'false');
    extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

    $attributes = array(
      'en' => 'English',
      'ar' => 'Arabic',
      'bg' => 'Bulgarian',
      'cs' => 'Czech',
      'de' => 'German',
      'el' => 'Greek',
      'es' => 'Spanish',
      'fi' => 'Finnish',
      'fr' => 'French',
      'it' => 'Italian',
      'ja' => 'Japanese',
      'ko' => 'Korean',
      'da' => 'Danish',
      'nl' => 'Dutch',
      'no' => 'Norwegian',
      'pt' => 'Portuguese',
      'pt-BR' => 'Portuguese (Brazil)',
      'pt-PT' => 'Portuguese (Portugal)',
      'ru' => 'Russian',
      'sv' => 'Swedish',
      'th' => 'Thai',
      'uk' => 'Ukranian');

    $attributes = apply_filters("wpp_google_maps_localizations", $attributes);

    if(!is_array($attributes))
      return;

    if($return_array == 'true')
      return $attributes;

    $return = "<select id='$id' " . (!empty($name) ? " name='$name' " : '') . " >";
    foreach($attributes as $slug => $label)
      $return .= "<option value='$slug' " . ($selected == $slug ? " selected='true' " : "") . "'>$label ($slug)</option>";
    $return .= "</select>";

    return $return;


  }


  /**
   * Removes all WPP cache files
   *
   * @return string Response
   * @version 0.1
   * @since 1.32.2
   * @author Maxim Peshkov
   */
  static function clear_cache() {
    $cache_dir = WPP_Path . 'cache/';
    if(file_exists($cache_dir)) {
      wpp_recursive_unlink($cache_dir);
    }
    return __('Cache was successfully cleared','wpp');
  }


  /**
   * Checks for updates against TwinCitiesTech.com Server
   *
   *
   * @since 0.55
   * @version 1.13.1
   *
   */
  static function feature_check($return = false) {
    global $wp_properties;

    $blogname = get_bloginfo('url');
    $blogname = urlencode(str_replace(array('http://', 'https://'), '', $blogname));
    $system = 'wpp';
    $wpp_version = get_option( "wpp_version" );

    //** Get API key - force API key update just in case */
    $api_key = WPP_F::get_api_key(array('force_check' => true, 'return' => true));

    $check_url = "http://updates.usabilitydynamics.com/?system={$system}&site={$blogname}&system_version={$wpp_version}&api_key={$api_key}";

    $response = @wp_remote_get($check_url);

     if(!$response) {
      return;
    }

    // Check for errors
    if(is_object($response) && !empty($response->errors)) {

      foreach($response->errors as $update_errrors) {
        $error_string .= implode(",", $update_errrors);
        WPP_F::log( "Feature Update Error: " . $error_string, true, array( 'method' => __METHOD__ ) );
      }

      if($return) {
        return sprintf(__('An error occurred during premium feature check: <b> %s </b>.','wpp'), $error_string);
      }

      return;
    }

    // Quit if failture
    if($response['response']['code'] != '200') {
      return;
    }

   $response = @json_decode($response['body']);

    if(is_object($response->available_features)) {

      $response->available_features = WPP_F::objectToArray($response->available_features);

      // Updata database
      $wpp_settings = get_option('wpp_settings');
      $wpp_settings['available_features'] =  WPP_F::objectToArray($response->available_features);
      update_option('wpp_settings', $wpp_settings);


    } // available_features

    if(strlen($api_key) != 40) {
      if($return) {
        if(empty($api_key)) {
          $api_key = __("The API key could not be generated.", 'wpp');
        }
        return sprintf(__('An error occurred during premium feature check: <b>%s</b>.','wpp'), $api_key);
      } else {
        return;
      }
    }


    if($response->features == 'eligible' && $wp_properties['configuration']['disable_automatic_feature_update'] != 'true') {

      // Try to create directory if it doesn't exist
      if(!is_dir(WPP_Premium)) {
        @mkdir(WPP_Premium, 0755);
      }

      // If didn't work, we quit
      if(!is_dir(WPP_Premium)) {
        continue;
      }

      // Save code
      if(is_object($response->code)) {
        foreach($response->code as $code) {

          $filename = $code->filename;
          $php_code = $code->code;
          $version = $code->version;

          // Check version

          $default_headers = array(
          'Name' => __('Feature Name','wpp'),
          'Version' => __('Version','wpp'),
          'Description' => __('Description','wpp')
          );

          $current_file = @get_file_data( WPP_Premium . "/" . $filename, $default_headers, 'plugin' );
          //echo "$filename - new version: $version , old version:$current_file[Version] |  " .  @version_compare($current_file[Version], $version) . "<br />";

          if(@version_compare($current_file['Version'], $version) == '-1') {
            $this_file = WPP_Premium . "/" . $filename;
            $fh = @fopen($this_file, 'w');
            if($fh) {
              fwrite($fh, $php_code);
              fclose($fh);

              if($current_file[Version])
                WPP_F::log(sprintf(__('WP-Property Premium Feature: %s updated to version %s from %s.','wpp'), $code->name, $version, $current_file['Version']));
              else
                WPP_F::log(sprintf(__('WP-Property Premium Feature: %s updated to version %s.','wpp'), $code->name, $version));

              $updated_features[] = $code->name;
            }
          } else {

          }


        }
      }
    }

    // Update settings
    WPP_F::settings_action(true);

    if($return && $wp_properties['configuration']['disable_automatic_feature_update'] == 'true') {
      return __('Update ran successfully but no features were downloaded because the setting is disabled. Enable in the "Data Structure" tab.','wpp');

    } elseif($return) {
      return __('Update ran successfully.','wpp');
    }
  }


  /**
   * Makes a given property featured via an AJAX call
   *
   * @updated 0.37.0
   * @since 0.721
   */
  static function toggle_featured( $post_id = false ) {
    global $current_user;

    if( !current_user_can('manage_options') || !$post_id ) {
      return;
    }

    $featured = get_post_meta($post_id, 'featured', true);

    // Check if already featured
    if( $featured == 'true' ) {
      update_post_meta($post_id, 'featured', 'false');
      $status = 'not_featured';
    } else {
      update_post_meta($post_id, 'featured', 'true');
      $status = 'featured';
    }

    return array(
      'success' => true,
      'label' => $status == 'featured' ? __( 'Unfeature','wpp' ) : __( 'Feature','wpp' ),
      'status' => $status,
      'post_id' => $post_id
    );

  }


  /**
   * Add or remove taxonomy columns
   *
   * @since ?
   */
  static function overview_columns($columns) {
    global $wp_properties, $wp_taxonomies;

    $overview_columns = apply_filters('wpp_overview_columns',  array(
      'cb' => '',
      'title' => __( 'Title', 'wpp' ),
      'overview' => __( 'Overview', 'wpp' ),
      'terms' => __( 'Terms', 'wpp' )
    ));

    if(!in_array('property_feature', array_keys($wp_taxonomies))) {
      unset($overview_columns['features']);
    }

    foreach($overview_columns as $column => $title) {
      $columns[$column] = $title;
    }

    return $columns;

  }


  /**
   * {}
   *
   */
  static function custom_attribute_columns( $columns ) {
    global $wp_properties;

    if ( !empty( $wp_properties['column_attributes'] ) ) {

      foreach( $wp_properties['column_attributes'] as $id => $slug ) {
        $columns[$slug] = __( $wp_properties['property_stats'][$slug], 'wpp' );
      }

    }

    return $columns;

  }


  /**
   * Displays dropdown of available property size images
   *
   * @since 0.54
   */
  static function image_sizes_dropdown($args = "") {
    global $wp_properties;

    $defaults = array(
      'name' => 'wpp_image_sizes',
      'selected' => 'none',
      'blank_selection_label' => ' - '
      );

    extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

    if(empty($id) && !empty($name)) {
      $id = $name;
    }

    $image_array = get_intermediate_image_sizes();

    ?>
      <select id="<?php echo $id ?>" name="<?php echo $name ?>" >
        <option value=""><?php echo $blank_selection_label; ?></option>
          <?php
            foreach($image_array as $name) {
            $sizes = WPP_F::image_sizes($name);

            if(!$sizes) {
              continue;
            }

          ?>
            <option value='<?php echo $name; ?>' <?php if($selected == $name) echo 'SELECTED'; ?>>
               <?php echo $name; ?>: <?php echo $sizes['width']; ?>px by <?php echo $sizes['height']; ?>px
            </option>
          <?php } ?>
      </select>

    <?php
  }


  /**
   * Displays dropdown of available property size images
   *
   * @since 1.37.0
   */
  static function render_dropdown( $options = array(), $selected = '', $args = array() ) {
    global $wp_properties;

    $args = wp_parse_args( $args, array(
      'name' => '',
      'blank_selection_label' => ' - '
    ));

    ?>
    <select name="<?php echo $args[ 'name' ] ?>" >
      <option value=""><?php echo $args[ 'blank_selection_label' ]; ?></option>
      <?php foreach( (array) $options as $key => $value ) { ?>
      <option value="<?php echo $key; ?>" <?php selected( $key, $selected ); ?>><?php echo esc_attr( $value ); ?></option>
      <?php } ?>
    </select>
    <?php

  }


  /**
   * Returns image sizes for a passed image size slug
   *
   * Looks through all images sizes.
   *
   * @since 0.54
   * @returns array keys: 'width' and 'height' if image type sizes found.
   */
  static function image_sizes($type = false, $args = "") {
    global $_wp_additional_image_sizes;

    extract( wp_parse_args( $args,  array(
      'return_all' => false
    ) ), EXTR_SKIP );

    if( !$type ) {
      return false;
    }

    if(isset($_wp_additional_image_sizes[$type]) && is_array($_wp_additional_image_sizes[$type])) {
      $return = $_wp_additional_image_sizes[$type];

    } else {

      if($type == 'thumbnail' || $type == 'thumb') {
        $return = array('width' => intval(get_option('thumbnail_size_w')), 'height' => intval(get_option('thumbnail_size_h')));
      }

      if($type == 'medium') {
        $return = array('width' => intval(get_option('medium_size_w')), 'height' => intval(get_option('medium_size_h')));
      }

      if($type == 'large') {
        $return = array('width' => intval(get_option('large_size_w')), 'height' => intval(get_option('large_size_h')));
      }

    }

    if(!is_array($return)) {
      return false;
    }

    if(!$return_all) {

      // Zeroed out dimensions means they are deleted
      if(empty($return['width']) || empty($return['height'])) {
        return false;
      }

      // Zeroed out dimensions means they are deleted
      if($return['width'] == '0' || $return['height'] == '0') {
        return false;
      }

    }

    return $return ? $return : false;

  }


  /**
   * Saves settings, applies filters, and loads settings into global variable
   *
   * Attached to do_action_ref_array('the_post', array(&$post)); in setup_postdata()
   *
   * As of 1.11 prevents removal of premium feature configurations that are not held in the settings page array
   *
   * 1.12 - added taxonomies filter: wpp_taxonomies
   * 1.14 - added backup from text file
   *
   * @return array|$wp_properties
   * @since 1.12
   */
  static function settings_action($force_db = false) {
    global $wp_properties, $wp_rewrite;

    // Process saving settings
    if(isset($_REQUEST['wpp_settings']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'wpp_setting_save') ) {

      // Handle backup
      if($backup_file = $_FILES['wpp_settings']['tmp_name']['settings_from_backup']) {
        $backup_contents = file_get_contents($backup_file);

        if(!empty($backup_contents)) {
          $decoded_settings = json_decode($backup_contents, true);
        }

        if(!empty($decoded_settings)) {
          $_REQUEST['wpp_settings'] = $decoded_settings;
        }
      }

      // Allow features to preserve their settings that are not configured on the settings page
      $wpp_settings = apply_filters('wpp_settings_save', $_REQUEST['wpp_settings'], $wp_properties);

      // Prevent removal of featured settings configurations if they are not present
      foreach( (array) $wp_properties['configuration']['feature_settings'] as $feature_type => $preserved_settings) {
        if(empty($_REQUEST['wpp_settings']['configuration']['feature_settings'][$feature_type])) {
          $wpp_settings['configuration']['feature_settings'][$feature_type] = $preserved_settings;
        }
      }

      if( isset( $_REQUEST[ 'ud_customer_key' ] ) ) {
        update_option( '_ud::customer_key', $_REQUEST[ 'ud_customer_key' ] );
      }

      update_option('wpp_settings', $wpp_settings);

      $wp_rewrite->flush_rules();

      // Load settings out of database to overwrite defaults from action_hooks.
      $wp_properties_db = get_option('wpp_settings');

      // Overwrite $wp_properties with database setting
      $wp_properties = array_merge($wp_properties, $wp_properties_db);

      // Reload page to make sure higher-end functions take affect of new settings
      // The filters below will be ran on reload, but the saving functions won't
      if($_REQUEST['page'] == 'property_settings'); {
        die( wp_redirect(admin_url("edit.php?post_type=property&page=property_settings&message=updated")) );
      }

    }

    if($force_db) {
      $wp_properties_db = get_option('wpp_settings');
      $wp_properties = array_merge($wp_properties, $wp_properties_db);
    }

    add_filter('wpp_image_sizes', array('WPP_F','remove_deleted_image_sizes'));

    // Filers are applied
    $wp_properties['configuration']       = apply_filters('wpp_configuration', $wp_properties['configuration']);
    $wp_properties['location_matters']       = apply_filters('wpp_location_matters', $wp_properties['location_matters']);
    $wp_properties['hidden_attributes']     = apply_filters('wpp_hidden_attributes', $wp_properties['hidden_attributes']);
    $wp_properties['descriptions']         = apply_filters('wpp_label_descriptions' , $wp_properties['descriptions']);
    $wp_properties['image_sizes']         = apply_filters('wpp_image_sizes' , $wp_properties['image_sizes']);
    $wp_properties['search_conversions']     = apply_filters('wpp_search_conversions' , $wp_properties['search_conversions']);
    $wp_properties['searchable_attributes']   = apply_filters('wpp_searchable_attributes' , $wp_properties['searchable_attributes']);
    $wp_properties['searchable_property_types'] = apply_filters('wpp_searchable_property_types' , $wp_properties['searchable_property_types']);
    $wp_properties['property_inheritance']     = apply_filters('wpp_property_inheritance' , $wp_properties['property_inheritance']);
    //** We've got rid of property_meta */
    //$wp_properties['property_meta']       = apply_filters('wpp_property_meta' , $wp_properties['property_meta']);
    $wp_properties['property_stats']       = apply_filters('wpp_property_stats' , $wp_properties['property_stats']);
    $wp_properties['property_types']       = apply_filters('wpp_property_types' , $wp_properties['property_types']);
    $wp_properties['taxonomies']         = apply_filters('wpp_taxonomies' , $wp_properties['taxonomies']);

    $wp_properties[ '_attribute_format' ] = array(
      'General' => array(
        'meta' => __( 'Free Text', 'wpp' ),
        'currency' => __( 'Currency', 'wpp' ),
        'location' => __( 'Physical Address', 'wpp' ),
        'numeric' => __( 'Number', 'wpp' ),
        'time_stamp' => __( 'Date', 'wpp' ),
        'link' => __( 'URL', 'wpp' ),
        'agent_name' => __( 'Person\'s Name', 'wpp' ),
        'boolean' => __( 'Yes / No', 'wpp' ),
        'detail' => __( 'Property Meta', 'wpp' )
      ),
      'Areas' => array(
        'room' => __( 'Room Area', 'wpp' ),
        'areas' => __( 'General Area', 'wpp' ),
      ),
      'Contact Venues' => array(
        'email' => __( 'Email Address', 'wpp' ),
        'phone_number' => __( 'Phone / Fax', 'wpp' ),
      )
    );

    $wp_properties[ 'attribute_type_standard' ] = array(

      //** Free Text */
      'meta' => array(
        'name'   => __( 'Free Text', 'wpp' ),
        'search' => array(
          'input'          => __( 'Free Text', 'wpp' ),
          'dropdown'       => __( 'Dropdown Selection', 'wpp' ),
          'multi_checkbox' => __( 'Multi-Checkbox', 'wpp' )
        ),
        'admin' => array(
          'input'          => __( 'Free Text', 'wpp' ),
          'dropdown'       => __( 'Dropdown Selection', 'wpp' )
        )
      ),

      //** Currency */
      'currency' => array(
        'name'   => __( 'Currency', 'wpp' ),
        'search' => array(
          'input'          => __( 'Free Text', 'wpp' ),
          'range_input'    => __( 'Text Input Range', 'wpp' ),
          'range_dropdown' => __( 'Range Dropdown', 'wpp' ),
          'dropdown'       => __( 'Dropdown Selection', 'wpp' ),
          'multi_checkbox' => __( 'Multi-Checkbox', 'wpp' )
        ),
        'admin' => array(
          'input'          => __( 'Free Text', 'wpp' ),
          'dropdown'       => __( 'Dropdown Selection', 'wpp' )
        )
      ),

      //** Physical Address */
      'location' => array(
        'name' => __( 'Physical Address', 'wpp' ),
        'search' => array(
          'input'          => __( 'Free Text', 'wpp' ),
          'dropdown'       => __( 'Dropdown Selection', 'wpp' ),
          'multi_checkbox' => __( 'Multi-Checkbox', 'wpp' )
        ),
        'admin' => array(
          'input'          => __( 'Free Text', 'wpp' ),
          'dropdown'       => __( 'Dropdown Selection', 'wpp' )
        )
      ),

      //** Number */
      'numeric' => array(
        'name' => __( 'Number', 'wpp' ),
        'search' => array(
          'input'          => __( 'Free Text', 'wpp' ),
          'range_input'    => __( 'Text Input Range', 'wpp' ),
          'range_dropdown' => __( 'Range Dropdown', 'wpp' ),
          'dropdown'       => __( 'Dropdown Selection', 'wpp' ),
          'multi_checkbox' => __( 'Multi-Checkbox', 'wpp' )
        ),
        'admin' => array(
          'input'          => __( 'Free Text', 'wpp' ),
          'dropdown'       => __( 'Dropdown Selection', 'wpp' )
        )
      ),

      //** Date */
      'time_stamp' => array(
        'name' => __( 'Date', 'wpp' ),
        'search' => array(
          'input'          => __( 'Free Text', 'wpp' ),
          'date_picker'    => __( 'Date Picker', 'wpp' )
        ),
        'admin' => array(
          'input'          => __( 'Free Text', 'wpp' ),
          'date_picker'    => __( 'Date Picker', 'wpp' )
        )
      ),

      //** URL */
      'link' => array(
        'name' => __( 'URL', 'wpp' ),
        'search' => array(
          'input'          => __( 'Free Text', 'wpp' ),
          'dropdown'       => __( 'Dropdown Selection', 'wpp' ),
          'multi_checkbox' => __( 'Multi-Checkbox', 'wpp' )
        ),
        'admin' => array(
          'input'          => __( 'Free Text', 'wpp' ),
          'dropdown'       => __( 'Dropdown Selection', 'wpp' )
        )
      ),

      //** Person's Name */
      'agent_name' => array(
        'name' => __( 'Person\'s Name', 'wpp' ),
        'search' => array(
          'input'          => __( 'Free Text', 'wpp' ),
          'dropdown'       => __( 'Dropdown Selection', 'wpp' ),
          'multi_checkbox' => __( 'Multi-Checkbox', 'wpp' )
        ),
        'admin' => array(
          'input'          => __( 'Free Text', 'wpp' ),
          'dropdown'       => __( 'Dropdown Selection', 'wpp' )
        )
      ),

      //** Yes / No */
      'boolean' => array(
        'name' => __( 'Yes / No', 'wpp' ),
        'search' => array(
          'checkbox'          => __( 'Single Checkbox', 'wpp' )
        ),
        'admin' => array(
          'checkbox'          => __( 'Single Checkbox', 'wpp' )
        )
      ),

      //** Property Meta */
      'detail' => array(
        'name' => __( 'Property Meta', 'wpp' ),
        'search' => array(
          'input'          => __( 'Free Text', 'wpp' )
        ),
        'admin' => array(
          'input'          => __( 'Free Text', 'wpp' )
        )
      ),

      //** Room Area */
      'room' => array(
        'name' => __( 'Room Area', 'wpp' ),
        'search' => array(
          'input'          => __( 'Free Text', 'wpp' ),
          'range_input'    => __( 'Text Input Range', 'wpp' ),
          'range_dropdown' => __( 'Range Dropdown', 'wpp' ),
          'dropdown'       => __( 'Dropdown Selection', 'wpp' ),
          'multi_checkbox' => __( 'Multi-Checkbox', 'wpp' )
        ),
        'admin' => array(
          'input'          => __( 'Free Text', 'wpp' ),
          'dropdown'       => __( 'Dropdown Selection', 'wpp' )
        )
      ),

      //** General Area */
      'areas' => array(
        'name' => __( 'General Area', 'wpp' ),
        'search' => array(
          'input'          => __( 'Free Text', 'wpp' ),
          'range_input'    => __( 'Text Input Range', 'wpp' ),
          'range_dropdown' => __( 'Range Dropdown', 'wpp' ),
          'dropdown'       => __( 'Dropdown Selection', 'wpp' ),
          'multi_checkbox' => __( 'Multi-Checkbox', 'wpp' )
        ),
        'admin' => array(
          'input'          => __( 'Free Text', 'wpp' ),
          'dropdown'       => __( 'Dropdown Selection', 'wpp' )
        )
      ),

      //** Email Address */
      'email' => array(
        'name' => __( 'Email Address', 'wpp' ),
        'search' => array(
          'input'          => __( 'Free Text', 'wpp' ),
          'dropdown'       => __( 'Dropdown Selection', 'wpp' ),
          'multi_checkbox' => __( 'Multi-Checkbox', 'wpp' )
        ),
        'admin' => array(
          'input'          => __( 'Free Text', 'wpp' ),
          'dropdown'       => __( 'Dropdown Selection', 'wpp' )
        )
      ),

      //** Phone / Fax */
      'phone_number' => array(
        'name' => __( 'Phone / Fax', 'wpp' ),
        'search' => array(
          'input'          => __( 'Free Text', 'wpp' ),
          'dropdown'       => __( 'Dropdown Selection', 'wpp' ),
          'multi_checkbox' => __( 'Multi-Checkbox', 'wpp' )
        ),
        'admin' => array(
          'input'          => __( 'Free Text', 'wpp' ),
          'dropdown'       => __( 'Dropdown Selection', 'wpp' )
        )
      )
    );

    /**
     * Specify PHP Callback Functions for Attribute Formatting
     *
     * Formatting may be called at different times.
     *
     */
    $wp_properties[ '_formatting_callback' ] = array(
      'currency' => array(
        'system' => array( 'wpp_default_api', 'currency_store' ),
        'human' => array( 'wpp_default_api', 'currency_format' )
      ),
      'time_stamp' => array(
        //'system' => 'strtotime',
        'system' => array( 'wpp_default_api', 'my_strtotime' ),
        'human' => array( 'wpp_default_api', 'date_time_format' )
      ),
      'numeric' => array(
        'system' => 'floatval',
        'human' => array( 'wpp_default_api', 'number_format' )
      ),
      'boolean' => array(
        'system' => array( 'WPP_F', 'to_boolean' ),
        'human' => array( 'WPP_F', 'from_boolean' )
      ),
      'detail' => array(
        'human' => array( 'WPP_F', 'detail_format' )
      ),
      'room' => array(
        'system' => array( 'WPP_F', 'floatval' ),
        'human' => array( 'wpp_default_api', 'area_format' )
      ),
      'areas' => array(
        'system' => array( 'WPP_F', 'floatval' ),
        'human' => array( 'wpp_default_api', 'area_format' )
      ),
      /*'location' => array(
        'system' => array( 'wpp_default_api', 'address_store' ),
        'human' => array( 'wpp_default_api', 'format_address' )
      ),*/
      'link' => array(
        'human' => array( 'wpp_default_api', 'link_format' )
      )

    );

    //** Standard Property Types */
    $wp_properties[ '_standard' ][ 'property_types' ] = array(
      'residential_property' => array(
        'en' => 'Residential'
      ),
      'farm' => array(
        'en' => 'Farm'
      )
    );

    //$wp_properties[ '_standard' ][ 'attributes' ] = array();

    $wp_properties = stripslashes_deep( $wp_properties );

    return $wp_properties;

  }


  static function remove_deleted_image_sizes($sizes) {
    global $wp_properties;

    foreach($sizes as $slug => $size) {
      if($size['width'] == '0' || $size['height'] == '0')
        unset($sizes[$slug]);

    }


    return $sizes;

  }


  /**
   * Loads property values into global $post variables.
   *
   * Attached to do_action_ref_array('the_post', array(&$post)); in setup_postdata()
   * Ran after template_redirect.
   * $property is loaded in WPP_Core::template_redirect();
   *
   * @since 0.54
   */
  static function the_post( $post ) {
    global $post, $property;

    if( $post->post_type != 'property' ) {
      return $post;
    }

    //** Update global $post object to include property specific attributes */
    $post = (object) ( (array) $post + (array) $property );

  }


  /**
   * Check for premium features and load them
   *
   * @todo Migrate into UD API and make use in other plugins that utilized premium features. - potanin@UD 8/6/12
   * @author potanin@UD
   * @updated 2.0
   * @since 0.624
   */
  static function load_premium() {
    global $wp_properties;

    $default_headers = array(
      'name' => __( 'Name','wpp' ),
      'version' => __( 'Version','wpp' ),
      'description' => __( 'Description','wpp' ),
      'minimum_core_version' => __( 'Minimum Core Version','wpp' ),
      'minimum_php_version' => __( 'Minimum PHP Version','wpp' ),
      'php_class' => __( 'Class','wpp' )
    );

    if( !is_dir( WPP_Premium )) {
      return new WP_Error( __METHOD__, __( 'WP-Property premium feature directory does not exist.', 'wpp' ) );
    }

    foreach( (array) glob( WPP_Premium . "/class_*.php" ) as $file ) {

      try {

        //** Load currently saved feature data */
        $_feature_data = array_filter( (array) wp_parse_args(
          (array) get_file_data( $file, $default_headers, 'plugin' ),
          $wp_properties['installed_features' ][ basename( $file, '.php' ) ]
        ));

        //** Feature requires name to be loaded.. */
        if( !$_feature_data[ 'name' ] ) {
          throw new Exception( sprintf( __( 'Feature not loaded - Name tag not declared in %1s.', 'wpp' ), $file ) );
        }

        //** Feature requires name to be loaded.. */
        if( $_feature_data[ 'php_class' ] && class_exists( $_feature_data[ 'php_class' ] ) ) {
          throw new Exception( sprintf( __( 'Feature not loaded - the PHP class %1s has already been declared.', 'wpp' ), $_feature_data[ 'php_class' ]  ) );
        }

        //** If feature requires minimum PHP version, and current is not sufficient, we do not load */
        if( $_feature_data[ 'minimum_php_version' ] && version_compare( PHP_VERSION, $_feature_data[ 'minimum_php_version' ] ) < 0 ) {
          throw new Exception( sprintf( __( 'Feature not loaded - requires PHP upgrade to %1s.', 'wpp' ), $_feature_data[ 'minimum_php_version' ] ) );
        }

        //** Disable feature if it requires a higher WPP version**/
        if( $_feature_data[ 'minimum_core_version' ] && version_compare( WPP_Version, $_feature_data[ 'minimum_core_version' ] ) < 0 ) {
          throw new Exception( sprintf( __( 'Feature not loaded - requires WP-Property upgrade to %1s.', 'wpp' ), $_feature_data[ 'minimum_core_version' ] ) );
        }

      } catch( Exception $e ) {
        $_feature_data[ 'disabled' ] = 'true';
        $_feature_data[ '_note' ] = $e->getMessage();
      }

      //** If feature is not disabled, load. If Debug is disabled, silence all warnings & errors */
      if( $_feature_data['disabled'] != 'true' ) {
        if( WP_DEBUG == true || WPP_Debug ) {
          include_once( $file );
        } else {
          @include_once( $file );
        }
      }

      //** Save Feature Data */
      $wp_properties[ 'installed_features' ][ basename( $file, '.php' ) ] = array_filter( (array) $_feature_data );

    }

  }


  /**
   * Check if premium feature is installed or not
   * @param string $slug. Slug of premium feature
   * @return boolean.
   */
  static function check_premium($slug) {
    global $wp_properties;

    if(empty($wp_properties['installed_features'][$slug]['version'])) {
      return false;
    }

    $file = WPP_Premium . "/" . $slug . ".php";

    $default_headers = array(
      'Name' => __('Name','wpp'),
      'Version' => __('Version','wpp'),
      'Description' => __('Description','wpp')
    );

    $plugin_data = @get_file_data( $file , $default_headers, 'plugin' );

    if(!is_array($plugin_data) || empty($plugin_data['Version'])) {
      return false;
    }

    return true;
  }


  /**
   * {}
   *
   * @since 1.10
   */
  static function check_plugin_updates() {
    global $wp_properties;

    echo WPP_F::feature_check(true);

  }


  /**
   * Run on plugin activation. As of WP 3.1 this is not ran on automatic update.
   *
   * @since 1.10
   */
  static function activation() {

  }


  /**
   * Run manually when a version mismatch is detected.
   *
   * Holds official current version designation.
   * Called in admin_init hook.
   *
   * @since 1.10
   * @version 1.13
   *
   */
  static function manual_activation() {

    $installed_ver = get_option( "wpp_version" );
    $wpp_version = WPP_Version;

    if( @version_compare($installed_ver, $wpp_version) == '-1' ) {
      // We are upgrading.

      // Unschedule event
      $timestamp = wp_next_scheduled( 'wpp_premium_feature_check' );
      wp_unschedule_event($timestamp, 'wpp_premium_feature_check' );
      wp_clear_scheduled_hook('wpp_premium_feature_check');

      // Schedule event
      wp_schedule_event(time(), 'daily', 'wpp_premium_feature_check');

      // Update option to latest version so this isn't run on next admin page load
      update_option( "wpp_version", $wpp_version );

      // Get premium features on activation
      @WPP_F::feature_check();

    }

    return;

  }


  /**
   * {}
   *
   */
  static function deactivation() {
    global $wp_rewrite;
    $timestamp = wp_next_scheduled( 'wpp_premium_feature_check' );
    wp_unschedule_event($timestamp, 'wpp_premium_feature_check' );
    wp_clear_scheduled_hook('wpp_premium_feature_check');

    $wp_rewrite->flush_rules();

  }


  /**
   * Returns array of searchable property IDs
   *
   *
   * @return array|$wp_properties
   * @since 0.621
   *
   */
  static function get_searchable_properties() {
    global $wp_properties;

    $searchable_properties = array();

    if(!is_array($wp_properties['searchable_property_types']))
      return;

    // Get IDs of all property types
    foreach($wp_properties['searchable_property_types'] as $property_type) {

      $this_type_properties = WPP_F::get_properties("property_type=$property_type");

      if(is_array($this_type_properties) && is_array($searchable_properties))
        $searchable_properties = array_merge($searchable_properties, $this_type_properties);
    }

    if(is_array($searchable_properties))
      return $searchable_properties;

    return false;

  }


  /**
   * Modifies value of specific property stat (property attribute)
   *
   * Used by filter wpp_attribute_filter in WPP_Object_List_Table::single_row();
   *
   * @param $value
   * @param $slug
   * @return $value Modified value
   */
  static function attribute_filter( $value, $slug ) {
    global $wp_properties;

    // Filter bool values
    if ( $value == 'true' ) {
      $value = __('Yes', 'wp');
    } elseif ( $value == 'false' ) {
      $value = __('No', 'wp');
    }

    // Filter currency
    if ( !empty( $wp_properties['currency_attributes'] ) ) {
      foreach( $wp_properties['currency_attributes'] as $id => $attr ) {
        if ( $slug == $attr ) {
          $value = apply_filters("wpp_stat_filter_price", $value);
        }
      }
    }

    return $value;
  }


  /**
   * Returns array of searchable attributes and their ranges
   *
   *
   * @return array|$range
   * @since 0.57
   *
   */
  static function get_search_values($search_attributes, $searchable_property_types, $cache = true, $instance_id = false) {
    global $wpdb, $wp_properties;

    if($instance_id) {
      //** Load value array from cache if it exists (search widget creates it on update */
      $cachefile = WPP_Path . 'cache/searchwidget/' . $instance_id . '.values.res';

      if($cache && is_file($cachefile) && time() - filemtime($cachefile) < 3600) {
        $result = unserialize(file_get_contents($cachefile));
      }
    }

    if(!$result) {
      $query_attributes = "";
      $query_types = "";

      //** Use the requested attributes, or all searchable */
      if(!is_array($search_attributes)) {
        $search_attributes = $wp_properties['searchable_attributes'];
      }

      if(!is_array($searchable_property_types)) {
        $searchable_property_types = explode(',', $searchable_property_types);
        foreach($searchable_property_types as $k => $v) {
          $searchable_property_types[$k] = trim($v);
        }
      }
      $searchable_property_types = "AND pm2.meta_value IN ('" . implode("','", $searchable_property_types) . "')";

      //** Cycle through requested attributes */
      foreach($search_attributes as $searchable_attribute) {

        if($searchable_attribute == 'property_type') {
          continue;
        }

        //** Load attribute data */
        $attribute_data = WPP_F::get_attribute_data($searchable_attribute);

        if($attribute_data['numeric'] || $attribute_data['currency']) {
          $is_numeric = true;
        } else {
          $is_numeric = false;
        }

        //** Check to see if this attribute has predefined values or if we have to get them from DB */
        //** If the attributes has predefind values, we use them */
        if($predefined_search_values = $wp_properties['predefined_search_values'][$searchable_attribute]) {
          $predefined_search_values = str_replace(array(', ', ' ,'), array(',', ','), trim($predefined_search_values));
          $predefined_search_values = explode(',', $predefined_search_values);

          if(is_array($predefined_search_values)) {
            foreach($predefined_search_values as $value) {
              $range[$searchable_attribute][] = $value;
            }
          } else {
            $range[$searchable_attribute][] = $predefined_search_values;
          }

        } else {

          //** No predefined value exist */
          $db_values = $wpdb->get_col("
            SELECT DISTINCT(pm1.meta_value)
            FROM {$wpdb->postmeta} pm1
            LEFT JOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id
            WHERE pm1.meta_key = '{$searchable_attribute}' AND pm2.meta_key = 'property_type'
            $searchable_property_types
            AND pm1.meta_value != ''
            ORDER BY " . ($is_numeric ? 'ABS(' : ''). "pm1.meta_value" . ($is_numeric ? ')' : ''). " ASC
          ");

          //* Get all available values for this attribute for this property_type */
          $range[$searchable_attribute] = $db_values;

        }

        //** Get unique values*/
        if(is_array($range[$searchable_attribute])) {
          $range[$searchable_attribute] = array_unique($range[$searchable_attribute]);
        } else {
          //* This should not happen */
        }

        foreach($range[$searchable_attribute] as $key => $value) {

          $original_value = $value;

          // Clean up values if a conversion exists
          $value = WPP_F::do_search_conversion($searchable_attribute, trim($value));

          // Fix value with special chars. Disabled here, should only be done in final templating stage.
          // $value = htmlspecialchars($value, ENT_QUOTES);

          //* Remove bad characters signs if attribute is numeric or currency */
          if($is_numeric) {
            $value = str_replace(array(",", "$"), '', $value);
          }

          //** Put cleaned up value back into array */
          $range[$searchable_attribute][$key] = $value;

        }

        //** Sort values */
        sort($range[$searchable_attribute], SORT_REGULAR);

      } //** End single attribute data gather */

      $result = $range;

      if($cachefile) {
        $cachedir = dirname($cachefile);
        if (! is_dir($cachedir)) {
          wp_mkdir_p($cachedir);
        }

        @file_put_contents($cachefile, serialize($result));
      }
    }

    return $result;
  }


  /**
   * Check if a search converstion exists for a attributes value
   */
  static function do_search_conversion($attribute, $value, $reverse = false)  {
    global $wp_properties;

    // First, check if any conversions exists for this attribute, if not, return value
    if(count($wp_properties['search_conversions'][$attribute]) < 1) {
      return $value;
    }

    // If reverse is set to true, means we are trying to convert a value to integerer (most likely),
    // For isntance: in "bedrooms", $value = 0 would be converted to "Studio"
    if($reverse) {

      $flipped_conversion = array_flip($wp_properties['search_conversions'][$attribute]);

      if(!empty($flipped_conversion[$value])) {
        return $flipped_conversion[$value];
      }

    }
    // Need to $conversion == '0' or else studios will not work, since they have 0 bedrooms
    $conversion = $wp_properties['search_conversions'][$attribute][$value];
    if($conversion == '0' || !empty($conversion))
      return $conversion;

    // Return value in case something messed up
    return $value;

  }


  /**
   * Primary static function for queries properties  based on type and attributes
   *
   * @todo There is a limitation when doing a search such as 4,5+ then mixture of specific and open ended search is not supported.
   * @since 1.08
   *
   * @param string/ $args
   */
  static function get_properties( $args = "", $total = false ) {
    global $wpdb, $wp_properties, $wpp_query;

    // Non post_meta fields
    $non_post_meta = array(
      'post_title'  => 'like',
      'post_status' => 'equal',
      'post_author' => 'equal',
      'ID' => 'equal',
      'post_parent' => 'equal',
      'post_date'   => 'date'
    );

    $capture_sql_args = array('limit_query');

    //** added to avoid range and "LIKE" searches on single numeric values *
    if(is_array($args)) {
      foreach($args as $thing => $value) {

        if(in_array($thing, $capture_sql_args)) {
          $sql_args[$thing] = $value;
          unset($args[$thing]);
          continue;
        }

        // unset empty filter options
        if ( empty( $value ) ) {
          unset($args[$thing]);
          continue;
        }

        if ( is_array( $value ) ) {
          $value = implode(',', $value);
        }
        $value = trim($value);

        $original_value = $value;

        //** If not CSV and last character is a +, we look for open-ended ranges, i.e. bedrooms: 5+
        if(substr($original_value, -1, 1) == '+' && !strpos($original_value, ',')) {
          //** User requesting an open ended range, we leave it off with a dash, i.e. 500- */
          $args[$thing] = str_replace('+', '', $value) .'-';
        } elseif(is_numeric($value)) {
          //** If number is numeric, we do a specific serach, i.e. 500-500 */
          if ( !key_exists($thing, $non_post_meta) ) {
            $args[$thing] = $value .'-'. $value;
          }
        } elseif(is_string($value)) {
          $args[$thing] = $value;
        }
      }
    }

    $defaults = array(
      'property_type' => 'all'
    );

    $query = wp_parse_args( $args, $defaults );
    $query = apply_filters('wpp_get_properties_query', $query);
    $query_keys = array_keys($query);

    //** Search by non meta values */
    $additional_sql = '';

    //** Show 'publish' posts if status is not specified */
    if ( !key_exists( 'post_status', $query ) ) {
      $additional_sql .= " AND p.post_status = 'publish' ";
    } else {
      if ( $query['post_status'] != 'all' ) {
        if( strpos( $query['post_status'], ',' ) === false ) {
          $additional_sql .= " AND p.post_status = '{$query['post_status']}' ";
        } else {
          $post_status = explode( ',', $query['post_status'] );
          foreach ( $post_status as &$ps ) {
            $ps = trim( $ps );
          }
          $additional_sql .= " AND p.post_status IN ('" . implode( "','", $post_status ) . "') ";
        }
      }
      unset($query['post_status']);
    }

    foreach( $non_post_meta as $field => $condition ) {
      if ( key_exists( $field, $query ) ) {
        if ( $condition == 'like' ) {
          $additional_sql .= " AND p.$field LIKE '%{$query[ $field ]}%' ";
        }
        if ( $condition == 'equal' ) {
          $additional_sql .= " AND p.$field = '{$query[ $field ]}' ";
        }
        if ( $condition == 'date' ) {
          $additional_sql .= " AND YEAR(p.$field) = ".substr($query[ $field ], 0, 4)." AND MONTH(p.$field) = ".substr($query[ $field ], 4, 2)." ";
        }
        unset( $query[ $field ] );
      }
    }

    if(!empty($sql_args['limit_query'])) {
      $sql_args['starting_row'] = ($sql_args['starting_row'] ? $sql_args['starting_row'] : 0);
      $limit_query = "LIMIT {$sql_args[starting_row]}, {$sql_args[limit_query]};";

    } elseif (substr_count($query['pagi'], '--')) {
      $pagi = explode('--', $query['pagi']);
      if(count($pagi) == 2 && is_numeric($pagi[0]) && is_numeric($pagi[1])) {
        $limit_query = "LIMIT $pagi[0], $pagi[1];";
      }
    }

    /** Handles the sort_by parameter in the Short Code */
    if( $query['sort_by'] ) {
      $sql_sort_by = $query['sort_by'];
      $sql_sort_order = ($query['sort_order']) ? strtoupper($query['sort_order']) : 'ASC';
    } else {
      $sql_sort_by = 'post_date';
      $sql_sort_order = 'ASC';
    }

    //** Unsert arguments that will conflict with attribute query */
    unset( $query['pagi'] );
    unset( $query['pagination'] );
    unset( $query['limit_query'] );
    unset( $query['starting_row'] );
    unset( $query['sort_by'] );
    unset( $query['sort_order'] );

    // Go down the array list narrowing down matching properties
    foreach ($query as $meta_key => $criteria) {

      $specific = '';
      $criteria = WPP_F::encode_mysql_input( $criteria, $meta_key);

      // Stop filtering (loop) because no IDs left
      if (isset($matching_ids) && empty($matching_ids)) {
        break;
      }

      if (substr_count($criteria, ',') || substr_count($criteria, '&ndash;') || substr_count($criteria, '--')) {
        if (substr_count($criteria, ',') && !substr_count($criteria, '&ndash;')) {
          $comma_and = explode(',', $criteria);
        }
        if (substr_count($criteria, '&ndash;') && !substr_count($criteria, ',')) {
          $cr = explode('&ndash;', $criteria);

          // Check pieces of criteria. Array should contains 2 integer's elements
          // In other way, it's just value of meta_key
          if(count($cr) > 2 || ((int)$cr[0] == 0 && (int)$cr[1] == 0)) {
            $specific = $criteria;
          } else {
            $hyphen_between = $cr;
            // If min value doesn't exist, set 1
            if(empty($hyphen_between[0])) {
              $hyphen_between[0] = 1;
            }
          }
        }
      } else {
        $specific = $criteria;
      }

      if (!$limit_query) {
        $limit_query = '';
      }

      switch ($meta_key) {

        case 'property_type':

          // Get all property types
          if ($specific == 'all') {
            if (isset($matching_ids)) {
              $matching_id_filter = implode("' OR ID ='", $matching_ids);
              $matching_ids = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE (ID ='$matching_id_filter') AND post_type = 'property'");
            } else {
              $matching_ids = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'property'");
            }
            break;
          }

          //** If comma_and is set, $criteria is ignored, otherwise $criteria is used */
          $property_type_array = is_array($comma_and) ? $comma_and : array($specific);

          //** Make sure property type is in slug format */
          foreach($property_type_array as $key => $this_property_type) {
            foreach($wp_properties['property_types'] as $pt_key => $pt_value) {
              if(strtolower($pt_value) == strtolower($this_property_type)) {
                $property_type_array[$key] = $pt_key;
              }
            }
          }

          if ( $comma_and ) {
            //** Multiple types passed */
            $where_string = implode("' OR meta_value ='", $property_type_array);
          } else {
            //** Only on type passed */
            $where_string = $property_type_array[0];
          }


          // See if mathinc_ids have already been filtered down
          if ( isset($matching_ids) ) {
            $matching_id_filter = implode("' OR post_id ='", $matching_ids);
            $matching_ids = $wpdb->get_col("SELECT post_id FROM {$wpdb->postmeta} WHERE (post_id ='$matching_id_filter') AND (meta_key = 'property_type' AND (meta_value ='$where_string'))");
          } else {
            $matching_ids = $wpdb->get_col("SELECT post_id FROM {$wpdb->postmeta} WHERE (meta_key = 'property_type' AND (meta_value ='$where_string'))");
          }

        break;

        default:

          // Get all properties for that meta_key
          if ($specific == 'all' && !$comma_and && !$hyphen_between) {

            if (isset($matching_ids)) {
              $matching_id_filter = implode("' OR post_id ='", $matching_ids);
              $matching_ids = $wpdb->get_col("SELECT post_id FROM {$wpdb->postmeta} WHERE (post_id ='$matching_id_filter') AND (meta_key = '$meta_key')");
              $wpdb->print_error();
            } else {
              $matching_ids = $wpdb->get_col("SELECT post_id FROM {$wpdb->postmeta} WHERE (meta_key = '$meta_key')");
            }
            break;

          } else {

            if ( $comma_and ) {
              $where_and = "(meta_value ='" . implode("' OR meta_value ='", $comma_and)."')";
              $specific = $where_and;
            }

            if ( $hyphen_between ) {
              // We are going to see if we are looking at some sort of date, in which case we have a special MySQL modifier
              $adate = false;
              if(preg_match('%\d{1,2}/\d{1,2}/\d{4}%i', $hyphen_between[0])) $adate = true;

              if(!empty($hyphen_between[1])) {

                if(preg_match('%\d{1,2}/\d{1,2}/\d{4}%i', $hyphen_between[1])){
                  foreach($hyphen_between as $key => $value) {
                    $hyphen_between[$key] = "STR_TO_DATE('{$value}', '%c/%e/%Y')";
                  }
                  $where_between = "STR_TO_DATE(`meta_value`, '%c/%e/%Y') BETWEEN " . implode(" AND ", $hyphen_between)."";
                } else {
                  $where_between = "`meta_value` BETWEEN " . implode(" AND ", $hyphen_between)."";
                }

              } else {

                if($adate) {
                  $where_between = "STR_TO_DATE(`meta_value`, '%c/%e/%Y') >= STR_TO_DATE('{$hyphen_between[0]}', '%c/%e/%Y')";
                } else {
                  $where_between = "`meta_value` >= $hyphen_between[0]";
                }

              }
              $specific = $where_between;
            }

            if ($specific == 'true') {
              // If properties data were imported, meta value can be '1' instead of 'true'
              // So we're trying to find also '1'
              $specific = "meta_value IN ('true', '1')";
            } elseif(!substr_count($specific, 'meta_value')) {
              // Adds conditions for Searching by partial value
              $s = explode(' ', trim($specific));
              $specific = '';
              $count = 0;
              foreach($s as $p) {
                if($count > 0) {
                  $specific .= " AND ";
                }
                $specific .= "meta_value LIKE '%{$p}%'";
                $count++;
              }
            }

            if (isset($matching_ids)) {
              $matching_id_filter = implode(",", $matching_ids);
              $matching_ids = $wpdb->get_col("SELECT post_id FROM {$wpdb->postmeta} WHERE post_id IN ($matching_id_filter) AND meta_key = '$meta_key' AND $specific");
            } else {
              $matching_ids = $wpdb->get_col("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '$meta_key' AND $specific $sql_order");
            }

          }
          break;

      } // END switch

      unset( $comma_and );
      unset( $hyphen_between );


    } // END foreach


    // Return false, if there are any result using filter conditions
    if (empty($matching_ids)) {
      return false;
    }

    // Remove duplicates
    $matching_ids = array_unique( $matching_ids );
    // Sorts the returned Properties by the selected sort order
    if ($sql_sort_by &&
        $sql_sort_by != 'menu_order' &&
        $sql_sort_by != 'post_date' &&
        $sql_sort_by != 'post_title' ) {

      /*
      * Determine if all values of meta_key are numbers
      * we use CAST in SQL query to avoid sort issues
      */
      if(self::meta_has_number_data_type ($matching_ids, $sql_sort_by)) {
        $meta_value = "CAST(meta_value AS DECIMAL(20,3))";
      } else {
        $meta_value = "meta_value";
      }

      $result = $wpdb->get_col("
        SELECT p.ID , (SELECT pm.meta_value FROM {$wpdb->postmeta} AS pm WHERE pm.post_id = p.ID AND pm.meta_key = '{$sql_sort_by}' LIMIT 1) as meta_value
          FROM {$wpdb->posts} AS p
          WHERE p.ID IN (" . implode(",", $matching_ids) . ")
          {$additional_sql}
          ORDER BY {$meta_value} {$sql_sort_order}
          {$limit_query}");

      // Stores the total Properties returned
      if ($total) {
        $total = count($wpdb->get_col("
          SELECT p.ID
            FROM {$wpdb->posts} AS p
            WHERE p.ID IN (" . implode(",", $matching_ids) . ")
            {$additional_sql}"));
      }

    } else {

      $result = $wpdb->get_col("
        SELECT ID FROM {$wpdb->posts } AS p
        WHERE ID IN (" . implode(",", $matching_ids) . ")
        $additional_sql
        ORDER BY $sql_sort_by $sql_sort_order
        $limit_query");

      // Stores the total Properties returned
      if($total) {
        $total = count($wpdb->get_col("
          SELECT ID FROM {$wpdb->posts} AS p
          WHERE ID IN (" . implode(",", $matching_ids) . ")
          {$additional_sql}"));
      }
    }

    if( !empty( $result ) ) {
      $return = array();
      if(!empty($total)) {
        $return['total'] = $total;
        $return['results'] = $result;
      } else {
        $return = $result;
      }

      return $return;
    }

    return false;
  }


  /**
   * Prepares Request params for get_properties() function
   *
   * @param array $attrs
   * @return array $attrs
   */
  function prepare_search_attributes($attrs) {
    global $wp_properties;

    $prepared = array();

    $non_numeric_chars = apply_filters('wpp_non_numeric_chars', array('-', '$', ','));

    foreach($attrs as $search_key => $search_query) {

      //** Fix search form passed paramters to be usable by get_properties();
      if(is_array($search_query)) {

        //** Array variables are either option lists or minimum and maxim variables
        if(is_numeric(array_shift(array_keys($search_query)))) {

          //** get regular arrays (non associative) */
          $search_query = implode(',', $search_query);
        } elseif(is_array($search_query['options'])) {

          //** Get queries with options */
          $search_query = implode(',', $search_query['options']);
        } elseif(in_array('min', array_keys($search_query))) {

          //** Get arrays with minimum and maxim ranges */

          //* There is no range if max value is empty and min value is -1 */
          if($search_query['min'] == '-1' && empty($search_query['max'])) {
            $search_query = '-1';
          } else {
          //* Set range */
            //** Ranges are always numeric, so we clear it up */
            foreach($search_query as $range_indicator => $value) {
              $search_query[$range_indicator] = str_replace($non_numeric_chars, '', $value);
            }

            if(empty($search_query['min']) && empty($search_query['max'])) {
              continue;
            }

            if(empty($search_query['min'])) {
              $search_query['min'] = '0';
            }

            if(empty($search_query['max'])) {
              $search_query = $search_query['min'] . '+';
            } else {
              $search_query = str_replace($non_numeric_chars, '', $search_query['min']) . '-' .  str_replace($non_numeric_chars, '', $search_query['max']);
            }
          }
        }
      }

      if(is_string($search_query)) {
        if($search_query != '-1' && $search_query != '-') {
          $prepared[$search_key] = trim($search_query);
        }
      }

      //** Date Picker implementation @author korotkov@ud */
      if ( !empty( $wp_properties['_attribute_type'] ) && is_array( $wp_properties['_attribute_type'] ) ) {
        if ( array_key_exists( $search_key, $wp_properties['_attribute_type'] ) ) {
          if ( $wp_properties['_attribute_type'][$search_key] == 'time_stamp' ) {
            $prepared[$search_key] = strtotime(trim($search_query));
          }
        }
      }

    }

    return $prepared;
  }


  /**
   * Returns array of all values for a particular attribute/meta_key
   */
  static function get_all_attribute_values($slug) {
    global $wpdb;

    // Non post_meta fields
    $non_post_meta = array(
      'post_title',
      'post_status',
      'post_author',
      'post_date'
    );

    if ( !in_array($slug, $non_post_meta) )
      $prefill_meta = $wpdb->get_col("SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '$slug'");
    else
      $prefill_meta = $wpdb->get_col("SELECT $slug FROM {$wpdb->posts} WHERE post_type = 'property' AND post_status != 'auto-draft'");
    /**
    * @todo check if this condition is required - Anton Korotkov
    */
    /*if(empty($prefill_meta[0]))
      unset($prefill_meta);*/

    $prefill_meta = apply_filters('wpp_prefill_meta', $prefill_meta, $slug);

    if(count($prefill_meta) < 1)
      return false;

    $return = array();
    // Clean up values
    foreach($prefill_meta as $meta) {

      if(empty($meta))
        continue;

      $return[] = $meta;

    }

    if ( !empty( $return ) && !empty( $return ) ) {
      // Remove duplicates
      $return = array_unique($return);

      sort($return);

    }

    return $return;


  }


  /**
   * Load property information into an array or an object
   *
   * @version 1.11 Added support for multiple meta values for a given key
   *
   * @since 1.11
   * @version 1.14 - fixed problem with drafts
   * @todo Code pertaining to displaying data should be migrated to prepare_property_for_display() like :$real_value = nl2br($real_value);
   * @todo Fix the long dashes - when in latitude or longitude it breaks it when using static map
   *
   */
  static function get_property( $id, $args = false ) {
    global $wp_properties, $wpdb;

    $id = trim($id);

    $_args = wp_parse_args( $args, array(
      'quick' => false,
      'get_children' => 'true',
      'return_object' => 'false',
      'load_gallery' => 'true',
      'load_thumbnail' => 'true',
      'allow_multiple_values' => ($wp_properties['configuration']['allow_multiple_attribute_values'] == 'true' ? 'true' : 'false'),
      'load_parent' => 'true'
    ));

    extract( $_args, EXTR_SKIP );

    /**
     * Fast Access Property Object Generation
     *
     * Only returns primary keys and meta keys, without any filters or formatting.
     *
     */
    if( $_args[ 'quick' ] ) {
      $return = array_filter( $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE ID = %d;", $id ), ARRAY_A ) );
      if( empty( $return ) ) {
        return false;
      }
      foreach( $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT( meta_key ), meta_value FROM {$wpdb->postmeta} WHERE post_id = %d ORDER BY meta_key;", $id ) ) as $row ) {
        $return[ 'post_meta' ][ $row->meta_key ] = maybe_unserialize( $row->meta_value );
      }
      return $return;
    }

    if( $return = wp_cache_get( $id.$args ) ) {
      return $return;
    }

    self::timer_start( __METHOD__ );

    $post = get_post($id, ARRAY_A);

    if( empty( $post ) || $post['post_type'] != 'property' ) {
      return false;
    }

    //** Figure out what all the editable attributes are, and get their keys */
    $wp_properties['property_stats'] = (is_array($wp_properties['property_stats']) ? $wp_properties['property_stats'] : array());
    $editable_keys = array_keys($wp_properties['property_stats']);

    /* Get all meta keys and values (order_by is important here - it keeps the order) */
    foreach ( (array) $wpdb->get_results( "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = {$id} ORDER BY meta_id ASC " ) as $count => $meta_data ) {

      //** We can assume that if has _ prefix it's a built-in WP key */
      if ( '_' == $meta_data->meta_key{0} ) {
        continue;
      }

      if( !is_array( $return[$meta_data->meta_key] ) ) {
        $return[$meta_data->meta_key] = array();
      }

      //** Fix for boolean values */
      switch( $meta_data->meta_value ) {

        case 'true':
          $return[$meta_data->meta_key][] = true;
        break;

        case 'false':
          $return[$meta_data->meta_key][] = false;
        break;

        default:
          $return[$meta_data->meta_key][] =  $meta_data->meta_value;
        break;

      }

      //** If multiple values are NOT allowed, we get the last added key */
      if( $allow_multiple_values != 'true' ) {
        $return[ $meta_data->meta_key ] = $return[$meta_data->meta_key][0];
      }

    }

    $return = array_merge( (array)$return, $post);

    //** Regardless of if multiple attribute values are allowed, some attributes can only have one value */
    foreach(apply_filters('wpp_single_value_attributes', array('property_type')) as $enforced_key) {
      $return[$enforced_key] = is_array($return[$enforced_key]) ? $return[$enforced_key][0] : $return[$enforced_key];
    }

    //** Make sure certain keys were not messed up by custom attributes */
    $return['system'] = array();
    $return['gallery'] = array();

    /**
     * Figure out what the thumbnail is, and load all sizes
     *
     */
    if($load_thumbnail == 'true') {
      $wp_image_sizes = get_intermediate_image_sizes();

      $thumbnail_id = get_post_meta( $id, '_thumbnail_id', true );
      $attachments = get_children( array('post_parent' => $id, 'post_type' => 'attachment', 'post_mime_type' => 'image',  'orderby' => 'menu_order ASC, ID', 'order' => 'DESC') );

      if ($thumbnail_id) {
        foreach($wp_image_sizes as $image_name) {
          $this_url = wp_get_attachment_image_src( $thumbnail_id, $image_name , true );
          $return['images'][$image_name] = $this_url[0];
        }

        $featured_image_id = $thumbnail_id;

      } elseif ($attachments) {
        foreach ( $attachments as $attachment_id => $attachment ) {

          foreach($wp_image_sizes as $image_name) {
            $this_url =  wp_get_attachment_image_src( $attachment_id, $image_name , true );
            $return['images'][$image_name] = $this_url[0];
          }

          $featured_image_id = $attachment_id;
          break;
        }
      }

      if($featured_image_id) {
        $return['featured_image'] = $featured_image_id;

        $image_title = $wpdb->get_var("SELECT post_title  FROM {$wpdb->posts} WHERE ID = '{$featured_image_id}' ");

        $return['featured_image_title'] = $image_title;
        $return['featured_image_url'] = wp_get_attachment_url($featured_image_id);
      }

    }


    /**
     * Load all attached images and their sizes
     *
     */
    if($load_gallery == 'true') {

      // Get gallery images
      if($attachments) {
        foreach ( $attachments as $attachment_id => $attachment ) {
          $return['gallery'][$attachment->post_name]['post_title'] = $attachment->post_title;
          $return['gallery'][$attachment->post_name]['post_excerpt'] = $attachment->post_excerpt;
          $return['gallery'][$attachment->post_name]['post_content'] = $attachment->post_content;
          $return['gallery'][$attachment->post_name]['attachment_id'] = $attachment_id;
          foreach($wp_image_sizes as $image_name) {
            $this_url =  wp_get_attachment_image_src( $attachment_id, $image_name , true );
            $return['gallery'][$attachment->post_name][$image_name] = $this_url[0];
          }
        }
      } else {
        $return['gallery'] = false;
      }
    }


   /**
    *  Load parent if exists and inherit Parent's atttributes.
    *
    */
    if($load_parent == 'true' && $post['post_parent']) {

      $return['is_child'] = true;

      $parent_object = WPP_F::get_property($post['post_parent'], "get_children=false");

      $return['parent_id'] = $post['post_parent'];
      $return['parent_link'] = $parent_object['permalink'];
      $return['parent_title'] = $parent_object['post_title'];

      // Inherit things
      if(is_array($wp_properties['property_inheritance'][$return['property_type']])) {
        foreach($wp_properties['property_inheritance'][$return['property_type']] as $inherit_attrib) {
          if(!empty($parent_object[$inherit_attrib]) && empty($return[$inherit_attrib])) {
            $return[$inherit_attrib] = $parent_object[$inherit_attrib];
          }
        }
      }
    }


    /**
     * Load Children and their attributes
     *
     */
    if($get_children == 'true') {

      //** Calculate variables if based off children if children exist */
      $children = $wpdb->get_col("SELECT ID FROM {$wpdb->posts} WHERE  post_type = 'property' AND post_status = 'publish' AND post_parent = '{$id}' ORDER BY menu_order ASC ");

      if(count($children) > 0) {

        //** Cycle through children and get necessary variables */
        foreach($children as $child_id) {

          $child_object = WPP_F::get_property($child_id, "load_parent=false");
          $return['children'][$child_id] = $child_object;

          //** Save child image URLs into one array for quick access */
          if(!empty($child_object['featured_image_url'])) {
            $return['system']['child_images'][$child_id] = $child_object['featured_image_url'];
          }

          //** Exclude variables from searchable attributes (to prevent ranges) */
          $excluded_attributes = $wp_properties['geo_type_attributes'];
          $excluded_attributes[] = $wp_properties['configuration']['address_attribute'];

          foreach($wp_properties['searchable_attributes'] as $searchable_attribute) {

            $attribute_data = WPP_F::get_attribute_data($searchable_attribute);

            if($attribute_data['numeric'] || $attribute_data['currency']) {

              if(!empty($child_object[$searchable_attribute]) && !in_array($searchable_attribute, $excluded_attributes)) {
                $range[$searchable_attribute][]  = $child_object[$searchable_attribute];
              }

            }
          }
        }

        //* Cycle through every type of range (i.e. price, deposit, bathroom, etc) and fix-up the respective data arrays */
        foreach((array)$range as $range_attribute => $range_values) {

          //* Cycle through all values of this range (attribute), and fix any ranges that use dashes */
          foreach($range_values as $key => $single_value) {

            //* Remove dollar signs */
            $single_value = str_replace("$" , '', $single_value);

            //* Fix ranges */
            if(strpos($single_value, '&ndash;')) {

              $split = explode('&ndash;', $single_value);

              foreach($split as $new_single_value)

                if(!empty($new_single_value)) {
                  array_push($range_values, trim($new_single_value));
                }

              //* Unset original value with dash */
              unset($range_values[$key]);

            }
          }

          //* Remove duplicate values from this range */
          $range[$range_attribute] =  array_unique($range_values);

          //* Sort the values in this particular range */
          sort($range[$range_attribute]);

          if(count($range[$range_attribute] ) < 2) {
            $return[$range_attribute] = $range[$range_attribute][0];
          }

          if(count($range[$range_attribute]) > 1) {
            $return[$range_attribute] = min($range[$range_attribute]) . " - " .  max($range[$range_attribute]);
          }

          //** If we end up with a range, we make a note of it */
          if(!empty($return[$range_attribute])) {
            $return['system']['upwards_inherited_attributes'][] = $range_attribute;
          }

        }

      }
    }


    if(!empty($return['location']) && !in_array('address', $editable_keys) && !isset($return['address'])) {
      $return['address'] = $return['location'];
    }

    $return['_wpp::gpid'] = WPP_F::maybe_set_gpid($id);
    $return['permalink'] = get_permalink($id);

    //** Make sure property_type stays as slug, or it will break many things:  (widgets, class names, etc)  */
    $return['property_type_label'] = $wp_properties['property_types'][$return['property_type']];

    if(empty($return['property_type_label'])) {
      foreach($wp_properties['property_types'] as $pt_key => $pt_value) {
        if(strtolower($pt_value) == strtolower($return['property_type'])) {
          $return['property_type'] = $pt_key;
          $return['property_type_label'] =  $pt_value;
        }
      }
    }

    //** If phone number is not set but set globally, we load it into property array here */
    if(empty($return['phone_number']) && !empty($wp_properties['configuration']['phone_number'])) {
      $return['phone_number'] = $wp_properties['configuration']['phone_number'];
    }

    if(is_array($return)) {
      ksort($return);
    }

    $return = apply_filters('wpp_get_property', $return);

    //* Get rid of all empty values */
    foreach($return as $key => $item) {

      //** Don't blank keys starting w/ post_  - this should be converted to use get_attribute_data() to check where data is stored for better check - potanin@UD */
      if(strpos($key, 'post_') === 0) {
        continue;
      }

      if(empty($item)) {
        unset($return[$key]);
      }

    }

    $return[ 'system' ][ 'timer' ] = self::timer_start( __METHOD__ );

    //** Convert to object */
    if($return_object == 'true') {
      $return = WPP_F::array_to_object($return);
    }

    wp_cache_add($id.$args, $return);

    return $return;

  }


  /**
  * Gets prefix to an attribute
  *
  * @todo This should be obsolete, in any case we can't assume everyone uses USD - potanin@UD (11/22/11)
  *
  */
  static function get_attrib_prefix($attrib) {

    if($attrib == 'price') {
      return "$";
    }

    if($attrib == 'deposit') {
      return "$";
    }

  }


  /*&
   * Gets annex to an attribute. (Unused Function)
   *
   * @todo This function does not seem to be used by anything. potanin@UD (11/12/11)
   *
   */
  static function get_attrib_annex($attrib) {

    if($attrib == 'area') {
      return __(' sq ft.','wpp');
    }

  }


  /**
   * Get coordinates for property out of database
   *
   */
  static function get_coordinates($listing_id = false) {
    global $post, $property;

    if(!$listing_id) {
      if(empty($property)) {
        return false;
      }
      $listing_id = is_object($property) ? $property->ID : $property['ID'];
    }

    $latitude = get_post_meta($listing_id, 'latitude', true);
    $longitude = get_post_meta($listing_id, 'longitude', true);

    if(empty($latitude) || empty($longitude)) {
      /** Try parent */
      if(!empty($property->parent_id))  {
        $latitude = get_post_meta($property->parent_id, 'latitude', true);
        $longitude = get_post_meta($property->parent_id, 'longitude', true);
      }
      /** Still nothing */
      if(empty($latitude) || empty($longitude)) {
        return false;
      }
    }
    return array('latitude' => $latitude, 'longitude' => $longitude);
  }


  /**
  Validate if a URL is valid.
*/
  static function isURL($url) {
    return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
  }


  /**
   * Returns an array of a property's stats and their values.
   *
   * Query is array of variables to use load ours to avoid breaking property maps.
   *
   * @updated 1.37.0 - Added ability to request specific group. - potanin@UD
   * @since 1.0
   */
  static function get_stat_values_and_labels( $property_object, $args = array() ) {
    global $wp_properties;

    $property_object = (object) $property_object;

    $args = shortcode_atts( array(
      'exclude' => '',
      'include' => '',
      'property_stats' => '',
      'group' => ''
    ), $args );

    $args = array_filter( (array) $args );

    if( $args[ 'exclude' ] && !is_array( $args[ 'exclude' ] ) ) {
      $args[ 'exclude' ] = explode( ',', $args[ 'exclude' ]);
    }

    if( $args[ 'include' ] && !is_array( $args[ 'exclude' ] ) ) {
      $args[ 'include' ] = explode( ',', $args[ 'include' ]);
    }

    if( $args[ 'property_stats' ] && !is_array( $args[ 'property_stats' ] ) ) {
      $args[ 'property_stats' ] = explode( ',', $args[ 'property_stats' ]);
    }

    //** Get Property Stats array */
    if( $args[ 'property_stats' ] ) {
      $_attributes = $args[ 'property_stats' ];
    } else if ( $args[ 'group' ] ) {

      foreach( (array) array_keys( $wp_properties[ 'property_stats_groups' ], $args[ 'group' ] ) as $attribute_key ) {
        $_attributes[ $attribute_key ] = $wp_properties['property_stats'][ $attribute_key ];
      }

    } else {
      $_attributes = $wp_properties[ 'property_stats' ];
    }

    foreach( array_filter( (array) $_attributes ) as $slug => $label ) {

      if( !is_admin() && in_array( $slug, (array) $wp_properties['hidden_frontend_attributes']) ) {
        continue;
      }

      if( in_array( $slug, (array) $args[ 'exclude' ] ) ) {
        continue;
      }

      if ($property_object->{$slug}){
        $value = $property_object->{$slug};
      }else{

        $value = get_post_meta( $property_object->ID, $slug, true );
        if (!empty($value)){
          $attribute_data = WPP_F::get_attribute_data( $slug );

          if( is_callable( $wp_properties[ '_formatting_callback' ][ $attribute_data['type'] ][ 'human' ] ) ) {
            $attribute_data = WPP_F::get_attribute_data( $slug );
            $value = call_user_func($wp_properties[ '_formatting_callback' ][ $attribute_data['type'] ][ 'human' ], $value);
          }
        }
      }

      //$value = html_entity_decode( $value );

      if ($value === true) {
        $value = 'true';
      }

      //** Override property_type slug with label */
      if( $slug == 'property_type' ) {
        $value = $property_object->property_type_label;
      }

      // Include only passed variables
      if( in_array( $slug, (array) $args[ 'include' ] ) ) {
        $return[ $label ] = $value;
        continue;
      }

      if( empty( $args[ 'include' ] ) ) {
        $return[ $label ] = $value;
      }

    }

    return array_filter( (array) $return );

  }


  /**
   * {}
   *
   */
  static function array_to_object($array = array()) {
    if (!empty($array)) {
        $data = false;

        foreach ($array as $akey => $aval) {
            $data -> {$akey} = $aval;
        }

        return $data;
    }

    return false;
  }


  /**
   * Returns a minified Google Maps Infobox
   *
   * Used in property map and supermap
   *
   * @filter wpp_google_maps_infobox
   * @version 1.11 - added return if $post or address attribute are not set to prevent fatal error
   * @since 1.081
   *
   */
  static function google_maps_infobox($post, $args = false) {
    global $wp_properties;

    $defaults = array(
      'map_image_type' => $wp_properties['configuration']['single_property_view']['map_image_type'],
      'infobox_attributes' => $wp_properties['configuration']['google_maps']['infobox_attributes'],
      'infobox_settings' => $wp_properties['configuration']['google_maps']['infobox_settings'],
      'show_true_as_image' => $wpp_settings['configuration']['google_maps']['show_true_as_image']
    );

    extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

    if(empty($wp_properties['configuration']['address_attribute'])) {
      return;
    }

    if(empty($post)) {
      return;
    }

    if(is_array($post)) {
      $post = (object) $post;
    }

    $property = (array) prepare_property_for_display($post, array(
      'load_gallery' => 'false',
      'scope' => 'google_map_infobox'
    ));

    //** Check if we have children */
    if(count($property['children']) > 0 && $wp_properties['configuration']['google_maps']['infobox_settings']['do_not_show_child_properties'] != 'true') {
      foreach($property['children'] as $child_property) {
        $child_property = (array) $child_property;
        $html_child_properties[] = '<li class="infobox_child_property"><a href="' . $child_property['permalink'] . '">'. $child_property['post_title'] .'</a></li>';
      }
    }

    if(empty($infobox_attributes)) {
      $infobox_attributes = array(
        'price',
        'bedrooms',
        'bathrooms');
    }

    if(empty($infobox_settings)) {
      $infobox_settings = array(
        'show_direction_link' => true,
        'show_property_title' => true
      );
    }

    if(empty($infobox_settings['minimum_box_width'])) {
      $infobox_settings['minimum_box_width'] = '400';
    }

    foreach($infobox_attributes as $attribute) {
      $property_stats[$attribute] = $wp_properties['property_stats'][$attribute];
    }

    $property_stats = WPP_F::get_stat_values_and_labels($property, array(
      'property_stats' => $property_stats
    ));

    $image = wpp_get_image_link($property['featured_image'], $map_image_type, array('return'=>'array'));

    $imageHTML = "<img width=\"{$image['width']}\" height=\"{$image['height']}\" src=\"{$image['link']}\" alt=\"". addslashes($post->post_title) . "\" />";
    if(@$wp_properties['configuration']['property_overview']['fancybox_preview'] == 'true' && !empty($property['featured_image_url'])) {
      $imageHTML = "<a href=\"{$property['featured_image_url']}\" class=\"fancybox_image thumbnail\">{$imageHTML}</a>";
    }

    ob_start();



    ?>

    <div id="infowindow" style="min-width:<?php echo $infobox_settings['minimum_box_width']; ?>px;">
    <?php if($infobox_settings['show_property_title']  == 'true') { ?>
      <div class="wpp_google_maps_attribute_row_property_title" >
      <a href="<?php echo get_permalink($property['ID']); ?>"><?php echo $property['post_title']; ?></a>
      </div>
    <?php }  ?>

    <table cellpadding="0" cellspacing="0" class="wpp_google_maps_infobox_table" style="">
      <tr>
        <?php if($image['link']) { ?>
        <td class="wpp_google_maps_left_col" style=" width: <?php echo $image['width']; ?>px">
          <?php echo $imageHTML; ?>
          <?php if($infobox_settings['show_direction_link'] == 'true'): ?>
          <div class="wpp_google_maps_attribute_row wpp_google_maps_attribute_row_directions_link">
            <a target="_blank" href="http://maps.google.com/maps?gl=us&daddr=<?php echo addslashes(str_replace(' ','+', $property[$wp_properties['configuration']['address_attribute']])); ?>" class="btn btn-info"><?php _e('Get Directions','wpp') ?></a>
          </div>
          <?php endif; ?>
        </td>
        <?php } ?>

        <td class="wpp_google_maps_right_col" style="vertical-align: top;">
        <?php if(!$image['link'] && $infobox_settings['show_direction_link'] == 'true') { ?>
          <div class="wpp_google_maps_attribute_row wpp_google_maps_attribute_row_directions_link">
          <a target="_blank" href="http://maps.google.com/maps?gl=us&daddr=<?php echo addslashes(str_replace(' ','+', $property[$wp_properties['configuration']['address_attribute']])); ?>" class="btn btn-info"><?php _e('Get Directions','wpp') ?></a>
          </div>
        <?php }

          @draw_stats("display=list&include=".implode(',', $infobox_attributes)."&sort_by_groups=false&hide_false=true&show_true_as_image={$show_true_as_image}",$post);

          if(!empty($html_child_properties)) {
            echo '<ul class="infobox_child_property_list">' . implode('', $html_child_properties) . '<li class="infobox_child_property wpp_fillter_element">&nbsp;</li></ul>';
          }

          ?>

          </td>
      </tr>
    </table>

    </div>


    <?php
    $data = ob_get_contents();
    $data = preg_replace(array('/[\r\n]+/'), array(""), $data);
    $data = addslashes($data);

    ob_end_clean();

    $data = apply_filters('wpp_google_maps_infobox', $data, $post);

    return $data;
  }

  /**
   * Returns property object for displaying on map
   *
   * Used for speeding up property queries, only returns:
   * ID, post_title, atitude, longitude, exclude_from_supermap, location, supermap display_attributes and featured image urls
   *
   * 1.11: addded htmlspecialchars and addslashes to post_title
   * @since 1.11
   *
   */
  static function get_property_map($id, $args = '') {
    global $wp_properties, $wpdb;

    $defaults = array(
      'thumb_type' => (!empty($wp_properties['feature_settings']['supermap']['supermap_thumb']) ? $wp_properties['feature_settings']['supermap']['supermap_thumb'] : 'thumbnail'),
      'return_object' => 'false',
      'map_image_type' => $wp_properties['configuration']['single_property_view']['map_image_type']
    );

    extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

    if(class_exists('class_wpp_supermap'))
      $display_attributes = $wp_properties['configuration']['feature_settings']['supermap']['display_attributes'];

     $return['ID'] = $id;

     $data = $wpdb->get_results("SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = $id GROUP BY meta_key");

     foreach($data as $row) {
      $return[$row->meta_key] = $row->meta_value;
     }

     $return['post_title'] = htmlspecialchars(addslashes($wpdb->get_var("SELECT post_title FROM {$wpdb->posts} WHERE ID = $id")));

     // Get Images
      $wp_image_sizes = get_intermediate_image_sizes();

      $thumbnail_id = get_post_meta( $id, '_thumbnail_id', true );
      $attachments = get_children( array('post_parent' => $id, 'post_type' => 'attachment', 'post_mime_type' => 'image',  'orderby' => 'menu_order ASC, ID', 'order' => 'DESC') );

      if ($thumbnail_id) {
        foreach($wp_image_sizes as $image_name) {
          $this_url = wp_get_attachment_image_src( $thumbnail_id, $image_name , true );
          $return['images'][$image_name] = $this_url[0];
          }

        $featured_image_id = $thumbnail_id;

      } elseif ($attachments) {
        foreach ( $attachments as $attachment_id => $attachment ) {

          foreach($wp_image_sizes as $image_name) {
            $this_url =  wp_get_attachment_image_src( $attachment_id, $image_name , true );
            $return['images'][$image_name] = $this_url[0];
          }

          $featured_image_id = $attachment_id;
          break;
        }
      }


      if($featured_image_id) {
        $return['featured_image'] = $featured_image_id;

        $image_title = $wpdb->get_var("SELECT post_title  FROM {$wpdb->posts} WHERE ID = '$featured_image_id' ");

        $return['featured_image_title'] = $image_title;
        $return['featured_image_url'] = wp_get_attachment_url($featured_image_id);

      }

    return $return;

  }


  /**
   * Generates Global Property ID for standard reference point during imports.
   *
   * Property ID is currently not used.
   *
   * @return integer. Global ID number
   * @param integer $property_id. Property ID.
   * @todo API call to UD server to verify there is no duplicates
   * @since 1.6
   */
  static function get_gpid($property_id = false, $check_existance = false) {

    if($check_existance && $property_id) {
      $exists = get_post_meta($property_id, '_wpp::gpid', true);

      if($exists) {
        return $exists;
      }
    }

    return 'gpid_' . rand(1000000000,9999999999);

  }


  /**
   * Generates Global Property ID if it does not exist
   *
   * @return string | Returns GPID
   * @since 1.6
   */
  static function maybe_set_gpid( $property_id = false ) {

    if( !$property_id ) {
      return false;
    }

    $exists = get_post_meta( $property_id, '_wpp::gpid', true );

    if( $exists ) {
      return $exists;
    }

    $gpid = WPP_F::get_gpid( $property_id, true );

    update_post_meta( $property_id, '_wpp::gpid', $gpid );

    return $gpid;

  }


  /**
   * Returns post_id fro GPID if it exists
   *
   * @since 1.6
   */
  static function get_property_from_gpid($gpid = false) {
    global $wpdb;

    if(!$gpid) {
      return false;
    }

    $post_id = $wpdb->get_var("SELECT ID FROM {$wpdb->posts} p LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id  WHERE meta_key = '_wpp::gpid' AND meta_value = '{$gpid}' ");

    if(is_numeric($post_id)) {
      return $post_id;
    }

    return false;

  }

  /**
   * Returns attribute type Label by its slug
   * @global type $wp_properties
   * @param type $type
   * @return type
   * @author odokienko@UD
   */
  static function get_type_label($type){
    global $wp_properties;

    foreach( (array) $wp_properties[ '_attribute_format' ] as $format_group => $format_values ) {
      if (array_key_exists($type, $format_values)) {
         return $wp_properties[ '_attribute_format' ][$format_group][$type];
      }
    }

    return $type;
  }


  /**
   *
   * @param type $structure
   *
   * WPP_F::standard_attributes(array(
   *  'bedrooms' => array( 'name' => 'Bedrooms', 'type'=>'numeric' ),
   *  'bathrooms' => array( 'name' => 'Bathrooms', 'type'=>'currency' )
   * ));
   *
   * @return fring|false
   * @author odokienko@UD
   */
  static function standard_attributes($structure=false){
    global $wp_properties;

    $wpp_settings = get_option('wpp_settings');

    if (!empty($structure)) {
      //** save new standard attributes */
      $wpp_settings['new_standard_attributes'] = $structure;
    }
    $failed_fields = array();
    foreach ((array)$wpp_settings['new_standard_attributes'] as $slug=>$attributes){
      //**  cust $attributes to array in case if it comes as array of objects */
      $attributes = (array)$attributes;

      //** if new slug does not exists just add it */
      if (!in_array($slug,array_keys($wpp_settings['property_stats']))){
        $wpp_settings['property_stats'][$slug] = $attributes['name'];
        $wpp_settings['_attribute_type'][$slug] = $attributes['type'];
      }

      if (isset($attributes['standard']) && $attributes['standard']==false){
        unset($wpp_settings['_standard_attributes'][$slug]);
      }else{
        $wpp_settings['_standard_attributes'][$slug] = $attributes['name'];
      }

      //**  if field has no type  */
      if(in_array($slug,array_keys((array)$wpp_settings['property_stats'])) && $wpp_settings['_attribute_type'][$slug]=='meta' ){
        //** set new type */
        $wpp_settings['_attribute_type'][$slug] = $attributes['type'];
      }

      //** if new slug is standard and already exists */
      if ($wpp_settings['_attribute_type'][$slug] != $attributes['type']){
        //** All the rest will appears in notice */
        $failed_fields[$slug] = array('name'=>$wpp_settings['property_stats'][$slug],'type'=>$attributes['type']);
      }
    }

    if (empty($failed_fields)){
      unset($wpp_settings['new_standard_attributes']);
    }

    $wp_properties['_standard_attributes'] = $wpp_settings['_standard_attributes'];
    $wp_properties['_attribute_type'] = $wpp_settings['_attribute_type'];
    $wp_properties['property_stats'] = $wpp_settings['property_stats'];

    // Updata database
    update_option('wpp_settings', $wpp_settings);

    if (!empty($structure)){
      return empty($failed_fields) ? true : false;
    }

    return $failed_fields;
  }


  /**
   * Render Admin notice
   * @author odokienko@UD
   */
  function standard_attributes_notice() {
    global $current_screen, $wp_properties;
    //* Notice will be shown only on WP-Property Settings page */

    if(($current_screen->id == "property_page_property_settings" || $current_screen->id =='property_page_property_settings_old') && method_exists('WPP_F','standard_attributes')){
      $failed_fields = WPP_F::standard_attributes();

      foreach ($failed_fields as $slug=>$data){
        $notice[] = sprintf(__('- Field %1$s with reserved slug <i>%2$s</i> has incorrect Type (should be %3$s);'),
          $data['name'],
          $slug,
          WPP_F::get_type_label($data['type'])
        );
      }

      if (!empty($notice)){
        $notice = sprintf(__('Please correct the following error(s):%1$s%2$s%1$s'),
          PHP_EOL,
          implode(PHP_EOL,$notice)
        );

        echo '<div class="updated"><p>'.str_replace(PHP_EOL,"<br>", $notice).'</p></div>';
      }
    }
  }



  /**
   * This static function is not actually used, it's only use to hold some common translations that may be used by our themes.
   *
   * Translations for Denali theme.
   *
   * @since 1.14
   *
   */
  static function strings_for_translations() {

    __('General Settings', 'wpp');
    sprintf(__('Find your %1$s', 'wpp'),ucfirst(WPP_F::property_label( 'singular' )));
    __('Edit', 'wpp');
    __('City', 'wpp');
    __('Contact us', 'wpp');
    __('Login', 'wpp');
    __('Explore', 'wpp');
    __('Message', 'wpp');
    __('Phone Number', 'wpp');
    __('Name', 'wpp');
    __('E-mail', 'wpp');
    __('Send Message', 'wpp');
    __('Submit Inquiry', 'wpp');
    __('Inquiry', 'wpp');
    __('Comment About', 'wpp');
    __('Inquire About', 'wpp');
    __('Inquiry About:', 'wpp');
    __('Inquiry message:', 'wpp');
    __('You forgot to enter your e-mail.', 'wpp');
    __('You forgot to enter a message.', 'wpp');
    __('You forgot to enter your  name.', 'wpp');
    __('Error with sending message. Please contact site administrator.', 'wpp');
    __('Thank you for your message.', 'wpp');
  }


  /**
   * Determine if all values of meta key have 'number type'
   * If yes, returns boolean true
   *
   * @param mixed $property_ids
   * @param string $meta_key
   * @return boolean
   * @since 1.16.2
   * @author Maxim Peshkov
   */
  static function meta_has_number_data_type ($property_ids, $meta_key) {
    global $wpdb;

    /* There is no sense to continue if no ids */
    if(empty($property_ids)) {
      return false;
    }

    if(is_array($property_ids)) {
      $property_ids = implode(",", $property_ids);
    }

    $values = $wpdb->get_col("
      SELECT pm.meta_value
      FROM {$wpdb->posts} AS p
      JOIN {$wpdb->postmeta} AS pm ON pm.post_id = p.ID
        WHERE p.ID IN (" . $property_ids . ")
          AND p.post_status = 'publish'
          AND pm.meta_key = '$meta_key'
    ");

    foreach($values as $value) {
      $value = trim($value);

      //** Hack for child properties. Skip values with dashes */
      if(empty($value) || strstr($value, '&ndash;') || strstr($value, '–')) {
        continue;
      }

      preg_match('#^[\d,\.\,]+$#', $value, $matches );
      if(empty($matches)) {
        return false;
      }
    }

    return true;
  }


  /**
   * Function for displaying WPP Data Table rows
   *
   * Ported from WP-CRM
   *
   * @since 3.0
   *
   */
  static function list_table() {
    global $current_screen;

    include WPP_Path . 'core/ui/class_wpp_object_list_table.php';

    //** Get the paramters we care about */
    $sEcho = $_REQUEST['sEcho'];
    $per_page = $_REQUEST['iDisplayLength'];
    $iDisplayStart = $_REQUEST['iDisplayStart'];
    $iColumns = $_REQUEST['iColumns'];
    $sColumns = $_REQUEST['sColumns'];
    $order_by = $_REQUEST['iSortCol_0'];
    $sort_dir = $_REQUEST['sSortDir_0'];
    //$current_screen = $wpi_settings['pages']['main'];

    //** Parse the serialized filters array */
    parse_str($_REQUEST['wpp_filter_vars'], $wpp_filter_vars);
    $wpp_search = $wpp_filter_vars['wpp_search'];

    $sColumns = explode("," , $sColumns);

    //* Init table object */
    $wp_list_table = new WPP_Object_List_Table(array(
      "ajax" => true,
      "per_page" => $per_page,
      "iDisplayStart" => $iDisplayStart,
      "iColumns" => $iColumns,
      "current_screen" => 'property_page_all_properties'
    ));

    if ( in_array( $sColumns[$order_by], $wp_list_table->get_sortable_columns() ) ) {
      $wpp_search['sorting'] = array(
        'order_by' => $sColumns[$order_by],
        'sort_dir' => $sort_dir
      );
    }

    $wp_list_table->prepare_items($wpp_search);

    //print_r( $wp_list_table ); die();

    if ( $wp_list_table->has_items() ) {
      foreach ( $wp_list_table->items as $count => $item ) {
        $data[] = $wp_list_table->single_row( $item );
      }
    } else {
      $data[] = $wp_list_table->no_items();
    }

    //print_r( $data );

    return json_encode(array(
      'sEcho' => $sEcho,
      'iTotalRecords' => count($wp_list_table->all_items),
      // @TODO: Why iTotalDisplayRecords has $wp_list_table->all_items value ? Maxim Peshkov
      'iTotalDisplayRecords' =>count($wp_list_table->all_items),
      'aaData' => $data
    ));
  }


  /**
   * Get Search filter fields
   */
  static function get_search_filters() {
    global $wp_properties, $wpdb;

    $filters = array();
    $filter_fields = array(
        'property_type' => array(
            'type'  => 'multi_checkbox',
            'label' => __('Type', 'wpp')
        ),
        'featured'      => array(
            'type'    => 'multi_checkbox',
            'label'   => __('Featured', 'wpp')
        ),
        'post_status'   => array(
            'default' => 'publish',
            'type'    => 'radio',
            'label'   => __('Status', 'wpp')
        ),
        'post_author'   => array(
            'default' => '0',
            'type'    => 'dropdown',
            'label'   => __('Author', 'wpp')
        ),
        'post_date'     => array(
            'default' => '',
            'type'    => 'dropdown',
            'label'   => __('Date', 'wpp')
        )

    );

    foreach( $filter_fields as $slug => $field ) {

      $f = array();

      switch ( $field['type'] ) {

        default: break;

        case 'input': break;

        case 'multi_checkbox':
          $attr_values = self::get_all_attribute_values( $slug );

          break;

        case 'range_dropdown':
          $attr_values = self::get_all_attribute_values( $slug );

          break;

        case 'dropdown':
          $attr_values = self::get_all_attribute_values( $slug );

          break;

        case 'radio':
          $attr_values = self::get_all_attribute_values( $slug );

          break;

      }

      $f  = $field;

      switch ( $slug ) {

        default: break;

        case 'property_type':

          if ( !empty( $wp_properties['property_types'] ) ) {
            $attrs = array();
            if(is_array($attr_values)) {
              foreach( $attr_values as $attr ) {
                if ( !empty( $wp_properties['property_types'][ $attr ] ) ) {
                  $attrs[ $attr ] = $wp_properties['property_types'][ $attr ];
                }
              }
            }
          }
          $attr_values = $attrs;

          break;

        case 'featured':

          $attrs = array();
          if(is_array($attr_values)) {
            foreach( $attr_values as $attr ) {
              $attrs[$attr] = $attr == 'true' ? 'Yes' : 'No';
            }
          }
          $attr_values = $attrs;

          break;

        case 'post_status':
          $all = 0;
          $attrs = array();
          if(is_array($attr_values)) {
            foreach ($attr_values as $attr) {
              $count = self::get_properties_quantity( array( $attr ));
              $attrs[$attr] = strtoupper( substr($attr, 0, 1) ).substr($attr, 1, strlen($attr)).' ('. WPP_F::format_numeric($count).')';
              $all += $count;
            }
          }

          $attrs['all'] = __('All', 'wpp').' ('.WPP_F::format_numeric($all).')';
          $attr_values = $attrs;

          ksort($attr_values);

          break;

        case 'post_author':

          $attr_values    = self::get_users_of_post_type('property');
          $attr_values[0] = __('Any', 'wpp');

          ksort($attr_values);

          break;

        case 'post_date':

          $attr_values = array();
          $attr_values[''] = __('Show all dates', 'wpp');

          $attrs     = self::get_property_month_periods();

          foreach( $attrs as $value => $attr ) {
            $attr_values[$value] = $attr;
          }

          break;

      }

      if ( !empty( $attr_values ) ) {

        $f['values'] = $attr_values;
        $filters[ $slug ] = $f;

      }

    }

    $filters = apply_filters( "wpp_get_search_filters", $filters );

    return $filters;
  }


  /**
   * Returns users' ids of post type
   * @global object $wpdb
   * @param string $post_type
   * @return array
   */
  static function get_users_of_post_type($post_type) {
    global $wpdb;

    switch ($post_type) {

      case 'property':
        $results = $wpdb->get_results($wpdb->prepare("
          SELECT DISTINCT u.ID, u.display_name
          FROM {$wpdb->posts} AS p
          JOIN {$wpdb->users} AS u ON u.ID = p.post_author
          WHERE p.post_type = '%s'
            AND p.post_status != 'auto-draft'
          ", $post_type), ARRAY_N);
        break;

      default: break;
    }

    if (empty($results)) {
      return false;
    }

    $users = array();
    foreach ($results as $result) {
      $users[$result[0]] = $result[1];
    }

    $users = apply_filters('wpp_get_users_of_post_type', $users, $post_type);

    return $users;
  }


  /**
   * Process bulk actions
   */
  static function property_page_all_properties_load() {

    if ( !empty( $_REQUEST['action'] ) && !empty( $_REQUEST['post'] ) ) {

      switch ( $_REQUEST['action'] ) {

        default: break;

        case 'trash':
          foreach( $_REQUEST['post'] as $post_id ) {
            $post_id = (int)$post_id;
            wp_trash_post($post_id);
          }
          break;

        case 'untrash':
          foreach( $_REQUEST['post'] as $post_id ) {
            $post_id = (int)$post_id;
            wp_untrash_post($post_id);
          }
          break;

        case 'delete':
          foreach( $_REQUEST['post'] as $post_id ) {
            $post_id = (int)$post_id;
            if ( get_post_status($post_id) == 'trash' ) {
              wp_delete_post($post_id);
            }else{
              wp_trash_post($post_id);
            }
          }
          break;

      }

    }

    /** Screen Options */
    add_screen_option('layout_columns', array('max' => 2, 'default' => 2) );

    //** Default Help items */
    $contextual_help['General Help'][] = '<h3>'.__('General Help', WPI).'</h3>';
    $contextual_help['General Help'][] = '<p>'.__('Comming soon...', WPI).'</p>';

    //** Hook this action is you want to add info */
    $contextual_help = apply_filters('property_page_all_properties_help', $contextual_help);

    do_action('wpproperty_contextual_help', array('contextual_help'=>$contextual_help));

  }


  /**
   * Settings page load handler
   * @author korotkov@ud
   */
  static function property_page_property_settings_load() {

    //** Default Help items */
    $contextual_help['Main'][] = '<h3>'. sprintf(__('Default %1$s Page', 'wpp'),ucfirst(WPP_F::property_label( 'plural' ))).'</h3>';
    $contextual_help['Main'][] = '<p>' . sprintf(__('By default, the <b>Default %2$s Page</b> is set to <b>%1$s</b>, which is a dynamically created page used for displaying %1$s Search Results. ','wpp'),ucfirst(WPP_F::property_label( 'singular' )),ucfirst(WPP_F::property_label( 'plural' ))) .'</p>';
    $contextual_help['Main'][] = '<p>' . sprintf(__('We recommend you create an actual WordPress page to be used as the <b>Default %2$s Page</b>. For example, you may create a root page called "Real Estate" - the URL of the default %1$s Page will be %3$s<b>/real_estate/</b>, and your %3$s will have the URLs of %3$s/real_estate/<b>property_name</b>/','wpp'),ucfirst(WPP_F::property_label( 'singular' )),ucfirst(WPP_F::property_label( 'plural' )),get_bloginfo('url')) .'</p>';
    $contextual_help['Main'][] = '<p>' . sprintf(__('When no Default %1$s page is selected, WP-Property uses a Listing Template available in your theme to better integrate WP-Property content.','wpp'),ucfirst(WPP_F::property_label( 'singular' ))) .'</p>';

    $contextual_help['Main'][] = '<h3>'. sprintf(__('%1$s Listing Template', 'wpp'),ucfirst(WPP_F::property_label( 'singular' ))).'</h3>';
    $contextual_help['Main'][] = '<p>' . sprintf(__('In order to display %1$s Search results a template is necessary to be used as a frame. The actual results are inserted into the content area. ','wpp'),ucfirst(WPP_F::property_label( 'singular' ))) .'</p>';

    $contextual_help['Main'][] = '<h3>'. sprintf(__('%1$s Overview Shortcode', 'wpp'),ucfirst(WPP_F::property_label( 'singular' ))).'</h3>';
    $contextual_help['Main'][] = '<p>' . sprintf(__('The [property_overview] shortcode displays a list of all %1$s.<br />The display settings may be edited further by customizing the <b>wp-content/plugins/wp-properties/templates/property-overview.php</b> file. To avoid losing your changes during updates, create a <b>property-overview.php</b> file in your template directory, which will be automatically loaded.','wpp'),ucfirst(WPP_F::property_label( 'plural' ))) .'</p>';

    $contextual_help['Main'][] = '<h3>'. sprintf(__( 'Single %1$s Page', 'wpp' ), ucfirst( WPP_F::property_label( 'plural' ) ) ) . '</h3>';
    $contextual_help['Main'][] = '<p>' . __('The display settings may be customizing the <b>wp-content/plugins/wp-properties/templates/property.php</b> file.  To avoid losing your changes during updates, create a <b>property.php</b> file in your template directory, which will be automatically loaded.','wpp') .'</p>';

    $contextual_help['Display'][] = '<h3>'. __('Display', 'wpp').'</h3>';
    $contextual_help['Display'][] = '<p>'. sprintf(__('This tab allows you to do many things. Make custom picture sizes that will let you to make posting pictures easier. Change the way you view %1$s photos with the use of Fancy Box, Choose  to use pagination on the bottom of %1$s pages and whether or not to show child %2$s. Manage Google map attributes and map thumbnail sizes. Select here which attributes you want to show once a %1$s is pin pointed on your map. Change your currency and placement of symbols.', 'wpp'),ucfirst(WPP_F::property_label( 'singular' )),ucfirst(WPP_F::property_label( 'plural' ))).'</p>';

    $contextual_help['Premium Features'][] = '<h3>'. __('Premium Features', 'wpp').'</h3>';
    $contextual_help['Premium Features'][] = '<p>'. __('Tab allows you to manage your WP-Property Premium Features', 'wpp').'</p>';

    $contextual_help['Help'][] = '<h3>'.__('Help', 'wpp').'</h3>';
    $contextual_help['Help'][] = '<p>'.__('This tab will help you troubleshoot your plugin, do exports and check for updates for Premium Features', 'wpp').'</p>';

    //** Hook this action is you want to add info */
    $contextual_help = apply_filters('property_page_property_settings_help', $contextual_help);

    $contextual_help['More Help'][] = '<h3>'. __('More Help', 'wpp').'</h3>';
    $contextual_help['More Help'][] = '<p>'. __('Visit <a target="_blank" href="https://usabilitydynamics.com/products/wp-property/">WP-Property Help Page</a> on UsabilityDynamics.com for more help.', 'wpp').'</>';

    do_action('wpproperty_contextual_help', array('contextual_help'=>$contextual_help));

  }


  /**
   * Counts properties by post types
   * @global object $wpdb
   * @param array $post_status
   * @return int
   */
  static function get_properties_quantity( $post_status = array('publish') ) {
    global $wpdb;

    $results = $wpdb->get_col("
      SELECT ID
      FROM {$wpdb->posts}
      WHERE post_status IN ('". implode( "','", $post_status ) ."')
        AND post_type = 'property'
    ");

    $results = apply_filters('wpp_get_properties_quantity', $results, $post_status);

    return count( $results );

  }


  /**
   * Returns month periods of properties
   * @global object $wpdb
   * @global object $wp_locale
   * @return array
   */
  static function get_property_month_periods() {
    global $wpdb, $wp_locale;

    $months = $wpdb->get_results("
      SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
      FROM $wpdb->posts
      WHERE post_type = 'property'
        AND post_status != 'auto-draft'
      ORDER BY post_date DESC
    ");

    $months = apply_filters('wpp_get_property_month_periods', $months);

    $results = array();

    foreach( $months as $date ) {

      $month = zeroise( $date->month, 2 );
      $year = $date->year;

      $results[ $date->year . $month ] = $wp_locale->get_month( $month ) . " $year";

    }

    return $results;

  }


  /**
   * Deletes directory recursively
   *
   * @param string $dirname
   * @return bool
   * @author korotkov@ud
   */
  static function delete_directory( $dirname ) {

    if ( is_dir( $dirname ) )
      $dir_handle = opendir($dirname);

    if ( !$dir_handle )
      return false;

    while( $file = readdir( $dir_handle ) ) {
      if ( $file != "." && $file != ".." ) {

        if ( !is_dir( $dirname."/".$file ) )
          unlink( $dirname."/".$file );
        else
          delete_directory( $dirname.'/'.$file );

      }
    }

    closedir( $dir_handle );
    return rmdir( $dirname );

  }


  /**
   * Prevent Facebook integration if 'Facebook Tabs' did not installed.
   * @author korotkov@ud
   */
  static function check_facebook_tabs() {
    //** Check if FB Tabs is not installed to prevent an ability to use WPP as Facebook App or Page Tab */
    if ( !class_exists('class_facebook_tabs') ) {

      //** If request goes really from Facebook */
      if ( !empty($_REQUEST['signed_request']) && strstr($_SERVER['HTTP_REFERER'], 'facebook.com') ) {

        //** Show message */
        die( sprintf(__('You cannot use your site as Facebook Application. You should <a href="%s">purchase</a> WP-Property Premium Feature "Facebook Tabs" to manage your Facebook Tabs.', 'wpp'), 'https://usabilitydynamics.com/products/wp-property/premium/') );
      }
    }
  }


  /**
   * Formats phone number for display
   *
   * @since 1.36.0
   * @source WPP_F
   *
   * @param string $phone_number
   * @return string $phone_number
   */
  static function format_phone_number($phone_number) {

     $phone_number = ereg_replace("[^0-9]",'',$phone_number);
    if(strlen($phone_number) != 10) return(False);
    $sArea = substr($phone_number,0,3);
    $sPrefix = substr($phone_number,3,3);
    $sNumber = substr($phone_number,6,4);
    $phone_number = "(".$sArea.") ".$sPrefix."-".$sNumber;

    return $phone_number;
  }


  /**
   * Shorthand static function for drawing checkbox input fields.
   *
   * @since 1.36.0
   * @source WPP_F
   *
   * @param string $args List of arguments to overwrite the defaults.
   * @param bool $checked Option, default is false. Whether checkbox is checked or not.
   * @return string Checkbox input field and hidden field with the opposive value
   */
  static function checkbox($args = '', $checked = false) {
    $defaults = array(
      'name' => '',
      'id' => false,
      'class' => false,
      'group' => false,
      'special' => '',
      'value' => 'true',
      'label' => false,
      'maxlength' => false
    );

    extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

    // Get rid of all brackets
    if(strpos("$name",'[') || strpos("$name",']')) {

      $class_from_name = $name;

      //** Remove closing empty brackets to avoid them being displayed as __ in class name */
      $class_from_name = str_replace('][]', '', $class_from_name);

      $replace_variables = array('][',']','[');
      $class_from_name = 'wpp_' . str_replace($replace_variables, '_', $class_from_name);
    } else {
      $class_from_name = 'wpp_' . $name;
    }


    // Setup Group
    if($group) {
      if(strpos($group,'|')) {
        $group_array = explode("|", $group);
        $count = 0;
        foreach($group_array as $group_member) {
          $count++;
          if($count == 1) {
            $group_string .= $group_member;
          } else {
            $group_string .= "[{$group_member}]";
          }
        }
      } else {
        $group_string = $group;
      }
    }


    if(is_array($checked)) {

      if(in_array($value, $checked)) {
        $checked = true;
      } else {
        $checked = false;
      }
    } else {
      $checked = strtolower($checked);
      if($checked == 'yes')   $checked = 'true';
      if($checked == 'true')   $checked = 'true';
      if($checked == 'no')   $checked = false;
      if($checked == 'false') $checked = false;
    }

    $id          =   ($id ? $id : $class_from_name);
    $insert_id       =   ($id ? " id='$id' " : " id='$class_from_name' ");
    $insert_name    =   (isset($group_string) ? " name='".$group_string."[$name]' " : " name='$name' ");
    $insert_checked    =   ($checked ? " checked='checked' " : " ");
    $insert_value    =   " value=\"$value\" ";
    $insert_class     =   " class='$class_from_name $class wpp_checkbox " . ($group ? 'wpp_' . $group . '_checkbox' : ''). "' ";
    $insert_maxlength  =   ($maxlength ? " maxlength='$maxlength' " : " ");

    $opposite_value = '';

    // Determine oppositve value
    switch ($value) {
      case 'yes':
      $opposite_value = 'no';
      break;

      case 'true':
      $opposite_value = 'false';
      break;

      case 'open':
      $opposite_value = 'closed';
      break;

    }

    $return = '';

    // Print label if one is set
    if($label) $return .= "<label for='$id'>";

    // Print hidden checkbox if there is an opposite value */
    if($opposite_value) {
      $return .= '<input type="hidden" value="' . $opposite_value. '" ' . $insert_name . ' />';
    }

    // Print checkbox
    $return .= "<input type='checkbox' $insert_name $insert_id $insert_class $insert_checked $insert_maxlength  $insert_value $special />";
    if($label) $return .= " $label</label>";

    return $return;
  }


  /**
   * Shorthand function for drawing a textarea
   *
   * @since 1.36.0
   * @source WPP_F
   *
   * @param string $args List of arguments to overwrite the defaults.
   * @return string Input field and hidden field with the opposive value
   */
  static function textarea($args = '') {
    $defaults = array('name' => '', 'id' => false,  'checked' => false,  'class' => false, 'style' => false, 'group' => '','special' => '','value' => '', 'label' => false, 'maxlength' => false);
    extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );


    // Get rid of all brackets
    if(strpos("$name",'[') || strpos("$name",']')) {
      $replace_variables = array('][',']','[');
      $class_from_name = $name;
      $class_from_name = 'wpp_' . str_replace($replace_variables, '_', $class_from_name);
    } else {
      $class_from_name = 'wpp_' . $name;
    }


    // Setup Group
    if($group) {
      if(strpos($group,'|')) {
        $group_array = explode("|", $group);
        $count = 0;
        foreach($group_array as $group_member) {
          $count++;
          if($count == 1) {
            $group_string .= "$group_member";
          } else {
            $group_string .= "[$group_member]";
          }
        }
      } else {
        $group_string = "$group";
      }
    }

    $id          =   ($id ? $id : $class_from_name);

    $insert_id       =   ($id ? " id='$id' " : " id='$class_from_name' ");
    $insert_name    =   ($group_string ? " name='".$group_string."[$name]' " : " name=' wpp_$name' ");
    $insert_checked    =   ($checked ? " checked='true' " : " ");
    $insert_style    =   ($style ? " style='$style' " : " ");
    $insert_value    =   ($value ? $value : "");
    $insert_class     =   " class='$class_from_name input_textarea $class' ";
    $insert_maxlength  =   ($maxlength ? " maxlength='$maxlength' " : " ");

    // Print label if one is set

    // Print checkbox
    $return .= "<textarea $insert_name $insert_id $insert_class $insert_checked $insert_maxlength $special $insert_style>$insert_value</textarea>";


    return $return;
  }


  /**
   * Shorthand function for drawing regular or hidden input fields.
   *
   * @since 1.36.0
   * @source WPP_F
   *
   * @param string $args List of arguments to overwrite the defaults.
   * @param string $value Value may be passed in arg array or seperately
   * @return string Input field and hidden field with the opposive value
   */
  static function input($args = '', $value = false) {
    $defaults = array('name' => '', 'group' => '','special' => '','value' => $value, 'title' => '', 'type' => 'text', 'class' => false, 'hidden' => false, 'style' => false, 'readonly' => false, 'label' => false);
    extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

    // Add prefix
    if($class) {
      $class = "wpp_$class";
    }


    // if [ character is present, we do not use the name in class and id field
    if(!strpos("$name",'[')) {
      $id = $name;
      $class_from_name = $name;
    }

    $return = '';

    if($label) $return .= "<label for='$name'>";
    $return .= "<input ".($type ?  "type=\"$type\" " : '')." ".($style ?  "style=\"$style\" " : '')." id=\"$id\" class=\"".($type ?  "" : "input_field")." $class_from_name $class ".($hidden ?  " hidden " : '').""  .($group ? "group_$group" : ''). " \"    name=\"" .($group ? $group."[".$name."]" : $name). "\"   value=\"".stripslashes($value)."\"   title=\"$title\" $special ".($type == 'forget' ?  " autocomplete='off'" : '')." ".($readonly ?  " readonly=\"readonly\" " : "")." />";
    if($label) $return .= " $label </label>";

    return $return;
  }


  /**
   * Recursive conversion of an object into an array
   *
   * @since 1.36.0
   * @source WPP_F
   *
   */
  static function objectToArray($object) {

      if(!is_object( $object ) && !is_array( $object )) {
        return $object;
      }

      if(is_object($object) ) {
      $object = get_object_vars( $object );
      }

      return array_map(array('WPP_F' , 'objectToArray'), $object );
   }


  /**
   * Get a URL of a page.
   *
   * @since 1.36.0
   * @source WPP_F
   *
   */
  static function base_url($page = '', $get = '') {
    global $wpdb,  $wp_properties;

    $permalink = '';
    $permalink_structure = get_option( 'permalink_structure' );

    //** Using Permalinks */
    if ( '' != $permalink_structure ) {
      $page_id = false;
      if( !is_numeric( $page ) ) {
        $page_id = $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} where post_name = '$page'" );
      } else {
        $page_id = $page;
      }
      //** If the page doesn't exist, return default url (base_slug) */
      if( empty( $page_id ) ) {
        $permalink = site_url() . "/" . ( !is_numeric( $page ) ? $page : $wp_properties['configuration']['base_slug'] ) . '/';
      } else {
        $permalink = get_permalink( $page_id );
      }
    }

    //** Not using permalinks */
    else {
      //** If a slug is passed, convert it into ID */
      if( !is_numeric( $page ) ) {
        $page_id = $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} where post_name = '$page' AND post_status = 'publish' AND post_type = 'page'" );
        //* In case no actual page_id was found, we continue using non-numeric $page, it may be 'property' */
        if( !$page_id ) {
          $query = '?p=' . $page;
        } else {
          $query = '?page_id=' . $page_id;
        }
      } else {
        $page_id = $page;
        $query = '?page_id=' . $page_id;
      }
      $permalink = home_url( $query );
    }

    //** Now set GET params */
    if ( !empty( $get ) ) {
      $get = wp_parse_args( $get );
      $get = http_build_query( $get, '', '&' );
      $permalink .= ( strpos( $permalink, '?' ) === false ) ? '?' : '&';
      $permalink .= $get;
    }

    return $permalink;

  }

  /**
   * Returns clear post status
   *
   * @author peshkov@UD
   * @version 0.1
   */
  static function clear_post_status ( $post_status = '', $ucfirst = true ) {
    switch ($post_status) {
      case 'publish':
        $post_status = __('published','wpp');
        break;
      case 'pending':
        $post_status = __('pending','wpp');
        break;
      case 'trash':
        $post_status = __('trashed','wpp');
        break;
      case 'inherit':
        $post_status = __('inherited','wpp');
        break;
      case 'auto-draft':
        $post_status = __('drafted','wpp');
        break;
    }
    return ( $ucfirst ? ucfirst($post_status) : $post_status );
  }

  /**
   * Do a single upgrade from 1.36.0 to higher.
   *
   * @global type $wp_properties
   * @global type $old_wp_properties
   */
  function wpp_handle_upgrade() {
    global $wp_properties;

    $installed_ver = get_option( "wpp_version" );
    $wpp_version = WPP_Version;

    $wp_properties['currency_attributes'] = !empty($wp_properties['currency_attributes'])?$wp_properties['currency_attributes']:array();
    $wp_properties['numeric_attributes'] = !empty($wp_properties['numeric_attributes'])?$wp_properties['numeric_attributes']:array();

    if( @version_compare($installed_ver, $wpp_version) == '-1' && $installed_ver == '1.36.0' ) {

      foreach( $wp_properties['property_stats'] as $property_stat_slug => $property_stat_name ) {

        //** Set defaults to meta */
        $wp_properties['_attribute_type'][$property_stat_slug] = 'meta';

        //** Process conditions */
        if ( in_array( $property_stat_slug, $wp_properties['currency_attributes'] ) ) {
          $wp_properties['_attribute_type'][$property_stat_slug] = 'currency';
        }
        if ( in_array( $property_stat_slug, $wp_properties['numeric_attributes'] ) ) {
          $wp_properties['_attribute_type'][$property_stat_slug] = 'numeric';
        }
        if ( $wp_properties['admin_attr_fields'][$property_stat_slug] == 'checkbox' ) {
          $wp_properties['_attribute_type'][$property_stat_slug] = 'boolean';
        }
        if ( $wp_properties['configuration']['address_attribute'] == $property_stat_slug ) {
          $wp_properties['_attribute_type'][$property_stat_slug] = 'location';
        }
      }

      //** Update settings in DB */
      $wpp_settings = get_option('wpp_settings');
      $wpp_settings['_attribute_type'] =  $wp_properties['_attribute_type'];
      update_option('wpp_settings', $wpp_settings);

    }
  }
}
