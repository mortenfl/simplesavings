<?php
/**
 * Theme supports, menus, image sizes and general front-end clean-up.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register theme supports.
 */
function ni_setup() {
	load_theme_textdomain( 'nordicinterior', NI_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 112,
			'width'       => 380,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support( 'customize-selective-refresh-widgets' );

	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 600,
			'single_image_width'    => 1000,
			'product_grid'          => array(
				'default_columns' => 4,
				'min_columns'     => 2,
				'max_columns'     => 5,
			),
		)
	);
	// The theme ships its own dependency-free gallery (no jQuery on the front end).

	add_editor_style( array( 'assets/css/editor.css' ) );

	register_nav_menus(
		array(
			'primary' => __( 'Huvudmeny', 'nordicinterior' ),
			'mobile'  => __( 'Mobilmeny', 'nordicinterior' ),
			'footer'  => __( 'Sidfotsmeny', 'nordicinterior' ),
		)
	);

	// 4:5 and 1:1 product crops, plus editorial sizes.
	add_image_size( 'ni-card', 600, 750, true );
	add_image_size( 'ni-square', 600, 600, true );
	add_image_size( 'ni-wide', 1200, 750, true );
	add_image_size( 'ni-hero', 1920, 1000, true );
	add_image_size( 'ni-logo', 320, 160, false );

	add_theme_support(
		'editor-color-palette',
		array(
			array(
				'name'  => __( 'Bläck', 'nordicinterior' ),
				'slug'  => 'ink',
				'color' => '#171716',
			),
			array(
				'name'  => __( 'Linne', 'nordicinterior' ),
				'slug'  => 'linen',
				'color' => '#EAE1D2',
			),
			array(
				'name'  => __( 'Havre', 'nordicinterior' ),
				'slug'  => 'oat',
				'color' => '#F7F3EC',
			),
			array(
				'name'  => __( 'Salvia', 'nordicinterior' ),
				'slug'  => 'sage',
				'color' => '#8A9A84',
			),
			array(
				'name'  => __( 'Ek', 'nordicinterior' ),
				'slug'  => 'oak',
				'color' => '#9C7B5B',
			),
			array(
				'name'  => __( 'Lera', 'nordicinterior' ),
				'slug'  => 'clay',
				'color' => '#A8553F',
			),
		)
	);
}
add_action( 'after_setup_theme', 'ni_setup' );

/**
 * Content width.
 */
function ni_content_width() {
	$GLOBALS['content_width'] = (int) apply_filters( 'ni_content_width', 760 );
}
add_action( 'after_setup_theme', 'ni_content_width', 0 );

/**
 * Body classes for layout options.
 *
 * @param string[] $classes Classes.
 * @return string[]
 */
function ni_body_classes( $classes ) {
	$classes[] = 'ni-header--' . sanitize_html_class( ni_mod( 'header_layout' ) );
	$classes[] = 'ni-cards--' . sanitize_html_class( ni_mod( 'card_style' ) );
	$classes[] = 'ni-ratio--' . sanitize_html_class( ni_mod( 'image_ratio' ) );
	$classes[] = 'ni-buttons--' . sanitize_html_class( ni_mod( 'button_style' ) );
	$classes[] = 'ni-filters--' . sanitize_html_class( ni_mod( 'filter_position' ) );
	if ( ni_mod( 'header_sticky' ) ) {
		$classes[] = 'ni-sticky-header';
	}
	if ( ! ni_mod( 'borders' ) ) {
		$classes[] = 'ni-no-borders';
	}
	if ( ni_mod( 'bottom_nav' ) ) {
		$classes[] = 'ni-has-bottom-nav';
	}
	if ( ni_mod( 'heading_uppercase' ) ) {
		$classes[] = 'ni-uc-headings';
	}
	return $classes;
}
add_filter( 'body_class', 'ni_body_classes' );

/**
 * Favicon / app icon fallback when no Site Icon is configured.
 */
function ni_favicon() {
	if ( has_site_icon() ) {
		return;
	}
	printf( '<link rel="icon" href="%s" type="image/svg+xml">' . "\n", esc_url( NI_URI . '/assets/img/logo/favicon.svg' ) );
	printf( '<link rel="icon" href="%s" sizes="32x32" type="image/png">' . "\n", esc_url( NI_URI . '/assets/img/icons/favicon-32.png' ) );
	printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( NI_URI . '/assets/img/icons/apple-touch-icon.png' ) );
	echo '<meta name="theme-color" content="#F7F3EC">' . "\n";
}
add_action( 'wp_head', 'ni_favicon', 2 );
add_action( 'admin_head', 'ni_favicon', 2 );

/**
 * Remove emoji scripts when enabled under Theme Adjustments → Prestanda.
 */
function ni_performance_cleanup() {
	if ( ni_mod( 'perf_disable_emoji' ) ) {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
	}
}
add_action( 'init', 'ni_performance_cleanup' );

/**
 * Excerpt length / more for editorial cards.
 *
 * @return int
 */
function ni_excerpt_length() {
	return 24;
}
add_filter( 'excerpt_length', 'ni_excerpt_length' );

/**
 * Replace "[...]" with an ellipsis.
 *
 * @return string
 */
function ni_excerpt_more() {
	return '…';
}
add_filter( 'excerpt_more', 'ni_excerpt_more' );

/**
 * Skip link target and a11y helpers are printed in header.php.
 * Here we just make sure the "Inspiration" posts page has a sensible title.
 *
 * @param array $title Title parts.
 * @return array
 */
function ni_document_title_parts( $title ) {
	if ( is_home() && ! is_front_page() && empty( $title['title'] ) ) {
		$title['title'] = __( 'Inspiration', 'nordicinterior' );
	}
	return $title;
}
add_filter( 'document_title_parts', 'ni_document_title_parts' );

/**
 * Generate WebP sub-sizes for uploaded JPEG/PNG images (WordPress 6.1+),
 * when the server's image editor supports it. Toggle under
 * Theme Adjustments → Prestanda.
 *
 * @param array $formats Mime type map.
 * @return array
 */
function ni_image_output_format( $formats ) {
	if ( ni_mod( 'perf_webp' ) && wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
		$formats['image/jpeg'] = 'image/webp';
		$formats['image/png']  = 'image/webp';
	}
	return $formats;
}
add_filter( 'image_editor_output_format', 'ni_image_output_format' );
