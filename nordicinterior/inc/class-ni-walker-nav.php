<?php
/**
 * Header navigation walker: mega menus, dropdowns and accessible toggles.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Header navigation walker: mega menus, dropdowns and accessible toggles.
 */
class NI_Walker_Nav extends Walker_Nav_Menu {

	/**
	 * Current top-level item.
	 *
	 * @var WP_Post|null
	 */
	private $top = null;

	/**
	 * Is the current top-level item a mega menu?
	 *
	 * @return bool
	 */
	private function is_mega() {
		return $this->top && get_post_meta( $this->top->ID, '_ni_mega', true );
	}

	/**
	 * Start level.
	 *
	 * @param string   $output Output.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Args.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$mobile = ! empty( $args->ni_mobile );
		if ( 0 === $depth && $this->top ) {
			$id = 'ni-sub-' . $this->top->ID . ( $mobile ? '-m' : '' );
			if ( $this->is_mega() && ! $mobile ) {
				$cols    = max( 2, min( 5, (int) get_post_meta( $this->top->ID, '_ni_columns', true ) ) );
				$output .= '<div class="ni-mega" id="' . esc_attr( $id ) . '"><div class="ni-container ni-mega__inner"><ul class="ni-mega__cols" style="--cols:' . esc_attr( (string) $cols ) . '">';
				return;
			}
			$output .= '<ul class="sub-menu" id="' . esc_attr( $id ) . '">';
			return;
		}
		$output .= '<ul class="sub-menu sub-menu--' . esc_attr( (string) ( $depth + 1 ) ) . '">';
	}

	/**
	 * End level.
	 *
	 * @param string   $output Output.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Args.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		if ( 0 === $depth && $this->is_mega() && empty( $args->ni_mobile ) ) {
			$output .= '</ul>';
			$image   = (int) get_post_meta( $this->top->ID, '_ni_image', true );
			if ( $image ) {
				$text    = (string) get_post_meta( $this->top->ID, '_ni_image_text', true );
				$link    = (string) get_post_meta( $this->top->ID, '_ni_image_link', true );
				$link    = $link ? $link : (string) $this->top->url;
				$output .= '<a class="ni-mega__promo" href="' . esc_url( $link ) . '">' . wp_get_attachment_image(
					$image,
					'ni-card',
					false,
					array(
						'loading' => 'lazy',
						'alt'     => '',
					)
				) . ( $text ? '<span class="ni-mega__promo-text">' . esc_html( $text ) . ' ' . ni_icon( 'arrow-right', 16 ) . '</span>' : '' ) . '</a>';
			}
			$output .= '</div></div>';
			return;
		}
		$output .= '</ul>';
	}

	/**
	 * Start element.
	 *
	 * @param string   $output            Output.
	 * @param WP_Post  $data_object       Item.
	 * @param int      $depth             Depth.
	 * @param stdClass $args              Args.
	 * @param int      $current_object_id Current ID.
	 */
	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		$item    = $data_object;
		$mobile  = ! empty( $args->ni_mobile );
		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes = array_filter( $classes );
		$has_sub = in_array( 'menu-item-has-children', $classes, true );
		$sale    = in_array( 'ni-sale', $classes, true ) || 'rea' === mb_strtolower( trim( wp_strip_all_tags( $item->title ) ) );

		if ( 0 === $depth ) {
			$this->top = $item;
			if ( $this->is_mega() ) {
				$classes[] = 'is-mega';
			}
		}
		if ( $sale ) {
			$classes[] = 'ni-sale';
		}
		if ( 1 === $depth && $this->is_mega() && ! $mobile ) {
			$classes[] = 'ni-mega__col';
		}

		$output .= '<li class="' . esc_attr( implode( ' ', array_unique( $classes ) ) ) . '">';

		$atts      = array(
			'href'         => ! empty( $item->url ) ? $item->url : '',
			'target'       => ! empty( $item->target ) ? $item->target : '',
			'rel'          => ! empty( $item->xfn ) ? $item->xfn : '',
			'aria-current' => $item->current ? 'page' : '',
			'class'        => ( 1 === $depth && $this->is_mega() && ! $mobile ) ? 'ni-mega__heading' : '',
		);
		$attr_html = '';
		foreach ( $atts as $name => $value ) {
			if ( '' !== $value ) {
				$attr_html .= ' ' . $name . '="' . ( 'href' === $name ? esc_url( $value ) : esc_attr( $value ) ) . '"';
			}
		}
		$title   = apply_filters( 'nav_menu_item_title', $item->title, $item, $args, $depth ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core hook.
		$output .= '<a' . $attr_html . '>' . esc_html( wp_strip_all_tags( (string) $title ) ) . '</a>';

		if ( $has_sub && ( 0 === $depth || $mobile ) ) {
			$sub_id = 'ni-sub-' . $item->ID . ( $mobile ? '-m' : '' );
			if ( $mobile && $depth > 0 ) {
				$sub_id = '';
			}
			$output .= sprintf(
				'<button type="button" class="ni-submenu-toggle" aria-expanded="false" %1$s data-ni-submenu><span class="screen-reader-text">%2$s</span>%3$s</button>',
				$sub_id ? 'aria-controls="' . esc_attr( $sub_id ) . '"' : '',
				/* translators: %s: menu item title. */
				esc_html( sprintf( __( 'Visa undermeny för %s', 'nordicinterior' ), wp_strip_all_tags( (string) $item->title ) ) ),
				ni_icon( 'chevron-down', 16 )
			);
		}
	}

	/**
	 * End element.
	 *
	 * @param string   $output      Output.
	 * @param WP_Post  $data_object Item.
	 * @param int      $depth       Depth.
	 * @param stdClass $args        Args.
	 */
	public function end_el( &$output, $data_object, $depth = 0, $args = null ) {
		$output .= '</li>';
	}
}
