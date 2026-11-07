<?php
/**
 * Structured data (JSON-LD), Open Graph / Pinterest Rich Pins and
 * breadcrumbs data.
 *
 * When Yoast SEO or Rank Math is active they own WebSite, Organization,
 * BreadcrumbList and Article; the theme then only adds Product and ItemList,
 * and only product Open Graph tags.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Is an SEO plugin handling general schema/OG?
 *
 * @return bool
 */
function ni_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'AIOSEO_VERSION' );
}

/**
 * Breadcrumb trail for the current page.
 *
 * @return array<int, array{name:string, url:string}>
 */
function ni_breadcrumb_items() {
	$items = array(
		array(
			'name' => __( 'Hem', 'nordicinterior' ),
			'url'  => home_url( '/' ),
		),
	);

	$landing = function_exists( 'ni_landing' ) ? ni_landing() : null;
	if ( $landing ) {
		$archive = 'ni_style' === $landing['term']->taxonomy ? ni_page_url( 'page-templates/styles.php' ) : ni_page_url( 'page-templates/rooms.php' );
		$items[] = array(
			'name' => 'ni_style' === $landing['term']->taxonomy ? __( 'Stilar', 'nordicinterior' ) : __( 'Rum', 'nordicinterior' ),
			'url'  => $archive,
		);
		$items[] = array(
			'name' => $landing['term']->name,
			'url'  => (string) get_term_link( $landing['term'] ),
		);
		$items[] = array(
			'name' => $landing['title'],
			'url'  => $landing['url'],
		);
		return $items;
	}

	if ( is_tax() || is_category() || is_tag() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$parents = array(
				'ni_room'       => array( __( 'Rum', 'nordicinterior' ), ni_page_url( 'page-templates/rooms.php' ) ),
				'ni_style'      => array( __( 'Stilar', 'nordicinterior' ), ni_page_url( 'page-templates/styles.php' ) ),
				'product_brand' => array( __( 'Varumärken', 'nordicinterior' ), ni_page_url( 'page-templates/brands.php' ) ),
				'ni_store'      => array( __( 'Butiker', 'nordicinterior' ), ni_page_url( 'page-templates/stores.php' ) ),
			);
			if ( isset( $parents[ $term->taxonomy ] ) ) {
				$items[] = array(
					'name' => $parents[ $term->taxonomy ][0],
					'url'  => $parents[ $term->taxonomy ][1],
				);
			}
			foreach ( array_reverse( get_ancestors( $term->term_id, $term->taxonomy, 'taxonomy' ) ) as $ancestor_id ) {
				$ancestor = get_term( $ancestor_id, $term->taxonomy );
				if ( $ancestor && ! is_wp_error( $ancestor ) ) {
					$items[] = array(
						'name' => $ancestor->name,
						'url'  => (string) get_term_link( $ancestor ),
					);
				}
			}
			$items[] = array(
				'name' => $term->name,
				'url'  => (string) get_term_link( $term ),
			);
		}
	} elseif ( function_exists( 'is_product' ) && is_product() ) {
		$product = wc_get_product( get_queried_object_id() );
		if ( $product ) {
			$cats = get_the_terms( $product->get_id(), 'product_cat' );
			if ( $cats && ! is_wp_error( $cats ) ) {
				// Deepest category.
				usort(
					$cats,
					static function ( $a, $b ) {
						return count( get_ancestors( $b->term_id, 'product_cat' ) ) <=> count( get_ancestors( $a->term_id, 'product_cat' ) );
					}
				);
				$cat = $cats[0];
				foreach ( array_reverse( get_ancestors( $cat->term_id, 'product_cat', 'taxonomy' ) ) as $ancestor_id ) {
					$ancestor = get_term( $ancestor_id, 'product_cat' );
					if ( $ancestor && ! is_wp_error( $ancestor ) ) {
						$items[] = array(
							'name' => $ancestor->name,
							'url'  => (string) get_term_link( $ancestor ),
						);
					}
				}
				$items[] = array(
					'name' => $cat->name,
					'url'  => (string) get_term_link( $cat ),
				);
			}
			$items[] = array(
				'name' => $product->get_name(),
				'url'  => (string) get_permalink( $product->get_id() ),
			);
		}
	} elseif ( is_singular( 'post' ) ) {
		$blog    = (int) get_option( 'page_for_posts' );
		$items[] = array(
			'name' => $blog ? get_the_title( $blog ) : __( 'Inspiration', 'nordicinterior' ),
			'url'  => $blog ? (string) get_permalink( $blog ) : home_url( '/' ),
		);
		$items[] = array(
			'name' => get_the_title(),
			'url'  => (string) get_permalink(),
		);
	} elseif ( is_page() && ! is_front_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_queried_object_id() ) ) as $ancestor ) {
			$items[] = array(
				'name' => get_the_title( $ancestor ),
				'url'  => (string) get_permalink( $ancestor ),
			);
		}
		$items[] = array(
			'name' => get_the_title(),
			'url'  => (string) get_permalink(),
		);
	} elseif ( is_search() ) {
		$items[] = array(
			/* translators: %s: search query. */
			'name' => sprintf( __( 'Sök: %s', 'nordicinterior' ), get_search_query( false ) ),
			'url'  => '',
		);
	} elseif ( function_exists( 'is_shop' ) && is_shop() ) {
		$items[] = array(
			'name' => __( 'Alla produkter', 'nordicinterior' ),
			'url'  => (string) get_permalink( wc_get_page_id( 'shop' ) ),
		);
	} elseif ( is_post_type_archive( 'ni_campaign' ) ) {
		$items[] = array(
			'name' => __( 'Kampanjer', 'nordicinterior' ),
			'url'  => (string) get_post_type_archive_link( 'ni_campaign' ),
		);
	} elseif ( is_home() ) {
		$items[] = array(
			'name' => __( 'Inspiration', 'nordicinterior' ),
			'url'  => '',
		);
	}
	return $items;
}

