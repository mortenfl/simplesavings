<?php
/**
 * Styles, scripts, local fonts and CSS custom properties.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Locally hosted fonts. Only the weights the design uses are shipped.
 *
 * @return array<string, array{family:string, stack:string, files:array<int,string>}>
 */
function ni_fonts() {
	return array(
		'jost'   => array(
			'label'  => 'Jost',
			'family' => 'Jost',
			'stack'  => '"Jost", "Futura", "Century Gothic", system-ui, sans-serif',
			'files'  => array(
				400 => 'jost-latin-400-normal.woff2',
				500 => 'jost-latin-500-normal.woff2',
			),
		),
		'inter'  => array(
			'label'  => 'Inter',
			'family' => 'Inter',
			'stack'  => '"Inter", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif',
			'files'  => array(
				400 => 'inter-latin-400-normal.woff2',
				500 => 'inter-latin-500-normal.woff2',
				600 => 'inter-latin-600-normal.woff2',
			),
		),
		'dmsans' => array(
			'label'  => 'DM Sans',
			'family' => 'DM Sans',
			'stack'  => '"DM Sans", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif',
			'files'  => array(
				400 => 'dm-sans-latin-400-normal.woff2',
				500 => 'dm-sans-latin-500-normal.woff2',
			),
		),
		'system' => array(
			'label'  => __( 'Systemtypsnitt', 'nordicinterior' ),
			'family' => '',
			'stack'  => 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif',
			'files'  => array(),
		),
	);
}

/**
 * The @font-face rules for the selected fonts only.
 *
 * @return string
 */
function ni_font_face_css() {
	$fonts = ni_fonts();
	$used  = array_unique( array( ni_mod( 'font_heading' ), ni_mod( 'font_body' ) ) );
	$css   = '';
	foreach ( $used as $key ) {
		if ( empty( $fonts[ $key ]['files'] ) ) {
			continue;
		}
		foreach ( $fonts[ $key ]['files'] as $weight => $file ) {
			$css .= sprintf(
				'@font-face{font-family:"%1$s";font-style:normal;font-weight:%2$d;font-display:swap;src:url("%3$s") format("woff2");unicode-range:U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+2000-206F,U+2074,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD;}',
				esc_attr( $fonts[ $key ]['family'] ),
				(int) $weight,
				esc_url( NI_URI . '/assets/fonts/' . $file )
			);
		}
	}
	return $css;
}

/**
 * Build the :root custom properties from Theme Adjustments.
 *
 * @return string
 */
function ni_css_variables() {
	$c     = ni_colors();
	$fonts = ni_fonts();
	$hf    = $fonts[ ni_mod( 'font_heading' ) ] ?? $fonts['jost'];
	$bf    = $fonts[ ni_mod( 'font_body' ) ] ?? $fonts['inter'];

	$spacing = array(
		'compact' => 0.8,
		'normal'  => 1,
		'airy'    => 1.25,
	);
	$space   = $spacing[ ni_mod( 'spacing' ) ] ?? 1;

	$vars = array(
		'--ni-ink'              => $c['ink'],
		'--ni-text'             => $c['text'],
		'--ni-muted'            => $c['muted'],
		'--ni-linen'            => $c['linen'],
		'--ni-oat'              => $c['oat'],
		'--ni-surface'          => $c['surface'],
		'--ni-line'             => $c['line'],
		'--ni-sage'             => $c['sage'],
		'--ni-oak'              => $c['oak'],
		'--ni-clay'             => $c['clay'],
		'--ni-dark'             => $c['dark'],
		'--ni-footer-bg'        => sanitize_hex_color( ni_mod( 'footer_bg' ) ) ? ni_mod( 'footer_bg' ) : $c['dark'],
		'--ni-footer-text'      => sanitize_hex_color( ni_mod( 'footer_text' ) ) ? ni_mod( 'footer_text' ) : '#E9E3D8',
		'--ni-font-heading'     => $hf['stack'],
		'--ni-font-body'        => $bf['stack'],
		'--ni-font-size'        => absint( ni_mod( 'font_size' ) ) . 'px',
		'--ni-scale'            => (string) (float) ni_mod( 'type_scale' ),
		'--ni-line-height'      => (string) (float) ni_mod( 'line_height' ),
		'--ni-heading-tracking' => (float) ni_mod( 'heading_tracking' ) . 'em',
		'--ni-heading-case'     => ni_mod( 'heading_uppercase' ) ? 'uppercase' : 'none',
		'--ni-radius'           => min( 8, absint( ni_mod( 'radius' ) ) ) . 'px',
		'--ni-border-width'     => ni_mod( 'borders' ) ? '1px' : '0px',
		'--ni-space'            => (string) $space,
		'--ni-logo-width'       => absint( ni_mod( 'logo_width' ) ) . 'px',
		'--ni-cols-desktop'     => (string) max( 2, min( 6, absint( ni_mod( 'cols_desktop' ) ) ) ),
		'--ni-cols-tablet'      => (string) max( 2, min( 4, absint( ni_mod( 'cols_tablet' ) ) ) ),
		'--ni-cols-mobile'      => (string) max( 1, min( 2, absint( ni_mod( 'cols_mobile' ) ) ) ),
		'--ni-ratio'            => '1-1' === ni_mod( 'image_ratio' ) ? '1 / 1' : '4 / 5',
	);

	$out = ':root{';
	foreach ( $vars as $name => $value ) {
		$out .= $name . ':' . str_replace( array( '<', '>', '{', '}' ), '', (string) $value ) . ';';
	}
	$out .= '}';
	return $out;
}

