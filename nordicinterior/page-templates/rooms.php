<?php
/**
 * Template Name: Alla rum
 *
 * Image cards with description and popular categories per term.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	ni_listing_header(
		array(
			'title' => get_the_title(),
			'intro' => has_excerpt() ? get_the_excerpt() : __( 'Hitta möbler och inredning rum för rum.', 'nordicinterior' ),
		)
	);
endwhile;

$ni_terms = ni_get_terms( 'ni_room', array( 'parent' => 0 ) );
?>
<div class="ni-container ni-page-body">
	<ul class="ni-term-list" role="list">
		<?php foreach ( $ni_terms as $ni_term ) : ?>
			<li class="ni-term-list__item">
				<?php ni_term_card( $ni_term, 'large', true ); ?>
				<?php $ni_cats = ni_landing_categories_for( $ni_term, 6 ); ?>
				<?php if ( $ni_cats ) : ?>
					<ul class="ni-chips__list" role="list" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: room or style name. */ __( 'Populära kategorier för %s', 'nordicinterior' ), $ni_term->name ) ); ?>">
						<?php foreach ( $ni_cats as $ni_cat ) : ?>
							<li><a class="ni-chip" href="<?php echo esc_url( ni_landing_url( $ni_term, $ni_cat ) ); ?>"><?php echo esc_html( $ni_cat->name ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
<?php
get_footer();
