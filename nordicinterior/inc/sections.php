<?php
/**
 * Front page sections. Order and visibility are set under
 * Theme Adjustments → Startsida. Every section can also be used on other
 * pages: [ni_section name="rooms"].
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Available sections.
 *
 * @return array<string,string>
 */
function ni_sections() {
	return array(
		'hero'        => __( 'Hero med sök', 'nordicinterior' ),
		'rooms'       => __( 'Shoppa efter rum', 'nordicinterior' ),
		'styles'      => __( 'Shoppa efter stil', 'nordicinterior' ),
		'campaigns'   => __( 'Kampanjer', 'nordicinterior' ),
		'products'    => __( 'Produkter i flikar', 'nordicinterior' ),
		'look'        => __( 'Shoppa looken', 'nordicinterior' ),
		'inspiration' => __( 'Inspiration', 'nordicinterior' ),
		'brands'      => __( 'Varumärken', 'nordicinterior' ),
		'categories'  => __( 'Kategorier', 'nordicinterior' ),
		'newsletter'  => __( 'Nyhetsbrev och affiliate-information', 'nordicinterior' ),
	);
}

/**
 * Enabled sections in order.
 *
 * @return string[]
 */
function ni_home_sections() {
	$all   = array_keys( ni_sections() );
	$order = array_filter( array_map( 'trim', explode( ',', (string) ni_mod( 'home_sections' ) ) ) );
	$off   = array_filter( array_map( 'trim', explode( ',', (string) ni_mod( 'home_sections_off' ) ) ) );
	$order = array_values( array_intersect( $order, $all ) );
	return array_values( array_diff( $order, $off ) );
}

/**
 * Render a section.
 *
 * @param string $name Section key.
 * @param array  $args Overrides.
 */
function ni_render_section( $name, $args = array() ) {
	$fn = 'ni_section_' . $name;
	if ( array_key_exists( $name, ni_sections() ) && function_exists( $fn ) ) {
		echo '<div class="ni-section-wrap ni-section-wrap--' . esc_attr( $name ) . '" data-ni-section="' . esc_attr( $name ) . '">';
		call_user_func( $fn, $args );
		echo '</div>';
	}
}

/**
 * Shortcode [ni_section name="rooms" title="..."].
 *
 * @param array $atts Attributes.
 * @return string
 */
function ni_section_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'name'  => '',
			'title' => '',
		),
		$atts,
		'ni_section'
	);
	ob_start();
	ni_render_section( sanitize_key( $atts['name'] ), array( 'title' => sanitize_text_field( $atts['title'] ) ) );
	return (string) ob_get_clean();
}
add_shortcode( 'ni_section', 'ni_section_shortcode' );

/**
 * Hero.
 *
 * @param array $args Args.
 */
function ni_section_hero( $args = array() ) {
	$images = array_values( array_filter( array( absint( ni_mod( 'hero_image_1' ) ), absint( ni_mod( 'hero_image_2' ) ), absint( ni_mod( 'hero_image_3' ) ) ) ) );
	$slider = ni_mod( 'hero_slider' ) && count( $images ) > 1;
	$title  = ! empty( $args['title'] ) ? $args['title'] : (string) ni_mod( 'hero_title' );
	if ( ! $slider ) {
		$images = array_slice( $images, 0, 1 );
	}
	?>
	<section class="ni-hero<?php echo $images ? ' ni-hero--image' : ''; ?>" aria-labelledby="ni-hero-title" <?php echo $slider ? 'data-ni-slider' : ''; ?>>
		<?php if ( $images ) : ?>
			<div class="ni-hero__media">
				<?php foreach ( $images as $i => $image_id ) : ?>
					<div class="ni-hero__slide<?php echo 0 === $i ? ' is-active' : ''; ?>" <?php echo $i ? 'aria-hidden="true"' : ''; ?>>
						<?php
						echo wp_get_attachment_image(
							$image_id,
							'ni-hero',
							false,
							array(
								'class'         => 'ni-hero__img',
								'loading'       => 0 === $i ? 'eager' : 'lazy',
								'fetchpriority' => 0 === $i ? 'high' : 'low',
								'sizes'         => '100vw',
							)
						);
						?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<div class="ni-container ni-hero__inner">
			<div class="ni-hero__content">
				<h1 class="ni-hero__title" id="ni-hero-title"><?php echo esc_html( $title ); ?></h1>
				<?php if ( ni_mod( 'hero_text' ) ) : ?>
					<p class="ni-hero__text"><?php echo esc_html( (string) ni_mod( 'hero_text' ) ); ?></p>
				<?php endif; ?>
				<?php ni_search_form( array( 'id' => 'ni-hero-search' ) ); ?>
			</div>
		</div>
		<?php if ( $slider ) : ?>
			<button type="button" class="ni-hero__pause ni-iconbtn" data-ni-slider-toggle aria-pressed="false">
				<span class="screen-reader-text"><?php esc_html_e( 'Pausa bildspelet', 'nordicinterior' ); ?></span>
				<span aria-hidden="true" class="ni-hero__pause-icon"></span>
			</button>
		<?php endif; ?>
	</section>
	<?php
}

