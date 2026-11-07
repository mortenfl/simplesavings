<?php
/**
 * Indexable landing pages for room + category and style + category:
 *   /rum/{rum}/kategori/{kategori}/   e.g. "Lampor för sovrum"
 *   /stil/{stil}/kategori/{kategori}/ e.g. "Soffor i japandistil"
 *
 * Titles and intros can be written per combination under
 * Nordic Interior → Landningssidor; otherwise a calm default text is used.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Rewrite rules.
 */
function ni_landing_rewrites() {
	$room  = _x( 'rum', 'URL slug', 'nordicinterior' );
	$style = _x( 'stil', 'URL slug', 'nordicinterior' );
	$cat   = _x( 'kategori', 'URL slug', 'nordicinterior' );
	foreach ( array(
		'room'  => array( $room, 'ni_room' ),
		'style' => array( $style, 'ni_style' ),
	) as $type => $def ) {
		add_rewrite_rule( '^' . $def[0] . '/([^/]+)/' . $cat . '/(.+?)/page/([0-9]+)/?$', 'index.php?' . $def[1] . '=$matches[1]&product_cat=$matches[2]&paged=$matches[3]&ni_landing=' . $type, 'top' );
		add_rewrite_rule( '^' . $def[0] . '/([^/]+)/' . $cat . '/(.+?)/?$', 'index.php?' . $def[1] . '=$matches[1]&product_cat=$matches[2]&ni_landing=' . $type, 'top' );
	}
}
add_action( 'init', 'ni_landing_rewrites' );

/**
 * Query var.
 *
 * @param string[] $vars Vars.
 * @return string[]
 */
function ni_landing_query_vars( $vars ) {
	$vars[] = 'ni_landing';
	return $vars;
}
add_filter( 'query_vars', 'ni_landing_query_vars' );

/**
 * Flush rewrite rules once after theme activation / update.
 */
function ni_maybe_flush_rewrites() {
	if ( get_option( 'ni_rewrite_version' ) !== NI_VERSION ) {
		flush_rewrite_rules( false );
		update_option( 'ni_rewrite_version', NI_VERSION, false );
	}
}
add_action( 'init', 'ni_maybe_flush_rewrites', 99 );
add_action( 'after_switch_theme', 'flush_rewrite_rules' );

/**
 * Landing page URL.
 *
 * @param WP_Term $term Room or style term.
 * @param WP_Term $cat  Product category.
 * @return string
 */
function ni_landing_url( $term, $cat ) {
	$base = 'ni_style' === $term->taxonomy ? _x( 'stil', 'URL slug', 'nordicinterior' ) : _x( 'rum', 'URL slug', 'nordicinterior' );
	return home_url( user_trailingslashit( $base . '/' . $term->slug . '/' . _x( 'kategori', 'URL slug', 'nordicinterior' ) . '/' . $cat->slug ) );
}

/**
 * Data for the current landing page.
 *
 * @return array{type:string, term:WP_Term, cat:WP_Term, title:string, intro:string, url:string}|null
 */
function ni_landing() {
	static $landing = false;
	if ( false !== $landing ) {
		return $landing;
	}
	$landing = null;
	$type    = get_query_var( 'ni_landing' );
	if ( ! $type ) {
		return null;
	}
	$tax  = 'style' === $type ? 'ni_style' : 'ni_room';
	$term = get_term_by( 'slug', sanitize_title( (string) get_query_var( $tax ) ), $tax );
	$cat  = get_term_by( 'slug', sanitize_title( basename( (string) get_query_var( 'product_cat' ) ) ), 'product_cat' );
	if ( ! $term || ! $cat ) {
		return null;
	}
	$custom = ni_landing_texts()[ $tax . ':' . $term->slug . '|' . $cat->slug ] ?? array();

	if ( 'ni_style' === $tax ) {
		/* translators: 1: category name, 2: style name in lowercase. */
		$title = sprintf( __( '%1$s i %2$s stil', 'nordicinterior' ), $cat->name, mb_strtolower( $term->name ) );
		/* translators: 1: category name in lowercase, 2: style name. */
		$intro = sprintf( __( 'Utvalda %1$s i %2$s stil från Nordens bästa butiker. Jämför pris, material och mått – och klicka vidare till butiken när du hittat rätt.', 'nordicinterior' ), mb_strtolower( $cat->name ), mb_strtolower( $term->name ) );
	} else {
		/* translators: 1: category name, 2: room name in lowercase. */
		$title = sprintf( __( '%1$s för %2$s', 'nordicinterior' ), $cat->name, mb_strtolower( $term->name ) );
		/* translators: 1: category name in lowercase, 2: room name in lowercase. */
		$intro = sprintf( __( '%1$s som passar i %2$s – handplockade från Nordens bästa butiker. Filtrera på stil, material, färg och mått.', 'nordicinterior' ), ucfirst( mb_strtolower( $cat->name ) ), mb_strtolower( $term->name ) );
	}

	$landing = array(
		'type'  => $type,
		'term'  => $term,
		'cat'   => $cat,
		'title' => ! empty( $custom['title'] ) ? $custom['title'] : $title,
		'intro' => ! empty( $custom['intro'] ) ? $custom['intro'] : $intro,
		'url'   => ni_landing_url( $term, $cat ),
	);
	return $landing;
}

