<?php
/**
 * Smart search: words that match a room, style, brand, material, colour,
 * designer or category become filters; editable synonyms; Swedish stemming
 * as fallback; SKU matching; "Menade du …?"; anonymous search log; live
 * search REST endpoint used by the ARIA combobox (search.js).
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Taxonomies that become filters when a search word matches a term.
 *
 * @return string[]
 */
function ni_search_filter_taxonomies() {
	$taxes = array( 'ni_room', 'ni_style', 'product_brand', 'ni_store', 'product_cat', 'pa_material', 'pa_farg', 'pa_designer' );
	return array_values( array_filter( $taxes, 'taxonomy_exists' ) );
}

/**
 * Very small Swedish stemmer (suffix stripping, Snowball-inspired).
 * Used as a fallback so "soffor", "soffan" and "soffa" meet at "soff".
 *
 * @param string $word Lowercase word.
 * @return string
 */
function ni_stem( $word ) {
	$word = (string) $word;
	if ( mb_strlen( $word ) <= 4 ) {
		return $word;
	}
	$suffixes = array( 'heterna', 'hetens', 'arnas', 'ernas', 'ornas', 'andet', 'andes', 'anden', 'heten', 'heter', 'arens', 'erna', 'arna', 'orna', 'ande', 'aren', 'arne', 'aste', 'ens', 'ern', 'het', 'are', 'ade', 'ast', 'or', 'ar', 'er', 'en', 'et', 'es', 'na', 'at', 'a', 'e', 's' );
	foreach ( $suffixes as $suffix ) {
		$len = mb_strlen( $suffix );
		if ( mb_substr( $word, -$len ) === $suffix && mb_strlen( $word ) - $len >= 3 ) {
			return mb_substr( $word, 0, -$len );
		}
	}
	return $word;
}

/**
 * Synonym groups from Theme Adjustments → Sök.
 *
 * @return array<string, string[]> word => all words in its group.
 */
function ni_synonyms() {
	static $map = null;
	if ( null !== $map ) {
		return $map;
	}
	$map = array();
	foreach ( ni_lines( (string) ni_mod( 'synonyms' ) ) as $line ) {
		$group = array_values( array_filter( array_map( 'ni_normalize', explode( ',', $line ) ) ) );
		if ( count( $group ) < 2 ) {
			continue;
		}
		foreach ( $group as $word ) {
			$map[ $word ] = array_values( array_unique( array_merge( $map[ $word ] ?? array(), $group ) ) );
		}
	}
	return $map;
}

/**
 * Vocabulary of filterable terms, keyed by normalised name/slug and stem.
 *
 * @return array<string, array<int, array{tax:string, id:int, name:string}>>
 */
function ni_search_vocab() {
	static $vocab = null;
	if ( null !== $vocab ) {
		return $vocab;
	}
	$vocab = get_transient( 'ni_search_vocab' );
	if ( is_array( $vocab ) ) {
		return $vocab;
	}
	$vocab = array();
	foreach ( ni_search_filter_taxonomies() as $tax ) {
		$terms = get_terms(
			array(
				'taxonomy'   => $tax,
				'hide_empty' => false,
			)
		);
		if ( is_wp_error( $terms ) ) {
			continue;
		}
		foreach ( $terms as $term ) {
			$entry = array(
				'tax'  => $tax,
				'id'   => (int) $term->term_id,
				'name' => $term->name,
			);
			$name  = ni_normalize( $term->name );
			$keys  = array_unique( array( $name, ni_normalize( str_replace( '-', ' ', $term->slug ) ), 'stem:' . ni_stem( $name ) ) );
			foreach ( $keys as $key ) {
				if ( '' === $key || 'stem:' === $key ) {
					continue;
				}
				$vocab[ $key ][] = $entry;
			}
		}
	}
	set_transient( 'ni_search_vocab', $vocab, DAY_IN_SECONDS );
	return $vocab;
}

/**
 * Bust the vocabulary when terms change.
 */
function ni_search_vocab_flush() {
	delete_transient( 'ni_search_vocab' );
}
add_action( 'created_term', 'ni_search_vocab_flush' );
add_action( 'edited_term', 'ni_search_vocab_flush' );
add_action( 'delete_term', 'ni_search_vocab_flush' );
add_action( 'customize_save_after', 'ni_search_vocab_flush' );

