<?php
/**
 * Product listings: filters (taxonomies, colour, dimensions, price, sale),
 * sorting, facets and noindex for filtered URLs.
 *
 * Filters are plain GET parameters so every filtered state has a shareable
 * URL and works without JavaScript. filters.js fetches the same URL with
 * ?ni_partial=1 and swaps the result fragment (AJAX).
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Taxonomy filters: GET parameter => taxonomy.
 *
 * @return array<string, array{tax:string,label:string}>
 */
function ni_filter_taxonomies() {
	$filters = array(
		'f_kategori' => array(
			'tax'   => 'product_cat',
			'label' => __( 'Kategori', 'nordicinterior' ),
		),
		'f_rum'      => array(
			'tax'   => 'ni_room',
			'label' => __( 'Rum', 'nordicinterior' ),
		),
		'f_stil'     => array(
			'tax'   => 'ni_style',
			'label' => __( 'Stil', 'nordicinterior' ),
		),
		'f_farg'     => array(
			'tax'   => 'pa_farg',
			'label' => __( 'Färg', 'nordicinterior' ),
		),
		'f_material' => array(
			'tax'   => 'pa_material',
			'label' => __( 'Material', 'nordicinterior' ),
		),
		'f_marke'    => array(
			'tax'   => 'product_brand',
			'label' => __( 'Varumärke', 'nordicinterior' ),
		),
		'f_designer' => array(
			'tax'   => 'pa_designer',
			'label' => __( 'Designer', 'nordicinterior' ),
		),
		'f_butik'    => array(
			'tax'   => 'ni_store',
			'label' => __( 'Butik', 'nordicinterior' ),
		),
	);
	return array_filter(
		$filters,
		static function ( $f ) {
			return taxonomy_exists( $f['tax'] );
		}
	);
}

/**
 * Range filters: key => meta key.
 *
 * @return array<string, array{meta:string,label:string,unit:string}>
 */
function ni_filter_ranges() {
	return array(
		'pris'  => array(
			'meta'  => '_price',
			'label' => __( 'Pris', 'nordicinterior' ),
			'unit'  => 'kr',
		),
		'bredd' => array(
			'meta'  => '_width',
			'label' => __( 'Bredd', 'nordicinterior' ),
			'unit'  => 'cm',
		),
		'djup'  => array(
			'meta'  => '_length',
			'label' => __( 'Djup', 'nordicinterior' ),
			'unit'  => 'cm',
		),
		'hojd'  => array(
			'meta'  => '_height',
			'label' => __( 'Höjd', 'nordicinterior' ),
			'unit'  => 'cm',
		),
	);
}

/**
 * Sorting options.
 *
 * @return array<string,string>
 */
function ni_sort_options() {
	return array(
		'rekommenderat' => __( 'Rekommenderat', 'nordicinterior' ),
		'populart'      => __( 'Populärast', 'nordicinterior' ),
		'pris-lag'      => __( 'Pris: lågt till högt', 'nordicinterior' ),
		'pris-hog'      => __( 'Pris: högt till lågt', 'nordicinterior' ),
		'nyast'         => __( 'Nyast', 'nordicinterior' ),
		'rabatt'        => __( 'Största rabatt', 'nordicinterior' ),
	);
}

/**
 * Read the active filters from the request.
 *
 * @return array{tax:array<string,string[]>, range:array<string,array{min:?float,max:?float}>, sale:bool, sort:string, view:string}
 */
