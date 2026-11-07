<?php
/**
 * Product listing: filters, toolbar, grid and pagination.
 *
 * Used by category/room/style/brand/store archives, landing pages, search
 * and the sale page. $args['query'] may hold a custom WP_Query.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

global $wp_query;
$ni_query    = isset( $args['query'] ) && $args['query'] instanceof WP_Query ? $args['query'] : $wp_query;
$ni_position = ni_mod( 'filter_position' );
$ni_view     = ni_active_filters()['view'];
$ni_place    = is_search() ? 'search' : 'card';
?>
<div class="ni-listing ni-listing--filters-<?php echo esc_attr( $ni_position ); ?>" data-ni-listing>
	<?php if ( ni_wc() ) : ?>
		<div class="ni-listing__filters" id="ni-filters" data-ni-filter-panel>
			<?php get_template_part( 'template-parts/filters' ); ?>
		</div>
	<?php endif; ?>

	<div class="ni-listing__main">
		<div class="ni-results" data-ni-results aria-live="polite" aria-busy="false">
			<?php get_template_part( 'template-parts/listing', 'toolbar', array( 'query' => $ni_query ) ); ?>

			<?php if ( $ni_query->have_posts() ) : ?>
				<ul class="ni-grid<?php echo 'lista' === $ni_view ? ' ni-grid--list' : ''; ?>" role="list" data-ni-grid>
					<?php
					while ( $ni_query->have_posts() ) :
						$ni_query->the_post();
						echo '<li class="ni-grid__item">';
						ni_product_card( get_the_ID(), $ni_place, 'lista' === $ni_view ? 'list' : 'grid' );
						echo '</li>';
					endwhile;
					wp_reset_postdata();
					?>
				</ul>
				<div data-ni-pagination>
					<?php ni_pagination( $ni_query ); ?>
				</div>
			<?php else : ?>
				<div class="ni-empty">
					<?php ni_the_icon( 'chair', 48 ); ?>
					<h2><?php esc_html_e( 'Inga produkter matchar', 'nordicinterior' ); ?></h2>
					<?php
					if ( is_search() ) {
						$ni_suggest = ni_did_you_mean( get_search_query( false ) );
						if ( $ni_suggest ) {
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
					}
					?>
					<p><?php esc_html_e( 'Prova att ta bort ett filter eller sök på något annat.', 'nordicinterior' ); ?></p>
					<?php if ( ni_has_active_filters() ) : ?>
						<a class="ni-btn ni-btn--outline" href="<?php echo esc_url( ni_listing_clean_url() ); ?>" data-ni-filter-link><?php esc_html_e( 'Rensa filter', 'nordicinterior' ); ?></a>
					<?php endif; ?>
					<?php if ( is_search() ) : ?>
						<div class="ni-empty__search"><?php ni_search_form( array( 'id' => 'ni-empty-search' ) ); ?></div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php ni_disclosure( 'listing' ); ?>
	</div>
</div>
