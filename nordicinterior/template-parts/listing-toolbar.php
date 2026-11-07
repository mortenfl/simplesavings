<?php
/**
 * Listing toolbar: count, active filter chips, sorting and view.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

$ni_query  = $args['query'];
$ni_active = ni_active_filters();
$ni_sort   = $ni_active['sort'] ? $ni_active['sort'] : ( is_page_template( 'page-templates/sale.php' ) ? 'rabatt' : 'rekommenderat' );
$ni_taxes  = ni_filter_taxonomies();
?>
<div class="ni-toolbar" data-ni-toolbar>
	<div class="ni-toolbar__row">
		<button type="button" class="ni-btn ni-btn--outline ni-btn--small ni-toolbar__filter" aria-controls="ni-filters" aria-expanded="false" data-ni-filters-open>
			<?php ni_the_icon( 'filter', 18 ); ?>
			<span><?php esc_html_e( 'Filter', 'nordicinterior' ); ?></span>
			<?php
			$ni_count = array_sum( array_map( 'count', $ni_active['tax'] ) ) + count( $ni_active['range'] ) + ( $ni_active['sale'] ? 1 : 0 );
			if ( $ni_count ) {
				echo '<span class="ni-badge">' . esc_html( (string) $ni_count ) . '</span>';
			}
			?>
		</button>
		<p class="ni-toolbar__count" data-ni-count>
			<?php
			/* translators: %s: number of products. */
			echo esc_html( sprintf( _n( '%s produkt', '%s produkter', (int) $ni_query->found_posts, 'nordicinterior' ), number_format_i18n( (int) $ni_query->found_posts ) ) );
			?>
		</p>
		<div class="ni-toolbar__controls">
			<label class="ni-select">
				<span class="screen-reader-text"><?php esc_html_e( 'Sortera', 'nordicinterior' ); ?></span>
				<select name="sortera" form="ni-filter-form" data-ni-sort>
					<?php foreach ( ni_sort_options() as $ni_key => $ni_label ) : ?>
						<option value="<?php echo esc_attr( $ni_key ); ?>" <?php selected( $ni_sort, $ni_key ); ?>><?php echo esc_html( $ni_label ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<div class="ni-view" role="group" aria-label="<?php esc_attr_e( 'Visning', 'nordicinterior' ); ?>">
				<a class="ni-iconbtn<?php echo 'rutnat' === $ni_active['view'] ? ' is-active' : ''; ?>" href="<?php echo esc_url( remove_query_arg( array( 'visa', 'ni_partial' ) ) ); ?>" data-ni-filter-link <?php echo 'rutnat' === $ni_active['view'] ? 'aria-current="true"' : ''; ?>><?php ni_the_icon( 'grid', 20, __( 'Rutnät', 'nordicinterior' ) ); ?></a>
				<a class="ni-iconbtn<?php echo 'lista' === $ni_active['view'] ? ' is-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'visa', 'lista', remove_query_arg( 'ni_partial' ) ) ); ?>" data-ni-filter-link <?php echo 'lista' === $ni_active['view'] ? 'aria-current="true"' : ''; ?>><?php ni_the_icon( 'list', 20, __( 'Lista', 'nordicinterior' ) ); ?></a>
			</div>
		</div>
	</div>
	<?php if ( ni_has_active_filters() ) : ?>
		<ul class="ni-active-filters" role="list" aria-label="<?php esc_attr_e( 'Valda filter', 'nordicinterior' ); ?>">
			<?php foreach ( $ni_active['tax'] as $ni_param => $ni_slugs ) : ?>
				<?php
				foreach ( $ni_slugs as $ni_slug ) :
					$ni_term = get_term_by( 'slug', $ni_slug, $ni_taxes[ $ni_param ]['tax'] );
					if ( ! $ni_term ) {
						continue;
					}
					?>
					<li>
						<a class="ni-chip is-active" href="<?php echo esc_url( ni_filter_toggle_url( $ni_param, $ni_slug ) ); ?>" data-ni-filter-link>
							<?php echo esc_html( $ni_term->name ); ?> <?php ni_the_icon( 'close', 14 ); ?>
							<span class="screen-reader-text"><?php esc_html_e( '(ta bort filter)', 'nordicinterior' ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			<?php endforeach; ?>
			<?php foreach ( $ni_active['range'] as $ni_key => $ni_range ) : ?>
				<?php $ni_def = ni_filter_ranges()[ $ni_key ]; ?>
				<li>
					<a class="ni-chip is-active" href="<?php echo esc_url( remove_query_arg( array( $ni_key . '_min', $ni_key . '_max', 'paged', 'ni_partial' ) ) ); ?>" data-ni-filter-link>
						<?php
						echo esc_html(
							$ni_def['label'] . ' ' .
							( null !== $ni_range['min'] ? number_format_i18n( $ni_range['min'] ) : '0' ) . '–' .
							( null !== $ni_range['max'] ? number_format_i18n( $ni_range['max'] ) : '' ) . ' ' . $ni_def['unit']
						);
						?>
						<?php ni_the_icon( 'close', 14 ); ?>
					</a>
				</li>
			<?php endforeach; ?>
			<?php if ( $ni_active['sale'] && ! is_page_template( 'page-templates/sale.php' ) ) : ?>
				<li><a class="ni-chip is-active" href="<?php echo esc_url( remove_query_arg( array( 'rea', 'paged', 'ni_partial' ) ) ); ?>" data-ni-filter-link><?php esc_html_e( 'Rea', 'nordicinterior' ); ?> <?php ni_the_icon( 'close', 14 ); ?></a></li>
			<?php endif; ?>
			<li><a class="ni-link-arrow ni-clear-filters" href="<?php echo esc_url( ni_listing_clean_url() ); ?>" data-ni-filter-link><?php esc_html_e( 'Rensa alla', 'nordicinterior' ); ?></a></li>
		</ul>
	<?php endif; ?>
</div>
