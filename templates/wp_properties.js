/**
 * WP-Property Frontend JavaScript
 *
 *
 */

 jQuery(document).ready(function() {


  /**
   * Un-obfuscate a string by reversing it.
   *
   * @source WPP 2.0 Brach
   */
  wpp.unobfuscate = function( value ) {
    return value.split("").reverse().join("");
  };


  /**
   * {}
   *
   *
   */
  if( typeof jQuery.fn.fancybox === 'function' ) {
    jQuery('a.fancybox_image').live( 'click' , function() {
      if(!jQuery(this).hasClass('activated')) {
        jQuery( this ).fancybox({
          'transitionIn'	:	'elastic',
          'transitionOut'	:	'elastic',
          'speedIn'    :  600,
          'speedOut'    :  200,
          'overlayShow'  :  false
        });
        jQuery(this).addClass('activated');
        jQuery(this).trigger('click');
      }
      return false;
    });
  }

  /**
   * {}
   *
   */
  jQuery("a.fancybox_image img").click(function(e) {
    /* Do nothing in FancyBox is set */
    if(typeof jQuery.fn.fancybox === 'function') {
      return;
    }
    /* Fancybox is not set as expected, do not open the image URL */
    e.preventDefault();
  });


  /**
   * Scroll to top of pagination
   *
   */
  jQuery(document).bind('wpp_pagination_change', function(e, data) {
    var overview_id = data.overview_id;
    var position = jQuery("#wpp_shortcode_" + overview_id).offset();
    jQuery.scrollTo(position.top - 40 + 'px', 1500);

  });

  /**
   * Actions after FEPS PAY NOW
   */
  jQuery(document).bind('wpp_feps_pay_now_success', function() {
    jQuery('.wpi_checkout').remove();
  });

  /**
   * Adds Remove notification to 'remove' links.
   *
   */
  wpp.enable_remove_notification();

});