/**
 * Print the breadcrumbs. Yoast/Rank Math breadcrumbs are used when enabled.
 */
function ni_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}
	$items = ni_breadcrumb_items();
	if ( count( $items ) < 2 ) {
		return;
	}
	echo '<nav class="ni-breadcrumbs" aria-label="' . esc_attr__( 'Brödsmulor', 'nordicinterior' ) . '"><ol>';
	$last = count( $items ) - 1;
	foreach ( $items as $i => $item ) {
		if ( $i === $last || '' === $item['url'] ) {
			echo '<li><span aria-current="page">' . esc_html( $item['name'] ) . '</span></li>';
		} else {
			echo '<li><a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['name'] ) . '</a></li>';
		}
	}
	echo '</ol></nav>';
}

/**
 * Product schema.
 *
 * @param WC_Product $product Product.
 * @return array
 */
function ni_product_schema( $product ) {
	$brand  = ni_product_brand( $product );
	$store  = ni_product_store( $product );
	$images = array();
	foreach ( array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() ) as $image_id ) {
		$url = wp_get_attachment_image_url( (int) $image_id, 'full' );
		if ( $url ) {
			$images[] = $url;
		}
	}
	$schema = array(
		'@type'       => 'Product',
		'@id'         => get_permalink( $product->get_id() ) . '#product',
		'name'        => $product->get_name(),
		'url'         => get_permalink( $product->get_id() ),
		'description' => wp_strip_all_tags( $product->get_short_description() ? $product->get_short_description() : $product->get_description() ),
		'image'       => $images,
	);
	if ( $product->get_sku() ) {
		$schema['sku'] = $product->get_sku();
	}
	if ( $brand ) {
		$schema['brand'] = array(
			'@type' => 'Brand',
			'name'  => $brand->name,
		);
	}
	$material = wp_list_pluck( ni_product_attr_terms( $product, 'pa_material' ), 'name' );
	if ( $material ) {
		$schema['material'] = implode( ', ', $material );
	}
	$color = wp_list_pluck( ni_product_attr_terms( $product, 'pa_farg' ), 'name' );
	if ( $color ) {
		$schema['color'] = implode( ', ', $color );
	}
	$dims = ni_product_dims( $product );
	foreach ( array(
		'width'  => 'width',
		'depth'  => 'depth',
		'height' => 'height',
	) as $key => $prop ) {
		if ( $dims[ $key ] > 0 ) {
			$schema[ $prop ] = array(
				'@type'    => 'QuantitativeValue',
				'value'    => $dims[ $key ],
				'unitCode' => 'CMT',
			);
		}
	}
	if ( $product->get_price() ) {
		$offer = array(
			'@type'         => 'Offer',
			'url'           => ni_product_url( $product ),
			'price'         => wc_format_decimal( $product->get_price(), wc_get_price_decimals() ),
			'priceCurrency' => get_woocommerce_currency(),
			'availability'  => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
			'itemCondition' => 'https://schema.org/NewCondition',
		);
		if ( $store ) {
			$offer['seller'] = array(
				'@type' => 'Organization',
				'name'  => $store->name,
			);
			$site            = (string) ni_term_meta( $store->term_id, 'website' );
			if ( $site ) {
				$offer['seller']['url'] = $site;
			}
		}
		if ( $product->is_on_sale() && $product->get_date_on_sale_to() ) {
			$offer['priceValidUntil'] = $product->get_date_on_sale_to()->date( 'Y-m-d' );
		}
		$schema['offers'] = $offer;
	}
	return $schema;
}

