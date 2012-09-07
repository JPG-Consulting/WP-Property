<?php
/**
 * WP-Property Default API
 *
 * @updated 1.37.0
 */


//** Initialize WPP Default API */
add_filter( 'plugins_loaded', array( 'wpp_default_api', 'plugins_loaded' ), 0, 9 );

add_action( 'after_setup_theme' , create_function( '', ' add_theme_support( "post-thumbnails" ); ' ));
add_action( 'save_property', 'save_property_coordinate_override', 0, 3 );

add_filter( 'wpp_get_property', 'add_display_address' );
add_filter( 'wpp_property_inheritance', 'add_city_to_inheritance' );
add_filter( 'wpp_searchable_attributes', 'add_city_to_searchable' );



if( !function_exists( 'wpp_property_stats_input_address' ) ) {
  /**
   * Add UI to set custom coordinates on property editing page
   *
   * @since 1.04
   */
  function wpp_property_stats_input_address( $content, $slug, $object, $attribute_data ) {

    ob_start(); ?>
    <div class="wpp_attribute_row_address<?php echo ((!empty($object['address_is_formatted']))?' address_is_formatted' : ((!empty($object[$slug]))?' address_is_not_formatted':'')); ?>">
      <?php echo $content; ?>
      <span class="wpp_google_maps_icon" title="<?php echo ((!empty($object['address_is_formatted']))?__("Address was successfully validated by Google's Geocoding Service" ) : ((!empty($object[$slug]))?__('Address is not validated yet.'):'') ); ?>" ></span>
      <div class="wpp_attribute_row_address_options">
          <input type="hidden" name="wpp_data[meta][manual_coordinates]" value="false" />
          <input type="checkbox" id="wpp_manual_coordinates" name="wpp_data[meta][manual_coordinates]" value="true" <?php checked( $object[ 'manual_coordinates' ], 1 ); ?> />
          <label for="wpp_manual_coordinates"><?php echo __( 'Set Coordinates Manually.','wpp' ); ?></label>
          <div id="wpp_coordinates" style="<?php if( !$object[ 'manual_coordinates' ] ) { ?>display:none;<?php } ?>">
            <ul>
              <li>
                  <input type="text" id="wpp_meta_latitude" name="wpp_data[meta][latitude]" value="<?php echo $object[ 'latitude' ]; ?>" />
                  <label><?php echo __( 'Latitude','wpp' ) ?></label>
                  <div class="wpp_clear"></div>
                </li>
                <li>
                  <input type="text" id="wpp_meta_longitude" name="wpp_data[meta][longitude]" value="<?php echo $object[ 'longitude' ]; ?>" />
                  <label><?php echo __( 'Longitude','wpp' ) ?></label>
                  <div class="wpp_clear"></div>
                </li>
              </ul>
          </div>
      </div>
    </div>

    <script type="text/javascript">

      jQuery( document ).ready( function() {

        jQuery( 'input#wpp_manual_coordinates' ).change( function() {

        var use_manual_coordinates;

        if( jQuery( this ).is( ":checked" ) ) {
          use_manual_coordinates = true;
          jQuery( '#wpp_coordinates' ).show();

        } else {
          use_manual_coordinates = false;
          jQuery( '#wpp_coordinates' ).hide();
        }

      });

      });

    </script>
    <?php

    $content = ob_get_contents();
    ob_end_clean();

    return $content;

  }
}


if( !function_exists( 'wpp_property_stats_input_currency' ) ) {
  /**
   * Add UI to set custom currency on property editing page
   *
   * @author odokienko@UD
   */
  function wpp_property_stats_input_currency( $content, $slug, $object, $attribute_data ) {
    global $wp_properties;


    $symbol = "<span class=\"currency\">". (($wp_properties['configuration']['currency_symbol'])?$wp_properties['configuration']['currency_symbol']:'$') . "</span>";

    $placement = (!empty($wp_properties['configuration']['currency_symbol_placement']))? $wp_properties['configuration']['currency_symbol_placement'] : 'before';

    ob_start(); ?>
    <div class="wpp_attribute_row_currency wpp_currency">
      <?php if($placement == 'before') { echo $symbol; } ?>
      <?php echo $content; ?>
      <?php if($placement == 'after')  { echo $symbol; } ?>
    </div>
    <?php

    $content = ob_get_contents();
    ob_end_clean();

    return $content;

  }
}

if( !function_exists( 'wpp_property_stats_input_time_stamp' ) ) {
  /**
   * Add UI to set custom time_stamp on property editing page
   *
   * @author odokienko@UD
   */
  function wpp_property_stats_input_time_stamp( $content, $slug, $object, $attribute_data ) {

    if((int)$object[$attribute_data['slug']] === strtotime(date('c', (int)$object[$attribute_data['slug']]))){
      ob_start(); ?>
      <div class="wpp_attribute_row_time_stamp">

        <?php echo preg_replace('~value="\d+"~','value="'.strftime("%m/%d/%Y", (int)$object[$attribute_data['slug']]).'"',$content); ?>

      </div>
      <?php

      $content = ob_get_contents();
      ob_end_clean();
    }

    return $content;

  }
}