function ni_active_filters() {
	static $active = null;
	if ( null !== $active ) {
		return $active;
	}
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only listing filters.
	$active = array(
		'tax'   => array(),
		'range' => array(),
		'sale'  => ! empty( $_GET['rea'] ),
		'sort'  => isset( $_GET['sortera'] ) ? sanitize_key( wp_unslash( $_GET['sortera'] ) ) : '',
		'view'  => isset( $_GET['visa'] ) && 'lista' === sanitize_key( wp_unslash( $_GET['visa'] ) ) ? 'lista' : 'rutnat',
	);
	foreach ( ni_filter_taxonomies() as $param => $filter ) {
		if ( ! empty( $_GET[ $param ] ) ) {
			$raw   = is_array( $_GET[ $param ] ) ? implode( ',', array_map( 'sanitize_text_field', wp_unslash( $_GET[ $param ] ) ) ) : sanitize_text_field( wp_unslash( $_GET[ $param ] ) );
			$slugs = array_values( array_filter( array_map( 'sanitize_title', explode( ',', $raw ) ) ) );
			if ( $slugs ) {
				$active['tax'][ $param ] = array_slice( $slugs, 0, 20 );
			}
		}
	}
	foreach ( array_keys( ni_filter_ranges() ) as $key ) {
		$min = isset( $_GET[ $key . '_min' ] ) && '' !== $_GET[ $key . '_min' ] ? (float) $_GET[ $key . '_min' ] : null;
		$max = isset( $_GET[ $key . '_max' ] ) && '' !== $_GET[ $key . '_max' ] ? (float) $_GET[ $key . '_max' ] : null;
		if ( null !== $min || null !== $max ) {
			$active['range'][ $key ] = array(
				'min' => $min,
				'max' => $max,
			);
		}
	}
	// phpcs:enable
	if ( ! array_key_exists( $active['sort'], ni_sort_options() ) ) {
		$active['sort'] = '';
	}
	return $active;
}

/**
 * Are filters (not just sorting) active?
 *
 * @return bool
 */
function ni_has_active_filters() {
	$a = ni_active_filters();
	return (bool) ( $a['tax'] || $a['range'] || $a['sale'] );
}

/**
 * Is this a product listing page?
 *
 * @return bool
 */
function ni_is_listing() {
	if ( ! ni_wc() ) {
		return false;
	}
	return is_shop() || is_product_taxonomy() || ( is_search() && 'post' !== get_query_var( 'post_type' ) ) || is_page_template( 'page-templates/sale.php' ) || (bool) get_query_var( 'ni_landing' );
}

/**
 * Apply filters and sorting to query args.
 *
 * @param array  $args         WP_Query args.
 * @param string $default_sort Default sort key.
 * @return array
 */
