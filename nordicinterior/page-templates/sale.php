<?php
/**
 * Template Name: Rea
 *
 * All products on sale, largest discount first, with quick room filters,
 * sorting, pagination and the Rea sidebar.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

$ni_paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$ni_sale  = ni_wc() ? array_map( 'intval', wc_get_product_ids_on_sale() ) : array();
$ni_args  = ni_listing_apply(
	array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => ni_wc_per_page(),
		'paged'          => $ni_paged,
		'post__in'       => $ni_sale ? $ni_sale : array( 0 ),
	),
	'rabatt'
);
$ni_query = new WP_Query( $ni_args );

if ( ni_is_partial() ) {
	get_template_part( 'template-parts/listing', null, array( 'query' => $ni_query ) );
	return;
}

get_header();
ni_listing_header( ni_current_listing_header() );
?>
<div class="ni-container ni-page-body ni-with-sidebar ni-with-sidebar--wide">
	<div class="ni-with-sidebar__main">
		<?php get_template_part( 'template-parts/listing', null, array( 'query' => $ni_query ) ); ?>
	</div>
	<aside class="ni-sidebar" aria-label="<?php esc_attr_e( 'Sidofält', 'nordicinterior' ); ?>">
		<?php ni_sidebar( 'sidebar-sale' ); ?>
	</aside>
</div>
<?php
get_footer();
