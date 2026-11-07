<?php
/**
 * Affiliate links, disclosure text and the /go/ redirect for campaigns.
 *
 * Product links are NEVER rewritten: they point straight to the product URL
 * stored in WooCommerce. Clicks are counted with a beacon (track.js) that
 * reads the data-ni-* attributes printed here.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Valid click placements.
 *
 * @return array<string,string>
 */
function ni_placements() {
	return array(
		'card'     => __( 'Produktkort', 'nordicinterior' ),
		'product'  => __( 'Produktsida', 'nordicinterior' ),
		'look'     => __( 'Shoppa looken', 'nordicinterior' ),
		'search'   => __( 'Sök', 'nordicinterior' ),
		'home'     => __( 'Startsida', 'nordicinterior' ),
		'sidebar'  => __( 'Sidofält', 'nordicinterior' ),
		'email'    => __( 'Mejl', 'nordicinterior' ),
		'campaign' => __( 'Kampanj', 'nordicinterior' ),
	);
}

/**
 * Attributes for an outgoing affiliate link.
 *
 * @param WC_Product $product   Product.
 * @param string     $placement Placement key.
 * @return string Escaped attribute string (href included).
 */
function ni_affiliate_attrs( $product, $placement = 'card' ) {
	$placement = array_key_exists( $placement, ni_placements() ) ? $placement : 'card';
	$room      = ni_product_term( $product, 'ni_room' );
	$style     = ni_product_term( $product, 'ni_style' );
	$attrs     = array(
		'href'           => esc_url( ni_product_url( $product ) ),
		'rel'            => 'sponsored nofollow noopener',
		'data-ni-click'  => (string) $product->get_id(),
		'data-placement' => $placement,
		'data-room'      => $room ? $room->slug : '',
		'data-style'     => $style ? $style->slug : '',
	);
	if ( ni_mod( 'new_tab' ) ) {
		$attrs['target'] = '_blank';
	}
	$out = '';
	foreach ( $attrs as $name => $value ) {
		$out .= sprintf( ' %s="%s"', $name, 'href' === $name ? $value : esc_attr( $value ) );
	}
	return $out;
}

/**
 * Print the "Till butiken" call to action.
 *
 * @param WC_Product $product   Product.
 * @param string     $placement Placement.
 * @param string     $variant   link|button|large.
 */
