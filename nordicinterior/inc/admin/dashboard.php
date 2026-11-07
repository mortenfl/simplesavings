<?php
/**
 * Admin: Nordic Interior menu, statistics dashboard, broken links,
 * searches and landing page texts.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register admin pages.
 */
function ni_admin_menu() {
	add_menu_page(
		__( 'Nordic Interior', 'nordicinterior' ),
		__( 'Nordic Interior', 'nordicinterior' ),
		'edit_posts',
		'nordicinterior',
		'ni_admin_dashboard_page',
		'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#a7aaad" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 6.5h4.5l.5 6H8.6zM8.6 12.5 8.2 18M13 12.5l.8 1.6h3l.8 4.4M13.8 14.1l-.4 4.4"/></svg>' ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Admin menu icon.
		58
	);
	add_submenu_page( 'nordicinterior', __( 'Översikt', 'nordicinterior' ), __( 'Översikt', 'nordicinterior' ), 'edit_posts', 'nordicinterior', 'ni_admin_dashboard_page' );
	add_submenu_page( 'nordicinterior', __( 'Kom igång', 'nordicinterior' ), __( 'Kom igång', 'nordicinterior' ), 'edit_theme_options', 'nordicinterior-start', 'ni_admin_start_page' );
	add_submenu_page( 'nordicinterior', __( 'Landningssidor', 'nordicinterior' ), __( 'Landningssidor', 'nordicinterior' ), 'edit_posts', 'nordicinterior-landings', 'ni_admin_landings_page' );
	add_submenu_page( 'nordicinterior', __( 'Länkkontroll', 'nordicinterior' ), __( 'Länkkontroll', 'nordicinterior' ), 'edit_products', 'nordicinterior-links', 'ni_admin_links_page' );
	add_submenu_page( 'nordicinterior', __( 'Sökningar', 'nordicinterior' ), __( 'Sökningar', 'nordicinterior' ), 'edit_posts', 'nordicinterior-searches', 'ni_admin_searches_page' );
}
add_action( 'admin_menu', 'ni_admin_menu' );

/**
 * Small horizontal bar table.
 *
 * @param string $title Title.
 * @param array  $rows  Rows: [label, value, url?].
 */
function ni_admin_bar_table( $title, $rows ) {
	$max = 1;
	foreach ( $rows as $row ) {
		$max = max( $max, (int) $row[1] );
	}
	echo '<div class="ni-admin-card"><h2>' . esc_html( $title ) . '</h2>';
	if ( ! $rows ) {
		echo '<p class="description">' . esc_html__( 'Inga data ännu.', 'nordicinterior' ) . '</p></div>';
		return;
	}
	echo '<table class="ni-bars"><tbody>';
	foreach ( $rows as $row ) {
		$pct   = round( (int) $row[1] / $max * 100 );
		$label = ! empty( $row[2] ) ? '<a href="' . esc_url( $row[2] ) . '">' . esc_html( $row[0] ) . '</a>' : esc_html( $row[0] );
		printf(
			'<tr><th scope="row">%1$s</th><td><span class="ni-bars__bar" style="width:%2$d%%"></span></td><td class="ni-bars__num">%3$s</td></tr>',
			$label, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above.
			(int) $pct,
			esc_html( number_format_i18n( (int) $row[1] ) )
		);
	}
	echo '</tbody></table></div>';
}

/**
 * Dashboard page.
 */
