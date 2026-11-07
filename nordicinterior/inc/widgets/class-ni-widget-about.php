<?php
/**
 * Widget: Om oss.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Om oss: logotyp, text och sociala länkar.
 */
class NI_Widget_About extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'ni_about',
			__( 'NI: Om oss', 'nordicinterior' ),
			array(
				'description'                 => __( 'Logotyp, text och sociala länkar (Instagram och Pinterest först).', 'nordicinterior' ),
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
		$text = isset( $instance['text'] ) && '' !== $instance['text'] ? $instance['text'] : __( 'Nordic Interior samlar möbler och inredning från Nordens bästa butiker. Vi jämför, sorterar efter rum och stil och skickar dig vidare när du hittat rätt.', 'nordicinterior' );
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme markup.
		echo '<div class="ni-about">';
		ni_the_logo( 'white', 'footer' );
		echo '<p>' . esc_html( $text ) . '</p>';
		ni_social_links();
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
		$text = $instance['text'] ?? '';
		printf(
			'<p><label for="%1$s">%2$s</label><textarea class="widefat" rows="5" id="%1$s" name="%3$s">%4$s</textarea></p><p class="description">%5$s</p>',
			esc_attr( $this->get_field_id( 'text' ) ),
			esc_html__( 'Text', 'nordicinterior' ),
			esc_attr( $this->get_field_name( 'text' ) ),
			esc_textarea( $text ),
			esc_html__( 'Sociala länkar ställs in under Theme Adjustments → Sidfot.', 'nordicinterior' )
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
		return array( 'text' => sanitize_textarea_field( $new_instance['text'] ?? '' ) );
	}
}