if( !function_exists( 'wpp_property_stats_input_room' ) ) {
  /**
   * Add UI to set custom area on property editing page
   *
   * @author odokienko@UD
   */
  function wpp_property_stats_input_room( $content, $slug, $object, $attribute_data ) {
    global $wp_properties;

    $symbol = "<span class=\"symbol\">". (wpp_default_api::get_area_unit()) . "</span>";

    ob_start(); ?>
    <div class="<?php echo $attribute_data['ui_class'].' area';?>">
      <?php echo $content; ?>
      <?php echo $symbol;  ?>
    </div>
    <?php

    $content = ob_get_contents();
    ob_end_clean();

    return $content;

  }
}



if( !function_exists( 'wpp_property_stats_input_areas' ) ) {
  /**
   * Add UI to set custom area on property editing page
   *
   * @since 1.04
   */
  function wpp_property_stats_input_areas( $content, $slug, $object, $attribute_data ) {
    global $wp_properties;

    $symbol = "<span class=\"symbol\">". (wpp_default_api::get_area_unit()) . "</span>";

    ob_start(); ?>
    <div class="<?php echo $attribute_data['ui_class'].' area';?>">
      <?php echo $content; ?>
      <?php echo $symbol;  ?>
    </div>
    <?php

    $content = ob_get_contents();
    ob_end_clean();

    return $content;


  }
}


add_filter( 'wpp_property_stats_input_'. $wp_properties[ 'configuration' ][ 'address_attribute' ], 'wpp_property_stats_input_address', 0, 4 );