function ni_listing_apply( $args, $default_sort = 'rekommenderat' ) {
	$active    = ni_active_filters();
	$tax_query = isset( $args['tax_query'] ) ? (array) $args['tax_query'] : array();
	$meta      = isset( $args['meta_query'] ) ? (array) $args['meta_query'] : array();

	foreach ( ni_filter_taxonomies() as $param => $filter ) {
		if ( empty( $active['tax'][ $param ] ) ) {
			continue;
		}
		$tax_query[] = array(
			'taxonomy'         => $filter['tax'],
			'field'            => 'slug',
			'terms'            => $active['tax'][ $param ],
			'include_children' => true,
		);
	}

	foreach ( ni_filter_ranges() as $key => $range ) {
		if ( empty( $active['range'][ $key ] ) ) {
			continue;
		}
		$min = $active['range'][ $key ]['min'];
		$max = $active['range'][ $key ]['max'];
		if ( null !== $min && null !== $max ) {
			$meta[] = array(
				'key'     => $range['meta'],
				'value'   => array( min( $min, $max ), max( $min, $max ) ),
				'compare' => 'BETWEEN',
				'type'    => 'DECIMAL(10,2)',
			);
		} elseif ( null !== $min ) {
			$meta[] = array(
				'key'     => $range['meta'],
				'value'   => $min,
				'compare' => '>=',
				'type'    => 'DECIMAL(10,2)',
			);
		} else {
			$meta[] = array(
				'key'     => $range['meta'],
				'value'   => $max,
				'compare' => '<=',
				'type'    => 'DECIMAL(10,2)',
			);
		}
	}

	if ( $active['sale'] ) {
		$on_sale          = array_map( 'intval', wc_get_product_ids_on_sale() );
		$args['post__in'] = empty( $args['post__in'] ) ? ( $on_sale ? $on_sale : array( 0 ) ) : array_values( array_intersect( (array) $args['post__in'], $on_sale ) );
		if ( empty( $args['post__in'] ) ) {
			$args['post__in'] = array( 0 );
		}
	}

	$sort = $active['sort'] ? $active['sort'] : $default_sort;
	switch ( $sort ) {
		case 'pris-lag':
		case 'pris-hog':
			$meta['ni_price'] = array(
				'key'  => '_price',
				'type' => 'DECIMAL(10,2)',
			);
			$args['orderby']  = array(
				'ni_price' => 'pris-lag' === $sort ? 'ASC' : 'DESC',
				'ID'       => 'DESC',
			);
			break;
		case 'nyast':
			$args['orderby'] = array( 'date' => 'DESC' );
			break;
		case 'rabatt':
			$meta['ni_discount_sort'] = array(
				'relation'    => 'OR',
				'ni_discount' => array(
					'key'  => '_ni_discount',
					'type' => 'NUMERIC',
				),
				array(
					'key'     => '_ni_discount',
					'compare' => 'NOT EXISTS',
				),
			);
			$args['orderby']          = array(
				'ni_discount' => 'DESC',
				'date'        => 'DESC',
			);
			break;
		case 'populart':
		case 'rekommenderat':
		default:
			$meta['ni_clicks_sort'] = array(
				'relation'  => 'OR',
				'ni_clicks' => array(
					'key'  => '_ni_clicks',
					'type' => 'NUMERIC',
				),
				array(
					'key'     => '_ni_clicks',
					'compare' => 'NOT EXISTS',
				),
			);
			$args['orderby']        = 'populart' === $sort
				? array(
					'ni_clicks' => 'DESC',
					'date'      => 'DESC',
				)
				: array(
					'menu_order' => 'ASC',
					'ni_clicks'  => 'DESC',
					'date'       => 'DESC',
				);
	}

	if ( $tax_query ) {
		$tax_query['relation'] = 'AND';
		$args['tax_query']     = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Listing filters.
	}
	if ( $meta ) {
		$meta['relation']   = 'AND';
		$args['meta_query'] = $meta; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Listing filters.
	}
	return $args;
}

/**
 * Apply filters to WooCommerce's main listing queries.
 *
 * @param WP_Query $query Query.
 */
function ni_listing_pre_get_posts( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! ni_wc() ) {
		return;
	}
	$is_product_search = $query->is_search() && 'post' !== $query->get( 'post_type' );
	$is_listing        = $query->is_post_type_archive( 'product' ) || $query->is_tax( get_object_taxonomies( 'product' ) ) || $is_product_search || $query->get( 'ni_landing' );
	if ( ! $is_listing ) {
		return;
	}
	$query->set( 'posts_per_page', ni_wc_per_page() );
	$args = ni_listing_apply(
		array(
			'tax_query'  => (array) $query->get( 'tax_query' ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Listing filters.
			'meta_query' => (array) $query->get( 'meta_query' ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Listing filters.
			'post__in'   => (array) $query->get( 'post__in' ),
		)
	);
	foreach ( array( 'tax_query', 'meta_query', 'post__in', 'orderby' ) as $key ) {
		if ( isset( $args[ $key ] ) && ( $args[ $key ] || 'post__in' !== $key ) ) {
			$query->set( $key, $args[ $key ] );
		}
	}
	// Our orderby replaces WooCommerce's catalog ordering.
	$query->set( 'wc_query', '' );
	$query->set( 'order', '' );
}
add_action( 'pre_get_posts', 'ni_listing_pre_get_posts', 25 );

/**
 * WooCommerce adds its own ordering on product queries; ours wins.
 *
 * @param array $args Ordering args.
 * @return array
 */
function ni_wc_ordering_args( $args ) {
	if ( is_admin() ) {
		return $args;
	}
	return array(
		'orderby'  => '',
		'order'    => '',
		'meta_key' => '', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Reset.
	);
}
add_filter( 'woocommerce_get_catalog_ordering_args', 'ni_wc_ordering_args', 99 );

/**
 * Filtered listing URLs: noindex, follow.
 *
 * @param array $robots Robots directives.
 * @return array
 */
function ni_listing_robots( $robots ) {
	$active = ni_active_filters();
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only.
	if ( ni_has_active_filters() || $active['sort'] || isset( $_GET['visa'] ) || is_search() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
		unset( $robots['index'], $robots['max-image-preview'] );
	}
	return $robots;
}
add_filter( 'wp_robots', 'ni_listing_robots', 20 );

/**
 * Yoast/Rank Math: keep their robots output consistent with ours.
 *
 * @param mixed $robots Robots.
 * @return mixed
 */
function ni_seo_plugin_robots( $robots ) {
	if ( ni_has_active_filters() ) {
		if ( is_array( $robots ) ) {
			$robots['index']  = 'noindex';
			$robots['follow'] = 'follow';
			return $robots;
		}
		return 'noindex, follow';
	}
	return $robots;
}
add_filter( 'wpseo_robots', 'ni_seo_plugin_robots', 20 );
add_filter( 'rank_math/frontend/robots', 'ni_seo_plugin_robots', 20 );

/**
 * Should the template return only the listing fragment?
 *
 * @return bool
 */
function ni_is_partial() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only flag.
	return ! empty( $_GET['ni_partial'] ) && ni_is_listing();
}

