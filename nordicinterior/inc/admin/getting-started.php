<?php
/**
 * "Kom igång": checklist, build menu, demo import, feed instructions.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Checklist items.
 *
 * @return array<int, array{done:bool, label:string, url:string}>
 */
function ni_checklist() {
	$count     = static function ( $tax ) {
		return taxonomy_exists( $tax ) ? (int) wp_count_terms(
			array(
				'taxonomy'   => $tax,
				'hide_empty' => false,
			)
		) : 0;
	};
	$locations = get_nav_menu_locations();
	$products  = wp_count_posts( 'product' );
	return array(
		array(
			'done'  => ni_wc(),
			'label' => __( 'WooCommerce är aktiverat', 'nordicinterior' ),
			'url'   => admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ),
		),
		array(
			'done'  => 'cm' === get_option( 'woocommerce_dimension_unit' ),
			'label' => __( 'Måttenhet är centimeter (WooCommerce → Inställningar → Produkter)', 'nordicinterior' ),
			'url'   => admin_url( 'admin.php?page=wc-settings&tab=products' ),
		),
		array(
			'done'  => $count( 'ni_room' ) > 0,
			'label' => __( 'Rum är skapade', 'nordicinterior' ),
			'url'   => admin_url( 'edit-tags.php?taxonomy=ni_room&post_type=product' ),
		),
		array(
			'done'  => $count( 'ni_style' ) > 0,
			'label' => __( 'Stilar är skapade', 'nordicinterior' ),
			'url'   => admin_url( 'edit-tags.php?taxonomy=ni_style&post_type=product' ),
		),
		array(
			'done'  => $count( 'ni_store' ) > 0,
			'label' => __( 'Butiker med frakt, retur och leveranstid', 'nordicinterior' ),
			'url'   => admin_url( 'edit-tags.php?taxonomy=ni_store&post_type=product' ),
		),
		array(
			'done'  => $count( 'product_brand' ) > 0,
			'label' => __( 'Varumärken med logotyp', 'nordicinterior' ),
			'url'   => admin_url( 'edit-tags.php?taxonomy=product_brand&post_type=product' ),
		),
		array(
			'done'  => taxonomy_exists( 'pa_material' ) && taxonomy_exists( 'pa_farg' ),
			'label' => __( 'Attributen material (pa_material) och färg (pa_farg)', 'nordicinterior' ),
			'url'   => admin_url( 'edit.php?post_type=product&page=product_attributes' ),
		),
		array(
			'done'  => $products && $products->publish > 0,
			'label' => __( 'Produkter är importerade', 'nordicinterior' ),
			'url'   => admin_url( 'edit.php?post_type=product' ),
		),
		array(
			'done'  => ! empty( $locations['primary'] ),
			'label' => __( 'Huvudmeny är vald', 'nordicinterior' ),
			'url'   => admin_url( 'nav-menus.php' ),
		),
		array(
			'done'  => 'page' === get_option( 'show_on_front' ),
			'label' => __( 'En statisk startsida är vald', 'nordicinterior' ),
			'url'   => admin_url( 'options-reading.php' ),
		),
		array(
			'done'  => (bool) ni_page_by_template( 'page-templates/brands.php' ) && (bool) ni_page_by_template( 'page-templates/sale.php' ),
			'label' => __( 'Sidor för varumärken, butiker, rea, rum och stilar', 'nordicinterior' ),
			'url'   => admin_url( 'edit.php?post_type=page' ),
		),
		array(
			'done'  => '' !== trim( (string) ni_mod( 'disclosure_text' ) ),
			'label' => __( 'Affiliate-information är ifylld', 'nordicinterior' ),
			'url'   => admin_url( 'customize.php?autofocus[section]=ni_affiliate' ),
		),
	);
}

/**
 * Page.
 */
