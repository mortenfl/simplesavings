<?php
/**
 * WP-CLI commands:
 *   wp nordicinterior demo
 *   wp nordicinterior menu
 *   wp nordicinterior check-links [--all]
 *   wp nordicinterior recalc
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Nordic Interior commands.
 */
class NI_CLI {

	/**
	 * Import demo content (rooms, styles, brands, stores, 60 products, campaigns, articles, pages and menu).
	 *
	 * ## EXAMPLES
	 *
	 *     wp nordicinterior demo
	 *
	 * @param array $args       Args.
	 * @param array $assoc_args Assoc args.
	 */
	public function demo( $args, $assoc_args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- WP-CLI signature.
		ni_install_tables();
		$summary = ni_demo_import();
		if ( isset( $summary['error'] ) ) {
			WP_CLI::error( $summary['error'] );
		}
		foreach ( $summary as $key => $count ) {
			WP_CLI::log( sprintf( '%-12s %d', $key, $count ) );
		}
		WP_CLI::success( 'Demoinnehållet är importerat.' );
	}

	/**
	 * Build the primary menu and assign it to the primary and mobile locations.
	 *
	 * @param array $args       Args.
	 * @param array $assoc_args Assoc args.
	 */
	public function menu( $args, $assoc_args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- WP-CLI signature.
		$result = ni_build_primary_menu();
		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
		}
		WP_CLI::success( sprintf( 'Huvudmeny skapad (ID %d).', $result ) );
	}

	/**
	 * Check every affiliate link now (synchronously).
	 *
	 * ## OPTIONS
	 *
	 * [--limit=<number>]
	 * : Max number of products. Default: all.
	 *
	 * @subcommand check-links
	 * @param array $args       Args.
	 * @param array $assoc_args Assoc args.
	 */
	public function check_links( $args, $assoc_args ) {
		$limit    = isset( $assoc_args['limit'] ) ? absint( $assoc_args['limit'] ) : -1;
		$ids      = get_posts(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => $limit,
				'fields'         => 'ids',
			)
		);
		$stats    = array(
			'ok'      => 0,
			'broken'  => 0,
			'unknown' => 0,
		);
		$progress = \WP_CLI\Utils\make_progress_bar( 'Kontrollerar länkar', count( $ids ) );
		foreach ( $ids as $id ) {
			$status = ni_check_product_link( $id );
			++$stats[ $status ];
			if ( 'broken' === $status ) {
				WP_CLI::warning( sprintf( 'Trasig: #%d %s', $id, get_the_title( $id ) ) );
			}
			$progress->tick();
		}
		$progress->finish();
		update_option( 'ni_last_link_check', time(), false );
		WP_CLI::success( sprintf( 'OK: %d, trasiga: %d, okända: %d', $stats['ok'], $stats['broken'], $stats['unknown'] ) );
	}

	/**
	 * Recalculate _ni_clicks and _ni_discount for all products.
	 *
	 * @param array $args       Args.
	 * @param array $assoc_args Assoc args.
	 */
	public function recalc( $args, $assoc_args ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- WP-CLI signature.
		$count = ni_recalc();
		WP_CLI::success( sprintf( '%d produkter uppdaterade.', $count ) );
	}
}
