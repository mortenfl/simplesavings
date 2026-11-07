<?php
/**
 * Template Name: Alla butiker
 *
 * Store cards with logo, product count, shipping and returns, and a
 * "Kampanj just nu" marker when the store has an active campaign.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

get_header();

$ni_stores = ni_get_terms( 'ni_store', array( 'hide_empty' => false ) );

while ( have_posts() ) :
	the_post();
	ni_listing_header(
		array(
			'title' => get_the_title(),
			'intro' => has_excerpt() ? get_the_excerpt() : __( 'Vi jämför inredning från Nordens bästa butiker. Här ser du frakt, returrätt och leveranstid innan du klickar vidare.', 'nordicinterior' ),
		)
	);
endwhile;
?>
<div class="ni-container ni-page-body ni-with-sidebar">
	<div class="ni-with-sidebar__main">
		<ul class="ni-store-grid" role="list">
			<?php foreach ( $ni_stores as $ni_store ) : ?>
				<?php
				$ni_logo     = (int) ni_term_meta( $ni_store->term_id, 'logo', 0 );
				$ni_campaign = ni_campaigns_for_term( 'ni_store', $ni_store->term_id );
				?>
				<li>
					<article class="ni-store-card">
						<div class="ni-store-card__logo">
							<?php
							if ( $ni_logo ) {
								echo wp_get_attachment_image(
									$ni_logo,
									'ni-logo',
									false,
									array(
										'alt'     => $ni_store->name,
										'loading' => 'lazy',
									)
								);
							} else {
								echo '<span class="ni-store-card__wordmark">' . esc_html( $ni_store->name ) . '</span>';
							}
							?>
							<?php if ( $ni_campaign ) : ?>
								<span class="ni-label ni-label--campaign"><?php esc_html_e( 'Kampanj just nu', 'nordicinterior' ); ?></span>
							<?php endif; ?>
						</div>
						<h2 class="ni-store-card__name"><a href="<?php echo esc_url( (string) get_term_link( $ni_store ) ); ?>"><?php echo esc_html( $ni_store->name ); ?></a></h2>
						<p class="ni-store-card__count">
							<?php
							/* translators: %s: number of products. */
							echo esc_html( sprintf( _n( '%s produkt', '%s produkter', (int) $ni_store->count, 'nordicinterior' ), number_format_i18n( (int) $ni_store->count ) ) );
							?>
						</p>
						<ul class="ni-store-info" role="list">
							<?php
							foreach ( array(
								'shipping' => 'truck',
								'returns'  => 'return',
								'delivery' => 'clock',
							) as $ni_key => $ni_icon ) :
								$ni_value = (string) ni_term_meta( $ni_store->term_id, $ni_key );
								if ( '' === $ni_value ) {
									continue;
								}
								?>
								<li><?php ni_the_icon( $ni_icon, 18 ); ?><span><?php echo esc_html( $ni_value ); ?></span></li>
							<?php endforeach; ?>
						</ul>
						<a class="ni-link-arrow" href="<?php echo esc_url( (string) get_term_link( $ni_store ) ); ?>">
							<?php
							/* translators: %s: store name. */
							echo esc_html( sprintf( __( 'Alla produkter från %s', 'nordicinterior' ), $ni_store->name ) );
							?>
							<?php ni_the_icon( 'arrow-right', 16 ); ?>
						</a>
					</article>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php ni_disclosure( 'listing' ); ?>
	</div>
	<aside class="ni-sidebar" aria-label="<?php esc_attr_e( 'Sidofält', 'nordicinterior' ); ?>">
		<?php ni_sidebar( 'sidebar-stores' ); ?>
	</aside>
</div>
<?php
get_footer();
