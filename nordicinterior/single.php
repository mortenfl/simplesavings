<?php
/**
 * Inspiration article: label (room or style), title, lead, large image and
 * content with optional Shoppa looken blocks. No date, reading time or
 * author. Product row and "Läs mer" below. Blog sidebar to the right on
 * desktop, below the content on mobile.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$ni_label   = ni_post_label( get_the_ID() );
	$ni_lead    = ni_post_lead( get_the_ID() );
	$ni_sidebar = 'none' !== ni_mod( 'post_sidebar' );
	?>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'ni-article' ); ?>>
		<header class="ni-article__header">
			<div class="ni-container ni-container--narrow">
				<?php ni_breadcrumbs(); ?>
				<?php if ( $ni_label ) : ?>
					<p class="ni-eyebrow"><a href="<?php echo esc_url( (string) get_term_link( $ni_label ) ); ?>"><?php echo esc_html( $ni_label->name ); ?></a></p>
				<?php endif; ?>
				<h1 class="ni-article__title"><?php the_title(); ?></h1>
				<?php if ( $ni_lead ) : ?>
					<p class="ni-article__lead"><?php echo esc_html( $ni_lead ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="ni-article__image ni-container ni-pinnable">
					<?php
					the_post_thumbnail(
						'ni-hero',
						array(
							'loading'       => 'eager',
							'fetchpriority' => 'high',
							'sizes'         => '(min-width: 1320px) 1280px, 100vw',
						)
					);
					echo ni_pin_button( (string) get_the_post_thumbnail_url( null, 'full' ), get_the_title() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped inside.
					?>
				</figure>
			<?php endif; ?>
		</header>

		<div class="ni-container ni-article__layout<?php echo $ni_sidebar ? ' has-sidebar' : ''; ?>">
			<div class="ni-article__content ni-prose">
				<?php
				the_content();
				wp_link_pages();
				ni_disclosure( 'post' );
				?>
			</div>
			<?php if ( $ni_sidebar ) : ?>
				<aside class="ni-sidebar" aria-label="<?php esc_attr_e( 'Sidofält', 'nordicinterior' ); ?>">
					<?php ni_sidebar( 'sidebar-blog' ); ?>
				</aside>
			<?php endif; ?>
		</div>

		<?php
		$ni_related = ni_id_list( (string) get_post_meta( get_the_ID(), '_ni_related_products', true ) );
		if ( $ni_related && ni_wc() ) :
			?>
			<section class="ni-section ni-section--rail" aria-labelledby="ni-article-products">
				<div class="ni-container">
					<?php ni_section_header( __( 'Produkter i artikeln', 'nordicinterior' ), '', '', '', 'ni-article-products' ); ?>
					<?php ni_product_rail( $ni_related, 'card', __( 'Produkter i artikeln', 'nordicinterior' ) ); ?>
				</div>
			</section>
		<?php endif; ?>

		<?php
		$ni_more = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 3,
				'post__not_in'   => array( get_the_ID() ),
				'no_found_rows'  => true,
			)
		);
		if ( $ni_more ) :
			?>
			<section class="ni-section" aria-labelledby="ni-read-more">
				<div class="ni-container">
					<?php ni_section_header( __( 'Läs mer', 'nordicinterior' ), get_option( 'page_for_posts' ) ? (string) get_permalink( (int) get_option( 'page_for_posts' ) ) : '', __( 'All inspiration', 'nordicinterior' ), '', 'ni-read-more' ); ?>
					<div class="ni-post-grid">
						<?php foreach ( $ni_more as $ni_post ) : ?>
							<?php ni_post_card( $ni_post, 'small' ); ?>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>
	</article>
	<?php
	if ( comments_open() || get_comments_number() ) {
		echo '<div class="ni-container ni-container--narrow">';
		comments_template();
		echo '</div>';
	}
endwhile;

get_footer();
