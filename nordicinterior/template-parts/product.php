<?php
/**
 * Single product layout.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

$ni_product = wc_get_product( get_the_ID() );
if ( ! $ni_product ) {
	return;
}
$ni_brand    = ni_product_brand( $ni_product );
$ni_designer = ni_product_designer( $ni_product );
$ni_store    = ni_store_info( $ni_product );
$ni_discount = ni_product_discount( $ni_product );
$ni_drop     = ni_price_drop( $ni_product );
$ni_images   = array_values( array_filter( array_merge( array( (int) $ni_product->get_image_id() ), array_map( 'intval', $ni_product->get_gallery_image_ids() ) ) ) );
$ni_colors   = ni_product_colors( $ni_product );
$ni_updated  = ni_price_updated( $ni_product );

do_action( 'woocommerce_before_single_product' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce hook (notices).
?>
<div class="ni-container ni-product-top">
	<?php ni_breadcrumbs(); ?>
</div>

<article id="product-<?php the_ID(); ?>" <?php wc_product_class( 'ni-product', $ni_product ); ?>>
	<div class="ni-container ni-product__layout">
		<div class="ni-product__gallery" data-ni-gallery>
			<div class="ni-product__stage">
				<?php if ( $ni_images ) : ?>
					<ul class="ni-product__slides" role="list">
						<?php foreach ( $ni_images as $ni_i => $ni_image ) : ?>
							<li class="ni-product__slide" id="ni-slide-<?php echo esc_attr( (string) $ni_i ); ?>">
								<?php
								echo wp_get_attachment_image(
									$ni_image,
									'woocommerce_single',
									false,
									array(
										'loading'       => 0 === $ni_i ? 'eager' : 'lazy',
										'fetchpriority' => 0 === $ni_i ? 'high' : 'auto',
										'sizes'         => '(min-width: 1024px) 55vw, 100vw',
										'alt'           => 0 === $ni_i ? $ni_product->get_name() : (string) get_post_meta( $ni_image, '_wp_attachment_image_alt', true ),
									)
								);
								?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<span class="ni-placeholder"><?php ni_the_icon( 'chair', 64 ); ?></span>
				<?php endif; ?>
				<?php if ( $ni_discount ) : ?>
					<span class="ni-label ni-label--sale ni-product__label">
						<?php
						/* translators: %d: discount percentage. */
						printf( esc_html__( '−%d %%', 'nordicinterior' ), (int) $ni_discount );
						?>
					</span>
				<?php endif; ?>
			</div>
			<?php if ( count( $ni_images ) > 1 ) : ?>
				<ul class="ni-product__thumbs" role="list">
					<?php foreach ( $ni_images as $ni_i => $ni_image ) : ?>
						<li>
							<a href="#ni-slide-<?php echo esc_attr( (string) $ni_i ); ?>" class="ni-product__thumb" data-ni-thumb="<?php echo esc_attr( (string) $ni_i ); ?>" <?php echo 0 === $ni_i ? 'aria-current="true"' : ''; ?>>
								<?php
								echo wp_get_attachment_image(
									$ni_image,
									'thumbnail',
									false,
									array(
										'loading' => 'lazy',
										/* translators: %d: image number. */
										'alt'     => sprintf( __( 'Visa bild %d', 'nordicinterior' ), $ni_i + 1 ),
									)
								);
								?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<div class="ni-product__info">
			<?php if ( $ni_brand ) : ?>
				<p class="ni-eyebrow"><a href="<?php echo esc_url( (string) get_term_link( $ni_brand ) ); ?>"><?php echo esc_html( $ni_brand->name ); ?></a></p>
			<?php endif; ?>
			<h1 class="ni-product__title"><?php echo esc_html( $ni_product->get_name() ); ?></h1>
			<?php if ( $ni_designer ) : ?>
				<p class="ni-product__designer">
					<?php
					/* translators: %s: designer name. */
					printf( esc_html__( 'Design: %s', 'nordicinterior' ), esc_html( $ni_designer ) );
					?>
				</p>
			<?php endif; ?>

			<div class="ni-product__price">
				<?php echo wp_kses_post( ni_price_html( $ni_product ) ); ?>
				<?php if ( $ni_discount ) : ?>
					<span class="ni-label ni-label--sale-text">
						<?php
						/* translators: %d: discount percentage. */
						printf( esc_html__( 'Spara %d %%', 'nordicinterior' ), (int) $ni_discount );
						?>
					</span>
				<?php elseif ( $ni_drop ) : ?>
					<span class="ni-label ni-label--drop"><?php ni_the_icon( 'trend-down', 14 ); ?><?php esc_html_e( 'Prissänkt', 'nordicinterior' ); ?></span>
				<?php endif; ?>
			</div>

			<?php if ( $ni_colors ) : ?>
				<div class="ni-product__colors">
					<span class="ni-product__colors-label"><?php esc_html_e( 'Färger:', 'nordicinterior' ); ?></span>
					<span class="ni-swatches">
						<?php foreach ( $ni_colors as $ni_color ) : ?>
							<span class="ni-swatch ni-swatch--lg" style="--c:<?php echo esc_attr( $ni_color['hex'] ); ?>" title="<?php echo esc_attr( $ni_color['name'] ); ?>"></span>
						<?php endforeach; ?>
					</span>
					<span><?php echo esc_html( implode( ', ', wp_list_pluck( $ni_colors, 'name' ) ) ); ?></span>
				</div>
			<?php endif; ?>

			<div class="ni-product__cta">
				<?php ni_cta( $ni_product, 'product', 'large' ); ?>
			</div>

			<?php if ( $ni_store['name'] ) : ?>
				<ul class="ni-store-info" role="list">
					<li>
						<?php ni_the_icon( 'shop', 18 ); ?>
						<span>
							<?php
							printf(
								/* translators: %s: store name with link. */
								esc_html__( 'Säljs av %s', 'nordicinterior' ),
								'<a href="' . esc_url( $ni_store['url'] ) . '">' . esc_html( $ni_store['name'] ) . '</a>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped inline.
							);
							?>
						</span>
					</li>
					<?php if ( $ni_store['delivery'] ) : ?>
						<li><?php ni_the_icon( 'clock', 18 ); ?><span><?php echo esc_html__( 'Leveranstid:', 'nordicinterior' ) . ' ' . esc_html( $ni_store['delivery'] ); ?></span></li>
					<?php endif; ?>
					<?php if ( $ni_store['shipping'] ) : ?>
						<li><?php ni_the_icon( 'truck', 18 ); ?><span><?php echo esc_html( $ni_store['shipping'] ); ?></span></li>
					<?php endif; ?>
					<?php if ( $ni_store['returns'] ) : ?>
						<li><?php ni_the_icon( 'return', 18 ); ?><span><?php echo esc_html( $ni_store['returns'] ); ?></span></li>
					<?php endif; ?>
				</ul>
			<?php endif; ?>

			<?php if ( $ni_updated ) : ?>
				<p class="ni-product__updated">
					<?php
					/* translators: %s: date. */
					printf( esc_html__( 'Pris senast uppdaterat %s. Priset kan ha ändrats hos butiken.', 'nordicinterior' ), esc_html( wp_date( 'j F Y', $ni_updated ) ) );
					?>
				</p>
			<?php endif; ?>

			<?php if ( $ni_product->get_short_description() ) : ?>
				<div class="ni-product__lead"><?php echo wp_kses_post( wpautop( $ni_product->get_short_description() ) ); ?></div>
			<?php endif; ?>

			<?php ni_disclosure( 'product' ); ?>
		</div>
	</div>

	<div class="ni-container ni-product__details">
		<div class="ni-tabs ni-tabs--product" data-ni-tabs>
			<div class="ni-tabs__list" role="tablist" aria-label="<?php esc_attr_e( 'Produktinformation', 'nordicinterior' ); ?>">
				<button type="button" role="tab" class="ni-tabs__tab" id="ni-tab-desc" aria-controls="ni-panel-desc" aria-selected="true"><?php esc_html_e( 'Beskrivning', 'nordicinterior' ); ?></button>
				<button type="button" role="tab" class="ni-tabs__tab" id="ni-tab-spec" aria-controls="ni-panel-spec" aria-selected="false" tabindex="-1"><?php esc_html_e( 'Specifikationer', 'nordicinterior' ); ?></button>
			</div>
			<div class="ni-tabs__panel ni-prose" role="tabpanel" id="ni-panel-desc" aria-labelledby="ni-tab-desc" tabindex="0">
				<?php
				$ni_description = $ni_product->get_description();
				echo $ni_description ? wp_kses_post( wpautop( do_shortcode( $ni_description ) ) ) : '<p>' . esc_html__( 'Ingen beskrivning ännu.', 'nordicinterior' ) . '</p>';
				?>
			</div>
			<div class="ni-tabs__panel" role="tabpanel" id="ni-panel-spec" aria-labelledby="ni-tab-spec" tabindex="0" hidden>
				<div class="ni-specs">
					<table class="ni-specs__table">
						<tbody>
							<?php foreach ( ni_product_specs( $ni_product ) as $ni_row ) : ?>
								<tr>
									<th scope="row"><?php echo esc_html( $ni_row['label'] ); ?></th>
									<td>
										<?php if ( ! empty( $ni_row['url'] ) ) : ?>
											<a href="<?php echo esc_url( $ni_row['url'] ); ?>"><?php echo esc_html( $ni_row['value'] ); ?></a>
										<?php else : ?>
											<?php echo esc_html( $ni_row['value'] ); ?>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
					<?php echo ni_dimension_sketch( ni_product_dims( $ni_product ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped inside. ?>
				</div>
			</div>
		</div>
	</div>

	<?php
	$ni_rails = array(
		array(
			'title' => __( 'Passar ihop med', 'nordicinterior' ),
			'ids'   => ni_related_matching( $ni_product ),
			'url'   => '',
		),
		array(
			'title' => __( 'Liknande till lägre pris', 'nordicinterior' ),
			'ids'   => ni_related_cheaper( $ni_product ),
			'url'   => '',
		),
	);
	$ni_more  = ni_related_more_from( $ni_product );
	if ( $ni_more['ids'] ) {
		$ni_rails[] = array(
			'title' => $ni_more['label'],
			'ids'   => $ni_more['ids'],
			'url'   => $ni_more['url'],
		);
	}
	foreach ( $ni_rails as $ni_i => $ni_rail ) :
		if ( ! $ni_rail['ids'] ) {
			continue;
		}
		?>
		<section class="ni-section ni-section--rail" aria-labelledby="ni-rail-<?php echo esc_attr( (string) $ni_i ); ?>">
			<div class="ni-container">
				<?php ni_section_header( $ni_rail['title'], $ni_rail['url'], '', '', 'ni-rail-' . $ni_i ); ?>
				<?php ni_product_rail( $ni_rail['ids'], 'product', $ni_rail['title'] ); ?>
			</div>
		</section>
	<?php endforeach; ?>

	<?php $ni_posts = ni_posts_featuring_product( $ni_product->get_id(), 3 ); ?>
	<?php if ( $ni_posts ) : ?>
		<section class="ni-section ni-section--inspiration" aria-labelledby="ni-rail-insp">
			<div class="ni-container">
				<?php ni_section_header( __( 'Inspiration där produkten förekommer', 'nordicinterior' ), '', '', '', 'ni-rail-insp' ); ?>
				<div class="ni-post-grid">
					<?php foreach ( $ni_posts as $ni_post ) : ?>
						<?php ni_post_card( $ni_post, 'small' ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>
</article>

<?php
do_action( 'woocommerce_after_single_product' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce hook.
