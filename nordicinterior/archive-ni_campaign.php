<?php
/**
 * Campaign archive: only active campaigns.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

get_header();
ni_listing_header(
	array(
		'title' => __( 'Kampanjer', 'nordicinterior' ),
		'intro' => __( 'Aktuella kampanjer och rabattkoder från våra butiker. Listan uppdateras automatiskt – avslutade kampanjer försvinner.', 'nordicinterior' ),
	)
);
?>
<div class="ni-container ni-page-body">
	<?php if ( have_posts() ) : ?>
		<div class="ni-campaign-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				ni_campaign_card( get_post(), has_post_thumbnail() ? 'main' : 'compact', 'campaign' );
			endwhile;
			?>
		</div>
		<?php ni_pagination( null, 'numbers' ); ?>
	<?php else : ?>
		<div class="ni-empty">
			<h2><?php esc_html_e( 'Inga aktiva kampanjer just nu', 'nordicinterior' ); ?></h2>
			<p><?php esc_html_e( 'Titta in igen snart – eller se allt som är på rea.', 'nordicinterior' ); ?></p>
			<a class="ni-btn" href="<?php echo esc_url( ni_page_url( 'page-templates/sale.php' ) ); ?>"><?php esc_html_e( 'Till rean', 'nordicinterior' ); ?></a>
		</div>
	<?php endif; ?>
	<?php ni_disclosure( 'listing' ); ?>
</div>
<?php
get_footer();
