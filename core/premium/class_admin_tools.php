<?php
/*
Name: Admin Tools
Feature ID: 1
Minimum Core Version: 1.36.0
Version: 3.5.1-alpha
Description: Tools for developing themes and extensions for WP-Property.
Class: class_admin_tools
*/


add_action( 'wpp_init', array( 'class_admin_tools', 'init' ) );
add_action( 'wpp_pre_init', array( 'class_admin_tools', 'pre_init' ) );

if( class_exists( 'class_admin_tools' ) ) {
  return;
}

/**
 * class_admin_tools Class
 *
 * Contains administrative functions
 *
 * @copyright 2010-2012 Usability Dynamics, Inc. <info@usabilitydynamics.com> *
 * @version 1.0
 * @author team@UD
 * @package WP-Property
 * @subpackage Admin Functions
 */
class class_admin_tools {

  /*
   * ( custom ) Capability to manage the current feature
   */
  static protected $capability = "manage_wpp_admintools";

  /**
   * Special functions that must be called prior to init
   *
   */
  function pre_init() {
    global $wp_properties;

    //**
    // As we want to get rid of property_meta - let's simply convert it to property stats.
    // We should do this only if property_meta exists which means that we did not do this yet.
    //*/
    if ( !empty( $wp_properties['property_meta'] ) && is_array( $wp_properties['property_meta'] ) ) {
      $wp_properties['property_stats'] = array_merge( $wp_properties['property_stats'], $wp_properties['property_meta'] );
      unset( $wp_properties['property_meta'] );
    }

    //** Legacy support for property_meta */
    if ( !is_admin() &&  empty($wp_properties[ 'configuration' ][ 'disable_legacy_detailed' ]) || $wp_properties[ 'configuration' ][ 'disable_legacy_detailed' ]=='false' ){
      foreach (array_keys((array)$wp_properties[ '_attribute_type' ],'detail') as $slug){
        $wp_properties['property_meta'][$slug] = $wp_properties['property_stats'][$slug];
      }
    }

    /* Add capability */
    add_filter( 'wpp_capabilities', array( 'class_admin_tools', "add_capability" ) );
  }

  /*
   * Apply feature's Hooks and other functionality
   */
  static function init() {

    if( current_user_can( self::$capability ) ) {
      //** Add Inquiry page to Property Settings page array */
      add_filter( 'wpp_settings_nav', array( 'class_admin_tools', 'settings_nav' ) );
      //** Contextual Help */
      add_action( 'property_page_property_settings_help', array( 'class_admin_tools', 'wpp_contextual_help' ) );
    }

  }

  /*
   * Adds Custom capability to the current premium feature
   */
  function add_capability( $capabilities ) {

    $capabilities[self::$capability] = __( 'Manage Admin Tools','wpp' );

    return $capabilities;
  }