/**
 * Base (unfiltered) context of the current listing: used for facets.
 *
 * @return array WP_Query args.
 */
function ni_listing_base_args() {
	$args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 3000, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page -- IDs only, facet result cached for 6 h.
		'fields'         => 'ids',
		'no_found_rows'  => true,
	);
	$tax  = array();
	if ( get_query_var( 'ni_landing' ) ) {
		foreach ( array( 'ni_room', 'ni_style', 'product_cat' ) as $t ) {
			$slug = get_query_var( $t );
			if ( $slug ) {
				$tax[] = array(
					'taxonomy' => $t,
					'field'    => 'slug',
					'terms'    => array( sanitize_title( basename( (string) $slug ) ) ),
				);
			}
		}
	} elseif ( is_product_taxonomy() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$tax[] = array(
				'taxonomy' => $term->taxonomy,
				'terms'    => array( $term->term_id ),
			);
		}
	} elseif ( is_search() ) {
		global $wp_query;
		$parsed = $wp_query->get( 'ni_parsed' );
		if ( is_array( $parsed ) ) {
			foreach ( $parsed['filters'] as $t => $ids ) {
				$tax[] = array(
					'taxonomy' => $t,
					'terms'    => $ids,
				);
			}
			$args['ni_search_sql'] = ni_search_sql( $parsed['words'] );
			$args['fields']        = 'ids';
		}
	} elseif ( is_page_template( 'page-templates/sale.php' ) ) {
		$sale             = wc_get_product_ids_on_sale();
		$args['post__in'] = $sale ? array_map( 'intval', $sale ) : array( 0 );
	}
	if ( $tax ) {
		$args['tax_query'] = $tax; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Cached facet query.
	}
	return $args;
}

/**
 * Facets (available terms and ranges) for the current listing.
 *
 * @return array{tax:array<string, array<int, array{slug:string,name:string,count:int,hex?:string,parent:int}>>, range:array<string,array{min:float,max:float}>}
 */
