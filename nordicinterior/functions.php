<?php
/**
 * Nordic Interior – theme bootstrap.
 *
 * Loads every module in /inc. Each module is self-contained and hooks itself
 * into WordPress, so the order below only matters for the shared helpers.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

define( 'NI_VERSION', '1.0.0' );
define( 'NI_DB_VERSION', '1' );
define( 'NI_DIR', get_template_directory() );
define( 'NI_URI', get_template_directory_uri() );

$ni_modules = array(
	'defaults',
	'helpers',
	'setup',
	'enqueue',
	'taxonomies',
	'campaigns',
	'woocommerce',
	'affiliate',
	'tracking',
	'prices',
	'search',
	'filters',
	'rewrites',
	'seo',
	'shop-the-look',
	'post-meta',
	'related',
	'template-tags',
	'sections',
	'menus',
	'widgets',
	'customizer',
	'cron',
	'admin/dashboard',
	'admin/products',
	'admin/getting-started',
	'admin/demo',
);

foreach ( $ni_modules as $ni_module ) {
	require_once NI_DIR . '/inc/' . $ni_module . '.php';
}
unset( $ni_modules, $ni_module );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once NI_DIR . '/inc/class-ni-cli.php';
	WP_CLI::add_command( 'nordicinterior', 'NI_CLI' );
}
