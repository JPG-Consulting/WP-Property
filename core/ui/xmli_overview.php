<?php
/**
 * Overview page for XMLI
 *
 * @version 2.0
 * @package WP-Property
 * @subpackage XMLI
 * @author  team@UD
 * @copyright 2010-2012 Usability Dyanmics, Inc.
 *
 */

 ?>

<?php if( isset( $wp_messages['error'] ) && $wp_messages['error'] ) { ?>
  <div class="wpxi_core_notice wpxi-overview_element" data-message-type="error"><?php foreach( (array) $wp_messages['error'] as $error_message ) { ?><?php echo $error_message; ?><?php } ?></div>
<?php } ?>

<?php if( isset( $wp_messages['notice'] ) && $wp_messages['notice'] ) { ?>
  <div class="wpxi_core_notice wpxi-overview_element" data-message-type="notice"><?php foreach( (array) $wp_messages['notice'] as $notice_message ) { ?><?php echo $notice_message; ?><?php } ?></div>
<?php } ?>

<?php if( !$schedules ) {  ?>
  <div class="wpxi_core_notice wpxi-overview_element">
    <?php _e( 'You do not have any saved schedules. Create one now.','wpp' ); ?>
  </div>
<?php } ?>

<div class="wpxi_primary_actions wpp_buttons wpxi-overview_element">

  <?php if( $schedules ) {  ?>
    <span class="wpp_button wpp_left" data-wpxi_action="toggle_visualization"><span class="wpp_icon wpp_icon_178"></span><span class="wpp_label"><?php _e( 'Toggle Statistics', 'wpp' ); ?></span></span>
  <?php } ?>

  <span class="wpp_button wpp_right" data-wpxi_action="toggle_backup_uploader"><span class="wpp_icon wpp_icon_67"></span><span class="wpp_label"><?php _e( 'Upload Backup', 'wpp' ); ?></span></span>

  <?php if( current_user_can( get_post_type_object( 'wpxi_schedule' )->cap->publish_posts ) ) { ?>
  <div class="alignright">
    <a class="wpxi_add_schedule wpp_button wpp_action wpp_red" href="#add_new_schedule" data-wpxi_action="add_new_schedule"><span class="wpp_label"><?php _e( 'Add New Import' ); ?></label></a>
  </div>
  <?php } ?>

</div>

<div class="wpxi_backup_uploader wpxi-overview_element hidden">
  <form method="post" action="<?php echo admin_url( 'edit.php?post_type=property&page=wpp_property_import' ); ?>" enctype="multipart/form-data" />
    <input type="hidden" name="wpxi_action" value="import_wpp_schedule" />
    <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'wpxi_backup_uploader' ); ?>" />
    <label class="wpp_major"><?php _e( 'Import Schedule:', 'wpp' ); ?></label>
    <div class="wpxi_upload_wrapper"><input class="wpp_major"  name="wpp_import[import_schedule][]" type="file" multiple="multiple" /></div>
    <input type="submit" value="<?php _e( 'Upload File','wpp' ); ?>" class="wpp_button wpp_red"/>
  </form>
</div>

<div class="wpxi_chart_wrapper wpxi-overview_element hidden">
  <div id="wpxi_feed_performance_chart" style="width: 100%; height: 350px;"></div>
</div>

