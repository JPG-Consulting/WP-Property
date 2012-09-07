/**
 * XMLI JavaScript
 *
 * jshint forin:true, noarg:true, noempty:true, bitwise:true, undef:true, curly:true, browser:true, devel:true, jquery:true, indent:4, maxerr:50
 */

var wpp = jQuery.extend( true, {}, typeof wpp === 'object' ? wpp : {} );

/*
if( typeof window.history.pushState === 'function' ) {
  wpp.log( 'wpp::xmli window.history.pushState' );
  if( wpp.screen ) {
    history.replaceState( {}, 'New Schedule', 'edit.php?post_type=property&page=wpp_property_import#new_schedule/' );
  } else {
    history.replaceState( {}, 'Editing', 'edit.php?post_type=property&page=wpp_property_import#edit#' + wpp.xmli.current.schedule_id );
  }
} */


/**
 * {}
 *
 */
jQuery( document ).ready( function() {
  'use strict';

  if( typeof wpp.log !== 'function' ) {
    console.error( 'WPP not loaded.' );
    return false;
  }

  if( typeof google === 'object' ) {
    jQuery( '[data-wpxi_action=toggle_visualization]' ).show();
  }

  wpp.xmli.dom_ready();

  /**
   * Monitors updates to editor, triggering draft status. Ensures at least a 30 second break between resaving.
   *
   * @todo Need to make sure this does not trigger if form is being saved - i.e. enter is pressed.
   */
  jQuery( document ).bind( 'wpp::xmli::editor_updated', function( e, args ) {
    wpp.log( 'wpp::xmli::editor_updated(); Trigger: ' + args.trigger.name );

    if( wpp.xmli.events.auto_save ) {
      clearInterval( wpp.xmli.events.auto_save );
    }

    /**
     * Schedule an auto-save event in a few seconds. This prevents it from being fired if form is saved manually in the meantime
     *
     */
    wpp.xmli.events.auto_save = setTimeout( function( args ) {

      if( wpp.xmli.editor.last_saved && ( new Date().getTime() - wpp.xmli.editor.last_saved ) < 60000 ) {
        return false;
      }

      wpp.xmli.save_schedule({ ajax_args: { post_status: 'draft' }, event_type: 'auto-save' });
    }, 3000 );

  });


  /**
   * Called when editor is loaded and is configured for RETS
   */
  jQuery( document ).bind( 'wpp::xmli::rets::ready', function( e, args ) {
    'use strict'; wpp.log( 'wpp::xmli::rets::ready', arguments );
    wpp.xmli.rets_ready( args );
  });


  /**
   * Called when editor is loaded and is configured for RETS
   */
  jQuery( document ).bind( 'wpp::xmli::xml::ready', function( e, args ) {
    'use strict'; wpp.log( 'wpp::xmli::xml::ready', arguments );
    wpp.xmli.xml_ready( args );
  });


  /**
   * {}
   */
  jQuery( document ).bind( 'wpp::xmli::is_draft', function( e, args ) {
    'use strict'; wpp.log( 'wpp::xmli::is_draft', arguments );
    jQuery( 'h2.wpp-title span.wpxi-status' ).remove();
    jQuery( 'h2.wpp-title' ).append( '<span class="wpxi-status">Draft Mode</span>' );
  });


  /**
   * {}
   */
  jQuery( document ).bind( 'wpp::xmli::is_publish', function( e, args ) {
    'use strict'; wpp.log( 'wpp::xmli::is_publish', arguments );
    jQuery( 'h2.wpp-title span.wpxi-status' ).remove();
  });


  /**
   * {}
   */
  jQuery( document ).bind( 'wpp::xmli::is_auto-draft', function( e, args ) {
    'use strict'; wpp.log( 'wpp::xmli::is_auto-draft', arguments );
    wpp.log( 'wpp::xmli::is_auto-draft' );
  });


  /**
   * Event Listener for changes requiring a Source Revalidation
   *
   */
  jQuery( document ).bind( 'wpp::xmli::revalidation_required', function( e, args ) {
    wpp.log( 'wpp::xmli::revalidation_required', arguments );
  });


  /**
   * Quick-fetch to check the source URL.
   *
   */
  jQuery( 'input.wpxi_schedule_url' ).live( 'change', function( e ) {
    if( jQuery( 'input.wpxi_schedule_url' ).val() ) {
      wpp.xmli.ajax( 'get_header_data', { url: jQuery( 'input.wpxi_schedule_url' ).val() }, function( result ) { wpp.xmli.current.headers = result.headers ? result.headers : {};
    });}
  });


  /**
   * {}
   *
   */
  jQuery( '*[data-wpxi_action]' ).live( 'click', function( e ) {
    return wpp.xmli.action( jQuery( this ).attr( 'data-wpxi_action' ), e, this );
  });


  /**
   * {}
   *
   */
  jQuery( 'input[type=text][data-wpxi_action], select[data-wpxi_action], options[data-wpxi_action]' ).live( 'change', function( e ) {
    return wpp.xmli.action( jQuery( this ).attr( 'data-wpxi_action' ), e, this );
  });


  /**
   * {}
   *
   */
  jQuery( '.wpxi_sort_attribute_rows' ).live( 'click', function() {
    wpp.xmli.sort_attributes();
  });


  /**
   * {}
   *
   */
  jQuery( 'input[name="wpxi[root_element"]' ).live( 'change', function() {
    jQuery( this ).val( jQuery( this ).val().replace( /'/g, '"' ) );
  });


  /**
   * Saves or updates current schedule.
   *
   */
  jQuery( 'form.wpxi_editor' ).live( 'submit', function( e ) {
    wpp.log( 'form.wpxi_editor: submit', arguments );
    e.preventDefault();
    wpp.xmli.save_schedule();
  });


  /**
   * {}
   *
   */
  jQuery( 'table.wpxi_attribute_mapper select.wpxi_attribute_dropdown' ).live( 'change', function() {
    wpp.xmli.update_unique_id_dropdown();
  });


});


/**
 * Initial function fired off when DOM is ready.
 *
 */
wpp.xmli.dom_ready = function() {
  'use strict'; wpp.log( 'wpp.xmli.dom_ready()', arguments );

  wpp.xmli.strings = ( typeof l10n === 'object' ? l10n : {} );

  var current_page = false;

  if( window.location.hash ) {

    if( window.location.hash && window.location.hash.replace( '#', '' ) ) {
      wpp.xmli.set_id( window.location.hash.replace( '#', '' ) );
      wpp.xmli.show_editor();
    } else if ( window.location.hash === "#add_new_schedule" ) {
      wpp.xmli.show_editor();
    }

  }

  /**
   * Update UI for any currently running jobs.
   *
   */
  if( wpp.xmli.in_progress && !jQuery.isEmptyObject( wpp.xmli.in_progress ) ) {
    jQuery.each( wpp.xmli.in_progress, function( i, schedule_id ) {
      wpp.xmli.import_job( parseInt( schedule_id ) );
    });
  }

  jQuery( window ).resize( function() {} );

};


/**
 * Primary Triggered Event Handler
 *
 */
wpp.xmli.action = function( action, e, _this ) {
  'use strict'; wpp.log( 'wpp.xmli.action()', arguments );

  if( jQuery( _this ).hasClass( 'wpp_processing' ) ) {
    return false;
  }

  switch( action ) {

    /**
     * Overview Actions: Start Import
     *
     */
    case 'import_job':
      wpp.xmli.import_job( jQuery( _this ).closest( '.wpxi_schedule_row' ).attr( 'data-schedule-id' ), _this );
    break;


    /**
     * Overview Actions: Delete Schedule
     *
     */
    case 'delete_schedule':
      wpp.xmli.delete_schedule( jQuery( _this ).closest( '.wpxi_schedule_row' ).attr( 'data-schedule-id' ), _this );
    break;


    /**
     * Overview Actions: Delete content created by a Schedule
     *
     */
    case 'remove_content':
      if( confirm( wpp.xmli.strings.verify_action ) ) {
        wpp.xmli.remove_content( jQuery( _this ).closest( '.wpxi_schedule_row' ).attr( 'data-schedule-id' ), _this );
      }
    break;


    /**
     * Overview Actions: Download Backup
     *
     */
    case 'download_backup':
      return true;
    break;


    /**
     * Overview Actions: Edit Schedule
     *
     */
    case 'edit_schedule':
      wpp.xmli.set_id( jQuery( _this ).closest( '.wpxi_schedule_row' ).attr( 'data-schedule-id' ), _this );
      wpp.xmli.show_editor();
    break;


    /**
     * Overview Actions: Add New Schedule
     *
     */
    case 'add_new_schedule':
      wpp.xmli.show_editor();
    break;


    /**
     * {}
     *
     */
    case 'toggle_backup_uploader':
      jQuery( _this ).toggleClass( 'wpp_on' );
      jQuery( '.wpxi_backup_uploader' ).toggle();
    break;


    /**
     * {}
     *
     */
    case 'toggle_visualization':

      if( !jQuery( '.wpxi_chart_wrapper:visible' ).length ) {
        jQuery( _this ).addClass( 'wpp_on' );
        jQuery( '.wpxi_chart_wrapper' ).show();
        wpp.xmli.render_visualization();
      } else {
        jQuery( _this ).removeClass( 'wpp_on' );
        jQuery( '.wpxi_chart_wrapper' ).hide();
      }

    break;


    /**
     * Toggles selection of all checkboxes in attribute map table
     *
     */
    case 'preview_raw_xml':
      wpp.xmli.preview_raw_xml( _this );
    break;


    /**
     * Toggles selection of all checkboxes in attribute map table
     *
     */
    case 'check_all_attributes':
      _this.checked ? jQuery( 'tr.wpp_dynamic_table_row [name^="wpxi[map]"]:checkbox' ).prop( 'checked', true ) : jQuery( 'tr.wpp_dynamic_table_row [name^="wpxi[map]"]:checkbox' ).prop( 'checked', false );
    break;


    /**
     * Triggered when source type changed in dropdown.
     *
     */
    case 'source_type_change':
      wpp.xmli.source_type_change( _this );
    break;


    /**
     * Validate source when "Source is Good" label is pressed. Third argument forces cache refresh.
     *
     */
    case 'source_validation':
      wpp.xmli.source_validation( _this );
    break;


    /**
     * {}
     *
     */
    case 'preview_listings':
      wpp.xmli.preview_listings();
    break;


    /**
     * {}
     *
     */
    case 'toggle_filters':
      _this = _this ? _this : jQuery( '[data-wpxi_action=toggle_filters]' );
      jQuery( '.wpxi-ui[data-wpxi-ui="filters"]' ).fadeToggle( 'fast', function() {
        jQuery( this ).is( ':visible' ) ? jQuery( _this ).addClass( 'wpp_active' ) : jQuery( _this ).removeClass( 'wpp_active' );
      });
    break;


    /**
     * {}
     *
     */
    case 'toggle_import_settings':
      _this = _this ? _this : jQuery( '[data-wpxi_action=toggle_import_settings]' );
      jQuery( '.wpxi-ui[data-wpxi-ui="advanced_options"]' ).fadeToggle( 'fast', function() {
        jQuery( this ).is( ':visible' ) ? jQuery( _this ).addClass( 'wpp_active' ) : jQuery( _this ).removeClass( 'wpp_active' );
      });
    break;


    /**
     * {}
     *
     */
    case 'toggle_server_query':
      _this  = _this ? _this : jQuery( '[data-wpxi_action=toggle_server_query]' );
      jQuery( '.wpxi-ui[data-wpxi-ui="server_query"]' ).fadeToggle( 'fast', function() {
        jQuery( this ).is( ':visible' ) ? jQuery( _this ).addClass( 'wpp_active' ) : jQuery( _this ).removeClass( 'wpp_active' );
      });
    break;


    /**
     * {}
     *
     */
    case 'toggle_attribute_map':
      _this  = _this ? _this : jQuery( '[data-wpxi_action=toggle_attribute_map]' );
      jQuery( '.wpxi-ui[data-wpxi-ui="attribute_map"]' ).fadeToggle( 'fast', function() {
        jQuery( this ).is( ':visible' ) ? jQuery( _this ).addClass( 'wpp_active' ) : jQuery( _this ).removeClass( 'wpp_active' );
      });
    break;


    /**
     * {}
     *
     */
    case 'save_schedule':
      wpp.xmli.save_schedule();
    break;

    /**
     * {}
     *
     */
    case 'server_query':
      wpp.xmli.contextual( 'server_query_result', 'processing');
      wpp.xmli.save_schedule({
        success: function() {
          jQuery( '.wpxi_sidebar_response' ).delay( 1000 ).fadeOut( "slow" );
          wpp.xmli.ajax( 'rets_query', { schedule_id: wpp.xmli.editor.object.schedule_id }, function( result ) {
            if( result.success ) {
              wpp.xmli.contextual( 'server_query_result', 'good', result.message );
            } else {
              if( result.message ) {
                wpp.xmli.contextual( 'server_query_result', 'error', result.message );
              } else {
                wpp.xmli.contextual( 'server_query_result', 'error', wpp.xmli.strings.internal_server_error );
              }
            }
          });
        }
      });
    break;

    /**
     * Do nothing on default.
     *
     */
    default:
      e.preventDefault();
    break;

  }

  /* Must return false for consitency - otherwise we'll have buttons submitting forms */
  return false;

};


/**
 * Standard AJAX Request handler for XMLI
 *
 * @since 3.3.0
 * @author potanin@UD
 */
wpp.xmli.ajax = function( wpxi_action, ajax_args, callback, args ) {
  'use strict'; wpp.log( 'wpp.xmli.ajax( ' + wpxi_action + ' )' /*, arguments*/ );

  /**
   * Perform standard UI operations, error handling and standardize response object.
   *
   */
  var response = function( jqXHR ) {
    'use strict'; wpp.log( 'wpp.xmli.ajax.response()', arguments );

    var result = {};

    wpp.xmli.toggle_loader( 'stop' );

    try {

      result.json_response = jQuery.parseJSON( jqXHR.responseText );

      if( typeof result.json_response != 'object' || jqXHR.responseText === '' ) {
        throw new Error( wpp.xmli.strings.ajax_response_empty );
      }

      if( jqXHR.status === 500 ) {
        throw new Error( wpp.xmli.strings.internal_server_error );
      }

    } catch( error ) {

      /* Clear out error log on next request */
      jQuery( document ).bind( 'wpp.xmli.ajax.beforeSend', function() {
        wpp.xmli.contextual( wpxi_action, '', '' );
      });

      error.message = 'AJAX Error: ' + ( error.message ? error.message : 'Unknown.' );
      wpp.log( 'wpp.xmli.ajax.response() - Thrown Error: ' + error.message , 'error' );
      wpp.xmli.contextual( wpxi_action, 'ajax_error',  error.message );
      result.json_response = { success: false, message: error.message }
    }

    result = jQuery.extend( true, {
      success: false,
      status: '',
      message: '',
      ui: jqXHR.responseText
    }, result.json_response );


    if( jqXHR.statusText === 'timeout' ) {
      result.status = 408;
      result.message = result.message ? result.message : wpp.xmli.strings.server_timeout;
    }

    result.schedule_id = result.schedule_id ? parseInt( result.schedule_id ) : false;
    result.status = result.status ? result.status : jqXHR.status;

    callback( result );

  };

  return jQuery.ajax( args = jQuery.extend( true, {
    url: ajaxurl + '?action=wpxi_handler&wpxi_action=' + wpxi_action,
    data: jQuery.extend( true, { args: ajax_args }, typeof data === 'object' ? data : {} ),
    dataType: 'json',
    type: 'POST',
    /* timeout: ( ( wpp._server.max_execution_time - 10 ) * 1000 ),*/
    beforeSend: function ( xhr ) {
      jQuery( document ).trigger( 'wpp.xmli.ajax.beforeSend' );
      xhr.overrideMimeType( 'application/json; charset=utf-8' );
    },
    complete: function( jqXHR ) {
      response( jqXHR );
    }
  }, typeof args === 'object' ? args : {} ) );

};


/**
 * Dynamically sort a table column.
 *
 * @todo Move to global WPP - potanin@UD
 * @since 3.3.0
 * @author potanin@UD
 */
wpp.xmli.sort_attributes = function() {
  'use strict'; wpp.log( 'wpxi_sort_attribute_rows()', arguments );

  var list_wrapper = jQuery( ".wpxi_attribute_mapper" );
  var listitems = jQuery( ".wpp_dynamic_table_row", list_wrapper ).get();

  listitems.sort( function( a, b ) {

    var compA = jQuery( "select.wpxi_attribute_dropdown option:selected", a ).text();
    var compB = jQuery( "select.wpxi_attribute_dropdown option:selected", b ).text();

    if( compA === undefined ) {
      compA = 0;
    } else {
      compA = compA;
    }

    if( compB === undefined ) {
      compB = 0;
    } else {
      compB = compB;
    }

    var index = ( compA < compB ) ? -1 : ( compA > compB ) ? 1 : 0;

    return index;

  });

  jQuery.each( listitems, function( idx, itm ) {
    list_wrapper.append( itm );
  });

};


/**
 * Removes all listings and attachments created by a particular schedule.
 *
 * @since 3.3.0
 * @author potanin@UD
 */
wpp.xmli.remove_content = function( schedule_id, _this ) {
  'use strict'; wpp.log( 'wpp.xmli.remove_content()', arguments );

  var args = jQuery.extend( true, {
    action: 'remove_content'
  }, typeof _this === 'object' ? _this : {} );

  wpp.xmli.import_job( jQuery( _this ).closest( '.wpxi_schedule_row' ).attr( 'data-schedule-id' ), args );

};


/**
 * Triggered when Schedule Editor is loaded, RETS is selected as source type, and API connection has been made
 *
 * @since 3.3.0
 * @author potanin@UD
 */
wpp.xmli.rets_ready = function( object ) {
  'use strict'; wpp.log( 'wpp.xmli.rets_ready()', arguments );

  object = jQuery.extend( true, object, wpp.xmli.editor.object );
  object.api_server = object.api_server ? object.api_server : {};

  /* Check if API connection has been made. If not - stop event */
  if( typeof object.api_server === 'undefined' || typeof object.api_server.connect === 'undefined' ) {
    return;
  }
  /* Check status of API connection. We mustn't continue event if status is false */
  if( typeof object.api_server.connect === 'object' ) {
    object.api_server.status = object.api_server.connect.success == '1' ? 'good' : 'error';
    wpp.xmli.contextual( 'import_heading', object.api_server.status, object.api_server.connect.message );
    if( object.api_server.connect.success != '1' ) {
      return;
    }
  } else {
    return;
  }

  if( typeof jQuery.prototype.dform !== 'function' ) {
    wpp.log( 'jQuery.dForm plugin not available. Required to generated RETS filter UI.', 'error' );
    return;
  }

  wpp.xmli.open_section( 'server_query' );

  /* Connection is established */
  if( typeof object.api_server.filters === 'object' ) {
    //console.log( object.api_server );

    try {
      var filters = [];

      /**
       * Determine if field is selected or checked
       *
       */
      var set_dform_values = function( filter ){
        if( typeof object.server_query === 'object' && typeof object.server_query[ filter.system_name ] !== 'undefined' ) {

          var filter_value = object.server_query[ filter.system_name ];
          if( filter.query_type == 'range' ) {
            if( typeof filter_value.min == 'undefined' || typeof filter_value.max == 'undefined' ) {
              return filter;
            } else {
              if ( /\[min\]/.test( filter.name ) ) filter_value = filter_value.min;
              else if ( /\[max\]/.test( filter.name ) ) filter_value = filter_value.max;
              else return filter;
            }
          }

          switch( filter.type ) {
            case 'radiobuttons':
              for( var a in filter.options ) {
                if( a == filter_value ) {
                  filter.options[a].checked = 'checked';
                }
              }
              break;
            case 'select':
              for( var a in filter.options ) {
                if( a == filter_value ) {
                  filter.options[a].selected = 'selected';
                }
              }
              break;
            case 'checkboxes':
              for( var a in filter.options ) {
                for( var e in filter_value ) {
                  if( a == filter_value[e] ) {
                    filter.options[a].checked = 'checked';
                  }
                }
              }
              break;
            case 'checkbox':
              if ( filter_value == 'on' ) {
                filter.checked = 'checked';
              }
              break;
            case 'text':
              filter.value = filter_value;
              break;
          }
        }
        return filter;
      }

      //** Prepare filters data for rendering */
      for( var i in object.api_server.filters ) {
        if(  typeof object.api_server.filters[i].dform == 'undefined' ) {
          continue;
        }
        filters[i] = object.api_server.filters[i].dform;

        var classification = typeof filters[i].classification != 'undefined' ? ' wpxi_categorical_classification' : '';

        if( typeof filters[i].length == 'number' ) {

          var range = [];
          if ( typeof filters[i][0]!='undefined' && typeof filters[i][1]!='undefined'){
            range[0] = {
              'type' : 'div',
              'class' : "wpxi_server_query_range_item",
              'html' : set_dform_values( filters[i][0] )
            };
            range[1] = {
              'type' : 'div',
              'class' : "wpxi_server_query_range_delim",
              'html' : '&nbsp;&ndash;&nbsp;'
            };
            range[2] = {
              'type' : 'div',
              'class' : "wpxi_server_query_range_item",
              'html' : set_dform_values( filters[i][1] )
            };
          }

          filters[i] = [
            {
            "type" : "label",
            "class": "wpxi_server_query_range_label",
            "html" : filters[i][0]['general_name'] + " (" + filters[i][0]['description'] + ")"
            },
            {
              'type':'div',
              "class": "wpxi_server_query_range_values",
              'html': range
            }
          ]

        } else {
          filters[i] = set_dform_values( filters[i] );
        }

        //** Move every field to container ( wrapper ) */
        filters[i] = {
          'type' : 'div',
          'class' : "ui-dform-elements wpxi_server_query_filter" + classification,
          'html' : filters[i]
        };
      }

      jQuery( '.wpxi-ui-server_query_form' ).empty();
      jQuery( '.wpxi-ui-server_query_form' ).dform({
        type: 'div',
        html: filters
      });

      if( typeof object.api_server.rets_query != 'undefined' ) {
        object.api_server.rets_query.status = object.api_server.connect.success == '1' ? 'good' : 'error';
        wpp.xmli.contextual( 'server_query_result', object.api_server.status, object.api_server.rets_query.message );
      }


      jQuery( '.wpxi-ui-server_query_contextual' ).show();
      jQuery( '.wpxi-ui-server_query_actions' ).show();

    } catch( error ) {
      console.error( error );
    }

  } else {

    jQuery( '.wpxi-ui-server_query_contextual' ).hide();
    jQuery( '.wpxi-ui-server_query_actions' ).hide();
    jQuery( '.wpxi-ui-server_query_form' ).text( 'Loading filters...' );

    wpp.xmli.ajax( 'rets_query', { schedule_id: object.schedule_id, update_structure: true }, function( result ) {

      if( result.success ) {
        if( typeof result.filters == 'undefined' ) {
          wpp.xmli.contextual( 'import_heading', 'error', wpp.xmli.strings.internal_server_error );
          wpp.xmli.close_section( 'server_query' );
        } else {
          //* Update filters and attribute map data */
          wpp.xmli.editor.object.api_server.filters = [];
          jQuery.each( result.filters, function( i, filter ) {
            wpp.xmli.editor.object.api_server.filters.push( filter );
          });
          wpp.xmli.editor.object.api_server.rets_query = result.rets_query;
          wpp.xmli.editor.object.server_query = result.server_query;
          /* Update Attribute Map */
          wpp.xmli.show_map();
          /* Update Server Query by rets_ready_calling */
          wpp.xmli.rets_ready();
        }
      } else {

        jQuery( '.wpxi-ui-server_query_form' ).empty();
        if( result.message ) {
          wpp.xmli.contextual( 'import_heading', 'error', result.message );
        } else {
          wpp.xmli.contextual( 'import_heading', 'error', wpp.xmli.strings.internal_server_error );
        }
        wpp.xmli.close_section( 'server_query' );

      }

    });

  }

};


/**
 * Triggered when Schedule Editor is loaded, and XML is selected as source type
 *
 * @since 3.3.0
 * @author potanin@UD
 */
wpp.xmli.xml_ready = function() {
  'use strict'; wpp.log( 'wpp.xmli.xml_ready()', arguments );

  /* Display information about cached source, if it is, in fact, cached */
  if( wpp.xmli.editor.object.files && typeof wpp.xmli.editor.object.files.source === 'object' ) {
    jQuery( '.wpxi-ui[data-wpxi-contextual-message="import_heading"]' ).show();
  }

  jQuery( '.wpxi-ui[data-wpxi-ui="filters"]' ).show();
  jQuery( '.wpxi-ui[data-wpxi-ui="toggle_filters"]' ).show();
  jQuery( '[data-wpxi_action=toggle_filters]').addClass( 'wpp_active' )
  jQuery( '.wpxi-ui[data-wpxi-ui="attribute_map"]' ).show();
  jQuery( '[data-wpxi_action=toggle_attribute_map]').addClass( 'wpp_active' )

};


/**
 * Displays overview
 *
 * @todo Must finish function to properly return result - right now it's not being returned as JSON. - potanin@UD 8/2/12
 */
wpp.xmli.show_overview = function( args ) {
  'use strict'; wpp.log( 'wpp.xmli.show_overview()', arguments );

  wpp.xmli.toggle_loader( 'start', 'wpxi-ajax-container' );

  wpp.xmli.ajax( 'show_overview', {}, function( result ) {

    wpp.xmli.current = {};
    wpp.xmli.editor = {};

    jQuery( '.wpxi-ajax-container' ).html( result.ui ).show();

    wpp.xmli.toggle_loader( 'stop', 'wpxi-ajax-container' );

  });

}


/**
 * Displays editor Called when Schedule is changed, or new one created.
 *
 */
wpp.xmli.show_editor = function( args ) {
  'use strict'; wpp.log( 'wpp.xmli.show_editor()', arguments );

  args = {
    ajax_args: {
      action: 'wpxi_handler',
      wpxi_action: 'show_editor',
      schedule_id: wpp.xmli.current.schedule_id
    }
  };

  wpp.xmli.toggle_loader( 'start', 'wpxi-ajax-container' );

  jQuery.post( ajaxurl, args.ajax_args, function( result ) {
    wpp.log( 'wpp.xmli.show_editor().success()', arguments );

    wpp.screen = 'xmli_editor';

    if( typeof result !== 'object' || !result.success ) {
      return;
    }

    jQuery( '.wpxi_core_notice' ).empty().hide();
    jQuery( '.wpxi-ajax-container' ).html( result.ui ).show();
    jQuery( '.wpp-ui-header' ).addClass( 'wpp-collapsed' );
    jQuery( '.wpxi-overview_element' ).hide();

    wpp.xmli.editor = {
      form: jQuery( 'form.wpxi_editor' ),
      preview_listings: jQuery( '.wpxi_preview_listings' ),
      preview_xml: jQuery( '.wpxi_preview_xml' ),
      object: result.object ? result.object : {},
      user_capabalities: ( typeof result.object === 'object' && typeof result.object.user_capabalities === 'object' ) ? result.object.user_capabalities : {},
      _updated: {}
    };

    wpp.xmli.current = wpp.xmli.current ? wpp.xmli.current : {};
    wpp.xmli.current.new_import = wpp.xmli.current.schedule_id ? false : true;
    wpp.xmli.current.url = jQuery( 'input[name="wpxi[url]"]' ).val();
    wpp.xmli.current.type = jQuery( 'select[name="wpxi[source_type]"]' ).val();

    /* Event Monitor: changes requiring saving */
    if( wpp.xmli.editor.user_capabalities.edit_post ) {
      wpp.xmli.editor.save_schedule = jQuery( 'button.wpxi_save_schedule', wpp.xmli.editor.form );

      jQuery( ':input, select', wpp.xmli.editor.form ).change( function() {
        wpp.xmli.editor._updated[ this.name ] = wpp.xmli.editor._updated[ this.name ] ? wpp.xmli.editor._updated[ this.name ] + 1 : 1;
        if( wpp.xmli.editor._updated[ this.name ] < 2 ) {
          jQuery( document ).trigger( 'wpp::xmli::editor_updated', { trigger: this } );
          jQuery.inArray( this.name, wpp.xmli.source_revalidation_triggers ) >= 0 ? jQuery( document ).trigger( 'wpp::xmli::revalidation_required', { trigger: this } ) : false;
        }
      });

      jQuery( '.wpxi-advanced input[type=checkbox]' ).each( function() {
        jQuery( this ).is( ':checked' ) ? jQuery( this ).closest( '.wpxi-advanced' ).addClass( 'wpxi_enabled_row' ) : jQuery( this ).closest( '.wpxi-advanced' ).removeClass( 'wpxi_enabled_row' )
      });

      jQuery( '.wpxi-advanced input[type=text]' ).each( function() {
        jQuery.trim( jQuery( this ).val() ) !== '' ? jQuery( this ).closest( '.wpxi-advanced' ).addClass( 'wpxi_enabled_row' ) : jQuery( this ).closest( '.wpxi-advanced' ).removeClass( 'wpxi_enabled_row' )
      });

    }

    jQuery(".wpxi-advanced input[type=checkbox]").live("change", function() {
      var wrapper = jQuery(this).closest(".wpxi-advanced ");
      if(jQuery(this).is(":checked")) {
        jQuery(wrapper).addClass("wpxi_enabled_row");
      } else {
        jQuery(wrapper).removeClass("wpxi_enabled_row");
      }
    });

    jQuery(".wpxi-advanced  input[type=text]").live("change", function() {
      var wrapper = jQuery(this).closest(".wpxi-advanced");
      var value = jQuery(this).val();

      if(value == "" || value == '0') {

        /* If 0 blank out this value */
        jQuery(this).val('');

        /* Check if all inputs are empty */
        if(jQuery("input:text[value != '' ]", wrapper).length == 0) {
            jQuery(wrapper).removeClass('wpxi_enabled_row');
        }
      } else {
        jQuery(wrapper).addClass('wpxi_enabled_row');
      }
    });

    /* Show UI elements that are displayed in all cases */
    jQuery( '.wpxi-ui[data-wpxi-ui="import_heading"]' ).show();
    jQuery( '.wpxi-ui[data-wpxi-ui="source_information"]' ).show();

    /* Adjust UI for New Schedules  */
    if( wpp.xmli.current.new_import ) {
      jQuery( 'input[name="wpxi[post_title]"]' ).focus();
    }

    /* Do actions on existing schedules */
    if( !wpp.xmli.current.new_import ) {
      wpp.xmli.update_unique_id_dropdown();

      if( !wpp.xmli.editor.user_capabalities.edit_post ) {
        jQuery( 'input[type=text], input[type=password], input[type=checkbox], select, span.wpp_add_row, span.wpxi_source_validation, button.wpxi_save_schedule', wpp.xmli.editor.form ).prop( 'disabled', true ).addClass( 'wpp_disabled' );
        jQuery( '.wpxi-ui[data-wpxi-ui="wpp_actions_bar"]' ).hide();
      }

    }

    if( result.object && ( result.object._edit_lock && result.object._edit_lock.message && result.object._edit_lock.user_id !== wpp.current_user.user_id ) ) {
      wpp.xmli.core_notice( result.object._edit_lock.message );
    }

    wpp.xmli.adjust_editor( { caller: 'show_editor' } );

    wpp.table.sortable();

    wpp.xmli.source_type_change();

  }, 'json' );

};


/**
 * Displays ( Updates ) Attribute Map for current ( existing! ) schedule.
 *
 */
wpp.xmli.show_map = function( args ) {
  'use strict'; wpp.log( 'wpp.xmli.show_map()', arguments );

  var map = jQuery( '.wpxi_schedule_map' );
  if( !map.length > 0 || !wpp.xmli.current.schedule_id ) {
    return;
  }

  args = {
    ajax_args: {
      action: 'wpxi_handler',
      wpxi_action: 'show_map',
      schedule_id: wpp.xmli.current.schedule_id
    }
  };

  jQuery.post( ajaxurl, args.ajax_args, function( result ) {
    wpp.log( 'wpp.xmli.show_map().success()', arguments );

    if( typeof result !== 'object' || !result.success ) {
      return;
    } else {
      map.html( result.ui );
      wpp.xmli.update_unique_id_dropdown();
    }

  }, 'json' );

};


/**
 * Determine emphasis of UI based on where user is in configuration.
 *
 * @since 3.3.0
 * @author potanin@UD
 */
wpp.xmli.adjust_editor = function( args ) {
  'use strict'; wpp.log( 'wpp.xmli.adjust_editor()', arguments );

  args = jQuery.extend( true, {}, typeof args === 'object' ? args : {} );

  if( !wpp.xmli.editor.object ) {
    wpp.log( 'wpp.xmli.adjust_editor called before editor UI is loaded.', 'error' );
    return;
  }

  wpp.xmli.toggle_loader( 'stop', 'wpxi-ajax-container' );

  /*
  jQuery( '.wpxi-ui[data-wpxi-ui="rets_login"]' ).hide();
  jQuery( '.wpxi-ui[data-wpxi-ui="rets_analysis"]' ).hide();
  jQuery( '.wpxi-ui[data-wpxi-ui="source_status_response"]' ).hide();
  jQuery( '.wpxi-ui[data-wpxi-ui="advanced_options"]' ).hide();
  jQuery( '.wpxi-ui[data-wpxi-ui="listing_preview_response"]' ).hide();
  jQuery( '[data-wpxi_action=toggle_attribute_map]').addClass( 'wpp_active' )
  jQuery( '[data-wpxi_action=toggle_filters]').addClass( 'wpp_active' )
  jQuery( '.wpxi-ui[data-wpxi-source]' ).hide();
  */

  /* Disabled during Development */
  jQuery( 'th.wpxi_attribute_format, td.wpxi_attribute_format' ).hide();

  jQuery( document ).trigger( 'wpp::xmli::' + wpp.xmli.current.type + '::ready' );

  if( typeof jQuery.prototype.stickySidebar === 'function' ) {
    /* We use timeout here to be sure that all triggers are called before we set stickySidebar */
    setTimeout( function() {
      /* If stickySidebar was already initialized, we should destroy it and init again. */
      jQuery( '.wpp_actions_bar' ).stickySidebar( 'remove' );
      jQuery( '.wpp_actions_bar' ).stickySidebar({
        padding: 40,
        timer: 400
      });
    }, 500 );
  }

};


/**
* {}
*
*/
wpp.xmli.delete_schedule = function( schedule_id, _this ) {
  'use strict'; wpp.log( 'wpp.xmli.delete_schedule()', arguments );

  if( !wpp.xmli.rockstar_status ) {
    if( !confirm( wpp.xmli.strings.verify_action ) ) {
      return;
    }
  }

  var _row = jQuery( _this ).closest( 'tr.wpxi_schedule_row' );

  jQuery( _row ).hide();

  jQuery.post( ajaxurl, {
    action: 'wpxi_handler',
    schedule_id: schedule_id,
    wpxi_action: 'delete_schedule'
  }, function( result ) {

    if( typeof result === 'object' && result.success ) {

      jQuery( _row ).remove();

      if( jQuery( 'table.wpxi_saved_schedules tr' ).length === 1 ) {
        jQuery( 'table.wpxi_saved_schedules' ).remove();
      }

    } else {
      jQuery( _row ).show();
      alert( 'Error. Could not delete.' );
    }

  }, 'json' );

};


/**
 * Save or Update Current Schedule
 *
 * This function will be called by other functions that require the schedule to be saved prior to them doing
 * whatever it is that they do. For this reason, some of the functionality is called via args.success and args.error
 * which may be overwritten when save_schedule() is called from another function.
 *
 * @since 3.3.0
 * @author potanin@UD
 */
wpp.xmli.save_schedule = function( args ) {
  'use strict'; wpp.log( 'wpp.xmli.save_schedule()', arguments );

  args = jQuery.extend( true, {
    ajax_args: {},
    success: function( message ) {
      wpp.log( 'wpp.xmli.save_schedule().success()' );
      wpp.xmli.global_message( message, 'good', 7000 );
      wpp.xmli.adjust_editor( { caller: 'save_schedule' } );

    },
    error: function( message ) {
      wpp.log( 'wpp.xmli.save_schedule().error()' );
      wpp.xmli.global_message( message ? message : 'Unknown Error - no response.' , 'bad' );
    },
    start: function() {
      'use strict'; wpp.log( 'wpp.xmli.save_schedule().start()', arguments );
      jQuery( 'input[data-wpxi_action="save_schedule"]' ).val( wpp.xmli.strings.saving );
      jQuery( '[data-schedule_meta="post_status"]' ).text( wpp.xmli.strings.saving );

      wpp.xmli.global_message( wpp.xmli.strings.saving_long );
    }
  }, typeof args === 'object' ? args : {} );

  jQuery.ajax({
    url: ajaxurl,
    dataType: 'json',
    type: 'POST',
    data: {
      action: 'wpxi_handler',
      wpxi_action: 'save_schedule',
      schedule_id: wpp.xmli.current.schedule_id,
      data: jQuery( 'form.wpxi_editor' ).serialize(),
      ajax_args: args.ajax_args
    },
    beforeSend: function() {
      typeof args.start === 'function' ? args.start() : false;

    },
    error: function( result ) {
      wpp.log( 'wpp.xmli.save_schedule().error()' );

      if( result.status === 500 ) {
        typeof args.error === 'function' ? args.error( wpp.xmli.strings.internal_server_error ) : false;
      } else {
        typeof args.error === 'function' ? args.error( result.message ) : false;
      }

    },
    success: function( result ) {
      'use strict'; wpp.log( 'wpp.xmli.save_schedule().success()', arguments );

      if( !result || typeof result !== 'object' ) {
        typeof args.error === 'function' ? args.error( result.message ) : false;
      }

      if( result && typeof result === 'object' && !result.success ) {
        typeof args.error === 'function' ? args.error( result.message ) : false;
      }

      if( result && typeof result === 'object' && result.success ) {

        /* Blank out updated monitor */
        wpp.xmli.editor._updated = {};

        /* Update full object */
        wpp.xmli.editor.object = result.object ? result.object : {};

        wpp.xmli.current.post_parent = result.post_parent ? result.post_parent : false;
        wpp.xmli.current.post_status = result.post_status;

        if( result.post_status === 'publish' ) {
          wpp.xmli.set_id( result.schedule_id );
          delete wpp.xmli.current.draft_id;
        }

        if( result.post_status === 'auto-draft' ) {
          wpp.xmli.set_id( result.schedule_id );
        }

        if( result.post_status === 'draft' ) {
          wpp.xmli.current.draft_id = result.schedule_id;
        }

        wpp.xmli.editor.last_saved = new Date().getTime();

        typeof args.success === 'function' ? args.success( result.message ) : false;

      }

    },
    complete: function() {
      //wpp.log( 'wpp.xmli.save_schedule().wpp()' );

      jQuery( 'input[data-wpxi_action="save_schedule"]' ).val( wpp.xmli.strings.save_configuration );
      jQuery( '[data-schedule_meta="post_status"]' ).text( wpp.xmli.status_labels[ wpp.xmli.current.post_status ] );

      wpp.xmli.toggle_loader( 'stop' );

    }
  });

};


/**
 * Ensure all necessary data for given source is filled in
 *
 * @since 3.3.0
 * @author potanin@UD
 */
wpp.xmli.validate_source = function( source_type ) {
  'use strict'; wpp.log( 'wpp.xmli.validate_source()', arguments );

  var source_specific = jQuery( 'input.wpp_required', '[data-wpxi-source=' + wpp.xmli.current.type + ']' );
  var success = true;

  if( !source_specific || source_specific.length < 1 ) {
    return true;
  }

  jQuery( source_specific ).each( function() {
    var value = jQuery.trim( jQuery( this ).val() );

    if( value === '' ) {
      jQuery( this ).addClass( 'wpp_error' );
      success = false;
    } else {
      jQuery( this ).val( value );
      jQuery( this ).removeClass( 'wpp_error' );
    }

  });

  return success;
};


/**
 * Verifies source can be loaded and is valid, and when available, received analysis data from UD API.
 *
 * @todo Update to use jQuery.ajax with improved error handling method. - potanin@UD 7/9/12
 */
wpp.xmli.source_validation = function( object ) {
  'use strict'; wpp.log( 'wpp.xmli.source_validation()' );

  wpp.xmli.current.url = jQuery( 'input[name="wpxi[url]"]' ).val();
  wpp.xmli.current.root_element = jQuery( 'input[name="wpxi[root_element"]' ).val();
  wpp.xmli.current.type = jQuery( 'select[name="wpxi[source_type]"]' ).val();

  if( !wpp.xmli.check_field( jQuery( 'input[name="wpxi[url]"]' ) ) ) {
    return;
  }

  if( !wpp.xmli.current.schedule_id ) {
    return wpp.xmli.save_schedule({
      success: function( message ) {
        wpp.xmli.source_validation();
      },
      error: function( message ) {
        wpp.xmli.contextual( 'import_heading', 'bad', message );
      },
      start: function() {
        wpp.xmli.global_message( wpp.xmli.strings.saving_xmli_before_previewing );
      },
      ajax_args: {
        ajax_args: { post_status: wpp.xmli.current.post_status = 'auto-draft' },
        event_type: 'auto-save'
      }
    });
  }

  wpp.xmli.toggle_loader( 'start' );

  wpp.xmli.contextual( 'import_heading', 'processing' );

  jQuery.post( ajaxurl + '?action=wpxi_handler&wpxi_action=source_validation', {
    data: jQuery( 'form.wpxi_editor' ).serialize(),
    source_type: wpp.xmli.current.type,
    schedule_id: wpp.xmli.current.schedule_id
  }, function( result, textStatus, jqXHR ) {},
  'json' ).success( function( result, textStatus, jqXHR ) {
    wpp.log( 'wpp.xmli.source_validation().post().success()' );

    wpp.xmli.toggle_loader( 'stop' );

    wpp.xmli.contextual( 'import_heading', result.success ? result.success : 'error' , result.message ? result.message : wpp.xmli.strings.unknown_error );

    if( result && typeof result === 'object' && result.success ) {

      /* Update full object */
      wpp.xmli.editor.object = result.object ? result.object : wpp.xmli.editor.object;

      /* Check if Source Analyses returned usable data */
      if( result.source_validation.source_analysis ) {

        var source_analysis = result.source_validation.source_analysis;

        if( source_analysis.listing_element_path && jQuery( '.wpxi_root_element' ).val() === '' ) {
          jQuery( '.wpxi_root_element' ).val( wpp.xmli.current.root_element = result.source_validation.source_analysis.listing_element_path );
        }

        if( source_analysis.attribute_data ) {

          /**
           * Add Attribute Rows
           *
           * @todo Add handler for situations when the given attribute is already selected.
           */
          jQuery.each( source_analysis.attribute_data, function( standard_key, data ) {
            wpp.xmli.add_attribute({
              attribute: standard_key,
              xpath: data.xpath,
              label: data.label
            });

          });

          wpp.xmli.update_unique_id_dropdown();

        }

      }

      wpp.xmli.adjust_editor( { caller: 'source_validation' } );

    }

  }).error( function( result, textStatus, jqXHR ) {
    wpp.log( 'wpp.xmli.source_validation().post().error()' );

    wpp.xmli.toggle_loader( 'stop' );

    switch ( result.status ) {

      case 500:
        wpp.xmli.contextual( 'import_heading', 'server_error', wpp.xmli.strings.internal_server_error );
      break;

      /**
       * Proper result not returned, and not a specific error
       */
      default:
        wpp.xmli.contextual( 'import_heading', 'bad', result.responseText );
      break;

    }

  });

};


/**
 * Sets the status of the source URL in UI. When called by wpp.xmli.ajax() ui_context is same as AJAX action.
 *
 */
wpp.xmli.contextual = function( ui_context, status, message ) {
  'use strict'; wpp.log( 'wpp.xmli.contextual()', arguments );

  status = status && typeof status === 'boolean' ? 'good' : status;
  //message = typeof message === 'object' ? message.join( '<br />' ) : message;

  wpp.xmli.current = wpp.xmli.current ? wpp.xmli.current : {};
  wpp.xmli.current.last_status = status;

  var _button = {
    wrapper: jQuery( '[data-wpxi_action="'+ ui_context + '"]' ),
    action: jQuery( '[data-wpxi_action="'+ ui_context + '"] > span.wpp_label' )
  };

  var _contextual = jQuery( '.wpxi-ui[data-wpxi-contextual-message="'+ ui_context + '"]' );

  if( !_contextual.length ) {
    _contextual = jQuery( '.wpp_actions_bar .wpxi_sidebar_response' ).show();
  }

  /** Save Ready State label */
  if( !_button.action.attr( 'data-original-text' ) ) {
    _button.action.attr( 'data-original-text', jQuery(_button.action.get(0)).text() );
  }

  _button.wrapper.removeClass( 'wpp_processing' ).removeClass( 'wpp_error' ).removeClass( 'wpp_success' );
  _contextual.removeClass( 'wpp_processing' ).removeClass( 'wpp_error' ).removeClass( 'wpp_success' );

  switch ( status )  {

    case 'processing':
      _button.wrapper.addClass( 'wpp_processing' );
      _button.action.text( message ? message : _button.action.attr( 'data-loading-text' ) );
      _contextual.show().addClass( 'wpp_processing' ).html( message ? message : wpp.xmli.strings.loading );
    break;

    case 'good':
      _button.wrapper.addClass( 'wpp_success' );
      _button.action.text( _button.action.attr( 'data-original-text' ) );
      _contextual.show().addClass( 'wpp_success' ).html( message ? message : wpp.xmli.strings.source_validated );
    break;

    case 'error':
    case 'bad':
      _button.wrapper.addClass( 'wpp_error' );
      _button.action.text( _button.action.attr( 'data-original-text' ) );
      _contextual.show().addClass( 'wpp_error' ).html( message ? message : wpp.xmli.strings.can_not_load_source );
    break;

    case 'ajax_error':
    case 'server_error':
      _button.wrapper.addClass( 'wpp_error' );
      _contextual.show().html( message );
    break;

    default:
      _button.action.text( message ? message : _button.action.attr( 'data-original-text' ) );
      _contextual.hide();
    break;

  }

};


/**
 * {}
 *
 * Example: wpp.xmli.close_section('filters');
 */
wpp.xmli.close_section = function( name ) {
  jQuery( '.wpxi-ui[data-wpxi-ui="' + name + '"]' ).hide();
  jQuery( '.wpxi-ui[data-wpxi-ui="toggle_' + name + '"]' ).show();
  jQuery( '.wpxi-ui[data-wpxi-ui="toggle_' + name + '"] > .wpp_link' ).show().removeClass( 'wpp_active' );
};


/**
 * {}
 *
 * Example: wpp.xmli.open_section('filters');
 */
wpp.xmli.open_section = function( name ) {
  jQuery( '.wpxi-ui[data-wpxi-ui="' + name + '"]' ).show();
  jQuery( '.wpxi-ui[data-wpxi-ui="toggle_' + name + '"]' ).show();
  jQuery( '.wpxi-ui[data-wpxi-ui="toggle_' + name + '"] > .wpp_link' ).show().addClass( 'wpp_active' );
};


/**
 * {}
 *
 * Example: wpp.xmli.disable_section('filters');
 */
wpp.xmli.disable_section = function( name ) {
  jQuery( '.wpxi-ui[data-wpxi-ui="' + name + '"]' ).hide();
  jQuery( '.wpxi-ui[data-wpxi-ui="toggle_' + name + '"]' ).hide();
  jQuery( '.wpxi-ui[data-wpxi-ui="toggle_' + name + '"] > .wpp_link' ).show().removeClass( 'wpp_active' );
};


/**
 * {}
 *
 * Example: wpp.xmli.enable_section('filters');
 */
wpp.xmli.enable_section = function( name ) {
  jQuery( '.wpxi-ui[data-wpxi-ui="' + name + '"]' ).show();
  jQuery( '.wpxi-ui[data-wpxi-ui="toggle_' + name + '"]' ).show();
  jQuery( '.wpxi-ui[data-wpxi-ui="toggle_' + name + '"] > .wpp_link' ).show().addClass( 'wpp_active' );
};


/**
 * Disables, resets, and grays out an option
 *
 */
wpp.xmli.disable_advanced_option = function( element ) {
  jQuery( element ).prop( 'disabled', true );
  jQuery( element ).prop( 'checked', false );
  jQuery( element ).closest( '.wpxi-advanced' ).css( 'opacity', 0.3 ).removeClass( '.wpxi_enabled_row' );
};


/**
 * Enables an option
 *
 */
wpp.xmli.enable_advanced_option = function( element ) {
  jQuery( element ).prop( 'disabled', false );
  jQuery( element ).closest( '.wpxi-advanced' ).css( 'opacity',  1 );
};


/**
 * Toggle loading icon at the top of the page.
 *
 */
wpp.xmli.toggle_loader = function ( action, scope ) {
  wpp.log( 'wpp.xmli.toggle_loader()', arguments );

  switch( action ) {

    case 'start':
      jQuery( '.wpxi_header_actions .wpxi_loader' ).fadeIn( 1000 );

      if( scope === 'wpxi-ajax-container' ) {
        jQuery( '.wpxi-ajax-container' ).css( 'opacity', 0.3 );
      }

    break;

    case 'stop':
      jQuery( '.wpxi_header_actions .wpxi_loader' ).fadeOut( 500 );

      if( scope === 'wpxi-ajax-container' ) {
       jQuery( '.wpxi-ajax-container' ).fadeTo( 300, 1 );
      }

    break;

    default:
      jQuery( '.wpxi_header_actions .wpxi_loader' ).toggle();
    break;

  }

};


/**
 * Rebuild unique ID dropdown
 *
 */
wpp.xmli.update_unique_id_dropdown = function() {
  //wpp.log( 'wpp.xmli.update_unique_id_dropdown()' );

  var uid_container = jQuery( ".wpxi_unique_id_wrapper" );
  var uid_select_element = jQuery( ".wpxi_unique_id" );
  var selected_id = uid_select_element.val();
  var selected_attributes = jQuery( "select.wpxi_attribute_dropdown option:selected[value!='']" ).length;

  //** If _wpp::source_unique_id is configured, we hide the dropdown selection */
  if( jQuery( 'select.wpxi_attribute_dropdown[option:selected][value="_wpp::source_unique_id"]' ).length ) {
    jQuery( uid_container ).append( '<input type="hidden" name="wpxi[unique_id]" value="_wpp::source_unique_id" />' );
    uid_container.hide();
    uid_select_element.prop( 'disabled', true );
    return;
  }

  uid_select_element.prop( 'disabled', false );

  uid_select_element.html( '' );

  uid_select_element.append( '<option value=""> - </option>' );

  jQuery( 'select.wpxi_attribute_dropdown option:selected' ).each( function() {

    var attribute_slug = jQuery( this ).val();

    var cur = jQuery( 'select.wpxi_unique_id option[value="' + attribute_slug + '"]' );

    /* Make sure that the attribute isn't already added to the UID dropdown and a value exists  */
    if( cur.length === 0 && cur.val() !== "" ) {
      var title = jQuery( this ).html();
      uid_select_element.append( '<option value="'+attribute_slug+'">' + title + '</option>' );
    }

    if( selected_id !== '' && selected_id !== null ) {
      uid_select_element.val( selected_id );
    }

  });

  //* No attribute found, nothing to display in UID dropdown */
  if( selected_attributes === 0 ) {
    jQuery( '.wpxi_unique_id_wrapper' ).hide();
  } else {
    jQuery( '.wpxi_unique_id_wrapper' ).show();

    if( selected_id === '' ) {
      jQuery( "span.description", uid_container ).html( wpp.xmli.strings.select_unique_id_attribute );
    } else {
      jQuery( "span.description", uid_container ).html( wpp.xmli.strings.unique_id_attribute );
    }
  }
};


/**
 * Simplifies validation of user inupt on a particular form field.
 *
 * @todo Move to WPP Global
 */
wpp.xmli.check_field = function( element ) {
  'use strict'; wpp.log( 'wpp.xmli.check_field( ' + element.name + ' )' );

  var _valid = true;
  var _validation_type = jQuery( element ).attr( 'data-validate' ) ? jQuery( element ).attr( 'data-validate' ) : 'value';

  switch( _validation_type ) {

    case 'value':
      _valid = jQuery( element ).val() !== '' ? true : false;
    break;

    case 'regex':
      //@todo
    break;

    case 'number':
      //@todo
    break;

  }

  if( !_valid ) {
    jQuery( element ).addClass( 'wpp_error' );
  }

  if( _valid ) {
    jQuery( element ).removeClass( 'wpp_error' );
  }

  return _valid;

};


/**
 * Ran when source valuation is requested via "Check Source" button.
 *
 */
wpp.xmli.source_type_change = function( element ) {
  'use strict'; wpp.xmli.current.type = jQuery( 'select[name="wpxi[source_type]"]' ).val();

  /* Show specific source data ( DOM elements ) for the current source type */
  jQuery( '[data-wpxi-source]' ).hide();
  jQuery( '[data-wpxi-source]' ).each( function( i, e ) {
    var s = jQuery( e ).data('wpxi-source').split( ',' );
    jQuery.each( s, function ( si, se ) {
      if( se == wpp.xmli.current.type ) {
        jQuery( e ).show();
      }
    } );
  } );

  switch( wpp.xmli.current.type ) {

    /**
     * RETS selected, but not connected.
     * Once API connection established, wpp.xmli.rets_ready() takes over.
     *
     */
    case 'rets':
      wpp.xmli.disable_section( 'filters' );
      wpp.xmli.enable_section( 'server_query' );

      wpp.xmli.open_section( 'rets_login' );
      wpp.xmli.close_section( 'attribute_map' );
    break;

    /**
     * XML, CSV or other non-API handled source type selected.
     *
     */
    default:
      wpp.xmli.enable_section( 'filters' );
      wpp.xmli.disable_section( 'server_query' );

      wpp.xmli.close_section( 'rets_login' );
      wpp.xmli.open_section( 'filters' );
      wpp.xmli.open_section( 'attribute_map' );
    break;

  }

};


/**
 * Adds an Attribute Row programatically.
 *
 */
wpp.xmli.add_attribute = function( args ) {
  'use strict'; wpp.log( 'wpp.xmli.add_attribute()', arguments );

  args = jQuery.extend( true, {
    attribute: '',
    xpath: '',
    type: ''
  }, args  );

  args.added_row = wpp.add_row( jQuery( 'span.wpxi_add_attribute' ) );

  if( !jQuery( 'select.wpxi_attribute_dropdown > option[value="' + args.attribute + '"]', args.added_row ).length && args.label ) {
    jQuery( 'select.wpxi_attribute_dropdown', args.added_row ).append( '<option value="' + args.attribute + '">' + args.label + '</option>' );
  }

  jQuery( 'select.wpxi_attribute_dropdown', args.added_row ).val( args.attribute );
  jQuery( 'input.wpxi_xpath_rule', args.added_row ).val( args.xpath );
  jQuery( 'select.wpxi_attribute_format', args.added_row ).val( args.type );

}


/**
 * Called by wpp.add_row() when Add Attribute button is pressed.
 *
 */
wpp.xmli.attribute_added = function() {
  'use strict'; wpp.log( 'wpp.xmli.attribute_added()', arguments );
}


/**
 * Applies the Primary Element XPath query to XML data, and displays it above the attribute map to assist with attribute mapping.
 *
 */
wpp.xmli.preview_raw_xml = function() {
  'use strict'; wpp.log( 'wpp.xmli.preview_raw_xml()', arguments );

  /* If Schedule ID is not set, call Save Schedule with current function as callback */
  if( wpp.xmli.editor.object.source_type == 'xml' && jQuery( '.wpxi_root_element' ).val() !== wpp.xmli.editor.object.root_element ) {
    return wpp.xmli.save_schedule({
      success: wpp.xmli.preview_raw_xml,
      start: function() { wpp.xmli.global_message( wpp.xmli.strings.saving_xmli_before_previewing ); }
    });
  }

  if( !wpp.xmli.validate_source() ) {
    return false;
  }

  jQuery( 'select[name="wpxi[source_type]"]' ).removeClass( 'wpp_error' );
  jQuery( wpp.xmli.editor.preview_xml ).addClass( 'wpp_processing' );

  wpp.xmli.contextual( 'preview_raw_xml', 'processing', wpp.xmli.strings.loading );

  wpp.xmli.toggle_loader( 'start' );

  wpp.xmli.ajax( 'preview_raw_xml', { schedule_id: wpp.xmli.current.schedule_id, data:jQuery( 'form.wpxi_editor' ).serialize() }, function( result ) {
    'use strict'; wpp.log( 'wpp.xmli.preview_raw_xml.response()' );

    jQuery( wpp.xmli.editor.preview_xml ).removeClass( 'wpp_processing' );

    wpp.xmli.contextual( 'preview_raw_xml', result.success ? 'good' : 'bad', result.message ? result.message : wpp.xmli.strings.unknown_error );

    if( typeof jQuery.prototype.snippet !== 'function' ) {
      return wpp.log( 'jQuery.snippet() script not found.', 'error' );
    }

    jQuery( '.wpxi-ui[data-wpxi-ui=xml_analysis] .wpxi_xml_analysis' ).empty();

    if( !result.success || result.preview_raw_xml.listing_elements ) {
      //jQuery( '.wpxi-ui[data-wpxi-ui="xml_analysis"]' ).slideUp();
    }

    if( result.success && result.preview_raw_xml.listing_elements ) {
      jQuery( '.wpxi-ui[data-wpxi-ui="xml_analysis"]' ).show();
      jQuery( '.wpxi-ui[data-wpxi-ui="xml_analysis"] .wpp_ajax_response' ).slideDown();

      var _listings = [];
      var _toggler = jQuery( '<div class="wpxi_xml_analysis_tools"></div>' );
      var _container = jQuery( '<div class="wpxi_pre_container wpxi_collapsed"></div>' );
      var _actions = {
        toggle: jQuery( '<span class="wpp_button wpp_left"><span class="wpp_icon wpp_icon_67"></span><span class="wpp_label">' + wpp.xmli.strings.toggle + '</span></span>' ),
        collapse: jQuery( '<span class="wpp_button wpp_middle"><span class="wpp_icon wpp_icon_120"></span><span class="wpp_label" data-alternative-text="' + wpp.xmli.strings.collapse + '">' + wpp.xmli.strings.expand + '</span></span>' ),
        /* colors: jQuery( '<span class="wpp_button wpp_right"><span class="wpp_icon wpp_icon_120"></span><span class="wpp_label">' + wpp.xmli.strings.colors + '</span></span>' ), */
        next: jQuery( '<span class="wpp_button alignright wpp_right" alt="' + wpp.xmli.strings.next + '"><span class="wpp_icon wpp_icon_136"></span></span>' ),
        previous: jQuery( '<span class="wpp_button alignright wpp_left" alt="' + wpp.xmli.strings.previous + '"><span class="wpp_icon wpp_icon_152"></span></span>' )
      }

      jQuery.each( _actions, function( i, action ) {
        jQuery( _toggler ).append( action );
      });

      var wpxi_xml_analysis = jQuery( '.wpxi-ui[data-wpxi-ui=xml_analysis] .wpxi_xml_analysis' );
      wpxi_xml_analysis.css( 'width', ( wpxi_xml_analysis.width() + 'px' ) );

      wpxi_xml_analysis.append( _toggler );
      wpxi_xml_analysis.append( _container );

      jQuery.each( result.preview_raw_xml.listing_elements, function( count, element ) {
        jQuery( _container ).append( _listings[ count ] = jQuery( '<pre></pre>' ).html( element ) );
        _listings[ count ].snippet( 'xml', { style: 'xmli', showNum: false, menu: false, transparent: true });

        if( count === 0 ) {
          _listings[ count ].closest( '.snippet-container' ).attr( 'data-wpxi-listing-element', count );
        } else {
          _listings[ count ].closest( '.snippet-container' ).hide().attr( 'data-wpxi-listing-element', count );
        }

      });

      /* _actions.colors.click( function() { jQuery( '.sh_xml' ).snippet( 'xml', { style: 'random', showNum: false, menu: false }); }); */

      _actions.toggle.click( function() { _container.toggle() });
      _actions.collapse.click( function() { _container.toggleClass( 'wpxi_collapsed' ); });
      _actions.next.click( function() {
        var _next = ( parseInt( jQuery( '[data-wpxi-listing-element]:visible' ).attr( 'data-wpxi-listing-element' ) ) + 1 );
        if( jQuery( '[data-wpxi-listing-element=' + _next + ']' ).length ) {
          jQuery( '[data-wpxi-listing-element]' ).hide();
          jQuery( '[data-wpxi-listing-element=' + _next + ']' ).show();
        };
      });

      _actions.previous.click( function() {
        var _previous = ( parseInt( jQuery( '[data-wpxi-listing-element]:visible' ).attr( 'data-wpxi-listing-element' ) ) - 1 );
        if( jQuery( '[data-wpxi-listing-element=' + _previous + ']' ).length ) {
          jQuery( '[data-wpxi-listing-element]' ).hide();
          jQuery( '[data-wpxi-listing-element=' + _previous + ']' ).show();
        };
      });

    }

  });

};


/**
 * {}
 *
 */
wpp.xmli.import_job = function( schedule_id, args ) {
  'use strict'; wpp.log( 'wpp.xmli.import_job()', arguments );

  if( !schedule_id ) {
    wpp.log( 'Schedule ID not passed.', arguments );
  }

  var _row = jQuery( '.wpxi_schedule_row[data-schedule-id=' + schedule_id + ']' );

  args = _row.args = jQuery.extend( true, {
    post_status: _row.attr( 'data-post-status' ),
    status_label: jQuery( '.wpxi_post_status', _row ),
    progress: jQuery( '.wpxi_progress_bar', _row ).show().empty(),
    //last_update: jQuery( '.wpxi_last_update', _row ).show().empty(),
    interval: 5000,
    action: 'start_job',
    actions: {
      edit_schedule: jQuery( '[data-wpxi_action=edit_schedule]', _row ),
      start_job: jQuery( '[data-wpxi_action=import_job]', _row ),
      cancel_job: jQuery( '[data-wpxi_action=cancel_job]', _row ),
      remove_content: jQuery( '[data-wpxi_action=remove_content]', _row ),
      delete_schedule: jQuery( '[data-wpxi_action=delete_schedule]', _row )
    },
    info: {
      total_listings: jQuery( 'li.wpxi_total_listings', _row ),
      wpxi_schedule: jQuery( 'li.wpxi_schedule', _row ),
      wpxi_source: jQuery( 'li.wpxi_source', _row ),
      updates: jQuery( 'ul.wpxi_schedule_updates', _row )
    }
  }, typeof args === 'object' ? args : {} );

  args.info.updates.last_id = parseInt( args.info.updates.attr( 'data-wpxi_last_id' ) );

  /**
   * Get status. Determine if need to continue / engage more resources.
   *
   */
  var display_status = wpp.xmli.import_job.display_status = function( schedule_id, args, result ) {
    'use strict'; wpp.log( 'xmli.import_job.display_status()', arguments );

    /* If triggered by a timeout request, schedule next status update */
    if( result.status === 408 ) {
      return get_status( schedule_id, args );
    }

    var progress_done = function() {
      jQuery( args.progress ).fadeOut( 5000 ).empty();
      args.status_label.text( '' );
      edit_schedule_toggle( 'show' );
      args.actions.start_job.show();
      args.actions.delete_schedule.show();
      args.actions.cancel_job.hide();
      /* Show 'Remove Content' action only if schedule has imported objects */
      if( typeof result.total_objects != 'undefined' && parseInt( result.total_objects ) > 0 ) {
        args.actions.remove_content.show();
      } else {
        args.info.total_listings.empty();
      }
    }

    /* Update 'Latest Updates' data. */
    if( typeof result.logs != 'undefined' && result.logs.data.length != 0 ) {
      /* Set the latest log ID */
      args.info.updates.last_id = result.logs.last_id;
      args.info.updates.attr( 'data-wpxi_last_id', args.info.updates.last_id );

      jQuery.each( result.logs.data, function( i, e ) {
        var row = jQuery( '<li><span class="wpxi_log_message">' + e.message + '</span> <span class="wpxi_log_time">' + e.time + '</span></td> ')
        args.info.updates.prepend( row );
      } );
    }

    if( result.message ) {
      //args.last_update.text( result.message );
    }

    _row.attr( 'data-post-status' , result.post_status );

    if( typeof result.progress === 'undefined' && result.post_status === 'publish' ) {
      progress_done();
    }

    result.info = ( typeof result.info != 'undefined' ) ? result.info : {};
    if( typeof result.info.total_listings != 'undefined' ) {
      args.info.total_listings.text( result.info.total_listings );
    }

    args.status_label.text( wpp.xmli.status_labels[ result.post_status ] );

    if( typeof result.progress === 'number' ) {

      if( !args.progress_bar  ) {
        jQuery( args.progress ).append( args.progress_bar = wpp.progress_bar({
          type: 'success',
          frequency : false
        }));
      }

      if( result.message ) {
        //args.last_update.text( result.message );
      }

      if( result.progress < 1 ) {
        wpp.log( 'xmli.import_job.display_status() - In Progress', arguments );
        result.progress = parseFloat( result.progress ) * 100;
        if( args.progress_bar.current < result.progress ) {
          if( !args.progress_bar.current ) {
            args.progress_bar.set( 1 );
            setTimeout( function(){ args.progress_bar.set( result.progress ) }, result.interval ? result.interval : args.interval );
          } else {
            args.progress_bar.set( result.progress );
          }
        }
        args.update_timer = setTimeout( get_status, result.interval ? result.interval : args.interval, schedule_id, args );
      } else if( result.progress === 1 ) {
        wpp.log( 'xmli.import_job.display_status() - Complete', arguments );
        args.progress_bar.set( 100 );
        setTimeout( function() {
          progress_done();
        }, 1500 );
      }

    }

  }


  /**
   * Get status. Determine if need to continue / engage more resources.
   *
   */
  var cancel_job = wpp.xmli.import_job.cancel_job = function( schedule_id, args ) {
    'use strict'; wpp.log( 'xmli.import_job.cancel_job()', arguments );
    if( confirm( wpp.xmli.strings.verify_action ) ) {
      clearInterval( args.update_timer );
      wpp.xmli.ajax( 'import_job_cancel', { schedule_id: schedule_id }, function( result ) {
        get_status( schedule_id, args );
      });
    }
  };


  /**
   * Get status. Determine if need to continue / engage more resources.
   *
   */
  var get_status = wpp.xmli.import_job.get_status = function( schedule_id, args ) {
    'use strict'; wpp.log( 'xmli.import_job.get_status()', arguments );
    wpp.xmli.ajax( 'get_status', { schedule_id: schedule_id, updates_id: args.info.updates.last_id }, function( result ) { display_status( schedule_id, args, result ); });
  };


  /**
   * Schedule next update check
   *
   */
  var start_import = wpp.xmli.import_job.start_import = function( schedule_id, args ) {
    'use strict'; wpp.log( 'xmli.import_job.start_import()', arguments );

    wpp.xmli.ajax( 'import_job_start', { schedule_id: schedule_id }, function( result ) {
      setTimeout( function() { get_status( schedule_id, args ) }, 1000 );
    });

  };

  var remove_content = wpp.xmli.import_job.remove_content = function( schedule_id, args ) {
    'use strict'; wpp.log( 'xmli.import_job.remove_content()', arguments );

    wpp.xmli.ajax( 'delete_all_schedule_properties', { schedule_id: schedule_id }, function( result ) {
      setTimeout( function() { get_status( schedule_id, args ) }, 1000 );
    });

  }

  /**
   * Toggles Edit Schedule links
   */
  var edit_schedule_toggle = wpp.xmli.import_job.edit_schedule_toggle = function() {
    if( typeof arguments[0] == 'undefined' ) return;
    var a = args.actions.edit_schedule,
        e = arguments[0];
    a.each( function( i, el ) {
      if( jQuery( el ).hasClass( 'wpxi_not_link' ) ) {
        if( e == 'hide' ) jQuery( el ).show();
        else jQuery( el ).hide();
      } else {
        if( e == 'hide' ) jQuery( el ).hide();
        else jQuery( el ).show();
      }
    } );
  }

  jQuery( args.actions.cancel_job ).click( function() {
    wpp.xmli.import_job.cancel_job( schedule_id, args );
  });

  args.actions.start_job.hide();
  args.actions.remove_content.hide();
  args.actions.delete_schedule.hide();

  if( args.post_status === 'publish' ) {

    if( args.action === 'start_job' ) {

      edit_schedule_toggle( 'hide' );
      args.actions.cancel_job.show();
      start_import( schedule_id, args );

    } else if ( args.action === 'remove_content' ) {

      edit_schedule_toggle( 'show' );
      args.actions.cancel_job.hide();
      remove_content( schedule_id, args );

    }

  } else if( args.post_status === 'importing' ) {

    edit_schedule_toggle( 'hide' );
    args.actions.cancel_job.show();
    get_status( schedule_id, args );

  } else if( args.post_status === 'clearing' ) {

    edit_schedule_toggle( 'show' );
    args.actions.cancel_job.hide();
    get_status( schedule_id, args );

  }

};


/**
 * {}
 *
 * @todo Listing links will be rendered better, this is temporary.
 */
wpp.xmli.preview_listings = function() {
  'use strict'; wpp.log( 'wpp.xmli.preview_listings()', arguments );

  //** Save schedule first, then do a callback to the Preview Listings Make Request function */
  wpp.xmli.save_schedule({
    start: function() {
      jQuery( '[data-wpxi-ui=listing_preview_response]' ).fadeIn();
      jQuery( '[data-wpxi-ui=listing_preview_response] .wpp_ajax_response' ).show().addClass( 'wpxi_loader' );
      jQuery( '[data-wpxi-ui=listing_preview_response] .wpp_ajax_response > *' ).css( 'opacity', 0 );
    },
    success: function() {

      var schedule_id = wpp.xmli.current.draft_id ? wpp.xmli.current.draft_id : wpp.xmli.current.schedule_id;

      wpp.xmli.ajax( 'preview_listings', { schedule_id: schedule_id }, function( result ) {
        'use strict'; wpp.log( 'wpp.xmli.preview_listings.ajax.success()', arguments );

        jQuery( '[data-wpxi-ui=listing_preview_response] .wpp_ajax_response' ).empty().removeClass( 'wpxi_loader' );
        jQuery( '[data-wpxi-ui=listing_preview_response] .wpp_ajax_response' ).append( '<p class="wpxi_ajax_message">' + result.message + '</p>' );

        if( result.success && result.inserted ) {

          jQuery.each( result.inserted, function( post_id, data ) {

            var _link = jQuery( '<a class="wpxi_listing_preview" target="_blank" href="' + data.permalink + '"><span class="post_thumbnail"></span><span class="post_title">' + data.post_title + '</a></a>' );

            if( data.featured_image_url ) {
              jQuery( 'span.post_thumbnail', _link ).append( '<img class="post_thumbnail" src="' + data.featured_image_url + '" />' );
            }

            jQuery( '[data-wpxi-ui=listing_preview_response] .wpp_ajax_response' ).append( _link );

          });

        }

        if( !result.success && wpp.developer_mode ) {
          //jQuery( '[data-wpxi-ui=listing_preview_response] .wpp_ajax_response .wpxi_ajax_message' ).append( '<pre class="wpxi_ajax_debug wpp_class_pre">' + ( result.ui ? result.ui : '' ) + '</pre>' );
        }

      });

    },
    ajax_args: { post_status: 'draft' },
    event_type: 'wpp.xmli.preview_listings'
  });

};


/**
 * Sets ID for schedule in editor.
 *
 */
wpp.xmli.set_id = function( schedule_id ) {
  'use strict'; wpp.log( 'wpp.xmli.set_id()', arguments );

  if( wpp.xmli.current.post_status === 'draft' ) {
    return false;
  }

  if( typeof schedule_id !== 'number' ) {
    schedule_id = parseInt( schedule_id );
  }

  if( !isNaN( schedule_id ) && typeof schedule_id === 'number' ) {
    window.location.hash = wpp.xmli.current.schedule_id = schedule_id;
    return true;
  }

  return wpp.xmli.current.schedule_id = false;

};


/**
 * Sets the status of the source URL in UI
 *
 */
wpp.xmli.core_notice = function( message ) {
  'use strict'; wpp.log( 'wpp.xmli.core_notice()', arguments );

  if( !jQuery( '.wpxi_core_notice' ).length ) {
    jQuery( '<div class="wpxi_core_notice"></div>' ).insertAfter( jQuery( '.wpp-ui-header' ) );
  }

  jQuery( '.wpxi_core_notice' ).show().empty().html( message );

};


/**
 * Display a message in the "Action Bar", below the Attribute Map
 *
 */
wpp.xmli.global_message = function( message, type, delay ) {
  'use strict'; wpp.log( 'wpp.xmli.global_message()', arguments );

  var error_class = false;
  var add_class;
  var element = jQuery( '.wpp_actions_bar .wpxi_sidebar_response' ).show();

  /* Remove all classes */
  element.removeClass( 'wpxi_error_text' );

  if( type !== undefined && type !== "" ) {

    if( type === 'bad' ) {
      add_class = 'wpxi_error_text'
    } else if ( type === 'good' ) {
      add_class = ''
    } else {
      add_class = type;
    }
  }

  //* If no message passed, just hide the element and bail */
  if( message === '' || message === undefined ) {

    if( delay !== undefined ) {
      element.delay( delay ).fadeOut( "slow" );
    } else {
      element.hide();
    }

    return;
  }

  element.show();

  element.html( message );

  if( delay !== undefined ) {
    element.delay( delay ).fadeOut( 'slow' );
  }

};


/**
 * Render chart showing import statistics. (In Development)
 *
 */
wpp.xmli.render_visualization = function() {
  'use strict';

  if( typeof google !== 'object' ) {
    return;
  }

  wpp.log( 'wpp.xmli.render_visualization()', arguments );

  if( !jQuery( '#wpxi_feed_performance_chart:visible' ).length ) {
    return;
  }

  jQuery( '.wpp-ui-header' ).addClass( 'wpp-collapsed' );

  wpp.xmli.visualization = {};

  //wpp.xmli.visualization_data

  wpp.xmli.visualization.data = new google.visualization.DataTable();
  wpp.xmli.visualization.data.addColumn( 'date', 'Date' );
  wpp.xmli.visualization.data.addColumn( 'number', 'Sold Pencils' );
  wpp.xmli.visualization.data.addColumn( 'string', 'title1' );
  wpp.xmli.visualization.data.addColumn( 'string', 'text1' );
  wpp.xmli.visualization.data.addColumn( 'number', 'Sold Pens' );
  wpp.xmli.visualization.data.addColumn( 'string', 'title2' );
  wpp.xmli.visualization.data.addColumn( 'string', 'text2' );

  wpp.xmli.visualization.data.addRows([
    [ new Date( 2008, 1 ,1 ), 30000, undefined, undefined, 40645, undefined, undefined ],
    [ new Date( 2008, 1 ,2 ), 14045, undefined, undefined, 20374, undefined, undefined ],
    [ new Date( 2008, 1 ,3 ), 55022, undefined, undefined, 50766, undefined, undefined ],
    [ new Date( 2008, 1 ,4 ), 75284, undefined, undefined, 14334, 'Out of Stock','Ran out of stock on pens at 4pm' ],
    [ new Date( 2008, 1 ,5 ), 41476, 'Bought Pens','Bought 200k pens', 66467, undefined, undefined ],
    [ new Date( 2008, 1 ,6 ), 33322, undefined, undefined, 39463, undefined, undefined ]
  ]);

  wpp.xmli.visualization.chart = new google.visualization.AnnotatedTimeLine( document.getElementById( 'wpxi_feed_performance_chart' ) );
  wpp.xmli.visualization.chart.draw( wpp.xmli.visualization.data, { allowRedraw: true, displayZoomButtons: false, displayRangeSelector: false, colors: [ '#4D90FE', '#334C74' ], fill: 40, thickness: 3 });

};




