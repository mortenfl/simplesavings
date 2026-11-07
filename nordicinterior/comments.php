<?php
/**
 * Comments.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}
?>
<section id="comments" class="ni-comments">
	<?php if ( have_comments() ) : ?>
		<h2 class="ni-comments__title">
			<?php
			/* translators: %s: number of comments. */
			echo esc_html( sprintf( _n( '%s kommentar', '%s kommentarer', get_comments_number(), 'nordicinterior' ), number_format_i18n( get_comments_number() ) ) );
			?>
		</h2>
		<ol class="ni-comments__list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 40,
				)
			);
			?>
		</ol>
		<?php the_comments_navigation(); ?>
	<?php endif; ?>
	<?php comment_form(); ?>
</section>
