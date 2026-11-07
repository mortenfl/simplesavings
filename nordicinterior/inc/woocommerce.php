<?php
/**
 * WooCommerce integration: catalogue only (external/affiliate products),
 * no cart, checkout or account pages, product data helpers and extra fields.
 *
 * HPOS: the theme never reads or writes orders, so it is compatible with
 * High-Performance Order Storage. WooCommerce's compatibility declaration API
 * (FeaturesUtil::declare_compatibility) only accepts plugin files, which is
 * why nothing is declared from a theme – see README.md.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Remove every purchase-related hook and template part.
 */
function ni_wc_remove_purchase() {
	if ( ! ni_wc() ) {
		return;
	}
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	remove_action( 'woocommerce_external_add_to_cart', 'woocommerce_external_add_to_cart', 30 );
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );

	// Our JSON-LD replaces WooCommerce's product schema (affiliate offer, seller = store).
	if ( function_exists( 'WC' ) && isset( WC()->structured_data ) ) {
		remove_action( 'woocommerce_single_product_summary', array( WC()->structured_data, 'generate_product_data' ), 60 );
		remove_action( 'wp_footer', array( WC()->structured_data, 'output_structured_data' ), 10 );
	}
}
add_action( 'wp', 'ni_wc_remove_purchase' );

/**
 * Nothing is purchasable on this site.
 *
 * @return bool
 */
function ni_wc_not_purchasable() {
	return false;
}
add_filter( 'woocommerce_is_purchasable', 'ni_wc_not_purchasable' );

/**
 * Redirect cart, checkout and account pages to the front page.
 */
