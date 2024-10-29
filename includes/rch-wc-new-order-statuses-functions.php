<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'rch_get_order_statuses' ) ) {
	function rch_get_order_statuses() {
		$result   = array();
		$statuses = function_exists( 'wc_get_order_statuses' ) ? wc_get_order_statuses() : array();
		foreach ( $statuses as $status => $status_name ) {
			$result[ substr( $status, 3 ) ] = $status_name;
		}
		return $result;
	}
}

if ( ! function_exists( 'rch_get_new_order_statuses' ) ) {
	function rch_get_new_order_statuses( $cut_prefix = false ) {
		$new_order_statuses = ( '' == get_option( 'rch_orders_new_statuses_array', array() ) ) ? array() : get_option( 'rch_orders_new_statuses_array', array() );
		if ( $cut_prefix ) {
			$new_order_statuses_no_prefix = array();
			foreach ( $new_order_statuses as $key => $value ) {
				$new_order_statuses_no_prefix[ substr( $key, 3 ) ] = $value;
			}
			$new_order_statuses = $new_order_statuses_no_prefix;
		}
		return $new_order_statuses;
	}
}

if ( ! function_exists( 'rch_get_table_html' ) ) {
	function rch_get_table_html( $data, $args = array() ) {
		$args       = array_merge(
			array(
				'table_class'        => '',
				'table_style'        => '',
				'row_styles'         => '',
				'table_heading_type' => 'horizontal',
				'columns_classes'    => array(),
				'columns_styles'     => array(),
			),
			$args
		);
		$row_styles = ( '' == $args['row_styles'] ? '' : ' style="' . $args['row_styles'] . '"' );
		$html       = '';
		$html      .= '<table' .
			( '' == $args['table_class'] ? '' : ' class="' . $args['table_class'] . '"' ) .
			( '' == $args['table_style'] ? '' : ' style="' . $args['table_style'] . '"' ) . '>';
		$html      .= '<tbody>';
		foreach ( $data as $row_number => $row ) {
			$html .= '<tr' . $row_styles . '>';
			foreach ( $row as $column_number => $value ) {
				$th_or_td = ( ( 0 === $row_number && 'horizontal' === $args['table_heading_type'] ) || ( 0 === $column_number && 'vertical' === $args['table_heading_type'] ) ? 'th' : 'td' );
				$html    .= '<' . $th_or_td .
					( ! empty( $args['columns_classes'][ $column_number ] ) ? ' class="' . $args['columns_classes'][ $column_number ] . '"' : '' ) .
					( ! empty( $args['columns_styles'][ $column_number ] ) ? ' style="' . $args['columns_styles'][ $column_number ] . '"' : '' ) . '>';
				$html    .= $value;
				$html    .= '</' . $th_or_td . '>';
			}
			$html .= '</tr>';
		}
		$html .= '</tbody>';
		$html .= '</table>';
		return $html;
	}
}