if ( !empty( $wp_properties['_attribute_type'] ) && is_array( $wp_properties['_attribute_type'] ) ) {
  foreach($wp_properties['_attribute_type'] as $slug => $type) {
    if (is_callable('wpp_property_stats_input_'. $type)){
      add_filter( 'wpp_property_stats_input_'. $slug, 'wpp_property_stats_input_'. $type, 0, 4 );
    }
  }
}

  /**
   * Add our listener to the XMLRPC methods
   *
   * @param array $methods
   * @return array
   */
  class wpp_default_api {

    /**
     * Loader for WPP API functions.
     *
     * @version 1.25.0
     */
    static function plugins_loaded() {

      //** Load API towards the end of init */
      add_filter( 'init', array( 'wpp_default_api', 'init' ), 0, 30 );

      add_filter( 'xmlrpc_methods', array( 'wpp_default_api', 'xmlrpc_methods' ), 0, 5 );

    }


    /**
     * {}
     *
     * @version 1.25.0
     */
    static function xmlrpc_methods( $methods ) {
      return $methods;
    }


    /**
     * Loader for WPP API functions.
     *
     * @version 1.25.0
     */
    static function init() {
      global $wp_properties, $shortcode_tags;

      $shortcodes = array_keys( $shortcode_tags );

      //** Load list-attachments shortcode if the List Attachments Shortcode plugin does not exist */
      if( !in_array( 'list-attachments', $shortcodes ) ) {
        add_shortcode( 'list_attachments', array( 'wpp_default_api', 'list_attachments' ));
      }

      add_filter( 'wpp_single_value_attributes', array( 'wpp_default_api', 'wpp_single_value_attributes' ));

      //** Add dollar sign to all attributes marked as currency */
      foreach( (array) $wp_properties[ 'currency_attributes' ] as $attribute ) {
        add_filter( "wpp_stat_filter_{$attribute}", array( 'wpp_default_api', 'currency_format' ));
      }

      //** Addres format */
      add_filter( "wpp_stat_filter_{$wp_properties[ 'configuration' ][ 'address_attribute' ]}", array( 'wpp_default_api', 'format_address_attribute' ), 0,3 );

      //** Format values as numeric if marked as numeric_attributes */
      foreach( (array) $wp_properties[ 'numeric_attributes' ] as $attribute ) {
        add_filter( "wpp_stat_filter_{$attribute}", 'number_format_i18n');
      }

    }


    /**
     * Converts value to currency.
     *
     * @updated 1.37.0 - renamed from add_dollar_sign to currency_format and moved into the wpp_default_api class
     * @since 1.15.3
     */
    function currency_format( $content ) {
      global $wp_properties;

      $currency_symbol = ( !empty( $wp_properties[ 'configuration' ][ 'currency_symbol' ] ) ? $wp_properties[ 'configuration' ][ 'currency_symbol' ] : "$" );
      $currency_symbol_placement  = ( !empty( $wp_properties[ 'configuration' ][ 'currency_symbol_placement' ] ) ? $wp_properties[ 'configuration' ][ 'currency_symbol_placement' ] : "before" );

      $content = preg_replace( array( '~\$~', '~,~','~\s*~' ), "", $content );

      $hyphen = '~(-|&ndash;)~';
      if ( !is_numeric( $content ) && preg_match( $hyphen, $content ) ){
        $hyphen_between = preg_split( $hyphen, $content, PREG_SPLIT_DELIM_CAPTURE );
        foreach ($hyphen_between as &$part){
          $part = self::currency_format( $part );
        }
        return implode( ' &ndash; ', $hyphen_between );
      } elseif ( !is_numeric( $content ) ) {
        return $content;
      } else {
        return "<nobr>".( $currency_symbol_placement == 'before' ? $currency_symbol.' ' : '' ) . number_format_i18n( $content,2 ) . ( $currency_symbol_placement == 'after' ? ' '.$currency_symbol : '' ) . "</nobr>";
      }

    }

    function currency_store( $content ) {
      $content = preg_replace('~[^\d|\.]~i','',$content);
      return $content;
    }


    function address_store( $content ){

      $geo_data = WPP_F::geo_locate_address( $content, $wp_properties[ 'configuration' ][ 'google_maps_localization' ], true );

      return ( !empty( $geo_data->formatted_address ) ) ? $geo_data : $content;
    }

    /**
     * Formats address on print.  If address it not formatted, makes an on-the-fly call to GMaps for validation.
     * @deprecated
     * @since 1.04
     */
    function format_address( $content, $format = "[street_number] [street_name], [city], [state]" ) {
      global $wp_properties;

      if (!is_object($content)) return $content;
      //** If the currently requested properties address has not been formatted, and on-the-fly geo-lookup has not been disabled, try to look up now */

      if( $content->formatted_address ) {


      } /*else {

        $street_number  = $property->street_number;
        $route  = $property->route;
        $city  = $property->city;
        $state  = $property->state;
        $state_code  = $property->state_code;
        $county  = $property->county;
        $country  = $property->country;
        $postal_code  = $property->postal_code;
      }

      $display_address = $format;

      $display_address =   str_replace( "[street_number]", $street_number,$display_address );
      $display_address =   str_replace( "[street_name]", $route, $display_address );
      $display_address =   str_replace( "[city]", "$city", $display_address );
      $display_address =   str_replace( "[state]", "$state", $display_address );
      $display_address =   str_replace( "[state_code]", "$state_code", $display_address );
      $display_address =   str_replace( "[county]", "$county", $display_address );
      $display_address =   str_replace( "[country]", "$country", $display_address );
      $display_address =   str_replace( "[zip_code]", "$postal_code", $display_address );
      $display_address =   str_replace( "[zip]", "$postal_code", $display_address );
      $display_address =   str_replace( "[postal_code]", "$postal_code", $display_address );
      $display_address =   preg_replace( '/^\n+|^[\t\s]*\n+/m', "", $display_address );

      if( str_replace( array(' ', ','), '', $display_address ) == '' ) {
        return !empty( $current_address ) ? $current_address : '';
      }

      //** Remove empty lines *
      foreach( explode( "\n" , $display_address ) as $line ) {

        $line = trim( $line );

        if( strlen( $line ) < 3 && ( strpos( $line, ',' ) === 1 || strpos( $line, ',' ) === 0 ) ) {
          continue;
        }

        $return[] = $line;

      }

      return implode( "\n", ( array ) $return );*/

    }

    /**
     * Formats address on print.  If address it not formatted, makes an on-the-fly call to GMaps for validation.
     * @deprecated
     * @since 1.04
     */
    function format_address_attribute( $data, $property = false, $format = "[street_number] [street_name], [city], [state]" ) {
      global $wp_properties;

      if( !is_object( $property ) ) {
        return $data;
      }

      $current_address = $property->$wp_properties[ 'configuration' ][ 'address_attribute' ];

      //** If the currently requested properties address has not been formatted, and on-the-fly geo-lookup has not been disabled, try to look up now */
      if( !$property->address_is_formatted ) {

        //** Silently attempt to validate address */
        $geo_data = WPP_F::revalidate_all_addresses( array(
          'property_ids' => array( $property->ID ),
          'echo_result' => false,
          'return_geo_data' => true
        ));

        if( $this_geo_data = $geo_data[ 'geo_data' ][ $property->ID ] ) {
          $street_number  = $this_geo_data->street_number;
          $route  = $this_geo_data->route;
          $city  = $this_geo_data->city;
          $state  = $this_geo_data->state;
          $state_code  = $this_geo_data->state_code;
          $county  = $this_geo_data->county;
          $country  = $this_geo_data->country;
          $postal_code  = $this_geo_data->postal_code;
        }

      } else {

        $street_number  = $property->street_number;
        $route  = $property->route;
        $city  = $property->city;
        $state  = $property->state;
        $state_code  = $property->state_code;
        $county  = $property->county;
        $country  = $property->country;
        $postal_code  = $property->postal_code;
      }

      $display_address = $format;

      $display_address =   str_replace( "[street_number]", $street_number,$display_address );
      $display_address =   str_replace( "[street_name]", $route, $display_address );
      $display_address =   str_replace( "[city]", "$city", $display_address );
      $display_address =   str_replace( "[state]", "$state", $display_address );
      $display_address =   str_replace( "[state_code]", "$state_code", $display_address );
      $display_address =   str_replace( "[county]", "$county", $display_address );
      $display_address =   str_replace( "[country]", "$country", $display_address );
      $display_address =   str_replace( "[zip_code]", "$postal_code", $display_address );
      $display_address =   str_replace( "[zip]", "$postal_code", $display_address );
      $display_address =   str_replace( "[postal_code]", "$postal_code", $display_address );
      $display_address =   preg_replace( '/^\n+|^[\t\s]*\n+/m', "", $display_address );

      if( str_replace( array(' ', ','), '', $display_address ) == '' ) {
        return !empty( $current_address ) ? $current_address : '';
      }

      //** Remove empty lines */
      foreach( explode( "\n" , $display_address ) as $line ) {

        $line = trim( $line );

        if( strlen( $line ) < 3 && ( strpos( $line, ',' ) === 1 || strpos( $line, ',' ) === 0 ) ) {
          continue;
        }

        $return[] = $line;

      }

      return implode( "\n", ( array ) $return );

    }


    /**
     * Returns area unit by slug
     * @global type $wp_properties
     * @param type $slug
     * @return type
     * @author odokienko@UD
     */
    static function get_area_unit($slug=false){
      global $wp_properties;

      $unit_slug = ($slug) ? $slug : $wp_properties['configuration']['area_unit_type'];

      switch ($unit_slug){
        case 'square_foot':       $return = __(" sq ft"); break;
        case 'square_kilometer':  $return = __(" sq km"); break;
        case 'square_mile':       $return = __(" sq mi"); break;
        case 'square_meter':
        default:                  $return = __(" sq m");  break;
      }

      return $return;
    }

    /**
     * Formats areas on print.
     *
     * @since 1.04
     */
    function area_format( $content ) {
      if (!empty($content)){
        $content = number_format_i18n( $content,1 ).wpp_default_api::get_area_unit();
      }
      return $content;
    }

    function detail_format( $content ) {

      return do_shortcode(html_entity_decode($content));

    }

    function link_format( $content ) {
      if(WPP_F::isURL($content)) {
        $content = str_replace('&ndash;', '-', $content);
        $content = "<a href='{$content}'>{$content}</a>";
      }
      return $content;
    }

    function my_strtotime($content){

      if (($timestamp = strtotime($content)) === false) {
        return $content;
      }

      return $timestamp;
    }

    function date_time_format( $content ) {
      global $wp_properties;

      if((int)$content === strtotime(date('c', (int)$content))){
        $content = UD_API::nice_time((int)$content, array('format'=>'date'));
      }

      return $content;
    }

    function number_format( $content ) {

      if(is_numeric($content)){
        $content = number_format_i18n($content);
      }

      return $content;
    }



    /**
     * Add attributes that are to be excluded from multi-value entry
     *
     * @used in WPP_F::get_property(), WPP_UI::page_attributes_meta_box()
     * @version 2.0.0
     * @author potanin@UD
     */
    function wpp_single_value_attributes( $fields = false ) {
      global $wp_properties;

      $fields = (array) $fields;

      //** Exclude meta fields */
      //$property_meta = ( is_array( $wp_properties[ 'property_meta' ] ) ? array_flip( $wp_properties[ 'property_meta' ] ) : array() );

      //** Exclude wp_posts columns */
      $post_table_keys = WPP_F::get_attribute_data( false, array( 'get_post_table_keys' => true ));

      $geo_type_attributes = $wp_properties[ 'geo_type_attributes' ];

      $fields = array_merge( ( array ) $fields, ( array ) $post_table_keys, ( array ) $property_meta, ( array )  $geo_type_attributes );

      if( !empty( $wp_properties[ 'configuration' ][ 'address_attribute' ] ) ) {
        $fields[] = $wp_properties[ 'configuration' ][ 'address_attribute' ];
      }

      $fields[] = 'featured';
      $fields[] = 'display_address';
      $fields[] = 'manual_coordinates';
      $fields[] = 'latitude';
      $fields[] = 'longitude';
      $fields[] = '_wpp::gpid';

      //** Clean up array */
      $fields = array_unique( $fields );
      $fields = array_filter( $fields );

      return $fields;

    }


    /**
     * Display list of attached files to a s post.
     *
     * Function ported over from List Attachments Shortcode plugin.
     *
     * @version 1.25.0
     */
    static function list_attachments( $atts = array() ) {
      global $post, $wp_query;

      $r = '';

      $atts = shortcode_atts( array(
        'type' => NULL,
        'orderby' => NULL,
        'groupby' => NULL,
        'order' => NULL,
        'post_id' => false,
        'before_list' => '',
        'after_list' => '',
        'opening' => '<ul class="attachment-list wpp_attachment_list">',
        'closing' => '</ul>',
        'before_item' => '<li>',
        'after_item' => '</li>',
        'show_descriptions' => true,
        'include_icon_classes' => true,
        'showsize' => false
      ), $atts );

      if( isset( $atts[ 'post_id' ] ) && is_numeric( $atts[ 'post_id' ] ) ) {
        $post = get_post( $atts[ 'post_id' ] );
      }

      if( !$post ) {
        return;
      }

      if( !empty( $atts[ 'type' ] ) ) {
        $types = explode( ',', str_replace( ' ', '', $atts[ 'type' ] ));
      } else {
        $types = array();
      }

      $showsize = ( $atts[ 'showsize' ] == true || $atts[ 'showsize' ] == 'true' || $atts[ 'showsize' ] == 1 ) ? true : false;
      $upload_dir = wp_upload_dir();

      $op = clone $post;
      $oq = clone $wp_query;

      foreach( array( 'before_list', 'after_list', 'opening', 'closing', 'before_item', 'after_item' ) as $htmlItem ) {
        $atts[$htmlItem] = str_replace( array( '&lt;', '&gt;' ), array( '<', '>' ), $atts[$htmlItem] );
      }

      $args = array(
        'post_type' => 'attachment',
        'numberposts' => -1,
        'post_status' => null,
        'post_parent' => $post->ID,
      );

      if( !empty( $atts[ 'orderby' ] ) ) {
        $args[ 'orderby' ] = $atts[ 'orderby' ];
      }
      if( !empty( $atts[ 'order' ] ) ) {
        $atts[ 'order' ] = ( in_array( $atts[ 'order' ], array( 'a','asc','ascending' ) ) ) ? 'asc' : 'desc';
        $args[ 'order' ] = $atts[ 'order' ];
      }
      if( !empty( $atts[ 'groupby' ] ) ) {
        $args[ 'orderby' ] = $atts[ 'groupby' ];
      }

      $attachments = get_posts( $args );

      if( $attachments ) {
        $grouper = $atts[ 'groupby' ];
        $test = $attachments;
        $test = array_shift( $test );
        if( !property_exists( $test, $grouper ) ) {
          $grouper = 'post_' . $grouper;
        }

        $attlist = array();

        foreach( $attachments as $att ) {
          $key = ( !empty( $atts[ 'groupby' ] ) ) ? $att->$grouper : $att->ID;
          $key .= ( !empty( $atts[ 'orderby' ] ) ) ? $att->$atts[ 'orderby' ] : '';

          $attlink = wp_get_attachment_url( $att->ID );

          if( count( $types ) ) {
            foreach( $types as $t ) {
              if( substr( $attlink, ( 0- strlen( '.' . $t ) ) ) == '.' . $t ) {
                $attlist[ $key ] = clone $att;
                $attlist[ $key ]->attlink = $attlink;
              }
            }
          }
          else {
            $attlist[ $key ] = clone $att;
            $attlist[ $key ]->attlink = $attlink;
          }
        }
        if( $atts[ 'groupby' ] ) {
          if( $atts[ 'order' ] == 'asc' ) {
            ksort( $attlist );
          }
          else {
            krsort( $attlist );
          }
        }
      }


      if( count( $attlist ) ) {
        $open = false;
        $r = $atts[ 'before_list' ] . $atts[ 'opening' ];
        foreach( $attlist as $att ) {

          $container_classes = array( 'attachment_container' );

          //** Determine class to display for this file type */
          if( $atts[ 'include_icon_classes' ] ) {

            switch( $att->post_mime_type ) {

              case 'application/zip':
                $class = 'zip';
              break;

              case 'vnd.ms-excel':
                $class = 'excel';
              break;

              case 'image/jpeg':
              case 'image/png':
              case 'image/gif':
              case 'image/bmp':
                $class = 'image';
              break;

              default:
                $class = 'default';
              break;
            }
          }

          $icon_class = ( $class ? 'wpp_attachment_icon file-' . $class : false );

          //** Determine if description shuold be displayed, and if it is not empty */
          $echo_description  = ( $atts[ 'show_descriptions' ] && !empty( $att->post_content ) ? ' <span class="attachment_description"> ' . $att->post_content . ' </span> ' : false );

          $echo_title = ( $att->post_excerpt ?  $att->post_excerpt :  __( 'View ', 'wpp' ) . apply_filters( 'the_title_attribute',$att->post_title ));

          if( $icon_class ) {
            $container_classes[] = 'has_icon';
          }

          if( !empty( $echo_description ) ) {
            $container_classes[] = 'has_description';
          }

          //** Add conditional classes if class is not already passed into container */
          if( !strpos( $atts[ 'before_item' ], 'class' ) ) {
            $this_before_item = str_replace( '>', ' class="' . implode( ' ', $container_classes ) . '">', $atts[ 'before_item' ] );
          }

          $echo_size = ( ( $showsize ) ? ' <span class="attachment-size">' . WPP_F::get_filesize( str_replace( $upload_dir[ 'baseurl' ], $upload_dir[ 'basedir' ], $attlink ) ) . '</span>' : '' ) ;

          if( !empty( $atts[ 'groupby' ] ) && $current_group != $att->$grouper ) {
            if( $open ) {
              $r .= $atts[ 'closing' ] . $atts[ 'after_item' ];
              $open = false;
            }
            $r .= $atts[ 'before_item' ] . '<h3>' . $att->$grouper . '</h3>' . $atts[ 'opening' ];
            $open = true;
            $current_group = $att->$grouper;
          }
          $attlink = $att->attlink;
          $r .= $this_before_item . '<a href="' . $attlink .'" title="'.$echo_title.'" class="wpp_attachment ' . $icon_class . '">' . apply_filters( 'the_title',$att->post_title ) . '</a>'  . $echo_size  . $echo_description . $atts[ 'after_item' ];
        }
        if( $open ) {
          $r .= $atts[ 'closing' ] . $atts[ 'after_item' ];
        }
        $r .= $atts[ 'closing' ] . $atts[ 'after_list' ];
      }

      $wp_query = clone $oq;
      $post = clone $op;

      return $r;

    }

  }


