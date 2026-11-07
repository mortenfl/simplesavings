<?php
/**
 * Generic helpers: icons, logo, term helpers and small utilities.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return a thin line icon (1.5 px stroke, round caps) as inline SVG.
 *
 * Icons are decorative by default (aria-hidden). Pass a label to expose it.
 *
 * @param string $name  Icon name.
 * @param int    $size  Pixel size.
 * @param string $label Optional accessible label.
 * @return string
 */
function ni_icon( $name, $size = 20, $label = '' ) {
	$paths = array(
		'arrow-right'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'arrow-left'     => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
		'arrow-up-right' => '<path d="M7 17 17 7M8 7h9v9"/>',
		'chevron-down'   => '<path d="m6 9 6 6 6-6"/>',
		'chevron-right'  => '<path d="m9 6 6 6-6 6"/>',
		'chevron-left'   => '<path d="m15 6-6 6 6 6"/>',
		'search'         => '<circle cx="11" cy="11" r="6.5"/><path d="m20 20-4.2-4.2"/>',
		'menu'           => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'close'          => '<path d="M6 6l12 12M18 6 6 18"/>',
		'filter'         => '<path d="M4 6h10M18 6h2M4 12h4M12 12h8M4 18h12M20 18h0"/><circle cx="16" cy="6" r="2"/><circle cx="10" cy="12" r="2"/><circle cx="18" cy="18" r="2"/>',
		'grid'           => '<rect x="4" y="4" width="7" height="7" rx="1"/><rect x="13" y="4" width="7" height="7" rx="1"/><rect x="4" y="13" width="7" height="7" rx="1"/><rect x="13" y="13" width="7" height="7" rx="1"/>',
		'list'           => '<path d="M9 6h11M9 12h11M9 18h11"/><path d="M4.5 6h.01M4.5 12h.01M4.5 18h.01"/>',
		'home'           => '<path d="M4 11 12 4l8 7"/><path d="M6 10v10h12V10"/>',
		'chair'          => '<path d="M7.5 3.5h6l.6 8H8.2z"/><path d="M8.2 11.5 7.7 20"/><path d="M14.1 11.5l1 2.2h3.6l1 6.3"/><path d="M15.1 13.7 14.6 20"/>',
		'sofa'           => '<path d="M5 11V8a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v3"/><path d="M3 13a2 2 0 0 1 4 0v2h10v-2a2 2 0 0 1 4 0v4H3z"/><path d="M5 17v2M19 17v2"/>',
		'lamp'           => '<path d="M8 4h8l3 7H5z"/><path d="M12 11v8M8 20h8"/>',
		'tag'            => '<path d="M4 12V5a1 1 0 0 1 1-1h7l8 8-8 8z"/><circle cx="8.5" cy="8.5" r="1.2"/>',
		'percent'        => '<path d="M18 6 6 18"/><circle cx="7" cy="7" r="2"/><circle cx="17" cy="17" r="2"/>',
		'truck'          => '<path d="M3 6h11v10H3zM14 10h4l3 3v3h-7"/><circle cx="7" cy="18" r="1.6"/><circle cx="17" cy="18" r="1.6"/>',
		'return'         => '<path d="M9 14 4 9l5-5"/><path d="M4 9h10a6 6 0 0 1 0 12h-3"/>',
		'clock'          => '<circle cx="12" cy="12" r="8"/><path d="M12 8v4l3 2"/>',
		'shop'           => '<path d="M4 9 5.5 4h13L20 9"/><path d="M4 9h16v1a3 3 0 0 1-5.3 1.9A3 3 0 0 1 12 13a3 3 0 0 1-2.7-1.1A3 3 0 0 1 4 10z"/><path d="M5.5 13v7h13v-7"/>',
		'copy'           => '<rect x="8" y="8" width="11" height="11" rx="1.5"/><path d="M5 15V6a1 1 0 0 1 1-1h9"/>',
		'check'          => '<path d="m5 12 4.5 4.5L19 7"/>',
		'plus'           => '<path d="M12 5v14M5 12h14"/>',
		'minus'          => '<path d="M5 12h14"/>',
		'instagram'      => '<rect x="4" y="4" width="16" height="16" rx="4.5"/><circle cx="12" cy="12" r="3.6"/><path d="M16.6 7.4h.01"/>',
		'pinterest'      => '<circle cx="12" cy="12" r="8.5"/><path d="M11 9.5c-.4 2-1.6 7-2 8.5M10.4 12.2c2.3 1.7 5.6.1 5.6-3 0-2-1.7-3.4-4-3.4-2.6 0-4.4 1.7-4.4 4 0 .9.3 1.6.8 2"/>',
		'facebook'       => '<path d="M14 8h2.5V4.5H14A3.5 3.5 0 0 0 10.5 8v2.5H8V14h2.5v6H14v-6h2.5l.5-3.5h-3V8.5A.5.5 0 0 1 14 8z"/>',
		'tiktok'         => '<path d="M14 4v10.5a3.5 3.5 0 1 1-3.5-3.5"/><path d="M14 4c.5 2.4 2.2 4 5 4.3"/>',
		'pin'            => '<path d="M12 21s-6-5.6-6-10.5a6 6 0 0 1 12 0C18 15.4 12 21 12 21z"/><circle cx="12" cy="10.5" r="2.2"/>',
		'ruler'          => '<path d="M3 16 16 3l5 5L8 21z"/><path d="m7 12 2 2M10 9l2 2M13 6l2 2"/>',
		'info'           => '<circle cx="12" cy="12" r="8.5"/><path d="M12 11v5M12 8h.01"/>',
		'sparkle'        => '<path d="M12 4v4M12 16v4M4 12h4M16 12h4M6.5 6.5l2 2M15.5 15.5l2 2M6.5 17.5l2-2M15.5 8.5l2-2"/>',
		'heart'          => '<path d="M12 19s-7-4.3-7-9.2A3.8 3.8 0 0 1 12 7.6a3.8 3.8 0 0 1 7 2.2C19 14.7 12 19 12 19z"/>',
		'trend-down'     => '<path d="m4 7 6 6 4-4 6 6"/><path d="M20 10v5h-5"/>',
		'external'       => '<path d="M14 4h6v6M20 4l-9 9"/><path d="M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
	);

	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}

	$a11y = $label
		? sprintf( 'role="img" aria-label="%s"', esc_attr( $label ) )
		: 'aria-hidden="true" focusable="false"';

	return sprintf(
		'<svg class="ni-icon ni-icon--%1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" %3$s>%4$s</svg>',
		esc_attr( $name ),
		absint( $size ),
		$a11y,
		$paths[ $name ] // Static, trusted markup defined above.
	);
}