/**
 * Output JSON-LD.
 */
function ni_json_ld() {
	$graph  = array();
	$plugin = ni_seo_plugin_active();
	$logo   = NI_URI . '/assets/img/icons/logo-512.png';

	if ( ! $plugin ) {
		if ( is_front_page() ) {
			$graph[] = array(
				'@type'           => 'WebSite',
				'@id'             => home_url( '/#website' ),
				'url'             => home_url( '/' ),
				'name'            => get_bloginfo( 'name' ),
				'inLanguage'      => get_bloginfo( 'language' ),
				'potentialAction' => array(
					'@type'       => 'SearchAction',
					'target'      => array(
						'@type'       => 'EntryPoint',
						'urlTemplate' => home_url( '/?s={search_term_string}&post_type=product' ),
					),
					'query-input' => 'required name=search_term_string',
				),
			);
			$sameas  = array_values( array_filter( array( ni_mod( 'social_instagram' ), ni_mod( 'social_pinterest' ), ni_mod( 'social_facebook' ), ni_mod( 'social_tiktok' ) ) ) );
			$graph[] = array(
				'@type'  => 'Organization',
				'@id'    => home_url( '/#organization' ),
				'name'   => get_bloginfo( 'name' ),
				'url'    => home_url( '/' ),
				'logo'   => array(
					'@type' => 'ImageObject',
					'url'   => $logo,
				),
				'sameAs' => $sameas,
			);
		}

		$crumbs = ni_breadcrumb_items();
		if ( count( $crumbs ) > 1 && ! is_front_page() ) {
			$list = array();
			foreach ( $crumbs as $i => $crumb ) {
				$entry = array(
					'@type'    => 'ListItem',
					'position' => $i + 1,
					'name'     => $crumb['name'],
				);
				if ( $crumb['url'] ) {
					$entry['item'] = $crumb['url'];
				}
				$list[] = $entry;
			}
			$graph[] = array(
				'@type'           => 'BreadcrumbList',
				'itemListElement' => $list,
			);
		}

		if ( is_singular( 'post' ) ) {
			$post    = get_queried_object();
			$graph[] = array(
				'@type'            => 'Article',
				'headline'         => get_the_title( $post ),
				'image'            => get_the_post_thumbnail_url( $post, 'full' ),
				// Dates and author are in the schema but never shown on the page.
				'datePublished'    => get_the_date( 'c', $post ),
				'dateModified'     => get_the_modified_date( 'c', $post ),
				'author'           => array(
					'@type' => 'Person',
					'name'  => get_the_author_meta( 'display_name', (int) $post->post_author ),
				),
				'publisher'        => array(
					'@type' => 'Organization',
					'name'  => get_bloginfo( 'name' ),
					'logo'  => array(
						'@type' => 'ImageObject',
						'url'   => $logo,
					),
				),
				'mainEntityOfPage' => get_permalink( $post ),
			);
		}
	}

	if ( function_exists( 'is_product' ) && is_product() ) {
		$product = wc_get_product( get_queried_object_id() );
		if ( $product ) {
			$graph[] = ni_product_schema( $product );
		}
	}

	if ( function_exists( 'ni_is_listing' ) && ni_is_listing() ) {
		global $wp_query;
		$elements = array();
		foreach ( $wp_query->posts as $i => $post ) {
			$elements[] = array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'url'      => get_permalink( $post ),
				'name'     => get_the_title( $post ),
			);
		}
		if ( $elements ) {
			$graph[] = array(
				'@type'           => 'ItemList',
				'itemListElement' => $elements,
			);
		}
	}

	if ( ! $graph ) {
		return;
	}
	echo '<script type="application/ld+json">' . wp_json_encode(
		array(
			'@context' => 'https://schema.org',
			'@graph'   => $graph,
		),
		JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG
	) . '</script>' . "\n";
}
add_action( 'wp_head', 'ni_json_ld', 30 );

