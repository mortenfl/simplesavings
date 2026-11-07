<?php
/**
 * "Shoppa looken": a room image with clickable product points.
 *
 * Available as a block (nordicinterior/shop-the-look) and as a shortcode:
 *   [ni_shop_the_look image="123" points="45:30:62|46:71:40" title="Vardagsrum i ek"]
 * where each point is product_id:x%:y%.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register block and shortcode.
 */
function ni_register_look() {
	wp_register_script(
		'ni-look-block',
		NI_URI . '/assets/js/look-block.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-data', 'wp-i18n', 'wp-server-side-render' ),
		NI_VERSION,
		true
	);
	wp_set_script_translations( 'ni-look-block', 'nordicinterior', NI_DIR . '/languages' );

	register_block_type(
		'nordicinterior/shop-the-look',
		array(
			'api_version'     => 3,
			'title'           => __( 'Shoppa looken', 'nordicinterior' ),
			'category'        => 'media',
			'icon'            => 'location',
			'description'     => __( 'Rumsbild med klickbara produktpunkter.', 'nordicinterior' ),
			'editor_script'   => 'ni-look-block',
			'render_callback' => 'ni_look_block_render',
			'supports'        => array(
				'align' => array( 'wide', 'full' ),
				'html'  => false,
			),
			'attributes'      => array(
				'image'  => array(
					'type'    => 'number',
					'default' => 0,
				),
				'title'  => array(
					'type'    => 'string',
					'default' => '',
				),
				'points' => array(
					'type'    => 'array',
					'default' => array(),
					'items'   => array(
						'type'       => 'object',
						'properties' => array(
							'id' => array( 'type' => 'number' ),
							'x'  => array( 'type' => 'number' ),
							'y'  => array( 'type' => 'number' ),
						),
					),
				),
				'align'  => array(
					'type'    => 'string',
					'default' => '',
				),
			),
		)
	);

	add_shortcode( 'ni_shop_the_look', 'ni_look_shortcode' );
}
add_action( 'init', 'ni_register_look' );

/**
 * Block render callback.
 *
 * @param array $attributes Attributes.
 * @return string
 */
function ni_look_block_render( $attributes ) {
	$points = array();
	foreach ( (array) ( $attributes['points'] ?? array() ) as $point ) {
		$points[] = array(
			'id' => absint( $point['id'] ?? 0 ),
			'x'  => (float) ( $point['x'] ?? 50 ),
			'y'  => (float) ( $point['y'] ?? 50 ),
		);
	}
	return ni_render_look( absint( $attributes['image'] ?? 0 ), $points, (string) ( $attributes['title'] ?? '' ), (string) ( $attributes['align'] ?? '' ) );
}

/**
 * Shortcode callback.
 *
 * @param array $atts Attributes.
 * @return string
 */
function ni_look_shortcode( $atts ) {
	$atts   = shortcode_atts(
		array(
			'image'  => 0,
			'points' => '',
			'title'  => '',
		),
		$atts,
		'ni_shop_the_look'
	);
	$points = array();
	foreach ( explode( '|', (string) $atts['points'] ) as $chunk ) {
		$parts = array_map( 'trim', explode( ':', $chunk ) );
		if ( count( $parts ) === 3 ) {
			$points[] = array(
				'id' => absint( $parts[0] ),
				'x'  => (float) $parts[1],
				'y'  => (float) $parts[2],
			);
		}
	}
	return ni_render_look( absint( $atts['image'] ), $points, sanitize_text_field( (string) $atts['title'] ) );
}

/**
 * Render a look.
 *
 * @param int    $image_id Attachment ID.
 * @param array  $points   Points.
 * @param string $title    Optional title.
 * @param string $align    Block alignment.
 * @return string
 */
