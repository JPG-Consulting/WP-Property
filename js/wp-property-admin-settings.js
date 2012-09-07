//** If jQuery exists */
if( typeof jQuery === 'function' ) {

  //** Initial wpp object (new or extended) */
  var wpp = jQuery.extend( true, {
    screen: 'xmli_overview',
    tabs_loaded: false,
    submit_attempt: false,
    load_timeout: 0,
    timer_delta: 100
  }, typeof wpp === 'object' ? wpp : {} );

  //** New object SETTINGS in WPP object */
  wpp.settings = {};

  /**
   * Helps us update sticky sidebar when we need
   **/
  wpp.settings.update_sticky_sidebar = function( options ) {
    'use strict';

    if ( typeof options != 'object' ) {
      options = {padding: 30,speed: 250}
    }

    if( typeof jQuery.prototype.stickySidebar === 'function' ) {
      jQuery( '.wpp_actions_bar' ).stickySidebar(options);
    }
  };

  //** Do everything of Ready */
  jQuery( document ).ready( function() {

    //** Init sticky sidebar (right menu and save button) */
    wpp.settings.update_sticky_sidebar( false );

    /**
     * Display a full-width notice.
     **/
    wpp.settings.core_notice = function( message, type ) {
      'use strict';

      //** Hide message area if message is empty */
      if( !message || message === '' ) {
        jQuery( '.wpxi_core_notice' ).fadeOut(3000, function(){
          jQuery(this).hide(500, function(){
            jQuery(this).remove();
            wpp.settings.update_sticky_sidebar( false );
          });
        });
        return;
      }

      //** If there is no area for message yet - place it. */
      if( !jQuery( '.wpxi_core_notice' ).length ) {
        jQuery( '<div class="wpxi_core_notice"></div>' ).insertAfter( jQuery( '.wpp-ui-header' ) );
      }

      //** Show message */
      jQuery( '.wpxi_core_notice' ).show().empty().html( message );

    };

    //** Prevent submission if not all settings loaded */
    jQuery( 'form.wpp_settings' ).bind('submit', function() {
      if ( !wpp.tabs_loaded ) {
        jQuery( '.wpp_sidebar_response' ).html('Saving...');
        wpp.submit_attempt = true;
        return false;
      }
    });

    //** Listen to 'tabs_loaded' event to do some extra things */
    jQuery(document).bind('tabs_loaded', function(){
      jQuery( 'form.wpp_settings' ).trigger('submit');
    });

    //** Get all menu links */
    var section_links = jQuery( 'ul.wpp_settings li span.wpp_link' );

    //** For each settings section run GET UI in background  */
    section_links.each(function(k, v) {
      var wrapper = jQuery( '.wpxi-ajax-container' );
      var section_class    = jQuery( v ).data('wpp_section_class')?jQuery( v ).data('wpp_section_class'):'self';
      var section_callback = jQuery( v ).data('wpp_toggle_ui');

      if ( !jQuery( '.wpp_settings_section.wpp_section_'+section_class+'_'+section_callback ).length ) {
        setTimeout(function() {
          wpp.ajax( 'get_ui', {ui: section_callback, 'class': section_class}, function( response ) {
            if ( !jQuery( '.wpp_settings_section.wpp_section_'+section_class+'_'+section_callback ).length ) {
              wrapper.append( '<div style="display:none;" class="wpp_settings_section wpp_section_'+response.id+'" data-menu-item-ui="'+section_callback+'" data-menu-item-class="'+section_class+'">'+response.ui+'</div>' );
              if ( jQuery( '.wpp_settings_section' ).length == section_links.length ) {
                wpp.tabs_loaded = true;
                if ( wpp.submit_attempt ) jQuery(document).trigger('tabs_loaded');
              }
            }
          });
        }, wpp.load_timeout+=wpp.timer_delta);
      }
    });

    //** Listen to "click" event on menu buttons */
    jQuery( '[data-wpp_toggle_ui]' ).bind("click", function( e ) {
      'use strict';

      var trigger = jQuery( this );

      //** Prevent clicking on active link because it doesn't make any sence */
      if( trigger.hasClass( 'wpp_active' ) && !wpp.developer_mode ) {
        return;
      }

      var wrapper = jQuery( '.wpxi-ajax-container' );

      //** Determine class of setting section. 'self' means Core functionality */
      var trigger_class = trigger.data( 'wpp_section_class' )?trigger.data( 'wpp_section_class' ):'self';

      //** If settings section already exists on the page */
      if ( jQuery( '.wpp_settings_section.wpp_section_'+trigger_class+'_'+trigger.data( 'wpp_toggle_ui' ) ).length ) {

        //** Remove notice if exists */
        wpp.settings.core_notice( false );

        //** Fade out visible section and show new selected */
        jQuery( '.wpp_settings_section:visible' ).fadeOut(500, function() {
          jQuery( '.wpp_settings_section' ).hide();
          jQuery( '.wpp_settings_section.wpp_section_'+trigger_class+'_'+trigger.data( 'wpp_toggle_ui' ) ).fadeIn(500, function(){
            jQuery( this ).show();
            wpp.settings.update_sticky_sidebar( false );
          });
        });

        //** Remove active class from ALL menu links */
        jQuery( '[data-wpp_toggle_ui]' ).removeClass( 'wpp_active' );

        //** Add active class to current menu link */
        trigger.addClass( 'wpp_active' );

        //** Set cookie with the data of current item */
        jQuery.cookie("wpp_settings_tabs", trigger_class+'|'+trigger.data( 'wpp_toggle_ui' ), {expires: 30});

        //** If settings section doesn't exist yet */
      } else {

        //** Fade out visible section */
        jQuery( '.wpp_settings_section:visible' ).fadeOut(500);

        //** Request for new section from API */
        wpp.ajax( 'get_ui', {ui: trigger.data( 'wpp_toggle_ui' ), 'class': trigger.data( 'wpp_section_class' )}, function( response ) {

          //** Remove notice if exists */
          wpp.settings.core_notice( false );

          if( response.success ) {

            //** Remove active class from ALL menu links */
            jQuery( '[data-wpp_toggle_ui]' ).removeClass( 'wpp_active' );

            //** Add active class to current menu link */
            trigger.addClass( 'wpp_active' );

            //** Set cookie with the data of current item */
            jQuery.cookie("wpp_settings_tabs", trigger_class+'|'+trigger.data( 'wpp_toggle_ui' ), {expires: 30});

          } else {
            //** Show error message in notice area */
            wpp.settings.core_notice( response.message ? response.message : 'Could not load UI section.' );
          }

          //** New settings section */
          var section = wrapper.append( '<div class="wpp_settings_section wpp_section_'+response.id+'" data-menu-item-ui="'+trigger.data( 'wpp_toggle_ui' )+'" data-menu-item-class="'+trigger_class+'">'+response.ui+'</div>' );

          //** Show new section */
          section.fadeIn(500, function(){
            section.show();
            wpp.settings.update_sticky_sidebar( false );
          });
        });
      }
    });

    //** Activate settings section in order to the value from cookie */
    if ( jQuery.cookie("wpp_settings_tabs") ) {
      if ( jQuery.cookie("wpp_settings_tabs") == 'self|main' ) {
        jQuery( '.wpp_settings_section.wpp_section_self_main' ).fadeOut().show(500, function(){
          jQuery(this).fadeIn(500);
          wpp.settings.update_sticky_sidebar( false );
        });
      } else {
        var callback = String(jQuery.cookie("wpp_settings_tabs")).split("|");
        if ( callback.length > 1 ) {
          jQuery( '[data-wpp_toggle_ui="'+callback[1]+'"][data-wpp_section_class="'+callback[0]+'"]' ).trigger("click");
        } else {
          jQuery( '.wpp_settings_section.wpp_section_self_main' ).fadeOut().show(500, function(){
            jQuery(this).fadeIn(500);
            wpp.settings.update_sticky_sidebar( false );
          });
        }
      }
    }

    /**
     * Monitor changes to the User Key, and validate when changed.
     * @todo Disable form saving while they key is validating against server.
     */
    jQuery( '.wpp_ud_customer_key' ).change( function() {
      wpp.log( 'User API key changed.' );
    });

    /**
     * Handles form saving
     * Do any validation/data work before the settings page form is submitted
     * @author korotkov@UD
     */
    jQuery( 'form.wpp_settings' ).submit(function( form ) {
      var error_field = {object:false,menu_item:false};

      //** The next block make validation for required fields    */
      jQuery(":input[validation_required=true],.wpp_required_field :input,:input[required],:input.slug_setter", jQuery('form.wpp_settings .wpxi-ajax-container')).each(function(){

        //** we allow empty value if dynamic_table has only one row */
        var dynamic_table_row_count = jQuery(this).closest('.wpp_dynamic_table_row').parent().children('tr.wpp_dynamic_table_row').length;

        if ( !jQuery(this).val() && dynamic_table_row_count != 1 ) {
          error_field.object = this;
          error_field.menu_item = jQuery( '[data-wpp_toggle_ui="'+jQuery(this).parents( '.wpp_settings_section' ).data('menu-item-ui')+'"][data-wpp_section_class="'+jQuery(this).parents( '.wpp_settings_section' ).data('menu-item-class')+'"]' );
          return false;
        }

      });

      //** if error_field object is not empty then we've error found */
      if ( error_field.object != false ) {
        //** do focus on tab with error field */
        if( typeof error_field.menu_item != 'undefined' ) {
          error_field.menu_item.trigger("click");
        }
        //** mark error field and remove mark on keyup */
        jQuery(error_field.object).addClass('ui-state-error').one('keyup',function(){jQuery(this).removeClass('ui-state-error');});
        jQuery(error_field.object).focus();
        return false;
      }
    });

    //** @author korotkov@ud */
    jQuery( '#wpp_inquiry_attribute_fields .wpp_dynamic_table_row' ).live('added', function(e){
      jQuery( '.wpp_attribute_type', jQuery( e.target ) ).trigger('change');
    });

    //** @author korotkov@ud */
    jQuery( '.wpp_attribute_type' ).live('change', function(e){
      //** Current select */
      var element = jQuery( e.target );

      //** Type of attribute */
      var type = String(element.val());

      //** Selects to change */
      var search_select = jQuery( 'select.wpp_searchable_attr_fields', element.parents('.wpp_dynamic_table_row') );
      var admin_select  = jQuery( 'select.wpp_admin_attr_fields', element.parents('.wpp_dynamic_table_row') );

      //** Objects with available options */
      var search_options = wpp.wp_properties.attribute_type_standard[type].search;
      var admin_options = wpp.wp_properties.attribute_type_standard[type].admin;

      //** Fill search select */
      search_select.empty();
      jQuery.each(search_options, function(k,v){
        search_select.append('<option value="'+k+'">'+v+'</option>');
      });
      search_select.trigger('change');

      //** Fill admin select */
      admin_select.empty();
      jQuery.each(admin_options, function(k,v){
        admin_select.append('<option value="'+k+'">'+v+'</option>');
      });
      admin_select.trigger('change');

    });

  });

}