<div class="wpxi_saved_schedules_wrapper <?php if( !$schedules ) { ?>hidden<?php } ?>">

  <table class="wpxi_saved_schedules wpp_clean wpxi-overview_element">
    <thead>
      <tr>
        <th class="wpxi_schedule_data" colspan="2"><?php _e( 'Import Overview', 'wpp' ); ?></th>
        <th class="wpxi_schedule_status"><?php _e( 'Latest Updates', 'wpp' ); ?></th>
      </tr>
    </thead>
    <tbody>

      <?php foreach( (array) $schedules as $schedule_id => $schedule ) {

        $_attributes = implode( ' ', array(
          'data-schedule-id="' . $schedule_id . '"',
          'data-source-type="' . $schedule[ 'source_type' ] . '"',
          'data-post-status="' . $schedule[ 'post_status' ] . '"',
          'data-import-title="' . esc_attr( $schedule[ 'post_title' ] ) . '"'
        ));

        $class = array(
          'edit_schedule' => ( $schedule[ 'post_status' ] != 'publish' ? 'hidden' : '' ),
          'import_job' => ( $schedule[ 'post_status' ] != 'publish' ? 'hidden' : '' ),
          'cancel_job' => ( $schedule[ 'post_status' ] == 'importing' ? '' : 'hidden' ),
          'remove_content' => ( ( empty( $schedule[ '_quantifiable' ][ 'total_listings' ] ) || $schedule[ 'post_status' ] != 'publish' ) ? 'hidden' : '' ),
          'delete_schedule' => ( $schedule[ 'post_status' ] != 'publish' ? 'hidden' : '' ),
        );

      ?>

      <tr class="wpxi_schedule_row" <?php echo $_attributes; ?> >

        <td class="wpxi_icon_column">
          <div class="wpp_ddm wpp_on">
            <a href="#<?php echo $schedule_id; ?>" data-wpxi_action="edit_schedule" class="wpxi_edit_schedule" <?php echo $_attributes; ?> ><span class="wpp_icon wpp_icon_55" ></span></a>
            <span class="wpxi_edit_schedule wpxi_not_link wpp_processing hidden" data-wpxi_action="edit_schedule" <?php echo $_attributes; ?>><span class="wpp_icon wpp_icon_55" ></span></span>
          </div>
        </td>

        <td class="wpxi_primary_column">
          <ul>
            <li class="wpxi_primary_action">
              <a href="#<?php echo $schedule_id; ?>" class="wpxi_edit_schedule" data-schedule-id="<?php echo $schedule_id; ?>" data-wpxi_action="edit_schedule"><?php echo $schedule[ 'post_title' ]; ?></a>
              <span class="wpxi_edit_schedule wpxi_not_link wpp_processing hidden" data-wpxi_action="edit_schedule"><?php echo $schedule[ 'post_title' ]; ?></span>
              <span class="wpxi_post_status"><?php echo get_post_status_object( $schedule[ 'post_status' ] )->label; ?></span>
            </li>

            <li class="wpxi_progress_bar"></li>

            <li class="wpxi_inline_actions">
              <span class="<?php echo $class['edit_schedule']; ?>" data-wpxi_action="edit_schedule"><a href="#<?php echo $schedule_id; ?>" class="wpp_link wpxi_edit_schedule"><?php _e( 'Edit', 'wpp' ); ?></a> | </span>
              <span class="<?php echo $class['import_job']; ?>" data-wpxi_action="import_job"><span class="wpp_link"><?php _e( 'Start Import', 'wpp' ); ?></span> | </span>
              <span class="<?php echo $class['cancel_job']; ?>" data-wpxi_action="cancel_job"><span class="wpp_link"><?php _e( 'Stop Import', 'wpp' ); ?></span> | </span>
              <span data-wpxi_action="download_backup"><a href="<?php echo wp_nonce_url( "edit.php?post_type=property&page=wpp_property_import&wpxi_action=download-wpp-import-schedule&schedule_id={$schedule_id}", 'download-wpp-import-schedule' ); ?>"><?php _e( 'Download Backup', 'wpp' ); ?></a> | </span>
              <span class="<?php echo $class['remove_content']; ?>" data-wpxi_action="remove_content"><span class="wpp_link wpxi_remove_content"><?php _e( 'Delete All Content', 'wpp' ); ?></span> | </span>
              <span class="<?php echo $class['delete_schedule']; ?>" data-wpxi_action="delete_schedule"><span class="wpp_link"><?php _e( 'Delete', 'wpp' ); ?></span></span>
            </li>

            <li class="wpxi_total_listings">
            <?php if( $schedule[ '_quantifiable' ][ 'total_listings' ] ) { echo $schedule[ '_print_stats' ][ 'total_listings' ]; } ?>
            </li>

            <li class="wpxi_schedule">
            <?php if( $schedule[ 'schedule_number' ] && $schedule[ 'schedule_unit' ] ) { printf( __( 'Scheduled for every %1s %2s.', 'wpp' ), $schedule[ 'schedule_number' ], $schedule[ 'schedule_unit' ] ); } ?>
            </li>

            <li class="wpxi_source">
            <?php if( $schedule['url'] ) { _e( 'Source domain:', 'wpp' ); ?> <span class="wpxi_overview_special_data"><?php echo parse_url( $schedule['url'], PHP_URL_HOST ); } ?></span>
            </li>

          </ul>
        </td>

        <td class="wpxi_schedule_status">
          <ul class="wpxi_schedule_updates" data-wpxi_last_id="<?php echo $schedule[ 'logs' ][ 'last_id' ]; ?>">
          <?php foreach( $schedule[ 'logs' ][ 'data' ] as $log ) : ?>
            <li>
              <span class="wpxi_log_message"><?php echo $log->message; ?></span>
              <span class="wpxi_log_time"><?php echo $log->time; ?></span>
            </li>
          <?php endforeach; ?>
          </ul>
        </td>

      </tr>
    <?php } ?>
    </tbody>
  </table>
</div>
