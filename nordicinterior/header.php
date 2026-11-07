<?php
/**
 * Header: topbar, sticky header with logo, menu and the "Alla rum" panel,
 * and the mobile drawer. There is intentionally no search field here.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="ni-skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Hoppa till innehållet', 'nordicinterior' ); ?></a>

<?php if ( ni_mod( 'topbar' ) ) : ?>
	<div class="ni-topbar">
		<div class="ni-container ni-topbar__inner">
			<p class="ni-topbar__usp ni-topbar__usp--1"><?php echo esc_html( (string) ni_mod( 'topbar_text' ) ); ?></p>
			<?php if ( ni_mod( 'topbar_text_2' ) ) : ?>
				<p class="ni-topbar__usp ni-topbar__usp--2"><?php echo esc_html( (string) ni_mod( 'topbar_text_2' ) ); ?></p>
			<?php endif; ?>
			<?php if ( post_type_exists( 'ni_campaign' ) ) : ?>
				<a class="ni-topbar__link" href="<?php echo esc_url( (string) get_post_type_archive_link( 'ni_campaign' ) ); ?>"><?php esc_html_e( 'Kampanjer just nu', 'nordicinterior' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>

<header class="ni-header" data-ni-header>
	<div class="ni-container ni-header__inner">
		<button type="button" class="ni-header__burger ni-iconbtn" aria-expanded="false" aria-controls="ni-drawer" data-ni-drawer-open>
			<?php ni_the_icon( 'menu', 24 ); ?>
			<span class="screen-reader-text"><?php esc_html_e( 'Öppna menyn', 'nordicinterior' ); ?></span>
		</button>

		<div class="ni-header__brand">
			<?php ni_the_logo( 'dark', 'header' ); ?>
		</div>

		<nav class="ni-nav" aria-label="<?php esc_attr_e( 'Huvudmeny', 'nordicinterior' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'ni-menu',
					'depth'          => 3,
					'walker'         => new NI_Walker_Nav(),
					'fallback_cb'    => 'ni_menu_fallback',
				)
			);
			?>
		</nav>

		<?php if ( ni_mod( 'rooms_button' ) && taxonomy_exists( 'ni_room' ) ) : ?>
			<button type="button" class="ni-header__rooms" aria-expanded="false" aria-controls="ni-rooms-panel" data-ni-rooms-toggle>
				<?php ni_the_icon( 'home', 18 ); ?>
				<span><?php echo esc_html( (string) ni_mod( 'rooms_button_text' ) ); ?></span>
				<?php ni_the_icon( 'chevron-down', 16 ); ?>
			</button>
		<?php endif; ?>
	</div>
	<?php ni_rooms_panel(); ?>
</header>

<div class="ni-drawer" id="ni-drawer" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Meny', 'nordicinterior' ); ?>" hidden data-ni-drawer>
	<div class="ni-drawer__panel">
		<div class="ni-drawer__head">
			<?php ni_the_logo( 'dark', 'drawer' ); ?>
			<button type="button" class="ni-iconbtn" data-ni-drawer-close>
				<?php ni_the_icon( 'close', 24 ); ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Stäng menyn', 'nordicinterior' ); ?></span>
			</button>
		</div>

		<?php
		$ni_top_cats = taxonomy_exists( 'product_cat' ) ? get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => 0,
				'hide_empty' => true,
				'number'     => 8,
				'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
			)
		) : array();
		if ( $ni_top_cats && ! is_wp_error( $ni_top_cats ) ) :
			?>
			<ul class="ni-drawer__cats" role="list">
				<?php foreach ( $ni_top_cats as $ni_cat ) : ?>
					<li><a class="ni-drawer__cat" href="<?php echo esc_url( (string) get_term_link( $ni_cat ) ); ?>"><?php echo esc_html( $ni_cat->name ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<nav class="ni-drawer__nav" aria-label="<?php esc_attr_e( 'Mobilmeny', 'nordicinterior' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => has_nav_menu( 'mobile' ) ? 'mobile' : 'primary',
					'container'      => false,
					'menu_class'     => 'ni-mobile-menu',
					'depth'          => 3,
					'walker'         => new NI_Walker_Nav(),
					'ni_mobile'      => true,
					'fallback_cb'    => 'ni_menu_fallback',
				)
			);
			?>
		</nav>

		<?php $ni_rooms = ni_get_terms( 'ni_room', array( 'parent' => 0 ) ); ?>
		<?php if ( $ni_rooms ) : ?>
			<div class="ni-drawer__rooms">
				<p class="ni-eyebrow"><?php esc_html_e( 'Rum', 'nordicinterior' ); ?></p>
				<ul class="ni-chips__list" role="list">
					<?php foreach ( $ni_rooms as $ni_room ) : ?>
						<li><a class="ni-chip" href="<?php echo esc_url( (string) get_term_link( $ni_room ) ); ?>"><?php echo esc_html( $ni_room->name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
	</div>
	<div class="ni-drawer__backdrop" data-ni-drawer-close></div>
</div>

<main id="main" class="ni-main" tabindex="-1">
