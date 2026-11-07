<?php
/**
 * Widget: Länklista.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Länklista: rum, stilar, kategorier, varumärken eller butiker.
 */
class NI_Widget_Links extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'ni_links',
			__( 'NI: Länklista', 'nordicinterior' ),
			array(
				'description'                 => __( 'Lista med rum, stilar, kategorier, varumärken eller butiker.', 'nordicinterior' ),
				'customize_selective_refresh' => true,
			)
		);
	}

	/**
	 * Sources.
	 *
	 * @return array<string,string>
	 */
	public static function sources() {
		return array(
			'ni_room'       => __( 'Rum', 'nordicinterior' ),
			'ni_style'      => __( 'Stilar', 'nordicinterior' ),
			'product_cat'   => __( 'Kategorier', 'nordicinterior' ),
			'product_brand' => __( 'Varumärken', 'nordicinterior' ),
			'ni_store'      => __( 'Butiker', 'nordicinterior' ),
		);
	}

	/**
	 * Output.
	 *
	 * @param array $args     Args.
	 * @param array $instance Instance.
	 */
	public function widget( $args, $instance ) {
		$source = $instance['source'] ?? 'ni_room';
		$count  = absint( $instance['count'] ?? 8 );
		$title  = ! empty( $instance['title'] ) ? $instance['title'] : ( self::sources()[ $source ] ?? '' );
		$terms  = ni_get_terms(
			$source,
			'product_cat' === $source ? array(
				'parent'     => 0,
				'hide_empty' => true,
			) : array( 'parent' => 0 )
		);
		if ( ! $terms ) {
			return;
		}
		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme markup.
		echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme markup.
		echo '<ul class="ni-linklist" role="list">';
		foreach ( array_slice( $terms, 0, $count ) as $term ) {
			if ( 'Uncategorized' === $term->name || (int) get_option( 'default_product_cat' ) === $term->term_id ) {
				continue;
			}
			echo '<li><a href="' . esc_url( (string) get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a></li>';
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
		$title  = $instance['title'] ?? '';
		$source = $instance['source'] ?? 'ni_room';
		$count  = absint( $instance['count'] ?? 8 );
		?>
		<p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Rubrik', 'nordicinterior' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" value="<?php echo esc_attr( $title ); ?>"></p>
		<p><label for="<?php echo esc_attr( $this->get_field_id( 'source' ) ); ?>"><?php esc_html_e( 'Innehåll', 'nordicinterior' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'source' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'source' ) ); ?>">
				<?php foreach ( self::sources() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $source, $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select></p>
		<p><label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"><?php esc_html_e( 'Antal', 'nordicinterior' ); ?></label>
			<input type="number" min="1" max="30" class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" value="<?php echo esc_attr( (string) $count ); ?>"></p>
		<?php
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
		$source = sanitize_key( $new_instance['source'] ?? 'ni_room' );
		return array(
			'title'  => sanitize_text_field( $new_instance['title'] ?? '' ),
			'source' => array_key_exists( $source, self::sources() ) ? $source : 'ni_room',
			'count'  => max( 1, min( 30, absint( $new_instance['count'] ?? 8 ) ) ),
		);
	}
}