/**
 * Preload the hero image (LCP) on the front page.
 */
function ni_hero_preload() {
	if ( ! is_front_page() || ! ni_mod( 'perf_hero_preload' ) || ! in_array( 'hero', ni_home_sections(), true ) ) {
		return;
	}
	$id = absint( ni_mod( 'hero_image_1' ) );
	if ( ! $id ) {
		return;
	}
	$src    = wp_get_attachment_image_url( $id, 'ni-hero' );
	$srcset = wp_get_attachment_image_srcset( $id, 'ni-hero' );
	if ( $src ) {
		printf( '<link rel="preload" as="image" href="%1$s"%2$s imagesizes="100vw" fetchpriority="high">' . "\n", esc_url( $src ), $srcset ? ' imagesrcset="' . esc_attr( $srcset ) . '"' : '' );
	}
}
add_action( 'wp_head', 'ni_hero_preload', 2 );

/**
 * Rooms for the front page according to the selected source.
 *
 * @param int $count Count.
 * @return WP_Term[]
 */
function ni_home_rooms( $count ) {
	$rooms  = ni_get_terms( 'ni_room', array( 'parent' => 0 ) );
	$source = ni_mod( 'rooms_source' );
	if ( 'flagged' === $source ) {
		$flagged = array_filter( $rooms, static fn( $t ) => (bool) ni_term_meta( $t->term_id, 'popular', 0 ) );
		$rooms   = array_merge( $flagged, array_udiff( $rooms, $flagged, static fn( $a, $b ) => $a->term_id <=> $b->term_id ) );
	} elseif ( 'season' === $source ) {
		$month  = (int) wp_date( 'n' );
		$season = array_filter( $rooms, static fn( $t ) => in_array( $month, array_map( 'intval', (array) ni_term_meta( $t->term_id, 'months', array() ) ), true ) );
		$rooms  = array_merge( $season, array_udiff( $rooms, $season, static fn( $a, $b ) => $a->term_id <=> $b->term_id ) );
	} elseif ( 'clicks' === $source ) {
		$clicks = ni_clicks_per_term_slug( 'room' );
		usort( $rooms, static fn( $a, $b ) => ( $clicks[ $b->slug ] ?? 0 ) <=> ( $clicks[ $a->slug ] ?? 0 ) );
	}
	return array_slice( $rooms, 0, max( 1, $count ) );
}

/**
 * Shop by room.
 *
 * @param array $args Args.
 */
