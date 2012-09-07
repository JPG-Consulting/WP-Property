<?php
/**
 * Single Listing Information that shows up in [property_overview]
 *
 * In an ideal world, this would be automatically inserted when get_template_part( 'content', 'property-listing' ) is called.
 * but WordPress has no hook for remote template paths, so file has to be copied into template directory
 * to be available and have listings show up amongst other post types in regular search results and term pages.
 *
 * @since 1.37.0
 * @author potanin@UD
 * @copyright 2010-2012 Usability Dynamics, Inc.
 * @package WP-Property
 *
 */ ?>


<div class="<?php wpp_css('property_overview::property_div', 'property_div' ); ?>">

  <div class="<?php wpp_css('property_overview::left_column', 'wpp_overview_left_column' ); ?>">
    <?php property_overview_image(); ?>
  </div>

  <div class="<?php wpp_css('property_overview::right_column', 'wpp_overview_right_column' ); ?>">

    <ul class="<?php wpp_css('property_overview::data', 'wpp_overview_data' ); ?>">

      <li class="property_title">
        <a <?php echo $in_new_window; ?> href="<?php echo $property['permalink']; ?>"><?php echo $property['post_title']; ?></a>
      </li>

      <?php draw_stats(); ?>

    </ul>

  </div>

</div>
