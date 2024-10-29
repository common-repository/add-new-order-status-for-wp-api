<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'RCH_WC_New_Order_Statuses_Settings_Emails' ) ) :

	/**
	 * Email Settings.
	 */
	class RCH_WC_New_Order_Statuses_Settings_Emails extends RCH_WC_New_Order_Statuses_Settings_Section {
		public function __construct() {
			$this->id   = 'emails';
			$this->desc = __( 'Emails', 'add-new-order-status-woocommerce-api' );
			parent::__construct();
		}
		public function get_settings() {
			$emails_replaced_values_desc = sprintf(
				// translators: Merge tags.
				__( 'Replaced values: %s.', 'add-new-order-status-woocommerce-api' ),
				'<code>' . implode( '</code>, <code>', array( '{order_id}', '{order_number}', '{order_date}', '{order_details}', '{first_name}', '{last_name}', '{site_title}', '{status_from}', '{status_to}' ) ) . '</code>'
			) . ' ' .
				__( 'You can also use shortcodes here.', 'add-new-order-status-woocommerce-api' );
			return array(
				array(
					'title' => __( 'Emails Options', 'add-new-order-status-woocommerce-api' ),
					'type'  => 'title',
					'id'    => 'rch_orders_new_statuses_emails_options',
				),
				array(
					'title'   => __( 'Enable Emails Options', 'add-new-order-status-woocommerce-api' ),
					'desc'    => __( 'Enable', 'add-new-order-status-woocommerce-api' ),
					'id'      => 'rch_orders_new_statuses_emails_opt',
					'default' => 'no',
					'type'    => 'checkbox',
				),
				array(
					'title'    => __( 'Statuses', 'add-new-order-status-woocommerce-api' ),
					'desc_tip' => __( 'Custom statuses to send emails. Leave blank to send emails on all custom statuses.', 'add-new-order-status-woocommerce-api' ),
					'id'       => 'rch_orders_new_statuses_emails_statuses',
					'default'  => array(),
					'type'     => 'multiselect',
					'class'    => 'chosen_select',
					'options'  => rch_get_new_order_statuses(),
				),
				array(
					'title'    => __( 'Email address', 'add-new-order-status-woocommerce-api' ),
					// translators: Comma seperated list of emails.
					'desc_tip' => sprintf( __( 'Comma separated list of emails. Leave blank to send emails to admin (%s).', 'add-new-order-status-woocommerce-api' ), get_option( 'admin_email' ) ),
					'desc'     => sprintf(
						// translators: Merge tags for customer & admin emails.
						__( 'Use %1$s to send email to the customer\'s billing email; %2$s to the admin\'s email.', 'add-new-order-status-woocommerce-api' ),
						'<code>{customer_email}</code>',
						'<code>{admin_email}</code>'
					),
					'id'       => 'rch_orders_new_statuses_emails_address',
					'default'  => '',
					'type'     => 'text',
					'css'      => 'width:100%',
				),
				array(
					'title'   => __( 'Email subject', 'add-new-order-status-woocommerce-api' ),
					'desc'    => str_replace( ', <code>{order_details}</code>, <code>{first_name}</code>, <code>{last_name}</code>', '', $emails_replaced_values_desc ),
					'id'      => 'rch_orders_new_statuses_emails_subject',
					'default' => sprintf(
						// translators: merge tags - order number, new status, order date.
						__( '[%1$s] Order #%2$s status changed to %3$s - %4$s', 'add-new-order-status-woocommerce-api' ),
						'{site_title}',
						'{order_number}',
						'{status_to}',
						'{order_date}'
					),
					'type'    => 'text',
					'css'     => 'width:100%',
				),
				array(
					'title'   => __( 'Email heading', 'add-new-order-status-woocommerce-api' ),
					'desc'    => str_replace( ', <code>{order_details}</code>, <code>{first_name}</code>, <code>{last_name}</code>', '', $emails_replaced_values_desc ),
					'id'      => 'rch_orders_new_statuses_emails_heading',
					// translators: Merge tags - new status.
					'default' => sprintf( __( 'Order status changed to %s', 'add-new-order-status-woocommerce-api' ), '{status_to}' ),
					'type'    => 'text',
					'css'     => 'width:100%',
				),
				array(
					'title'          => __( 'Email content', 'add-new-order-status-woocommerce-api' ),
					'desc'           => '<em>' . $emails_replaced_values_desc . '</em>',
					'id'             => 'rch_orders_new_statuses_emails_content',
					// translators: Merge tags - Order Number, old status, new status.
					'default'        => sprintf( __( 'Order #%1$s status changed from %2$s to %3$s', 'add-new-order-status-woocommerce-api' ), '{order_number}', '{status_from}', '{status_to}' ),
					'type'           => 'textarea',
					'css'            => 'width:100%;height:400px',
					'rch_wc_ocs_raw' => true,
				),
				array(
					'type' => 'sectionend',
					'id'   => 'rch_orders_new_statuses_emails_options',
				),
			);
		}

	}

endif;

return new RCH_WC_New_Order_Statuses_Settings_Emails();
