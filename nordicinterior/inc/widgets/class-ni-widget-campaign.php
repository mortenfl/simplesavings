<?php
/**
 * Widget: Aktuell kampanj.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Aktuell kampanj.
 */
class NI_Widget_Campaign extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'ni_campaign',
			__( 'NI: Aktuell kampanj', 'nordicinterior' ),
			array( 'description' => __( 'Visar huvudkampanjen eller den senaste aktiva kampanjen.', 'nordicinterior' ) )
		);
	}

	/**
	 * Output.
	 *
	 * @param array $args     Args.
	 * @param array $instance Instance.
	 */
	public function widget( $args, $instance ) {
		$campaigns = ni_get_active_campaigns( array( 'posts_per_page' => 1 ) );
		if ( ! $campaigns ) {
			return;
		}
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme markup.
		echo $args['before_title'] . esc_html( ! empty( $instance['title'] ) ? $instance['title'] : __( 'Kampanj just nu', 'nordicinterior' ) ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme markup.
		ni_campaign_card( $campaigns[0], 'compact', 'sidebar' );
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