/**
 * Parse a search query into filters and remaining free text.
 *
 * "soffa bouclé vardagsrum" →
 *   filters: product_cat=Soffor, pa_material=Bouclé, ni_room=Vardagsrum; text: ''.
 *
 * @param string $query Raw query.
 * @return array{filters:array<string,int[]>, labels:array<int,array{tax:string,id:int,name:string}>, text:string, words:string[], sku:int}
 */
function ni_parse_search( $query ) {
	$normalized = ni_normalize( $query );
	$result     = array(
		'filters' => array(),
		'labels'  => array(),
		'text'    => '',
		'words'   => array(),
		'sku'     => 0,
	);
	if ( '' === $normalized ) {
		return $result;
	}

	// Exact SKU match wins.
	if ( function_exists( 'wc_get_product_id_by_sku' ) ) {
		$sku_id = (int) wc_get_product_id_by_sku( trim( (string) $query ) );
		if ( $sku_id ) {
			$result['sku'] = $sku_id;
			return $result;
		}
	}

	$vocab    = ni_search_vocab();
	$synonyms = ni_synonyms();
	$words    = explode( ' ', $normalized );
	$rest     = array();
	$count    = count( $words );
	$i        = 0;

	while ( $i < $count ) {
		$matched = false;
		// Try 3-, 2- and 1-word phrases ("kök & servering", "modern klassisk").
		for ( $n = min( 3, $count - $i ); $n >= 1; $n-- ) {
			$phrase     = implode( ' ', array_slice( $words, $i, $n ) );
			$candidates = array( $phrase );
			if ( 1 === $n && isset( $synonyms[ $phrase ] ) ) {
				$candidates = array_merge( $candidates, $synonyms[ $phrase ] );
			}
			$entries = array();
			foreach ( $candidates as $candidate ) {
				if ( isset( $vocab[ $candidate ] ) ) {
					$entries = $vocab[ $candidate ];
					break;
				}
			}
			if ( ! $entries && 1 === $n && mb_strlen( $phrase ) > 3 ) {
				$stem = 'stem:' . ni_stem( $phrase );
				if ( isset( $vocab[ $stem ] ) ) {
					$entries = $vocab[ $stem ];
				}
			}
			if ( $entries ) {
				// Prefer the most specific taxonomy order defined in ni_search_filter_taxonomies().
				$entry                                = $entries[0];
				$result['filters'][ $entry['tax'] ][] = $entry['id'];
				$result['labels'][]                   = $entry;
				$i                                   += $n;
				$matched                              = true;
				break;
			}
		}
		if ( ! $matched ) {
			$rest[] = $words[ $i ];
			++$i;
		}
	}

	foreach ( $result['filters'] as $tax => $ids ) {
		$result['filters'][ $tax ] = array_values( array_unique( $ids ) );
	}
	$result['words'] = $rest;
	$result['text']  = implode( ' ', $rest );
	return $result;
}

/**
 * Build a SQL search clause for free-text words with synonyms and stems.
 *
 * Every word must match (AND); each word may match any of its variants (OR)
 * in title, excerpt, content or SKU.
 *
 * @param string[] $words Words.
 * @return string SQL starting with " AND ", or ''.
 */
function ni_search_sql( $words ) {
	global $wpdb;
	if ( ! $words ) {
		return '';
	}
	$synonyms = ni_synonyms();
	$groups   = array();
	foreach ( $words as $word ) {
		$variants = array_unique( array_merge( array( $word ), $synonyms[ $word ] ?? array(), mb_strlen( $word ) > 4 ? array( ni_stem( $word ) ) : array() ) );
		$ors      = array();
		foreach ( $variants as $variant ) {
			$like  = '%' . $wpdb->esc_like( $variant ) . '%';
			$ors[] = $wpdb->prepare(
				"({$wpdb->posts}.post_title LIKE %s OR {$wpdb->posts}.post_excerpt LIKE %s OR {$wpdb->posts}.post_content LIKE %s OR {$wpdb->posts}.ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_sku' AND meta_value LIKE %s))",
				$like,
				$like,
				$like,
				$like
			);
		}
		$groups[] = '(' . implode( ' OR ', $ors ) . ')';
	}
	return ' AND ' . implode( ' AND ', $groups );
}