function ni_wc_redirect_purchase_pages() {
	if ( ! ni_wc() ) {
		return;
	}
	if ( is_cart() || is_checkout() || is_account_page() ) {
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'ni_wc_redirect_purchase_pages' );

/**
 * Dequeue cart scripts.
 */
function ni_wc_dequeue_cart_scripts() {
	wp_dequeue_script( 'wc-cart-fragments' );
	wp_dequeue_script( 'wc-add-to-cart' );
	wp_dequeue_script( 'woocommerce' );
	wp_dequeue_script( 'sourcebuster-js' );
	wp_dequeue_script( 'wc-order-attribution' );
	wp_dequeue_script( 'wc-single-product' );
	wp_dequeue_script( 'zoom' );
	wp_dequeue_script( 'flexslider' );
	wp_dequeue_script( 'photoswipe' );
	wp_dequeue_script( 'photoswipe-ui-default' );
	wp_dequeue_script( 'wc-zoom' );
	wp_dequeue_script( 'wc-flexslider' );
	wp_dequeue_script( 'wc-photoswipe' );
	wp_dequeue_script( 'wc-photoswipe-ui-default' );
	wp_dequeue_style( 'photoswipe' );
	wp_dequeue_style( 'photoswipe-default-skin' );
	wp_dequeue_style( 'woocommerce_prettyPhoto_css' );
}
add_action( 'wp_enqueue_scripts', 'ni_wc_dequeue_cart_scripts', 99 );

/**
 * Default new products to "External/Affiliate".
 *
 * @param array $types Product types.
 * @return array
 */
function ni_wc_product_type_selector( $types ) {
	// Keep external first so it is the obvious choice.
	if ( isset( $types['external'] ) ) {
		$types = array( 'external' => $types['external'] ) + $types;
	}
	return $types;
}
add_filter( 'product_type_selector', 'ni_wc_product_type_selector' );

/**
 * Products per page.
 *
 * @return int
 */
function ni_wc_per_page() {
	return max( 4, absint( ni_mod( 'per_page' ) ) );
}
add_filter( 'loop_shop_per_page', 'ni_wc_per_page', 20 );

/**
 * Extra product fields: series, care and box text.
 */
function ni_wc_product_fields() {
	echo '<div class="options_group">';
	woocommerce_wp_text_input(
		array(
			'id'          => '_ni_series',
			'label'       => __( 'Serie', 'nordicinterior' ),
			'desc_tip'    => true,
			'description' => __( 'Används för ”Fler från samma serie”.', 'nordicinterior' ),
		)
	);
	woocommerce_wp_textarea_input(
		array(
			'id'    => '_ni_care',
			'label' => __( 'Skötsel', 'nordicinterior' ),
		)
	);
	woocommerce_wp_text_input(
		array(
			'id'          => '_ni_delivery',
			'label'       => __( 'Leveranstid (valfri)', 'nordicinterior' ),
			'desc_tip'    => true,
			'description' => __( 'Ersätter butikens leveranstid för denna produkt.', 'nordicinterior' ),
		)
	);
	echo '</div>';
}
add_action( 'woocommerce_product_options_general_product_data', 'ni_wc_product_fields' );

/**
 * Save extra product fields.
 *
 * @param WC_Product $product Product.
 */
function ni_wc_save_product_fields( $product ) {
	// WooCommerce verifies its own nonce before this action runs.
	// phpcs:disable WordPress.Security.NonceVerification.Missing
	if ( isset( $_POST['_ni_series'] ) ) {
		$product->update_meta_data( '_ni_series', sanitize_text_field( wp_unslash( $_POST['_ni_series'] ) ) );
	}
	if ( isset( $_POST['_ni_care'] ) ) {
		$product->update_meta_data( '_ni_care', sanitize_textarea_field( wp_unslash( $_POST['_ni_care'] ) ) );
	}
	if ( isset( $_POST['_ni_delivery'] ) ) {
		$product->update_meta_data( '_ni_delivery', sanitize_text_field( wp_unslash( $_POST['_ni_delivery'] ) ) );
	}
	// phpcs:enable
}
add_action( 'woocommerce_admin_process_product_object', 'ni_wc_save_product_fields' );

/**
 * Colour hex for pa_farg terms.
 *
 * @param array $fields Fields.
 * @return array
 */
function ni_color_term_fields( $fields ) {
	$fields['pa_farg'] = array(
		'hex' => array(
			'type'  => 'color',
			'label' => __( 'Färgprick', 'nordicinterior' ),
			'desc'  => __( 'Färgen som visas i filter och på produktkort.', 'nordicinterior' ),
		),
	);
	return $fields;
}
add_filter( 'ni_term_fields', 'ni_color_term_fields' );

/*
 * -------------------------------------------------------------------------
 * Product data helpers
 * -------------------------------------------------------------------------
 */

/**
 * Get a WC_Product from ID/post/product.
 *
 * @param mixed $product Product, ID or post.
 * @return WC_Product|null
 */
function ni_product( $product = null ) {
	if ( ! ni_wc() ) {
		return null;
	}
	if ( $product instanceof WC_Product ) {
		return $product;
	}
	$p = wc_get_product( $product ? $product : get_the_ID() );
	return $p ? $p : null;
}

/**
 * First term of a taxonomy for a product.
 *
 * @param WC_Product $product  Product.
 * @param string     $taxonomy Taxonomy.
 * @return WP_Term|null
 */
function ni_product_term( $product, $taxonomy ) {
	if ( ! taxonomy_exists( $taxonomy ) ) {
		return null;
	}
	$terms = get_the_terms( $product->get_id(), $taxonomy );
	return ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
}

/**
 * Store of a product.
 *
 * @param WC_Product $product Product.
 * @return WP_Term|null
 */
function ni_product_store( $product ) {
	return ni_product_term( $product, 'ni_store' );
}

/**
 * Brand of a product.
 *
 * @param WC_Product $product Product.
 * @return WP_Term|null
 */
function ni_product_brand( $product ) {
	return ni_product_term( $product, 'product_brand' );
}

/**
 * Attribute term names (pa_*) of a product.
 *
 * @param WC_Product $product   Product.
 * @param string     $attribute Attribute taxonomy, e.g. pa_material.
 * @return WP_Term[]
 */
function ni_product_attr_terms( $product, $attribute ) {
	if ( ! taxonomy_exists( $attribute ) ) {
		return array();
	}
	$terms = wc_get_product_terms( $product->get_id(), $attribute, array( 'fields' => 'all' ) );
	return is_array( $terms ) ? $terms : array();
}

/**
 * Designer name(s).
 *
 * @param WC_Product $product Product.
 * @return string
 */
function ni_product_designer( $product ) {
	return implode( ', ', wp_list_pluck( ni_product_attr_terms( $product, 'pa_designer' ), 'name' ) );
}

/**
 * Colour swatches for a product.
 *
 * @param WC_Product $product Product.
 * @return array<int, array{name:string, hex:string, slug:string}>
 */
function ni_product_colors( $product ) {
	$out = array();
	foreach ( ni_product_attr_terms( $product, 'pa_farg' ) as $term ) {
		$hex   = (string) get_term_meta( $term->term_id, 'ni_hex', true );
		$out[] = array(
			'name' => $term->name,
			'slug' => $term->slug,
			'hex'  => sanitize_hex_color( $hex ) ? $hex : '#CCCCCC',
		);
	}
	return $out;
}

/**
 * Dimensions in cm: width, depth (WooCommerce "length") and height.
 *
 * @param WC_Product $product Product.
 * @return array{width:float, depth:float, height:float}
 */
function ni_product_dims( $product ) {
	return array(
		'width'  => (float) $product->get_width(),
		'depth'  => (float) $product->get_length(),
		'height' => (float) $product->get_height(),
	);
}

/**
 * The affiliate URL (WooCommerce external product URL) – never rewritten.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function ni_product_url( $product ) {
	if ( $product instanceof WC_Product_External ) {
		return (string) $product->get_product_url();
	}
	return (string) $product->get_meta( '_product_url' );
}

/**
 * CTA text: the product's own button text or the default.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function ni_cta_text( $product ) {
	$text = '';
	if ( $product instanceof WC_Product_External ) {
		$text = (string) $product->get_button_text();
		// WooCommerce's own default "Buy product" is replaced by ours.
		if ( __( 'Buy product', 'woocommerce' ) === $text || 'Buy product' === $text ) { // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch -- Compare with WooCommerce's default.
			$text = '';
		}
	}
	return $text ? $text : (string) ni_mod( 'cta_text' );
}

/**
 * Discount in whole percent (0 when not on sale).
 *
 * @param WC_Product $product Product.
 * @return int
 */
function ni_product_discount( $product ) {
	$regular = (float) $product->get_regular_price();
	$sale    = (float) $product->get_sale_price();
	if ( $regular > 0 && $sale > 0 && $sale < $regular ) {
		return (int) round( ( 1 - $sale / $regular ) * 100 );
	}
	return 0;
}

/**
 * Store information with per-product overrides.
 *
 * @param WC_Product $product Product.
 * @return array{name:string,url:string,shipping:string,returns:string,delivery:string,term:?WP_Term}
 */
function ni_store_info( $product ) {
	$store    = ni_product_store( $product );
	$info     = array(
		'name'     => $store ? $store->name : '',
		'url'      => $store ? (string) get_term_link( $store ) : '',
		'shipping' => $store ? (string) ni_term_meta( $store->term_id, 'shipping' ) : '',
		'returns'  => $store ? (string) ni_term_meta( $store->term_id, 'returns' ) : '',
		'delivery' => $store ? (string) ni_term_meta( $store->term_id, 'delivery' ) : '',
		'term'     => $store,
	);
	$override = (string) $product->get_meta( '_ni_delivery' );
	if ( $override ) {
		$info['delivery'] = $override;
	}
	return $info;
}

/**
 * Price HTML with tabular numbers and the original price in clay.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function ni_price_html( $product ) {
	$price   = (float) $product->get_price();
	$regular = (float) $product->get_regular_price();
	if ( $price <= 0 ) {
		return '';
	}
	$html = '<span class="ni-price">';
	if ( $product->is_on_sale() && $regular > $price ) {
		$html .= '<span class="ni-price__now ni-price__now--sale"><span class="screen-reader-text">' . esc_html__( 'Nu', 'nordicinterior' ) . ' </span>' . esc_html( ni_format_price( $price ) ) . '</span> ';
		$html .= '<del class="ni-price__was"><span class="screen-reader-text">' . esc_html__( 'Ordinarie pris', 'nordicinterior' ) . ' </span>' . esc_html( ni_format_price( $regular ) ) . '</del>';
	} else {
		$html .= '<span class="ni-price__now">' . esc_html( ni_format_price( $price ) ) . '</span>';
	}
	$html .= '</span>';
	return $html;
}

/**
 * Make sure the "Butik" row exists among the attributes; used in the
 * Specifications tab (see template-tags.php).
 *
 * @param WC_Product $product Product.
 * @return array<int, array{label:string, value:string, url?:string}>
 */
function ni_product_specs( $product ) {
	$rows = array();
	$map  = array(
		'pa_material' => __( 'Material', 'nordicinterior' ),
		'pa_farg'     => __( 'Färg', 'nordicinterior' ),
		'pa_designer' => __( 'Designer', 'nordicinterior' ),
	);
	foreach ( $map as $tax => $label ) {
		$terms = ni_product_attr_terms( $product, $tax );
		if ( $terms ) {
			$rows[ $tax ] = array(
				'label' => $label,
				'value' => implode( ', ', wp_list_pluck( $terms, 'name' ) ),
			);
		}
	}

	// Other visible attributes (custom or taxonomy) that are not mapped above.
	foreach ( $product->get_attributes() as $key => $attribute ) {
		if ( isset( $map[ $key ] ) || ! $attribute->get_visible() ) {
			continue;
		}
		$label = wc_attribute_label( $attribute->get_name(), $product );
		if ( 'butik' === sanitize_title( $label ) ) {
			continue; // Store row is always generated below.
		}
		$value        = $attribute->is_taxonomy()
			? implode( ', ', wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'names' ) ) )
			: implode( ', ', $attribute->get_options() );
		$rows[ $key ] = array(
			'label' => $label,
			'value' => $value,
		);
	}

	$dims = ni_product_dims( $product );
	foreach ( array(
		'width'  => __( 'Bredd', 'nordicinterior' ),
		'depth'  => __( 'Djup', 'nordicinterior' ),
		'height' => __( 'Höjd', 'nordicinterior' ),
	) as $key => $label ) {
		if ( $dims[ $key ] > 0 ) {
			$rows[ 'dim_' . $key ] = array(
				'label' => $label,
				'value' => wc_format_localized_decimal( $dims[ $key ] ) . ' cm',
			);
		}
	}

	$care = (string) $product->get_meta( '_ni_care' );
	if ( $care ) {
		$rows['care'] = array(
			'label' => __( 'Skötsel', 'nordicinterior' ),
			'value' => $care,
		);
	}

	$brand = ni_product_brand( $product );
	if ( $brand ) {
		$rows['brand'] = array(
			'label' => __( 'Varumärke', 'nordicinterior' ),
			'value' => $brand->name,
			'url'   => (string) get_term_link( $brand ),
		);
	}

	// The "Butik" row is added automatically when missing.
	$store = ni_product_store( $product );
	if ( $store ) {
		$rows['store'] = array(
			'label' => __( 'Butik', 'nordicinterior' ),
			'value' => $store->name,
			'url'   => (string) get_term_link( $store ),
		);
	}

	$sku = $product->get_sku();
	if ( $sku ) {
		$rows['sku'] = array(
			'label' => __( 'Artikelnummer', 'nordicinterior' ),
			'value' => $sku,
		);
	}

	/**
	 * Filter the specification rows.
	 *
	 * @param array      $rows    Rows.
	 * @param WC_Product $product Product.
	 */
	return apply_filters( 'ni_product_specs', $rows, $product );
}