/**
 * Echo an icon.
 *
 * @param string $name  Icon name.
 * @param int    $size  Pixel size.
 * @param string $label Optional label.
 */
function ni_the_icon( $name, $size = 20, $label = '' ) {
	echo ni_icon( $name, $size, $label ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped inside ni_icon().
}

/**
 * Allowed SVG markup for wp_kses.
 *
 * @return array
 */
function ni_svg_kses() {
	$common = array(
		'fill'            => true,
		'stroke'          => true,
		'stroke-width'    => true,
		'stroke-linecap'  => true,
		'stroke-linejoin' => true,
		'd'               => true,
		'transform'       => true,
		'class'           => true,
	);
	return array(
		'svg'    => array(
			'xmlns'           => true,
			'viewbox'         => true,
			'width'           => true,
			'height'          => true,
			'role'            => true,
			'aria-hidden'     => true,
			'aria-label'      => true,
			'aria-labelledby' => true,
			'focusable'       => true,
			'class'           => true,
			'fill'            => true,
			'stroke'          => true,
			'stroke-width'    => true,
			'stroke-linecap'  => true,
			'stroke-linejoin' => true,
		),
		'title'  => array( 'id' => true ),
		'g'      => $common,
		'path'   => $common,
		'circle' => array_merge(
			$common,
			array(
				'cx' => true,
				'cy' => true,
				'r'  => true,
			)
		),
		'rect'   => array_merge(
			$common,
			array(
				'x'      => true,
				'y'      => true,
				'width'  => true,
				'height' => true,
				'rx'     => true,
			)
		),
	);
}

/**
 * Return the inline SVG logo.
 *
 * @param string $variant "dark" (black text) or "white".
 * @param string $layout  "two-rows" or "oneline" or "icon".
 * @return string
 */
function ni_logo_svg( $variant = 'dark', $layout = 'two-rows' ) {
	$file = 'logo';
	if ( 'icon' === $layout ) {
		$file = 'logo-icon';
	}
	if ( 'white' === $variant ) {
		$file .= '-white';
	}
	if ( 'oneline' === $layout ) {
		$file .= '-oneline';
	}
	$path = NI_DIR . '/assets/img/logo/' . $file . '.svg';
	if ( ! is_readable( $path ) ) {
		return '';
	}
	$svg = (string) file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local theme file.
	// Unique title id to keep the document valid when the logo appears twice.
	static $count = 0;
	++$count;
	$svg = str_replace( array( 'id="t"', 'aria-labelledby="t"' ), array( 'id="ni-logo-title-' . $count . '"', 'aria-labelledby="ni-logo-title-' . $count . '"' ), $svg );
	$svg = str_replace( '<svg ', '<svg class="ni-logo__svg" ', $svg );
	return $svg;
}

/**
 * Print the site logo. Uses the Custom Logo when one is set, otherwise the
 * bundled SVG.
 *
 * @param string $variant dark|white.
 * @param string $context header|footer.
 */
function ni_the_logo( $variant = 'dark', $context = 'header' ) {
	$layout = 'header' === $context ? ni_mod( 'logo_variant' ) : 'two-rows';
	$custom = 'dark' === $variant ? get_theme_mod( 'custom_logo' ) : 0;

	printf( '<a class="ni-logo ni-logo--%1$s" href="%2$s" rel="home">', esc_attr( $context ), esc_url( home_url( '/' ) ) );
	if ( $custom ) {
		echo wp_get_attachment_image(
			$custom,
			'full',
			false,
			array(
				'class' => 'ni-logo__img',
				'alt'   => get_bloginfo( 'name', 'display' ),
			)
		);
	} else {
		echo wp_kses( ni_logo_svg( $variant, $layout ), ni_svg_kses() );
	}
	echo '</a>';
}

/**
 * Get a term meta value with a default.
 *
 * @param int    $term_id Term ID.
 * @param string $key     Meta key without "ni_" prefix.
 * @param mixed  $default_value Default.
 * @return mixed
 */
function ni_term_meta( $term_id, $key, $default_value = '' ) {
	$value = get_term_meta( (int) $term_id, 'ni_' . $key, true );
	return ( '' === $value || false === $value ) ? $default_value : $value;
}

/**
 * Return an <img> for a term's image meta, or a calm placeholder.
 *
 * @param WP_Term $term  Term.
 * @param string  $size  Image size.
 * @param array   $attr  Attributes.
 * @param string  $key   Meta key (image|logo).
 * @return string
 */
function ni_term_image( $term, $size = 'ni-card', $attr = array(), $key = 'image' ) {
	$id = (int) ni_term_meta( $term->term_id, $key, 0 );
	if ( ! $id && 'product_brand' === $term->taxonomy && 'logo' === $key ) {
		// WooCommerce brands store their own thumbnail.
		$id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
	}
	if ( $id ) {
		$attr = wp_parse_args(
			$attr,
			array(
				'alt'     => $term->name,
				'loading' => 'lazy',
			)
		);
		return wp_get_attachment_image( $id, $size, false, $attr );
	}
	$tone = ni_term_meta( $term->term_id, 'tone', '' );
	$tone = sanitize_hex_color( $tone ) ? $tone : 'var(--ni-linen)';
	return sprintf(
		'<span class="ni-placeholder" style="--ni-tone:%1$s" aria-hidden="true">%2$s</span>',
		esc_attr( $tone ),
		ni_icon( 'chair', 40 )
	);
}

/**
 * Ordered terms of a taxonomy using the "order" term meta.
 *
 * @param string $taxonomy Taxonomy.
 * @param array  $args     Extra get_terms args.
 * @return WP_Term[]
 */
function ni_get_terms( $taxonomy, $args = array() ) {
	if ( ! taxonomy_exists( $taxonomy ) ) {
		return array();
	}
	$args  = wp_parse_args(
		$args,
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
		)
	);
	$terms = get_terms( $args );
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	if ( empty( $args['orderby'] ) ) {
		usort(
			$terms,
			static function ( $a, $b ) {
				$oa = (int) get_term_meta( $a->term_id, 'ni_order', true );
				$ob = (int) get_term_meta( $b->term_id, 'ni_order', true );
				if ( $oa === $ob ) {
					return strcoll( $a->name, $b->name );
				}
				return $oa <=> $ob;
			}
		);
	}
	return $terms;
}

