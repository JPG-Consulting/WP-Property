<?php
/**
 * Invoice List Table class.
 *
 * @package WP-Invoice
 * @since 3.0
 * @access private
 */

require_once(WPP_Path . 'core/class_list_table.php');

class WPP_Object_List_Table extends WPP_List_Table {

  function __construct($args = '') {
    $args = wp_parse_args( $args, array(
      'plural' => '',
      'iColumns' => 3,
      'per_page' => 20,
      'iDisplayStart' => 0,
      'ajax_action' => 'wpp_ajax_list_table',
      'current_screen' => '',
      'table_scope' => 'wpp_overview',
      'singular' => '',
      'ajax' => false
    ) );

    parent::__construct($args);
  }

  /**
   * Get a list of sortable columns.
   *
   * @since 3.1.0
   * @access protected
   *
   * @return array
   */
  function get_sortable_columns() {
    global $wp_properties;

    return array();
  }

  /**
   * Set Bulk Actions
   *
   * @since 3.1.0
   *
   * @return array
   */
  public function get_bulk_actions() {
    $actions = array();

    if(current_user_can('delete_wpp_property')) {
      $actions['untrash'] = __( 'Restore' );
      $actions['delete'] = __( 'Delete' );
    }

    return $actions;
  }

  /**
   * Generate HTML for a single row on the users.php admin panel.
   *
   */
  function single_row( $ID ) {
    global $post, $wp_properties;

    $ID = (int) $ID;

    $post = WPP_F::get_property($ID);

    $post = (object) $post;

    $title = _draft_or_post_title($post->ID);
    $post_type_object = get_post_type_object( $post->post_type );
    $can_edit_post = current_user_can( $post_type_object->cap->edit_post);
    $can_edit_post = apply_filters('wpp_list_table_can_edit_post', $can_edit_post);

    $result = "<tr id='object-{$ID}' class='wpp_parent_element'>";

    list( $columns, $hidden ) = $this->get_column_info();

    foreach ( $columns as $column => $column_display_name ) {

      $class = "class=\"".str_replace(array('::',':','.'),'-',$column)." column-".str_replace(array('::',':','.'),'-',$column)."\"";
      $style = '';

      if ( in_array( $column, $hidden ) ) {
        $style = ' style="display:none;"';
      }

      $attributes = "$class$style";

      $result .= "<td {$attributes}>";

      $r = "";

      switch($column) {

        /**
         * {}
         *
         */
        case 'cb':
          if ( $can_edit_post ) {
            $r .= '<input type="checkbox" name="post[]" value="'. get_the_ID() . '"/>';
          } else {
            $r .= '&nbsp;';
          }
        break;

        /**
         * {}
         *
         */
        case 'title':
          $attributes = 'class="post-title page-title column-title"' . $style;

          $r .= '<div class="wpp_listing_overview">';

          if( $post->featured_image ) {
            $overview_thumb_type = $wp_properties['configuration']['admin_ui']['overview_table_thumbnail_size'] ? $wp_properties['configuration']['admin_ui']['overview_table_thumbnail_size'] : 'thumbnail';
            if( WPP_F::image_sizes( $overview_thumb_type ) && $image_thumb_obj = wpp_get_image_link($post->featured_image, $overview_thumb_type, array( 'return' => 'array' ) ) ) {
              if( $image_thumb_obj && !is_wp_error( $image_thumb_obj ) ) {
                $r .= '<a class="wpp_listing_thumbnail wpp_fancybox" href="'.$post->images['large'].'" rel="overview_group"><img src="'.$image_thumb_obj['url'].'" class="wpp_thumbnail" style="width:'.$image_thumb_obj['width'].'px; height:' . $image_thumb_obj['height'] . 'px;" /></a>';
              }
            }
          }

          $r .= '<div class="wpp_listing_primary">';

            if ( $can_edit_post && $post->post_status != 'trash' && $post->post_status != 'archived' ) {
              $r .= '<a class="row-title" href="' . get_edit_post_link( $post->ID, true ) . '" title="' . esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;' ), $title ) ) . '">' . $title . '</a>';
            } else {
              $r .= $title;
            }

            $r .= $post->property_type ? '<span class="wpp_property_type"> ' . $wp_properties['property_types'][$post->property_type] . '</span>' : '';

            $actions = array();

            if ( $can_edit_post && 'trash' != $post->post_status && 'archived' != $post->post_status ) {
             $actions['edit'] = '<a href="' . get_edit_post_link( $post->ID, true ) . '" title="' . esc_attr( __( 'Edit this item' ) ) . '">' . __( 'Edit' ) . '</a>';
            }

            if ( current_user_can( $post_type_object->cap->delete_post, $post->ID ) ) {
              if ( 'trash' == $post->post_status ) {
                $actions['untrash'] = "<a title='" . esc_attr( __( 'Restore this item from the Trash' ) ) . "' href='" . wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $post->ID ) ), 'untrash-' . $post->post_type . '_' . $post->ID ) . "'>" . __( 'Restore' ) . "</a>";
              } elseif ( EMPTY_TRASH_DAYS && 'pending' != $post->post_status ) {
                $actions['trash'] = "<a class='submitdelete' title='" . esc_attr( __( 'Move this item to the Trash' ) ) . "' href='" . get_delete_post_link( $post->ID ) . "'>" . __( 'Trash' ) . "</a>";
              }

              if ( 'trash' == $post->post_status || !EMPTY_TRASH_DAYS ) {
                $actions['delete'] = "<a class='submitdelete permanently' title='" . esc_attr( __( 'Delete this item permanently' ) ) . "' href='" . get_delete_post_link( $post->ID, '', true ) . "'>" . __( 'Delete Permanently' ) . "</a>";
              }
            }

