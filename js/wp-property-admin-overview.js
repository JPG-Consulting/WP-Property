/**
 * WP-Property Admin Overview Scripts
 *
*/

jQuery(document).ready(function() {

  admin_overview_init();

  // Toggling filter options
  jQuery('.wpp_filter_section_title').click(function(){
    var parent = jQuery(this).parents('.wpp_overview_filters');
    jQuery('.wpp_checkbox_filter', parent).slideToggle('fast', function(){
      if(jQuery(this).css('display') == 'none') {
        jQuery('.wpp_filter_show', parent).html('Show');
      } else {
        jQuery('.wpp_filter_show', parent).html('Hide');
      }
    });
  });

  // DataTable check all checkbox
  jQuery("input.check-all", "#wp-list-table").click(function(e){
    if ( e.target.checked ) {
      jQuery("#the-list td.cb input:checkbox").attr('checked', 'checked');
    } else {
      jQuery("#the-list td.cb input:checkbox").removeAttr('checked');
    }
  });

});

function admin_overview_init() {

  /* Load fancybox if it exists */
  if( typeof jQuery.fn.fancybox === 'function' ) {
    jQuery( 'a.wpp_listing_thumbnail' ).fancybox({
      'transitionIn'  :  'elastic',
      'transitionOut'  :  'elastic',
      'speedIn'    :  600,
      'speedOut'    :  200,
      'overlayShow'  :  false
    });
  }


  /**
   * Toggle Featured Setting
   *
   */
  jQuery( '.wpp_featured_toggle' ).click(function( event ){

    event.preventDefault();

    var _button = this;
    var _post_id = jQuery( this ).attr( 'data-post_id' );
    var _wpnonce = jQuery( this ).attr( 'data-wp_nonce' );

    jQuery.post( ajaxurl, {
      action: 'wpp_make_featured',
      _wpnonce: _wpnonce,
      post_id: _post_id
    }, function( data ) {

      if( typeof data === 'object' ) {

        jQuery( _button ).text( data.label );

        if( data.status == 'featured' ) {
          jQuery( _button ).closest( 'tr' ).addClass( 'wpp_is_featured' );
        }

        if( data.status == 'not_featured' ) {
          jQuery( _button ).closest( 'tr' ).removeClass( 'wpp_is_featured' );
        }

      } else {
        wpp.log( 'AJAX Feature Toggle call did not return JSON response.', 'error' );
      }

    }, 'json' );

  });

}


