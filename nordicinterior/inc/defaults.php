<?php
/**
 * Default values for every Theme Adjustments (Customizer) setting.
 *
 * Every setting is stored as a theme mod with the prefix "ni_". ni_mod()
 * always falls back to the value defined here, so templates never have to
 * repeat defaults.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Colour schemes offered under Theme Adjustments → Färger.
 *
 * @return array<string, array{label:string, colors:array<string,string>}>
 */
function ni_color_schemes() {
	return array(
		'linne'  => array(
			'label'  => __( 'Linne (standard)', 'nordicinterior' ),
			'colors' => array(
				'ink'     => '#171716',
				'text'    => '#3A3734',
				'muted'   => '#655E56',
				'linen'   => '#EAE1D2',
				'oat'     => '#F7F3EC',
				'surface' => '#F0EAE0',
				'line'    => '#DDD3C3',
				'sage'    => '#8A9A84',
				'oak'     => '#9C7B5B',
				'clay'    => '#A8553F',
				'dark'    => '#2A2826',
			),
		),
		'salvia' => array(
			'label'  => __( 'Salvia', 'nordicinterior' ),
			'colors' => array(
				'ink'     => '#1B1E1A',
				'text'    => '#363A34',
				'muted'   => '#646B60',
				'linen'   => '#DFE3D8',
				'oat'     => '#F4F5F0',
				'surface' => '#E9ECE4',
				'line'    => '#CED4C6',
				'sage'    => '#6F8169',
				'oak'     => '#8C7354',
				'clay'    => '#A4513C',
				'dark'    => '#262A25',
			),
		),
		'ek'     => array(
			'label'  => __( 'Ek', 'nordicinterior' ),
			'colors' => array(
				'ink'     => '#1C1712',
				'text'    => '#3D342B',
				'muted'   => '#6F6254',
				'linen'   => '#E6D6C0',
				'oat'     => '#F8F2E9',
				'surface' => '#EFE4D4',
				'line'    => '#DCCBB3',
				'sage'    => '#87957C',
				'oak'     => '#8E6A47',
				'clay'    => '#A24F38',
				'dark'    => '#2D251E',
			),
		),
		'sand'   => array(
			'label'  => __( 'Sand', 'nordicinterior' ),
			'colors' => array(
				'ink'     => '#1A1916',
				'text'    => '#3B3933',
				'muted'   => '#6B675D',
				'linen'   => '#EDE6D6',
				'oat'     => '#FAF8F2',
				'surface' => '#F3EEE3',
				'line'    => '#E0D8C6',
				'sage'    => '#86977F',
				'oak'     => '#977B56',
				'clay'    => '#A9553E',
				'dark'    => '#2B2A26',
			),
		),
	);
}

/**
 * All setting defaults.
 *
 * Note on "muted": the brief specifies #7A736A. On the oat background that
 * colour reaches 4.2:1, just below WCAG AA for body-size text, so the default
 * is darkened slightly to #655E56 (5.8:1 on oat, 4.9:1 on linen). #7A736A is still used for large
 * text and decorative strokes.
 *
 * @return array<string, mixed>
 */
