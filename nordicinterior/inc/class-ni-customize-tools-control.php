<?php
/**
 * Customizer control: export, import and reset buttons.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Export, import and reset buttons.
 */
class NI_Customize_Tools_Control extends WP_Customize_Control {

	/**
	 * Control type.
	 *
	 * @var string
	 */
	public $type = 'ni-tools';

	/**
	 * Render.
	 */
	public function render_content() {
		?>
		<div class="ni-tools" data-ni-tools>
			<p><?php esc_html_e( 'Spara alla Theme Adjustments som en JSON-fil, läs in en tidigare export eller återställ standardvärdena.', 'nordicinterior' ); ?></p>
			<p><button type="button" class="button" data-ni-export><?php esc_html_e( 'Exportera inställningar', 'nordicinterior' ); ?></button></p>
			<p>
				<label for="ni-import-file" class="customize-control-title"><?php esc_html_e( 'Importera', 'nordicinterior' ); ?></label>
				<input type="file" id="ni-import-file" accept="application/json,.json" data-ni-import>
			</p>
			<p><button type="button" class="button button-link-delete" data-ni-reset><?php esc_html_e( 'Återställ till standard', 'nordicinterior' ); ?></button></p>
			<p class="ni-tools__status" aria-live="polite"></p>
		</div>
		<?php
	}
}
