<?php
/**
 * Custom taxonomies (Rum, Stil, Butik) and term meta for rooms, styles,
 * brands and stores.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register ni_room, ni_style and ni_store.
 */
function ni_register_taxonomies() {
	$common = array(
		'public'            => true,
		'hierarchical'      => false,
		'show_ui'           => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => true,
		'meta_box_cb'       => 'post_categories_meta_box',
	);

	register_taxonomy(
		'ni_room',
		array( 'product', 'post', 'ni_campaign' ),
		array_merge(
			$common,
			array(
				'hierarchical' => true,
				'labels'       => array(
					'name'          => __( 'Rum', 'nordicinterior' ),
					'singular_name' => __( 'Rum', 'nordicinterior' ),
					'menu_name'     => __( 'Rum', 'nordicinterior' ),
					'all_items'     => __( 'Alla rum', 'nordicinterior' ),
					'edit_item'     => __( 'Redigera rum', 'nordicinterior' ),
					'add_new_item'  => __( 'Lägg till rum', 'nordicinterior' ),
					'search_items'  => __( 'Sök rum', 'nordicinterior' ),
					'not_found'     => __( 'Inga rum hittades.', 'nordicinterior' ),
				),
				'rewrite'      => array(
					'slug'       => _x( 'rum', 'URL slug', 'nordicinterior' ),
					'with_front' => false,
				),
			)
		)
	);

	register_taxonomy(
		'ni_style',
		array( 'product', 'post', 'ni_campaign' ),
		array_merge(
			$common,
			array(
				'hierarchical' => true,
				'labels'       => array(
					'name'          => __( 'Stilar', 'nordicinterior' ),
					'singular_name' => __( 'Stil', 'nordicinterior' ),
					'menu_name'     => __( 'Stilar', 'nordicinterior' ),
					'all_items'     => __( 'Alla stilar', 'nordicinterior' ),
					'edit_item'     => __( 'Redigera stil', 'nordicinterior' ),
					'add_new_item'  => __( 'Lägg till stil', 'nordicinterior' ),
					'search_items'  => __( 'Sök stilar', 'nordicinterior' ),
					'not_found'     => __( 'Inga stilar hittades.', 'nordicinterior' ),
				),
				'rewrite'      => array(
					'slug'       => _x( 'stil', 'URL slug', 'nordicinterior' ),
					'with_front' => false,
				),
			)
		)
	);

	register_taxonomy(
		'ni_store',
		array( 'product', 'ni_campaign' ),
		array_merge(
			$common,
			array(
				'hierarchical' => true,
				'labels'       => array(
					'name'          => __( 'Butiker', 'nordicinterior' ),
					'singular_name' => __( 'Butik', 'nordicinterior' ),
					'menu_name'     => __( 'Butiker', 'nordicinterior' ),
					'all_items'     => __( 'Alla butiker', 'nordicinterior' ),
					'edit_item'     => __( 'Redigera butik', 'nordicinterior' ),
					'add_new_item'  => __( 'Lägg till butik', 'nordicinterior' ),
					'search_items'  => __( 'Sök butiker', 'nordicinterior' ),
					'not_found'     => __( 'Inga butiker hittades.', 'nordicinterior' ),
				),
				'rewrite'      => array(
					'slug'       => _x( 'butik', 'URL slug', 'nordicinterior' ),
					'with_front' => false,
				),
			)
		)
	);

	// Brands are WooCommerce's own product_brand. Make them usable on posts.
	if ( taxonomy_exists( 'product_brand' ) ) {
		register_taxonomy_for_object_type( 'product_brand', 'post' );
		register_taxonomy_for_object_type( 'product_brand', 'ni_campaign' );
	}
}
add_action( 'init', 'ni_register_taxonomies', 5 );

/**
 * The product_brand taxonomy is registered by WooCommerce on init (priority 10 or later);
 * attach it to posts afterwards.
 */
