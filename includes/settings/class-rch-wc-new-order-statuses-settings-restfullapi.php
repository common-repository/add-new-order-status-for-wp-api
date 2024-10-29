<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'RCH_WC_New_Order_Statuses_Settings_RestFullApi' ) ) :

	/**
	 * RestFullApi Settings Section.
	 */
	class RCH_WC_New_Order_Statuses_Settings_RestFullApi extends RCH_WC_New_Order_Statuses_Settings_Section {
		public function __construct() {
			$this->id   = 'restfullapi';
			$this->desc = __( 'Rest Full Api', 'add-new-order-status-woocommerce-api' );
			parent::__construct();
		}

		public function get_settings() {
			return array(
				array(
					'title' => __( 'Rest Full Api Options', 'add-new-order-status-woocommerce-api' ),
					'type'  => 'title',
					'id'    => 'rch_orders_new_statuses_restfullapi_options',
				),
				array(
					'title'   => __( 'Enable Rest Full Api After Order', 'add-new-order-status-woocommerce-api' ),
					'desc'    => __( 'Enable', 'add-new-order-status-woocommerce-api' ),
					'id'      => 'rch_orders_new_statuses_restfullapi_opt',
					'default' => 'no',
					'type'    => 'checkbox',
				),
				'rch_api_url' => array(
						'title' 		=> __( 'API URL', 'rc-woocommerce-custom-payment-gateway-api' ),
						'type' 			=> 'text',
						'description' 	=> __( 'After complete payment call api.', 'rc-woocommerce-custom-payment-gateway-api' ),
						'default'		=> __( 'http://dummy.restapiexample.com/api/v1/create', 'rc-woocommerce-custom-payment-gateway-api' ),
						'desc_tip'		=> true,
						'id'    => 'rch_orders_new_statuses_restfullapi_url',
					),	
					'rch_api_key1' => array(
						'title' 		=> __( 'POST Data Key 1', 'rc-woocommerce-custom-payment-gateway-api' ),
						'type' 			=> 'text',
						'description' 	=> __( 'After complete payment post data for API.', 'rc-woocommerce-custom-payment-gateway-api' ),
						'default'		=> __( 'name', 'rc-woocommerce-custom-payment-gateway-api' ),
						'desc_tip'		=> true,
						'id'    => 'rch_orders_new_statuses_restfullapi_key1',
					),	
					'rch_api_value1' => array(
						'title' 		=> __( 'POST Data Value for key 1', 'rc-woocommerce-custom-payment-gateway-api' ),
						'type' 			=> 'text',
						'description' 	=> __( 'After complete payment post data for API.', 'rc-woocommerce-custom-payment-gateway-api' ),
						'default'		=> __( 'Ram', 'rc-woocommerce-custom-payment-gateway-api' ),
						'desc_tip'		=> true,
						'id'    => 'rch_orders_new_statuses_restfullapi_value1',
					),	
					'rch_api_key2' => array(
						'title' 		=> __( 'POST Data Key 2', 'rc-woocommerce-custom-payment-gateway-api' ),
						'type' 			=> 'text',
						'description' 	=> __( 'After complete payment post data for API.', 'rc-woocommerce-custom-payment-gateway-api' ),
						'default'		=> __( 'salary', 'rc-woocommerce-custom-payment-gateway-api' ),
						'desc_tip'		=> true,
						'id'    => 'rch_orders_new_statuses_restfullapi_key2',
					),	
					'rch_api_value2' => array(
						'title' 		=> __( 'POST Data Value for key 2', 'rc-woocommerce-custom-payment-gateway-api' ),
						'type' 			=> 'text',
						'description' 	=> __( 'After complete payment post data for API.', 'rc-woocommerce-custom-payment-gateway-api' ),
						'default'		=> __( '1000', 'rc-woocommerce-custom-payment-gateway-api' ),
						'desc_tip'		=> true,
						'id'    => 'rch_orders_new_statuses_restfullapi_value2',
					),	
					'rch_api_key3' => array(
						'title' 		=> __( 'POST Data Key 3', 'rc-woocommerce-custom-payment-gateway-api' ),
						'type' 			=> 'text',
						'description' 	=> __( 'After complete payment post data for API.', 'rc-woocommerce-custom-payment-gateway-api' ),
						'default'		=> __( 'age', 'rc-woocommerce-custom-payment-gateway-api' ),
						'desc_tip'		=> true,
						'id'    => 'rch_orders_new_statuses_restfullapi_key3',
					),	
					'rch_api_value3' => array(
						'title' 		=> __( 'POST Data Value for key 3', 'rc-woocommerce-custom-payment-gateway-api' ),
						'type' 			=> 'text',
						'description' 	=> __( 'After complete payment post data for API.', 'rc-woocommerce-custom-payment-gateway-api' ),
						'default'		=> __( '40', 'rc-woocommerce-custom-payment-gateway-api' ),
						'desc_tip'		=> true,
						'id'    => 'rch_orders_new_statuses_restfullapi_value3',
					),	
					array(
					'type' => 'sectionend',
					'id'   => 'rch_orders_new_statuses_restfullapi_options',
				),
			);
		}

	}

endif;

return new RCH_WC_New_Order_Statuses_Settings_RestFullApi();