/**
 * Format a number as SEK in the shop format.
 *
 * @param float $amount Amount.
 * @return string Plain text price.
 */
function ni_format_price( $amount ) {
	if ( function_exists( 'wc_price' ) ) {
		return wp_strip_all_tags( wc_price( $amount ) );
	}
	return number_format_i18n( (float) $amount ) . ' kr';
}

/**
 * Is the request coming from a known bot?
 *
 * @return bool
 */
function ni_is_bot() {
	$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
	if ( '' === $ua ) {
		return true;
	}
	return (bool) preg_match( '/bot|crawl|spider|slurp|facebookexternalhit|embedly|quora|pinterest|bingpreview|lighthouse|headless|phantom|curl|wget|python|httpclient|monitor|preview|scan|validator|feedfetcher|ahrefs|semrush|mj12|dotbot|petal|yandex|baidu|bytespider|gptbot|ccbot|claudebot|perplexity|applebot/i', $ua );
}

/**
 * Should clicks/searches from the current user be ignored?
 *
 * @return bool
 */
function ni_ignore_current_visitor() {
	if ( ni_is_bot() ) {
		return true;
	}
	return is_user_logged_in() && current_user_can( 'edit_posts' );
}

/**
 * Normalise a Swedish search string: lowercase, trim, collapse whitespace.
 *
 * @param string $text Text.
 * @return string
 */
