<?php
/**
 * Widget: Populära rum och stilar.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Populära rum och stilar.
 */
class NI_Widget_Terms extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'ni_terms',
			__( 'NI: Populära rum och stilar', 'nordicinterior' ),
			array( 'description' => __( 'Chips med populära rum och stilar.', 'nordicinterior' ) )
		);
	}

	/**
	 * Output.
	 *
	 * @param array $args     Args.
	 * @param array $instance Instance.
	 */
	public function widget( $args, $instance ) {
		$rooms  = ni_home_rooms( 6 );
		$styles = array_slice( ni_get_terms( 'ni_style', array( 'parent' => 0 ) ), 0, 6 );
		if ( ! $rooms && ! $styles ) {
			return;
		}
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme markup.
		echo $args['before_title'] . esc_html( ! empty( $instance['title'] ) ? $instance['title'] : __( 'Populära rum och stilar', 'nordicinterior' ) ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme markup.
		echo '<ul class="ni-chips__list" role="list">';
		foreach ( array_merge( $rooms, $styles ) as $term ) {
			echo '<li><a class="ni-chip" href="' . esc_url( (string) get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a></li>';
		}
		echo '</ul>';
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