  /**
   * Add Contextual help item
   *
   * @param type $data
   * @return string
   * @author korotkov@ud
   */
  function wpp_contextual_help( $data ) {

    $data['Data Structure'][] = '<h3>' . __( 'Data Structure', 'wpp' ) .'</h3>';
    $data['Data Structure'][] = '<p>' . __( 'The <b>slug</b> is automatically created from the title and is used in the back-end.  It is also used for template selection, example: floorplan will look for a template called property-floorplan.php in your theme folder, or default to property.php if nothing is found.' ) .'</p>';
    $data['Data Structure'][] = '<p>' . sprintf(__( 'If <b>Searchable</b> is checked then the %1$s will be loaded for search, and available on the %1$s search widget.' ),WPP_F::property_label( 'singular' )) .'</p>';
    $data['Data Structure'][] = '<p>' . sprintf(__( 'If <b>Location Matters</b> is checked, then an address field will be displayed for the  %1$s, and validated against Google Maps API.  Additionally, the %1$s will be displayed on the SuperMap, if the feature is installed.' ),WPP_F::property_label( 'singular' )) .'</p>';
    $data['Data Structure'][] = '<p>' . sprintf(__( '<b>Hidden Attributes</b> determine which attributes are not applicable to the given %1$s type, and will be grayed out in the back-end.' ),WPP_F::property_label( 'singular' )) .'</p>';
    $data['Data Structure'][] = '<p>' . sprintf(__( '<b>Inheritance</b> determines which attributes should be automatically inherited from the parent %1$s' ), WPP_F::property_label( 'singular' )) .'</p>';
    $data['Data Structure'][] = '<p>' . sprintf(__( '%1$s attributes are meant to be short entries that can be searchable, on the back-end attributes will be displayed as single-line input boxes. On the front-end they are displayed using a definitions list.' ), ucfirst(WPP_F::property_label( 'singular' ))) .'</p>';
    $data['Data Structure'][] = '<p>' . sprintf(__( 'Making an attribute as "searchable" will list it as one of the searchable options in the %1$s Search widget settings.' ), ucfirst(WPP_F::property_label( 'singular' ))) .'</p>';
    $data['Data Structure'][] = '<p>' . __( 'Be advised, attributes added via add_filter() function supercede the settings on this page.' ) .'</p>';
    $data['Data Structure'][] = '<p>' . sprintf(__( '<b>Search Input:</b> Select and input type and enter comma-separated values that you would like to be used in %1$s Search, on the front-end.', 'wpp' ), ucfirst(WPP_F::property_label( 'singular' ))) .'</p>';
    $data['Data Structure'][] = '<p>' . sprintf(__( '<b>Administrative:</b> Enter comma-separated values that you would like to use on the back-end when editing %1$s.', 'wpp' ), WPP_F::property_label( 'plural' )) .'</p>';

    return $data;

  }


  /**
   * Adds admin tools manu to settings page navigation
   *
   * @version 1.0
   * @copyright 2010-2012 Usability Dynamics, Inc. <info@usabilitydynamics.com>
   */
  function settings_nav( $tabs ) {

     $tabs['admin_tools'] = array(
      'slug' => 'class_admin_tools',
      'title' => __( 'Data Structure','wpp' )
    );

    return $tabs;
  }

