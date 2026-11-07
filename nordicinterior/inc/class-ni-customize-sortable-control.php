<?php
/**
 * Customizer control: sortable list with visibility checkboxes.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sortable list with visibility checkboxes. Drag and drop with the mouse, or
 * use the up/down buttons with the keyboard.
 */
class NI_Customize_Sortable_Control extends WP_Customize_Control {

	/**
	 * Control type.
	 *
	 * @var string
	 */
	public $type = 'ni-sortable';

	/**
	 * Render.
	 */
	public function render_content() {
		$order   = array_filter( explode( ',', (string) $this->value( 'default' ) ) );
		$off     = array_filter( explode( ',', (string) $this->value( 'off' ) ) );
		$choices = $this->choices;
		// Sections missing from the saved order are appended (and hidden).
		foreach ( array_keys( $choices ) as $key ) {
			if ( ! in_array( $key, $order, true ) ) {
				$order[] = $key;
				$off[]   = $key;
			}
		}
		?>
		<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
		<span class="description customize-control-description"><?php esc_html_e( 'Dra för att ändra ordning. Bocka ur för att dölja.', 'nordicinterior' ); ?></span>
		<ul class="ni-sortable" data-ni-sortable>
			<?php foreach ( $order as $key ) : ?>
				<?php
				if ( ! isset( $choices[ $key ] ) ) {
					continue;
				}
				?>
				<li class="ni-sortable__item" draggable="true" data-key="<?php echo esc_attr( $key ); ?>">
					<span class="dashicons dashicons-menu" aria-hidden="true"></span>
					<label>
						<input type="checkbox" <?php checked( ! in_array( $key, $off, true ) ); ?>>
						<?php echo esc_html( $choices[ $key ] ); ?>
					</label>
					<span class="ni-sortable__buttons">
						<button type="button" class="button-link" data-dir="up" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: section name. */ __( 'Flytta upp %s', 'nordicinterior' ), $choices[ $key ] ) ); ?>">↑</button>
						<button type="button" class="button-link" data-dir="down" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: section name. */ __( 'Flytta ned %s', 'nordicinterior' ), $choices[ $key ] ) ); ?>">↓</button>
					</span>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
	}
}
