<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'RCH_WC_New_Order_Statuses_Core' ) ) :

	/**
	 * Core Functionality.
	 */
	class RCH_WC_New_Order_Statuses_Core {
		public function __construct() {

			// Filters priority.
			$filters_priority = get_option( 'rch_orders_new_statuses_filters_priority', 0 );
			if ( 0 === $filters_priority ) {
				$filters_priority = PHP_INT_MAX;
			}

			add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 );

			// Custom Status: Filter, Register, Icons.
			add_filter( 'wc_order_statuses', array( $this, 'add_new_statuses_to_filter' ), $filters_priority );
			add_action( 'init', array( $this, 'register_new_post_statuses' ) );
			add_action( 'admin_head', array( $this, 'hook_statuses_icons_css' ), 11 );

			// Default Status.
			if ( 'rch_disabled' !== get_option( 'rch_orders_new_statuses_default_status', 'rch_disabled' ) ) {
				add_filter( 'woocommerce_default_order_status', array( $this, 'set_default_order_status' ), $filters_priority );
			}
			if ( 'rch_disabled' !== get_option( 'rch_orders_new_statuses_default_status_bacs', 'rch_disabled' ) ) {
				add_filter( 'woocommerce_bacs_process_payment_order_status', array( $this, 'set_default_order_status_bacs' ), $filters_priority );
			}
			if ( 'rch_disabled' !== get_option( 'rch_orders_new_statuses_default_status_cod', 'rch_disabled' ) ) {
				add_filter( 'woocommerce_cod_process_payment_order_status', array( $this, 'set_default_order_status_cod' ), $filters_priority );
			}

			// Reports.
			if ( 'yes' === get_option( 'rch_orders_new_statuses_add_to_reports', 'yes' ) ) {
				add_filter( 'woocommerce_reports_order_statuses', array( $this, 'add_new_order_statuses_to_reports' ), $filters_priority );
			}

			// Bulk Actions.
			if ( 'yes' === get_option( 'rch_orders_new_statuses_add_to_bulk_actions', 'yes' ) ) {
				if ( version_compare( get_bloginfo( 'version' ), '4.7' ) >= 0 ) {
					add_filter( 'bulk_actions-edit-shop_order', array( $this, 'register_order_new_status_bulk_actions' ), $filters_priority );
				} else {
					add_action( 'admin_footer', array( $this, 'bulk_admin_footer' ), 11 );
				}
			}

			// Admin Order List Actions.
			if ( 'yes' === apply_filters( 'rch_orders_new_statuses', 'no', 'value_order_list_actions' ) ) {
				add_filter( 'woocommerce_admin_order_actions', array( $this, 'add_new_status_actions_buttons' ), $filters_priority, 2 );
				add_action( 'admin_head', array( $this, 'add_new_status_actions_buttons_css' ) );
			}

			// Column Colors.
			if ( 'yes' === apply_filters( 'rch_orders_new_statuses', 'no', 'value_column_colored' ) ) {
				add_action( 'admin_head', array( $this, 'add_new_status_column_css' ) );
			}

			// Order preview actions.
			if ( 'yes' === apply_filters( 'rch_orders_new_statuses', 'no', 'value_order_preview_actions' ) ) {
				add_filter( 'woocommerce_admin_order_preview_actions', array( $this, 'add_new_status_to_order_preview' ), PHP_INT_MAX, 2 );
			}

			// Editable orders.
			if ( 'yes' === apply_filters( 'rch_orders_new_statuses', 'no', 'value_is_editable' ) ) {
				add_filter( 'wc_order_is_editable', array( $this, 'add_new_order_statuses_to_order_editable' ), PHP_INT_MAX, 2 );
			}


			// Emails.
			add_action( 'woocommerce_order_status_changed', array( $this, 'send_email_on_order_status_changed' ), PHP_INT_MAX, 4 );

			// Rest full api.
			add_action( 'woocommerce_order_status_changed', array( $this, 'restfullapi_on_order_status_changed' ), PHP_INT_MAX, 4 );

		}

		public function get_new_order_statuses_actions( $_order ) {
			$status_actions        = array();
			$new_order_statuses = rch_get_new_order_statuses( true );
			foreach ( $new_order_statuses as $new_order_status => $label ) {
				if ( ! $_order->has_status( array( $new_order_status ) ) ) { // if order status is not $new_order_status.
					$status_actions[ $new_order_status ] = $label;
				}
			}
			return $status_actions;
		}

		public function get_new_order_statuses_action_url( $status, $order_id ) {
			return wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status=' . $status . '&order_id=' . $order_id ), 'woocommerce-mark-order-status' );
		}

		public function add_new_status_to_order_preview( $actions, $_order ) {
			$status_actions  = array();
			$_status_actions = $this->get_new_order_statuses_actions( $_order );
			if ( ! empty( $_status_actions ) ) {
				$order_id = ( version_compare( get_option( 'woocommerce_version', null ), '3.0.0', '<' ) ? $_order->id : $_order->get_id() );
				foreach ( $_status_actions as $new_order_status => $label ) {
					$status_actions[ $new_order_status ] = array(
						'url'    => $this->get_new_order_statuses_action_url( $new_order_status, $order_id ),
						'name'   => $label,
						// translators: Custom Order status.
						'title'  => sprintf( __( 'Change order status to %s', 'add-new-order-status-woocommerce-api' ), $new_order_status ),
						'action' => $new_order_status,
					);
				}
			}
			if ( $status_actions ) {
				if ( ! empty( $actions['status']['actions'] ) && is_array( $actions['status']['actions'] ) ) {
					$actions['status']['actions'] = array_merge( $actions['status']['actions'], $status_actions );
				} else {
					$actions['status'] = array(
						'group'   => __( 'Change status: ', 'woocommerce' ),
						'actions' => $status_actions,
					);
				}
			}
			return $actions;
		}


	public static function plugin_row_meta( $links, $file ) {			
		if ( 'add-new-order-status-woocommerce-api/add-new-order-status-woocommerce-api.php'=== $file ) {
			$row_meta = array(
				'add'    => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=rch_wc_new_order_statuses' ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>',
				'docs'    => '<a href="' . admin_url( 'admin.php?page=rch-new-order-status' ) . '">' . __( 'Add New Status', 'woocommerce' ) . '</a>',
				
			);

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}

	public function restfullapi_on_order_status_changed($order_id, $status_from, $status_to, $order){
		$restfullapiOPt = get_option( 'rch_orders_new_statuses_restfullapi_opt');
		if($restfullapiOPt=='yes'){
			//Call API
			$rch_api_url 	= get_option('rch_orders_new_statuses_restfullapi_url');
			$rch_api_key1 	= get_option('rch_orders_new_statuses_restfullapi_key1');
			$rch_api_key2 	= get_option('rch_orders_new_statuses_restfullapi_key2');
			$rch_api_key3 	= get_option('rch_orders_new_statuses_restfullapi_key3');

			$rch_api_value1 = get_option('rch_orders_new_statuses_restfullapi_value1');
			$rch_api_value2 = get_option('rch_orders_new_statuses_restfullapi_value2');
			$rch_api_value3 = get_option('rch_orders_new_statuses_restfullapi_value3');

			//$d = $rch_api_url." = ".$rch_api_key1." = ".$rch_api_key2." = ".$rch_api_key3." = ".$rch_api_value1." = ".$rch_api_value2." = ".$rch_api_value3;

			if($rch_api_url!='' && $rch_api_url!='http://dummy.restapiexample.com/api/v1/create'){
			//if($rch_api_url!=''){
				// User data to send using HTTP POST method 
				$post_data  = array();

				$post_data[$rch_api_key1] 	= $rch_api_value1; 
				$post_data[$rch_api_key2] 	= $rch_api_value2; 
				$post_data[$rch_api_key3] 	= $rch_api_value3; 

				$args = array(
				    'body' 			=> json_encode($post_data),
				    'timeout' 		=> '5',
				    'redirection' 	=> '5',
				    'httpversion' 	=> '1.0',
				    'blocking' 		=> true,
				    'headers' 		=> array(),
				    'cookies' 		=> array()
				);
	            
	            //Post Request for api
				$response 		= wp_remote_post( $rch_api_url, $args );
				$response_body 	= $response['body'];

				// Create post object
				$my_post = array(
				'post_title'    => 'New Order Status Payment API Response',
				'post_content'  => $response_body,
				'post_status'   => 'publish',
				'post_author'   => 1
				);
				// Insert the post into the database
				wp_insert_post( $my_post );
			}
		}	
	}


		public function send_email_on_order_status_changed( $order_id, $status_from, $status_to, $order ) {

			$sendEmailOPt = get_option( 'rch_orders_new_statuses_emails_opt');
			if($sendEmailOPt=='yes'){

				$rch_orders_new_statuses_array = rch_get_new_order_statuses();

				$emails_statuses = get_option( 'rch_orders_new_statuses_emails_statuses', array() );
				if ( in_array( 'wc-' . $status_to, $emails_statuses, true ) || ( empty( $emails_statuses ) && in_array( 'wc-' . $status_to, array_keys( $rch_orders_new_statuses_array ), true ) ) ) {
					// Options.
					$email_address = get_option( 'rch_orders_new_statuses_emails_address', '' );
					$email_subject = get_option(
						'rch_orders_new_statuses_emails_subject',
						// translators: WC Order Number, New Status & Date on which the order was placed.
						sprintf( __( '[%1$s] Order #%2$s status changed to %3$s - %4$s', 'add-new-order-status-woocommerce-api' ), '{site_title}', '{order_number}', '{status_to}', '{order_date}' )
					);
					$email_heading = get_option(
						'rch_orders_new_statuses_emails_heading',
						// translators: New Order status.
						sprintf( __( 'Order status changed to %s', 'add-new-order-status-woocommerce-api' ), '{status_to}' )
					);
					$email_content = get_option(
						'rch_orders_new_statuses_emails_content',
						// translators: WC Order Number, Old status, new status.
						sprintf( __( 'Order #%1$s status changed from %2$s to %3$s', 'add-new-order-status-woocommerce-api' ), '{order_number}', '{status_from}', '{status_to}' )
					);

					$woo_statuses        = wc_get_order_statuses();
					$replace_status_from = isset( $rch_orders_new_statuses_array[ 'wc-' . $status_from ] ) ? $rch_orders_new_statuses_array[ 'wc-' . $status_from ] : $woo_statuses[ 'wc-' . $status_from ];
					$replace_status_to   = isset( $rch_orders_new_statuses_array[ 'wc-' . $status_to ] ) ? $rch_orders_new_statuses_array[ 'wc-' . $status_to ] : $woo_statuses[ 'wc-' . $status_to ];

					// Replaced values.
					$replaced_values       = array(
						'{order_id}'      => $order_id,
						'{order_number}'  => $order->get_order_number(),
						'{order_date}'    => date( get_option( 'date_format' ), strtotime( $order->get_date_created() ) ), // phpcs:ignore
						'{order_details}' => ( false !== strpos( $email_content, '{order_details}' ) ? $this->get_wc_order_details_template( $order ) : '' ),
						'{site_title}'    => wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ),
						'{status_from}'   => $replace_status_from,
						'{status_to}'     => $replace_status_to,
						'{first_name}'    => $order->get_billing_first_name(),
						'{last_name}'     => $order->get_billing_last_name(),
					);
					$email_replaced_values = array(
						'{customer_email}' => $order->get_billing_email(),
						'{admin_email}'    => get_option( 'admin_email' ),
					);
					// Final processing.
					$email_address = ( '' === $email_address ? get_option( 'admin_email' ) : str_replace( array_keys( $email_replaced_values ), $email_replaced_values, $email_address ) );
					$email_subject = do_shortcode( str_replace( array_keys( $replaced_values ), $replaced_values, $email_subject ) );
					$email_heading = do_shortcode( str_replace( array_keys( $replaced_values ), $replaced_values, $email_heading ) );
					$email_content = do_shortcode( str_replace( array_keys( $replaced_values ), $replaced_values, $this->wrap_in_wc_email_template( $email_content, $email_heading ) ) );
					// Send mail.
					wc_mail( $email_address, $email_subject, $email_content );
				}
			}
		}

		public function get_wc_order_details_template( $order ) {
			ob_start();
			wc_get_template(
				'emails/email-order-details.php',
				array(
					'order'         => $order,
					'sent_to_admin' => false,
					'plain_text'    => false,
					'email'         => '',
				)
			);
			return ob_get_clean();
		}

		public function wrap_in_wc_email_template( $content, $email_heading = '' ) {
			return $this->get_wc_email_part( 'header', $email_heading ) . $content . $this->get_wc_email_part( 'footer' );
		}

		public function get_wc_email_part( $part, $email_heading = '' ) {
			ob_start();
			switch ( $part ) {
				case 'header':
					wc_get_template( 'emails/email-header.php', array( 'email_heading' => $email_heading ) );
					break;
				case 'footer':
					wc_get_template( 'emails/email-footer.php' );
					break;
			}
			return ob_get_clean();
		}

		public function add_new_order_statuses_to_order_editable( $is_editable, $_order ) {
			return ( in_array( 'wc-' . $_order->get_status(), array_keys( rch_get_new_order_statuses() ), true ) ? true : $is_editable );
		}

		public function add_new_status_column_css() {
			$statuses = rch_get_new_order_statuses();
			foreach ( $statuses as $slug => $label ) {
				$new_order_status = substr( $slug, 3 );
				$icon_data           = get_option( 'rch_orders_new_status_icon_data_' . $new_order_status, '' );
				if ( '' !== $icon ) {
					$color      = $icon_data['color'];
					$text_color = ( isset( $icon_data['text_color'] ) ? $icon_data['text_color'] : '#000000' );
				} else {
					$color      = '#999999';
					$text_color = '#000000';
				}
				echo '<style>mark.order-status.status-' . esc_attr( $new_order_status ) . ' { color: ' . esc_attr( $text_color ) . '; background-color: ' . esc_attr( $color ) . ' }</style>';
			}
		}

		public function add_new_status_actions_buttons( $actions, $_order ) {
			$statuses = rch_get_new_order_statuses();
			foreach ( $statuses as $slug => $label ) {
				$new_order_status = substr( $slug, 3 );
				if ( ! $_order->has_status( array( $new_order_status ) ) ) { // if order status is not $new_order_status.
					$_order_id                       = ( version_compare( get_option( 'woocommerce_version', null ), '3.0.0', '<' ) ? $_order->id : $_order->get_id() );
					$actions[ $new_order_status ] = array(
						'url'    => $this->get_new_order_statuses_action_url( $new_order_status, $_order_id ),
						'name'   => $label,
						'action' => 'view ' . $new_order_status, // setting "view" for proper button CSS.
					);
				}
			}
			return $actions;
		}

		public function add_new_status_actions_buttons_css() {
			$statuses = rch_get_new_order_statuses();
			foreach ( $statuses as $slug => $label ) {
				$new_order_status = substr( $slug, 3 );
				$icon_data           = get_option( 'rch_orders_new_status_icon_data_' . $new_order_status, '' );
				if ( '' !== $icon_data ) {
					$content = $icon_data['content'];
					$color   = $icon_data['color'];
				} else {
					$content = 'e011';
					$color   = '#999999';
				}
				$color_style = ( 'yes' === apply_filters( 'rch_orders_new_statuses', 'no', 'value_order_list_actions_colored' ) ) ? ' color: ' . $color . ' !important;' : '';
				echo '<style>.view.' . esc_attr( $new_order_status ) . '::after { font-family: WooCommerce !important;' . esc_attr( $color_style ) . ' content: "\\' . esc_attr( $content ) . '" !important; }</style>';
			}
		}

		public function add_new_order_statuses_to_reports( $order_statuses ) {
			if ( is_array( $order_statuses ) && in_array( 'completed', $order_statuses, true ) ) {
				return array_merge( $order_statuses, array_keys( rch_get_new_order_statuses( true ) ) );
			}
			return $order_statuses;
		}

		public function set_default_order_status() {
			return get_option( 'rch_orders_new_statuses_default_status', 'rch_disabled' );
		}

		public function set_default_order_status_bacs() {
			return get_option( 'rch_orders_new_statuses_default_status_bacs', 'rch_disabled' );
		}

			public function set_default_order_status_cod() {
			return get_option( 'rch_orders_new_statuses_default_status_cod', 'rch_disabled' );
		}

		public function register_new_post_statuses() {
			$rch_orders_new_statuses_array = rch_get_new_order_statuses();
			foreach ( $rch_orders_new_statuses_array as $slug => $label ) {
				register_post_status(
					$slug,
					array(
						'label'                     => $label,
						'public'                    => true,
						'exclude_from_search'       => false,
						'show_in_admin_all_list'    => true,
						'show_in_admin_status_list' => true,
						// translators: Count of orders with the custom status.
						'label_count'               => _n_noop( "$label <span class='count'>(%s)</span>", "$label <span class='count'>(%s)</span>" ), // phpcs:ignore
					)
				);
			}
		}

		public function add_new_statuses_to_filter( $order_statuses ) {
			$rch_orders_new_statuses_array = rch_get_new_order_statuses();
			$order_statuses                   = ( '' === $order_statuses ) ? array() : $order_statuses;
			return array_merge( $order_statuses, $rch_orders_new_statuses_array );
		}

		public function hook_statuses_icons_css() {
			$output   = '<style>';
			$statuses = rch_get_new_order_statuses();
			foreach ( $statuses as $status => $status_name ) {
				$icon_data = get_option( 'rch_orders_new_status_icon_data_' . substr( $status, 3 ), '' );
				if ( '' !== $icon_data ) {
					$content = $icon_data['content'];
					$color   = $icon_data['color'];
				} else {
					$content = 'e011';
					$color   = '#999999';
				}
				$output .= 'mark.' . substr( $status, 3 ) . '::after { content: "\\' . $content . '"; color: ' . $color . '; }';
				$output .= 'mark.' . substr( $status, 3 ) . ':after {font-family:WooCommerce;speak:none;font-weight:400;font-variant:normal;text-transform:none;line-height:1;-webkit-font-smoothing:antialiased;margin:0;text-indent:0;position:absolute;top:0;left:0;width:100%;height:100%;text-align:center}';
			}
			$output .= '</style>';
			echo wp_kses( $output, array( 'style' => array() ) );
		}

		public function register_order_new_status_bulk_actions( $bulk_actions ) {
			$new_order_statuses = rch_get_new_order_statuses( true );
			foreach ( $new_order_statuses as $slug => $label ) {
				// translators: New Status.
				$bulk_actions[ 'mark_' . $slug ] = sprintf( __( 'Change status to %s', 'add-new-order-status-woocommerce-api' ), $label );
			}
			return $bulk_actions;
		}


		public function bulk_admin_footer() {
			global $post_type;
			if ( 'shop_order' === $post_type ) {
				?><script type="text/javascript">
				<?php
				foreach ( rch_get_order_statuses() as $key => $order_status ) {
					if ( in_array( $key, array( 'processing', 'on-hold', 'completed' ), true ) ) {
						continue;
					}
					?>
				jQuery(function() {
					// translators: custom status.
					jQuery('<option>').val('mark_<?php echo esc_attr( $key ); ?>').text('<?php sprintf( __( 'Mark %s', 'add-new-order-status-woocommerce-api' ), esc_attr( $order_status ) ); ?>').appendTo('select[name="action"]');
					// translators: custom status.
					jQuery('<option>').val('mark_<?php echo esc_attr( $key ); ?>').text('<?php sprintf( __( 'Mark %s', 'add-new-order-status-woocommerce-api' ), esc_attr( $order_status ) ); ?>').appendTo('select[name="action2"]');
				});
					<?php
				}
				?>
			</script>
				<?php
			}
		}

	}

endif;

return new RCH_WC_New_Order_Statuses_Core();
