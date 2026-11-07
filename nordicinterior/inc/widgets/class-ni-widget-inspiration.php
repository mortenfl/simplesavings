<?php
/**
 * Widget: Mer inspiration.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Mer inspiration.
 */
class NI_Widget_Inspiration extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'ni_inspiration',
			__( 'NI: Mer inspiration', 'nordicinterior' ),
			array( 'description' => __( 'Artiklar från samma rum eller stil.', 'nordicinterior' ) )
		);
	}

	/**
	 * Output.
	 *
	 * @param array $args     Args.
	 * @param array $instance Instance.
	 */
	public function widget( $args, $instance ) {
		$query = array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 3,
			'no_found_rows'  => true,
			'post__not_in'   => is_singular( 'post' ) ? array( get_queried_object_id() ) : array(),
		);
		if ( is_singular( 'post' ) ) {
			$label = ni_post_label( get_queried_object_id() );
			if ( $label ) {
				$query['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Small query.
					array(
						'taxonomy' => $label->taxonomy,
						'terms'    => array( $label->term_id ),
					),
				);
			}
		}
		$posts = get_posts( $query );
		if ( ! $posts && isset( $query['tax_query'] ) ) {
			unset( $query['tax_query'] );
			$posts = get_posts( $query );
		}
		if ( ! $posts ) {
			return;
		}
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme markup.
		echo $args['before_title'] . esc_html( ! empty( $instance['title'] ) ? $instance['title'] : __( 'Mer inspiration', 'nordicinterior' ) ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme markup.
		echo '<div class="ni-widget-posts">';
		foreach ( $posts as $post ) {
			ni_post_card( $post, 'row' );
		}
		echo '</div>';
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme markup.
	}

	/**
	 * Form.
	 *
	 * @param array $instance Instance.
	 * @return string
	 */
	public function form( $instance ) {
		printf(
			'<p><label for="%1$s">%2$s</label><input class="widefat" id="%1$s" name="%3$s" value="%4$s"></p>',
			esc_attr( $this->get_field_id( 'title' ) ),
			esc_html__( 'Rubrik', 'nordicinterior' ),
			esc_attr( $this->get_field_name( 'title' ) ),
			esc_attr( $instance['title'] ?? '' )
		);
		return '';
	}

	/**
	 * Update.
	 *
	 * @param array $new_instance New.
	 * @param array $old_instance Old.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		return array( 'title' => sanitize_text_field( $new_instance['title'] ?? '' ) );
	}
}
