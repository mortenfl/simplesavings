<?php
/**
 * Front page built from sections (Theme Adjustments → Startsida).
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

get_header();

foreach ( ni_home_sections() as $ni_section ) {
	ni_render_section( $ni_section );
}

// Content of the static front page (if any) after the sections.
while ( have_posts() ) :
	the_post();
	if ( '' !== trim( (string) get_the_content() ) ) :
		?>
		<section class="ni-section">
			<div class="ni-container ni-prose"><?php the_content(); ?></div>
		</section>
		<?php
	endif;
endwhile;

get_footer();
