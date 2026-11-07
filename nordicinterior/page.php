<?php
/**
 * Default page.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	ni_listing_header(
		array(
			'title' => get_the_title(),
			'intro' => has_excerpt() ? get_the_excerpt() : '',
		)
	);
	?>
	<div class="ni-container ni-page-body">
		<div class="ni-prose ni-prose--page">
			<?php
			the_content();
			wp_link_pages();
			?>
		</div>
		<?php
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
		?>
	</div>
	<?php
endwhile;

get_footer();
