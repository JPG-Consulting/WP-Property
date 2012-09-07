<?php
/**
 * Renders WPP Log
 *
 * @since 1.37.0
 * @package WP-Property
 * @author  team@UD
 * @copyright 2010-2012 Usability Dyanmics, Inc.
 */

global $wpdb;

?>
<div class="wrap wpp-log-wrap">
  <h2><?php _e( 'Activity Log', 'wpp' ); ?><a class="button add-new-h2 wpp_remove" data-notice="<?php _e( 'Are you sure you want to remove all logs?', 'wpp' ); ?>" href="<?php echo WPP_F::current_url( array( 'wpp_action' => 'clear', '_wpnonce' => wp_create_nonce('wpp_clear_log') ) ); ?>"><?php _e( 'Clear', 'wpp' ); ?></a></h2>

  <table class="wpp_log wpp_clean wpp_tight">
    <thead>
      <tr>
        <th style="width:40px;"><?php _e( 'ID', 'wpp' ); ?></th>
        <th style="width:100px;"><?php _e( 'Feature', 'wpp' ); ?></th>
        <th style=""><?php _e( 'Message', 'wpp' ); ?></th>
        <th style="width:120px;"><?php _e( 'Action', 'wpp' ); ?></th>
        <th style="width:80px;"><?php _e( 'Time', 'wpp' ); ?></th>
      </tr>
    </thead>
    <tbody></tbody>
    <tfoot>
    </tfoot>
  </table>

  <script type="text/javascript">

    jQuery( document ).ready( function() {
      wpp.render_log({
        columns: [ 'id', 'feature', 'message', 'action', 'time' ]
      });
    });

  </script>

</div>



