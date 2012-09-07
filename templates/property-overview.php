<?php
/**
 * WP-Property Overview Template - called by [property_overview] shortcode.
 *
 * To customize this file, copy it into your theme directory, and the plugin will
 * automatically load your version.

 * @version 1.4
 * @author Andy Potanin <andy.potnain@twincitiestech.com>
 * @package WP-Property
 *
 */
 ?>


<?php if ( have_properties() ): ?>

  <div class="<?php wpp_css('property_overview::row_view', 'wpp_row_view' ); ?>">

  <?php foreach ( returned_properties( 'load_gallery=false' ) as $property ): the_post(); ?>

    <?php echo WPP_F::get_template_part( array( 'content-property-listing-' . $property[ 'property_type' ], 'content-property-listing', ), array( WPP_Templates ), true );  ?>

  <?php endforeach; ?>

  </div>

<?php else: ?>
  
<?php endif; ?>


