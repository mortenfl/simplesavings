<?php
/**
 * Related product rails on the product page:
 *  - Passar ihop med: same style or material, other categories.
 *  - Liknande till lägre pris: same category, cheaper, same style/material first.
 *  - Fler från samma serie / designer / varumärke.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Term IDs of a product in a taxonomy.
 *
 * @param WC_Product $product  Product.
 * @param string     $taxonomy Taxonomy.
 * @return int[]
 */
function ni_product_term_ids( $product, $taxonomy ) {
	if ( ! taxonomy_exists( $taxonomy ) ) {
		return array();
	}
	$ids = wp_get_post_terms( $product->get_id(), $taxonomy, array( 'fields' => 'ids' ) );
	return is_wp_error( $ids ) ? array() : array_map( 'intval', $ids );
}

/**
 * "Passar ihop med": same style and/or material, different category.
 *
 * @param WC_Product $product Product.
 * @param int        $limit   Limit.
 * @return int[]
 */
function ni_related_matching( $product, $limit = 8 ) {
	$styles    = ni_product_term_ids( $product, 'ni_style' );
	$materials = ni_product_term_ids( $product, 'pa_material' );
	$cats      = $product->get_category_ids();
	if ( ! $styles && ! $materials ) {
		return array();
	}
	$or = array( 'relation' => 'OR' );
	if ( $styles ) {
		$or[] = array(
			'taxonomy' => 'ni_style',
			'terms'    => $styles,
		);
	}
	if ( $materials ) {
		$or[] = array(
			'taxonomy' => 'pa_material',
			'terms'    => $materials,
		);
	}
	$tax = array( $or );
	if ( $cats ) {
		$tax[] = array(
			'taxonomy'         => 'product_cat',
			'terms'            => $cats,
			'operator'         => 'NOT IN',
			'include_children' => false,
		);
	}
	return ni_query_product_ids(
		array(
			'posts_per_page' => $limit,
			'post__not_in'   => array( $product->get_id() ),
			'tax_query'      => $tax, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Cached.
			'meta_key'       => '_ni_clicks', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Cached.
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC',
		)
	);
}

/**
 * "Liknande till lägre pris".
 *
 * @param WC_Product $product Product.
 * @param int        $limit   Limit.
 * @return int[]
 */
