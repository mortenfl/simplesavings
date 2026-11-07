<?php
/**
 * Footer: dark warm surface, white logo, three widget areas, bottom row
 * with affiliate information, copyright and footer menu. Optional bottom
 * navigation on mobile.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<footer class="ni-footer">
	<div class="ni-container">
		<div class="ni-footer__widgets">
			<div class="ni-footer__col ni-footer__col--1"><?php ni_sidebar( 'footer-1' ); ?></div>
			<div class="ni-footer__col ni-footer__col--2"><?php ni_sidebar( 'footer-2' ); ?></div>
			<div class="ni-footer__col ni-footer__col--3"><?php ni_sidebar( 'footer-3' ); ?></div>
		</div>
		<div class="ni-footer__bottom">
			<?php ni_disclosure( 'footer' ); ?>
			<div class="ni-footer__legal">
				<p class="ni-footer__copy"><?php echo esc_html( str_replace( '{year}', wp_date( 'Y' ), (string) ni_mod( 'copyright' ) ) ); ?></p>
				<?php
				if ( has_nav_menu( 'footer' ) ) {
					wp_nav_menu(
						array(
							'theme_location'       => 'footer',
							'container'            => 'nav',
							'container_class'      => 'ni-footer__menu',
							'container_aria_label' => __( 'Sidfotsmeny', 'nordicinterior' ),
							'depth'                => 1,
						)
					);
				}
				?>
			</div>
		</div>
	</div>
</footer>

<?php if ( ni_mod( 'bottom_nav' ) ) : ?>
	<nav class="ni-bottom-nav" aria-label="<?php esc_attr_e( 'Snabbnavigering', 'nordicinterior' ); ?>">
		<ul role="list">
			<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>" <?php echo is_front_page() ? 'aria-current="page"' : ''; ?>><?php ni_the_icon( 'home', 22 ); ?><span><?php esc_html_e( 'Hem', 'nordicinterior' ); ?></span></a></li>
			<li><a href="<?php echo esc_url( ni_page_url( 'page-templates/rooms.php' ) ); ?>" <?php echo is_page_template( 'page-templates/rooms.php' ) || is_tax( 'ni_room' ) ? 'aria-current="page"' : ''; ?>><?php ni_the_icon( 'sofa', 22 ); ?><span><?php esc_html_e( 'Rum', 'nordicinterior' ); ?></span></a></li>
			<li><a href="<?php echo esc_url( ni_page_url( 'page-templates/brands.php' ) ); ?>" <?php echo is_page_template( 'page-templates/brands.php' ) || is_tax( 'product_brand' ) ? 'aria-current="page"' : ''; ?>><?php ni_the_icon( 'tag', 22 ); ?><span><?php esc_html_e( 'Varumärken', 'nordicinterior' ); ?></span></a></li>
			<li class="ni-sale"><a href="<?php echo esc_url( ni_page_url( 'page-templates/sale.php' ) ); ?>" <?php echo is_page_template( 'page-templates/sale.php' ) ? 'aria-current="page"' : ''; ?>><?php ni_the_icon( 'percent', 22 ); ?><span><?php esc_html_e( 'Rea', 'nordicinterior' ); ?></span></a></li>
		</ul>
	</nav>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
