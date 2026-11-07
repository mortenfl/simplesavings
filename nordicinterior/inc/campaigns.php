<?php
/**
 * Campaigns (ni_campaign): link, discount code, badge, period and relations.
 *
 * Campaigns are only shown inside their date range. Outgoing campaign links
 * go through /go/c/{id}/ (see affiliate.php).
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the post type.
 */
function ni_register_campaigns() {
	register_post_type(
		'ni_campaign',
		array(
			'labels'        => array(
				'name'               => __( 'Kampanjer', 'nordicinterior' ),
				'singular_name'      => __( 'Kampanj', 'nordicinterior' ),
				'add_new'            => __( 'Lägg till kampanj', 'nordicinterior' ),
				'add_new_item'       => __( 'Lägg till kampanj', 'nordicinterior' ),
				'edit_item'          => __( 'Redigera kampanj', 'nordicinterior' ),
				'all_items'          => __( 'Alla kampanjer', 'nordicinterior' ),
				'search_items'       => __( 'Sök kampanjer', 'nordicinterior' ),
				'not_found'          => __( 'Inga kampanjer hittades.', 'nordicinterior' ),
				'not_found_in_trash' => __( 'Inga kampanjer i papperskorgen.', 'nordicinterior' ),
				'menu_name'          => __( 'Kampanjer', 'nordicinterior' ),
			),
			'public'        => true,
			'show_in_rest'  => true,
			'has_archive'   => _x( 'kampanjer', 'URL slug', 'nordicinterior' ),
			'rewrite'       => array(
				'slug'       => _x( 'kampanj', 'URL slug', 'nordicinterior' ),
				'with_front' => false,
			),
			'menu_icon'     => 'dashicons-megaphone',
			'menu_position' => 56,
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
		)
	);
}
add_action( 'init', 'ni_register_campaigns' );

/**
 * Meta fields.
 *
 * @return array<string, array{label:string,type:string,desc?:string}>
 */
function ni_campaign_fields() {
	return array(
		'url'    => array(
			'label' => __( 'Kampanjlänk (affiliatelänk)', 'nordicinterior' ),
			'type'  => 'url',
		),
		'code'   => array(
			'label' => __( 'Rabattkod', 'nordicinterior' ),
			'type'  => 'text',
		),
		'badge'  => array(
			'label' => __( 'Badge', 'nordicinterior' ),
			'type'  => 'text',
			'desc'  => __( 'Kort text, t.ex. ”−25 %” eller ”Fri frakt”.', 'nordicinterior' ),
		),
		'start'  => array(
			'label' => __( 'Start', 'nordicinterior' ),
			'type'  => 'datetime-local',
		),
		'end'    => array(
			'label' => __( 'Slut', 'nordicinterior' ),
			'type'  => 'datetime-local',
		),
		'main'   => array(
			'label' => __( 'Huvudkampanj', 'nordicinterior' ),
			'type'  => 'checkbox',
		),
		'params' => array(
			'label' => __( 'Extra parametrar (valfritt)', 'nordicinterior' ),
			'type'  => 'text',
			'desc'  => __( 'Läggs till länken, t.ex. subid=startsida. Ersätter globala parametrar med samma namn.', 'nordicinterior' ),
		),
	);
}

/**
 * Add meta box.
 */
