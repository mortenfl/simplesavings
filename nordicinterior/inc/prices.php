<?php
/**
 * Price history and "Prissänkt" (price dropped) badge.
 *
 * History is stored per product in the meta key _ni_price_history as a list
 * of [timestamp, price] pairs (latest last, max 60 entries). A new entry is
 * only added when the price actually changes, e.g. after a feed import.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Record the current price if it differs from the latest entry.
 *
 * @param int $product_id Product ID.
 */
function ni_record_price( $product_id ) {
	$product = function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : null;
	if ( ! $product ) {
		return;
	}
	$price = (float) $product->get_price();
	if ( $price <= 0 ) {
		return;
	}
	$history = ni_price_history( $product_id );
	$last    = end( $history );
	update_post_meta( $product_id, '_ni_price_checked', time() );
	if ( $last && abs( (float) $last[1] - $price ) < 0.01 ) {
		return;
	}
	$history[] = array( time(), $price );
	$history   = array_slice( $history, -60 );
	update_post_meta( $product_id, '_ni_price_history', $history );

	// Remember the previous price for quick "Prissänkt" checks and admin column.
	if ( $last ) {
		update_post_meta( $product_id, '_ni_prev_price', (float) $last[1] );
		update_post_meta( $product_id, '_ni_price_changed', time() );
	}
}
add_action( 'woocommerce_update_product', 'ni_record_price', 20 );
add_action( 'woocommerce_new_product', 'ni_record_price', 20 );
add_action( 'pmxi_saved_post', 'ni_record_price', 20 );

/**
 * Price history for a product.
 *
 * @param int $product_id Product ID.
 * @return array<int, array{0:int, 1:float}>
 */
function ni_price_history( $product_id ) {
	$history = get_post_meta( $product_id, '_ni_price_history', true );
	return is_array( $history ) ? array_values( $history ) : array();
}

/**
 * Has the price dropped since the previous update (within 30 days)?
 *
 * @param WC_Product $product Product.
 * @return float Drop in percent (0 when not dropped).
 */
function ni_price_drop( $product ) {
	$prev    = (float) $product->get_meta( '_ni_prev_price' );
	$changed = (int) $product->get_meta( '_ni_price_changed' );
	$price   = (float) $product->get_price();
	if ( $prev > 0 && $price > 0 && $price < $prev && $changed > time() - 30 * DAY_IN_SECONDS ) {
		return round( ( 1 - $price / $prev ) * 100, 1 );
	}
	return 0.0;
}

/**
 * "Pris senast uppdaterat" timestamp.
 *
 * @param WC_Product $product Product.
 * @return int
 */
function ni_price_updated( $product ) {
	$checked = (int) $product->get_meta( '_ni_price_checked' );
	if ( ! $checked ) {
		$modified = $product->get_date_modified();
		$checked  = $modified ? $modified->getTimestamp() : 0;
	}
	return $checked;
}

/**
 * Products with a recent price drop (admin dashboard).
 *
 * @param int $limit Limit.
 * @return int[]
 */
function ni_products_with_price_drop( $limit = 10 ) {
	$ids = get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => $limit * 3,
			'fields'         => 'ids',
			'meta_key'       => '_ni_price_changed', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Admin only.
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		)
	);
	$out = array();
	foreach ( $ids as $id ) {
		$product = wc_get_product( $id );
		if ( $product && ni_price_drop( $product ) > 0 ) {
			$out[] = (int) $id;
		}
		if ( count( $out ) >= $limit ) {
			break;
		}
	}
	return $out;
}