/**
 * Add our listener to the XMLRPC methods
 *
 * It's only outside the wpp_default_api class because I can't figure out
 *
 * @param array $methods
 * @return array
 */
if( !function_exists( 'ud_api_call' ) ) {
  function ud_api_call( $request = array() ) {
    $api_key = $request[0];

    $_call = array(
      'class' => $request[1][ 'class' ],
      'method' => $request[1][ 'method' ],
      'args' => $request[2]
    );

    if( $api_key != get_option( 'ud_api_key' ) || did_action( 'ud_api_call' ) ) {
      return new IXR_Error( 401, __( 'Sorry, invalid request.', 'wpp' ));
    }

    apply_filters( 'ud_api_call', $_call );

    return array( 'success' => true );

  }
}





if( !function_exists( 'save_property_coordinate_override' ) ) {
  /**
   * Save manually entered coordinates if setting exists
   *
   * Does not blank out latitude or longitude unless maual_coordinates are set
   * @since 1.08
   */
  function save_property_coordinate_override( $post_id, $post_data, $geo_data ) {
    global $wp_properties;

    if ( get_post_meta( $post_id, 'manual_coordinates', true ) != 'true' ) {

      foreach( (array) $post_data[ 'wpp_data' ][ 'meta' ][$wp_properties[ 'configuration' ][ 'address_attribute' ]] as $count => $value ) {
        if (!empty($value)){
          if( $geo_data->latitude )
            update_post_meta( $post_id, 'latitude', ( float )$geo_data->latitude );

          if( $geo_data->longitude )
            update_post_meta( $post_id, 'longitude', ( float )$geo_data->longitude );
        }else{
          update_post_meta( $post_id, 'latitude', '' );
          update_post_meta( $post_id, 'longitude', '' );
        }
      }

    } else {

      if ( !empty( $post_data[ 'wpp_data' ][ 'meta' ][$wp_properties[ 'configuration' ][ 'address_attribute' ]] ) ){
        foreach( (array) $post_data[ 'wpp_data' ][ 'meta' ][$wp_properties[ 'configuration' ][ 'address_attribute' ]] as $count => $value ) {
          add_post_meta( $post_id, $meta_key, $formatted[ $wp_properties[ 'configuration' ][ 'address_attribute' ] ][ $count ] = WPP_F::encode_mysql_input( $value, $wp_properties[ 'configuration' ][ 'address_attribute' ] ) );
          add_post_meta( $post_id, $meta_key, $formatted[ 'display_address' ] = WPP_F::encode_mysql_input( $value, $wp_properties[ 'configuration' ][ 'address_attribute' ] ) );

        }
      }

      $old_coordinates = ( empty( $post_data[ 'wpp_data' ][ 'meta' ][ 'latitude' ] ) || empty( $post_data[ 'wpp_data' ][ 'meta' ][ 'longitude' ] ) ) ? "" : array( 'lat'=>( float )$post_data[ 'wpp_data' ][ 'meta' ][ 'latitude' ],'lng'=>( float )$post_data[ 'wpp_data' ][ 'meta' ][ 'longitude' ] );

      if ( !empty( $old_coordinates ) ){
        update_post_meta( $post_id, 'latitude', $old_coordinates[ 'lat' ] );
        update_post_meta( $post_id, 'longitude', $old_coordinates[ 'lng' ] );
      }
    }

  }
}