function ni_attach_brands_late() {
	if ( taxonomy_exists( 'product_brand' ) ) {
		register_taxonomy_for_object_type( 'product_brand', 'post' );
		register_taxonomy_for_object_type( 'product_brand', 'ni_campaign' );
	}
}
add_action( 'init', 'ni_attach_brands_late', 99 );

/**
 * Field definitions per taxonomy.
 *
 * @return array<string, array<string, array{type:string,label:string,desc?:string}>>
 */
function ni_term_fields() {
	$months        = array(
		'type'  => 'months',
		'label' => __( 'Säsongsmånader', 'nordicinterior' ),
		'desc'  => __( 'Rummet lyfts på startsidan under dessa månader när källan är ”Säsong”.', 'nordicinterior' ),
	);
	static $fields = null;
	if ( null !== $fields ) {
		return $fields;
	}
	$fields = array(
		'ni_room'       => array(
			'image'   => array(
				'type'  => 'image',
				'label' => __( 'Stämningsbild', 'nordicinterior' ),
			),
			'tone'    => array(
				'type'  => 'color',
				'label' => __( 'Färgton', 'nordicinterior' ),
				'desc'  => __( 'Bakgrundston för kort och sidhuvud.', 'nordicinterior' ),
			),
			'popular' => array(
				'type'  => 'checkbox',
				'label' => __( 'Populärt just nu', 'nordicinterior' ),
			),
			'order'   => array(
				'type'  => 'number',
				'label' => __( 'Ordning', 'nordicinterior' ),
			),
			'months'  => $months,
		),
		'ni_style'      => array(
			'image' => array(
				'type'  => 'image',
				'label' => __( 'Stämningsbild', 'nordicinterior' ),
			),
			'lead'  => array(
				'type'  => 'textarea',
				'label' => __( 'Kort beskrivning', 'nordicinterior' ),
				'desc'  => __( 'Visas på kort och i sidhuvudet.', 'nordicinterior' ),
			),
			'order' => array(
				'type'  => 'number',
				'label' => __( 'Ordning', 'nordicinterior' ),
			),
		),
		'product_brand' => array(
			'logo'     => array(
				'type'  => 'image',
				'label' => __( 'Logotyp', 'nordicinterior' ),
			),
			'featured' => array(
				'type'  => 'checkbox',
				'label' => __( 'Utvalt varumärke', 'nordicinterior' ),
			),
			'website'  => array(
				'type'  => 'url',
				'label' => __( 'Webbplats', 'nordicinterior' ),
			),
			'country'  => array(
				'type'  => 'text',
				'label' => __( 'Ursprungsland', 'nordicinterior' ),
			),
			'order'    => array(
				'type'  => 'number',
				'label' => __( 'Ordning', 'nordicinterior' ),
			),
		),
		'ni_store'      => array(
			'logo'     => array(
				'type'  => 'image',
				'label' => __( 'Logotyp', 'nordicinterior' ),
			),
			'website'  => array(
				'type'  => 'url',
				'label' => __( 'Webbplats', 'nordicinterior' ),
			),
			'shipping' => array(
				'type'  => 'text',
				'label' => __( 'Fraktinfo', 'nordicinterior' ),
				'desc'  => __( 'T.ex. ”Fri frakt över 499 kr”.', 'nordicinterior' ),
			),
			'returns'  => array(
				'type'  => 'text',
				'label' => __( 'Returrätt', 'nordicinterior' ),
				'desc'  => __( 'T.ex. ”30 dagars öppet köp”.', 'nordicinterior' ),
			),
			'delivery' => array(
				'type'  => 'text',
				'label' => __( 'Leveranstid', 'nordicinterior' ),
				'desc'  => __( 'T.ex. ”2–5 arbetsdagar”.', 'nordicinterior' ),
			),
			'order'    => array(
				'type'  => 'number',
				'label' => __( 'Ordning', 'nordicinterior' ),
			),
		),
		'product_cat'   => array(
			'tone' => array(
				'type'  => 'color',
				'label' => __( 'Färgton', 'nordicinterior' ),
			),
		),
	);

	/**
	 * Filter term meta field definitions.
	 *
	 * @param array $fields Fields per taxonomy.
	 */
	$fields = apply_filters( 'ni_term_fields', $fields );
	return $fields;
}

