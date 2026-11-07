<?php
/**
 * Filter form. Plain GET form that works without JavaScript; filters.js
 * submits it with AJAX and keeps the URL in sync.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

$ni_facets = ni_listing_facets();
$ni_active = ni_active_filters();
$ni_skip   = array();
$ni_term   = get_queried_object();
if ( $ni_term instanceof WP_Term && ! get_query_var( 'ni_landing' ) ) {
	$ni_skip[] = $ni_term->taxonomy;
}
if ( get_query_var( 'ni_landing' ) ) {
	$ni_skip[] = 'style' === get_query_var( 'ni_landing' ) ? 'ni_style' : 'ni_room';
}
?>
<div class="ni-filters__head">
	<h2 class="ni-filters__title"><?php esc_html_e( 'Filter', 'nordicinterior' ); ?></h2>
	<button type="button" class="ni-iconbtn ni-filters__close" data-ni-filters-close>
		<?php ni_the_icon( 'close', 22, __( 'Stäng filter', 'nordicinterior' ) ); ?>
	</button>
</div>
<form class="ni-filters" id="ni-filter-form" method="get" action="<?php echo esc_url( ni_listing_clean_url() ); ?>" data-ni-filters>
	<?php if ( is_search() ) : ?>
		<input type="hidden" name="s" value="<?php echo esc_attr( get_search_query( false ) ); ?>">
		<input type="hidden" name="post_type" value="product">
	<?php endif; ?>
	<?php if ( 'lista' === $ni_active['view'] ) : ?>
		<input type="hidden" name="visa" value="lista">
	<?php endif; ?>

	<?php
	foreach ( ni_filter_taxonomies() as $ni_param => $ni_filter ) :
		if ( in_array( $ni_filter['tax'], $ni_skip, true ) && 'product_cat' !== $ni_filter['tax'] ) {
			continue;
		}
		$ni_items = $ni_facets['tax'][ $ni_filter['tax'] ] ?? array();
		// On a category archive only show its descendants as category options.
		if ( 'product_cat' === $ni_filter['tax'] && $ni_term instanceof WP_Term && 'product_cat' === $ni_term->taxonomy ) {
			$ni_children = get_term_children( $ni_term->term_id, 'product_cat' );
			$ni_items    = array_values(
				array_filter(
					$ni_items,
					static function ( $item ) use ( $ni_children ) {
						$t = get_term_by( 'slug', $item['slug'], 'product_cat' );
						return $t && in_array( $t->term_id, (array) $ni_children, true );
					}
				)
			);
		}
		if ( count( $ni_items ) < 2 && empty( $ni_active['tax'][ $ni_param ] ) ) {
			continue;
		}
		$ni_selected = $ni_active['tax'][ $ni_param ] ?? array();
		$ni_is_color = 'pa_farg' === $ni_filter['tax'];
		?>
		<details class="ni-filter<?php echo $ni_is_color ? ' ni-filter--colors' : ''; ?>" <?php echo $ni_selected || in_array( $ni_param, array( 'f_rum', 'f_stil', 'f_kategori', 'f_farg' ), true ) ? 'open' : ''; ?>>
			<summary class="ni-filter__summary">
				<?php echo esc_html( $ni_filter['label'] ); ?>
				<?php if ( $ni_selected ) : ?>
					<span class="ni-badge"><?php echo esc_html( (string) count( $ni_selected ) ); ?></span>
				<?php endif; ?>
				<?php ni_the_icon( 'chevron-down', 16 ); ?>
			</summary>
			<fieldset class="ni-filter__body">
				<legend class="screen-reader-text"><?php echo esc_html( $ni_filter['label'] ); ?></legend>
				<ul class="ni-filter__list" role="list">
					<?php foreach ( array_slice( $ni_items, 0, 40 ) as $ni_item ) : ?>
						<li>
							<label class="ni-check<?php echo $ni_is_color ? ' ni-check--swatch' : ''; ?>">
								<input type="checkbox" name="<?php echo esc_attr( $ni_param ); ?>[]" value="<?php echo esc_attr( $ni_item['slug'] ); ?>" <?php checked( in_array( $ni_item['slug'], $ni_selected, true ) ); ?>>
								<?php if ( $ni_is_color ) : ?>
									<span class="ni-swatch ni-swatch--lg" style="--c:<?php echo esc_attr( $ni_item['hex'] ?? '#ccc' ); ?>" aria-hidden="true"></span>
								<?php else : ?>
									<span class="ni-check__box" aria-hidden="true"></span>
								<?php endif; ?>
								<span class="ni-check__label"><?php echo esc_html( $ni_item['name'] ); ?></span>
								<span class="ni-check__count"><?php echo esc_html( (string) $ni_item['count'] ); ?></span>
							</label>
						</li>
					<?php endforeach; ?>
				</ul>
			</fieldset>
		</details>
	<?php endforeach; ?>

	<?php foreach ( ni_filter_ranges() as $ni_key => $ni_range ) : ?>
		<?php
		if ( empty( $ni_facets['range'][ $ni_key ] ) ) {
			continue;
		}
		$ni_bounds = $ni_facets['range'][ $ni_key ];
		$ni_cur    = $ni_active['range'][ $ni_key ] ?? array(
			'min' => null,
			'max' => null,
		);
		?>
		<details class="ni-filter" <?php echo isset( $ni_active['range'][ $ni_key ] ) || 'pris' === $ni_key ? 'open' : ''; ?>>
			<summary class="ni-filter__summary"><?php echo esc_html( $ni_range['label'] . ( 'pris' === $ni_key ? '' : ' (' . $ni_range['unit'] . ')' ) ); ?><?php ni_the_icon( 'chevron-down', 16 ); ?></summary>
			<fieldset class="ni-filter__body ni-range">
				<legend class="screen-reader-text"><?php echo esc_html( $ni_range['label'] ); ?></legend>
				<label>
					<span><?php esc_html_e( 'Från', 'nordicinterior' ); ?></span>
					<input type="number" inputmode="numeric" name="<?php echo esc_attr( $ni_key ); ?>_min" min="0" step="1" value="<?php echo esc_attr( null !== $ni_cur['min'] ? (string) $ni_cur['min'] : '' ); ?>" placeholder="<?php echo esc_attr( (string) $ni_bounds['min'] ); ?>">
				</label>
				<span aria-hidden="true">–</span>
				<label>
					<span><?php esc_html_e( 'Till', 'nordicinterior' ); ?></span>
					<input type="number" inputmode="numeric" name="<?php echo esc_attr( $ni_key ); ?>_max" min="0" step="1" value="<?php echo esc_attr( null !== $ni_cur['max'] ? (string) $ni_cur['max'] : '' ); ?>" placeholder="<?php echo esc_attr( (string) $ni_bounds['max'] ); ?>">
				</label>
				<span class="ni-range__unit"><?php echo esc_html( $ni_range['unit'] ); ?></span>
			</fieldset>
		</details>
	<?php endforeach; ?>

	<?php if ( ! is_page_template( 'page-templates/sale.php' ) ) : ?>
		<div class="ni-filter ni-filter--toggle">
			<label class="ni-check">
				<input type="checkbox" name="rea" value="1" <?php checked( $ni_active['sale'] ); ?>>
				<span class="ni-check__box" aria-hidden="true"></span>
				<span class="ni-check__label"><?php esc_html_e( 'Endast rea', 'nordicinterior' ); ?></span>
			</label>
		</div>
	<?php endif; ?>

	<div class="ni-filters__actions">
		<button type="submit" class="ni-btn ni-btn--block"><?php esc_html_e( 'Visa produkter', 'nordicinterior' ); ?></button>
		<a class="ni-btn ni-btn--text" href="<?php echo esc_url( ni_listing_clean_url() ); ?>" data-ni-filter-link><?php esc_html_e( 'Rensa', 'nordicinterior' ); ?></a>
	</div>
</form>
