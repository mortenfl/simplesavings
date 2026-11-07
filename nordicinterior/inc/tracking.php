<?php
/**
 * Anonymous click tracking: custom tables, REST endpoint and statistics.
 *
 * No personal data is stored: no IP address, no user agent, no cookies.
 * Only product, placement, room, style, store, brand and a timestamp.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Create or upgrade tables.
 */
function ni_install_tables() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$charset = $wpdb->get_charset_collate();

	dbDelta(
		"CREATE TABLE {$wpdb->prefix}ni_clicks (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			object_id bigint(20) unsigned NOT NULL DEFAULT 0,
			type varchar(20) NOT NULL DEFAULT 'product',
			placement varchar(20) NOT NULL DEFAULT '',
			room varchar(100) NOT NULL DEFAULT '',
			style varchar(100) NOT NULL DEFAULT '',
			store_id bigint(20) unsigned NOT NULL DEFAULT 0,
			brand_id bigint(20) unsigned NOT NULL DEFAULT 0,
			created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY object_created (object_id,created),
			KEY created (created),
			KEY placement (placement)
		) $charset;"
	);

	dbDelta(
		"CREATE TABLE {$wpdb->prefix}ni_searches (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			term varchar(190) NOT NULL DEFAULT '',
			hits bigint(20) unsigned NOT NULL DEFAULT 0,
			results int(11) NOT NULL DEFAULT 0,
			last_seen datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			UNIQUE KEY term (term),
			KEY hits (hits)
		) $charset;"
	);

	update_option( 'ni_db_version', NI_DB_VERSION, false );
}
add_action( 'after_switch_theme', 'ni_install_tables' );

/**
 * Upgrade tables when the schema version changes.
 */
function ni_maybe_install_tables() {
	if ( get_option( 'ni_db_version' ) !== NI_DB_VERSION ) {
		ni_install_tables();
	}
}
add_action( 'admin_init', 'ni_maybe_install_tables' );

/**
 * Insert a click row.
 *
 * @param array $data Click data.
 * @return bool
 */
function ni_record_click( $data ) {
	global $wpdb;
	$data = wp_parse_args(
		$data,
		array(
			'object_id' => 0,
			'type'      => 'product',
			'placement' => 'card',
			'room'      => '',
			'style'     => '',
			'store_id'  => 0,
			'brand_id'  => 0,
			'created'   => '',
		)
	);
	if ( 'product' === $data['type'] && function_exists( 'wc_get_product' ) ) {
		$product = wc_get_product( $data['object_id'] );
		if ( ! $product ) {
			return false;
		}
		$store            = ni_product_store( $product );
		$brand            = ni_product_brand( $product );
		$data['store_id'] = $store ? $store->term_id : 0;
		$data['brand_id'] = $brand ? $brand->term_id : 0;
		// Fall back to the product's own room/style when the page has none.
		if ( '' === $data['room'] ) {
			$room         = ni_product_term( $product, 'ni_room' );
			$data['room'] = $room ? $room->slug : '';
		}
		if ( '' === $data['style'] ) {
			$style         = ni_product_term( $product, 'ni_style' );
			$data['style'] = $style ? $style->slug : '';
		}
	}
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table insert.
	$ok = $wpdb->insert(
		$wpdb->prefix . 'ni_clicks',
		array(
			'object_id' => absint( $data['object_id'] ),
			'type'      => sanitize_key( $data['type'] ),
			'placement' => sanitize_key( $data['placement'] ),
			'room'      => sanitize_title( $data['room'] ),
			'style'     => sanitize_title( $data['style'] ),
			'store_id'  => absint( $data['store_id'] ),
			'brand_id'  => absint( $data['brand_id'] ),
			'created'   => preg_match( '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', (string) $data['created'] ) ? $data['created'] : current_time( 'mysql' ),
		),
		array( '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%s' )
	);
	return (bool) $ok;
}

/**
 * REST routes.
 */
function ni_tracking_routes() {
	register_rest_route(
		'nordicinterior/v1',
		'/click',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'ni_rest_click',
			'permission_callback' => '__return_true', // Public, anonymous beacon. Validated and rate limited below.
			'args'                => array(
				'product'   => array(
					'type'              => 'integer',
					'required'          => true,
					'sanitize_callback' => 'absint',
				),
				'placement' => array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
					'default'           => 'card',
				),
				'room'      => array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_title',
					'default'           => '',
				),
				'style'     => array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_title',
					'default'           => '',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'ni_tracking_routes' );

