<?php
/**
 * Widget: Nyhetsbrev.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Nyhetsbrev: text och formulär-shortcode.
 */
class NI_Widget_Newsletter extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'ni_newsletter',
			__( 'NI: Nyhetsbrev', 'nordicinterior' ),
			array(
				'description'                 => __( 'Text och formulär från valfritt nyhetsbrevsplugin (shortcode).', 'nordicinterior' ),
				'customize_selective_refresh' => true,
			)
		);
	}

	/**
	 * Output.
	 *
	 * @param array $args     Args.
	 * @param array $instance Instance.
	 */
	public function widget( $args, $instance ) {
		$title     = ! empty( $instance['title'] ) ? $instance['title'] : (string) ni_mod( 'newsletter_title' );
		$text      = ! empty( $instance['text'] ) ? $instance['text'] : (string) ni_mod( 'newsletter_text' );
		$shortcode = ! empty( $instance['shortcode'] ) ? $instance['shortcode'] : (string) ni_mod( 'newsletter_shortcode' );
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme markup.
		echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme markup.
		echo '<p>' . esc_html( $text ) . '</p>';
		if ( trim( $shortcode ) ) {
			echo do_shortcode( $shortcode );
		} else {
			ni_newsletter_placeholder_form();
		}
		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme markup.
	}

	/**
	 * Form.
	 *
	 * @param array $instance Instance.
	 * @return string
	 */
	public function form( $instance ) {
		foreach ( array(
			'title'     => __( 'Rubrik', 'nordicinterior' ),
			'text'      => __( 'Text', 'nordicinterior' ),
			'shortcode' => __( 'Formulär-shortcode, t.ex. [mc4wp_form id="1"]', 'nordicinterior' ),
		) as $key => $label ) {
			printf(
				'<p><label for="%1$s">%2$s</label><input class="widefat" id="%1$s" name="%3$s" value="%4$s"></p>',
				esc_attr( $this->get_field_id( $key ) ),
				esc_html( $label ),
				esc_attr( $this->get_field_name( $key ) ),
				esc_attr( $instance[ $key ] ?? '' )
			);
		}
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
		return array(
			'title'     => sanitize_text_field( $new_instance['title'] ?? '' ),
			'text'      => sanitize_text_field( $new_instance['text'] ?? '' ),
			'shortcode' => sanitize_text_field( $new_instance['shortcode'] ?? '' ),
		);
	}
}
