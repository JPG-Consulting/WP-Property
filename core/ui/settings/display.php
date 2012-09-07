<?php
/**
 * Settings Page Section - Display Options
 *
 * @version 1.37.0
 * @package WP-Property
 * @author  team@UD
 * @copyright 2010-2012 Usability Dyanmics, Inc.
 */

//** Required globals */
global $wp_properties;
?>

<table class="form-table wpp_option_table">

  <tr class="wpp_first" data-wpp-feature="image_sizes">
    <th>
      <strong><?php _e('Image Sizes', 'wpp'); ?></strong>
      <div class="description"><p><?php _e('In addition to WP-Property use, the images sizes you create here can be used by your theme as well as other plugins.', 'wpp'); ?> </p></div>
    </th>
    <td>
      <table id="wpp_image_sizes" class="ud_ui_dynamic_table wpp_clean">
        <thead>
          <tr>
            <th><?php _e('Slug', 'wpp'); ?></th>
            <th><?php _e('Width', 'wpp'); ?></th>
            <th><?php _e('Height', 'wpp'); ?></th>
            <th>&nbsp;</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $wpp_image_sizes = (array) $wp_properties['image_sizes'];

          foreach ((array) get_intermediate_image_sizes() as $slug) {

            $slug = trim($slug);
            $image_dimensions = WPP_F::image_sizes($slug, "return_all=true");

            //** Skip images w/o dimensions */
            if (!$image_dimensions) {
              continue;
            }

            if (!empty($wpp_image_sizes[$slug])) {
          ?>
              <tr class="wpp_dynamic_table_row" slug="<?php echo $slug; ?>">
                <td class="wpp_slug"><input class="slug_setter slug"  type="text" value="<?php echo $slug; ?>" /></td>
                <td class="wpp_width"><input type="text" name="wpp_settings[image_sizes][<?php echo $slug; ?>][width]" value="<?php echo $image_dimensions['width']; ?>" /></td>
                <td class="wpp_height"><input type="text" name="wpp_settings[image_sizes][<?php echo $slug; ?>][height]" value="<?php echo $image_dimensions['height']; ?>" /></td>
                <td><span class="wpp_delete_row wpp_link"><?php _e('Delete', 'wpp') ?></span></td>
              </tr>
          <?php } else { ?>
              <tr>
                <td><div class="wpp_permanent_image"><?php echo $slug; ?></div></td>
                <td><div class="wpp_permanent_image"><?php echo $image_dimensions['width']; ?></div></td>
                <td><div class="wpp_permanent_image"><?php echo $image_dimensions['height']; ?></div></td>
                <td>&nbsp;</td>
              </tr>
          <?php }
              }
          ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="4">
              <input type="button" class="wpp_add_row button-secondary" value="<?php _e('Add Row', 'wpp') ?>" />
            </td>
          </tr>
        </tfoot>
      </table>
    </td>
  </tr>
  <tr class="wpp_something_advanced_wrapper" data-wpp-feature="localization">
    <th>
      <strong><?php _e('Localization', 'wpp'); ?></strong>
      <div class="description"><p><?php /* _e( '','wpp' ); */ ?></p></div>
    </th>
    <td>
      <ul>
        <li data-wpp-setting="area_unit_type">
          <label>
            <?php _e('All areas are saved using squared', 'wpp'); ?>
            <select name="wpp_settings[configuration][area_unit_type]">
              <option value=""> - </option>
              <option value="square_foot" <?php selected($wp_properties['configuration']['area_unit_type'], 'square_foot'); ?>><?php _e('feet', 'wpp'); ?>&nbsp;(<?php echo wpp_default_api::get_area_unit('square_foot')?>)</option>
              <option value="square_meter" <?php selected($wp_properties['configuration']['area_unit_type'], 'square_meter'); ?>><?php _e('meters', 'wpp'); ?>&nbsp;(<?php echo wpp_default_api::get_area_unit('square_meter')?>)</option>
              <option value="square_kilometer" <?php selected($wp_properties['configuration']['area_unit_type'], 'square_kilometer'); ?>><?php _e('kilometers', 'wpp'); ?>&nbsp;(<?php echo wpp_default_api::get_area_unit('square_kilometer')?>)</option>
              <option value="square_mile" <?php selected($wp_properties['configuration']['area_unit_type'], 'square_mile'); ?>><?php _e('miles', 'wpp'); ?>&nbsp;(<?php echo wpp_default_api::get_area_unit('square_mile')?>)</option>
            </select>
          </label>
        </li>
        <li data-wpp-setting="currency_symbol">
          <label><?php echo __('All prices are stored using the', 'wpp') . WPP_F::input("name=currency_symbol&label=" . __('currency.', 'wpp') . "&class=currency&group=wpp_settings[configuration]", $wp_properties['configuration']['currency_symbol']); ?></label>
        </li>
        <li data-wpp-setting="thousands_sep" class="wpp_development_advanced_option">
          <label>
            <?php _e('Thousands separator symbol:', 'wpp'); ?>
            <select name="wpp_settings[configuration][thousands_sep]">
              <option value=""> - </option>
              <option value="." <?php selected($wp_properties['configuration']['thousands_sep'], '.'); ?>><?php _e('. (period)', 'wpp'); ?></option>
              <option value="," <?php selected($wp_properties['configuration']['thousands_sep'], ','); ?>><?php _e(', (comma)', 'wpp'); ?></option>
            </select>
          </label>
          <span class="description"><?php _e('The character separating the 1 and the 5: $1<b>,</b>500'); ?></span>
        </li>
        <li data-wpp-setting="currency_symbol_placement" class="wpp_development_advanced_option">
          <label>
            <?php _e('Currency symbol placement:', 'wpp'); ?>
            <select name="wpp_settings[configuration][currency_symbol_placement]">
              <option value=""> - </option>
              <option value="before" <?php selected($wp_properties['configuration']['currency_symbol_placement'], 'before'); ?>><?php _e('Before number', 'wpp'); ?></option>
              <option value="after" <?php selected($wp_properties['configuration']['currency_symbol_placement'], 'after'); ?>><?php _e('After number', 'wpp'); ?></option>
            </select>
          </label>
        </li>
        <li data-wpp-setting="google_maps_localization">
          <label>
            <?php _e('Localize and display geolocated addresses in', 'wpp'); ?> <?php echo WPP_F::draw_localization_dropdown("name=wpp_settings[configuration][google_maps_localization]&selected={$wp_properties['configuration']['google_maps_localization']}"); ?>
          </label>
        </li>
        <li>
          <span class="wpp_show_advanced"><?php _e('Toggle Advanced Localization Settings', 'wpp'); ?></span>
        </li>
      </ul>
    </td>
  </tr>

  <?php do_action('wpp_settings_display_tab_bottom'); ?>

</table>