/**
 * Hook the term meta UI for each taxonomy.
 */
function ni_term_meta_hooks() {
	foreach ( array_keys( ni_term_fields() ) as $taxonomy ) {
		add_action( "{$taxonomy}_add_form_fields", 'ni_term_add_fields' );
		add_action( "{$taxonomy}_edit_form_fields", 'ni_term_edit_fields', 10, 2 );
		add_action( "created_{$taxonomy}", 'ni_save_term_fields' );
		add_action( "edited_{$taxonomy}", 'ni_save_term_fields' );
	}
}
add_action( 'init', 'ni_term_meta_hooks', 100 );

/**
 * Render one field control.
 *
 * @param string $key   Field key.
 * @param array  $field Definition.
 * @param mixed  $value Current value.
 */
function ni_term_field_control( $key, $field, $value ) {
	$name = 'ni_term[' . $key . ']';
	$id   = 'ni-term-' . $key;
	switch ( $field['type'] ) {
		case 'image':
			$img = $value ? wp_get_attachment_image( (int) $value, 'thumbnail' ) : '';
			printf(
				'<div class="ni-media" data-ni-media><input type="hidden" name="%1$s" id="%2$s" value="%3$s"><div class="ni-media__preview">%4$s</div><button type="button" class="button" data-ni-media-choose>%5$s</button> <button type="button" class="button-link" data-ni-media-remove>%6$s</button></div>',
				esc_attr( $name ),
				esc_attr( $id ),
				esc_attr( (string) $value ),
				wp_kses_post( $img ),
				esc_html__( 'Välj bild', 'nordicinterior' ),
				esc_html__( 'Ta bort', 'nordicinterior' )
			);
			break;
		case 'checkbox':
			printf( '<input type="checkbox" name="%1$s" id="%2$s" value="1" %3$s>', esc_attr( $name ), esc_attr( $id ), checked( (bool) $value, true, false ) );
			break;
		case 'textarea':
			printf( '<textarea name="%1$s" id="%2$s" rows="3" class="large-text">%3$s</textarea>', esc_attr( $name ), esc_attr( $id ), esc_textarea( (string) $value ) );
			break;
		case 'color':
			printf( '<input type="color" name="%1$s" id="%2$s" value="%3$s">', esc_attr( $name ), esc_attr( $id ), esc_attr( $value ? (string) $value : '#EAE1D2' ) );
			break;
		case 'number':
			printf( '<input type="number" name="%1$s" id="%2$s" value="%3$s" class="small-text" step="1">', esc_attr( $name ), esc_attr( $id ), esc_attr( (string) (int) $value ) );
			break;
		case 'months':
			$selected = array_map( 'intval', (array) $value );
			echo '<fieldset class="ni-months">';
			for ( $m = 1; $m <= 12; $m++ ) {
				printf(
					'<label><input type="checkbox" name="%1$s[]" value="%2$d" %3$s> %4$s</label> ',
					esc_attr( $name ),
					(int) $m,
					checked( in_array( $m, $selected, true ), true, false ),
					esc_html( date_i18n( 'M', mktime( 0, 0, 0, $m, 1 ) ) )
				);
			}
			echo '</fieldset>';
			break;
		case 'url':
			printf( '<input type="url" name="%1$s" id="%2$s" value="%3$s" class="regular-text">', esc_attr( $name ), esc_attr( $id ), esc_attr( (string) $value ) );
			break;
		default:
			printf( '<input type="text" name="%1$s" id="%2$s" value="%3$s" class="regular-text">', esc_attr( $name ), esc_attr( $id ), esc_attr( (string) $value ) );
	}
	if ( ! empty( $field['desc'] ) ) {
		printf( '<p class="description">%s</p>', esc_html( $field['desc'] ) );
	}
}

/**
 * Fields on the "Add term" form.
 *
 * @param string $taxonomy Taxonomy.
 */