function ni_admin_dashboard_page() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only filter.
	$days   = isset( $_GET['days'] ) ? max( 1, min( 365, absint( $_GET['days'] ) ) ) : 30;
	$totals = ni_click_totals( $days );
	$names  = static function ( $rows, $type ) {
		$out = array();
		foreach ( $rows as $row ) {
			$label = (string) $row->k;
			$url   = '';
			switch ( $type ) {
				case 'product':
					$label = get_the_title( (int) $row->k );
					$url   = (string) get_edit_post_link( (int) $row->k );
					break;
				case 'store':
				case 'brand':
					$term  = get_term( (int) $row->k );
					$label = $term && ! is_wp_error( $term ) ? $term->name : '#' . $row->k;
					break;
				case 'room':
				case 'style':
					$term  = get_term_by( 'slug', (string) $row->k, 'room' === $type ? 'ni_room' : 'ni_style' );
					$label = $term ? $term->name : (string) $row->k;
					break;
				case 'placement':
					$label = ni_placements()[ (string) $row->k ] ?? (string) $row->k;
					break;
			}
			$out[] = array( $label, (int) $row->clicks, $url );
		}
		return $out;
	};
	?>
	<div class="wrap ni-admin">
		<h1><?php esc_html_e( 'Nordic Interior – översikt', 'nordicinterior' ); ?></h1>
		<form method="get" class="ni-admin-filter">
			<input type="hidden" name="page" value="nordicinterior">
			<label for="ni-days"><?php esc_html_e( 'Period', 'nordicinterior' ); ?></label>
			<select id="ni-days" name="days" onchange="this.form.submit()">
				<?php foreach ( array( 7, 30, 90, 365 ) as $d ) : ?>
					<option value="<?php echo esc_attr( (string) $d ); ?>" <?php selected( $days, $d ); ?>>
						<?php
						/* translators: %d: number of days. */
						echo esc_html( sprintf( _n( '%d dag', '%d dagar', $d, 'nordicinterior' ), $d ) );
						?>
					</option>
				<?php endforeach; ?>
			</select>
			<noscript><button class="button"><?php esc_html_e( 'Visa', 'nordicinterior' ); ?></button></noscript>
		</form>

		<div class="ni-kpis">
			<div class="ni-kpi"><span class="ni-kpi__num"><?php echo esc_html( number_format_i18n( $totals['total'] ) ); ?></span><span><?php esc_html_e( 'Klick till butiker', 'nordicinterior' ); ?></span></div>
			<div class="ni-kpi"><span class="ni-kpi__num"><?php echo esc_html( number_format_i18n( count( ni_broken_link_products( 500 ) ) ) ); ?></span><span><?php esc_html_e( 'Trasiga länkar', 'nordicinterior' ); ?></span></div>
			<div class="ni-kpi"><span class="ni-kpi__num"><?php echo esc_html( number_format_i18n( count( ni_get_active_campaigns( array( 'posts_per_page' => 50 ) ) ) ) ); ?></span><span><?php esc_html_e( 'Aktiva kampanjer', 'nordicinterior' ); ?></span></div>
			<div class="ni-kpi"><span class="ni-kpi__num"><?php echo esc_html( number_format_i18n( count( ni_products_with_price_drop( 50 ) ) ) ); ?></span><span><?php esc_html_e( 'Prissänkningar', 'nordicinterior' ); ?></span></div>
		</div>

		<?php if ( $totals['days'] ) : ?>
			<div class="ni-admin-card">
				<h2><?php esc_html_e( 'Klick per dag', 'nordicinterior' ); ?></h2>
				<div class="ni-spark" role="img" aria-label="<?php esc_attr_e( 'Stapeldiagram över klick per dag', 'nordicinterior' ); ?>">
					<?php
					$max = max( $totals['days'] );
					foreach ( $totals['days'] as $date => $count ) {
						printf( '<span style="height:%1$d%%" title="%2$s: %3$d"></span>', (int) round( $count / max( 1, $max ) * 100 ), esc_attr( (string) $date ), (int) $count );
					}
					?>
				</div>
			</div>
		<?php endif; ?>

		<div class="ni-admin-grid">
			<?php
			ni_admin_bar_table( __( 'Klick per placering', 'nordicinterior' ), $names( ni_click_stats( 'placement', $days, 10 ), 'placement' ) );
			ni_admin_bar_table( __( 'Klick per produkt', 'nordicinterior' ), $names( ni_click_stats( 'object_id', $days, 10 ), 'product' ) );
			ni_admin_bar_table( __( 'Klick per butik', 'nordicinterior' ), $names( ni_click_stats( 'store_id', $days, 10 ), 'store' ) );
			ni_admin_bar_table( __( 'Klick per varumärke', 'nordicinterior' ), $names( ni_click_stats( 'brand_id', $days, 10 ), 'brand' ) );
			ni_admin_bar_table( __( 'Klick per rum', 'nordicinterior' ), $names( ni_click_stats( 'room', $days, 10 ), 'room' ) );
			ni_admin_bar_table( __( 'Klick per stil', 'nordicinterior' ), $names( ni_click_stats( 'style', $days, 10 ), 'style' ) );

			$searches = array();
			foreach ( ni_top_searches( 10 ) as $row ) {
				$searches[] = array( $row->term, (int) $row->hits );
			}
			ni_admin_bar_table( __( 'Populära sökningar', 'nordicinterior' ), $searches );

			$zero = array();
			foreach ( ni_zero_result_searches( 10 ) as $row ) {
				$zero[] = array( $row->term, (int) $row->hits );
			}
			ni_admin_bar_table( __( 'Sökningar utan träff', 'nordicinterior' ), $zero );
			?>

			<div class="ni-admin-card">
				<h2><?php esc_html_e( 'Kampanjer som snart slutar', 'nordicinterior' ); ?></h2>
				<?php $ending = ni_campaigns_ending_soon( 7 ); ?>
				<?php if ( $ending ) : ?>
					<ul>
						<?php foreach ( $ending as $c ) : ?>
							<li><a href="<?php echo esc_url( (string) get_edit_post_link( $c->ID ) ); ?>"><?php echo esc_html( get_the_title( $c ) ); ?></a> – <?php echo esc_html( substr( (string) get_post_meta( $c->ID, '_ni_end', true ), 0, 16 ) ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p class="description"><?php esc_html_e( 'Inga kampanjer slutar inom en vecka.', 'nordicinterior' ); ?></p>
				<?php endif; ?>
			</div>

			<div class="ni-admin-card">
				<h2><?php esc_html_e( 'Trasiga länkar', 'nordicinterior' ); ?></h2>
				<?php $broken = ni_broken_link_products( 8 ); ?>
				<?php if ( $broken ) : ?>
					<ul>
						<?php foreach ( $broken as $id ) : ?>
							<li><a href="<?php echo esc_url( (string) get_edit_post_link( $id ) ); ?>"><?php echo esc_html( get_the_title( $id ) ); ?></a></li>
						<?php endforeach; ?>
					</ul>
					<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=nordicinterior-links' ) ); ?>"><?php esc_html_e( 'Visa alla', 'nordicinterior' ); ?></a></p>
				<?php else : ?>
					<p class="description"><?php esc_html_e( 'Inga trasiga länkar hittades vid senaste kontrollen.', 'nordicinterior' ); ?></p>
				<?php endif; ?>
			</div>

			<div class="ni-admin-card">
				<h2><?php esc_html_e( 'Produkter med prissänkning', 'nordicinterior' ); ?></h2>
				<?php $drops = ni_products_with_price_drop( 8 ); ?>
				<?php if ( $drops ) : ?>
					<ul>
						<?php
						foreach ( $drops as $id ) :
							$p = wc_get_product( $id );
							?>
							<li><a href="<?php echo esc_url( (string) get_edit_post_link( $id ) ); ?>"><?php echo esc_html( get_the_title( $id ) ); ?></a> – −<?php echo esc_html( (string) ni_price_drop( $p ) ); ?> %</li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p class="description"><?php esc_html_e( 'Inga prissänkningar de senaste 30 dagarna.', 'nordicinterior' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<p class="description">
			<?php
			$last = (int) get_option( 'ni_last_recalc' );
			/* translators: %s: date and time. */
			echo esc_html( $last ? sprintf( __( 'Sortering räknades senast om %s.', 'nordicinterior' ), wp_date( 'Y-m-d H:i', $last ) ) : __( 'Nattlig omräkning har inte körts ännu.', 'nordicinterior' ) );
			?>
			<?php esc_html_e( 'Statistiken innehåller inga personuppgifter. Botar och inloggade redaktörer räknas inte.', 'nordicinterior' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Broken links page.
 */
function ni_admin_links_page() {
	$ids  = ni_broken_link_products( 200 );
	$last = (int) get_option( 'ni_last_link_check' );
	?>
	<div class="wrap ni-admin">
		<h1><?php esc_html_e( 'Länkkontroll', 'nordicinterior' ); ?></h1>
		<p>
			<?php
			echo esc_html(
				$last
					/* translators: %s: date and time. */
					? sprintf( __( 'Senaste fullständiga kontroll: %s. Kontrollen körs varje natt i omgångar om 25 produkter.', 'nordicinterior' ), wp_date( 'Y-m-d H:i', $last ) )
					: __( 'Länkkontrollen körs varje natt i omgångar om 25 produkter.', 'nordicinterior' )
			);
			?>
		</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'ni_run_link_check' ); ?>
			<input type="hidden" name="action" value="ni_run_link_check">
			<?php submit_button( __( 'Starta kontroll nu', 'nordicinterior' ), 'secondary', 'submit', false ); ?>
		</form>
		<table class="widefat striped" style="margin-top:1em">
			<thead><tr><th><?php esc_html_e( 'Produkt', 'nordicinterior' ); ?></th><th><?php esc_html_e( 'Butik', 'nordicinterior' ); ?></th><th><?php esc_html_e( 'Affiliatelänk', 'nordicinterior' ); ?></th><th><?php esc_html_e( 'Kontrollerad', 'nordicinterior' ); ?></th></tr></thead>
			<tbody>
				<?php if ( ! $ids ) : ?>
					<tr><td colspan="4"><?php esc_html_e( 'Inga trasiga länkar.', 'nordicinterior' ); ?></td></tr>
				<?php endif; ?>
				<?php
				foreach ( $ids as $id ) :
					$product = wc_get_product( $id );
					if ( ! $product ) {
						continue;
					}
					$store = ni_product_store( $product );
					?>
					<tr>
						<td><a href="<?php echo esc_url( (string) get_edit_post_link( $id ) ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></td>
						<td><?php echo esc_html( $store ? $store->name : '–' ); ?></td>
						<td><code><?php echo esc_html( ni_product_url( $product ) ); ?></code></td>
						<td><?php echo esc_html( wp_date( 'Y-m-d H:i', (int) $product->get_meta( '_ni_link_checked' ) ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * Manual start of the link check.
 */
function ni_handle_run_link_check() {
	if ( ! current_user_can( 'edit_products' ) ) {
		wp_die( esc_html__( 'Du har inte behörighet att göra detta.', 'nordicinterior' ) );
	}
	check_admin_referer( 'ni_run_link_check' );
	ni_start_link_check();
	wp_safe_redirect( add_query_arg( 'ni-notice', 'links-started', admin_url( 'admin.php?page=nordicinterior-links' ) ) );
	exit;
}
add_action( 'admin_post_ni_run_link_check', 'ni_handle_run_link_check' );

/**
 * Searches page.
 */
function ni_admin_searches_page() {
	?>
	<div class="wrap ni-admin">
		<h1><?php esc_html_e( 'Sökningar', 'nordicinterior' ); ?></h1>
		<p><?php esc_html_e( 'Söktermer loggas anonymt (utan IP-adress eller användare). Lägg till synonymer under Theme Adjustments → Sök för sökningar utan träff.', 'nordicinterior' ); ?></p>
		<div class="ni-admin-grid">
			<?php
			foreach ( array(
				'zero' => __( 'Sökningar utan träff', 'nordicinterior' ),
				'top'  => __( 'Populära sökningar', 'nordicinterior' ),
			) as $type => $title ) :
				?>
				<div class="ni-admin-card">
					<h2><?php echo esc_html( $title ); ?></h2>
					<table class="widefat striped">
						<thead><tr><th><?php esc_html_e( 'Sökning', 'nordicinterior' ); ?></th><th><?php esc_html_e( 'Antal', 'nordicinterior' ); ?></th><th><?php esc_html_e( 'Senast', 'nordicinterior' ); ?></th></tr></thead>
						<tbody>
							<?php foreach ( 'zero' === $type ? ni_zero_result_searches( 100 ) : ni_top_searches( 100 ) as $row ) : ?>
								<tr><td><?php echo esc_html( $row->term ); ?></td><td><?php echo esc_html( (string) $row->hits ); ?></td><td><?php echo esc_html( substr( (string) $row->last_seen, 0, 16 ) ); ?></td></tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/**
 * Landing page texts.
 */
function ni_admin_landings_page() {
	$texts = ni_landing_texts();
	$rooms = ni_get_terms( 'ni_room' );
	$style = ni_get_terms( 'ni_style' );
	$cats  = ni_get_terms(
		'product_cat',
		array(
			'orderby'    => 'name',
			'hide_empty' => false,
		)
	);
	?>
	<div class="wrap ni-admin">
		<h1><?php esc_html_e( 'Landningssidor', 'nordicinterior' ); ?></h1>
		<p><?php esc_html_e( 'Rum + kategori (/rum/sovrum/kategori/belysning/) och stil + kategori (/stil/japandi/kategori/soffor/) är indexerbara sidor. Skriv egen rubrik och ingress för de viktigaste kombinationerna – övriga får en automatisk text.', 'nordicinterior' ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'ni_save_landings' ); ?>
			<input type="hidden" name="action" value="ni_save_landings">
			<table class="widefat striped ni-landings">
				<thead><tr><th><?php esc_html_e( 'Rum eller stil', 'nordicinterior' ); ?></th><th><?php esc_html_e( 'Kategori', 'nordicinterior' ); ?></th><th><?php esc_html_e( 'Rubrik', 'nordicinterior' ); ?></th><th><?php esc_html_e( 'Ingress', 'nordicinterior' ); ?></th><th><?php esc_html_e( 'Ta bort', 'nordicinterior' ); ?></th></tr></thead>
				<tbody>
					<?php
					$rows   = $texts;
					$rows[] = array(); // Blank row for a new entry.
					$i      = 0;
					foreach ( $rows as $key => $row ) :
						$parts    = is_string( $key ) ? explode( '|', $key ) : array( '', '' );
						$selected = $parts[0] ?? '';
						$cat      = $parts[1] ?? '';
						?>
						<tr>
							<td>
								<label class="screen-reader-text" for="ni-l-term-<?php echo esc_attr( (string) $i ); ?>"><?php esc_html_e( 'Rum eller stil', 'nordicinterior' ); ?></label>
								<select name="ni_l[<?php echo esc_attr( (string) $i ); ?>][term]" id="ni-l-term-<?php echo esc_attr( (string) $i ); ?>">
									<option value=""><?php esc_html_e( '— Välj —', 'nordicinterior' ); ?></option>
									<optgroup label="<?php esc_attr_e( 'Rum', 'nordicinterior' ); ?>">
										<?php foreach ( $rooms as $t ) : ?>
											<option value="<?php echo esc_attr( 'ni_room:' . $t->slug ); ?>" <?php selected( $selected, 'ni_room:' . $t->slug ); ?>><?php echo esc_html( $t->name ); ?></option>
										<?php endforeach; ?>
									</optgroup>
									<optgroup label="<?php esc_attr_e( 'Stilar', 'nordicinterior' ); ?>">
										<?php foreach ( $style as $t ) : ?>
											<option value="<?php echo esc_attr( 'ni_style:' . $t->slug ); ?>" <?php selected( $selected, 'ni_style:' . $t->slug ); ?>><?php echo esc_html( $t->name ); ?></option>
										<?php endforeach; ?>
									</optgroup>
								</select>
							</td>
							<td>
								<label class="screen-reader-text" for="ni-l-cat-<?php echo esc_attr( (string) $i ); ?>"><?php esc_html_e( 'Kategori', 'nordicinterior' ); ?></label>
								<select name="ni_l[<?php echo esc_attr( (string) $i ); ?>][cat]" id="ni-l-cat-<?php echo esc_attr( (string) $i ); ?>">
									<option value=""><?php esc_html_e( '— Välj —', 'nordicinterior' ); ?></option>
									<?php foreach ( $cats as $t ) : ?>
										<option value="<?php echo esc_attr( $t->slug ); ?>" <?php selected( $cat, $t->slug ); ?>><?php echo esc_html( $t->name ); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
							<td><input type="text" class="widefat" aria-label="<?php esc_attr_e( 'Rubrik', 'nordicinterior' ); ?>" name="ni_l[<?php echo esc_attr( (string) $i ); ?>][title]" value="<?php echo esc_attr( $row['title'] ?? '' ); ?>"></td>
							<td><textarea class="widefat" rows="2" aria-label="<?php esc_attr_e( 'Ingress', 'nordicinterior' ); ?>" name="ni_l[<?php echo esc_attr( (string) $i ); ?>][intro]"><?php echo esc_textarea( $row['intro'] ?? '' ); ?></textarea></td>
							<td><input type="checkbox" aria-label="<?php esc_attr_e( 'Ta bort', 'nordicinterior' ); ?>" name="ni_l[<?php echo esc_attr( (string) $i ); ?>][delete]" value="1"></td>
						</tr>
						<?php
						++$i;
					endforeach;
					?>
				</tbody>
			</table>
			<?php submit_button( __( 'Spara landningssidor', 'nordicinterior' ) ); ?>
		</form>
	</div>
	<?php
}

/**
 * Save landing texts.
 */
function ni_handle_save_landings() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'Du har inte behörighet att göra detta.', 'nordicinterior' ) );
	}
	check_admin_referer( 'ni_save_landings' );
	$out = array();
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitised per field.
	$rows = isset( $_POST['ni_l'] ) && is_array( $_POST['ni_l'] ) ? wp_unslash( $_POST['ni_l'] ) : array();
	foreach ( $rows as $row ) {
		if ( ! empty( $row['delete'] ) || empty( $row['term'] ) || empty( $row['cat'] ) ) {
			continue;
		}
		$term = sanitize_text_field( (string) $row['term'] );
		if ( ! preg_match( '/^ni_(room|style):[a-z0-9\-%]+$/', $term ) ) {
			continue;
		}
		$out[ $term . '|' . sanitize_title( (string) $row['cat'] ) ] = array(
			'title' => sanitize_text_field( (string) ( $row['title'] ?? '' ) ),
			'intro' => sanitize_textarea_field( (string) ( $row['intro'] ?? '' ) ),
		);
	}
	update_option( 'ni_landing_texts', $out, false );
	wp_safe_redirect( add_query_arg( 'ni-notice', 'saved', admin_url( 'admin.php?page=nordicinterior-landings' ) ) );
	exit;
}
add_action( 'admin_post_ni_save_landings', 'ni_handle_save_landings' );

/**
 * Admin notices for our actions.
 */
function ni_admin_notices() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display only.
	$notice = isset( $_GET['ni-notice'] ) ? sanitize_key( wp_unslash( $_GET['ni-notice'] ) ) : '';
	$map    = array(
		'saved'         => array( 'success', __( 'Sparat.', 'nordicinterior' ) ),
		'menu-ok'       => array( 'success', __( 'Huvudmenyn är skapad och vald som huvud- och mobilmeny.', 'nordicinterior' ) ),
		'menu-error'    => array( 'error', __( 'Menyn kunde inte skapas.', 'nordicinterior' ) ),
		'links-started' => array( 'info', __( 'Länkkontrollen har startat och körs i bakgrunden.', 'nordicinterior' ) ),
		'demo-ok'       => array( 'success', __( 'Demoinnehållet är importerat.', 'nordicinterior' ) ),
		'recalc-ok'     => array( 'success', __( 'Sorteringen är omräknad.', 'nordicinterior' ) ),
	);
	if ( isset( $map[ $notice ] ) ) {
		printf( '<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>', esc_attr( $map[ $notice ][0] ), esc_html( $map[ $notice ][1] ) );
	}
}
add_action( 'admin_notices', 'ni_admin_notices' );
