<?php
/**
 * Plugin Name: Add New Order Status For WP API
 * Description: Add new order status to WooCommerce, Call restfull apis after change order status, Send email to customer after change order status, Check restfull apis response.
 * Version: 1.0.0
 * Author: rcodehub107
 * Text Domain: add-new-order-status-woocommerce-api
 * WC tested up to: 5.4.1
 * License: GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'RCH_WC_New_Order_Statuses' ) ) {
	include_once 'class-rch-wc-new-order-statuses.php';
}