function ni_section_rooms( $args = array() ) {
	$rooms = ni_home_rooms( absint( ni_mod( 'rooms_count' ) ) );
	if ( ! $rooms ) {
		return;
	}
	$title = ! empty( $args['title'] ) ? $args['title'] : (string) ni_mod( 'rooms_title' );
	?>
	<section class="ni-section ni-section--rooms" aria-labelledby="ni-rooms-title">
		<div class="ni-container">
			<?php ni_section_header( $title, ni_page_url( 'page-templates/rooms.php' ), __( 'Alla rum', 'nordicinterior' ), '', 'ni-rooms-title' ); ?>
			<ul class="ni-term-grid ni-term-grid--rooms" role="list">
				<?php foreach ( $rooms as $i => $room ) : ?>
					<li class="<?php echo 0 === $i ? 'is-featured' : ''; ?>"><?php ni_term_card( $room, 0 === $i ? 'large' : 'tile' ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
	<?php
}

/**
 * Shop by style.
 *
 * @param array $args Args.
 */
function ni_section_styles( $args = array() ) {
	$styles = array_slice( ni_get_terms( 'ni_style', array( 'parent' => 0 ) ), 0, absint( ni_mod( 'styles_count' ) ) );
	if ( ! $styles ) {
		return;
	}
	$title = ! empty( $args['title'] ) ? $args['title'] : (string) ni_mod( 'styles_title' );
	?>
	<section class="ni-section ni-section--styles" aria-labelledby="ni-styles-title">
		<div class="ni-container">
			<?php ni_section_header( $title, ni_page_url( 'page-templates/styles.php' ), __( 'Alla stilar', 'nordicinterior' ), '', 'ni-styles-title' ); ?>
		</div>
		<div class="ni-container ni-container--bleed">
			<ul class="ni-rail ni-rail--terms" role="list">
				<?php foreach ( $styles as $style ) : ?>
					<li class="ni-rail__item"><?php ni_term_card( $style, 'tile', true ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
	<?php
}

/**
 * Campaigns: main campaign plus list.
 *
 * @param array $args Args.
 */
function ni_section_campaigns( $args = array() ) {
	$campaigns = ni_get_active_campaigns( array( 'posts_per_page' => 4 ) );
	if ( ! $campaigns ) {
		return;
	}
	$title = ! empty( $args['title'] ) ? $args['title'] : __( 'Kampanjer just nu', 'nordicinterior' );
	$main  = array_shift( $campaigns );
	?>
	<section class="ni-section ni-section--campaigns" aria-labelledby="ni-campaigns-title">
		<div class="ni-container">
			<?php ni_section_header( $title, (string) get_post_type_archive_link( 'ni_campaign' ), __( 'Alla kampanjer', 'nordicinterior' ), '', 'ni-campaigns-title' ); ?>
			<div class="ni-campaigns">
				<div class="ni-campaigns__main"><?php ni_campaign_card( $main, 'main', 'home' ); ?></div>
				<?php if ( $campaigns ) : ?>
					<div class="ni-campaigns__list">
						<?php foreach ( $campaigns as $campaign ) : ?>
							<?php ni_campaign_card( $campaign, 'compact', 'home' ); ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Product IDs for the products section.
 *
 * @param string $source  popular|new|sale|featured|handpicked.
 * @param int    $count   Count.
 * @param array  $tax     Optional tax_query clause.
 * @return int[]
 */
function ni_section_product_ids( $source, $count, $tax = array() ) {
	$args = array( 'posts_per_page' => $count );
	if ( $tax ) {
		$args['tax_query'] = array( $tax ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Cached.
	}
	switch ( $source ) {
		case 'new':
			$args['orderby'] = 'date';
			$args['order']   = 'DESC';
			break;
		case 'sale':
			$sale             = wc_get_product_ids_on_sale();
			$args['post__in'] = $sale ? array_map( 'intval', $sale ) : array( 0 );
			$args['meta_key'] = '_ni_discount'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Cached.
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'DESC';
			break;
		case 'featured':
			$args['post__in'] = wc_get_featured_product_ids() ? wc_get_featured_product_ids() : array( 0 );
			break;
		case 'handpicked':
			$ids = ni_id_list( (string) ni_mod( 'products_ids' ) );
			if ( ! $ids ) {
				return array();
			}
			$args['post__in'] = $ids;
			$args['orderby']  = 'post__in';
			break;
		case 'popular':
		default:
			$args['meta_key'] = '_ni_clicks'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Cached.
			$args['orderby']  = array(
				'meta_value_num' => 'DESC',
				'date'           => 'DESC',
			);
	}
	return ni_query_product_ids( $args );
}

/**
 * Products in tabs.
 *
 * @param array $args Args.
 */
function ni_section_products( $args = array() ) {
	if ( ! ni_wc() ) {
		return;
	}
	$title   = ! empty( $args['title'] ) ? $args['title'] : (string) ni_mod( 'products_title' );
	$source  = (string) ni_mod( 'products_source' );
	$count   = max( 2, absint( ni_mod( 'products_per_tab' ) ) );
	$tabs_by = (string) ni_mod( 'products_tabs_by' );
	$tabs    = array();

	if ( 'none' !== $tabs_by ) {
		$tax = 'category' === $tabs_by ? 'product_cat' : 'ni_room';
		foreach ( array_filter( array_map( 'trim', explode( ',', (string) ni_mod( 'products_tabs' ) ) ) ) as $slug ) {
			$term = get_term_by( 'slug', sanitize_title( $slug ), $tax );
			if ( ! $term ) {
				continue;
			}
			$ids = ni_section_product_ids(
				$source,
				$count,
				array(
					'taxonomy' => $tax,
					'terms'    => array( $term->term_id ),
				)
			);
			if ( $ids ) {
				$tabs[] = array(
					'term' => $term,
					'ids'  => $ids,
				);
			}
		}
	}
	if ( ! $tabs ) {
		$ids = ni_section_product_ids( $source, $count );
		if ( ! $ids ) {
			return;
		}
		$tabs[] = array(
			'term' => null,
			'ids'  => $ids,
		);
	}
	$uid = 'ni-ptabs-' . wp_unique_id();
	?>
	<section class="ni-section ni-section--products" aria-labelledby="<?php echo esc_attr( $uid ); ?>-title">
		<div class="ni-container">
			<?php
			$link = 'sale' === $source ? ni_page_url( 'page-templates/sale.php' ) : ( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '' );
			ni_section_header( $title, $link, __( 'Visa fler', 'nordicinterior' ), '', $uid . '-title' );
			?>
			<?php if ( count( $tabs ) > 1 ) : ?>
				<div class="ni-tabs" data-ni-tabs>
					<div class="ni-tabs__list" role="tablist" aria-labelledby="<?php echo esc_attr( $uid ); ?>-title">
						<?php foreach ( $tabs as $i => $tab ) : ?>
							<button type="button" role="tab" class="ni-tabs__tab" id="<?php echo esc_attr( $uid . '-tab-' . $i ); ?>" aria-controls="<?php echo esc_attr( $uid . '-panel-' . $i ); ?>" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>" <?php echo 0 === $i ? '' : 'tabindex="-1"'; ?>>
								<?php echo esc_html( $tab['term']->name ); ?>
							</button>
						<?php endforeach; ?>
					</div>
					<?php foreach ( $tabs as $i => $tab ) : ?>
						<div class="ni-tabs__panel" role="tabpanel" id="<?php echo esc_attr( $uid . '-panel-' . $i ); ?>" aria-labelledby="<?php echo esc_attr( $uid . '-tab-' . $i ); ?>" tabindex="0" <?php echo 0 === $i ? '' : 'hidden'; ?>>
							<?php ni_product_rail( $tab['ids'], 'home', $tab['term']->name ); ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<?php ni_product_rail( $tabs[0]['ids'], 'home', $title ); ?>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * Shop the look.
 *
 * @param array $args Args.
 */
function ni_section_look( $args = array() ) {
	$post_id = absint( ni_mod( 'look_post' ) );
	if ( ! $post_id ) {
		$candidates = array_keys( ni_posts_with_looks() );
		$post_id    = $candidates[1] ?? 0;
	}
	if ( ! $post_id ) {
		return;
	}
	$look = ni_first_look_in_post( $post_id );
	if ( ! $look ) {
		return;
	}
	$title = ! empty( $args['title'] ) ? $args['title'] : __( 'Shoppa looken', 'nordicinterior' );
	?>
	<section class="ni-section ni-section--look" aria-labelledby="ni-look-title">
		<div class="ni-container">
			<?php ni_section_header( $title, (string) get_permalink( $post_id ), __( 'Läs artikeln', 'nordicinterior' ), get_the_title( $post_id ), 'ni-look-title' ); ?>
			<?php echo $look; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in ni_render_look(). ?>
		</div>
	</section>
	<?php
}

/**
 * Inspiration: one large and several small articles.
 *
 * @param array $args Args.
 */
function ni_section_inspiration( $args = array() ) {
	$posts = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 5,
			'no_found_rows'  => true,
		)
	);
	if ( ! $posts ) {
		return;
	}
	$title = ! empty( $args['title'] ) ? $args['title'] : (string) ni_mod( 'inspiration_title' );
	$blog  = (int) get_option( 'page_for_posts' );
	$first = array_shift( $posts );
	?>
	<section class="ni-section ni-section--inspiration" aria-labelledby="ni-insp-title">
		<div class="ni-container">
			<?php ni_section_header( $title, $blog ? (string) get_permalink( $blog ) : '', __( 'Mer inspiration', 'nordicinterior' ), '', 'ni-insp-title' ); ?>
			<div class="ni-editorial">
				<div class="ni-editorial__main"><?php ni_post_card( $first, 'large' ); ?></div>
				<?php if ( $posts ) : ?>
					<div class="ni-editorial__side">
						<?php foreach ( $posts as $post ) : ?>
							<?php ni_post_card( $post, 'row' ); ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Brand logo wall (grayscale, colour on hover).
 *
 * @param array $args Args.
 */
function ni_section_brands( $args = array() ) {
	$brands = ni_get_terms( 'product_brand' );
	if ( ! $brands ) {
		return;
	}
	usort( $brands, static fn( $a, $b ) => (int) (bool) ni_term_meta( $b->term_id, 'featured', 0 ) <=> (int) (bool) ni_term_meta( $a->term_id, 'featured', 0 ) );
	$brands = array_slice( $brands, 0, 12 );
	$title  = ! empty( $args['title'] ) ? $args['title'] : (string) ni_mod( 'brands_title' );
	?>
	<section class="ni-section ni-section--brands" aria-labelledby="ni-brands-title">
		<div class="ni-container">
			<?php ni_section_header( $title, ni_page_url( 'page-templates/brands.php' ), __( 'Alla varumärken A–Ö', 'nordicinterior' ), '', 'ni-brands-title' ); ?>
			<ul class="ni-logo-wall" role="list">
				<?php foreach ( $brands as $brand ) : ?>
					<li>
						<a href="<?php echo esc_url( (string) get_term_link( $brand ) ); ?>" class="ni-logo-wall__item">
							<?php
							$logo = (int) ni_term_meta( $brand->term_id, 'logo', 0 );
							$logo = $logo ? $logo : (int) get_term_meta( $brand->term_id, 'thumbnail_id', true );
							if ( $logo ) {
								echo wp_get_attachment_image(
									$logo,
									'ni-logo',
									false,
									array(
										'alt'     => $brand->name,
										'loading' => 'lazy',
									)
								);
							} else {
								echo '<span class="ni-logo-wall__name">' . esc_html( $brand->name ) . '</span>';
							}
							?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
	<?php
}

/**
 * Category tiles.
 *
 * @param array $args Args.
 */
function ni_section_categories( $args = array() ) {
	$cats = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => 0,
			'hide_empty' => true,
			'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
			'menu_order' => 'ASC', // WooCommerce's sortable category order.
		)
	);
	if ( is_wp_error( $cats ) || ! $cats ) {
		return;
	}
	$title = ! empty( $args['title'] ) ? $args['title'] : (string) ni_mod( 'categories_title' );
	?>
	<section class="ni-section ni-section--categories" aria-labelledby="ni-cats-title">
		<div class="ni-container">
			<?php ni_section_header( $title, '', '', '', 'ni-cats-title' ); ?>
			<ul class="ni-term-grid ni-term-grid--cats" role="list">
				<?php foreach ( $cats as $cat ) : ?>
					<li><?php ni_term_card( $cat, 'tile' ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
	<?php
}

/**
 * Newsletter and affiliate information on a linen band.
 *
 * @param array $args Args.
 */
function ni_section_newsletter( $args = array() ) {
	$title     = ! empty( $args['title'] ) ? $args['title'] : (string) ni_mod( 'newsletter_title' );
	$shortcode = trim( (string) ni_mod( 'newsletter_shortcode' ) );
	?>
	<section class="ni-section ni-band" aria-labelledby="ni-news-title">
		<div class="ni-container ni-band__inner">
			<div class="ni-band__text">
				<h2 class="ni-section__title" id="ni-news-title"><?php echo esc_html( $title ); ?></h2>
				<p><?php echo esc_html( (string) ni_mod( 'newsletter_text' ) ); ?></p>
			</div>
			<div class="ni-band__form">
				<?php
				if ( $shortcode ) {
					echo do_shortcode( $shortcode );
				} else {
					ni_newsletter_placeholder_form();
				}
				?>
			</div>
			<div class="ni-band__disclosure">
				<?php ni_disclosure( 'footer' ); ?>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Accessible newsletter form used until a form plugin shortcode is set.
 * It posts nowhere until configured; editors see a hint.
 */
function ni_newsletter_placeholder_form() {
	$id = 'ni-news-' . wp_unique_id();
	?>
	<form class="ni-newsletter" action="#" method="post" onsubmit="return false;">
		<label for="<?php echo esc_attr( $id ); ?>" class="screen-reader-text"><?php esc_html_e( 'E-postadress', 'nordicinterior' ); ?></label>
		<input type="email" id="<?php echo esc_attr( $id ); ?>" name="email" placeholder="<?php esc_attr_e( 'Din e-postadress', 'nordicinterior' ); ?>" autocomplete="email" required>
		<button type="submit" class="ni-btn"><?php esc_html_e( 'Prenumerera', 'nordicinterior' ); ?></button>
	</form>
	<?php if ( current_user_can( 'edit_theme_options' ) ) : ?>
		<p class="ni-admin-hint"><?php esc_html_e( 'Lägg in formulärets shortcode under Theme Adjustments → Startsida → Nyhetsbrev.', 'nordicinterior' ); ?></p>
	<?php endif; ?>
	<?php
}