function ni_defaults() {
	static $defaults = null;
	if ( null !== $defaults ) {
		return $defaults;
	}

	$colors = ni_color_schemes()['linne']['colors'];

	$defaults = array(
		// Colours.
		'color_scheme'           => 'linne',
		// Typography.
		'font_heading'           => 'jost',
		'font_body'              => 'inter',
		'font_size'              => 17,
		'type_scale'             => 1.25,
		'line_height'            => 1.65,
		'heading_tracking'       => 0.12,
		'heading_uppercase'      => true,
		// Form.
		'radius'                 => 2,
		'borders'                => true,
		'spacing'                => 'normal',
		'button_style'           => 'filled',
		// Header.
		'header_layout'          => 'left',
		'header_sticky'          => true,
		'logo_width'             => 190,
		'logo_variant'           => 'two-rows',
		'rooms_button_text'      => __( 'Alla rum', 'nordicinterior' ),
		'rooms_button'           => true,
		'topbar'                 => true,
		'topbar_text'            => __( 'Jämför inredning från Nordens bästa butiker', 'nordicinterior' ),
		'topbar_text_2'          => __( 'Handplockat av inredare', 'nordicinterior' ),
		'bottom_nav'             => true,
		// Footer.
		'footer_bg'              => $colors['dark'],
		'footer_text'            => '#E9E3D8',
		'copyright'              => __( '© {year} Nordic Interior. Alla rättigheter förbehållna.', 'nordicinterior' ),
		'social_instagram'       => 'https://www.instagram.com/',
		'social_pinterest'       => 'https://www.pinterest.se/',
		'social_facebook'        => '',
		'social_tiktok'          => '',
		// Products.
		'card_style'             => 'minimal',
		'image_ratio'            => '4-5',
		'cols_desktop'           => 4,
		'cols_tablet'            => 3,
		'cols_mobile'            => 2,
		'card_show_brand'        => true,
		'card_show_store'        => true,
		'card_show_colors'       => true,
		'card_show_cta'          => true,
		'card_cta_style'         => 'link',
		'cta_text'               => __( 'Till butiken', 'nordicinterior' ),
		'filter_position'        => 'left',
		'pagination'             => 'loadmore',
		'per_page'               => 24,
		// Search.
		'search_placeholder'     => __( 'Sök soffa, lampa, matta, varumärke …', 'nordicinterior' ),
		'search_groups'          => 'product,product_cat,ni_room,ni_style,product_brand,post',
		'popular_searches'       => __( "Bouclé\nSoffbord\nTaklampa\nUllmatta\nJapandi\nSänggavel", 'nordicinterior' ),
		'popular_auto'           => true,
		'synonyms'               => __( "byrå, kommod\npall, puff\nsoffa, soffor, sofa\nlampa, belysning, armatur\nmatta, mattor\nsoffbord, vardagsrumsbord\nsänggavel, huvudgavel\ngarderob, klädskåp", 'nordicinterior' ),
		// Affiliate.
		'new_tab'                => true,
		'disclosure_text'        => __( 'Nordic Interior innehåller affiliatelänkar. När du klickar vidare och handlar hos en butik kan vi få provision. Det kostar inget extra för dig och påverkar aldrig vilka produkter vi visar.', 'nordicinterior' ),
		'disclosure_footer'      => true,
		'disclosure_product'     => true,
		'disclosure_listing'     => true,
		'disclosure_post'        => true,
		'go_prefix'              => 'go',
		'campaign_params'        => 'utm_source=nordicinterior&utm_medium=affiliate&utm_campaign={slug}',
		'campaign_subid'         => '',
		// Front page.
		'home_sections'          => 'hero,rooms,styles,campaigns,products,look,inspiration,brands,categories,newsletter',
		'home_sections_off'      => '',
		'hero_title'             => __( 'Inred med omtanke', 'nordicinterior' ),
		'hero_text'              => __( 'Möbler och inredning från Nordens bästa butiker – samlat, sorterat efter rum och stil.', 'nordicinterior' ),
		'hero_image_1'           => 0,
		'hero_image_2'           => 0,
		'hero_image_3'           => 0,
		'hero_slider'            => false,
		'rooms_title'            => __( 'Shoppa efter rum', 'nordicinterior' ),
		'rooms_source'           => 'flagged',
		'rooms_count'            => 5,
		'styles_title'           => __( 'Shoppa efter stil', 'nordicinterior' ),
		'styles_count'           => 8,
		'products_title'         => __( 'Populärt just nu', 'nordicinterior' ),
		'products_source'        => 'popular',
		'products_ids'           => '',
		'products_tabs_by'       => 'room',
		'products_tabs'          => 'vardagsrum,sovrum,kok,matrum,hemmakontor',
		'products_per_tab'       => 8,
		'look_post'              => 0,
		'inspiration_title'      => __( 'Inspiration', 'nordicinterior' ),
		'brands_title'           => __( 'Varumärken', 'nordicinterior' ),
		'categories_title'       => __( 'Kategorier', 'nordicinterior' ),
		'newsletter_title'       => __( 'Inspiration i inkorgen', 'nordicinterior' ),
		'newsletter_text'        => __( 'Nyheter, guider och de bästa kampanjerna – en gång i veckan.', 'nordicinterior' ),
		'newsletter_shortcode'   => '',
		// Posts.
		'post_sidebar'           => 'right',
		// Performance.
		'perf_preload_fonts'     => true,
		'perf_disable_emoji'     => true,
		'perf_disable_wc_blocks' => true,
		'perf_hero_preload'      => true,
		'perf_webp'              => true,
	);

	/**
	 * Filters theme defaults.
	 *
	 * @param array $defaults Default values.
	 */
	$defaults = apply_filters( 'ni_defaults', $defaults );

	return $defaults;
}

/**
 * Read a theme setting with its default.
 *
 * @param string $key Setting key without the "ni_" prefix.
 * @return mixed
 */
function ni_mod( $key ) {
	$defaults = ni_defaults();
	$default  = $defaults[ $key ] ?? null;
	return get_theme_mod( 'ni_' . $key, $default );
}

/**
 * Current colour palette (scheme + per-colour overrides).
 *
 * @return array<string,string>
 */
function ni_colors() {
	$schemes = ni_color_schemes();
	$scheme  = ni_mod( 'color_scheme' );
	$base    = isset( $schemes[ $scheme ] ) ? $schemes[ $scheme ]['colors'] : $schemes['linne']['colors'];
	foreach ( $base as $key => $value ) {
		$custom = get_theme_mod( 'ni_color_' . $key, '' );
		if ( $custom ) {
			$base[ $key ] = sanitize_hex_color( $custom ) ? $custom : $value;
		}
	}
	return $base;
}
