<?php
/**
 * Search form (get_search_form()).
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

ni_search_form(
	array(
		'id'      => 'ni-search-' . wp_unique_id(),
		'size'    => 'small',
		'popular' => false,
	)
);
