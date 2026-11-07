<?php
/**
 * Product card inside WooCommerce loops (shortcodes, widgets, blocks that
 * use the classic template). Uses the theme's minimal card.
 *
 * Overrides woocommerce/templates/content-product.php.
 *
 * @package NordicInterior
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}
?>
<li <?php wc_product_class( 'ni-grid__item', $product ); ?>>
	<?php ni_product_card( $product, 'card' ); ?>
</li>