if( !function_exists( 'add_city_to_inheritance' ) ) {
  /**
   * Add "city" as an inheritable attribute for city property_type
   *
   * Modifies $wp_properties[ 'property_inheritance' ] in WPP_F::settings_action(), overriding database settings
   *
   * @since 1.0
   * @param array $property_inheritance
   * @return array $property_inheritance
   */
  function add_city_to_inheritance( $property_inheritance ) {
    $property_inheritance[ 'floorplan' ][] = 'city';
    return $property_inheritance;
  }
}


if( !function_exists( 'add_city_to_searchable' ) ) {
  /**
   * Adds city to searchable
   *
   * Modifies $wp_properties[ 'searchable_attributes' ] in WPP_F::settings_action(), overriding database settings
   *
   * @since 1.0
   * @param string $area
   * @return string $area
   */
  function add_city_to_searchable( $array ) {

    global $wp_properties;

    /** Determine if property attribute 'city' already exists, we don't need to set searchable here */
    if( empty( $wp_properties[ 'property_stats' ] ) ) {
      if( is_array( $array ) && !in_array( 'city', $array ) ) {
        array_push( $array, 'city' );
      }
    }

    return $array;

  }
}


if( !function_exists( 'add_display_address' ) ) {
  /**
   * Demonstrates how to add a new attribute to the property class
   *
   * @since 1.08
   * @uses WPP_F::get_coordinates() Creates an array from string $args.
   * @param string $listing_id Listing ID must be passed
   */
  function add_display_address( $property ) {
    global $wp_properties;

    // Don't execute function if coordinates are set to manual
    if( isset( $property[ 'manual_coordinates' ] ) && $property[ 'manual_coordinates' ] == 'true' )
      return $property;

    $display_address = $wp_properties[ 'configuration' ][ 'display_address_format' ];

    if( empty( $display_address ) ) {
      $display_address =  "[street_number] [street_name], [city], [state]";
    }

    $display_address_code = $display_address;

    // Check if property is supposed to inehrit the address
    if( isset( $property[ 'parent_id' ] )
      && is_array( $wp_properties[ 'property_inheritance' ][$property[ 'property_type' ]] )
        && in_array( $wp_properties[ 'configuration' ][ 'address_attribute' ], $wp_properties[ 'property_inheritance' ][$property[ 'property_type' ]] ) ) {

      if( get_post_meta( $property[ 'parent_id' ], 'address_is_formatted', true ) ) {
        $street_number = get_post_meta( $property[ 'parent_id' ],'street_number', true );
        $route = get_post_meta( $property[ 'parent_id' ],'route', true );
        $city = get_post_meta( $property[ 'parent_id' ],'city', true );
        $state = get_post_meta( $property[ 'parent_id' ],'state', true );
        $state_code = get_post_meta( $property[ 'parent_id' ],'state_code', true );
        $postal_code = get_post_meta( $property[ 'parent_id' ],'postal_code', true );
        $county = get_post_meta( $property[ 'parent_id' ],'county', true );
        $country = get_post_meta( $property[ 'parent_id' ],'country', true );

        $display_address = str_replace( "[street_number]", $street_number,$display_address );
        $display_address = str_replace( "[street_name]", $route, $display_address );
        $display_address = str_replace( "[city]", "$city", $display_address );
        $display_address = str_replace( "[state]", "$state", $display_address );
        $display_address = str_replace( "[state_code]", "$state_code", $display_address );
        $display_address = str_replace( "[country]", "$country", $display_address );
        $display_address = str_replace( "[county]", "$county", $display_address );
        $display_address = str_replace( "[zip_code]", "$postal_code", $display_address );
        $display_address = str_replace( "[zip]", "$postal_code", $display_address );
        $display_address = str_replace( "[postal_code]", "$postal_code", $display_address );
        $display_address =  preg_replace( '/^\n+|^[\t\s]*\n+/m', "", $display_address );
        $display_address = nl2br(WPP_F::cleanup_extra_whitespace($display_address ));

      }
    } else {

      // Verify that address has been converted via Google Maps API
      if( $property[ 'address_is_formatted' ] ) {

          $street_number  = $property[ 'street_number' ];
          $route  = $property[ 'route' ];
          $city  = $property[ 'city' ];
          $state  = $property[ 'state' ];
          $state_code  = $property[ 'state_code' ];
          $country  = $property[ 'country' ];
          $postal_code  = $property[ 'postal_code' ];
          $county  = $property[ 'county' ];

          $display_address = str_replace( "[street_number]", $street_number,$display_address );
          $display_address = str_replace( "[street_name]", $route, $display_address );
          $display_address = str_replace( "[city]", "$city", $display_address );
          $display_address = str_replace( "[state]", "$state", $display_address );
          $display_address = str_replace( "[state_code]", "$state_code", $display_address );
          $display_address = str_replace( "[country]", "$country", $display_address );
          $display_address = str_replace( "[county]", "$county", $display_address );
          $display_address = str_replace( "[zip_code]", "$postal_code", $display_address );
          $display_address = str_replace( "[zip]", "$postal_code", $display_address );
          $display_address = str_replace( "[postal_code]", "$postal_code", $display_address );
          $display_address =  preg_replace( '/^\n+|^[\t\s]*\n+/m', "", $display_address );
          $display_address = nl2br( WPP_F::cleanup_extra_whitespace($display_address ));

      }

    }


    // If somebody is smart enough to do the following with regular expressions, let us know!

    $comma_killer = explode( ",", $display_address );

    if( is_array( $comma_killer ) )
      foreach( $comma_killer as $key => $addy_line )
        if( isset( $addy_line ) )
          if( trim( $addy_line ) == "" )
            unset( $comma_killer[$key] );

    $display_address  = implode( ", ", $comma_killer );

    $empty_line_killer = explode( "<br />", $display_address );

    if( is_array( $empty_line_killer ) )
      foreach( $empty_line_killer as $key => $addy_line )
        if( isset( $addy_line ) )
          if( trim( $addy_line ) == "" )
            unset( $empty_line_killer[$key] );


    if( is_array( $empty_line_killer ) ) {
      $display_address  = implode( "<br />", $empty_line_killer );
    }


    $property[ 'display_address' ] = apply_filters( 'wpp_display_address', $display_address, $property );


    // Don't return if result matches the
    if( str_replace( array( " ", "," , "\n" ), "", $display_address_code ) == str_replace( array( " ", "," , "\n" ), "", $display_address ) ) {
      $property[ 'display_address' ] = "";
    }

    //** Make sure that address isn't retunred with no data */
    if( str_replace( ',', '', $property[ 'display_address' ] ) == '' ) {
      /* No Address */
    }

    return $property;
  }
}