function ni_term_add_fields( $taxonomy ) {
	$fields = ni_term_fields()[ $taxonomy ] ?? array();
	wp_nonce_field( 'ni_term_meta', 'ni_term_nonce' );
	foreach ( $fields as $key => $field ) {
		echo '<div class="form-field">';
		printf( '<label for="%s">%s</label>', esc_attr( 'ni-term-' . $key ), esc_html( $field['label'] ) );
		ni_term_field_control( $key, $field, '' );
		echo '</div>';
	}
}

/**
 * Fields on the "Edit term" form.
 *
 * @param WP_Term $term     Term.
 * @param string  $taxonomy Taxonomy.
 */
function ni_term_edit_fields( $term, $taxonomy ) {
	$fields = ni_term_fields()[ $taxonomy ] ?? array();
	wp_nonce_field( 'ni_term_meta', 'ni_term_nonce' );
	foreach ( $fields as $key => $field ) {
		echo '<tr class="form-field">';
		printf( '<th scope="row"><label for="%s">%s</label></th><td>', esc_attr( 'ni-term-' . $key ), esc_html( $field['label'] ) );
		ni_term_field_control( $key, $field, get_term_meta( $term->term_id, 'ni_' . $key, true ) );
		echo '</td></tr>';
	}
}

/**
 * Save term meta.
 *
 * @param int $term_id Term ID.
 */
function ni_save_term_fields( $term_id ) {
	if ( ! isset( $_POST['ni_term_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ni_term_nonce'] ) ), 'ni_term_meta' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_product_terms' ) && ! current_user_can( 'manage_categories' ) ) {
		return;
	}
	$term = get_term( $term_id );
	if ( ! $term || is_wp_error( $term ) ) {
		return;
	}
	$fields = ni_term_fields()[ $term->taxonomy ] ?? array();
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitised per field below.
	$input = isset( $_POST['ni_term'] ) && is_array( $_POST['ni_term'] ) ? wp_unslash( $_POST['ni_term'] ) : array();

	foreach ( $fields as $key => $field ) {
		$raw = $input[ $key ] ?? '';
		switch ( $field['type'] ) {
			case 'image':
			case 'number':
				$value = (int) $raw;
				break;
			case 'checkbox':
				$value = $raw ? 1 : 0;
				break;
			case 'textarea':
				$value = sanitize_textarea_field( (string) $raw );
				break;
			case 'color':
				$value = (string) sanitize_hex_color( (string) $raw );
				break;
			case 'months':
				$value = array_values( array_filter( array_map( 'absint', (array) $raw ), static fn( $m ) => $m >= 1 && $m <= 12 ) );
				break;
			case 'url':
				$value = esc_url_raw( (string) $raw );
				break;
			default:
				$value = sanitize_text_field( (string) $raw );
		}
		update_term_meta( $term_id, 'ni_' . $key, $value );
	}
}

/**
 * Register term meta for REST/WP All Import discoverability.
 */
function ni_register_term_meta() {
	foreach ( ni_term_fields() as $taxonomy => $fields ) {
		foreach ( $fields as $key => $field ) {
			register_term_meta(
				$taxonomy,
				'ni_' . $key,
				array(
					'type'         => 'months' === $field['type'] ? 'array' : ( in_array( $field['type'], array( 'image', 'number', 'checkbox' ), true ) ? 'integer' : 'string' ),
					'single'       => true,
					'show_in_rest' => 'months' === $field['type'] ? array(
						'schema' => array(
							'type'  => 'array',
							'items' => array( 'type' => 'integer' ),
						),
					) : true,
				)
			);
		}
	}
}
add_action( 'init', 'ni_register_term_meta', 101 );

/**
 * Taxonomy archives shared between products and posts should list products.
 *
 * @param WP_Query $query Query.
 */
function ni_taxonomy_archives_show_products( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->is_tax( array( 'ni_room', 'ni_style', 'ni_store', 'product_brand' ) ) ) {
		$query->set( 'post_type', 'product' );
	}
}
add_action( 'pre_get_posts', 'ni_taxonomy_archives_show_products', 5 );