function ni_related_cheaper( $product, $limit = 8 ) {
	$price = (float) $product->get_price();
	$cats  = $product->get_category_ids();
	if ( $price <= 0 || ! $cats ) {
		return array();
	}
	$base = array(
		'posts_per_page' => $limit,
		'post__not_in'   => array( $product->get_id() ),
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Cached.
			'ni_price' => array(
				'key'     => '_price',
				'value'   => $price * 0.98,
				'compare' => '<',
				'type'    => 'DECIMAL(10,2)',
			),
		),
		'orderby'        => array( 'ni_price' => 'DESC' ),
	);
	$tax  = array(
		array(
			'taxonomy' => 'product_cat',
			'terms'    => $cats,
		),
	);
	// First try: same category AND same style or material.
	$styles    = ni_product_term_ids( $product, 'ni_style' );
	$materials = ni_product_term_ids( $product, 'pa_material' );
	$ids       = array();
	if ( $styles || $materials ) {
		$or = array( 'relation' => 'OR' );
		if ( $styles ) {
			$or[] = array(
				'taxonomy' => 'ni_style',
				'terms'    => $styles,
			);
		}
		if ( $materials ) {
			$or[] = array(
				'taxonomy' => 'pa_material',
				'terms'    => $materials,
			);
		}
		$ids = ni_query_product_ids( array_merge( $base, array( 'tax_query' => array_merge( $tax, array( $or ) ) ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Cached.
	}
	if ( count( $ids ) < $limit ) {
		$more = ni_query_product_ids(
			array_merge(
				$base,
				array(
					'tax_query'    => $tax, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Cached.
					'post__not_in' => array_merge( array( $product->get_id() ), $ids ),
				)
			)
		);
		$ids  = array_slice( array_merge( $ids, $more ), 0, $limit );
	}
	return $ids;
}

/**
 * "Fler från …": series, then designer, then brand.
 *
 * @param WC_Product $product Product.
 * @param int        $limit   Limit.
 * @return array{label:string, ids:int[], url:string}
 */
function ni_related_more_from( $product, $limit = 8 ) {
	$series = (string) $product->get_meta( '_ni_series' );
	if ( $series ) {
		$ids = ni_query_product_ids(
			array(
				'posts_per_page' => $limit,
				'post__not_in'   => array( $product->get_id() ),
				'meta_key'       => '_ni_series', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Cached.
				'meta_value'     => $series, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Cached.
			)
		);
		if ( count( $ids ) >= 2 ) {
			return array(
				/* translators: %s: series name. */
				'label' => sprintf( __( 'Fler från serien %s', 'nordicinterior' ), $series ),
				'ids'   => $ids,
				'url'   => '',
			);
		}
	}
	foreach ( array( 'pa_designer', 'product_brand' ) as $tax ) {
		$terms = 'pa_designer' === $tax ? ni_product_attr_terms( $product, $tax ) : array_filter( array( ni_product_brand( $product ) ) );
		if ( ! $terms ) {
			continue;
		}
		$term = reset( $terms );
		$ids  = ni_query_product_ids(
			array(
				'posts_per_page' => $limit,
				'post__not_in'   => array( $product->get_id() ),
				'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Cached.
					array(
						'taxonomy' => $tax,
						'terms'    => array( $term->term_id ),
					),
				),
			)
		);
		if ( $ids ) {
			return array(
				/* translators: %s: designer or brand name. */
				'label' => sprintf( __( 'Fler från %s', 'nordicinterior' ), $term->name ),
				'ids'   => $ids,
				'url'   => 'product_brand' === $tax ? (string) get_term_link( $term ) : '',
			);
		}
	}
	return array(
		'label' => '',
		'ids'   => array(),
		'url'   => '',
	);
}

/**
 * Simple dimension sketch (SVG) with width, depth and height.
 *
 * @param array $dims Dimensions.
 * @return string
 */
function ni_dimension_sketch( $dims ) {
	if ( $dims['width'] <= 0 && $dims['height'] <= 0 ) {
		return '';
	}
	$fmt = static function ( $v ) {
		return $v > 0 ? wc_format_localized_decimal( $v ) . ' cm' : '–';
	};
	return sprintf(
		'<figure class="ni-sketch"><svg viewBox="0 0 320 220" role="img" aria-labelledby="ni-sketch-t"><title id="ni-sketch-t">%1$s</title><g fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"><path d="M60 80h170v100H60z"/><path d="M60 80l40-35h170l-40 35M230 180l40-35V45"/></g><g fill="none" stroke="currentColor" stroke-width="1" stroke-dasharray="3 3" opacity=".6"><path d="M60 196h170M44 80v100M246 196l40-35"/></g><g font-size="12" fill="currentColor" font-family="inherit"><text x="145" y="214" text-anchor="middle">%2$s</text><text x="30" y="134" text-anchor="middle" transform="rotate(-90 30 134)">%3$s</text><text x="282" y="190" text-anchor="start">%4$s</text></g></svg><figcaption class="screen-reader-text">%5$s</figcaption></figure>',
		esc_html__( 'Måttskiss', 'nordicinterior' ),
		/* translators: %s: width. */
		esc_html( sprintf( __( 'B %s', 'nordicinterior' ), $fmt( $dims['width'] ) ) ),
		/* translators: %s: height. */
		esc_html( sprintf( __( 'H %s', 'nordicinterior' ), $fmt( $dims['height'] ) ) ),
		/* translators: %s: depth. */
		esc_html( sprintf( __( 'D %s', 'nordicinterior' ), $fmt( $dims['depth'] ) ) ),
		/* translators: 1: width, 2: depth, 3: height. */
		esc_html( sprintf( __( 'Bredd %1$s, djup %2$s, höjd %3$s.', 'nordicinterior' ), $fmt( $dims['width'] ), $fmt( $dims['depth'] ), $fmt( $dims['height'] ) ) )
	);
}
