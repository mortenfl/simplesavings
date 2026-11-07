<?php
/**
 * Product listings: shop, categories, rooms, styles, brands, stores and the
 * room/style + category landing pages.
 *
 * Overrides woocommerce/templates/archive-product.php.
 *
 * @package NordicInterior
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

// Product searches use the theme's search template (search hero, smart filters, articles).
if ( is_search() ) {
	load_template( locate_template( 'search.php' ), false );
	return;
}

// AJAX filters request only the result fragment.
if ( ni_is_partial() ) {
	get_template_part( 'template-parts/listing' );
	return;
}

get_header();
ni_listing_header( ni_current_listing_header() );
?>
<div class="ni-container ni-page-body">
	<?php
	/**
	 * Hook kept for plugin compatibility (notices etc.).
	 */
	do_action( 'woocommerce_before_main_content' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce hook.

	$ni_term = get_queried_object();
	if ( $ni_term instanceof WP_Term && ! get_query_var( 'ni_landing' ) ) {
		$ni_campaigns = ni_campaigns_for_term( $ni_term->taxonomy, $ni_term->term_id );
		if ( $ni_campaigns ) {
			echo '<div class="ni-listing-campaign">';
			ni_campaign_card( $ni_campaigns[0], 'compact', 'campaign' );
			echo '</div>';
		}
	}

	get_template_part( 'template-parts/listing' );

	do_action( 'woocommerce_after_main_content' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce hook.
	?>
</div>
<?php
get_footer();