function ni_listing_facets() {
	$base = ni_listing_base_args();
	$key  = 'ni_facets_' . md5( (string) wp_json_encode( $base ) . ni_cache_version() );
	$out  = get_transient( $key );
	if ( is_array( $out ) ) {
		return $out;
	}
	$out = array(
		'tax'   => array(),
		'range' => array(),
	);

	if ( isset( $base['ni_search_sql'] ) ) {
		$q   = new WP_Query( $base );
		$ids = array_map( 'intval', $q->posts );
	} else {
		$ids = ni_query_product_ids( $base );
	}

	if ( $ids ) {
		$taxes = wp_list_pluck( ni_filter_taxonomies(), 'tax' );
		$terms = wp_get_object_terms( $ids, array_values( $taxes ), array( 'fields' => 'all_with_object_id' ) );
		if ( ! is_wp_error( $terms ) ) {
			$counts = array();
			foreach ( $terms as $term ) {
				$k = $term->taxonomy . ':' . $term->term_id;
				if ( ! isset( $counts[ $k ] ) ) {
					$counts[ $k ] = array(
						'term'  => $term,
						'count' => 0,
					);
				}
				++$counts[ $k ]['count'];
			}
			foreach ( $counts as $row ) {
				$term = $row['term'];
				$item = array(
					'slug'   => $term->slug,
					'name'   => $term->name,
					'count'  => $row['count'],
					'parent' => (int) $term->parent,
				);
				if ( 'pa_farg' === $term->taxonomy ) {
					$hex         = (string) get_term_meta( $term->term_id, 'ni_hex', true );
					$item['hex'] = sanitize_hex_color( $hex ) ? $hex : '#CCCCCC';
				}
				$out['tax'][ $term->taxonomy ][] = $item;
			}
			foreach ( $out['tax'] as $tax => $items ) {
				usort(
					$items,
					static function ( $a, $b ) {
						$diff = $b['count'] <=> $a['count'];
						return 0 !== $diff ? $diff : strcoll( $a['name'], $b['name'] );
					}
				);
				$out['tax'][ $tax ] = $items;
			}
		}

		global $wpdb;
		$id_list = implode( ',', array_map( 'intval', $ids ) );
		foreach ( ni_filter_ranges() as $rkey => $range ) {
			// IDs are integers cast above; meta key is a fixed string.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT MIN(CAST(meta_value AS DECIMAL(10,2))) AS mn, MAX(CAST(meta_value AS DECIMAL(10,2))) AS mx FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value <> '' AND post_id IN ({$id_list})", $range['meta'] ) );
			if ( $row && null !== $row->mx && (float) $row->mx > 0 ) {
				$out['range'][ $rkey ] = array(
					'min' => floor( (float) $row->mn ),
					'max' => ceil( (float) $row->mx ),
				);
			}
		}
	}

	set_transient( $key, $out, 6 * HOUR_IN_SECONDS );
	return $out;
}

/**
 * URL of the current listing without filters (keeps the search term).
 *
 * @return string
 */
function ni_listing_clean_url() {
	$keep = array();
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only.
	if ( isset( $_GET['s'] ) ) {
		$keep['s']         = sanitize_text_field( wp_unslash( $_GET['s'] ) );
		$keep['post_type'] = 'product';
	}
	// phpcs:enable
	$url = remove_query_arg( false );
	$url = strtok( (string) $url, '?' );
	$url = (string) preg_replace( '#/page/\d+/?#', '/', (string) $url );
	return $keep ? add_query_arg( array_map( 'rawurlencode', $keep ), $url ) : $url;
}

/**
 * URL with one filter value toggled.
 *
 * @param string $param Param.
 * @param string $value Value (slug) or '' to remove the whole param.
 * @return string
 */
function ni_filter_toggle_url( $param, $value = '' ) {
	$active  = ni_active_filters();
	$current = $active['tax'][ $param ] ?? array();
	if ( '' === $value ) {
		$current = array();
	} elseif ( in_array( $value, $current, true ) ) {
		$current = array_values( array_diff( $current, array( $value ) ) );
	} else {
		$current[] = $value;
	}
	$url = remove_query_arg( array( $param, 'paged', 'ni_partial' ) );
	$url = (string) preg_replace( '#/page/\d+/?#', '/', $url );
	return $current ? add_query_arg( $param, implode( ',', $current ), $url ) : $url;
}