/**
 * Query product IDs with simple, cached criteria.
 *
 * @param array $args WP_Query args (merged with sane defaults).
 * @return int[]
 */
function ni_query_product_ids( $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'posts_per_page'      => 8,
			'fields'              => 'ids',
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
		)
	);
	$key  = 'ni_q_' . md5( (string) wp_json_encode( $args ) . ni_cache_version() );
	$ids  = get_transient( $key );
	if ( false === $ids ) {
		$q   = new WP_Query( $args );
		$ids = array_map( 'intval', $q->posts );
		set_transient( $key, $ids, 6 * HOUR_IN_SECONDS );
	}
	return $ids;
}

/**
 * Version number used to bust product query caches.
 *
 * @return string
 */
function ni_cache_version() {
	return (string) get_option( 'ni_cache_version', '1' );
}

/**
 * Bump the cache version when products change.
 */
function ni_bump_cache() {
	update_option( 'ni_cache_version', (string) time(), false );
}
add_action( 'woocommerce_update_product', 'ni_bump_cache' );
add_action( 'woocommerce_new_product', 'ni_bump_cache' );
add_action( 'ni_after_recalc', 'ni_bump_cache' );
add_action( 'pmxi_after_xml_import', 'ni_bump_cache' );

/**
 * Sorting by our meta should work also for products missing the meta.
 * Ensures _ni_clicks/_ni_discount exist on save.
 *
 * @param int $product_id Product ID.
 */
function ni_wc_ensure_sort_meta( $product_id ) {
	if ( '' === get_post_meta( $product_id, '_ni_clicks', true ) ) {
		update_post_meta( $product_id, '_ni_clicks', 0 );
	}
	$product = wc_get_product( $product_id );
	if ( $product ) {
		update_post_meta( $product_id, '_ni_discount', ni_product_discount( $product ) );
	}
}
add_action( 'woocommerce_update_product', 'ni_wc_ensure_sort_meta' );
add_action( 'woocommerce_new_product', 'ni_wc_ensure_sort_meta' );

/**
 * Replace WooCommerce wrapper markup.
 */
function ni_wc_wrappers() {
	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
}
add_action( 'init', 'ni_wc_wrappers' );

/**
 * No reviews/comments on products (catalogue only).
 *
 * @param bool $open    Open.
 * @param int  $post_id Post ID.
 * @return bool
 */
function ni_wc_no_product_comments( $open, $post_id ) {
	return 'product' === get_post_type( $post_id ) ? false : $open;
}
add_filter( 'comments_open', 'ni_wc_no_product_comments', 10, 2 );