function ni_campaign_meta_box() {
	add_meta_box( 'ni-campaign', __( 'Kampanjinställningar', 'nordicinterior' ), 'ni_campaign_meta_box_html', 'ni_campaign', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'ni_campaign_meta_box' );

/**
 * Meta box markup.
 *
 * @param WP_Post $post Post.
 */
function ni_campaign_meta_box_html( $post ) {
	wp_nonce_field( 'ni_campaign_save', 'ni_campaign_nonce' );
	echo '<table class="form-table" role="presentation">';
	foreach ( ni_campaign_fields() as $key => $field ) {
		$value = get_post_meta( $post->ID, '_ni_' . $key, true );
		$id    = 'ni-campaign-' . $key;
		echo '<tr><th scope="row"><label for="' . esc_attr( $id ) . '">' . esc_html( $field['label'] ) . '</label></th><td>';
		if ( 'checkbox' === $field['type'] ) {
			printf( '<input type="checkbox" id="%1$s" name="ni_campaign[%2$s]" value="1" %3$s>', esc_attr( $id ), esc_attr( $key ), checked( (bool) $value, true, false ) );
		} else {
			if ( 'datetime-local' === $field['type'] && $value ) {
				$value = str_replace( ' ', 'T', substr( (string) $value, 0, 16 ) );
			}
			printf( '<input type="%1$s" id="%2$s" name="ni_campaign[%3$s]" value="%4$s" class="regular-text">', esc_attr( $field['type'] ), esc_attr( $id ), esc_attr( $key ), esc_attr( (string) $value ) );
		}
		if ( ! empty( $field['desc'] ) ) {
			echo '<p class="description">' . esc_html( $field['desc'] ) . '</p>';
		}
		echo '</td></tr>';
	}
	echo '</table>';
	echo '<p class="description">' . esc_html__( 'Koppla kampanjen till rum, stilar, varumärken och butiker i sidopanelen. Kampanjen visas bara mellan start och slut.', 'nordicinterior' ) . '</p>';
	printf(
		'<p><strong>%1$s</strong> <code>%2$s</code></p>',
		esc_html__( 'Utgående länk:', 'nordicinterior' ),
		esc_html( ni_campaign_go_url( $post->ID ) )
	);
}

/**
 * Save campaign meta.
 *
 * @param int $post_id Post ID.
 */
function ni_campaign_save( $post_id ) {
	if ( ! isset( $_POST['ni_campaign_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ni_campaign_nonce'] ) ), 'ni_campaign_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitised per field.
	$input = isset( $_POST['ni_campaign'] ) && is_array( $_POST['ni_campaign'] ) ? wp_unslash( $_POST['ni_campaign'] ) : array();
	foreach ( ni_campaign_fields() as $key => $field ) {
		$raw = $input[ $key ] ?? '';
		switch ( $field['type'] ) {
			case 'url':
				$value = esc_url_raw( (string) $raw );
				break;
			case 'checkbox':
				$value = $raw ? '1' : '';
				break;
			case 'datetime-local':
				$value = '';
				if ( $raw && preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', (string) $raw ) ) {
					$value = str_replace( 'T', ' ', substr( (string) $raw, 0, 16 ) ) . ':00';
				}
				break;
			default:
				$value = sanitize_text_field( (string) $raw );
		}
		update_post_meta( $post_id, '_ni_' . $key, $value );
	}
}
add_action( 'save_post_ni_campaign', 'ni_campaign_save' );

/**
 * Is a campaign active right now?
 *
 * @param int $id Campaign ID.
 * @return bool
 */
function ni_campaign_is_active( $id ) {
	if ( 'publish' !== get_post_status( $id ) ) {
		return false;
	}
	$now   = current_time( 'mysql' );
	$start = (string) get_post_meta( $id, '_ni_start', true );
	$end   = (string) get_post_meta( $id, '_ni_end', true );
	if ( $start && $start > $now ) {
		return false;
	}
	if ( $end && $end < $now ) {
		return false;
	}
	return true;
}

/**
 * Query arguments that restrict campaigns to their date range.
 *
 * @return array
 */
function ni_campaign_active_meta_query() {
	$now = current_time( 'mysql' );
	return array(
		'relation' => 'AND',
		array(
			'relation' => 'OR',
			array(
				'key'     => '_ni_start',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'   => '_ni_start',
				'value' => '',
			),
			array(
				'key'     => '_ni_start',
				'value'   => $now,
				'compare' => '<=',
			),
		),
		array(
			'relation' => 'OR',
			array(
				'key'     => '_ni_end',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'   => '_ni_end',
				'value' => '',
			),
			array(
				'key'     => '_ni_end',
				'value'   => $now,
				'compare' => '>=',
			),
		),
	);
}

/**
 * Active campaigns, main campaign first.
 *
 * @param array $args Extra WP_Query args (tax_query, posts_per_page ...).
 * @return WP_Post[]
 */
function ni_get_active_campaigns( $args = array() ) {
	$args  = wp_parse_args(
		$args,
		array(
			'post_type'      => 'ni_campaign',
			'post_status'    => 'publish',
			'posts_per_page' => 6,
			'no_found_rows'  => true,
			'meta_query'     => ni_campaign_active_meta_query(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Few campaigns.
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);
	$posts = get_posts( $args );
	usort(
		$posts,
		static function ( $a, $b ) {
			return (int) (bool) get_post_meta( $b->ID, '_ni_main', true ) <=> (int) (bool) get_post_meta( $a->ID, '_ni_main', true );
		}
	);
	return $posts;
}

/**
 * Active campaigns for a term (room/style/brand/store).
 *
 * @param string $taxonomy Taxonomy.
 * @param int    $term_id  Term ID.
 * @return WP_Post[]
 */
function ni_campaigns_for_term( $taxonomy, $term_id ) {
	return ni_get_active_campaigns(
		array(
			'tax_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Few campaigns.
				array(
					'taxonomy' => $taxonomy,
					'terms'    => array( (int) $term_id ),
				),
			),
		)
	);
}

/**
 * Only active campaigns in the archive.
 *
 * @param WP_Query $query Query.
 */
function ni_campaign_archive_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'ni_campaign' ) ) {
		return;
	}
	$query->set( 'meta_query', ni_campaign_active_meta_query() );
	$query->set( 'posts_per_page', 24 );
}
add_action( 'pre_get_posts', 'ni_campaign_archive_query' );

