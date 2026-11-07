<?php
/**
 * Core sitemap provider for room/style + category landing pages.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sitemap provider.
 */
class NI_Landing_Sitemap extends WP_Sitemaps_Provider {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->name        = 'nilandings';
		$this->object_type = 'nilanding';
	}

	/**
	 * URL list.
	 *
	 * @param int    $page_num       Page.
	 * @param string $object_subtype Unused.
	 * @return array
	 */
	public function get_url_list( $page_num, $object_subtype = '' ) {
		unset( $object_subtype );
		if ( $page_num > 1 ) {
			return array();
		}
		$urls = array();
		foreach ( array( 'ni_room', 'ni_style' ) as $tax ) {
			foreach ( ni_get_terms( $tax, array( 'hide_empty' => true ) ) as $term ) {
				foreach ( ni_landing_categories_for( $term, 12 ) as $cat ) {
					$urls[] = array( 'loc' => ni_landing_url( $term, $cat ) );
				}
			}
		}
		return $urls;
	}

	/**
	 * Max pages.
	 *
	 * @param string $object_subtype Unused.
	 * @return int
	 */
	public function get_max_num_pages( $object_subtype = '' ) {
		unset( $object_subtype );
		return 1;
	}
}
