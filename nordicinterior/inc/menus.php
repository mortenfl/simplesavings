<?php
/**
 * Menus: mega menu fields per menu item (Utseende → Menyer), the walker
 * (three levels, disclosure buttons), the "Alla rum" panel and the
 * "Skapa huvudmeny" builder.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

require_once NI_DIR . '/inc/class-ni-walker-nav.php';

/**
 * Menu item fields.
 *
 * @param int     $item_id Item ID.
 * @param WP_Post $item    Item.
 * @param int     $depth   Depth.
 */
function ni_menu_item_fields( $item_id, $item, $depth ) {
	wp_nonce_field( 'ni_menu_item', 'ni_menu_item_nonce' );
	$mega  = (bool) get_post_meta( $item_id, '_ni_mega', true );
	$cols  = (int) get_post_meta( $item_id, '_ni_columns', true );
	$image = (int) get_post_meta( $item_id, '_ni_image', true );
	$text  = (string) get_post_meta( $item_id, '_ni_image_text', true );
	$link  = (string) get_post_meta( $item_id, '_ni_image_link', true );
	?>
	<div class="ni-menu-fields" <?php echo $depth > 0 ? 'hidden' : ''; ?>>
		<p class="description description-wide">
			<label>
				<input type="checkbox" name="ni_mega[<?php echo esc_attr( (string) $item_id ); ?>]" value="1" <?php checked( $mega ); ?>>
				<?php esc_html_e( 'Mega-meny (nivå 2 blir kolumnrubriker, nivå 3 länkar)', 'nordicinterior' ); ?>
			</label>
		</p>
		<p class="description description-thin">
			<label><?php esc_html_e( 'Kolumner (2–5)', 'nordicinterior' ); ?><br>
				<input type="number" min="2" max="5" name="ni_columns[<?php echo esc_attr( (string) $item_id ); ?>]" value="<?php echo esc_attr( (string) ( $cols ? $cols : 4 ) ); ?>" class="widefat">
			</label>
		</p>
		<div class="description description-wide ni-media" data-ni-media>
			<span><?php esc_html_e( 'Stämningsbild (valfri)', 'nordicinterior' ); ?></span>
			<input type="hidden" name="ni_image[<?php echo esc_attr( (string) $item_id ); ?>]" value="<?php echo esc_attr( (string) $image ); ?>">
			<div class="ni-media__preview"><?php echo $image ? wp_get_attachment_image( $image, 'thumbnail' ) : ''; ?></div>
			<button type="button" class="button" data-ni-media-choose><?php esc_html_e( 'Välj bild', 'nordicinterior' ); ?></button>
			<button type="button" class="button-link" data-ni-media-remove><?php esc_html_e( 'Ta bort', 'nordicinterior' ); ?></button>
		</div>
		<p class="description description-wide">
			<label><?php esc_html_e( 'Bildtext, t.ex. ”Nyheter i höst”', 'nordicinterior' ); ?><br>
				<input type="text" name="ni_image_text[<?php echo esc_attr( (string) $item_id ); ?>]" value="<?php echo esc_attr( $text ); ?>" class="widefat">
			</label>
		</p>
		<p class="description description-wide">
			<label><?php esc_html_e( 'Bildlänk', 'nordicinterior' ); ?><br>
				<input type="url" name="ni_image_link[<?php echo esc_attr( (string) $item_id ); ?>]" value="<?php echo esc_attr( $link ); ?>" class="widefat">
			</label>
		</p>
	</div>
	<?php
}
add_action( 'wp_nav_menu_item_custom_fields', 'ni_menu_item_fields', 10, 3 );

/**
 * Save menu item fields.
 *
 * @param int $menu_id Menu ID.
 * @param int $item_id Item ID.
 */
function ni_menu_item_save( $menu_id, $item_id ) {
	if ( ! isset( $_POST['ni_menu_item_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ni_menu_item_nonce'] ) ), 'ni_menu_item' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$fields = array(
		'ni_mega'       => '_ni_mega',
		'ni_columns'    => '_ni_columns',
		'ni_image'      => '_ni_image',
		'ni_image_text' => '_ni_image_text',
		'ni_image_link' => '_ni_image_link',
	);
	foreach ( $fields as $field => $meta ) {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitised below.
		$raw = isset( $_POST[ $field ][ $item_id ] ) ? wp_unslash( $_POST[ $field ][ $item_id ] ) : '';
		switch ( $field ) {
			case 'ni_mega':
				$value = $raw ? 1 : 0;
				break;
			case 'ni_columns':
				$value = max( 2, min( 5, absint( $raw ) ) );
				break;
			case 'ni_image':
				$value = absint( $raw );
				break;
			case 'ni_image_link':
				$value = esc_url_raw( (string) $raw );
				break;
			default:
				$value = sanitize_text_field( (string) $raw );
		}
		update_post_meta( $item_id, $meta, $value );
	}
}
add_action( 'wp_update_nav_menu_item', 'ni_menu_item_save', 10, 2 );