/**
 * Open Graph and Pinterest Rich Pin (product) meta.
 */
function ni_open_graph() {
	$plugin  = ni_seo_plugin_active();
	$product = ( function_exists( 'is_product' ) && is_product() ) ? wc_get_product( get_queried_object_id() ) : null;
	$tags    = array();

	if ( ! $plugin ) {
		$tags['og:site_name'] = get_bloginfo( 'name' );
		$tags['og:locale']    = str_replace( '-', '_', get_bloginfo( 'language' ) );
		if ( is_singular() ) {
			$tags['og:title']       = get_the_title();
			$tags['og:url']         = get_permalink();
			$tags['og:description'] = wp_strip_all_tags( get_the_excerpt() );
			$tags['og:type']        = $product ? 'product' : ( is_singular( 'post' ) ? 'article' : 'website' );
			$image                  = get_the_post_thumbnail_url( null, 'large' );
			if ( $image ) {
				$tags['og:image'] = $image;
			}
		} else {
			$tags['og:title'] = wp_get_document_title();
			$tags['og:type']  = 'website';
		}
	}

	if ( $product ) {
		$tags['og:type']                = 'product';
		$tags['product:price:amount']   = wc_format_decimal( $product->get_price(), 2 );
		$tags['product:price:currency'] = get_woocommerce_currency();
		$tags['og:price:amount']        = wc_format_decimal( $product->get_price(), 2 );
		$tags['og:price:currency']      = get_woocommerce_currency();
		$tags['product:availability']   = $product->is_in_stock() ? 'in stock' : 'out of stock';
		$tags['og:availability']        = $product->is_in_stock() ? 'instock' : 'oos';
		$brand                          = ni_product_brand( $product );
		if ( $brand ) {
			$tags['product:brand'] = $brand->name;
		}
		$color = wp_list_pluck( ni_product_attr_terms( $product, 'pa_farg' ), 'name' );
		if ( $color ) {
			$tags['product:color'] = implode( ', ', $color );
		}
		$material = wp_list_pluck( ni_product_attr_terms( $product, 'pa_material' ), 'name' );
		if ( $material ) {
			$tags['product:material'] = implode( ', ', $material );
		}
	}

	foreach ( $tags as $property => $content ) {
		if ( '' === (string) $content ) {
			continue;
		}
		printf( '<meta property="%1$s" content="%2$s">' . "\n", esc_attr( $property ), esc_attr( (string) $content ) );
	}
}
add_action( 'wp_head', 'ni_open_graph', 5 );

/**
 * Pinterest "Spara" button for an image (no third-party script).
 *
 * @param string $image       Image URL.
 * @param string $description Description.
 * @param string $url         Page URL.
 * @return string
 */
function ni_pin_button( $image, $description, $url = '' ) {
	$href = add_query_arg(
		array(
			'url'         => rawurlencode( $url ? $url : (string) get_permalink() ),
			'media'       => rawurlencode( $image ),
			'description' => rawurlencode( $description ),
		),
		'https://www.pinterest.com/pin/create/button/'
	);
	return sprintf(
		'<a class="ni-pin" href="%1$s" target="_blank" rel="noopener nofollow">%2$s<span>%3$s</span><span class="screen-reader-text"> %4$s</span></a>',
		esc_url( $href ),
		ni_icon( 'pinterest', 16 ),
		esc_html__( 'Spara', 'nordicinterior' ),
		esc_html__( 'på Pinterest (öppnas i nytt fönster)', 'nordicinterior' )
	);
}

/**
 * Add a Pinterest "Spara" button to images in inspiration posts.
 *
 * @param string $content Content.
 * @return string
 */
function ni_pin_content_images( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	return (string) preg_replace_callback(
		'#<figure([^>]*)class="([^"]*wp-block-image[^"]*)"([^>]*)>(.*?)</figure>#s',
		static function ( $m ) {
			if ( ! preg_match( '#<img[^>]+src="([^"]+)"#', $m[4], $src ) ) {
				return $m[0];
			}
			preg_match( '#alt="([^"]*)"#', $m[4], $alt );
			$button = ni_pin_button( html_entity_decode( $src[1] ), isset( $alt[1] ) ? html_entity_decode( $alt[1] ) : get_the_title() );
			return '<figure' . $m[1] . 'class="' . $m[2] . ' ni-pinnable"' . $m[3] . '>' . $m[4] . $button . '</figure>';
		},
		$content
	);
}
add_filter( 'the_content', 'ni_pin_content_images', 20 );