            if ( 'trash' != $post->post_status && 'archived' != $post->post_status ) {
              $actions['view'] = '<a target="_blank" href="' . get_permalink( $post->ID ) . '" title="' . esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), $title ) ) . '" rel="permalink">' . __( 'View' ) . '</a>';
            }

            if(current_user_can('manage_options')) {
              $actions['feature'] = '<a href="#" class="wpp_featured_toggle" data-post_id="' . $post->ID . '" data-wp_nonce="' . wp_create_nonce( 'wpp_make_featured_' . $post->ID ) . '">' . ( $post->featured ? __( 'Unfeature','wpp' ) : __( 'Feature','wpp' ) ) . '</a>';
            } else {
              $actions['feature'] =  $post->featured ? __( 'Featured', 'wpp' ) : '';
            }

            $r .= $this->row_actions( apply_filters( is_post_type_hierarchical( $post->post_type ) ? 'page_row_actions' : 'post_row_actions', $actions, $post ) );

          $r .= '</div>'; /* .wpp_listing_primary */

          $r .= '</div>'; /* .wpp_listing_overview */

        break;

        /**
         * {}
         *
         */
        case 'overview':

          $post = prepare_property_for_display($post);

          $overview_stats = $wp_properties['property_stats'];

          unset($overview_stats['phone_number']);

          $stat_count = 0;
          $hidden_count = 0;

          foreach($overview_stats as $stat => $label) {

            $values = $post->$stat;

            if(!is_array($values)) {
              $values = array($values);
            }

            foreach($values as $value) {

              $print_values = array();

              if(empty($value) || strlen($value) > 25) {
                continue;
              }

              $print_values[] = apply_filters("wpp_stat_filter_{$stat}", $value);

              $print_values = implode('<br />', $print_values);

              $stat_count++;

              if($stat_count > 5) {
                $stat_row_class = 'hidden wpp_overview_hidden_stats';
                $hidden_count++;
              }

              $display_stats[$stat] = '<li class="'.$stat_row_class.'"><span class="wpp_label">' . $label . ':</span> <span class="wpp_value">' . $print_values . '</span></li>';

            }

          }

          if(is_array($display_stats) && count($display_stats) > 0) {

            if($stat_count > 5) {
              $display_stats['toggle_advanced'] = '<li class="wpp_show_advanced" advanced_option_class="wpp_overview_hidden_stats">' . sprintf(__('Toggle %1s more.', 'wpp'), $hidden_count) . '</li>';
            }

            $r .= '<ul class="wpp_overview_column_stats wpp_something_advanced_wrapper">' . implode('', $display_stats) . '</ul>';
          }

        break;

        /**
         * Print our all terms associated with current listing
         *
         */
        case 'terms':

          foreach ( (array) get_the_taxonomies( $post, array( 'label' => 'blal' ) ) as $taxonomy => $data ) {
            $terms[] = $data;
          }

          if( $terms ) {
            $r .= '<ul><li>' . implode( '</li><li>' , $terms ) . '</li></ul>';
          }

        break;

        /**
         * {}
         *
         */
        default:

          $print_values = array();

          $value = $post->{$column};

          if(!is_array($value)) {
            $value = array($value);
          }

          foreach($value as $single_value) {
            $print_values[] = apply_filters("wpp_attribute_filter", $single_value, $column);
          }

          $print_values = implode('<br />', $print_values);

          $r .= $print_values;

        break;
      }

      //** Need to insert some sort of space in there to avoid DataTable error that occures when "null" is returned */
      $ajax_cells[] = $r;

      $result .= $r;
      $result .= "</td>";
    }

    $result .= '</tr>';

    //var_dump( $this->_args['ajax'] );

    if($this->_args['ajax']) {
      return $ajax_cells;
    }

    return $result;
  }



}