/**
 * Editor-written landing texts.
 *
 * @return array<string, array{title:string, intro:string}>
 */
function ni_landing_texts() {
	$texts = get_option( 'ni_landing_texts', array() );
	return is_array( $texts ) ? $texts : array();
}

/**
 * Document title for landing pages.
 *
 * @param array $parts Title parts.
 * @return array
 */
function ni_landing_title( $parts ) {
	$landing = ni_landing();
	if ( $landing ) {
		$parts['title'] = $landing['title'];
	}
	return $parts;
}
add_filter( 'document_title_parts', 'ni_landing_title', 20 );

/**
 * SEO plugins: title, description and canonical for landing pages.
 *
 * @param string $value Value.
 * @return string
 */
function ni_landing_seo_title( $value ) {
	$landing = ni_landing();
	return $landing ? $landing['title'] . ' – ' . get_bloginfo( 'name' ) : $value;
}
add_filter( 'wpseo_title', 'ni_landing_seo_title', 20 );
add_filter( 'rank_math/frontend/title', 'ni_landing_seo_title', 20 );

/**
 * Description.
 *
 * @param string $value Value.
 * @return string
 */
function ni_landing_seo_description( $value ) {
	$landing = ni_landing();
	return $landing ? wp_strip_all_tags( $landing['intro'] ) : $value;
}
add_filter( 'wpseo_metadesc', 'ni_landing_seo_description', 20 );
add_filter( 'rank_math/frontend/description', 'ni_landing_seo_description', 20 );

/**
 * Canonical.
 *
 * @param string $value Value.
 * @return string
 */
function ni_landing_seo_canonical( $value ) {
	$landing = ni_landing();
	if ( ! $landing ) {
		return $value;
	}
	$paged = max( 1, (int) get_query_var( 'paged' ) );
	return $paged > 1 ? trailingslashit( $landing['url'] ) . user_trailingslashit( 'page/' . $paged ) : $landing['url'];
}
add_filter( 'wpseo_canonical', 'ni_landing_seo_canonical', 20 );
add_filter( 'rank_math/frontend/canonical', 'ni_landing_seo_canonical', 20 );

/**
 * Core canonical (when no SEO plugin is active).
 */
function ni_landing_core_canonical() {
	$landing = ni_landing();
	if ( $landing && ! defined( 'WPSEO_VERSION' ) && ! class_exists( 'RankMath' ) ) {
		printf( '<link rel="canonical" href="%s">' . "\n", esc_url( ni_landing_seo_canonical( '' ) ) );
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( wp_strip_all_tags( $landing['intro'] ) ) );
	}
}
add_action( 'wp_head', 'ni_landing_core_canonical', 3 );

/**
 * Combinations that have products (cached). Used for internal linking and
 * the sitemap.
 *
 * @param WP_Term $term Room or style.
 * @param int     $limit Max categories.
 * @return WP_Term[] Categories.
 */
function ni_landing_categories_for( $term, $limit = 8 ) {
	$key = 'ni_lc_' . $term->term_id . '_' . $limit . '_' . ni_cache_version();
	$ids = get_transient( $key );
	if ( false === $ids ) {
		$product_ids = ni_query_product_ids(
			array(
				'posts_per_page' => 2000, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page -- IDs only, cached for a day.
				'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Cached.
					array(
						'taxonomy' => $term->taxonomy,
						'terms'    => array( $term->term_id ),
					),
				),
			)
		);
		$counts      = array();
		if ( $product_ids ) {
			$terms = wp_get_object_terms( $product_ids, 'product_cat', array( 'fields' => 'all_with_object_id' ) );
			if ( ! is_wp_error( $terms ) ) {
				foreach ( $terms as $t ) {
					// Only second-level categories (e.g. Soffor, Taklampor) make good landings.
					if ( 0 === (int) $t->parent ) {
						continue;
					}
					$counts[ $t->term_id ] = ( $counts[ $t->term_id ] ?? 0 ) + 1;
				}
			}
		}
		arsort( $counts );
		$ids = array_slice( array_keys( $counts ), 0, $limit );
		set_transient( $key, $ids, DAY_IN_SECONDS );
	}
	if ( ! $ids ) {
		return array();
	}
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'include'    => $ids,
			'orderby'    => 'include',
			'hide_empty' => false,
		)
	);
	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Add landing pages to the core XML sitemap.
 */
function ni_register_landing_sitemap() {
	if ( ! class_exists( 'WP_Sitemaps_Provider' ) ) {
		return;
	}
	require_once NI_DIR . '/inc/class-ni-landing-sitemap.php';
	wp_register_sitemap_provider( 'nilandings', new NI_Landing_Sitemap() );
}
add_action( 'init', 'ni_register_landing_sitemap', 20 );
