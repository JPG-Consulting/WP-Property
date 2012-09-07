<?php
/**
 * Schedule Editor Page for XMLI - Delivered via AJAX
 *
 * @version 1.37.0
 * @package WP-Property
 * @author  team@UD
 * @copyright 2010-2012 Usability Dyanmics, Inc.
 */

?>


<div class="wpp_ui_wrapper">

  <form class="wpxi_editor" action="#" method="post">

  <table class="form-table wpp_option_table">
    <tbody>

      <tr class="wpxi-ui" data-wpxi-ui="import_heading">
        <th class="wpxi-ui-label"><strong><?php _e( 'Name & Source', 'wpp' ); ?></strong><div class="description"><?php /* _e( 'Use the XPath Query to narrow down the results from the XML file.', 'wpp' ); */ ?></div></th>
        <td class="wpxi-ui-content">

          <input class="wpp_major wpxi_schedule_name" name="wpxi[post_title]" type="text" value="<?php echo esc_attr( $wpxi[ 'post_title' ]); ?>" autocomplete="off"/>

          <table class="wpxi-ui wpp_gray_table" data-wpxi-ui="source_information" cellpadding="0" cellspacing="0">
            <tr>
              <td colspan="3">
                <input class="wpp_major wpxi_schedule_url" name="wpxi[url]" type="text" value="<?php echo esc_attr( $wpxi['url'] ); ?>" placeholder="http://source-of-data.com/file.xml" />
                <select name="wpxi[source_type]" class="wpp_major wpxi_source_type" data-wpxi_action="source_type_change">
                  <option <?php selected( $wpxi[ 'source_type' ], 'xml' ); ?> value="xml"><?php _e( 'XML', 'wpp' ); ?></option>
                  <option <?php selected( $wpxi[ 'source_type' ], 'rets' ); ?> value="rets"><?php _e( 'RETS', 'wpp' ); ?></option>
                  <option <?php selected( $wpxi[ 'source_type' ], 'uplf' ); ?> value='wpp'><?php _e( 'UPLF', 'wpp' ); ?></option>
                  <option <?php selected( $wpxi[ 'source_type' ], 'json' ); ?> value="json"><?php _e( 'JSON', 'wpp' ); ?></option>
                  <option <?php selected( $wpxi[ 'source_type' ], 'csv' ); ?> value="csv"><?php _e( 'CSV', 'wpp' ); ?></option>
                </select>

                <span class="wpp_button wpp_blue wpxi_source_validation" data-wpxi_action="source_validation"><span class="wpp_label" data-loading-text="<?php _e( 'Processing...', 'wpp'); ?>"><?php _e( 'Connect', 'wpp'); ?></span></span>

                <div class="wpxi-ui wpxi_contextual" data-wpxi-contextual-message="import_heading">
                  <?php

                  if( $wpxi[ 'files' ][ 'source' ][ 'path' ] && file_exists( $wpxi[ 'files' ][ 'source' ][ 'path' ] ) ) {
                    printf( __( 'Source file <a href="%1s" target="_blank"> %2s</a> cached.', 'wpp' ), $wpxi[ 'files' ][ 'source' ][ 'url' ], basename( $wpxi[ 'files' ][ 'source' ][ 'url' ] ) );
                  }

                  if( $wpxi[ 'source_type' ] == 'rets' && self::fresher_than( $wpxi[ 'analysis' ]->_updated, '3 hours' ) ) {
                    printf( __( 'RETS connection verified and data analyzed %1s ago.', 'wpp' ), human_time_diff( $wpxi[ 'analysis' ]->_updated ) );
                  }

                  ?>
                </div>

              </td>
            </tr>

            <tr class="wpxi-ui wpxi_login_detail wpp_first" data-wpxi-ui="rets_login">
              <th><?php _e( 'Account', 'wpp' ); ?></th>
              <td class="wpp_inline_list">
                <label>
                  <input type="text" name="wpxi[rets_username]" class="regular-text" value="<?php echo $wpxi['rets_username']?>" />
                  <span class="wpxi_label"><?php _e( 'Username', 'wpp' ); ?></span>
                </label>
                <label>
                  <input type="password" name="wpxi[rets_password]" value="<?php echo $wpxi['rets_password']?>" />
                  <span class="wpxi_label"><?php _e( 'Password', 'wpp' ); ?></span>
                </label>

              </td>
              <td class="wpxi-ui wpxi_contextual" data-wpxi-contextual-message="rets_credentials">&nbsp;</td>
            </tr>

            <tr class="wpxi-ui wpxi_login_detail wpxi-advanced" data-wpxi-ui="rets_advanced_login">
              <th><?php _e( 'User-Agent', 'wpp' ); ?></th>
              <td class="wpp_inline_list">
                <label>
                  <input type="text" name="wpxi[rets_agent]" class="regular-text"  value="<?php echo $wpxi['rets_agent']?>" />
                  <span class="wpxi_label"><?php _e( 'Name', 'wpp' ); ?></span>
                </label>
                <label>
                  <input type="password" name="wpxi[rets_agent_password]" value="<?php echo $wpxi['rets_agent_password']?>" />
                  <span class="wpxi_label"><?php _e( 'Password', 'wpp' ); ?></span>
                </label>
              </td>
              <td class="wpxi-ui wpxi_contextual" data-wpxi-contextual-message="rets_user_agent">&nbsp;</td>
            </tr>

          </table>

        </td>
        <td rowspan="6" class="wpp_ui_sidebar">
          <div class="wpp_sidebar_wrapper">

            <dl class="wpxi_schedule_attributes hidden">
              <dt><?php _e( 'Status', 'wpp' ); ?></dt>
              <dd class="wpxi_post_status" data-schedule_meta="post_status"><?php echo get_post_status_object( $wpxi[ 'post_status' ] )->label; ?></dd>
            </dl>

            <div class="wpp_sidebar_options">
              <ul class="wpp_settings">
                <li class="wpxi-ui" data-wpxi-ui="toggle_server_query"><span class="wpp_link" data-wpxi_action="toggle_server_query"><?php _e( 'Server Query', 'wpp' ); ?></span></li>
                <li class="wpxi-ui" data-wpxi-ui="toggle_filters"><span class="wpp_link" data-wpxi_action="toggle_filters"><?php _e( 'XPath Query', 'wpp' ); ?></span></li>
                <li class="wpxi-ui" data-wpxi-ui="toggle_attribute_map"><span class="wpp_link" data-wpxi_action="toggle_attribute_map"><?php _e( 'Attribute Map', 'wpp' ); ?></span></li>
                <li><span class="wpp_link" data-wpxi_action="toggle_import_settings"><?php _e( 'Advanced Options', 'wpp' ); ?></span></li>
              </ul>
            </div>

            <div class="wpp_actions_bar">
              <div class="wpp_save_wrapper">
                <input type="submit" class="wpp_button wpp_red wpxi_save_schedule" value="<?php _e( 'Save Schedule', 'wpp' ); ?>" />
                <div class="wpxi-ui wpxi_sidebar_response"></div>
              </div>
            </div>

          </div>
        </td>
      </tr>

      <tr class="wpxi-ui" data-wpxi-ui="source_status_response">
        <td colspan="2" class="wpp_ajax_response"></td>
      </tr>

      <tr class="wpxi-ui" data-wpxi-ui="server_query">
        <th class="wpxi-ui-label"><strong><?php _e( 'Server Query', 'wpp' ); ?></strong><div class="description"></div></th>
        <td class="wpxi-ui-content wpxi-ui-server_query_form_wrapper">
          <div class="wpxi-ui-server_query_form"></div>
          <div class="wpxi-ui-server_query_actions hidden">
            <div class="alignleft">
              <div class="wpxi-ui wpxi_contextual hidden wpxi-ui-server_query_contextual" data-wpxi-contextual-message="server_query_result"></div>
            </div>
            <div class="alignright">
              <span class="wpp_button wpp_grey wpxi_preview_xml" data-wpxi_action="preview_raw_xml"><span class="wpp_label" data-loading-text="<?php _e( 'Processing...', 'wpp'); ?>"><?php _e( 'Preview XML', 'wpp'); ?></span></span>
              <span class="wpp_button wpp_blue wpxi_server_query" data-wpxi_action="server_query"><span class="wpp_label" data-loading-text="<?php _e( 'Processing...', 'wpp'); ?>"><?php _e( 'Check Query', 'wpp'); ?></span></span>
            </div>
          </div>
        </td>
      </tr>

      <tr class="wpxi-ui" data-wpxi-ui="filters">
        <th class="wpxi-ui-label"><strong><?php _e( 'XPath Query', 'wpp' ); ?></strong></th>
        <td class="wpxi-ui-content">
          <input type="text" name="wpxi[root_element]" value="<?php echo esc_attr( $wpxi[ 'root_element' ] ); ?>" class="wpp_major wpxi_root_element" placeholder="/root/listings" data-wpxi_action="root_element_changed" />
          <span class="wpp_button wpp_blue wpxi_preview_xml" data-wpxi_action="preview_raw_xml"><span class="wpp_label" data-loading-text="<?php _e( 'Processing...', 'wpp'); ?>"><?php _e( 'Preview XML', 'wpp'); ?></span></span>
          <div class="wpxi-ui wpxi_contextual" data-wpxi-contextual-message="preview_raw_xml"></div>
        </td>
      </tr>

      <tr class="wpxi-ui" data-wpxi-ui="xml_analysis">
        <th class="wpxi-ui-label"><strong>&nbsp;</strong></td>
        <td class="wpp_ajax_response wpxi-ui-content">
          <div class="wpxi_xml_analysis"></div>
        </td>
      </tr>

      <tr class="wpxi-ui" data-wpxi-ui="advanced_options">
        <th class="wpxi-ui-label"><strong><?php _e( 'Options', 'wpp' ); ?></strong><div class="description"></div></th>
        <td class="wpxi-ui-content">

          <ul class="wpp_settings">

            <li class="wpxi-advanced">
              <label><?php _e( 'Run job every' , 'wpp' ); ?>
              <input type="text" class=" wpp_number" name="wpxi[schedule_number]" value="<?php echo esc_attr( $wpxi[ 'schedule_number' ] ); ?>"/>
              </label>
              <select class="" name="wpxi[schedule_unit]">
                <option value=""> - </option>
                <option value="hours" <?php selected( 'hours', $wpxi[ 'schedule_unit' ] ); ?>><?php _e( 'hours', 'wpp' ); ?></option>
                <option value="days" <?php selected( 'days', $wpxi[ 'schedule_unit' ] ); ?>><?php _e( 'days', 'wpp' ); ?></option>
              </select>
            </li>

            <li class="wpxi-advanced">
               <label for="wpxi_property_type"><?php echo sprintf(__( 'Default %1$s Type', 'wpp' ), ucfirst(WPP_F::property_label('singular'))); ?></label>
               <select name="wpxi[property_type]" id="wpxi_property_type">
                <option value=""> - </option>
                <?php foreach( (array) $wp_properties[ 'property_types' ] as $property_slug => $property_title ) { ?>
                <option value="<?php echo $property_slug; ?>"<?php selected( $property_slug, $wpxi['property_type'] ); ?>><?php echo $property_title; ?></option>
                <?php } ?>
               </select>
              <span class="description"><?php echo sprintf(__( 'Will be defaulted to if no xPath rule exists for the "%1$s Type".', 'wpp' ), ucfirst(WPP_F::property_label('singular'))); ?></span>
            </li>

            <li class="wpxi-advanced wpxi-source" data-wpxi-source="xml,json,csv">
              <label>
                <input type="checkbox" name="wpxi[postauth]" <?php echo checked( 'on', $wpxi[ 'postauth' ] ); ?>/>
                <input type="hidden" name="wpxi[postauth]" value="" />
                <?php _e( 'Make feed request using POST, and convert any GET variables into POST variables.', 'wpp' ); ?>
              </label>
            </li>

            <li class="wpxi-advanced">
              <label>
                <input type="checkbox" name="wpxi[send_email_updates]" value="on" <?php echo checked( 'on', $wpxi['send_email_updates'] ); ?>/>
                <input type="hidden" name="wpxi[send_email_updates]" value="" />
                <?php printf( __( 'Send %1s reports with updates.','wpp' ), get_option( 'admin_email' ) ); ?>
              </label>
            </li>

            <li class="wpxi-advanced">
              <label>
                <input type="checkbox" name="wpxi[remove_non_existant]" value="on"<?php echo checked( 'on', $wpxi['remove_non_existant'] ); ?> />
                <input type="hidden" name="wpxi[remove_non_existant]" value="" />
                <?php echo sprintf(__( 'Remove %1$s that are no longer in source XML from this site\'s database.' , 'wpp' ), WPP_F::property_label('plural')); ?>
              </label>
            </li>

            <li class="wpxi-advanced">
              <?php printf( __( 'Only import images that are over %1$spx in width, and %2$spx in height.','wpp' ), '<input type="text" value="'. $wpxi["min_image_width"] .'" name="wpxi[min_image_width]" class="wpp_number" />', '<input type="text" value="'. $wpxi["min_image_height"] .'" name="wpxi[min_image_height]" class="wpp_number" />' ); ?>
            </li>

            <li class="wpxi-advanced">
              <label for="wpxi_minimum_images"><?php _e( 'Imported properties must have at least ','wpp' ); ?></label>
              <input id="wpxi_minimum_images" type="text" class="wpp_number wpp_xmli_enforce_integer" name="wpxi[minimum_images]" value="<?php echo ( empty( $wpxi['minimum_images'] ) ? '' : $wpxi['minimum_images'] ); ?>"/><?php _e( ', but no more than ','wpp' ); ?>
              <input type="text" class="wpp_number wpp_xmli_enforce_integer" name="wpxi[limit_images]" value="<?php echo ( empty( $wpxi['limit_images'] ) ? '' : $wpxi['limit_images'] ); ?>"/>
              <?php _e( ' valid images.','wpp' ); ?>
            </li>

            <?php /*

            @development Hidden Temporarily.

            <li class="wpxi-advanced">
              <label><?php _e( 'Skip listings that have been updated less than ','wpp' ); ?>
              <input type="text" class="wpp_number" name="wpxi[reimport_delay]" value="<?php echo $wpxi['reimport_delay']; ?>"/>
              <?php _e( 'hour(s) before.','wpp' ); ?>
              </label>
            </li>

            <li class="wpxi-advanced">
              <label>
                <input type="checkbox" name="wpxi[fix_caps]" value="on"<?php echo checked( 'on', $wpxi['fix_caps'] ); ?> />
                <?php _e( 'Fix strings that are in all caps.','wpp' ); ?>
             </label>
            </li>

            <li class="wpxi-advanced">
              <label>
                <input type="checkbox" name="wpxi[force_remove_formatting]" value="on"<?php echo checked( 'on', $wpxi['force_remove_formatting'] ); ?> />
                <?php _e( 'Scan for any formatting tags and strip them out.','wpp' ); ?>
             </label>
            </li>

            <li class="wpxi-advanced">
              <label>
                <input type="checkbox" name="wpxi[revalidate_addreses_on_completion]" value="on" <?php echo checked( 'on', $wpxi['revalidate_addreses_on_completion'] ); ?> />
                <?php _e( 'Geolocate listings that do not already have latitute and longitude values.','wpp' ); ?>
             </label>
            </li>

            <li class="wpxi-advanced">
              <label class="description" for="wpp_property_limit_scanned_properties"><?php _e( '<b>Pre-QC Limit:</b> Limit import to the first','wpp' ); ?>
              <input type="text"  class="wpp_xmli_enforce_integer wpp_number"  id="wpp_property_limit_scanned_properties" name="wpxi[limit_scanned_properties]" value="<?php echo ( empty( $wpxi['limit_scanned_properties'] ) ? '' : $wpxi['limit_scanned_properties'] ); ?>"/>
              <?php _e( 'properties in the feed.','wpp' ); ?>
              <span wpp_scroll_to="h3.limit_import" class="wpp_link wpp_toggle_contextual_help"><?php _e( 'More about limits.', 'wpp' ); ?></span>
           </label>
            </li>

            <li class="wpxi-advanced">
              <label><?php _e( '<b>Post-QC Limit:</b> Limit import to the first','wpp' ); ?>
              <input type="text"   class="wpp_xmli_enforce_integer wpp_number" id="wpp_property_limit_properties" name="wpxi[limit_properties]" value="<?php echo ( empty( $wpxi['limit_properties'] ) ? '' : $wpxi['limit_properties'] ); ?>"/>
              <?php _e( 'created properties that have passed quality standards.','wpp' ); ?>
             </label>
            </li>

            <li class="wpxi-advanced">
              <label>
                <input type="checkbox" name="wpxi[remove_images]" value="on"<?php echo checked( 'on', $wpxi['remove_images'] ); ?>/>
                <?php _e( 'When updating an existing property, remove all old images before downloading new ones. (Not recommended) ','wpp' ); ?>
              </label>
            </li>

            <li class="wpxi-advanced">
              <label>
                <input type="checkbox" name="wpxi[remove_all_from_this_source]" value="on" <?php echo checked( 'on', $wpxi['remove_all_from_this_source'] ); ?> />
                <?php _e( 'Remove all properties that were originally imported from this feed on import.','wpp' ); ?>
             </label>
            </li>

            <li class="wpxi-advanced">
              <label>
              <input type="checkbox" name="wpxi[remove_all_before_import]" value="on"<?php echo checked( 'on', $wpxi['remove_all_before_import'] ); ?> />
                <?php _e( 'Completely remove <b>all</b> existing properties prior to import.','wpp' ); ?>
             </label>
            </li>

            <?php if( class_exists( 'class_wpp_slideshow' ) ) { ?>
            <li class="wpxi-advanced">
              <label>
                <input type="checkbox" name="wpxi[automatically_load_slideshow_images]" value="on"<?php echo checked( 'on', $wpxi['automatically_load_slideshow_images'] ); ?> />
                <?php _e( 'Automatically load imported images into property slideshow.','wpp' ); ?>
             </label>
            </li>
            <?php } ?>

            <li class="wpxi-advanced">
              <label>
                <input type="checkbox" name="wpxi[log_detail]" value="on" <?php echo checked( 'on', $wpxi['log_detail'] ); ?> />
                <?php _e( 'Enable detailed logging to assist with troubleshooting.','wpp' ); ?>
              </label>
            </li>


             do_action( 'wpp_import_advanced_options', $wpxi );

             */ ?>

          </ul>

        </td>
      </tr>

      <tr class="wpxi-ui" data-wpxi-ui="attribute_map">
        <th class="wpxi-ui-label"><strong><?php _e( 'Attribute Map', 'wpp' ); ?></strong></th>
        <td class="wpxi-ui-content wpxi_schedule_map">
          <?php echo class_wpp_property_import::ui_map( $wpxi ); ?>
        </td>
      </tr>

      <tr class="wpxi-ui" data-wpxi-ui="listing_preview_response">
        <th class="wpxi-ui-label"><strong><?php _e( 'Listing Preview', 'wpp' ); ?></strong></th>
        <td class="wpxi-ui-content wpp_ajax_response wpxi_listing_preview_response"></td>
      </tr>

    </tbody>
  </table>

  </form>
</div>