/**
 * Enqueue front-end assets.
 */
function ni_enqueue_assets() {
	$ver = NI_VERSION;

	wp_enqueue_style( 'nordicinterior', NI_URI . '/assets/css/main.css', array(), $ver );
	wp_add_inline_style( 'nordicinterior', ni_font_face_css() . ni_css_variables() );

	wp_enqueue_script(
		'nordicinterior',
		NI_URI . '/assets/js/main.js',
		array(),
		$ver,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
	wp_enqueue_script(
		'ni-track',
		NI_URI . '/assets/js/track.js',
		array(),
		$ver,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
	wp_enqueue_script(
		'ni-search',
		NI_URI . '/assets/js/search.js',
		array(),
		$ver,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	$config = array(
		'rest'      => esc_url_raw( rest_url( 'nordicinterior/v1/' ) ),
		'nonce'     => is_user_logged_in() ? wp_create_nonce( 'wp_rest' ) : '',
		'context'   => ni_page_tracking_context(),
		'searchUrl' => esc_url_raw( home_url( '/' ) ),
		'i18n'      => array(
			'copied'        => __( 'Kopierad!', 'nordicinterior' ),
			'copy'          => __( 'Kopiera', 'nordicinterior' ),
			'noResults'     => __( 'Inga förslag. Tryck Enter för att söka.', 'nordicinterior' ),
			/* translators: %d: number of suggestions. */
			'results'       => __( '%d förslag', 'nordicinterior' ),
			/* translators: %s: search query. */
			'seeAll'        => __( 'Visa alla resultat för ”%s”', 'nordicinterior' ),
			'loading'       => __( 'Laddar …', 'nordicinterior' ),
			'groups'        => array(
				'product'       => __( 'Produkter', 'nordicinterior' ),
				'product_cat'   => __( 'Kategorier', 'nordicinterior' ),
				'ni_room'       => __( 'Rum', 'nordicinterior' ),
				'ni_style'      => __( 'Stilar', 'nordicinterior' ),
				'product_brand' => __( 'Varumärken', 'nordicinterior' ),
				'post'          => __( 'Artiklar', 'nordicinterior' ),
			),
			'didYouMean'    => __( 'Menade du', 'nordicinterior' ),
			'days'          => __( 'd', 'nordicinterior' ),
			'ended'         => __( 'Kampanjen har avslutats', 'nordicinterior' ),
			'showMore'      => __( 'Ladda fler', 'nordicinterior' ),
			'filtersOpen'   => __( 'Filter', 'nordicinterior' ),
			/* translators: %d: number of products. */
			'productsFound' => __( '%d produkter', 'nordicinterior' ),
			'error'         => __( 'Något gick fel. Försök igen.', 'nordicinterior' ),
		),
	);
	wp_add_inline_script( 'nordicinterior', 'window.niConfig=' . wp_json_encode( $config ) . ';', 'before' );

	if ( ni_is_listing() ) {
		wp_enqueue_script(
			'ni-filters',
			NI_URI . '/assets/js/filters.js',
			array( 'nordicinterior' ),
			$ver,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
	}

	if ( ( is_singular() && ( has_shortcode( (string) get_post_field( 'post_content' ), 'ni_shop_the_look' ) || has_block( 'nordicinterior/shop-the-look' ) ) ) || is_front_page() ) {
		wp_enqueue_script(
			'ni-look',
			NI_URI . '/assets/js/look.js',
			array( 'nordicinterior' ),
			$ver,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	// Remove WooCommerce/Gutenberg styles we do not need.
	if ( ni_mod( 'perf_disable_wc_blocks' ) ) {
		wp_dequeue_style( 'wc-blocks-style' );
		wp_dequeue_style( 'wc-blocks-vendors-style' );
		if ( ! is_singular() ) {
			wp_dequeue_style( 'wp-block-library' );
			wp_dequeue_style( 'classic-theme-styles' );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'ni_enqueue_assets', 20 );

/**
 * Do not load WooCommerce's general/layout styles – the theme styles
 * everything it shows.
 *
 * @param array $styles Styles.
 * @return array
 */
function ni_woocommerce_styles( $styles ) {
	unset( $styles['woocommerce-general'], $styles['woocommerce-layout'], $styles['woocommerce-smallscreen'] );
	return $styles;
}
add_filter( 'woocommerce_enqueue_styles', 'ni_woocommerce_styles' );

/**
 * Preload the fonts that render above the fold.
 */
function ni_preload_fonts() {
	if ( ! ni_mod( 'perf_preload_fonts' ) ) {
		return;
	}
	$fonts = ni_fonts();
	$pairs = array(
		array( ni_mod( 'font_heading' ), 500 ),
		array( ni_mod( 'font_body' ), 400 ),
	);
	foreach ( $pairs as $pair ) {
		$file = $fonts[ $pair[0] ]['files'][ $pair[1] ] ?? '';
		if ( $file ) {
			printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( NI_URI . '/assets/fonts/' . $file ) );
		}
	}
}
add_action( 'wp_head', 'ni_preload_fonts', 1 );

/**
 * Mark theme scripts as strictly necessary for Cookiebot's auto-blocker
 * (tracking is anonymous and first party), and Complianz's script center.
 *
 * @param string $tag    Script tag.
 * @param string $handle Handle.
 * @return string
 */
function ni_script_tag( $tag, $handle ) {
	if ( in_array( $handle, array( 'nordicinterior', 'ni-track', 'ni-search', 'ni-filters', 'ni-look' ), true ) ) {
		$tag = str_replace( '<script ', '<script data-cookieconsent="ignore" data-category="functional" ', $tag );
	}
	return $tag;
}
add_filter( 'script_loader_tag', 'ni_script_tag', 10, 2 );

/**
 * Admin assets.
 *
 * @param string $hook Current admin page.
 */
function ni_admin_assets( $hook ) {
	wp_enqueue_style( 'ni-admin', NI_URI . '/assets/css/admin.css', array(), NI_VERSION );
	$screens = array( 'edit-tags.php', 'term.php', 'post.php', 'post-new.php', 'edit.php', 'nav-menus.php' );
	if ( in_array( $hook, $screens, true ) || false !== strpos( $hook, 'nordicinterior' ) ) {
		wp_enqueue_media();
		wp_enqueue_script( 'ni-admin', NI_URI . '/assets/js/admin.js', array(), NI_VERSION, true );
		wp_localize_script(
			'ni-admin',
			'niAdmin',
			array(
				'choose' => __( 'Välj bild', 'nordicinterior' ),
				'use'    => __( 'Använd bild', 'nordicinterior' ),
				'remove' => __( 'Ta bort', 'nordicinterior' ),
				'point'  => __( 'Produkt-ID för punkten', 'nordicinterior' ),
			)
		);
	}
}
add_action( 'admin_enqueue_scripts', 'ni_admin_assets' );

/**
 * Tracking context of the current page (room/style/placement) used by the
 * click beacon.
 *
 * @return array<string,string>
 */
function ni_page_tracking_context() {
	$ctx = array(
		'room'  => '',
		'style' => '',
		'page'  => '',
	);
	if ( is_front_page() ) {
		$ctx['page'] = 'home';
	} elseif ( is_search() ) {
		$ctx['page'] = 'search';
	} elseif ( function_exists( 'is_product' ) && is_product() ) {
		$ctx['page'] = 'product';
	} elseif ( is_singular( 'post' ) ) {
		$ctx['page'] = 'post';
	}
	$room  = get_query_var( 'ni_room' );
	$style = get_query_var( 'ni_style' );
	if ( $room ) {
		$ctx['room'] = sanitize_title( is_array( $room ) ? reset( $room ) : $room );
	}
	if ( $style ) {
		$ctx['style'] = sanitize_title( is_array( $style ) ? reset( $style ) : $style );
	}
	/**
	 * Filter the tracking context passed to track.js.
	 *
	 * @param array $ctx Context.
	 */
	return apply_filters( 'ni_tracking_context', $ctx );
}

/**
 * Flag JavaScript support as early as possible (avoids layout shifts for
 * the filter drawer and menus).
 */
function ni_js_class() {
	wp_print_inline_script_tag( "document.documentElement.classList.add('ni-js');" );
}
add_action( 'wp_head', 'ni_js_class', 0 );
