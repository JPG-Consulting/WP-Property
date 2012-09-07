<?php
/**
 * Property Default Template for Single Property View
 *
 * To customize, copy this file to your theme directory. To create a property-type specific template, add the
 * property type to the end to customize further, example property-building.php or property-floorplan.php, etc.
 *
 *
 *
 * @version 2.0
 * @author team@UD
 * @copyright 2010-2012 Usability Dynamics, Inc. <info@usabilitydynamics.com>
 * @package WP-Property
 */

// Uncomment to disable fancybox script being loaded on this page
//wp_deregister_script('jquery-fancybox');
//wp_deregister_script('jquery-fancybox-css');
?>

<?php get_header(); ?>
<?php the_post(); ?>

  <div id="container" class="<?php wpp_css('property::container', array((!empty($post->property_type) ? $post->property_type . "_container" : ""))); ?>">
    <div id="content" class="<?php wpp_css('property::content', "property_content"); ?>" role="main">
      <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>


      <div class="<?php wpp_css('property::title', "building_title_wrapper"); ?>">
        <h1 class="property-title entry-title"><?php the_title(); ?></h1>
        <h3 class="entry-subtitle"><?php the_tagline(); ?></h3>
      </div>

      <div class="<?php wpp_css('property::entry_content', "entry-content"); ?>">
        <div class="<?php wpp_css('property::the_content', "wpp_the_content"); ?>"><?php @the_content(); ?></div>

        <?php @draw_stats("display=list&make_link=true&exclude=tagline&display_detailed=true"); ?>

        <?php if(!empty($wp_properties['taxonomies'])) foreach($wp_properties['taxonomies'] as $tax_slug => $tax_data): ?>
          <?php if(get_features("type={$tax_slug}&format=count")):  ?>
          <div class="<?php echo $tax_slug; ?>_list">
          <h2><?php echo $tax_data['label']; ?></h2>
          <ul class="clearfix">
          <?php get_features("type={$tax_slug}&format=list&links=true"); ?>
          </ul>
          </div>
          <?php endif; ?>
        <?php endforeach; ?>

        <?php //draw_stats("display=div_h2_p&make_link=true&exclude=tagline&display_detailed=only"); ?>

        <?php if(WPP_F::get_coordinates()): ?>
          <div id="property_map" class="<?php wpp_css('property::property_map'); ?>" style="width:100%; height:450px"></div>
        <?php endif; ?>

        <?php if(class_exists('WPP_Inquiry')): ?>
          <h2><?php _e('Interested?','wpp') ?></h2>
          <?php WPP_Inquiry::contact_form(); ?>
        <?php endif; ?>


        <?php if($post->post_parent): ?>
          <a href="<?php echo $post->parent_link; ?>" class="<?php wpp_css('btn', "btn btn-return"); ?>"><?php _e('Return to building page.','wpp') ?></a>
        <?php endif; ?>

      </div><!-- .entry-content -->
    </div><!-- #post-## -->

    </div><!-- #content -->
  </div><!-- #container -->


<?php
  // Primary property-type sidebar.
  if ( is_active_sidebar( "wpp_sidebar_" . $post->property_type ) ) : ?>

    <div id="primary" class="<?php wpp_css('property::primary', "widget-area wpp_sidebar_{$post->property_type}"); ?>" role="complementary">
      <ul class="xoxo">
        <?php dynamic_sidebar( "wpp_sidebar_" . $post->property_type ); ?>
      </ul>
    </div><!-- #primary .widget-area -->

<?php endif; ?>

<?php get_footer(); ?>