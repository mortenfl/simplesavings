<?php
/**
 * Widget areas and theme widgets.
 *
 * Footer: three areas (Om oss, Länklista, Nyhetsbrev). Sidebars: Blogg,
 * Varumärken, Butiker, Rea. Empty areas show default content.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register widget areas.
 */
function ni_register_sidebars() {
	$common = array(
		'before_widget' => '<section id="%1$s" class="ni-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="ni-widget__title">',
		'after_title'   => '</h2>',
	);
	$areas  = array(
		'footer-1'       => array( __( 'Sidfot 1 – Om oss', 'nordicinterior' ), __( 'Logotyp, text och sociala länkar.', 'nordicinterior' ) ),
		'footer-2'       => array( __( 'Sidfot 2 – Länklista', 'nordicinterior' ), __( 'Rum, stilar, kategorier, varumärken eller butiker.', 'nordicinterior' ) ),
		'footer-3'       => array( __( 'Sidfot 3 – Nyhetsbrev', 'nordicinterior' ), __( 'Text och formulär-shortcode.', 'nordicinterior' ) ),
		'sidebar-blog'   => array( __( 'Blogg-sidofält', 'nordicinterior' ), __( 'Visas till höger om inlägg.', 'nordicinterior' ) ),
		'sidebar-brands' => array( __( 'Varumärken-sidofält', 'nordicinterior' ), __( 'Visas på Alla varumärken.', 'nordicinterior' ) ),
		'sidebar-stores' => array( __( 'Butiker-sidofält', 'nordicinterior' ), __( 'Visas på Alla butiker.', 'nordicinterior' ) ),
		'sidebar-sale'   => array( __( 'Rea-sidofält', 'nordicinterior' ), __( 'Visas på Rea-sidan.', 'nordicinterior' ) ),
	);
	foreach ( $areas as $id => $def ) {
		register_sidebar(
			array_merge(
				$common,
				array(
					'id'          => $id,
					'name'        => $def[0],
					'description' => $def[1],
				)
			)
		);
	}

	foreach ( array( 'about', 'links', 'newsletter', 'campaign', 'terms', 'inspiration' ) as $widget ) {
		require_once NI_DIR . '/inc/widgets/class-ni-widget-' . $widget . '.php';
	}
	register_widget( 'NI_Widget_About' );
	register_widget( 'NI_Widget_Links' );
	register_widget( 'NI_Widget_Newsletter' );
	register_widget( 'NI_Widget_Campaign' );
	register_widget( 'NI_Widget_Terms' );
	register_widget( 'NI_Widget_Inspiration' );
}
add_action( 'widgets_init', 'ni_register_sidebars' );

/**
 * Print a sidebar or its default content.
 *
 * @param string $id Sidebar ID.
 */
function ni_sidebar( $id ) {
	if ( is_active_sidebar( $id ) ) {
		dynamic_sidebar( $id );
		return;
	}
	$default_args = array(
		'before_widget' => '<section class="ni-widget">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="ni-widget__title">',
		'after_title'   => '</h2>',
	);
	switch ( $id ) {
		case 'footer-1':
			the_widget( 'NI_Widget_About', array(), $default_args );
			break;
		case 'footer-2':
			echo '<div class="ni-footer-links">';
			the_widget(
				'NI_Widget_Links',
				array(
					'title'  => __( 'Rum', 'nordicinterior' ),
					'source' => 'ni_room',
					'count'  => 9,
				),
				$default_args
			);
			the_widget(
				'NI_Widget_Links',
				array(
					'title'  => __( 'Stilar', 'nordicinterior' ),
					'source' => 'ni_style',
					'count'  => 8,
				),
				$default_args
			);
			echo '</div>';
			break;
		case 'footer-3':
			the_widget( 'NI_Widget_Newsletter', array(), $default_args );
			break;
		default:
			the_widget( 'NI_Widget_Campaign', array(), $default_args );
			the_widget( 'NI_Widget_Terms', array(), $default_args );
			if ( 'sidebar-blog' === $id ) {
				the_widget( 'NI_Widget_Inspiration', array(), $default_args );
			}
	}
}

/**
 * Social links (Instagram and Pinterest first).
 */
function ni_social_links() {
	$links = array(
		'instagram' => array( ni_mod( 'social_instagram' ), 'Instagram' ),
		'pinterest' => array( ni_mod( 'social_pinterest' ), 'Pinterest' ),
		'facebook'  => array( ni_mod( 'social_facebook' ), 'Facebook' ),
		'tiktok'    => array( ni_mod( 'social_tiktok' ), 'TikTok' ),
	);
	$items = '';
	foreach ( $links as $icon => $link ) {
		if ( $link[0] ) {
			$items .= sprintf( '<li><a href="%1$s" rel="noopener me" target="_blank">%2$s</a></li>', esc_url( (string) $link[0] ), ni_icon( $icon, 22, $link[1] ) );
		}
	}
	if ( $items ) {
		echo '<ul class="ni-social" role="list">' . $items . '</ul>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above.
	}
}