/**
 * Expired or scheduled campaigns should not be indexable or reachable as
 * singles; send visitors to the archive instead.
 */
function ni_campaign_single_redirect() {
	if ( is_singular( 'ni_campaign' ) && ! ni_campaign_is_active( get_queried_object_id() ) && ! current_user_can( 'edit_posts' ) ) {
		wp_safe_redirect( get_post_type_archive_link( 'ni_campaign' ), 302 );
		exit;
	}
}
add_action( 'template_redirect', 'ni_campaign_single_redirect' );

/**
 * Admin columns.
 *
 * @param array $columns Columns.
 * @return array
 */
function ni_campaign_columns( $columns ) {
	$columns['ni_period'] = __( 'Period', 'nordicinterior' );
	$columns['ni_status'] = __( 'Status', 'nordicinterior' );
	$columns['ni_code']   = __( 'Rabattkod', 'nordicinterior' );
	return $columns;
}
add_filter( 'manage_ni_campaign_posts_columns', 'ni_campaign_columns' );

/**
 * Admin column values.
 *
 * @param string $column  Column.
 * @param int    $post_id Post ID.
 */
function ni_campaign_column_values( $column, $post_id ) {
	switch ( $column ) {
		case 'ni_period':
			echo esc_html( substr( (string) get_post_meta( $post_id, '_ni_start', true ), 0, 16 ) . ' – ' . substr( (string) get_post_meta( $post_id, '_ni_end', true ), 0, 16 ) );
			break;
		case 'ni_status':
			echo ni_campaign_is_active( $post_id ) ? '<span class="ni-pill ni-pill--ok">' . esc_html__( 'Aktiv', 'nordicinterior' ) . '</span>' : '<span class="ni-pill">' . esc_html__( 'Inaktiv', 'nordicinterior' ) . '</span>';
			if ( get_post_meta( $post_id, '_ni_main', true ) ) {
				echo ' <span class="ni-pill ni-pill--main">' . esc_html__( 'Huvud', 'nordicinterior' ) . '</span>';
			}
			break;
		case 'ni_code':
			echo esc_html( (string) get_post_meta( $post_id, '_ni_code', true ) );
			break;
	}
}
add_action( 'manage_ni_campaign_posts_custom_column', 'ni_campaign_column_values', 10, 2 );