/**
 * Fallback when no menu is assigned: a simple list of rooms and pages.
 *
 * @param array $args Menu args.
 */
function ni_menu_fallback( $args ) {
	$items = array();
	if ( taxonomy_exists( 'product_cat' ) ) {
		$cats = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => 0,
				'hide_empty' => true,
				'number'     => 4,
				'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
			)
		);
		if ( ! is_wp_error( $cats ) ) {
			foreach ( $cats as $cat ) {
				$items[] = '<li class="menu-item"><a href="' . esc_url( (string) get_term_link( $cat ) ) . '">' . esc_html( $cat->name ) . '</a></li>';
			}
		}
	}
	$items[] = '<li class="menu-item"><a href="' . esc_url( ni_page_url( 'page-templates/rooms.php' ) ) . '">' . esc_html__( 'Rum', 'nordicinterior' ) . '</a></li>';
	$items[] = '<li class="menu-item ni-sale"><a href="' . esc_url( ni_page_url( 'page-templates/sale.php' ) ) . '">' . esc_html__( 'Rea', 'nordicinterior' ) . '</a></li>';
	$class   = ! empty( $args['menu_class'] ) ? $args['menu_class'] : 'ni-menu';
	echo '<ul class="' . esc_attr( $class ) . '">' . implode( '', $items ) . '</ul>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above.
	if ( current_user_can( 'edit_theme_options' ) ) {
		echo '<p class="ni-admin-hint"><a href="' . esc_url( admin_url( 'admin.php?page=nordicinterior-start' ) ) . '">' . esc_html__( 'Skapa huvudmeny', 'nordicinterior' ) . '</a></p>';
	}
}

/**
 * The "Alla rum" panel: rooms plus a separate column of styles.
 */