if(!function_exists('group_search_values')){
  /**
   *
   * Group search values
   *
   */
  function group_search_values( $values ) {
    $result = array();

    if( !is_array( $values ) ) {
        return $values;
    }

    $min = 0;
    $max = 0;
    $control = false;

    for( $i=0; $i<count( $values ); $i++ ) {
        $value = ( int )$values[$i];
        if( !$control && $min == 0 && $value != 0 ) {
            $control = true;
            $min = $value;
        } elseif( $value < $min ) {
            $min = $value;
        } elseif( $value > $max ) {
            $max = $value;
        }
    }

    $range = $max-$min;

    if( $range == 0 ) {
        return $values;
    }

    $s = round( $range/10 );
    $stepup = ( $s > 1 )?$s:1;

    $result[] = $min;
      for( $i= ( $min + $stepup ); $i<$max; $i ) {
          $result[] = $i;
        $i = $i + $stepup;
    }
    $result[] = $max;

      return $result;
  }
}


/**
 * Implementing this for old versions of PHP
 *
 * @since 1.15.9
 *
 */
if( !function_exists( 'array_fill_keys' ) ){

  function array_fill_keys($target, $value = '') {

    if(is_array($target)) {

      foreach($target as $key => $val) {

        $filledArray[$val] = is_array($value) ? $value[$key] : $value;

      }

    }

    return $filledArray;

  }
}


/**
 * Delete a file or recursively delete a directory
 *
 * @param string $str Path to file or directory
 * @param boolean $flag If false, doesn't remove root directory
 * @version 0.1
 * @since 1.32.2
 * @author Maxim Peshkov
 */
if(!function_exists('wpp_recursive_unlink')){
  function wpp_recursive_unlink($str, $flag = false){
    if(is_file($str)){
      return @unlink($str);
    }
    elseif(is_dir($str)){
      $scan = glob(rtrim($str,'/').'/*');
      foreach($scan as $index=>$path){
        wpp_recursive_unlink($path, true);
      }
      if($flag) {
        return @rmdir($str);
      } else {
        return true;
      }
    }
  }
}
