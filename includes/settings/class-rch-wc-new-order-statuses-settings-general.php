<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'RCH_WC_New_Order_Statuses_Settings_General' ) ) :

	/**
	 * General Settings.
	 */
	class RCH_WC_New_Order_Statuses_Settings_General extends RCH_WC_New_Order_Statuses_Settings_Section {
		public function __construct() {
			$this->id   = '';
			$this->desc = __( 'General', 'add-new-order-status-woocommerce-api' );
			parent::__construct();
		}

		public function get_settings() {
			return array(
				array(
					'title' => __( 'General Options', 'add-new-order-status-woocommerce-api' ),
					'type'  => 'title',
					'desc'  => sprintf(
						// translators: Link to the page to create custom statuses.
						__( 'Use %s to create, edit and delete custom statuses.', 'add-new-order-status-woocommerce-api' ),
						'<a href="' . admin_url( 'admin.php?page=rch-new-order-status' ) . '">' .
						__( 'custom order statuses tool', 'add-new-order-status-woocommerce-api' ) . '</a>'
					),
					'id'    => 'rch_orders_new_statuses_general_options',
				),
				array(
					'title'   => __( 'Add custom statuses to admin order bulk actions', 'add-new-order-status-woocommerce-api' ),
					'desc'    => __( 'Add', 'add-new-order-status-woocommerce-api' ),
					'id'      => 'rch_orders_new_statuses_add_to_bulk_actions',
					'default' => 'yes',
					'type'    => 'checkbox',
				),
				array(
					'title'   => __( 'Add custom statuses to admin reports', 'add-new-order-status-woocommerce-api' ),
					'desc'    => __( 'Add', 'add-new-order-status-woocommerce-api' ),
					'id'      => 'rch_orders_new_statuses_add_to_reports',
					'default' => 'yes',
					'type'    => 'checkbox',
				),
				array(
					'title'    => __( 'Default order status', 'add-new-order-status-woocommerce-api' ),
					'desc_tip' => __( 'You can change the default order status here. However some payment gateways may change this status immediately on order creation. E.g. BACS gateway will change status to On-hold.', 'add-new-order-status-woocommerce-api' ) . ' ' .
						__( 'Plugin must be enabled to add custom statuses to the list.', 'add-new-order-status-woocommerce-api' ),
					'id'       => 'rch_orders_new_statuses_default_status',
					'default'  => 'rch_disabled',
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'options'  => array_merge( array( 'rch_disabled' => __( 'No changes', 'add-new-order-status-woocommerce-api' ) ), rch_get_order_statuses() ),
				),
				array(
					'title'    => __( 'Default order status for BACS (Direct bank transfer) payment method', 'add-new-order-status-woocommerce-api' ),
					'desc_tip' => __( 'Plugin must be enabled to add custom statuses to the list.', 'add-new-order-status-woocommerce-api' ),
					'id'       => 'rch_orders_new_statuses_default_status_bacs',
					'default'  => 'rch_disabled',
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'options'  => array_merge( array( 'rch_disabled' => __( 'No changes', 'add-new-order-status-woocommerce-api' ) ), rch_get_order_statuses() ),
				),
				array(
					'title'    => __( 'Default order status for COD (Cash on delivery) payment method', 'add-new-order-status-woocommerce-api' ),
					'desc_tip' => __( 'Plugin must be enabled to add custom statuses to the list.', 'add-new-order-status-woocommerce-api' ),
					'id'       => 'rch_orders_new_statuses_default_status_cod',
					'default'  => 'rch_disabled',
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'options'  => array_merge( array( 'rch_disabled' => __( 'No changes', 'add-new-order-status-woocommerce-api' ) ), rch_get_order_statuses() ),
				),
				array(
					'title'    => __( 'Fallback delete order status', 'add-new-order-status-woocommerce-api' ),
					'desc_tip' => __( 'When you delete some custom status with "Custom Order Statuses Tool", all orders with that status will be updated to this fallback status. Please note that all fallback status triggers (email etc.) will be activated.', 'add-new-order-status-woocommerce-api' ),
					'id'       => 'rch_orders_new_statuses_fallback_delete_status',
					'default'  => 'on-hold',
					'type'     => 'select',
					'class'    => 'wc-enhanced-select',
					'options'  => array_merge( rch_get_order_statuses(), array( 'rch_none' => __( 'No fallback', 'add-new-order-status-woocommerce-api' ) ) ),
				),
				array(
					'type' => 'sectionend',
					'id'   => 'rch_orders_new_statuses_general_options',
				),
			);
		}

	}

endif;

return new RCH_WC_New_Order_Statuses_Settings_General();