function ni_admin_start_page() {
	?>
	<div class="wrap ni-admin">
		<h1><?php esc_html_e( 'Kom igång med Nordic Interior', 'nordicinterior' ); ?></h1>
		<div class="ni-admin-grid">
			<div class="ni-admin-card">
				<h2><?php esc_html_e( 'Checklista', 'nordicinterior' ); ?></h2>
				<ul class="ni-checklist">
					<?php foreach ( ni_checklist() as $item ) : ?>
						<li class="<?php echo $item['done'] ? 'is-done' : ''; ?>">
							<span class="dashicons <?php echo $item['done'] ? 'dashicons-yes-alt' : 'dashicons-marker'; ?>" aria-hidden="true"></span>
							<a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
							<span class="screen-reader-text"><?php echo $item['done'] ? esc_html__( '(klart)', 'nordicinterior' ) : esc_html__( '(ej klart)', 'nordicinterior' ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div class="ni-admin-card">
				<h2><?php esc_html_e( 'Huvudmeny', 'nordicinterior' ); ?></h2>
				<p><?php esc_html_e( 'Skapar ”Huvudmeny” med MÖBLER som mega-meny, BELYSNING, TEXTILIER & MATTOR, RUM och STIL som dropdowns, VARUMÄRKEN, BUTIKER, INSPIRATION och REA i lerfärg. Menyn väljs som huvud- och mobilmeny. En befintlig meny med samma namn byggs om.', 'nordicinterior' ); ?></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'ni_build_menu' ); ?>
					<input type="hidden" name="action" value="ni_build_menu">
					<?php submit_button( __( 'Skapa huvudmeny', 'nordicinterior' ), 'primary', 'submit', false ); ?>
				</form>
				<p class="description"><code>wp nordicinterior menu</code></p>
			</div>

			<div class="ni-admin-card">
				<h2><?php esc_html_e( 'Demoinnehåll', 'nordicinterior' ); ?></h2>
				<p><?php esc_html_e( '9 rum, 8 stilar, 16 varumärken, 6 butiker, kategorier i tre nivåer, 60 produkter med material, färg och mått, 3 kampanjer och 6 artiklar (2 med Shoppa looken), sidorna och menyn. Bilderna är enkla platshållare.', 'nordicinterior' ); ?></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'ni_import_demo' ); ?>
					<input type="hidden" name="action" value="ni_import_demo">
					<?php submit_button( __( 'Importera demoinnehåll', 'nordicinterior' ), 'secondary', 'submit', false ); ?>
				</form>
				<p class="description"><code>wp nordicinterior demo</code></p>
			</div>

			<div class="ni-admin-card">
				<h2><?php esc_html_e( 'Sortering och statistik', 'nordicinterior' ); ?></h2>
				<p><?php esc_html_e( 'Klick och rabatter räknas om varje natt. Kör omräkningen manuellt efter en stor import.', 'nordicinterior' ); ?></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'ni_recalc' ); ?>
					<input type="hidden" name="action" value="ni_recalc">
					<?php submit_button( __( 'Räkna om nu', 'nordicinterior' ), 'secondary', 'submit', false ); ?>
				</form>
				<p class="description"><code>wp nordicinterior recalc</code> · <code>wp nordicinterior check-links</code></p>
			</div>

			<div class="ni-admin-card ni-admin-card--wide">
				<h2><?php esc_html_e( 'Produktflöden (WP All Import)', 'nordicinterior' ); ?></h2>
				<p><?php esc_html_e( 'Importera flöden från Adtraction, Awin, Partner-ads eller Tradedoubler med WP All Import Pro + WooCommerce-tillägget. Välj produkttypen ”Extern/Affiliate-produkt” och mappa fälten så här:', 'nordicinterior' ); ?></p>
				<table class="widefat striped">
					<thead><tr><th><?php esc_html_e( 'WooCommerce / tema', 'nordicinterior' ); ?></th><th>Adtraction</th><th>Awin</th><th>Partner-ads</th><th>Tradedoubler</th></tr></thead>
					<tbody>
						<tr><td><?php esc_html_e( 'Produkt-URL (_product_url)', 'nordicinterior' ); ?></td><td>{TrackingUrl}</td><td>{aw_deep_link}</td><td>{vareurl}</td><td>{productUrl}</td></tr>
						<tr><td><?php esc_html_e( 'Ordinarie pris', 'nordicinterior' ); ?></td><td>{OriginalPrice}</td><td>{rrp_price}</td><td>{glpris}</td><td>{previousPrice}</td></tr>
						<tr><td><?php esc_html_e( 'Reapris / pris', 'nordicinterior' ); ?></td><td>{Price}</td><td>{search_price}</td><td>{nypris}</td><td>{price}</td></tr>
						<tr><td><?php esc_html_e( 'SKU', 'nordicinterior' ); ?></td><td>{Sku}</td><td>{merchant_product_id}</td><td>{varenummer}</td><td>{sku}</td></tr>
						<tr><td>product_brand</td><td>{Brand}</td><td>{brand_name}</td><td>{brand}</td><td>{brand}</td></tr>
						<tr><td>ni_store</td><td colspan="4"><?php esc_html_e( 'Fast värde per flöde, t.ex. ”Royal Design”.', 'nordicinterior' ); ?></td></tr>
						<tr><td>product_cat / ni_room / ni_style</td><td colspan="4"><?php esc_html_e( 'Mappa butikens kategori med ”Mapping rules”, t.ex. ”Sofas > 3-seater” → Möbler > Soffor > 3-sitssoffor; rum och stil från nyckelord i namn eller kategori.', 'nordicinterior' ); ?></td></tr>
						<tr><td>pa_material / pa_farg / pa_designer</td><td>{Material} / {Color} / {Designer}</td><td>{material} / {colour} / –</td><td>{materiale} / {farve} / –</td><td>{fields_material} / {fields_color} / –</td></tr>
						<tr><td><?php esc_html_e( 'Mått: _width, _length (djup), _height', 'nordicinterior' ); ?></td><td colspan="4"><?php esc_html_e( 'Centimeter. Tolka t.ex. ”B 210 x D 90 x H 80 cm” med en PHP-funktion i importen (se README).', 'nordicinterior' ); ?></td></tr>
					</tbody>
				</table>
				<p><?php esc_html_e( 'Efter varje import sparas prishistorik automatiskt (Prissänkt-badge) och cachen töms. Kör ”Räkna om nu” efter första importen.', 'nordicinterior' ); ?></p>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Manual recalc.
 */