  /**
   * Displays advanced management page
   *
    *
   * @version 1.0
   * @copyright 2010-2012 Usability Dynamics, Inc. <info@usabilitydynamics.com>
   */
  function settings_page() {
    global $wpdb, $wp_properties;

    $wpp_inheritable_attributes = $wp_properties['property_stats'];

    ?>

  <script type="text/javascript">
    var geo_type_attrs = <?php echo json_encode( (array)$wp_properties['geo_type_attributes'] ); ?>

    jQuery( document ).ready( function() {

      jQuery( "#wpp_inquiry_attribute_fields tbody" ).sortable( {
        delay: 200
      } );

      jQuery( "#wpp_inquiry_meta_fields tbody" ).sortable( {
        delay: 200
      } );

      jQuery( "#wpp_inquiry_attribute_fields tbody tr, #wpp_inquiry_meta_fields tbody tr" ).live( "mouseover", function() {
        jQuery( this ).addClass( "wpp_draggable_handle_show" );
      } );;

      jQuery( "#wpp_inquiry_attribute_fields tbody tr, #wpp_inquiry_meta_fields tbody tr" ).live( "mouseout", function() {
        jQuery( this ).removeClass( "wpp_draggable_handle_show" );
      } );;

      jQuery( ".wpp_all_advanced_settings" ).live( "click", function() {
        var action = jQuery( this ).attr( "action" );

        if( action == "expand" ) {
          jQuery( "#wpp_inquiry_attribute_fields .wpp_development_advanced_option" ).show();
        }

        if( action == "collapse" ) {
          jQuery( "#wpp_inquiry_attribute_fields .wpp_development_advanced_option" ).hide();
        }

      } )

      //* Stats to group functionality */
      jQuery( '.wpp_attribute_group' ).wppGroups();

      //* Fire Event after Row is added */
      jQuery( '#wpp_inquiry_attribute_fields tr' ).live( 'added', function() {
        //* Remove notice block if it exists */
        var notice = jQuery( this ).find( '.wpp_notice' );
        if( notice.length > 0 ) {
          notice.remove();
        }
        //* Unassign Group from just added Attribute */
        jQuery( 'input.wpp_group_slug' , this ).val( '' );
        this.removeAttribute( 'wpp_attribute_group' );

        //* Remove background-color from the added row if it's set */
        if( typeof jQuery.browser.msie != 'undefined' && ( parseInt( jQuery.browser.version ) == 9 ) ) {
          //* HACK FOR IE9 ( it's just unset background color ) peshkov@UD: */
          setTimeout( function(){
            var lr = jQuery( '#wpp_inquiry_attribute_fields tr.wpp_dynamic_table_row td.wpp_draggable_handle' ).last();
            var bc = lr.css( 'background-color' );
            lr.css( 'background-color', '' );
            jQuery( document ).bind( 'mousemove', function(){
              setTimeout( function(){
                lr.prev().css( 'background-color', bc );
              }, 50 );
              jQuery( document ).unbind( 'mousemove' );
            } );
          }, 50 );
        } else {
          jQuery( this ).css( 'background-color', '' );
        }

        //* Stat to group functionality */
        jQuery( this ).find( '.wpp_attribute_group' ).wppGroups();

      } );

      jQuery('#wpp_attr_groups').live('row_removed', function(e, row) {
        jQuery('#wpp_inquiry_attribute_fields tr[wpp_attribute_group="'+jQuery(row).attr('slug')+'"]').each(function(k,v){
          jQuery( v ).removeAttr( 'wpp_attribute_group' );
          jQuery( 'input.wpp_group_slug' , v ).val( '' );
          jQuery( 'input.wpp_attribute_group' , v ).val( '' );
          jQuery( v ).find( 'td' ).css( 'background-color', '' );
        });
      });

      //* Determine if slug of property stat is the same as Geo Type has and show notice */
      jQuery( '#wpp_inquiry_attribute_fields tr .wpp_stats_slug_field' ).live( 'change', function(){
        var slug = jQuery( this ).val();
        var geo_type = false;
        if( typeof geo_type_attrs == 'object' ) {
          for( var i in geo_type_attrs ) {
            if( slug == geo_type_attrs[i] ) {
              geo_type = true;
              break;
            }
          }
        }
        var notice = jQuery( this ).parent().find( '.wpp_notice' );
        if( geo_type ) {
          if( !notice.length > 0 ) {
            //* Toggle Advanced option to show notice */
            var advanced_options = ( jQuery( this ).parents( 'tr.wpp_dynamic_table_row' ).find( '.wpp_development_advanced_option' ) );
            if( advanced_options.length > 0 ) {
              if( jQuery( advanced_options.get( 0 ) ).is( ':hidden' ) ) {
                jQuery( this ).parents( 'tr.wpp_dynamic_table_row' ).find( '.wpp_show_advanced' ).trigger( 'click' );
              }
            }
            jQuery( this ).parent().append( '<div class="wpp_notice"></div>' );
            notice = jQuery( this ).parent().find( '.wpp_notice' );
          }
          notice.html( '<span><?php echo sprintf(__( 'Attention! This attribute ( slug ) is used by Google Validator and Address Display functionality. It is set automaticaly and can not be edited on %1$s Adding/Updating page.','wpp' ), ucfirst(WPP_F::property_label('singular'))); ?></span>' );
        } else {
          if( notice.length > 0 ) {
            notice.remove();
          }
        }
      } );

      jQuery( ".wpp_pre_defined_value_setter" ).live( "change", function() {
        set_pre_defined_values_for_attribute( this );
      } );

      jQuery( ".wpp_pre_defined_value_setter" ).each( function() {
        set_pre_defined_values_for_attribute( this );
      } );

      function set_pre_defined_values_for_attribute( setter_element ) {

        var wrapper = jQuery( setter_element ).closest( "ul" );
        var setting = jQuery( setter_element ).val();
        var value_field = jQuery( "textarea.wpp_attribute_pre_defined_values", wrapper );

        switch ( setting ) {

          case 'input':
            jQuery( value_field ).hide();
          break;

          case 'range_input':
            jQuery( value_field ).hide();
          break;

          case 'dropdown':
            jQuery( value_field ).show();
          break;

          case 'checkbox':
            jQuery( value_field ).hide();
          break;

          case 'multi_checkbox':
            jQuery( value_field ).show();
          break;

          default:
            jQuery( value_field ).hide();

        }

      }


    } );
  </script>
  <style type="style/text">
  #wpp_inquiry_attribute_fields tbody tr { cursor:move; }
  #wpp_inquiry_meta_fields tbody tr { cursor:move; }
  </style>


  <table class="form-table wpp_option_table">
    <tbody>

      <tr>
        <td colspan="2">
          <div>
            <h3 style="float:left;"><?php printf( __( '%1s Attributes','wpp' ), WPP_F::property_label() ); ?></h3>
            <span class="">
            <div class="wpp_property_stat_functions">
              <?php _e( 'Advanced Stats Settings:','wpp' ) ?>
              <span class="wpp_all_advanced_settings" action="expand"><?php _e( 'expand all','wpp' ) ?></span>,
              <span class="wpp_all_advanced_settings" action="collapse"><?php _e( 'collapse all','wpp' ) ?></span>.
              <input type="button" id="sort_stats_by_groups" class="button-secondary" value="<?php _e( 'Sort Stats by Groups','wpp' ) ?>" />
            </div>
            <div class="clear"></div>
          </div>

          <div id="wpp_dialog_wrapper_for_groups"></div>
          <div id="wpp_attribute_groups">
              <table id="wpp_attr_groups" allow_random_slug="true" class="ud_ui_dynamic_table widefat wpp_option_table wpp_sortable" cellpadding="0" cellspacing="0" >
                <thead>
                  <tr>
                    <th class="wpp_group_assign_col">&nbsp;</th>
                    <th class="wpp_draggable_handle">&nbsp;</th>
                    <th class="wpp_group_name_col"><?php _e( 'Group Name','wpp' ) ?></th>
                    <th class="wpp_group_slug_col"><?php _e( 'Slug','wpp' ) ?></th>
                    <th class="wpp_group_main_col"><?php _e( 'Main','wpp' ) ?></th>
                    <th class="wpp_group_color_col"><?php _e( 'Group Color','wpp' ) ?></th>
                    <th class="wpp_group_action_col">&nbsp;</th>
                  </tr>
                </thead>
                <tbody>
                <?php
                if( empty( $wp_properties['property_groups'] ) ) {
                  //* If there is no any group, we set default */
                  $wp_properties['property_groups'] = array(
                    'main' =>array(
                      'name' => 'Main',
                      'color' => '#bdd6ff'
                    )
                  );
                }
                ?>
                <?php  foreach( $wp_properties['property_groups'] as $slug => $group ):  ?>
                  <tr class="wpp_dynamic_table_row" slug="<?php echo $slug; ?>"  new_row='false'>
                    <td class="wpp_group_assign_col">
                      <input type="button" class="wpp_assign_to_group button-secondary" value="<?php _e( 'Assign','wpp' ) ?>" />
                    </td>
                    <td class="wpp_draggable_handle">&nbsp;</td>
                    <td class="wpp_group_name_col">
                      <input class="slug_setter" type="text" name="wpp_settings[property_groups][<?php echo $slug; ?>][name]" value="<?php echo $group['name']; ?>" />
                    </td>
                    <td class="wpp_group_slug_col">
                      <input type="text" class="slug" readonly='readonly' value="<?php echo $slug; ?>" />
                    </td>
                    <td class="wpp_group_main_col">
                      <input type="radio" class="wpp_no_change_name" name="wpp_settings[configuration][main_stats_group]" <?php checked( $slug, $wp_properties[ 'configuration' ][ 'main_stats_group' ] ); ?> value="<?php echo $slug; ?>" />
                    </td>
                    <td class="wpp_group_color_col">
                      <input type="text" class="wpp_input_colorpicker" name="wpp_settings[property_groups][<?php echo $slug; ?>][color]" value="<?php echo $group['color']; ?>" />
                    </td>
                    <td class="wpp_group_action_col">
                      <span class="wpp_delete_row wpp_link"><?php _e( 'Delete','wpp' ) ?></span>
                    </td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="7">
                      <div style="float:left;text-align:left;">
                        <input type="button" class="wpp_add_row button-secondary" value="<?php _e( 'Add Group','wpp' ) ?>" />
                        <input type="button" class="wpp_unassign_from_group button-secondary" value="<?php _e( 'Unassign from Group','wpp' ) ?>" />
                      </div>
                      <div style="float:right;">
                        <input type="button" class="wpp_close_dialog button-secondary" value="<?php _e( 'Save and Close','wpp' ) ?>" />
                      </div>
                      <div class="clear"></div>
                    </td>
                  </tr>
                </tfoot>
              </table>
          </div>

          <table id="wpp_inquiry_attribute_fields" class="ud_ui_dynamic_table wpp_option_table wpp_clean" allow_random_slug="true">
            <thead>
              <tr>
                <th class="wpp_draggable_handle">&nbsp;</th>
                <th class="wpp_attribute_name_col"><?php _e( 'Attribute','wpp' ) ?></th>
                <th class="wpp_type_input_col"><?php _e( 'Type', 'wpp' ); ?></th>
                <th class="wpp_options_col"><?php _e( 'Options','wpp' ) ?></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach( $wp_properties['property_stats'] as $slug => $label ): ?>
                <?php $gslug = false; ?>
                <?php $group = false; ?>
                <?php if( !empty( $wp_properties['property_stats_groups'][$slug] ) ) : ?>
                  <?php $gslug = $wp_properties['property_stats_groups'][$slug]; ?>
                  <?php $group = $wp_properties['property_groups'][$gslug]; ?>
                <?php endif; ?>
              <tr class="wpp_dynamic_table_row" <?php echo ( !empty( $gslug ) ? "wpp_attribute_group=\"" . $gslug . "\"" : "" ); ?> slug="<?php echo $slug; ?>"  new_row="false">
                <td class="wpp_draggable_handle" style="width: 3%;<?php echo ( !empty( $group['color'] ) ? "background-color:" . $group['color'] : "" ); ?>">&nbsp;</td>
                <td class="wpp_attribute_name_col">
                  <ul>
                    <li>
                      <label><?php _e( 'Name', 'wpp' ); ?></label>
                      <input class="slug_setter" type="text" name="wpp_settings[property_stats][<?php echo $slug; ?>]" value="<?php echo $label; ?>" />
                    </li>
                    <li class="wpp_development_advanced_option">
                      <label><?php _e( 'Slug', 'wpp' ); ?></label>
                      <input type="text" class="slug wpp_stats_slug_field" readonly="readonly" value="<?php echo $slug; ?>" />
                      <?php if( in_array( $slug, $wp_properties['geo_type_attributes'] ) ): ?>
                      <div class="wpp_notice">
                        <span><?php echo sprintf(_e( 'Attention! This attribute ( slug ) is used by Google Validator and Address Display functionality. It is set automaticaly and can not be edited on %1$s Adding/Updating page.','wpp' ), WPP_F::property_label('singular')); ?></span>
                      </div>
                      <?php endif; ?>
                    </li>
                    <li class="wpp_development_advanced_option">
                      <label><?php _e( 'Group', 'wpp' ); ?></label>
                      <input type="text" class="wpp_attribute_group" value="<?php echo ( !empty( $group['name'] ) ? $group['name'] : "" ); ?>" />
                      <input type="hidden" class="wpp_group_slug" name="wpp_settings[property_stats_groups][<?php echo $slug; ?>]" value="<?php echo ( !empty( $gslug ) ? $gslug : "" ); ?>">
                    </li>
                    <li class="wpp_development_advanced_option">
                      <span class="wpp_delete_row wpp_link"><?php _e( 'Delete Attribute','wpp' ) ?></span>
                    </li>
                    <li>
                      <span class="wpp_show_advanced"><?php _e( 'Toggle Advanced', 'wpp' ); ?></span>
                    </li>
                  </ul>
                </td>

                <td class="wpp_type_input_col">
                  <ul>
                    <li data-wpp-attribute-setting="_attribute_type">
                      <label><?php _e( 'Common', 'wpp' ); ?></label>
                      <select class="wpp_attribute_type" name="wpp_settings[_attribute_type][<?php echo $slug; ?>]">
                        <?php foreach( (array) $wp_properties[ '_attribute_format' ] as $format_group => $format_values ) { ?>
                          <optgroup label="<?php echo esc_attr( $format_group ); ?>">
                          <?php foreach( (array) $format_values as $format_slug => $format_label ) { ?>
                          <option value="<?php echo esc_attr( $format_slug ); ?>" <?php selected( $format_slug, $wp_properties[ '_attribute_type' ][ $slug ] ); ?>><?php echo esc_attr( $format_label ); ?></option>
                          <?php } ?>
                          </optgroup>
                        <?php } ?>
                      </select>
                    </li>
                    <li class="wpp_development_advanced_option">
                        <?php
                          $available_search_options = $wp_properties['attribute_type_standard'][$wp_properties['_attribute_type'][$slug]]['search'];
                        ?>
                        <ul>
                          <li>
                            <label><?php _e('Search Input', 'wpp'); ?></label>
                            <?php if ( !empty( $available_search_options ) ) : ?>
                              <select name="wpp_settings[searchable_attr_fields][<?php echo $slug; ?>]" class="wpp_pre_defined_value_setter wpp_searchable_attr_fields">
                                <?php foreach( $available_search_options as $val => $label ) : ?>
                                <option value="<?php echo $val; ?>" <?php selected( $wp_properties['searchable_attr_fields'][$slug], $val ); ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                              </select>
                            <?php else: ?>
                              <?php _e('Nothing available', 'wpp'); ?>
                            <?php endif; ?>
                          </li>
                          <li>
                            <textarea class="wpp_attribute_pre_defined_values" name="wpp_settings[predefined_search_values][<?php echo $slug; ?>]"><?php echo $wp_properties['predefined_search_values'][$slug]; ?></textarea>
                          </li>
                        </ul>
                      <div class="clear"></div>
                    </li>
                    <li class="wpp_development_advanced_option">
                        <?php
                          $available_admin_options = $wp_properties['attribute_type_standard'][$wp_properties['_attribute_type'][$slug]]['admin'];
                        ?>
                        <ul>
                          <li>
                            <label><?php _e('Administrative', 'wpp'); ?></label>
                            <?php if ( !empty( $available_admin_options ) ) : ?>
                              <select name="wpp_settings[admin_attr_fields][<?php echo $slug; ?>]" class="wpp_pre_defined_value_setter wpp_admin_attr_fields">
                                <?php foreach( $available_admin_options as $val => $label ) : ?>
                                  <option value="<?php echo $val; ?>" <?php selected( $wp_properties['admin_attr_fields'][$slug], $val ); ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                              </select>
                            <?php else: ?>
                              <?php _e('Nothing available', 'wpp'); ?>
                            <?php endif; ?>
                          </li>
                          <li><textarea class="wpp_attribute_pre_defined_values" name="wpp_settings[predefined_values][<?php echo $slug; ?>]"><?php echo $wp_properties['predefined_values'][$slug]; ?></textarea></li>
                        </ul>
                      <div class="clear"></div>
                    </li>
                  </ul>
                </td>

                <td class="wpp_options_col">
                  <ul>
                    <li>
                      <label>
                        <input <?php if( in_array( $slug, ( ( !empty( $wp_properties['sortable_attributes'] )?$wp_properties['sortable_attributes']:array() ) ) ) ) echo " CHECKED "; ?> type="checkbox" class="slug" name="wpp_settings[sortable_attributes][]" value="<?php echo $slug; ?>" />
                        <?php _e( 'Sortable.', 'wpp' ); ?>
                      </label>
                    </li>
                    <li>
                      <label>
                        <input <?php if( is_array( $wp_properties['searchable_attributes'] ) && in_array( $slug, $wp_properties['searchable_attributes'] ) ) echo " CHECKED "; ?> type="checkbox" class="slug" name="wpp_settings[searchable_attributes][]" value="<?php echo $slug; ?>" />
                        <?php _e( 'Searchable.', 'wpp' ); ?>
                      </label>
                    </li>
                    <li class="wpp_development_advanced_option">
                      <label>
                        <input <?php if( is_array( $wp_properties['hidden_frontend_attributes'] ) && in_array( $slug, $wp_properties['hidden_frontend_attributes'] ) ) echo " CHECKED "; ?> type="checkbox" class="slug" name="wpp_settings[hidden_frontend_attributes][]" value="<?php echo $slug; ?>" />
                        <?php _e( 'Admin Only.', 'wpp' ); ?>
                      </label>
                    </li>
                    <li class="wpp_development_advanced_option">
                      <label>
                        <input <?php if( is_array( $wp_properties['column_attributes'] ) && in_array( $slug, $wp_properties['column_attributes'] ) ) echo " CHECKED "; ?> type="checkbox" class="slug" name="wpp_settings[column_attributes][]" value="<?php echo $slug; ?>" />
                        <?php _e( 'Show in Overview.', 'wpp' ); ?>
                      </label>
                    </li>
                  </ul>
                </td>
              </tr>
              <?php endforeach;?>
            </tbody>

            <tfoot>
              <tr>
                <td colspan="5">
                  <input type="button" class="wpp_add_row button-secondary" value="<?php _e( 'Add Row','wpp' ) ?>" />
                </td>
              </tr>
            </tfoot>

          </table>

        </td>
      </tr>

      <tr>
        <td colspan="2">
          <h3><?php printf( __( '%1s Types','wpp' ), WPP_F::property_label() ); ?></h3>
          <table id="wpp_inquiry_property_types" class="wpp_option_table ud_ui_dynamic_table wpp_clean" allow_random_slug="true">
          <thead>
            <tr>
              <th><?php _e( 'Type','wpp' ) ?></th>
              <th><?php _e( 'Settings','wpp' ) ?></th>
              <th><?php _e( 'Hidden Attributes','wpp' ) ?></th>
              <th><?php _e( 'Inherit from Parent','wpp' ) ?></th>
            </tr>
          </thead>
          <tbody>
          <?php  foreach( $wp_properties['property_types'] as $property_slug => $label ):  ?>

          <tr class="wpp_dynamic_table_row" slug="<?php echo $property_slug; ?>"  new_row='false'>
            <td>

              <ul>
                <li>
                  <label>
                    <?php _e( 'Title:', 'wpp' ); ?>
                    <input class="slug_setter" type="text" name="wpp_settings[property_types][<?php echo $property_slug; ?>]" value="<?php echo $label; ?>" />
                  </label>
                </li>
                <li>
                  <label>
                    <?php _e( 'Slug:', 'wpp' ); ?>
                    <input type="text" class="slug" readonly="readonly" value="<?php echo $property_slug; ?>" />
                  </label>
                </li>

                <li><span class="wpp_delete_row wpp_link"><?php _e( 'Delete', 'wpp' ); ?></span></li>
              </ul>

            </td>

            <td>

              <ul>
                <li>
                  <label for="<?php echo $property_slug; ?>_searchable_property_types">
                    <input class="slug" id="<?php echo $property_slug; ?>_searchable_property_types" <?php if( is_array( $wp_properties['searchable_property_types'] ) && in_array( $property_slug, $wp_properties['searchable_property_types'] ) ) echo " CHECKED "; ?> type="checkbox" name="wpp_settings[searchable_property_types][]" value="<?php echo $property_slug; ?>" />
                    <?php _e( 'Searchable','wpp' ) ?>
                  </label>
                </li>

                <li>
                  <label for="<?php echo $property_slug; ?>_location_matters">
                    <input class="slug" id="<?php echo $property_slug; ?>_location_matters"  <?php if( in_array( $property_slug, $wp_properties['location_matters'] ) ) echo " CHECKED "; ?> type="checkbox"  name="wpp_settings[location_matters][]" value="<?php echo $property_slug; ?>" />
                    <?php _e( 'Location Matters','wpp' ) ?>
                  </label>
                </li>

                <?php $property_type_settings = apply_filters( 'wpp_property_type_settings', array(), $property_slug ); ?>
                <?php foreach( (array)$property_type_settings as $property_type_setting ) : ?>
                  <li>
                  <?php echo $property_type_setting; ?>
                  </li>
                <?php endforeach; ?>
                <?php do_action( 'wpp_admin_tools_property_type_options', $property_slug ); ?>
              </ul>

            </td>

            <td>
              <ul class="wp-tab-panel wpp_hidden_property_attributes wpp_something_advanced_wrapper">

              <li class="wpp_show_advanced" wrapper="wpp_something_advanced_wrapper"><?php _e( 'Toggle Attributes Selection', 'wpp' ); ?></li>

              <?php foreach( $wp_properties['property_stats'] as $property_stat_slug => $property_stat_label ) { ?>
              <li class="wpp_development_advanced_option">
                <input id="<?php echo $property_slug . "_" .$property_stat_slug;?>_hidden_attributes" <?php if( isset( $wp_properties['hidden_attributes'][$property_slug] ) && in_array( $property_stat_slug, $wp_properties['hidden_attributes'][$property_slug] ) ) echo " CHECKED "; ?> type="checkbox" name="wpp_settings[hidden_attributes][<?php echo $property_slug;?>][]" value="<?php echo $property_stat_slug; ?>" />
                <label for="<?php echo $property_slug . "_" .$property_stat_slug;?>_hidden_attributes">
                  <?php echo $property_stat_label;?>
                </label>
              </li>
              <?php } ?>

              <?php if( !$wp_properties['property_stats']['parent'] ) { ?>
              <li class="wpp_development_advanced_option">
                <input id="<?php echo $property_slug; ?>parent_hidden_attributes" <?php if( isset( $wp_properties['hidden_attributes'][$property_slug] ) && in_array( 'parent', $wp_properties['hidden_attributes'][$property_slug] ) ) echo " CHECKED "; ?> type="checkbox" name="wpp_settings[hidden_attributes][<?php echo $property_slug;?>][]" value="parent" />
                <label for="<?php echo $property_slug; ?>parent_hidden_attributes"><?php _e( 'Parent Selection', 'wpp' ); ?></label>
              </li>
              <?php } ?>

              </ul>
            </td>

             <td>
              <ul class="wp-tab-panel wpp_inherited_property_attributes wpp_something_advanced_wrapper">
                <li class="wpp_show_advanced" wrapper="wpp_something_advanced_wrapper"><?php _e( 'Toggle Attributes Selection', 'wpp' ); ?></li>
                <?php foreach( $wpp_inheritable_attributes as $property_stat_slug => $property_stat_label ): ?>
                <li class="wpp_development_advanced_option">
                  <input id="<?php echo $property_slug . "_" .$property_stat_slug;?>_inheritance" <?php if( isset( $wp_properties['property_inheritance'][$property_slug] ) && in_array( $property_stat_slug, $wp_properties['property_inheritance'][$property_slug] ) ) echo " CHECKED "; ?> type="checkbox" name="wpp_settings[property_inheritance][<?php echo $property_slug;?>][]" value="<?php echo $property_stat_slug; ?>" />
                  <label for="<?php echo $property_slug . "_" .$property_stat_slug;?>_inheritance">
                    <?php echo $property_stat_label;?>
                  </label>
                </li>
                <?php endforeach; ?>
                <li>
              </ul>
            </td>

          </tr>

          <?php endforeach; ?>
          </tbody>

          <tfoot>
            <tr>
              <td colspan="4">
                <input type="button" class="wpp_add_row button-secondary" value="<?php _e( 'Add Row','wpp' ) ?>" />
              </td>
            </tr>
          </tfoot>

          </table>
        </td>
      </tr>
    </tbody>
  </table>

    <?php
  }



}