/**
 * Should our SQL replace the default search? Not when a search plugin
 * (SearchWP, Relevanssi) handles the text part.
 *
 * @return bool
 */
function ni_search_engine_is_ours() {
	$ours = ! class_exists( 'SearchWP' ) && ! class_exists( '\SearchWP\SearchWP' ) && ! function_exists( 'relevanssi_do_query' );
	/**
	 * Filter whether the theme's search SQL is used.
	 *
	 * @param bool $ours Use theme search.
	 */
	return (bool) apply_filters( 'ni_use_theme_search', $ours );
}

/**
 * Turn the main search into a filtered product search.
 *
 * @param WP_Query $query Query.
 */
function ni_search_pre_get_posts( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only search parameter.
	$type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : 'product';
	if ( 'post' === $type ) {
		$query->set( 'post_type', 'post' );
		return;
	}

	$parsed = ni_parse_search( (string) $query->get( 's' ) );
	$query->set( 'post_type', 'product' );
	$query->set( 'ni_parsed', $parsed );

	if ( $parsed['sku'] ) {
		$query->set( 'post__in', array( $parsed['sku'] ) );
		$query->set( 'ni_search_sql', '' );
		return;
	}

	$tax_query = (array) $query->get( 'tax_query' );
	foreach ( $parsed['filters'] as $tax => $ids ) {
		$tax_query[] = array(
			'taxonomy' => $tax,
			'terms'    => $ids,
		);
	}
	if ( $tax_query ) {
		$tax_query['relation'] = 'AND';
		$query->set( 'tax_query', $tax_query );
	}

	if ( ni_search_engine_is_ours() ) {
		$query->set( 'ni_search_sql', ni_search_sql( $parsed['words'] ) );
	} elseif ( $parsed['filters'] ) {
		// Let the search plugin search only the remaining words.
		$query->set( 's', $parsed['text'] );
	}
}
add_action( 'pre_get_posts', 'ni_search_pre_get_posts', 20 );

/**
 * Replace WordPress' search SQL with ours for flagged queries.
 *
 * @param string   $search SQL.
 * @param WP_Query $query  Query.
 * @return string
 */
function ni_posts_search( $search, $query ) {
	$sql = $query->get( 'ni_search_sql', null );
	if ( null === $sql ) {
		return $search;
	}
	return (string) $sql;
}
add_filter( 'posts_search', 'ni_posts_search', 20, 2 );

/**
 * Order search results by popularity when no explicit order is set and there
 * is no free text to rank by.
 *
 * @param WP_Query $query Query.
 */
function ni_search_default_order( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return;
	}
	$orderby = $query->get( 'orderby' );
	if ( is_array( $orderby ) ) {
		return; // Listing sort already applied (see filters.php).
	}
	if ( 'relevance' === $orderby || '' === (string) $orderby ) {
		$parsed = $query->get( 'ni_parsed' );
		if ( is_array( $parsed ) && ! $parsed['words'] ) {
			$query->set( 'meta_key', '_ni_clicks' );
			$query->set(
				'orderby',
				array(
					'meta_value_num' => 'DESC',
					'date'           => 'DESC',
				)
			);
		}
	}
}
add_action( 'pre_get_posts', 'ni_search_default_order', 30 );

/**
 * Log searches without personal data.
 *
 * @param string $term    Normalised term.
 * @param int    $results Number of results.
 */
function ni_log_search( $term, $results ) {
	global $wpdb;
	$term = mb_substr( ni_normalize( $term ), 0, 190 );
	if ( mb_strlen( $term ) < 2 || ni_ignore_current_visitor() ) {
		return;
	}
	$table = $wpdb->prefix . 'ni_searches';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$wpdb->query( $wpdb->prepare( "INSERT INTO {$table} (term, hits, results, last_seen) VALUES (%s, 1, %d, %s) ON DUPLICATE KEY UPDATE hits = hits + 1, results = VALUES(results), last_seen = VALUES(last_seen)", $term, absint( $results ), current_time( 'mysql' ) ) );
}

/**
 * Log the main search once the query has run.
 */