function ni_cta( $product, $placement = 'card', $variant = 'link' ) {
	$url = ni_product_url( $product );
	if ( ! $url ) {
		return;
	}
	$text  = ni_cta_text( $product );
	$store = ni_product_store( $product );
	$label = $text;
	if ( 'large' === $variant && $store ) {
		/* translators: 1: CTA text, 2: store name. */
		$label = sprintf( __( '%1$s – %2$s', 'nordicinterior' ), $text, $store->name );
	}
	$classes = array(
		'link'   => 'ni-cta ni-cta--link',
		'button' => 'ni-cta ni-btn ni-btn--outline ni-btn--small',
		'large'  => 'ni-cta ni-btn ni-btn--large ni-btn--block',
	);
	printf(
		'<a class="%1$s"%2$s><span>%3$s</span>%4$s<span class="screen-reader-text"> %5$s</span></a>',
		esc_attr( $classes[ $variant ] ?? $classes['link'] ),
		ni_affiliate_attrs( $product, $placement ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in ni_affiliate_attrs().
		esc_html( $label ),
		ni_icon( 'arrow-right', 'large' === $variant ? 20 : 16 ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG.
		/* translators: %s: product name. */
		esc_html( sprintf( __( '(%s, öppnas i butiken)', 'nordicinterior' ), $product->get_name() ) )
	);
}

/**
 * Print the affiliate disclosure for a given context.
 *
 * @param string $context footer|product|listing|post.
 */
function ni_disclosure( $context ) {
	if ( ! ni_mod( 'disclosure_' . $context ) ) {
		return;
	}
	$text = (string) ni_mod( 'disclosure_text' );
	if ( '' === trim( $text ) ) {
		return;
	}
	printf(
		'<p class="ni-disclosure ni-disclosure--%1$s">%2$s<span>%3$s</span></p>',
		esc_attr( $context ),
		'footer' === $context ? '' : ni_icon( 'info', 16 ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG.
		wp_kses( $text, array( 'a' => array( 'href' => true ) ) )
	);
}

/**
 * Campaign outgoing URL (/go/c/{id}/).
 *
 * @param int    $id        Campaign ID.
 * @param string $placement Placement (added as ?p= for statistics).
 * @return string
 */
function ni_campaign_go_url( $id, $placement = '' ) {
	$prefix = sanitize_title( (string) ni_mod( 'go_prefix' ) );
	$prefix = $prefix ? $prefix : 'go';
	$url    = home_url( user_trailingslashit( $prefix . '/c/' . absint( $id ) ) );
	if ( $placement ) {
		$url = add_query_arg( 'p', rawurlencode( $placement ), $url );
	}
	return $url;
}

/**
 * Rewrite rule for /go/c/{id}/.
 */
function ni_go_rewrite() {
	$prefix = sanitize_title( (string) ni_mod( 'go_prefix' ) );
	$prefix = $prefix ? $prefix : 'go';
	add_rewrite_rule( '^' . preg_quote( $prefix, '/' ) . '/c/([0-9]+)/?$', 'index.php?ni_go_campaign=$matches[1]', 'top' );
}
add_action( 'init', 'ni_go_rewrite' );

/**
 * Query vars.
 *
 * @param string[] $vars Vars.
 * @return string[]
 */
function ni_go_query_vars( $vars ) {
	$vars[] = 'ni_go_campaign';
	return $vars;
}
add_filter( 'query_vars', 'ni_go_query_vars' );

/**
 * Build the final campaign URL with UTM/subID parameters.
 *
 * @param int    $id        Campaign ID.
 * @param string $placement Placement.
 * @return string
 */
function ni_campaign_target_url( $id, $placement = '' ) {
	$url = (string) get_post_meta( $id, '_ni_url', true );
	if ( ! $url ) {
		return '';
	}
	$replace = array(
		'{id}'        => (string) $id,
		'{slug}'      => (string) get_post_field( 'post_name', $id ),
		'{placement}' => $placement ? $placement : 'campaign',
	);
	$params  = array();
	foreach ( array( (string) ni_mod( 'campaign_params' ), (string) ni_mod( 'campaign_subid' ), (string) get_post_meta( $id, '_ni_params', true ) ) as $string ) {
		if ( '' === trim( $string ) ) {
			continue;
		}
		parse_str( strtr( ltrim( trim( $string ), '?&' ), $replace ), $parsed );
		$params = array_merge( $params, $parsed );
	}
	return $params ? add_query_arg( array_map( 'rawurlencode', $params ), $url ) : $url;
}

/**
 * Perform the /go/ redirect. noindex, counted as a "campaign" click.
 */
function ni_go_redirect() {
	$id = absint( get_query_var( 'ni_go_campaign' ) );
	if ( ! $id ) {
		return;
	}
	header( 'X-Robots-Tag: noindex, nofollow', true );
	nocache_headers();
	if ( 'ni_campaign' !== get_post_type( $id ) || ! ni_campaign_is_active( $id ) ) {
		wp_safe_redirect( get_post_type_archive_link( 'ni_campaign' ), 302 );
		exit;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only statistics label.
	$placement = isset( $_GET['p'] ) ? sanitize_key( wp_unslash( $_GET['p'] ) ) : 'campaign';
	$target    = ni_campaign_target_url( $id, $placement );
	if ( ! $target ) {
		wp_safe_redirect( home_url( '/' ), 302 );
		exit;
	}
	if ( ! ni_ignore_current_visitor() ) {
		ni_record_click(
			array(
				'object_id' => $id,
				'type'      => 'campaign',
				'placement' => array_key_exists( $placement, ni_placements() ) ? $placement : 'campaign',
			)
		);
	}
	// External affiliate network URL: wp_redirect (not wp_safe_redirect) by design.
	wp_redirect( $target, 302, 'Nordic Interior' ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- Intentional redirect to an editor-entered affiliate URL.
	exit;
}
add_action( 'template_redirect', 'ni_go_redirect', 1 );

/**
 * Block /go/ in robots.txt.
 *
 * @param string $output Robots.txt.
 * @param bool   $is_public Site public.
 * @return string
 */
function ni_robots_txt( $output, $is_public ) {
	if ( ! $is_public ) {
		return $output;
	}
	$prefix  = sanitize_title( (string) ni_mod( 'go_prefix' ) );
	$prefix  = $prefix ? $prefix : 'go';
	$path    = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	$output .= "\n# Nordic Interior\nUser-agent: *\nDisallow: " . trailingslashit( $path ) . $prefix . "/\n";
	$output .= 'Disallow: ' . trailingslashit( $path ) . "*?*f_\n";
	return $output;
}
add_filter( 'robots_txt', 'ni_robots_txt', 20, 2 );

/**
 * Add rel="sponsored" to external product links WooCommerce prints
 * elsewhere (e.g. widgets, blocks) – the URL itself is left untouched.
 *
 * @param string $html Link HTML.
 * @return string
 */
function ni_wc_loop_link_rel( $html ) {
	if ( false === strpos( $html, 'rel=' ) ) {
		$html = str_replace( '<a ', '<a rel="sponsored nofollow noopener" ', $html );
	}
	return $html;
}
add_filter( 'woocommerce_loop_add_to_cart_link', 'ni_wc_loop_link_rel' );