/**
 * Handle a click beacon.
 *
 * Editors are recognised through the REST nonce that is printed for
 * logged-in users only (cookie authentication), bots by user agent.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function ni_rest_click( WP_REST_Request $request ) {
	if ( ni_ignore_current_visitor() ) {
		return new WP_REST_Response( array( 'counted' => false ), 202 );
	}

	$product_id = (int) $request->get_param( 'product' );
	if ( 'product' !== get_post_type( $product_id ) ) {
		return new WP_REST_Response( array( 'counted' => false ), 400 );
	}

	// Simple flood protection without storing personal data: a salted,
	// daily-rotating hash kept in object cache/transient for 10 seconds.
	$remote = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key    = 'ni_c_' . substr( hash_hmac( 'sha256', $remote . '|' . $product_id . '|' . gmdate( 'Ymd' ), wp_salt( 'nonce' ) ), 0, 20 );
	if ( get_transient( $key ) ) {
		return new WP_REST_Response( array( 'counted' => false ), 202 );
	}
	set_transient( $key, 1, 10 );

	$placement = (string) $request->get_param( 'placement' );
	$ok        = ni_record_click(
		array(
			'object_id' => $product_id,
			'placement' => array_key_exists( $placement, ni_placements() ) ? $placement : 'card',
			'room'      => (string) $request->get_param( 'room' ),
			'style'     => (string) $request->get_param( 'style' ),
		)
	);
	return new WP_REST_Response( array( 'counted' => $ok ), 202 );
}

/**
 * Email placement: links in newsletters can use ?ni_src=email on internal
 * product pages; the click on that page is then attributed to "email".
 * This filter exposes the value to track.js.
 *
 * @param array $context Context.
 * @return array
 */
function ni_tracking_email_source( $context ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only.
	if ( isset( $_GET['ni_src'] ) && 'email' === sanitize_key( wp_unslash( $_GET['ni_src'] ) ) ) {
		$context['source'] = 'email';
	}
	return $context;
}
add_filter( 'ni_tracking_context', 'ni_tracking_email_source' );

/*
 * -------------------------------------------------------------------------
 * Statistics
 * -------------------------------------------------------------------------
 */

/**
 * Click totals grouped by a column.
 *
 * @param string $group   object_id|placement|room|style|store_id|brand_id.
 * @param int    $days    Period in days.
 * @param int    $limit   Rows.
 * @return array<int, object{k:string, clicks:int}>
 */
function ni_click_stats( $group, $days = 30, $limit = 10 ) {
	global $wpdb;
	$allowed = array( 'object_id', 'placement', 'room', 'style', 'store_id', 'brand_id' );
	if ( ! in_array( $group, $allowed, true ) ) {
		return array();
	}
	$since = gmdate( 'Y-m-d H:i:s', (int) current_time( 'timestamp' ) - absint( $days ) * DAY_IN_SECONDS ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- Local time matches stored values.
	$table = $wpdb->prefix . 'ni_clicks';
	$cache = 'ni_stats_' . md5( $group . $days . $limit );
	$rows  = wp_cache_get( $cache, 'nordicinterior' );
	if ( false === $rows ) {
		// $group is whitelisted above; table name is built from the prefix.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT {$group} AS k, COUNT(*) AS clicks FROM {$table} WHERE type = 'product' AND created >= %s AND {$group} <> '' AND {$group} <> '0' GROUP BY {$group} ORDER BY clicks DESC LIMIT %d", $since, absint( $limit ) ) );
		wp_cache_set( $cache, $rows, 'nordicinterior', 5 * MINUTE_IN_SECONDS );
	}
	return is_array( $rows ) ? $rows : array();
}

/**
 * Total clicks and clicks per day for a period.
 *
 * @param int $days Days.
 * @return array{total:int, days:array<string,int>}
 */
function ni_click_totals( $days = 30 ) {
	global $wpdb;
	$since = gmdate( 'Y-m-d 00:00:00', (int) current_time( 'timestamp' ) - absint( $days ) * DAY_IN_SECONDS ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- Local time matches stored values.
	$table = $wpdb->prefix . 'ni_clicks';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$rows = $wpdb->get_results( $wpdb->prepare( "SELECT DATE(created) AS d, COUNT(*) AS c FROM {$table} WHERE created >= %s GROUP BY DATE(created) ORDER BY d ASC", $since ) );
	$out  = array(
		'total' => 0,
		'days'  => array(),
	);
	foreach ( (array) $rows as $row ) {
		$out['days'][ $row->d ] = (int) $row->c;
		$out['total']          += (int) $row->c;
	}
	return $out;
}

/**
 * Clicks per product in the last N days (used by the nightly recalc).
 *
 * @param int $days Days.
 * @return array<int,int> product_id => clicks
 */
function ni_clicks_per_product( $days = 30 ) {
	global $wpdb;
	$since = gmdate( 'Y-m-d H:i:s', (int) current_time( 'timestamp' ) - absint( $days ) * DAY_IN_SECONDS ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- Local time matches stored values.
	$table = $wpdb->prefix . 'ni_clicks';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$rows = $wpdb->get_results( $wpdb->prepare( "SELECT object_id, COUNT(*) AS c FROM {$table} WHERE type = 'product' AND created >= %s GROUP BY object_id", $since ) );
	$out  = array();
	foreach ( (array) $rows as $row ) {
		$out[ (int) $row->object_id ] = (int) $row->c;
	}
	return $out;
}

/**
 * Clicks per term slug for rooms (used for "Shoppa efter rum" source "klick").
 *
 * @param string $column room|style.
 * @param int    $days   Days.
 * @return array<string,int>
 */
function ni_clicks_per_term_slug( $column = 'room', $days = 30 ) {
	$out = array();
	foreach ( ni_click_stats( 'room' === $column ? 'room' : 'style', $days, 50 ) as $row ) {
		$out[ (string) $row->k ] = (int) $row->clicks;
	}
	return $out;
}
