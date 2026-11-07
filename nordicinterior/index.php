<?php
/**
 * Fallback template and the Inspiration (posts) archive.
 * Posts never show date, reading time or author.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

get_header();

$ni_title = __( 'Inspiration', 'nordicinterior' );
$ni_intro = '';
if ( is_home() && get_option( 'page_for_posts' ) ) {
	$ni_title = get_the_title( (int) get_option( 'page_for_posts' ) );
	$ni_intro = (string) get_the_excerpt( (int) get_option( 'page_for_posts' ) );
} elseif ( is_archive() ) {
	$ni_title = wp_strip_all_tags( get_the_archive_title() );
	$ni_intro = (string) get_the_archive_description();
}

$ni_chips = array();
foreach ( get_categories( array( 'hide_empty' => true ) ) as $ni_cat ) {
	$ni_chips[] = array( $ni_cat->name, (string) get_category_link( $ni_cat ), is_category( $ni_cat->term_id ) );
}

ni_listing_header(
	array(
		'title' => $ni_title,
		'intro' => $ni_intro,
		'chips' => $ni_chips,
	)
);
?>
<div class="ni-container ni-page-body">
	<?php if ( have_posts() ) : ?>
		<div class="ni-post-grid ni-post-grid--archive">
			<?php
			$ni_first = ! is_paged();
			while ( have_posts() ) :
				the_post();
				ni_post_card( get_post(), $ni_first ? 'large' : 'small' );
				$ni_first = false;
			endwhile;
			?>
		</div>
		<?php ni_pagination( null, 'numbers' ); ?>
	<?php else : ?>
		<div class="ni-empty">
			<h2><?php esc_html_e( 'Inget här ännu', 'nordicinterior' ); ?></h2>
			<p><?php esc_html_e( 'Vi skriver nya guider och inredningstips hela tiden. Titta in snart igen.', 'nordicinterior' ); ?></p>
		</div>
	<?php endif; ?>
</div>
<?php
get_footer();
