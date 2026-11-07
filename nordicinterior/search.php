<?php
/**
 * Search results. Product searches use the listing with smart filters
 * (words matching rooms, styles, brands, materials or categories become
 * filters). Article searches (?post_type=post) show editorial cards.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

if ( ni_is_partial() ) {
	get_template_part( 'template-parts/listing' );
	return;
}

get_header();

if ( 'post' === get_query_var( 'post_type' ) ) {
	ni_listing_header(
		array(
			/* translators: %s: search query. */
			'title' => sprintf( __( 'Artiklar om ”%s”', 'nordicinterior' ), get_search_query( false ) ),
		)
	);
	echo '<div class="ni-container ni-page-body"><div class="ni-post-grid">';
	while ( have_posts() ) {
		the_post();
		ni_post_card( get_post(), 'small' );
	}
	echo '</div>';
	ni_pagination( null, 'numbers' );
	echo '</div>';
	get_footer();
	return;
}
?>
<section class="ni-search-hero">
	<div class="ni-container ni-container--narrow">
		<?php ni_search_form( array( 'id' => 'ni-page-search' ) ); ?>
	</div>
</section>
<?php ni_listing_header( ni_current_listing_header() ); ?>
<div class="ni-container ni-page-body">
	<?php
	$ni_suggest = ni_did_you_mean( get_search_query( false ) );
	if ( $ni_suggest && have_posts() ) {
		printf(
			'<p class="ni-did-you-mean">%1$s <a href="%2$s">%3$s</a>?</p>',
			esc_html__( 'Menade du', 'nordicinterior' ),
			esc_url(
				add_query_arg(
					array(
						's'         => rawurlencode( $ni_suggest ),
						'post_type' => 'product',
					),
					home_url( '/' )
				)
			),
			esc_html( $ni_suggest )
		);
	}
	get_template_part( 'template-parts/listing' );

	$ni_articles = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			's'              => get_search_query( false ),
			'posts_per_page' => 3,
			'no_found_rows'  => true,
		)
	);
	if ( $ni_articles ) :
		?>
		<section class="ni-section" aria-labelledby="ni-search-articles">
			<?php
			ni_section_header(
				__( 'Artiklar', 'nordicinterior' ),
				add_query_arg(
					array(
						's'         => rawurlencode( get_search_query( false ) ),
						'post_type' => 'post',
					),
					home_url( '/' )
				),
				__( 'Alla artiklar', 'nordicinterior' ),
				'',
				'ni-search-articles'
			);
			?>
			<div class="ni-post-grid">
				<?php foreach ( $ni_articles as $ni_post ) : ?>
					<?php ni_post_card( $ni_post, 'small' ); ?>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>
</div>
<?php
get_footer();
