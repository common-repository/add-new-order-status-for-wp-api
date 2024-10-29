<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'RCH_WC_New_Order_Statuses_Tool' ) ) :

	/**
	 * Custom Order Statuses Tool Class.
	 */
	class RCH_WC_New_Order_Statuses_Tool {
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'add_tool' ), PHP_INT_MAX );
		}

		public function add_tool() {
			add_submenu_page(
				'woocommerce',
				__( 'Add New Order Status', 'add-new-order-status-woocommerce-api' ),
				__( 'Add New Order Status', 'add-new-order-status-woocommerce-api' ),
				'manage_woocommerce',
				'rch-new-order-status',
				array( $this, 'create_new_statuses_tool' )
			);
		}

		public function maybe_execute_actions() {
			$result_message = '';
			if ( isset( $_POST['rch_add_new_status'], $_POST['new_status'], $_POST['new_status_label'], $_POST['new_status_icon_content'], $_POST['new_status_icon_color'], $_POST['new_status_text_color'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$result_message = $this->add_new_status(
					sanitize_key( $_POST['new_status'] ), // phpcs:ignore WordPress.Security.NonceVerification
					sanitize_text_field( $_POST['new_status_label'] ), // phpcs:ignore WordPress.Security.NonceVerification
					sanitize_text_field( $_POST['new_status_icon_content'] ), // phpcs:ignore WordPress.Security.NonceVerification
					sanitize_text_field( $_POST['new_status_icon_color'] ), // phpcs:ignore WordPress.Security.NonceVerification
					sanitize_text_field( $_POST['new_status_text_color'] ) // phpcs:ignore WordPress.Security.NonceVerification
				);
			} elseif ( isset( $_POST['rch_edit_new_status'], $_POST['new_status'], $_POST['new_status_label'], $_POST['new_status_icon_content'], $_POST['new_status_icon_color'], $_POST['new_status_text_color'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$result_message = $this->edit_new_status(
					sanitize_key( $_POST['new_status'] ), // phpcs:ignore WordPress.Security.NonceVerification
					sanitize_text_field( $_POST['new_status_label'] ), // phpcs:ignore WordPress.Security.NonceVerification
					sanitize_text_field( $_POST['new_status_icon_content'] ), // phpcs:ignore WordPress.Security.NonceVerification
					sanitize_text_field( $_POST['new_status_icon_color'] ), // phpcs:ignore WordPress.Security.NonceVerification
					sanitize_text_field( $_POST['new_status_text_color'] ) // phpcs:ignore WordPress.Security.NonceVerification
				);
			} elseif ( isset( $_GET['delete'] ) && ( '' !== $_GET['delete'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$result_message = $this->delete_new_status( sanitize_text_field( $_GET['delete'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			}
			echo wp_kses_post( $result_message );
		}

		public function get_status_table_html() {
			$table_data       = array();
			$table_data[]     = array(
				__( '<strong>Slug</strong>', 'add-new-order-status-woocommerce-api' ),
				__( '<strong>Label</strong>', 'add-new-order-status-woocommerce-api' ),
				__( '<strong>Icon Code</strong>', 'add-new-order-status-woocommerce-api' ),
				__( '<strong>Color</strong>', 'add-new-order-status-woocommerce-api' ),
				__( '<strong>Text Color</strong>', 'add-new-order-status-woocommerce-api' ),
				__( '<strong>Actions</strong>', 'add-new-order-status-woocommerce-api' ),
			);
			$statuses         = function_exists( 'wc_get_order_statuses' ) ? wc_get_order_statuses() : array();
			$default_statuses = $this->get_default_order_statuses();
			$plugin_statuses  = rch_get_new_order_statuses();
			foreach ( $statuses as $status => $status_name ) {
				if ( array_key_exists( $status, $default_statuses ) || ! array_key_exists( $status, $plugin_statuses ) ) {
					$icon_and_actions = array( '', '', '', '' );
				} else {
					$icon_data = get_option( 'rch_orders_new_status_icon_data_' . substr( $status, 3 ), '' );
					if ( '' !== $icon_data ) {
						$content    = $icon_data['content'];
						$color      = $icon_data['color'];
						$text_color = ( isset( $icon_data['text_color'] ) ? $icon_data['text_color'] : '#000000' );
					} else {
						$content    = 'e011';
						$color      = '#999999';
						$text_color = '#000000';
					}
					$fallback_status_without_wc_prefix  = get_option( 'rch_orders_new_statuses_fallback_delete_status', 'on-hold' );
					$delete_button_ending               = ' href="' . add_query_arg( 'delete', $status, remove_query_arg( array( 'edit', 'fallback' ) ) ) .
					'" onclick="return confirm(\'' . __( 'Are you sure?', 'add-new-order-status-woocommerce-api' ) . '\')">';
					$delete_with_fallback_button_ending = ( substr( $status, 3 ) !== $fallback_status_without_wc_prefix ?
					' href="' . add_query_arg(
						array(
							'delete'   => $status,
							'fallback' => 'yes',
						),
						remove_query_arg( 'edit' )
					) .
					'" onclick="return confirm(\'' . __( 'Are you sure?', 'add-new-order-status-woocommerce-api' ) . '\')" title="' .
					sprintf(
						// translators: New status.
						__( 'Status for orders with this status will be changed to \'%s\'.' ),
						$this->get_status_title( 'wc-' . $fallback_status_without_wc_prefix )
					)
					. '">'
					:
					' disabled title="' .
					__( 'This status can not be deleted as it\'s set to be the fallback status. Change \'Fallback Delete Order Status\' to some other value in plugin\'s settings to delete this status.', 'add-new-order-status-woocommerce-api' )
					. '">'
					);
					$edit_button_ending          = ' href="' . add_query_arg( 'edit', $status, remove_query_arg( array( 'delete', 'fallback' ) ) ) . '">';
					$delete_button               = '<a class="button-primary"' . $delete_button_ending . __( 'Delete', 'add-new-order-status-woocommerce-api' ) . '</a>';
					$delete_with_fallback_button = '<a class="button-primary"' . $delete_with_fallback_button_ending . __( 'Fallback', 'add-new-order-status-woocommerce-api' ) . '</a>';
					$edit_button                 = '<a class="button-primary"' . $edit_button_ending . __( 'Edit', 'add-new-order-status-woocommerce-api' ) . '</a>';
					$icon_and_actions            = array(
						$content,
						'<input disabled type="color" value="' . $color . '">',
						'<input disabled type="color" value="' . $text_color . '">',
						$delete_button . ' ' . $delete_with_fallback_button . ' ' . $edit_button,
					);
				}
				$table_data[] = array_merge( array( esc_attr( $status ), esc_html( $status_name ) ), $icon_and_actions );
			}
			return '<h2>' . __( 'Status List', 'add-new-order-status-woocommerce-api' ) . '</h2>' .
			rch_get_table_html( $table_data, array( 'table_class' => 'wc_status_table widefat striped' ) ) .
			'<p style="font-style:italic;">* ' . sprintf(
				// translators: plugin settings link.
				__( '"Fallback" button will delete custom status and change status for every order with that status to "fallback status". Fallback status can be set in <a href="%s">plugin\'s general settings</a>. Please note - if you have large number of orders this may take longer.', 'add-new-order-status-woocommerce-api' ),
				admin_url( 'admin.php?page=wc-settings&tab=rch_wc_new_order_statuses' )
			) .
				'</p>';
		}

		public function get_actions_box_html() {
			$is_editing = ( isset( $_GET['edit'] ) ) ? true : false; // phpcs:ignore WordPress.Security.NonceVerification
			if ( $is_editing ) {
				$edit_slug             = sanitize_text_field( $_GET['edit'] ); // phpcs:ignore WordPress.Security.NonceVerification
				$new_order_statuses = rch_get_new_order_statuses();
				$edit_label            = isset( $new_order_statuses[ $edit_slug ] ) ? $new_order_statuses[ $edit_slug ] : '';
				$edit_icon_data        = get_option( 'rch_orders_new_status_icon_data_' . substr( $edit_slug, 3 ), '' );
				if ( '' !== $edit_icon_data ) {
					$edit_content    = $edit_icon_data['content'];
					$edit_color      = $edit_icon_data['color'];
					$edit_text_color = ( isset( $edit_icon_data['text_color'] ) ? $edit_icon_data['text_color'] : '#000000' );
				} else {
					$edit_content    = 'e011';
					$edit_color      = '#999999';
					$edit_text_color = '#000000';
				}
			}
			$title            = ( $is_editing ? __( 'Edit', 'add-new-order-status-woocommerce-api' ) : __( 'Add New ', 'add-new-order-status-woocommerce-api' ) );
			$slug_input       = '<input required type="text" name="new_status" maxlength="17" style="width:90%;"' .
			( $is_editing ? ' value="' . substr( $edit_slug, 3 ) . '" readonly' : '' ) . '>';
			$label_input      = '<input required type="text" name="new_status_label" style="width:90%;"' . ( $is_editing ? ' value="' . $edit_label . '"' : '' ) . '>';
			$icon_input       = '<input required type="text" name="new_status_icon_content" style="width:90%;" maxlength="4" pattern="[e]{1,1}[a-fA-F\d]{3,3}" value="' .
			( $is_editing ? $edit_content : 'e011' ) . '" >';
			$icon_color_input = '<input required type="color" name="new_status_icon_color" value="' . ( $is_editing ? $edit_color : '#999999' ) . '">';
			$text_color_input = '<input required type="color" name="new_status_text_color" value="' . ( $is_editing ? $edit_text_color : '#000000' ) . '">';
			$icon_desc        = sprintf(
				// translators: Icon page Link.
				'* ' . __( '<a target="_blank" href="%s">Click here for Icon list </a>', 'add-new-order-status-woocommerce-api' ),
				'https://rawgit.com/woothemes/woocommerce-icons/master/demo.html'
			);
			// translators: WC status prefix.
			$slug_desc       = '* ' . sprintf( __( 'Without %s prefix', 'add-new-order-status-woocommerce-api' ), '<code>wc-</code>' );
			$icon_input     .= "<br><em>$icon_desc</em>";
			$slug_input     .= "<br><em>$slug_desc</em>";
			$add_edit_button = '<input class="button-primary" type="submit" ' .
				'name="' . ( $is_editing ? 'rch_edit_new_status' : 'rch_add_new_status' ) . '" ' .
				'value="' . ( $is_editing ? __( 'Edit custom status', 'add-new-order-status-woocommerce-api' ) : __( 'Submit', 'add-new-order-status-woocommerce-api' ) ) .
				'">';
			$clear_button    = ( $is_editing ?
				'<a href="' . remove_query_arg( array( 'delete', 'fallback', 'edit' ) ) . '">' . __( 'Back', 'add-new-order-status-woocommerce-api' ) . '</a>' : '' );

			$table_data = array(
				array( __( '<strong>Slug</strong>', 'add-new-order-status-woocommerce-api' ), $slug_input ),
				array( __( '<strong>Label</strong>', 'add-new-order-status-woocommerce-api' ), $label_input ),
				array( __( '<strong>Icon Code</strong>', 'add-new-order-status-woocommerce-api' ), $icon_input ),
				array( __( '<strong>Color</strong>', 'add-new-order-status-woocommerce-api' ), $icon_color_input ),
				array( __( '<strong>Text Color</strong>', 'add-new-order-status-woocommerce-api' ), $text_color_input ),
				array( $add_edit_button, '' ),
			);
			if ( '' !== $clear_button ) {
				$table_data[] = array( $clear_button, '' );
			}
			return '<form method="post" action="' . remove_query_arg( array( 'delete', 'fallback' ) ) . '">' .
			'<h2>' . $title . ' ' . __( 'Status', 'add-new-order-status-woocommerce-api' ) . '</h2>' .
			rch_get_table_html(
				$table_data,
				array(
					'table_style'        => 'width:50%;min-width:290px;',
					'table_class'        => 'widefat striped',
					'table_heading_type' => 'vertical',
				)
			) .
			'</form>';
		}

		/**
		 * Create_new_statuses_tool.
		 *
		 * @version 1.4.0
		 * @since   1.0.0
		 */
		public function create_new_statuses_tool() {
			$html = '<table>';
			$html.='<tr>';
			?>
			<div class="wrap">
			<?php
			$this->maybe_execute_actions();
			$html .= '<td valign="top">'.$this->get_actions_box_html().'</td>';
			$html .= '<td>'.$this->get_status_table_html().'</td>';
			$html.='</tr></table>';
			echo $html;
			?>
			</div>

			<?php
		}

		public function add_new_status( $new_status, $new_status_label, $new_status_icon_content, $new_status_icon_color, $new_status_text_color ) {

			// Checking function arguments.
			if ( '' === $new_status ) {
				return '<div class="error"><p>' . __( 'Status slug is empty. Status was not added!', 'add-new-order-status-woocommerce-api' ) . '</p></div>';
			} else {
				global $wpdb;
				$terms_list = $wpdb->get_col( 'SELECT DISTINCT slug FROM `' . $wpdb->prefix . 'terms`' ); //phpcs:ignore
				if ( is_array( $terms_list ) && in_array( $new_status, $terms_list, true ) ) {
					return '<div class="error"><p>' . __( 'Status slug is already present. Please use another slug name.', 'add-new-order-status-woocommerce-api' ) . '</p></div>';
				}
			}
			if ( strlen( $new_status ) > 17 ) {
				return '<div class="error"><p>' . __( 'The length of status slug must be 17 or less characters. Status was not added!', 'add-new-order-status-woocommerce-api' ) . '</p></div>';
			}
			if ( ! isset( $new_status_label ) || '' === $new_status_label ) {
				return '<div class="error"><p>' . __( 'Status label is empty. Status was not added!', 'add-new-order-status-woocommerce-api' ) . '</p></div>';
			}

			// Checking status.
			$statuses_updated = rch_get_new_order_statuses();
			$new_key          = 'wc-' . $new_status;
			if ( isset( $statuses_updated[ $new_key ] ) ) {
				return '<div class="error"><p>' . __( 'Duplicate slug. Status was not added!', 'add-new-order-status-woocommerce-api' ) . '</p></div>';
			}
			$default_statuses = $this->get_default_order_statuses();
			if ( isset( $default_statuses[ $new_key ] ) ) {
				return '<div class="error"><p>' . __( 'Duplicate slug (default WooCommerce status). Status was not added!', 'add-new-order-status-woocommerce-api' ) . '</p></div>';
			}
			$statuses_updated[ $new_key ] = $new_status_label;

			// Adding custom status.
			$result = update_option( 'rch_orders_new_statuses_array', $statuses_updated );
			$result = update_option(
				'rch_orders_new_status_icon_data_' . $new_status,
				array(
					'content'    => $new_status_icon_content,
					'color'      => $new_status_icon_color,
					'text_color' => $new_status_text_color,
				)
			);
			return ( true === $result ) ?
				'<div class="updated"><p>' . __( 'New status has been successfully added!', 'add-new-order-status-woocommerce-api' ) . '</p></div>' :
				'<div class="error"><p>' . __( 'Status was not added!', 'add-new-order-status-woocommerce-api' ) . '</p></div>';
		}

		public function edit_new_status( $new_status, $new_status_label, $new_status_icon_content, $new_status_icon_color, $new_status_text_color ) {
			if ( '' === $new_status_label ) {
				$result_message = '<div class="error"><p>' . __( 'Status label is empty. Status was not edited!', 'add-new-order-status-woocommerce-api' ) . '</p></div>';
			} else {
				$statuses_updated                        = rch_get_new_order_statuses();
				$statuses_updated[ 'wc-' . $new_status ] = $new_status_label;
				$result                                  = update_option( 'rch_orders_new_statuses_array', $statuses_updated );
				$result_icon_data                        = update_option(
					'rch_orders_new_status_icon_data_' . $new_status,
					array(
						'content'    => $new_status_icon_content,
						'color'      => $new_status_icon_color,
						'text_color' => $new_status_text_color,
					)
				);
				if ( $result || $result_icon_data ) {
					$result_message = '<div class="updated"><p>' . __( 'Status has been successfully edited!', 'add-new-order-status-woocommerce-api' ) . '</p></div>';
				} else {
					$result_message = '<div class="error"><p>' . __( 'Status was not edited!', 'add-new-order-status-woocommerce-api' ) . '</p></div>';
				}
			}
			return $result_message;
		}

		public function delete_new_status( $delete_status ) {
			// Statuses data.
			$statuses_updated = rch_get_new_order_statuses();
			if ( isset( $statuses_updated[ $delete_status ] ) ) {
				// Fallback.
				$new_status_without_wc_prefix = get_option( 'rch_orders_new_statuses_fallback_delete_status', 'on-hold' );
				if ( isset( $_GET['fallback'] ) && 'rch_none' !== $new_status_without_wc_prefix ) { // phpcs:ignore WordPress.Security.NonceVerification
					$total_orders_changed = $this->change_orders_status( $delete_status, $new_status_without_wc_prefix );
				} else {
					$total_orders_changed = 0;
				}
				// Delete status.
				unset( $statuses_updated[ $delete_status ] );
				$result = update_option( 'rch_orders_new_statuses_array', $statuses_updated );
				// Delete icon data.
				$result_icon_data = delete_option( 'rch_orders_new_status_icon_data_' . substr( $delete_status, 3 ) );
				// Result message.
				if ( true === $result && true === $result_icon_data ) {
					$result_message = '<div class="updated"><p>' . __( 'Status has been successfully deleted.', 'add-new-order-status-woocommerce-api' ) . '</p></div>';
					if ( $total_orders_changed > 0 ) {
						// translators: number of orders for which status has been changed.
						$result_message .= '<div class="updated"><p>' . sprintf( __( 'Status has been changed for %d orders.', 'add-new-order-status-woocommerce-api' ), $total_orders_changed ) . '</p></div>';
					}
				} else {
					$result_message = '<div class="error"><p>' . __( 'Delete failed.', 'add-new-order-status-woocommerce-api' ) . '</p></div>';
				}
			} else {
				$result_message = '<div class="error"><p>' . __( 'Delete failed (status not found).', 'add-new-order-status-woocommerce-api' ) . '</p></div>';
			}
			return $result_message;
		}

		public function change_orders_status( $old_status, $new_status_without_wc_prefix ) {
			$total_orders_changed = 0;
			$offset               = 0;
			$block_size           = 1024;
			while ( true ) {
				$args_orders = array(
					'post_type'      => 'shop_order',
					'post_status'    => $old_status,
					'posts_per_page' => $block_size,
					'offset'         => $offset,
					'fields'         => 'ids',
				);
				$loop_orders = new WP_Query( $args_orders );
				if ( ! $loop_orders->have_posts() ) {
					break;
				}
				foreach ( $loop_orders->posts as $order_id ) {
					$order = wc_get_order( $order_id );
					$order->update_status( $new_status_without_wc_prefix );
					$total_orders_changed++;
				}
				$offset += $block_size;
			}
			return $total_orders_changed;
		}

		public function get_status_title( $slug ) {
			$statuses = $this->get_all_order_statuses();
			return ( isset( $statuses[ $slug ] ) ) ? $statuses[ $slug ] : '';
		}

		public function get_all_order_statuses() {
			return array_merge( $this->get_default_order_statuses(), rch_get_new_order_statuses() );
		}

		public function get_default_order_statuses() {
			return array(
				'wc-pending'    => _x( 'Pending payment', 'Order status', 'woocommerce' ),
				'wc-processing' => _x( 'Processing', 'Order status', 'woocommerce' ),
				'wc-on-hold'    => _x( 'On hold', 'Order status', 'woocommerce' ),
				'wc-completed'  => _x( 'Completed', 'Order status', 'woocommerce' ),
				'wc-cancelled'  => _x( 'Cancelled', 'Order status', 'woocommerce' ),
				'wc-refunded'   => _x( 'Refunded', 'Order status', 'woocommerce' ),
				'wc-failed'     => _x( 'Failed', 'Order status', 'woocommerce' ),
			);
		}

	}

endif;

return new RCH_WC_New_Order_Statuses_Tool();
