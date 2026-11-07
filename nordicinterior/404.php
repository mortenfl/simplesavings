<?php
/**
 * 404 with search, popular searches and rooms.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="ni-hero ni-hero--404">
	<div class="ni-container ni-hero__inner">
		<div class="ni-hero__content">
			<p class="ni-eyebrow"><?php esc_html_e( 'Sidan hittades inte', 'nordicinterior' ); ?></p>
			<h1 class="ni-hero__title"><?php esc_html_e( 'Här var det tomt', 'nordicinterior' ); ?></h1>
			<p class="ni-hero__text"><?php esc_html_e( 'Sidan kan ha flyttats eller tagits bort. Sök efter det du letar efter, eller börja med ett rum.', 'nordicinterior' ); ?></p>
			<?php ni_search_form( array( 'id' => 'ni-404-search' ) ); ?>
		</div>
	</div>
</section>
<?php
ni_render_section( 'rooms' );
get_footer();
