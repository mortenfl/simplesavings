<?php
/**
 * Products list: columns for clicks, link status and price change, and the
 * affiliate URL in Quick Edit.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add columns.
 *
 * @param array $columns Columns.
 * @return array
 */
function ni_product_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'price' === $key ) {
			$new['ni_clicks']       = __( 'Klick (30 d)', 'nordicinterior' );
			$new['ni_link']         = __( 'Länk', 'nordicinterior' );
			$new['ni_price_change'] = __( 'Prisändring', 'nordicinterior' );
		}
	}
	if ( ! isset( $new['ni_clicks'] ) ) {
		$new['ni_clicks']       = __( 'Klick (30 d)', 'nordicinterior' );
		$new['ni_link']         = __( 'Länk', 'nordicinterior' );
		$new['ni_price_change'] = __( 'Prisändring', 'nordicinterior' );
	}
	return $new;
}
add_filter( 'manage_edit-product_columns', 'ni_product_columns', 20 );

/**
 * Column values.
 *
 * @param string $column  Column.
 * @param int    $post_id Post ID.
 */
function ni_product_column_values( $column, $post_id ) {
	switch ( $column ) {
		case 'ni_clicks':
			echo esc_html( number_format_i18n( (int) get_post_meta( $post_id, '_ni_clicks', true ) ) );
			break;
		case 'ni_link':
			$status = (string) get_post_meta( $post_id, '_ni_link_status', true );
			$labels = array(
				'ok'      => array( 'ok', __( 'OK', 'nordicinterior' ) ),
				'broken'  => array( 'broken', __( 'Trasig', 'nordicinterior' ) ),
				'unknown' => array( 'unknown', __( 'Okänd', 'nordicinterior' ) ),
			);
			$label  = $labels[ $status ] ?? $labels['unknown'];
			printf( '<span class="ni-pill ni-pill--%1$s">%2$s</span>', esc_attr( $label[0] ), esc_html( $label[1] ) );
			$product = wc_get_product( $post_id );
			$url     = $product ? ni_product_url( $product ) : '';
			// Hidden value for Quick Edit.
			printf( '<span class="hidden ni-qe-url" data-url="%s"></span>', esc_attr( $url ) );
			break;
		case 'ni_price_change':
			$prev    = (float) get_post_meta( $post_id, '_ni_prev_price', true );
			$product = wc_get_product( $post_id );
			$now     = $product ? (float) $product->get_price() : 0;
			if ( $prev > 0 && $now > 0 && abs( $prev - $now ) >= 0.01 ) {
				$pct   = round( ( $now - $prev ) / $prev * 100, 1 );
				$class = $pct < 0 ? 'down' : 'up';
				printf( '<span class="ni-change ni-change--%1$s">%2$s%3$s %%</span>', esc_attr( $class ), $pct > 0 ? '+' : '', esc_html( number_format_i18n( $pct, 1 ) ) );
			} else {
				echo '–';
			}
			break;
	}
}
add_action( 'manage_product_posts_custom_column', 'ni_product_column_values', 10, 2 );

/**
 * Sortable clicks column.
 *
 * @param array $columns Columns.
 * @return array
 */
function ni_product_sortable_columns( $columns ) {
	$columns['ni_clicks'] = 'ni_clicks';
	return $columns;
}
add_filter( 'manage_edit-product_sortable_columns', 'ni_product_sortable_columns' );

/**
 * Sort by clicks in admin.
 *
 * @param WP_Query $query Query.
 */
function ni_product_admin_orderby( $query ) {
	if ( is_admin() && $query->is_main_query() && 'ni_clicks' === $query->get( 'orderby' ) ) {
		$query->set( 'meta_key', '_ni_clicks' );
		$query->set( 'orderby', 'meta_value_num' );
	}
}
add_action( 'pre_get_posts', 'ni_product_admin_orderby' );

/**
 * Quick Edit field for the affiliate URL.
 *
 * @param string $column    Column.
 * @param string $post_type Post type.
 */
function ni_quick_edit_url( $column, $post_type ) {
	if ( 'product' !== $post_type || 'ni_link' !== $column ) {
		return;
	}
	wp_nonce_field( 'ni_quick_edit', 'ni_qe_nonce' );
	?>
	<fieldset class="inline-edit-col-left ni-qe">
		<div class="inline-edit-col">
			<label>
				<span class="title"><?php esc_html_e( 'Affiliate-URL', 'nordicinterior' ); ?></span>
				<span class="input-text-wrap"><input type="url" name="ni_product_url" value="" class="ni-qe-url-input"></span>
			</label>
		</div>
	</fieldset>
	<?php
}
add_action( 'quick_edit_custom_box', 'ni_quick_edit_url', 10, 2 );

/**
 * Save Quick Edit URL.
 *
 * @param int $post_id Post ID.
 */
function ni_quick_edit_save( $post_id ) {
	if ( ! isset( $_POST['ni_qe_nonce'], $_POST['ni_product_url'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ni_qe_nonce'] ) ), 'ni_quick_edit' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_product', $post_id ) ) {
		return;
	}
	$product = wc_get_product( $post_id );
	if ( $product instanceof WC_Product_External ) {
		$url = esc_url_raw( wp_unslash( $_POST['ni_product_url'] ) );
		if ( $url !== $product->get_product_url() ) {
			$product->set_product_url( $url );
			$product->save();
			delete_post_meta( $post_id, '_ni_link_status' );
		}
	}
}
add_action( 'save_post_product', 'ni_quick_edit_save', 20 );

/**
 * Filter products by link status in admin (?ni_link=broken).
 *
 * @param WP_Query $query Query.
 */
function ni_product_admin_link_filter( $query ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Admin list filter.
	if ( is_admin() && $query->is_main_query() && 'product' === $query->get( 'post_type' ) && isset( $_GET['ni_link'] ) ) {
		$query->set( 'meta_key', '_ni_link_status' );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Admin list filter.
		$query->set( 'meta_value', sanitize_key( wp_unslash( $_GET['ni_link'] ) ) );
	}
}
add_action( 'pre_get_posts', 'ni_product_admin_link_filter' );
