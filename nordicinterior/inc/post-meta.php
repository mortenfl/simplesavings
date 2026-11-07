<?php
/**
 * Inspiration posts: related products field, room/style/brand relations and
 * an index of which products appear in which articles.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register post meta (REST visible for the block editor).
 */
function ni_register_post_meta() {
	register_post_meta(
		'post',
		'_ni_related_products',
		array(
			'type'          => 'string',
			'single'        => true,
			'show_in_rest'  => true,
			'auth_callback' => static function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);
	register_post_meta(
		'post',
		'_ni_lead',
		array(
			'type'          => 'string',
			'single'        => true,
			'show_in_rest'  => true,
			'auth_callback' => static function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}
add_action( 'init', 'ni_register_post_meta' );

/**
 * Meta box.
 */
function ni_post_meta_box() {
	add_meta_box( 'ni-post', __( 'Nordic Interior', 'nordicinterior' ), 'ni_post_meta_box_html', 'post', 'normal', 'default' );
}
add_action( 'add_meta_boxes', 'ni_post_meta_box' );

/**
 * Meta box markup.
 *
 * @param WP_Post $post Post.
 */
function ni_post_meta_box_html( $post ) {
	wp_nonce_field( 'ni_post_save', 'ni_post_nonce' );
	?>
	<p>
		<label for="ni-lead"><strong><?php esc_html_e( 'Ingress', 'nordicinterior' ); ?></strong></label><br>
		<textarea id="ni-lead" name="ni_lead" rows="3" class="widefat"><?php echo esc_textarea( (string) get_post_meta( $post->ID, '_ni_lead', true ) ); ?></textarea>
		<span class="description"><?php esc_html_e( 'Visas under rubriken. Lämnas tomt används utdraget.', 'nordicinterior' ); ?></span>
	</p>
	<p>
		<label for="ni-related"><strong><?php esc_html_e( 'Relaterade produkter', 'nordicinterior' ); ?></strong></label><br>
		<input type="text" id="ni-related" name="ni_related_products" class="widefat" value="<?php echo esc_attr( (string) get_post_meta( $post->ID, '_ni_related_products', true ) ); ?>" placeholder="123, 456, 789">
		<span class="description"><?php esc_html_e( 'Produkt-ID:n separerade med kommatecken. Visas som produktrad under artikeln.', 'nordicinterior' ); ?></span>
	</p>
	<?php
}

/**
 * Save post meta and update the product reference index.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post.
 */
function ni_post_save( $post_id, $post ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['ni_post_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ni_post_nonce'] ) ), 'ni_post_save' ) ) {
		if ( isset( $_POST['ni_related_products'] ) ) {
			$ids = ni_id_list( sanitize_text_field( wp_unslash( $_POST['ni_related_products'] ) ) );
			update_post_meta( $post_id, '_ni_related_products', implode( ',', $ids ) );
		}
		if ( isset( $_POST['ni_lead'] ) ) {
			update_post_meta( $post_id, '_ni_lead', sanitize_textarea_field( wp_unslash( $_POST['ni_lead'] ) ) );
		}
	}
	ni_index_post_products( $post_id, $post );
}
add_action( 'save_post_post', 'ni_post_save', 10, 2 );

/**
 * Store one _ni_product_ref row per product referenced by the post, so that
 * product pages can list "Inspiration där produkten förekommer".
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post.
 */
function ni_index_post_products( $post_id, $post ) {
	$ids = array_unique(
		array_merge(
			ni_id_list( (string) get_post_meta( $post_id, '_ni_related_products', true ) ),
			ni_look_product_ids( (string) $post->post_content )
		)
	);
	delete_post_meta( $post_id, '_ni_product_ref' );
	foreach ( $ids as $id ) {
		add_post_meta( $post_id, '_ni_product_ref', (int) $id );
	}
}

/**
 * Posts that feature a product.
 *
 * @param int $product_id Product ID.
 * @param int $limit      Limit.
 * @return WP_Post[]
 */
function ni_posts_featuring_product( $product_id, $limit = 3 ) {
	return get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'no_found_rows'  => true,
			'meta_key'       => '_ni_product_ref', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Indexed lookup.
			'meta_value'     => (string) absint( $product_id ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Indexed lookup.
		)
	);
}

/**
 * The lead (ingress) of a post.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function ni_post_lead( $post_id ) {
	$lead = (string) get_post_meta( $post_id, '_ni_lead', true );
	if ( '' === $lead && has_excerpt( $post_id ) ) {
		$lead = (string) get_the_excerpt( $post_id );
	}
	return $lead;
}

/**
 * Label (room or style) for a post.
 *
 * @param int $post_id Post ID.
 * @return WP_Term|null
 */
function ni_post_label( $post_id ) {
	foreach ( array( 'ni_room', 'ni_style', 'category' ) as $tax ) {
		$terms = get_the_terms( $post_id, $tax );
		if ( $terms && ! is_wp_error( $terms ) ) {
			return $terms[0];
		}
	}
	return null;
}

/**
 * Hide date, author and reading time everywhere posts are listed – they are
 * only part of the structured data. Some plugins print these through the
 * standard template tags, so filter them out on the front end.
 *
 * @param string $value Value.
 * @return string
 */
function ni_hide_post_byline( $value ) {
	if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || doing_action( 'wp_head' ) || doing_filter( 'wp_head' ) ) {
		return $value;
	}
	return '';
}
add_filter( 'the_author_posts_link', 'ni_hide_post_byline' );
