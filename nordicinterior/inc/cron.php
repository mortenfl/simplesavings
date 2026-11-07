<?php
/**
 * Nightly jobs:
 *  - recalc: _ni_clicks (last 30 days) and _ni_discount for sorting.
 *  - link check: HEAD/GET every affiliate URL in batches → ok|broken|unknown.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Schedule events.
 */
function ni_schedule_events() {
	if ( ! wp_next_scheduled( 'ni_nightly_recalc' ) ) {
		wp_schedule_event( strtotime( 'tomorrow 02:30' ), 'daily', 'ni_nightly_recalc' );
	}
	if ( ! wp_next_scheduled( 'ni_nightly_link_check' ) ) {
		wp_schedule_event( strtotime( 'tomorrow 03:15' ), 'daily', 'ni_nightly_link_check' );
	}
}
add_action( 'init', 'ni_schedule_events' );

/**
 * Unschedule on theme switch.
 */
function ni_unschedule_events() {
	wp_clear_scheduled_hook( 'ni_nightly_recalc' );
	wp_clear_scheduled_hook( 'ni_nightly_link_check' );
	wp_clear_scheduled_hook( 'ni_link_check_batch' );
}
add_action( 'switch_theme', 'ni_unschedule_events' );

/**
 * Recalculate sort meta for all products.
 *
 * @return int Number of products updated.
 */
function ni_recalc() {
	if ( ! ni_wc() ) {
		return 0;
	}
	$clicks = ni_clicks_per_product( 30 );
	$page   = 1;
	$count  = 0;
	do {
		$ids = get_posts(
			array(
				'post_type'      => 'product',
				'post_status'    => 'any',
				'posts_per_page' => 200, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page -- Nightly batch job.
				'paged'          => $page,
				'fields'         => 'ids',
				'orderby'        => 'ID',
				'order'          => 'ASC',
			)
		);
		foreach ( $ids as $id ) {
			$product = wc_get_product( $id );
			if ( ! $product ) {
				continue;
			}
			update_post_meta( $id, '_ni_clicks', (int) ( $clicks[ $id ] ?? 0 ) );
			update_post_meta( $id, '_ni_discount', ni_product_discount( $product ) );
			ni_record_price( $id );
			++$count;
		}
		++$page;
		$batch_size = count( $ids );
	} while ( 200 === $batch_size );

	delete_transient( 'ni_popular_searches' );
	update_option( 'ni_last_recalc', time(), false );
	do_action( 'ni_after_recalc', $count );
	return $count;
}
add_action( 'ni_nightly_recalc', 'ni_recalc' );

/**
 * Check one affiliate URL.
 *
 * @param string $url URL.
 * @return string ok|broken|unknown
 */
function ni_check_url( $url ) {
	if ( ! $url || ! wp_http_validate_url( $url ) ) {
		return $url ? 'broken' : 'unknown';
	}
	$args     = array(
		'timeout'     => 10,
		'redirection' => 6,
		'user-agent'  => 'Mozilla/5.0 (compatible; NordicInteriorLinkCheck/1.0; +' . home_url( '/' ) . ')',
	);
	$response = wp_safe_remote_head( $url, $args );
	$code     = is_wp_error( $response ) ? 0 : (int) wp_remote_retrieve_response_code( $response );
	// Many shops answer HEAD with 403/405 – retry with a light GET.
	if ( 0 === $code || 403 === $code || 405 === $code || 501 === $code ) {
		$args['limit_response_size'] = 2048;
		$response                    = wp_safe_remote_get( $url, $args );
		$code                        = is_wp_error( $response ) ? 0 : (int) wp_remote_retrieve_response_code( $response );
	}
	if ( $code >= 200 && $code < 400 ) {
		return 'ok';
	}
	if ( in_array( $code, array( 404, 410 ), true ) || ( $code >= 500 && $code < 600 ) ) {
		return 'broken';
	}
	return 'unknown';
}

/**
 * Check a product's link and store the result.
 *
 * @param int $product_id Product ID.
 * @return string
 */
function ni_check_product_link( $product_id ) {
	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		return 'unknown';
	}
	$status = ni_check_url( ni_product_url( $product ) );
	update_post_meta( $product_id, '_ni_link_status', $status );
	update_post_meta( $product_id, '_ni_link_checked', time() );
	return $status;
}

/**
 * Start the nightly link check: queue the first batch.
 */
function ni_start_link_check() {
	update_option( 'ni_link_check_offset', 0, false );
	wp_schedule_single_event( time() + 5, 'ni_link_check_batch' );
}
add_action( 'ni_nightly_link_check', 'ni_start_link_check' );

/**
 * Process one batch of 25 products, then queue the next.
 *
 * @param int $batch Batch size.
 * @return int Products checked.
 */
function ni_link_check_batch( $batch = 25 ) {
	if ( ! ni_wc() ) {
		return 0;
	}
	$offset = (int) get_option( 'ni_link_check_offset', 0 );
	$ids    = get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => $batch,
			'offset'         => $offset,
			'fields'         => 'ids',
			'orderby'        => 'ID',
			'order'          => 'ASC',
		)
	);
	foreach ( $ids as $id ) {
		ni_check_product_link( $id );
	}
	if ( count( $ids ) === $batch ) {
		update_option( 'ni_link_check_offset', $offset + $batch, false );
		wp_schedule_single_event( time() + 30, 'ni_link_check_batch' );
	} else {
		update_option( 'ni_link_check_offset', 0, false );
		update_option( 'ni_last_link_check', time(), false );
	}
	return count( $ids );
}
add_action( 'ni_link_check_batch', 'ni_link_check_batch', 10, 0 );

/**
 * Products with broken links.
 *
 * @param int $limit Limit.
 * @return int[]
 */
function ni_broken_link_products( $limit = 50 ) {
	return get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_key'       => '_ni_link_status', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Admin only.
			'meta_value'     => 'broken', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Admin only.
		)
	);
}

/**
 * Campaigns ending within N days.
 *
 * @param int $days Days.
 * @return WP_Post[]
 */
function ni_campaigns_ending_soon( $days = 7 ) {
	$now  = current_time( 'mysql' );
	$then = wp_date( 'Y-m-d H:i:s', time() + $days * DAY_IN_SECONDS );
	return get_posts(
		array(
			'post_type'      => 'ni_campaign',
			'post_status'    => 'publish',
			'posts_per_page' => 20,
			'no_found_rows'  => true,
			'meta_key'       => '_ni_end', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Admin only.
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Admin only.
				array(
					'key'     => '_ni_end',
					'value'   => array( $now, $then ),
					'compare' => 'BETWEEN',
				),
			),
		)
	);
}