function ni_handle_recalc() {
	if ( ! current_user_can( 'edit_products' ) ) {
		wp_die( esc_html__( 'Du har inte behörighet att göra detta.', 'nordicinterior' ) );
	}
	check_admin_referer( 'ni_recalc' );
	ni_recalc();
	wp_safe_redirect( add_query_arg( 'ni-notice', 'recalc-ok', admin_url( 'admin.php?page=nordicinterior-start' ) ) );
	exit;
}
add_action( 'admin_post_ni_recalc', 'ni_handle_recalc' );

/**
 * Show a one-time pointer to "Kom igång" after activation.
 */
function ni_activation_notice() {
	if ( get_option( 'ni_welcome_dismissed' ) || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( $screen && false !== strpos( (string) $screen->id, 'nordicinterior' ) ) {
		update_option( 'ni_welcome_dismissed', 1, false );
		return;
	}
	printf(
		'<div class="notice notice-info"><p>%1$s <a href="%2$s">%3$s</a></p></div>',
		esc_html__( 'Tack för att du valde Nordic Interior!', 'nordicinterior' ),
		esc_url( admin_url( 'admin.php?page=nordicinterior-start' ) ),
		esc_html__( 'Kom igång', 'nordicinterior' )
	);
}
add_action( 'admin_notices', 'ni_activation_notice' );
