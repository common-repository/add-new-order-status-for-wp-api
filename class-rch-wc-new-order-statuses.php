<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Check if WooCommerce is active.
$plugin_name = 'woocommerce/woocommerce.php';
if (
	! in_array( $plugin_name, apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ), true ) &&
	! ( is_multisite() && array_key_exists( $plugin_name, get_site_option( 'active_sitewide_plugins', array() ) ) )
) {
	return;
}


if ( ! class_exists( 'RCH_WC_New_Order_Statuses' ) ) :
	final class RCH_WC_New_Order_Statuses {
		public $version = '1.0.0';
		protected static $instance = null;
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public function __construct() {

			// Include required files.
			$this->includes();

			// Admin.
			if ( is_admin() ) {
				add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );
				add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
				// Tool.
				require_once 'includes/class-rch-wc-new-order-statuses-tool.php';
				// Settings.
				require_once 'includes/settings/class-rch-wc-new-order-statuses-settings-section.php';
				$this->settings             = array();
				$this->settings['general']  = require_once 'includes/settings/class-rch-wc-new-order-statuses-settings-general.php';
				$this->settings['emails']   = require_once 'includes/settings/class-rch-wc-new-order-statuses-settings-emails.php';
				$this->settings['restfullapi'] = require_once 'includes/settings/class-rch-wc-new-order-statuses-settings-restfullapi.php';

				
			}
		}


		public function action_links( $links ) {
			$new_links   = array();
			$new_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=rch_wc_new_order_statuses' ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>';
			return array_merge( $new_links, $links );
		}

		public function includes() {
			// Functions.
			require_once 'includes/rch-wc-new-order-statuses-functions.php';
			// Core.
			require_once 'includes/class-rch-wc-new-order-statuses-core.php';
		}

		public function version_updated() {
			foreach ( $this->settings as $section ) {
				foreach ( $section->get_settings() as $value ) {
					if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
						$autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
						add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
					}
				}
			}

			// get the email send to address option as it needs to be updated.
			$email_send_to = get_option( 'rch_orders_new_statuses_emails_address', '' );
			if ( '' !== $email_send_to && in_array( $email_send_to, array( '%customer%', '%admin%', 'min%' ), true ) ) { // contains old values.
				switch ( $email_send_to ) {
					case 'min%':
					case '%admin%':
						update_option( 'rch_orders_new_statuses_emails_address', '{admin_email}' );
						break;
					case '%customer%':
						update_option( 'rch_orders_new_statuses_emails_address', '{customer_email}' );
						break;
					default:
						break;
				}
			}
		}

		public function add_woocommerce_settings_tab( $settings ) {
			$settings[] = require_once 'includes/settings/class-rch-wc-settings-new-order-statuses.php';
			return $settings;
		}

		public function plugin_url() {
			return untrailingslashit( plugin_dir_url( __FILE__ ) );
		}

		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}

	}

endif;

if ( ! function_exists( 'rch_wc_new_order_statuses' ) ) {
	function rch_wc_new_order_statuses() {
		return RCH_WC_New_Order_Statuses::instance();
	}
}

rch_wc_new_order_statuses();
