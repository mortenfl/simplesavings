<?php
/**
 * Single product: gallery on a warm grey surface, product information that
 * scrolls with the page (not sticky), a large "Till butiken" CTA, tabs and
 * related rails. No purchase functions.
 *
 * Overrides woocommerce/templates/single-product.php.
 *
 * @package NordicInterior
 * @version 1.6.4
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/product' );
endwhile;

get_footer();