function ni_rooms_panel() {
	$rooms  = ni_get_terms( 'ni_room', array( 'parent' => 0 ) );
	$styles = ni_get_terms( 'ni_style', array( 'parent' => 0 ) );
	if ( ! $rooms && ! $styles ) {
		return;
	}
	?>
	<div class="ni-rooms-panel" id="ni-rooms-panel" hidden>
		<div class="ni-container ni-rooms-panel__inner">
			<div class="ni-rooms-panel__rooms">
				<p class="ni-eyebrow"><?php esc_html_e( 'Rum', 'nordicinterior' ); ?></p>
				<ul class="ni-rooms-panel__grid" role="list">
					<?php foreach ( $rooms as $room ) : ?>
						<li>
							<a href="<?php echo esc_url( (string) get_term_link( $room ) ); ?>" class="ni-rooms-panel__room">
								<span class="ni-rooms-panel__thumb"><?php echo ni_term_image( $room, 'thumbnail', array( 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by WP. ?></span>
								<span><?php echo esc_html( $room->name ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
				<a class="ni-link-arrow" href="<?php echo esc_url( ni_page_url( 'page-templates/rooms.php' ) ); ?>"><?php esc_html_e( 'Alla rum', 'nordicinterior' ); ?> <?php ni_the_icon( 'arrow-right', 16 ); ?></a>
			</div>
			<?php if ( $styles ) : ?>
				<div class="ni-rooms-panel__styles">
					<p class="ni-eyebrow"><?php esc_html_e( 'Stilar', 'nordicinterior' ); ?></p>
					<ul role="list">
						<?php foreach ( $styles as $style ) : ?>
							<li><a href="<?php echo esc_url( (string) get_term_link( $style ) ); ?>"><?php echo esc_html( $style->name ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * Build the primary menu ("Skapa huvudmeny" / wp nordicinterior menu).
 *
 * @return int|WP_Error Menu ID.
 */
function ni_build_primary_menu() {
	$name = __( 'Huvudmeny', 'nordicinterior' );
	$menu = wp_get_nav_menu_object( $name );
	if ( $menu ) {
		// Rebuild from scratch.
		foreach ( (array) wp_get_nav_menu_items( $menu->term_id ) as $item ) {
			wp_delete_post( $item->ID, true );
		}
		$menu_id = (int) $menu->term_id;
	} else {
		$menu_id = wp_create_nav_menu( $name );
		if ( is_wp_error( $menu_id ) ) {
			return $menu_id;
		}
	}

	$add = static function ( $args, $parent_id = 0 ) use ( $menu_id ) {
		$args = wp_parse_args(
			$args,
			array(
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => $parent_id,
			)
		);
		return (int) wp_update_nav_menu_item( $menu_id, 0, $args );
	};

	$term_item = static function ( $term, $title = '' ) {
		return array(
			'menu-item-title'     => $title ? $title : $term->name,
			'menu-item-object'    => $term->taxonomy,
			'menu-item-object-id' => $term->term_id,
			'menu-item-type'      => 'taxonomy',
		);
	};

	$page_item = static function ( $template, $title ) {
		$id = ni_page_by_template( $template );
		return $id
			? array(
				'menu-item-title'     => $title,
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $id,
				'menu-item-type'      => 'post_type',
			)
			: array(
				'menu-item-title' => $title,
				'menu-item-url'   => home_url( '/' ),
				'menu-item-type'  => 'custom',
			);
	};

	// Top-level categories as defined by the brief.
	$top_cats = array(
		'mobler'           => array( __( 'MÖBLER', 'nordicinterior' ), true ),
		'belysning'        => array( __( 'BELYSNING', 'nordicinterior' ), false ),
		'textilier-mattor' => array( __( 'TEXTILIER & MATTOR', 'nordicinterior' ), false ),
	);
	foreach ( $top_cats as $slug => $def ) {
		$cat = get_term_by( 'slug', $slug, 'product_cat' );
		if ( ! $cat && 'textilier-mattor' === $slug ) {
			$cat = get_term_by( 'slug', 'textilier', 'product_cat' );
		}
		if ( ! $cat ) {
			continue;
		}
		$top_id = $add( $term_item( $cat, $def[0] ) );
		if ( $def[1] ) {
			update_post_meta( $top_id, '_ni_mega', 1 );
			update_post_meta( $top_id, '_ni_columns', 4 );
		}
		$children = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => $cat->term_id,
				'hide_empty' => false,
			)
		);
		foreach ( is_wp_error( $children ) ? array() : $children as $child ) {
			$child_id = $add( $term_item( $child ), $top_id );
			if ( $def[1] ) {
				$grand = get_terms(
					array(
						'taxonomy'   => 'product_cat',
						'parent'     => $child->term_id,
						'hide_empty' => false,
						'number'     => 6,
					)
				);
				foreach ( is_wp_error( $grand ) ? array() : $grand as $g ) {
					$add( $term_item( $g ), $child_id );
				}
			}
		}
	}

	$rooms_id = $add( $page_item( 'page-templates/rooms.php', __( 'RUM', 'nordicinterior' ) ) );
	foreach ( ni_get_terms( 'ni_room', array( 'parent' => 0 ) ) as $room ) {
		$add( $term_item( $room ), $rooms_id );
	}
	$styles_id = $add( $page_item( 'page-templates/styles.php', __( 'STIL', 'nordicinterior' ) ) );
	foreach ( ni_get_terms( 'ni_style', array( 'parent' => 0 ) ) as $style ) {
		$add( $term_item( $style ), $styles_id );
	}
	$add( $page_item( 'page-templates/brands.php', __( 'VARUMÄRKEN', 'nordicinterior' ) ) );
	$add( $page_item( 'page-templates/stores.php', __( 'BUTIKER', 'nordicinterior' ) ) );

	$blog = (int) get_option( 'page_for_posts' );
	$add(
		$blog
			? array(
				'menu-item-title'     => __( 'INSPIRATION', 'nordicinterior' ),
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $blog,
				'menu-item-type'      => 'post_type',
			)
			: array(
				'menu-item-title' => __( 'INSPIRATION', 'nordicinterior' ),
				'menu-item-url'   => home_url( '/' ),
				'menu-item-type'  => 'custom',
			)
	);
	$add( array_merge( $page_item( 'page-templates/sale.php', __( 'REA', 'nordicinterior' ) ), array( 'menu-item-classes' => 'ni-sale' ) ) );

	$locations            = (array) get_theme_mod( 'nav_menu_locations', array() );
	$locations['primary'] = $menu_id;
	$locations['mobile']  = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );

	return $menu_id;
}

/**
 * Admin-post handler for the "Skapa huvudmeny" button.
 */
function ni_handle_build_menu() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'Du har inte behörighet att göra detta.', 'nordicinterior' ) );
	}
	check_admin_referer( 'ni_build_menu' );
	$result = ni_build_primary_menu();
	$status = is_wp_error( $result ) ? 'menu-error' : 'menu-ok';
	wp_safe_redirect( add_query_arg( 'ni-notice', $status, admin_url( 'admin.php?page=nordicinterior-start' ) ) );
	exit;
}
add_action( 'admin_post_ni_build_menu', 'ni_handle_build_menu' );
