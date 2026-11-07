<?php
/**
 * Single campaign (only reachable while active).
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

get_header();
while ( have_posts() ) :
	the_post();
	ni_listing_header(
		array(
			'title'   => get_the_title(),
			'eyebrow' => __( 'Kampanj', 'nordicinterior' ),
		)
	);
	?>
	<div class="ni-container ni-page-body ni-campaign-single">
		<?php ni_campaign_card( get_post(), 'main', 'campaign' ); ?>
		<div class="ni-prose"><?php the_content(); ?></div>
		<?php ni_disclosure( 'listing' ); ?>
	</div>
	<?php
endwhile;
get_footer();