function ni_render_look( $image_id, $points, $title = '', $align = '' ) {
	static $instance = 0;
	++$instance;

	if ( ! $image_id || ! ni_wc() ) {
		return '';
	}
	$products = array();
	foreach ( $points as $point ) {
		$product = wc_get_product( $point['id'] );
		if ( $product && 'publish' === $product->get_status() ) {
			$products[] = array(
				'product' => $product,
				'x'       => max( 0, min( 100, $point['x'] ) ),
				'y'       => max( 0, min( 100, $point['y'] ) ),
			);
		}
	}

	$uid = 'ni-look-' . $instance;
	ob_start();
	?>
	<figure class="ni-look<?php echo $align ? ' align' . esc_attr( $align ) : ''; ?>" data-ni-look id="<?php echo esc_attr( $uid ); ?>">
		<div class="ni-look__stage">
			<?php
			echo wp_get_attachment_image(
				$image_id,
				'ni-wide',
				false,
				array(
					'class'   => 'ni-look__img',
					'loading' => 'lazy',
					'sizes'   => '(min-width: 1200px) 1200px, 100vw',
				)
			);
			foreach ( $products as $i => $item ) :
				$product = $item['product'];
				$pop_id  = $uid . '-p' . $i;
				?>
				<button
					type="button"
					class="ni-look__dot"
					style="left:<?php echo esc_attr( (string) $item['x'] ); ?>%;top:<?php echo esc_attr( (string) $item['y'] ); ?>%"
					aria-expanded="false"
					aria-controls="<?php echo esc_attr( $pop_id ); ?>"
					data-ni-look-dot
				>
					<span class="screen-reader-text">
						<?php
						/* translators: %s: product name. */
						printf( esc_html__( 'Visa produkt: %s', 'nordicinterior' ), esc_html( $product->get_name() ) );
						?>
					</span>
					<span class="ni-look__dot-inner" aria-hidden="true"></span>
				</button>
				<div class="ni-look__pop<?php echo $item['x'] > 60 ? ' ni-look__pop--left' : ''; ?><?php echo $item['y'] > 55 ? ' ni-look__pop--up' : ''; ?>" id="<?php echo esc_attr( $pop_id ); ?>" role="dialog" aria-label="<?php echo esc_attr( $product->get_name() ); ?>" style="--x:<?php echo esc_attr( (string) $item['x'] ); ?>%;--y:<?php echo esc_attr( (string) $item['y'] ); ?>%" hidden>
					<button type="button" class="ni-look__close" data-ni-look-close>
						<?php ni_the_icon( 'close', 18, __( 'Stäng', 'nordicinterior' ) ); ?>
					</button>
					<?php ni_product_card( $product, 'look', 'mini' ); ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php if ( $title ) : ?>
			<figcaption class="ni-look__caption"><?php echo esc_html( $title ); ?></figcaption>
		<?php endif; ?>
		<?php if ( $products ) : ?>
			<div class="ni-look__list">
				<p class="ni-eyebrow"><?php esc_html_e( 'Produkter i looken', 'nordicinterior' ); ?></p>
				<ul class="ni-rail" role="list">
					<?php foreach ( $products as $item ) : ?>
						<li class="ni-rail__item"><?php ni_product_card( $item['product'], 'look' ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
	</figure>
	<?php
	return (string) ob_get_clean();
}

/**
 * Extract product IDs used in looks (blocks + shortcodes) of a post.
 *
 * @param string $content Post content.
 * @return int[]
 */
function ni_look_product_ids( $content ) {
	$ids = array();
	if ( has_blocks( $content ) ) {
		$walk = static function ( $blocks ) use ( &$walk, &$ids ) {
			foreach ( $blocks as $block ) {
				if ( 'nordicinterior/shop-the-look' === $block['blockName'] ) {
					foreach ( (array) ( $block['attrs']['points'] ?? array() ) as $point ) {
						$ids[] = absint( $point['id'] ?? 0 );
					}
				}
				if ( ! empty( $block['innerBlocks'] ) ) {
					$walk( $block['innerBlocks'] );
				}
			}
		};
		$walk( parse_blocks( $content ) );
	}
	if ( preg_match_all( '/\[ni_shop_the_look[^\]]*points="([^"]+)"/', $content, $m ) ) {
		foreach ( $m[1] as $points ) {
			foreach ( explode( '|', $points ) as $chunk ) {
				$ids[] = absint( strtok( $chunk, ':' ) );
			}
		}
	}
	return array_values( array_unique( array_filter( $ids ) ) );
}

/**
 * First look in a post, rendered (for the front page section).
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ni_first_look_in_post( $post_id ) {
	$content = (string) get_post_field( 'post_content', $post_id );
	foreach ( parse_blocks( $content ) as $block ) {
		if ( 'nordicinterior/shop-the-look' === $block['blockName'] ) {
			return render_block( $block );
		}
	}
	if ( preg_match( '/\[ni_shop_the_look[^\]]*\]/', $content, $m ) ) {
		return do_shortcode( $m[0] );
	}
	return '';
}

/**
 * Posts that contain a look (for the Customizer select).
 *
 * @return array<int,string>
 */
function ni_posts_with_looks() {
	$posts = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			's'              => 'shop-the-look',
			'no_found_rows'  => true,
		)
	);
	$posts = array_merge(
		$posts,
		get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 50,
				's'              => 'ni_shop_the_look',
				'no_found_rows'  => true,
			)
		)
	);
	$out   = array( 0 => __( 'Senaste inlägget med Shoppa looken', 'nordicinterior' ) );
	foreach ( $posts as $post ) {
		$out[ $post->ID ] = get_the_title( $post );
	}
	return $out;
}
