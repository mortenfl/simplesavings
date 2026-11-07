<?php
/**
 * Default sidebar (blog).
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;
?>
<aside class="ni-sidebar" aria-label="<?php esc_attr_e( 'Sidofält', 'nordicinterior' ); ?>">
	<?php ni_sidebar( 'sidebar-blog' ); ?>
</aside>
