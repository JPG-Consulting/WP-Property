<?php
/**
 * Schedule's Map for XMLI - Delivered via AJAX
 *
 * @version 1.37.0
 * @package WP-Property
 * @author  team@UD
 * @copyright 2010-2012 Usability Dyanmics, Inc.
 */

?>

<table data-auto_increment="true" class="wpp_sortable wpxi_attribute_mapper ud_ui_dynamic_table wpp_clean wpp_tight">
  <thead>
    <tr>
      <th class='wpp_draggable_handle'>&nbsp;</th>
      <th class="wpxi_attribute_dropdown wpp_sortable"><span class="wpxi_sort_attribute_rows"><?php _e( 'Attribute','wpp' ); ?></span><span class="wpp_sorted"></span></th>
      <th class="wpxi_xpath_rule"><?php _e( 'XPath Rule','wpp' ); ?></th>
      <?php /* ?><th class="wpxi_attribute_format"><?php _e( 'Data Format','wpp' ); ?></th><?php //*/ ?>
      <th class="wpp_remove_row"></th>
    </tr>
  </thead>

  <tbody>
    <?php foreach( (array) $wpxi[ 'map' ] as $index => $attr ) : ?>
    <tr class="wpp_dynamic_table_row" data-row-id="<?php echo $index; ?>">
      <td class="wpp_draggable_handle">&nbsp;</td>
      <td class="wpp_attribute_dropdown">

        <select name="wpxi[map][<?php echo ($index); ?>][wpp_attribute]" class="wpxi_attribute_dropdown">
          <option value=""> - </option>
          <option value="post_title" <?php selected( 'post_title', $attr[ 'wpp_attribute' ] ); ?>><?php _e( 'Title', 'wpp' ); ?></option>
          <option value="post_content" <?php selected( 'post_content', $attr[ 'wpp_attribute' ] ); ?>><?php _e( 'Description', 'wpp' ); ?></option>
          <option value="post_excerpt" <?php selected( 'post_excerpt', $attr[ 'wpp_attribute' ] ); ?>><?php _e( 'Excerpt', 'wpp' ); ?></option>
          <option value="display_address" <?php selected( $attr['wpp_attribute'], 'display_address' ); ?>><?php _e( 'Address', 'wpp' ); ?></option>
          <option value="images" <?php selected( 'images', $attr['wpp_attribute'] ); ?>><?php _e( 'Images', 'wpp' ); ?></option>
          <option value="_wpp::source_modified_time" <?php selected( '_wpp::source_modified_time', $attr['wpp_attribute'] ); ?>><?php _e( 'Modification Date', 'wpp' ); ?></option>
          <option value="_wpp::source_unique_id" <?php selected( '_wpp::source_unique_id', $attr['wpp_attribute'] ); ?>><?php _e( 'Unique ID', 'wpp' ); ?></option>

          <?php foreach( WPP_F::get_total_attribute_array( array( 'use_optgroups' => true ) ) as $_group_label => $_group_attributes ) {  ?>
          <optgroup label="<?php echo $_group_label ?>">
            <?php foreach( (array) $_group_attributes as $property_stat_slug => $property_stat_label ) { ?>
            <option value="<?php echo $property_stat_slug; ?>" <?php selected( $attr['wpp_attribute'], $property_stat_slug ); ?>><?php echo esc_attr( $property_stat_label ); ?></option>
            <?php } ?>
          </optgroup>
          <?php } ?>

          <optgroup label="<?php _e( 'Taxonomies', 'wpp' ); ?>">
            <?php foreach( (array) $wp_properties['taxonomies'] as $tax_slug => $tax ) { ?>
            <option value="<?php echo $tax_slug; ?>"<?php echo ( $attr['wpp_attribute'] == $tax_slug ) ? 'selected="selected"':''; ?> ><?php echo $tax['label']; ?></option>
            <?php } ?>
          </optgroup>

          <optgroup label="<?php _e( 'Address', 'wpp' ); ?>" class="wpxi_advanced_attributes">
            <option class="wpxi_advanced_attributes" value="street_number" <?php selected( $attr['wpp_attribute'], 'street_number' ); ?>><?php _e( 'Street Number', 'wpp' ); ?></option>
            <option class="wpxi_advanced_attributes" value="route" <?php selected( $attr['wpp_attribute'], 'route' ); ?>><?php _e( 'Street', 'wpp' ); ?></option>
            <option class="wpxi_advanced_attributes" value="city" <?php selected( $attr['wpp_attribute'], 'city' ); ?>><?php _e( 'City', 'wpp' ); ?></option>
            <option class="wpxi_advanced_attributes" value="county" <?php selected( $attr['wpp_attribute'], 'county' ); ?>><?php _e( 'County', 'wpp' ); ?></option>
            <option class="wpxi_advanced_attributes" value="state" <?php selected( $attr['wpp_attribute'], 'state' ); ?>><?php _e( 'State', 'wpp' ); ?></option>
            <option class="wpxi_advanced_attributes" value="latitude" <?php selected( $attr['wpp_attribute'], 'latitude' ); ?>><?php _e( 'Latitude', 'wpp' ); ?></option>
            <option class="wpxi_advanced_attributes" value="longitude" <?php selected( $attr['wpp_attribute'], 'longitude' ); ?>><?php _e( 'Longitude', 'wpp' ); ?></option>
            <option class="wpxi_advanced_attributes" value="country" <?php selected( $attr['wpp_attribute'], 'country' ); ?>><?php _e( 'Country', 'wpp' ); ?></option>
            <option class="wpxi_advanced_attributes" value="postal_code" <?php selected( $attr['wpp_attribute'], 'postal_code' ); ?>><?php _e( 'Postal Code', 'wpp' ); ?></option>
          </optgroup>

          <optgroup label="<?php _e( 'Advanced', 'wpp' ); ?>" class="wpxi_advanced_attributes">
            <?php if( class_exists( 'class_agents' ) ) { ?>
              <option value="wpp_agents" <?php selected( $attr['wpp_attribute'], 'wpp_agents' ); ?>><?php _e( 'Agent', 'wpp' ); ?></option>
            <?php } ?>
            <option class="wpxi_advanced_attributes" value="property_type" <?php selected( $attr['wpp_attribute'], 'property_type' ); ?>><?php _e( 'Listing Type', 'wpp' ); ?></option>
            <option class="wpxi_advanced_attributes" value="featured-image" <?php selected( $attr['wpp_attribute'], 'featured-image' ); ?> ><?php _e( 'Featured Image', 'wpp' ); ?></option>
            <option class="wpxi_advanced_attributes" value="post_author" <?php selected( $attr['wpp_attribute'], 'post_author' ); ?>><?php _e( 'Author ID', 'wpp' ); ?></option>
            <option class="wpxi_advanced_attributes" value="post_date" <?php selected( $attr['wpp_attribute'], 'post_date' ); ?>><?php _e( 'Listing Date', 'wpp' ); ?></option>
            <option class="wpxi_advanced_attributes" value="menu_order" <?php selected( $attr['wpp_attribute'], 'menu_order' ); ?>><?php _e( 'Menu Order', 'wpp' ); ?></option>
            <option class="wpxi_advanced_attributes" value="post_status" <?php selected( 'post_status', $attr[ 'wpp_attribute' ] ); ?>><?php _e( 'Listing Status', 'wpp' ); ?></option>
            <option class="wpxi_advanced_attributes" value="_wpp::gpid" disabled="disabled" <?php selected( $attr['wpp_attribute'], '_wpp::gpid' ); ?>><?php _e( 'Global ID', 'wpp' ); ?></option>
          </optgroup>

        </select>

      </td>

      <td class="wpxi_xpath_rule">
        <input style="width: 100%;" name="wpxi[map][<?php echo ($index); ?>][xpath_rule]" type="text" class="xpath_rule wpxi_xpath_rule regular-text" value="<?php echo esc_attr( $attr[ 'xpath_rule' ] ); ?>" />
      </td>

      <?php /* // There is no any sense to set attribute type here. Attribute type must be set on WPP Settings page. ?>
      <td class="wpxi_attribute_format">
        <select name="wpxi[map][<?php echo ($index); ?>][type]" class="wpxi_attribute_format">
          <option value=""> - </option>
          <?php foreach( (array) $wp_properties[ '_attribute_format' ] as $format_group => $format_values ) { ?>
            <optgroup label="<?php echo esc_attr( $format_group ); ?>">
            <?php foreach( (array) $format_values as $format_slug => $format_label ) { ?>
            <option value="<?php echo esc_attr( $format_slug ); ?>" <?php selected( $format_slug, $wp_properties[ '_attribute_type' ][ $slug ] ); ?>><?php echo esc_attr( $format_label ); ?></option>
            <?php } ?>
            </optgroup>
          <?php } ?>
        </select>
      </td>
      <?php //*/ ?>

      <td class="wpp_remove_row" title="<?php _e( 'Remove Attribute', 'wpp' ); ?>">&nbsp;</td>

    </tr>
    <?php endforeach; ?>
  </tbody>

  <tfoot>
    <tr>
      <td colspan="4">
        <span class="wpp_button wpp_add_row wpp_blue wpp_left wpxi_add_attribute" data-callback-function="wpp.xmli.attribute_added"><span class="wpp_label"><?php _e( 'Add Attribute Row', 'wpp' ); ?></span></span>

        <span class="wpxi_unique_id_wrapper">
          <label class="wpp_major"><?php _e( 'Unique Identifier:' , 'wpp' ); ?></label>
          <select class="wpxi_unique_id wpp_major" name="wpxi[unique_id]">
            <?php $total_attribute_array = WPP_F::get_total_attribute_array(); ?>
            <?php foreach( (array) $wpxi[ 'map' ] as $attr ) : ?>
              <option value="<?php echo $attr['wpp_attribute']; ?>"<?php selected( $attr['wpp_attribute'], $wpxi['unique_id'] ); ?>><?php echo $total_attribute_array[ $attr['wpp_attribute'] ]; ?> ( <?php echo $attr[ 'wpp_attribute' ]; ?> )</option>
            <?php endforeach; ?>
          </select>
        </span>

        <div class="alignright">
          <span class="wpp_button wpp_green" data-wpxi_action="preview_listings"><span class="wpp_label"><?php _e( 'Preview Listings', 'wpp' ); ?></span></span>
        </div>

      </td>
    </tr>
  </tfoot>
</table>