function ni_log_main_search() {
	global $wp_query;
	if ( is_search() && ! is_paged() && ! ni_has_active_filters() ) {
		ni_log_search( get_search_query( false ), (int) $wp_query->found_posts );
	}
}
add_action( 'template_redirect', 'ni_log_main_search', 50 );

/**
 * A single SKU hit goes straight to the product page.
 */
function ni_search_sku_redirect() {
	global $wp_query;
	if ( ! is_search() ) {
		return;
	}
	$parsed = $wp_query->get( 'ni_parsed' );
	if ( is_array( $parsed ) && $parsed['sku'] && 1 === (int) $wp_query->found_posts ) {
		wp_safe_redirect( get_permalink( $parsed['sku'] ), 302 );
		exit;
	}
}
add_action( 'template_redirect', 'ni_search_sku_redirect', 60 );

/**
 * Popular searches: editor list plus most frequent logged searches.
 *
 * @param int $limit Limit.
 * @return string[]
 */
function ni_popular_searches( $limit = 6 ) {
	global $wpdb;
	$list = ni_lines( (string) ni_mod( 'popular_searches' ) );
	if ( ni_mod( 'popular_auto' ) && count( $list ) < $limit ) {
		$cached = get_transient( 'ni_popular_searches' );
		if ( false === $cached ) {
			$table = $wpdb->prefix . 'ni_searches';
			// Custom table (name from $wpdb->prefix), result cached in a transient below.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$cached = (array) $wpdb->get_col( $wpdb->prepare( "SELECT term FROM {$table} WHERE results > 0 ORDER BY hits DESC LIMIT %d", 20 ) );
			set_transient( 'ni_popular_searches', $cached, 6 * HOUR_IN_SECONDS );
		}
		foreach ( $cached as $term ) {
			if ( ! in_array( mb_strtolower( $term ), array_map( 'mb_strtolower', $list ), true ) ) {
				$list[] = $term;
			}
		}
	}
	return array_slice( $list, 0, $limit );
}

/**
 * Searches with no results (admin).
 *
 * @param int $limit Limit.
 * @return array
 */
function ni_zero_result_searches( $limit = 20 ) {
	global $wpdb;
	$table = $wpdb->prefix . 'ni_searches';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	return (array) $wpdb->get_results( $wpdb->prepare( "SELECT term, hits, last_seen FROM {$table} WHERE results = 0 ORDER BY hits DESC, last_seen DESC LIMIT %d", absint( $limit ) ) );
}

/**
 * Top searches (admin).
 *
 * @param int $limit Limit.
 * @return array
 */
function ni_top_searches( $limit = 20 ) {
	global $wpdb;
	$table = $wpdb->prefix . 'ni_searches';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	return (array) $wpdb->get_results( $wpdb->prepare( "SELECT term, hits, results, last_seen FROM {$table} ORDER BY hits DESC LIMIT %d", absint( $limit ) ) );
}

/**
 * "Menade du …?" – closest known word for each unmatched word.
 *
 * @param string $query Query.
 * @return string Suggested query or ''.
 */
function ni_did_you_mean( $query ) {
	$parsed = ni_parse_search( $query );
	if ( ! $parsed['words'] ) {
		return '';
	}
	$dictionary = array();
	foreach ( array_keys( ni_search_vocab() ) as $key ) {
		if ( 0 !== strpos( $key, 'stem:' ) ) {
			$dictionary[] = $key;
		}
	}
	$dictionary = array_merge( $dictionary, array_keys( ni_synonyms() ), array_map( 'ni_normalize', ni_popular_searches( 30 ) ) );
	$dictionary = array_unique( $dictionary );

	$changed = false;
	$out     = array();
	foreach ( explode( ' ', ni_normalize( $query ) ) as $word ) {
		if ( ! in_array( $word, $parsed['words'], true ) || mb_strlen( $word ) < 4 ) {
			$out[] = $word;
			continue;
		}
		$best      = $word;
		$best_dist = 3;
		foreach ( $dictionary as $candidate ) {
			if ( abs( mb_strlen( $candidate ) - mb_strlen( $word ) ) > 2 ) {
				continue;
			}
			$dist = levenshtein( $word, $candidate );
			if ( $dist > 0 && $dist < $best_dist ) {
				$best      = $candidate;
				$best_dist = $dist;
			}
		}
		if ( $best !== $word ) {
			$changed = true;
		}
		$out[] = $best;
	}
	return $changed ? implode( ' ', $out ) : '';
}

