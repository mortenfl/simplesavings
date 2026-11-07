<?php
/**
 * Template tags: product cards, rails, post cards, section headers,
 * pagination and listing pieces.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Print a product card.
 *
 * @param WC_Product|int $product   Product.
 * @param string         $placement Tracking placement.
 * @param string         $variant   grid|list|mini.
 */
function ni_product_card( $product, $placement = 'card', $variant = 'grid' ) {
	$product = ni_product( $product );
	if ( ! $product ) {
		return;
	}
	$id        = $product->get_id();
	$link      = get_permalink( $id );
	$brand     = ni_mod( 'card_show_brand' ) ? ni_product_brand( $product ) : null;
	$store     = ni_mod( 'card_show_store' ) ? ni_product_store( $product ) : null;
	$colors    = ni_mod( 'card_show_colors' ) ? ni_product_colors( $product ) : array();
	$discount  = ni_product_discount( $product );
	$drop      = ni_price_drop( $product );
	$size      = '1-1' === ni_mod( 'image_ratio' ) ? 'ni-square' : 'ni-card';
	$cta_style = 'button' === ni_mod( 'card_cta_style' ) ? 'button' : 'link';
	?>
	<article class="ni-card ni-card--<?php echo esc_attr( $variant ); ?>" data-product-id="<?php echo esc_attr( (string) $id ); ?>">
		<a class="ni-card__media" href="<?php echo esc_url( $link ); ?>" tabindex="-1" aria-hidden="true">
			<?php
			if ( $product->get_image_id() ) {
				echo wp_get_attachment_image(
					(int) $product->get_image_id(),
					$size,
					false,
					array(
						'class'   => 'ni-card__img',
						'loading' => 'lazy',
						'alt'     => '',
						'sizes'   => 'mini' === $variant ? '120px' : '(min-width: 1200px) 300px, (min-width: 768px) 33vw, 50vw',
					)
				);
			} else {
				echo '<span class="ni-placeholder">' . ni_icon( 'chair', 40 ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG.
			}
			?>
			<?php if ( $discount || $drop ) : ?>
				<span class="ni-card__labels">
					<?php if ( $discount ) : ?>
						<span class="ni-label ni-label--sale">
							<?php
							/* translators: %d: discount percentage. */
							printf( esc_html__( '−%d %%', 'nordicinterior' ), (int) $discount );
							?>
						</span>
					<?php endif; ?>
					<?php if ( $drop && ! $discount ) : ?>
						<span class="ni-label ni-label--drop"><?php ni_the_icon( 'trend-down', 14 ); ?><?php esc_html_e( 'Prissänkt', 'nordicinterior' ); ?></span>
					<?php endif; ?>
				</span>
			<?php endif; ?>
		</a>
		<div class="ni-card__body">
			<?php if ( $brand ) : ?>
				<p class="ni-card__brand"><?php echo esc_html( $brand->name ); ?></p>
			<?php endif; ?>
			<h3 class="ni-card__title"><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
			<div class="ni-card__price"><?php echo wp_kses_post( ni_price_html( $product ) ); ?></div>
			<?php if ( $store || $colors ) : ?>
				<div class="ni-card__meta">
					<?php if ( $store ) : ?>
						<span class="ni-card__store"><?php echo esc_html( $store->name ); ?></span>
					<?php endif; ?>
					<?php if ( $colors ) : ?>
						<span class="ni-swatches" role="list" aria-label="<?php esc_attr_e( 'Färger', 'nordicinterior' ); ?>">
							<?php foreach ( array_slice( $colors, 0, 5 ) as $color ) : ?>
								<span class="ni-swatch" role="listitem" style="--c:<?php echo esc_attr( $color['hex'] ); ?>" title="<?php echo esc_attr( $color['name'] ); ?>"><span class="screen-reader-text"><?php echo esc_html( $color['name'] ); ?></span></span>
							<?php endforeach; ?>
							<?php if ( count( $colors ) > 5 ) : ?>
								<span class="ni-swatch-more">+<?php echo esc_html( (string) ( count( $colors ) - 5 ) ); ?></span>
							<?php endif; ?>
						</span>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<?php if ( 'list' === $variant && $product->get_short_description() ) : ?>
				<div class="ni-card__excerpt"><?php echo wp_kses_post( wp_trim_words( $product->get_short_description(), 28 ) ); ?></div>
			<?php endif; ?>
			<?php
			if ( ni_mod( 'card_show_cta' ) || 'mini' === $variant ) {
				ni_cta( $product, $placement, 'list' === $variant ? 'button' : $cta_style );
			}
			?>
		</div>
	</article>
	<?php
}

/**
 * A horizontal, scroll-snapping product rail (carousel).
 *
 * @param int[]  $ids       Product IDs.
 * @param string $placement Placement.
 * @param string $label     Accessible label.
 */
function ni_product_rail( $ids, $placement = 'card', $label = '' ) {
	if ( ! $ids ) {
		return;
	}
	?>
	<div class="ni-carousel" data-ni-carousel>
		<ul class="ni-rail" role="list" <?php echo $label ? 'aria-label="' . esc_attr( $label ) . '"' : ''; ?>>
			<?php foreach ( $ids as $id ) : ?>
				<li class="ni-rail__item"><?php ni_product_card( $id, $placement ); ?></li>
			<?php endforeach; ?>
		</ul>
		<div class="ni-carousel__nav">
			<button type="button" class="ni-iconbtn" data-ni-prev><?php ni_the_icon( 'arrow-left', 20, __( 'Föregående', 'nordicinterior' ) ); ?></button>
			<button type="button" class="ni-iconbtn" data-ni-next><?php ni_the_icon( 'arrow-right', 20, __( 'Nästa', 'nordicinterior' ) ); ?></button>
		</div>
	</div>
	<?php
}

/**
 * Product grid.
 *
 * @param int[]  $ids       Product IDs.
 * @param string $placement Placement.
 */
function ni_product_grid( $ids, $placement = 'card' ) {
	if ( ! $ids ) {
		return;
	}
	echo '<ul class="ni-grid" role="list">';
	foreach ( $ids as $id ) {
		echo '<li class="ni-grid__item">';
		ni_product_card( $id, $placement );
		echo '</li>';
	}
	echo '</ul>';
}

/**
 * Section header with optional "see all" link.
 *
 * @param string $title     Title.
 * @param string $url       Link URL.
 * @param string $link_text Link text.
 * @param string $eyebrow   Small text above.
 * @param string $id        Heading id.
 */
function ni_section_header( $title, $url = '', $link_text = '', $eyebrow = '', $id = '' ) {
	echo '<header class="ni-section__header">';
	echo '<div>';
	if ( $eyebrow ) {
		echo '<p class="ni-eyebrow">' . esc_html( $eyebrow ) . '</p>';
	}
	printf( '<h2 class="ni-section__title"%s>%s</h2>', $id ? ' id="' . esc_attr( $id ) . '"' : '', esc_html( $title ) );
	echo '</div>';
	if ( $url ) {
		printf(
			'<a class="ni-link-arrow" href="%1$s">%2$s %3$s</a>',
			esc_url( $url ),
			esc_html( $link_text ? $link_text : __( 'Visa alla', 'nordicinterior' ) ),
			ni_icon( 'arrow-right', 16 ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG.
		);
	}
	echo '</header>';
}

/**
 * Editorial post card. No date, no author, no reading time.
 *
 * @param WP_Post|int $post    Post.
 * @param string      $variant small|large|row.
 */
function ni_post_card( $post, $variant = 'small' ) {
	$post  = get_post( $post );
	$label = ni_post_label( $post->ID );
	$size  = 'large' === $variant ? 'ni-wide' : 'ni-card';
	?>
	<article class="ni-post-card ni-post-card--<?php echo esc_attr( $variant ); ?>">
		<a class="ni-post-card__media" href="<?php echo esc_url( get_permalink( $post ) ); ?>" tabindex="-1" aria-hidden="true">
			<?php
			if ( has_post_thumbnail( $post ) ) {
				echo get_the_post_thumbnail(
					$post,
					$size,
					array(
						'loading' => 'lazy',
						'alt'     => '',
					)
				);
			} else {
				echo '<span class="ni-placeholder">' . ni_icon( 'sparkle', 40 ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static SVG.
			}
			?>
		</a>
		<div class="ni-post-card__body">
			<?php if ( $label ) : ?>
				<p class="ni-eyebrow"><?php echo esc_html( $label->name ); ?></p>
			<?php endif; ?>
			<h3 class="ni-post-card__title"><a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></h3>
			<?php if ( 'small' !== $variant ) : ?>
				<p class="ni-post-card__lead"><?php echo esc_html( wp_trim_words( ni_post_lead( $post->ID ), 'large' === $variant ? 30 : 18 ) ); ?></p>
			<?php endif; ?>
		</div>
	</article>
	<?php
}

/**
 * Term card (room, style, category) with image and optional description.
 *
 * @param WP_Term $term    Term.
 * @param string  $variant tile|large|text.
 * @param bool    $desc    Show description.
 */
function ni_term_card( $term, $variant = 'tile', $desc = false ) {
	$tone = ni_term_meta( $term->term_id, 'tone', '' );
	?>
	<a class="ni-term-card ni-term-card--<?php echo esc_attr( $variant ); ?>" href="<?php echo esc_url( (string) get_term_link( $term ) ); ?>" <?php echo sanitize_hex_color( $tone ) ? 'style="--ni-tone:' . esc_attr( $tone ) . '"' : ''; ?>>
		<span class="ni-term-card__media">
			<?php
			$image_key = 'product_cat' === $term->taxonomy ? 'thumbnail_id' : 'image';
			if ( 'product_cat' === $term->taxonomy ) {
				$thumb = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
				echo $thumb
					? wp_get_attachment_image(
						$thumb,
						'ni-card',
						false,
						array(
							'loading' => 'lazy',
							'alt'     => '',
						)
					)
					: ni_term_image( $term, 'ni-card', array( 'alt' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by WP.
			} else {
				echo ni_term_image( $term, 'large' === $variant ? 'ni-wide' : 'ni-card', array( 'alt' => '' ), $image_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by WP / static SVG.
			}
			?>
		</span>
		<span class="ni-term-card__body">
			<span class="ni-term-card__title"><?php echo esc_html( $term->name ); ?></span>
			<?php
			if ( $desc ) {
				$text = 'ni_style' === $term->taxonomy ? (string) ni_term_meta( $term->term_id, 'lead', '' ) : '';
				$text = $text ? $text : wp_strip_all_tags( term_description( $term ) );
				if ( $text ) {
					echo '<span class="ni-term-card__desc">' . esc_html( wp_trim_words( $text, 18 ) ) . '</span>';
				}
			}
			?>
			<span class="ni-term-card__arrow" aria-hidden="true"><?php ni_the_icon( 'arrow-right', 18 ); ?></span>
		</span>
	</a>
	<?php
}

/**
 * Pagination for a query: numbered or "Ladda fler".
 *
 * @param WP_Query|null $query Query (defaults to main).
 * @param string        $mode  numbers|loadmore|auto.
 */
function ni_pagination( $query = null, $mode = 'auto' ) {
	global $wp_query;
	$query = $query ? $query : $wp_query;
	$total = (int) $query->max_num_pages;
	if ( $total < 2 ) {
		return;
	}
	$mode    = 'auto' === $mode ? ni_mod( 'pagination' ) : $mode;
	$current = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );

	if ( 'loadmore' === $mode && $current < $total ) {
		printf(
			'<div class="ni-loadmore"><p class="ni-loadmore__status">%1$s</p><a class="ni-btn ni-btn--outline" href="%2$s" data-ni-loadmore rel="next">%3$s</a></div>',
			esc_html(
				sprintf(
					/* translators: 1: shown products, 2: total products. */
					__( 'Visar %1$d av %2$d', 'nordicinterior' ),
					min( (int) $query->found_posts, $current * (int) $query->get( 'posts_per_page' ) ),
					(int) $query->found_posts
				)
			),
			esc_url( get_pagenum_link( $current + 1 ) ),
			esc_html__( 'Ladda fler', 'nordicinterior' )
		);
		echo '<noscript>';
	}

	$links = paginate_links(
		array(
			'total'     => $total,
			'current'   => $current,
			'type'      => 'list',
			'prev_text' => ni_icon( 'arrow-left', 18, __( 'Föregående sida', 'nordicinterior' ) ),
			'next_text' => ni_icon( 'arrow-right', 18, __( 'Nästa sida', 'nordicinterior' ) ),
			'mid_size'  => 1,
		)
	);
	if ( $links ) {
		echo '<nav class="ni-pagination" aria-label="' . esc_attr__( 'Sidnumrering', 'nordicinterior' ) . '">' . wp_kses( $links, array_merge( wp_kses_allowed_html( 'post' ), ni_svg_kses() ) ) . '</nav>';
	}

	if ( 'loadmore' === $mode && $current < $total ) {
		echo '</noscript>';
	}
}

/**
 * Archive/listing header on a calm linen surface.
 *
 * @param array $args { title, intro, image (html), chips (array of [name,url,active]) }.
 */
function ni_listing_header( $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'title'   => '',
			'intro'   => '',
			'image'   => '',
			'chips'   => array(),
			'eyebrow' => '',
			'tone'    => '',
		)
	);
	$tone = sanitize_hex_color( $args['tone'] ) ? $args['tone'] : '';
	?>
	<header class="ni-listing-header<?php echo $args['image'] ? ' ni-listing-header--image' : ''; ?>" <?php echo $tone ? 'style="--ni-tone:' . esc_attr( $tone ) . '"' : ''; ?>>
		<div class="ni-container ni-listing-header__inner">
			<div class="ni-listing-header__text">
				<?php ni_breadcrumbs(); ?>
				<?php if ( $args['eyebrow'] ) : ?>
					<p class="ni-eyebrow"><?php echo esc_html( $args['eyebrow'] ); ?></p>
				<?php endif; ?>
				<h1 class="ni-listing-header__title"><?php echo esc_html( $args['title'] ); ?></h1>
				<?php if ( $args['intro'] ) : ?>
					<div class="ni-listing-header__intro"><?php echo wp_kses_post( wpautop( $args['intro'] ) ); ?></div>
				<?php endif; ?>
				<?php if ( $args['chips'] ) : ?>
					<ul class="ni-chips__list ni-chips__list--sub" role="list">
						<?php foreach ( $args['chips'] as $chip ) : ?>
							<li><a class="ni-chip<?php echo ! empty( $chip[2] ) ? ' is-active' : ''; ?>" href="<?php echo esc_url( $chip[1] ); ?>"><?php echo esc_html( $chip[0] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
			<?php if ( $args['image'] ) : ?>
				<div class="ni-listing-header__media">
					<?php echo $args['image']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Generated by wp_get_attachment_image()/ni_term_image(). ?>
				</div>
			<?php endif; ?>
		</div>
	</header>
	<?php
}

/**
 * Header data for the current product listing.
 *
 * @return array
 */
function ni_current_listing_header() {
	$landing = ni_landing();
	if ( $landing ) {
		$chips = array();
		foreach ( ni_landing_categories_for( $landing['term'], 10 ) as $cat ) {
			$chips[] = array( $cat->name, ni_landing_url( $landing['term'], $cat ), $cat->term_id === $landing['cat']->term_id );
		}
		return array(
			'title'   => $landing['title'],
			'intro'   => $landing['intro'],
			'eyebrow' => $landing['term']->name,
			'image'   => ni_term_has_image( $landing['term'] ) ? ni_term_image(
				$landing['term'],
				'ni-wide',
				array(
					'fetchpriority' => 'high',
					'loading'       => 'eager',
				)
			) : '',
			'chips'   => $chips,
			'tone'    => (string) ni_term_meta( $landing['term']->term_id, 'tone', '' ),
		);
	}

	if ( is_search() ) {
		global $wp_query;
		$parsed = $wp_query->get( 'ni_parsed' );
		$chips  = array();
		if ( is_array( $parsed ) ) {
			foreach ( $parsed['labels'] as $label ) {
				$term = get_term( $label['id'], $label['tax'] );
				if ( $term && ! is_wp_error( $term ) ) {
					$chips[] = array( $term->name, (string) get_term_link( $term ), true );
				}
			}
		}
		return array(
			/* translators: %s: search query. */
			'title' => sprintf( __( 'Sökresultat för ”%s”', 'nordicinterior' ), get_search_query( false ) ),
			'intro' => $chips ? __( 'Vi tolkade din sökning som filter:', 'nordicinterior' ) : '',
			'chips' => $chips,
		);
	}

	if ( is_page_template( 'page-templates/sale.php' ) ) {
		$chips = array();
		foreach ( ni_get_terms( 'ni_room', array( 'hide_empty' => true ) ) as $room ) {
			$chips[] = array( $room->name, ni_filter_toggle_url( 'f_rum', $room->slug ), in_array( $room->slug, ni_active_filters()['tax']['f_rum'] ?? array(), true ) );
		}
		return array(
			'title'   => get_the_title(),
			'intro'   => has_excerpt() ? get_the_excerpt() : __( 'Alla produkter som är på rea just nu – största rabatt först.', 'nordicinterior' ),
			'eyebrow' => __( 'Rea', 'nordicinterior' ),
			'chips'   => $chips,
		);
	}

	$term = get_queried_object();
	if ( $term instanceof WP_Term ) {
		$chips    = array();
		$children = get_terms(
			array(
				'taxonomy'   => $term->taxonomy,
				'parent'     => $term->term_id,
				'hide_empty' => true,
			)
		);
		if ( ! is_wp_error( $children ) ) {
			foreach ( $children as $child ) {
				$chips[] = array( $child->name, (string) get_term_link( $child ), false );
			}
		}
		// Rooms and styles: link to their category landings.
		if ( in_array( $term->taxonomy, array( 'ni_room', 'ni_style' ), true ) ) {
			foreach ( ni_landing_categories_for( $term, 10 ) as $cat ) {
				$chips[] = array( $cat->name, ni_landing_url( $term, $cat ), false );
			}
		}
		$intro = term_description( $term );
		if ( 'ni_style' === $term->taxonomy && ! $intro ) {
			$intro = (string) ni_term_meta( $term->term_id, 'lead', '' );
		}
		$image = '';
		if ( in_array( $term->taxonomy, array( 'ni_room', 'ni_style' ), true ) && ni_term_has_image( $term ) ) {
			$image = ni_term_image(
				$term,
				'ni-wide',
				array(
					'fetchpriority' => 'high',
					'loading'       => 'eager',
				)
			);
		}
		$eyebrows = array(
			'ni_room'       => __( 'Rum', 'nordicinterior' ),
			'ni_style'      => __( 'Stil', 'nordicinterior' ),
			'product_brand' => __( 'Varumärke', 'nordicinterior' ),
			'ni_store'      => __( 'Butik', 'nordicinterior' ),
		);
		if ( 'ni_store' === $term->taxonomy ) {
			$facts = array_filter( array( ni_term_meta( $term->term_id, 'shipping' ), ni_term_meta( $term->term_id, 'returns' ), ni_term_meta( $term->term_id, 'delivery' ) ) );
			if ( $facts ) {
				$intro .= '<p class="ni-store-facts">' . esc_html( implode( ' · ', $facts ) ) . '</p>';
			}
		}
		if ( 'product_brand' === $term->taxonomy ) {
			$country = (string) ni_term_meta( $term->term_id, 'country' );
			if ( $country ) {
				$intro .= '<p class="ni-store-facts">' . esc_html( $country ) . '</p>';
			}
		}
		return array(
			'title'   => $term->name,
			'intro'   => $intro,
			'eyebrow' => $eyebrows[ $term->taxonomy ] ?? '',
			'image'   => $image,
			'chips'   => $chips,
			'tone'    => (string) ni_term_meta( $term->term_id, 'tone', '' ),
		);
	}

	return array(
		'title' => function_exists( 'is_shop' ) && is_shop() ? woocommerce_page_title( false ) : get_the_title(),
	);
}

/**
 * Does a term have its own image?
 *
 * @param WP_Term $term Term.
 * @return bool
 */
function ni_term_has_image( $term ) {
	return (bool) ni_term_meta( $term->term_id, 'image', 0 );
}
