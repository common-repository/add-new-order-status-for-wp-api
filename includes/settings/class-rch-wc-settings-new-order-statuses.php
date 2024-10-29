<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'rch_WC_Settings_Custom_Order_Statuses' ) ) :

	/**
	 * Inherits the WC Settings Class.
	 */
	class rch_WC_Settings_Custom_Order_Statuses extends WC_Settings_Page {
		public function __construct() {
			$this->id    = 'rch_wc_new_order_statuses';
			$this->label = __( 'Add New Order Status', 'add-new-order-status-woocommerce-api' );
			parent::__construct();
			add_filter( 'woocommerce_admin_settings_sanitize_option', array( $this, 'maybe_unsanitize_option' ), PHP_INT_MAX, 3 );
		}
		public function maybe_unsanitize_option( $value, $option, $raw_value ) {
			return ( ! empty( $option['rch_wc_ocs_raw'] ) ? $raw_value : $value );
		}
		public function get_settings() {
			global $current_section;
			return array_merge(
				apply_filters( 'woocommerce_get_settings_' . $this->id . '_' . $current_section, array() ),
				array(
					array(
						'title' => __( 'Reset Settings', 'add-new-order-status-woocommerce-api' ),
						'type'  => 'title',
						'id'    => $this->id . '_' . $current_section . '_reset_options',
					),
					array(
						'title'   => __( 'Reset section settings', 'add-new-order-status-woocommerce-api' ),
						'desc'    => '<strong>' . __( 'Reset', 'add-new-order-status-woocommerce-api' ) . '</strong>',
						'id'      => $this->id . '_' . $current_section . '_reset',
						'default' => 'no',
						'type'    => 'checkbox',
					),
					array(
						'type' => 'sectionend',
						'id'   => $this->id . '_' . $current_section . '_reset_options',
					),
				)
			);
		}

		public function maybe_reset_settings() {
			global $current_section;
			if ( 'yes' === get_option( $this->id . '_' . $current_section . '_reset', 'no' ) ) {
				foreach ( $this->get_settings() as $value ) {
					if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
						delete_option( $value['id'] );
						$autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
						add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
					}
				}
			}
		}

		public function save() {
			parent::save();
			$this->maybe_reset_settings();
		}

	}

endif;

return new rch_WC_Settings_Custom_Order_Statuses();
