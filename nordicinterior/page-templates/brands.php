<?php
/**
 * Template Name: Alla varumärken (A–Ö)
 *
 * Every brand in WooCommerce – also those without products – with logo,
 * country, product count, letter navigation and a filter field.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

get_header();

$ni_brands  = taxonomy_exists( 'product_brand' ) ? ni_get_terms(
	'product_brand',
	array(
		'hide_empty' => false,
		'orderby'    => 'name',
	)
) : array();
$ni_letters = array();
foreach ( $ni_brands as $ni_brand ) {
	$ni_first                  = mb_strtoupper( mb_substr( ltrim( $ni_brand->name, '&' ), 0, 1 ) );
	$ni_first                  = preg_match( '/\p{L}/u', $ni_first ) ? $ni_first : '#';
	$ni_letters[ $ni_first ][] = $ni_brand;
}
uksort(
	$ni_letters,
	static function ( $a, $b ) {
		$order = '#ABCDEFGHIJKLMNOPQRSTUVWXYZÅÄÖ';
		return (int) mb_strpos( $order, $a ) <=> (int) mb_strpos( $order, $b );
	}
);

while ( have_posts() ) :
	the_post();
	ni_listing_header(
		array(
			'title' => get_the_title(),
			'intro' => has_excerpt() ? get_the_excerpt() : __( 'Alla varumärken i katalogen, från A till Ö.', 'nordicinterior' ),
		)
	);
endwhile;
?>
<div class="ni-container ni-page-body ni-with-sidebar">
	<div class="ni-with-sidebar__main" data-ni-brand-filter>
		<div class="ni-brands-tools">
			<label class="ni-field">
				<span class="screen-reader-text"><?php esc_html_e( 'Filtrera varumärken', 'nordicinterior' ); ?></span>
				<?php ni_the_icon( 'search', 18 ); ?>
				<input type="search" placeholder="<?php esc_attr_e( 'Filtrera varumärken …', 'nordicinterior' ); ?>" data-ni-brand-input aria-controls="ni-brand-list">
			</label>
			<nav class="ni-letters" aria-label="<?php esc_attr_e( 'Bokstäver', 'nordicinterior' ); ?>">
				<ul role="list">
					<?php foreach ( mb_str_split( 'ABCDEFGHIJKLMNOPQRSTUVWXYZÅÄÖ' ) as $ni_letter ) : ?>
						<li>
							<?php if ( isset( $ni_letters[ $ni_letter ] ) ) : ?>
								<a href="#ni-letter-<?php echo esc_attr( sanitize_title( $ni_letter ) ); ?>"><?php echo esc_html( $ni_letter ); ?></a>
							<?php else : ?>
								<span aria-hidden="true"><?php echo esc_html( $ni_letter ); ?></span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>
			<p class="screen-reader-text" aria-live="polite" data-ni-brand-status></p>
		</div>

		<div id="ni-brand-list">
			<?php foreach ( $ni_letters as $ni_letter => $ni_group ) : ?>
				<section class="ni-letter-group" id="ni-letter-<?php echo esc_attr( sanitize_title( $ni_letter ) ); ?>" aria-labelledby="ni-letter-h-<?php echo esc_attr( sanitize_title( $ni_letter ) ); ?>">
					<h2 class="ni-letter-group__letter" id="ni-letter-h-<?php echo esc_attr( sanitize_title( $ni_letter ) ); ?>"><?php echo esc_html( $ni_letter ); ?></h2>
					<ul class="ni-brand-grid" role="list">
						<?php foreach ( $ni_group as $ni_brand ) : ?>
							<?php
							$ni_logo    = (int) ni_term_meta( $ni_brand->term_id, 'logo', 0 );
							$ni_logo    = $ni_logo ? $ni_logo : (int) get_term_meta( $ni_brand->term_id, 'thumbnail_id', true );
							$ni_country = (string) ni_term_meta( $ni_brand->term_id, 'country' );
							?>
							<li data-name="<?php echo esc_attr( mb_strtolower( $ni_brand->name ) ); ?>">
								<a class="ni-brand-card" href="<?php echo esc_url( (string) get_term_link( $ni_brand ) ); ?>">
									<span class="ni-brand-card__logo">
										<?php
										if ( $ni_logo ) {
											echo wp_get_attachment_image(
												$ni_logo,
												'ni-logo',
												false,
												array(
													'alt' => '',
													'loading' => 'lazy',
												)
											);
										} else {
											echo '<span class="ni-brand-card__initial" aria-hidden="true">' . esc_html( mb_substr( ltrim( $ni_brand->name, '&' ), 0, 1 ) ) . '</span>';
										}
										?>
									</span>
									<span class="ni-brand-card__name"><?php echo esc_html( $ni_brand->name ); ?></span>
									<span class="ni-brand-card__meta">
										<?php
										$ni_meta = array();
										if ( $ni_country ) {
											$ni_meta[] = $ni_country;
										}
										/* translators: %s: number of products. */
										$ni_meta[] = sprintf( _n( '%s produkt', '%s produkter', (int) $ni_brand->count, 'nordicinterior' ), number_format_i18n( (int) $ni_brand->count ) );
										echo esc_html( implode( ' · ', $ni_meta ) );
										?>
									</span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endforeach; ?>
			<?php if ( ! $ni_brands ) : ?>
				<p><?php esc_html_e( 'Inga varumärken ännu.', 'nordicinterior' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<aside class="ni-sidebar" aria-label="<?php esc_attr_e( 'Sidofält', 'nordicinterior' ); ?>">
		<?php ni_sidebar( 'sidebar-brands' ); ?>
	</aside>
</div>
<?php
get_footer();