function ni_normalize( $text ) {
	$text = wp_strip_all_tags( (string) $text );
	$text = mb_strtolower( $text, 'UTF-8' );
	$text = preg_replace( '/[^\p{L}\p{N}&\-\s]+/u', ' ', $text );
	$text = preg_replace( '/\s+/u', ' ', (string) $text );
	return trim( (string) $text );
}

/**
 * Parse comma/newline separated list of integers.
 *
 * @param mixed $value Value.
 * @return int[]
 */
function ni_id_list( $value ) {
	if ( is_array( $value ) ) {
		return array_values( array_filter( array_map( 'absint', $value ) ) );
	}
	return array_values( array_filter( array_map( 'absint', preg_split( '/[\s,]+/', (string) $value ) ) ) );
}

/**
 * Lines of a textarea setting as an array.
 *
 * @param string $value Value.
 * @return string[]
 */
function ni_lines( $value ) {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $value ) ) ) );
}

/**
 * Get a page ID by the template file assigned to it.
 *
 * @param string $template Template file, e.g. page-templates/brands.php.
 * @return int
 */
function ni_page_by_template( $template ) {
	$cache = wp_cache_get( 'tpl_' . $template, 'nordicinterior' );
	if ( false !== $cache ) {
		return (int) $cache;
	}
	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Small, cached lookup.
			'meta_value'     => $template, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Small, cached lookup.
			'no_found_rows'  => true,
		)
	);
	$id    = $pages ? (int) $pages[0] : 0;
	wp_cache_set( 'tpl_' . $template, $id, 'nordicinterior', HOUR_IN_SECONDS );
	return $id;
}

/**
 * URL of a page by template, falling back to a given URL.
 *
 * @param string $template Template file.
 * @param string $fallback Fallback URL.
 * @return string
 */
function ni_page_url( $template, $fallback = '' ) {
	$id = ni_page_by_template( $template );
	return $id ? (string) get_permalink( $id ) : ( $fallback ? $fallback : home_url( '/' ) );
}

/**
 * Is WooCommerce active?
 *
 * @return bool
 */
function ni_wc() {
	return class_exists( 'WooCommerce' );
}
