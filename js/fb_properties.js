wpp_fb_frontend_actions = {
  init : function () {
    wpp_fb_frontend_actions.settings.open_links_in_new_window( true );
    wpp_fb_frontend_actions.settings.open_forms_in_new_window( true );
  },
  settings : {
    open_links_in_new_window : function ( state ) {
      if ( state ) {
        jQuery('a:not(:has(*))[href^="http"]').unbind('click').bind("click", function() {window.open( jQuery(this).attr('href') );
  return false;})
      }
    },
    open_forms_in_new_window : function ( state ) {
      if ( state ) {
        jQuery('form').attr('target', '_blank');
      }
    }
  }
};
jQuery(document).ready( wpp_fb_frontend_actions.init );
jQuery(document).ajaxComplete( wpp_fb_frontend_actions.init );