/**
 * REST routes for live search.
 */
function ni_search_routes() {
	register_rest_route(
		'nordicinterior/v1',
		'/search',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'ni_rest_search',
			'permission_callback' => '__return_true', // Public read-only search.
			'args'                => array(
				'q' => array(
					'type'              => 'string',
					'required'          => true,
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'ni_search_routes' );

/**
 * Live search response grouped by type.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function ni_rest_search( WP_REST_Request $request ) {
	$q = mb_substr( trim( (string) $request->get_param( 'q' ) ), 0, 100 );
	if ( mb_strlen( $q ) < 2 ) {
		return new WP_REST_Response( array( 'groups' => array() ) );
	}

	$cache_key = 'ni_ls_' . md5( mb_strtolower( $q ) . ni_cache_version() );
	$cached    = get_transient( $cache_key );
	if ( is_array( $cached ) ) {
		return new WP_REST_Response( $cached );
	}

	$enabled = array_map( 'trim', explode( ',', (string) ni_mod( 'search_groups' ) ) );
	$parsed  = ni_parse_search( $q );
	$norm    = ni_normalize( $q );
	$groups  = array();

	// Products.
	if ( in_array( 'product', $enabled, true ) ) {
		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 6,
			'no_found_rows'  => false,
		);
		if ( $parsed['sku'] ) {
			$args['post__in'] = array( $parsed['sku'] );
		} else {
			$tax_query = array();
			foreach ( $parsed['filters'] as $tax => $ids ) {
				$tax_query[] = array(
					'taxonomy' => $tax,
					'terms'    => $ids,
				);
			}
			if ( $tax_query ) {
				$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Search.
			}
			$args['ni_search_sql'] = ni_search_sql( $parsed['words'] );
			$args['meta_key']      = '_ni_clicks'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Sort by popularity.
			$args['orderby']       = array(
				'meta_value_num' => 'DESC',
				'date'           => 'DESC',
			);
		}
		$query = new WP_Query( $args );
		$items = array();
		foreach ( $query->posts as $post ) {
			$product = wc_get_product( $post );
			if ( ! $product ) {
				continue;
			}
			$brand   = ni_product_brand( $product );
			$store   = ni_product_store( $product );
			$items[] = array(
				'title' => $product->get_name(),
				'url'   => get_permalink( $post ),
				'image' => (string) wp_get_attachment_image_url( (int) $product->get_image_id(), 'thumbnail' ),
				'meta'  => trim( ( $brand ? $brand->name : '' ) . ( $store ? ' · ' . $store->name : '' ), ' ·' ),
				'price' => $product->get_price() ? ni_format_price( (float) $product->get_price() ) : '',
			);
		}
		if ( $items ) {
			$groups[] = array(
				'type'  => 'product',
				'total' => (int) $query->found_posts,
				'items' => $items,
			);
		}
	}

	// Terms: categories, rooms, styles, brands (prefix/contains match).
	foreach ( array( 'product_cat', 'ni_room', 'ni_style', 'product_brand' ) as $tax ) {
		if ( ! in_array( $tax, $enabled, true ) || ! taxonomy_exists( $tax ) ) {
			continue;
		}
		$terms = get_terms(
			array(
				'taxonomy'   => $tax,
				'hide_empty' => 'product_brand' !== $tax,
				'number'     => 4,
				'name__like' => $norm,
			)
		);
		// Also include terms detected by the parser (e.g. "vardagsrum" in "soffa vardagsrum").
		$ids = $parsed['filters'][ $tax ] ?? array();
		if ( $ids ) {
			$extra = get_terms(
				array(
					'taxonomy'   => $tax,
					'include'    => $ids,
					'hide_empty' => false,
				)
			);
			$terms = array_merge( is_wp_error( $extra ) ? array() : $extra, is_wp_error( $terms ) ? array() : $terms );
		}
		if ( is_wp_error( $terms ) || ! $terms ) {
			continue;
		}
		$items = array();
		$seen  = array();
		foreach ( $terms as $term ) {
			if ( isset( $seen[ $term->term_id ] ) ) {
				continue;
			}
			$seen[ $term->term_id ] = true;
			$parent                 = $term->parent ? get_term( $term->parent, $tax ) : null;
			$items[]                = array(
				'title' => $term->name,
				'url'   => (string) get_term_link( $term ),
				'meta'  => $parent && ! is_wp_error( $parent ) ? $parent->name : '',
				'count' => (int) $term->count,
			);
		}
		$groups[] = array(
			'type'  => $tax,
			'items' => array_slice( $items, 0, 4 ),
		);
	}

	// Articles.
	if ( in_array( 'post', $enabled, true ) ) {
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				's'              => $q,
				'posts_per_page' => 3,
				'no_found_rows'  => true,
			)
		);
		if ( $posts ) {
			$groups[] = array(
				'type'  => 'post',
				'items' => array_map(
					static function ( $post ) {
						return array(
							'title' => get_the_title( $post ),
							'url'   => get_permalink( $post ),
							'image' => (string) get_the_post_thumbnail_url( $post, 'thumbnail' ),
						);
					},
					$posts
				),
			);
		}
	}

	$response = array(
		'query'      => $q,
		'groups'     => $groups,
		'filters'    => array_map(
			static function ( $label ) {
				return $label['name'];
			},
			$parsed['labels']
		),
		'didYouMean' => $groups ? '' : ni_did_you_mean( $q ),
		'url'        => add_query_arg(
			array(
				's'         => rawurlencode( $q ),
				'post_type' => 'product',
			),
			home_url( '/' )
		),
	);

	set_transient( $cache_key, $response, 10 * MINUTE_IN_SECONDS );
	return new WP_REST_Response( $response );
}

/**
 * Search form markup (hero, search page, 404).
 *
 * @param array $args { id, size, autofocus }.
 */
function ni_search_form( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'id'      => 'ni-search',
			'size'    => 'large',
			'popular' => true,
		)
	);
	$id   = sanitize_html_class( $args['id'] );
	?>
	<form role="search" method="get" class="ni-search ni-search--<?php echo esc_attr( $args['size'] ); ?>" action="<?php echo esc_url( home_url( '/' ) ); ?>" data-ni-search>
		<label for="<?php echo esc_attr( $id ); ?>-input" class="screen-reader-text"><?php esc_html_e( 'Sök inredning', 'nordicinterior' ); ?></label>
		<div class="ni-search__field">
			<?php ni_the_icon( 'search', 20 ); ?>
			<input
				type="search"
				id="<?php echo esc_attr( $id ); ?>-input"
				class="ni-search__input"
				name="s"
				value="<?php echo esc_attr( get_search_query() ); ?>"
				placeholder="<?php echo esc_attr( (string) ni_mod( 'search_placeholder' ) ); ?>"
				autocomplete="off"
				spellcheck="false"
				role="combobox"
				aria-autocomplete="list"
				aria-expanded="false"
				aria-controls="<?php echo esc_attr( $id ); ?>-listbox"
			>
			<input type="hidden" name="post_type" value="product">
			<button type="submit" class="ni-search__submit"><?php esc_html_e( 'Sök', 'nordicinterior' ); ?></button>
		</div>
		<div class="ni-search__panel" id="<?php echo esc_attr( $id ); ?>-listbox" role="listbox" aria-label="<?php esc_attr_e( 'Sökförslag', 'nordicinterior' ); ?>" hidden></div>
		<p class="screen-reader-text" aria-live="polite" data-ni-search-status></p>
	</form>
	<?php
	if ( $args['popular'] ) {
		$popular = ni_popular_searches();
		if ( $popular ) {
			echo '<div class="ni-chips ni-chips--popular"><span class="ni-chips__label">' . esc_html__( 'Populärt:', 'nordicinterior' ) . '</span><ul class="ni-chips__list">';
			foreach ( $popular as $term ) {
				printf(
					'<li><a class="ni-chip" href="%1$s">%2$s</a></li>',
					esc_url(
						add_query_arg(
							array(
								's'         => rawurlencode( $term ),
								'post_type' => 'product',
							),
							home_url( '/' )
						)
					),
					esc_html( $term )
				);
			}
			echo '</ul></div>';
		}
	}
}