/**
 * Print a campaign card.
 *
 * @param WP_Post $campaign  Campaign.
 * @param string  $variant   main|compact.
 * @param string  $placement Tracking placement.
 */
function ni_campaign_card( $campaign, $variant = 'compact', $placement = 'campaign' ) {
	$code  = (string) get_post_meta( $campaign->ID, '_ni_code', true );
	$badge = (string) get_post_meta( $campaign->ID, '_ni_badge', true );
	$end   = (string) get_post_meta( $campaign->ID, '_ni_end', true );
	$url   = ni_campaign_go_url( $campaign->ID, $placement );
	$store = get_the_terms( $campaign->ID, 'ni_store' );
	$store = $store && ! is_wp_error( $store ) ? $store[0] : null;
	?>
	<article class="ni-campaign ni-campaign--<?php echo esc_attr( $variant ); ?>">
		<?php if ( 'main' === $variant && has_post_thumbnail( $campaign ) ) : ?>
			<div class="ni-campaign__media"><?php echo get_the_post_thumbnail( $campaign, 'ni-wide', array( 'loading' => 'lazy' ) ); ?></div>
		<?php endif; ?>
		<div class="ni-campaign__body">
			<?php if ( $badge ) : ?>
				<span class="ni-campaign__badge"><?php echo esc_html( $badge ); ?></span>
			<?php endif; ?>
			<?php if ( $store ) : ?>
				<p class="ni-eyebrow"><?php echo esc_html( $store->name ); ?></p>
			<?php endif; ?>
			<h3 class="ni-campaign__title"><?php echo esc_html( get_the_title( $campaign ) ); ?></h3>
			<?php if ( has_excerpt( $campaign ) ) : ?>
				<p class="ni-campaign__text"><?php echo esc_html( get_the_excerpt( $campaign ) ); ?></p>
			<?php endif; ?>
			<?php if ( $code ) : ?>
				<div class="ni-code">
					<span class="ni-code__label"><?php esc_html_e( 'Rabattkod', 'nordicinterior' ); ?></span>
					<code class="ni-code__value"><?php echo esc_html( $code ); ?></code>
					<button type="button" class="ni-code__copy" data-ni-copy="<?php echo esc_attr( $code ); ?>">
						<?php ni_the_icon( 'copy', 16 ); ?><span><?php esc_html_e( 'Kopiera', 'nordicinterior' ); ?></span>
					</button>
				</div>
			<?php endif; ?>
			<div class="ni-campaign__foot">
				<?php if ( $end ) : ?>
					<p class="ni-countdown" data-ni-countdown="<?php echo esc_attr( (string) get_gmt_from_date( $end, 'c' ) ); ?>">
						<?php ni_the_icon( 'clock', 16 ); ?>
						<span class="ni-countdown__text">
							<?php
							/* translators: %s: end date of the campaign. */
							printf( esc_html__( 'Gäller t.o.m. %s', 'nordicinterior' ), esc_html( date_i18n( 'j F', strtotime( $end ) ) ) );
							?>
						</span>
					</p>
				<?php endif; ?>
				<a class="ni-btn ni-btn--small" href="<?php echo esc_url( $url ); ?>" rel="sponsored nofollow noopener" <?php echo ni_mod( 'new_tab' ) ? 'target="_blank"' : ''; ?>>
					<?php esc_html_e( 'Till kampanjen', 'nordicinterior' ); ?> <?php ni_the_icon( 'arrow-right', 16 ); ?>
				</a>
			</div>
		</div>
	</article>
	<?php
}
