<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'RCH_WC_New_Order_Statuses_Settings_Section' ) ) :

	/**
	 * Settings class.
	 */
	class RCH_WC_New_Order_Statuses_Settings_Section {
		public function __construct() {
			add_filter( 'woocommerce_get_sections_rch_wc_new_order_statuses', array( $this, 'settings_section' ) );
			add_filter( 'woocommerce_get_settings_rch_wc_new_order_statuses_' . $this->id, array( $this, 'get_settings' ), PHP_INT_MAX );
		}

		public function settings_section( $sections ) {
			$sections[ $this->id ] = $this->desc;
			return $sections;
		}

	}

endif;
