<?php
/**
 * Demo content importer (admin button and `wp nordicinterior demo`).
 *
 * Creates terms, attributes, 60 external products with generated placeholder
 * images, 3 campaigns, 6 articles (2 with Shoppa looken), pages and the menu.
 * Everything created is tagged with the meta/term meta "_ni_demo".
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/demo-data.php';

/**
 * Admin-post handler.
 */
function ni_handle_import_demo() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Du har inte behörighet att göra detta.', 'nordicinterior' ) );
	}
	check_admin_referer( 'ni_import_demo' );
	ni_demo_import();
	wp_safe_redirect( add_query_arg( 'ni-notice', 'demo-ok', admin_url( 'admin.php?page=nordicinterior-start' ) ) );
	exit;
}
add_action( 'admin_post_ni_import_demo', 'ni_handle_import_demo' );

/**
 * Allocate a GD colour from hex.
 *
 * @param GdImage $img   Image.
 * @param string  $hex   Hex colour.
 * @param float   $shade -1..1 darken/lighten.
 * @return int
 */
function ni_demo_color( $img, $hex, $shade = 0.0 ) {
	$hex = ltrim( $hex, '#' );
	$rgb = array( hexdec( substr( $hex, 0, 2 ) ), hexdec( substr( $hex, 2, 2 ) ), hexdec( substr( $hex, 4, 2 ) ) );
	foreach ( $rgb as $i => $c ) {
		$rgb[ $i ] = (int) max( 0, min( 255, $shade < 0 ? $c * ( 1 + $shade ) : $c + ( 255 - $c ) * $shade ) );
	}
	return (int) imagecolorallocate( $img, $rgb[0], $rgb[1], $rgb[2] );
}

/**
 * Filled rectangle with rounded corners.
 *
 * @param GdImage $img Image.
 * @param int     $x1  X1.
 * @param int     $y1  Y1.
 * @param int     $x2  X2.
 * @param int     $y2  Y2.
 * @param int     $r   Radius.
 * @param int     $col Colour.
 */
function ni_demo_rrect( $img, $x1, $y1, $x2, $y2, $r, $col ) {
	imagefilledrectangle( $img, $x1 + $r, $y1, $x2 - $r, $y2, $col );
	imagefilledrectangle( $img, $x1, $y1 + $r, $x2, $y2 - $r, $col );
	foreach ( array( array( $x1 + $r, $y1 + $r ), array( $x2 - $r, $y1 + $r ), array( $x1 + $r, $y2 - $r ), array( $x2 - $r, $y2 - $r ) ) as $c ) {
		imagefilledellipse( $img, $c[0], $c[1], $r * 2, $r * 2, $col );
	}
}

/**
 * Draw a furniture shape centred in a box.
 *
 * @param GdImage $img   Image.
 * @param string  $shape Shape.
 * @param string  $hex   Main colour.
 * @param float   $s     Scale (1 = 800×1000 canvas).
 * @param int     $ox    X offset.
 * @param int     $oy    Y offset.
 */
function ni_demo_shape( $img, $shape, $hex, $s = 1.0, $ox = 0, $oy = 0 ) {
	$main = ni_demo_color( $img, $hex );
	$dark = ni_demo_color( $img, $hex, -0.22 );
	$lite = ni_demo_color( $img, $hex, 0.18 );
	$wood = ni_demo_color( $img, '#8B6A4A' );
	$ink  = ni_demo_color( $img, '#2A2826' );
	$p    = static function ( $v ) use ( $s ) {
		return (int) round( $v * $s );
	};
	$x    = static function ( $v ) use ( $p, $ox ) {
		return $p( $v ) + $ox;
	};
	$y    = static function ( $v ) use ( $p, $oy ) {
		return $p( $v ) + $oy;
	};
	imagesetthickness( $img, max( 2, $p( 10 ) ) );

	switch ( $shape ) {
		case 'sofa':
			ni_demo_rrect( $img, $x( 140 ), $y( 400 ), $x( 660 ), $y( 600 ), $p( 30 ), $dark );
			ni_demo_rrect( $img, $x( 110 ), $y( 530 ), $x( 690 ), $y( 700 ), $p( 26 ), $main );
			ni_demo_rrect( $img, $x( 90 ), $y( 470 ), $x( 190 ), $y( 700 ), $p( 40 ), $lite );
			ni_demo_rrect( $img, $x( 610 ), $y( 470 ), $x( 710 ), $y( 700 ), $p( 40 ), $lite );
			imageline( $img, $x( 400 ), $y( 545 ), $x( 400 ), $y( 640 ), $dark );
			imagefilledrectangle( $img, $x( 140 ), $y( 700 ), $x( 158 ), $y( 750 ), $wood );
			imagefilledrectangle( $img, $x( 642 ), $y( 700 ), $x( 660 ), $y( 750 ), $wood );
			break;
		case 'armchair':
			ni_demo_rrect( $img, $x( 250 ), $y( 330 ), $x( 550 ), $y( 600 ), $p( 70 ), $dark );
			ni_demo_rrect( $img, $x( 220 ), $y( 540 ), $x( 580 ), $y( 700 ), $p( 40 ), $main );
			ni_demo_rrect( $img, $x( 200 ), $y( 480 ), $x( 280 ), $y( 700 ), $p( 36 ), $lite );
			ni_demo_rrect( $img, $x( 520 ), $y( 480 ), $x( 600 ), $y( 700 ), $p( 36 ), $lite );
			imageline( $img, $x( 240 ), $y( 700 ), $x( 225 ), $y( 780 ), $wood );
			imageline( $img, $x( 560 ), $y( 700 ), $x( 575 ), $y( 780 ), $wood );
			break;
		case 'chair':
			imageline( $img, $x( 300 ), $y( 300 ), $x( 330 ), $y( 600 ), $main );
			imageline( $img, $x( 420 ), $y( 300 ), $x( 440 ), $y( 600 ), $main );
			imagefilledrectangle( $img, $x( 300 ), $y( 300 ), $x( 425 ), $y( 330 ), $main );
			ni_demo_rrect( $img, $x( 300 ), $y( 580 ), $x( 520 ), $y( 615 ), $p( 10 ), $main );
			imageline( $img, $x( 325 ), $y( 615 ), $x( 310 ), $y( 800 ), $dark );
			imageline( $img, $x( 505 ), $y( 615 ), $x( 520 ), $y( 800 ), $dark );
			imageline( $img, $x( 440 ), $y( 615 ), $x( 440 ), $y( 790 ), $dark );
			break;
		case 'coffeetable':
			imagefilledellipse( $img, $x( 400 ), $y( 560 ), $p( 520 ), $p( 150 ), $main );
			imagefilledellipse( $img, $x( 400 ), $y( 575 ), $p( 520 ), $p( 150 ), $dark );
			imagefilledellipse( $img, $x( 400 ), $y( 560 ), $p( 520 ), $p( 150 ), $main );
			imageline( $img, $x( 230 ), $y( 600 ), $x( 220 ), $y( 760 ), $wood );
			imageline( $img, $x( 570 ), $y( 600 ), $x( 580 ), $y( 760 ), $wood );
			imageline( $img, $x( 400 ), $y( 625 ), $x( 400 ), $y( 790 ), $wood );
			break;
		case 'table':
			imagefilledrectangle( $img, $x( 110 ), $y( 480 ), $x( 690 ), $y( 515 ), $main );
			imagefilledrectangle( $img, $x( 110 ), $y( 515 ), $x( 690 ), $y( 525 ), $dark );
			imagefilledrectangle( $img, $x( 150 ), $y( 525 ), $x( 172 ), $y( 790 ), $dark );
			imagefilledrectangle( $img, $x( 628 ), $y( 525 ), $x( 650 ), $y( 790 ), $dark );
			break;
		case 'bed':
			ni_demo_rrect( $img, $x( 140 ), $y( 330 ), $x( 660 ), $y( 600 ), $p( 20 ), $dark );
			ni_demo_rrect( $img, $x( 100 ), $y( 540 ), $x( 700 ), $y( 690 ), $p( 16 ), ni_demo_color( $img, '#F2EEE6' ) );
			ni_demo_rrect( $img, $x( 100 ), $y( 590 ), $x( 700 ), $y( 700 ), $p( 16 ), $main );
			ni_demo_rrect( $img, $x( 190 ), $y( 500 ), $x( 380 ), $y( 560 ), $p( 24 ), $lite );
			ni_demo_rrect( $img, $x( 420 ), $y( 500 ), $x( 610 ), $y( 560 ), $p( 24 ), $lite );
			imagefilledrectangle( $img, $x( 120 ), $y( 700 ), $x( 140 ), $y( 750 ), $wood );
			imagefilledrectangle( $img, $x( 660 ), $y( 700 ), $x( 680 ), $y( 750 ), $wood );
			break;
		case 'headboard':
			ni_demo_rrect( $img, $x( 140 ), $y( 280 ), $x( 660 ), $y( 640 ), $p( 60 ), $main );
			for ( $i = 1; $i < 6; $i++ ) {
				imageline( $img, $x( 140 + $i * 87 ), $y( 300 ), $x( 140 + $i * 87 ), $y( 620 ), $dark );
			}
			imagefilledrectangle( $img, $x( 170 ), $y( 640 ), $x( 190 ), $y( 760 ), $wood );
			imagefilledrectangle( $img, $x( 610 ), $y( 640 ), $x( 630 ), $y( 760 ), $wood );
			break;
		case 'pendant':
			imageline( $img, $x( 400 ), $y( 0 ), $x( 400 ), $y( 380 ), $ink );
			imagefilledarc( $img, $x( 400 ), $y( 600 ), $p( 440 ), $p( 440 ), 180, 360, $main, IMG_ARC_PIE );
			imagefilledrectangle( $img, $x( 180 ), $y( 595 ), $x( 620 ), $y( 610 ), $dark );
			imagefilledellipse( $img, $x( 400 ), $y( 400 ), $p( 60 ), $p( 40 ), $dark );
			break;
		case 'tablelamp':
			imagefilledpolygon( $img, array( $x( 290 ), $y( 280 ), $x( 510 ), $y( 280 ), $x( 580 ), $y( 470 ), $x( 220 ), $y( 470 ) ), $main );
			imageline( $img, $x( 400 ), $y( 470 ), $x( 400 ), $y( 720 ), $dark );
			imagefilledellipse( $img, $x( 400 ), $y( 640 ), $p( 200 ), $p( 230 ), $lite );
			imagefilledellipse( $img, $x( 400 ), $y( 760 ), $p( 230 ), $p( 50 ), $dark );
			break;
		case 'floorlamp':
			imagefilledpolygon( $img, array( $x( 320 ), $y( 140 ), $x( 480 ), $y( 140 ), $x( 540 ), $y( 300 ), $x( 260 ), $y( 300 ) ), $main );
			imageline( $img, $x( 400 ), $y( 300 ), $x( 400 ), $y( 860 ), $ink );
			imagefilledellipse( $img, $x( 400 ), $y( 870 ), $p( 220 ), $p( 44 ), $ink );
			break;
		case 'rug':
			imagefilledpolygon( $img, array( $x( 180 ), $y( 420 ), $x( 620 ), $y( 420 ), $x( 740 ), $y( 760 ), $x( 60 ), $y( 760 ) ), $main );
			imagesetthickness( $img, max( 1, $p( 6 ) ) );
			imagepolygon( $img, array( $x( 215 ), $y( 445 ), $x( 585 ), $y( 445 ), $x( 690 ), $y( 735 ), $x( 110 ), $y( 735 ) ), $dark );
			imageline( $img, $x( 260 ), $y( 520 ), $x( 540 ), $y( 520 ), $lite );
			imageline( $img, $x( 220 ), $y( 640 ), $x( 580 ), $y( 640 ), $lite );
			break;
		case 'cushion':
			ni_demo_rrect( $img, $x( 220 ), $y( 330 ), $x( 580 ), $y( 690 ), $p( 70 ), $main );
			imagesetthickness( $img, max( 1, $p( 4 ) ) );
			imageline( $img, $x( 260 ), $y( 510 ), $x( 540 ), $y( 510 ), $dark );
			break;
		case 'vase':
			imagefilledellipse( $img, $x( 400 ), $y( 600 ), $p( 280 ), $p( 340 ), $main );
			imagefilledrectangle( $img, $x( 350 ), $y( 330 ), $x( 450 ), $y( 470 ), $main );
			imagefilledellipse( $img, $x( 400 ), $y( 330 ), $p( 130 ), $p( 34 ), $dark );
			break;
		case 'candle':
			foreach ( array( 300, 400, 500 ) as $i => $cx ) {
				$top = 420 + $i * 40;
				imagefilledrectangle( $img, $x( $cx - 16 ), $y( $top ), $x( $cx + 16 ), $y( 760 ), $main );
				imagefilledrectangle( $img, $x( $cx - 10 ), $y( $top - 120 ), $x( $cx + 10 ), $y( $top ), ni_demo_color( $img, '#F2EEE6' ) );
				imagefilledellipse( $img, $x( $cx ), $y( $top - 135 ), $p( 16 ), $p( 30 ), ni_demo_color( $img, '#E7B85B' ) );
			}
			imagefilledellipse( $img, $x( 400 ), $y( 765 ), $p( 340 ), $p( 40 ), $dark );
			break;
		case 'poster':
			imagefilledrectangle( $img, $x( 210 ), $y( 220 ), $x( 590 ), $y( 760 ), $ink );
			imagefilledrectangle( $img, $x( 225 ), $y( 235 ), $x( 575 ), $y( 745 ), ni_demo_color( $img, '#FBF8F2' ) );
			imagefilledellipse( $img, $x( 400 ), $y( 430 ), $p( 200 ), $p( 200 ), $main );
			imagefilledrectangle( $img, $x( 260 ), $y( 560 ), $x( 540 ), $y( 690 ), $lite );
			break;
		case 'shelf':
			imagefilledrectangle( $img, $x( 220 ), $y( 160 ), $x( 236 ), $y( 860 ), $dark );
			imagefilledrectangle( $img, $x( 564 ), $y( 160 ), $x( 580 ), $y( 860 ), $dark );
			foreach ( array( 260, 440, 620, 800 ) as $sy ) {
				imagefilledrectangle( $img, $x( 220 ), $y( $sy ), $x( 580 ), $y( $sy + 18 ), $main );
			}
			imagefilledellipse( $img, $x( 300 ), $y( 400 ), $p( 70 ), $p( 110 ), ni_demo_color( $img, '#B4643F' ) );
			imagefilledrectangle( $img, $x( 420 ), $y( 520 ), $x( 520 ), $y( 620 ), ni_demo_color( $img, '#9A9A96' ) );
			break;
		case 'dresser':
		case 'sideboard':
			$w1 = 'sideboard' === $shape ? 100 : 220;
			$w2 = 'sideboard' === $shape ? 700 : 580;
			imagefilledrectangle( $img, $x( $w1 ), $y( 400 ), $x( $w2 ), $y( 700 ), $main );
			imagesetthickness( $img, max( 1, $p( 4 ) ) );
			if ( 'dresser' === $shape ) {
				foreach ( array( 500, 600 ) as $ly ) {
					imageline( $img, $x( $w1 + 10 ), $y( $ly ), $x( $w2 - 10 ), $y( $ly ), $dark );
				}
			} else {
				foreach ( array( 250, 400, 550 ) as $lx ) {
					imageline( $img, $x( $lx ), $y( 410 ), $x( $lx ), $y( 690 ), $dark );
				}
			}
			imagesetthickness( $img, max( 2, $p( 10 ) ) );
			imageline( $img, $x( $w1 + 30 ), $y( 700 ), $x( $w1 + 22 ), $y( 780 ), $wood );
			imageline( $img, $x( $w2 - 30 ), $y( 700 ), $x( $w2 - 22 ), $y( 780 ), $wood );
			break;
		case 'plate':
			imagefilledellipse( $img, $x( 400 ), $y( 560 ), $p( 560 ), $p( 260 ), $dark );
			imagefilledellipse( $img, $x( 400 ), $y( 550 ), $p( 540 ), $p( 240 ), $main );
			imagefilledellipse( $img, $x( 400 ), $y( 555 ), $p( 380 ), $p( 150 ), $lite );
			break;
		case 'glass':
			foreach ( array( 300, 500 ) as $cx ) {
				imagefilledpolygon( $img, array( $x( $cx - 70 ), $y( 380 ), $x( $cx + 70 ), $y( 380 ), $x( $cx + 55 ), $y( 700 ), $x( $cx - 55 ), $y( 700 ) ), $lite );
				imagesetthickness( $img, max( 1, $p( 5 ) ) );
				imagepolygon( $img, array( $x( $cx - 70 ), $y( 380 ), $x( $cx + 70 ), $y( 380 ), $x( $cx + 55 ), $y( 700 ), $x( $cx - 55 ), $y( 700 ) ), $dark );
			}
			break;
	}
}

/**
 * Create an image attachment from a GD drawing.
 *
 * @param string   $name   File name without extension.
 * @param int      $w      Width.
 * @param int      $h      Height.
 * @param callable $draw   Drawing callback ( $img, $w, $h ).
 * @param string   $alt    Alt text.
 * @param string   $bg     Background colour.
 * @return int Attachment ID.
 */
function ni_demo_attachment( $name, $w, $h, $draw, $alt, $bg = '#FFFFFF' ) {
	if ( ! function_exists( 'imagecreatetruecolor' ) ) {
		return 0;
	}
	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'name'           => sanitize_title( $name ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'post_status'    => 'inherit',
		)
	);
	if ( $existing ) {
		return (int) $existing[0];
	}
	$uploads = wp_upload_dir();
	$dir     = trailingslashit( $uploads['basedir'] ) . 'nordicinterior-demo';
	wp_mkdir_p( $dir );
	$file = $dir . '/' . sanitize_file_name( $name ) . '.jpg';

	$img = imagecreatetruecolor( $w, $h );
	imagefilledrectangle( $img, 0, 0, $w, $h, ni_demo_color( $img, $bg ) );
	call_user_func( $draw, $img, $w, $h );
	imagejpeg( $img, $file, 84 );
	imagedestroy( $img );

	$id = wp_insert_attachment(
		array(
			'post_title'     => $alt,
			'post_name'      => sanitize_title( $name ),
			'post_mime_type' => 'image/jpeg',
			'post_status'    => 'inherit',
		),
		$file
	);
	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	update_post_meta( $id, '_ni_demo', 1 );
	return (int) $id;
}

/**
 * Draw a calm room scene (mood image).
 *
 * @param GdImage $img  Image.
 * @param int     $w    Width.
 * @param int     $h    Height.
 * @param string  $wall Wall colour.
 * @param int     $seed Variation seed.
 */
function ni_demo_scene( $img, $w, $h, $wall, $seed ) {
	imagefilledrectangle( $img, 0, 0, $w, $h, ni_demo_color( $img, $wall ) );
	imagefilledrectangle( $img, 0, (int) ( $h * 0.74 ), $w, $h, ni_demo_color( $img, $wall, -0.12 ) );
	// Window light.
	imagefilledrectangle( $img, (int) ( $w * 0.08 ), (int) ( $h * 0.1 ), (int) ( $w * 0.3 ), (int) ( $h * 0.55 ), ni_demo_color( $img, $wall, 0.35 ) );
	// Framed art.
	imagefilledrectangle( $img, (int) ( $w * 0.55 ), (int) ( $h * 0.16 ), (int) ( $w * 0.72 ), (int) ( $h * 0.4 ), ni_demo_color( $img, '#2A2826' ) );
	imagefilledrectangle( $img, (int) ( $w * 0.56 ), (int) ( $h * 0.175 ), (int) ( $w * 0.71 ), (int) ( $h * 0.385 ), ni_demo_color( $img, '#F7F3EC' ) );
	imagefilledellipse( $img, (int) ( $w * 0.635 ), (int) ( $h * 0.28 ), (int) ( $w * 0.06 ), (int) ( $w * 0.06 ), ni_demo_color( $img, array( '#B4643F', '#7D8C6C', '#5B6E85', '#C9B08A' )[ $seed % 4 ] ) );
	$scale = $h / 1000 * 0.95;
	$furn  = array( 'sofa', 'table', 'bed', 'armchair' )[ $seed % 4 ];
	$color = array( '#D9C8AE', '#C8A97E', '#9A9A96', '#7D8C6C', '#E8E1D4' )[ $seed % 5 ];
	ni_demo_shape( $img, $furn, $color, $scale, (int) ( $w * 0.36 ), (int) ( $h * 0.08 ) );
	ni_demo_shape( $img, 'floorlamp', '#F2EEE6', $scale * 0.85, (int) ( $w * 0.75 ), (int) ( $h * 0.12 ) );
	// Plant.
	$green = ni_demo_color( $img, '#6F8169' );
	for ( $i = 0; $i < 7; $i++ ) {
		imagefilledellipse( $img, (int) ( $w * 0.2 + ( $i - 3 ) * $w * 0.012 ), (int) ( $h * ( 0.52 + abs( $i - 3 ) * 0.03 ) ), (int) ( $w * 0.03 ), (int) ( $h * 0.12 ), $green );
	}
	imagefilledrectangle( $img, (int) ( $w * 0.17 ), (int) ( $h * 0.6 ), (int) ( $w * 0.23 ), (int) ( $h * 0.76 ), ni_demo_color( $img, '#B4643F' ) );
}

/**
 * Get or create a term with meta.
 *
 * @param string $name     Name.
 * @param string $taxonomy Taxonomy.
 * @param array  $args     wp_insert_term args.
 * @param array  $meta     Term meta.
 * @return int Term ID.
 */
function ni_demo_term( $name, $taxonomy, $args = array(), $meta = array() ) {
	$slug     = $args['slug'] ?? sanitize_title( $name );
	$existing = get_term_by( 'slug', $slug, $taxonomy );
	if ( $existing ) {
		$id = (int) $existing->term_id;
	} else {
		$res = wp_insert_term( $name, $taxonomy, $args );
		if ( is_wp_error( $res ) ) {
			$id = isset( $res->error_data['term_exists'] ) ? (int) $res->error_data['term_exists'] : 0;
		} else {
			$id = (int) $res['term_id'];
		}
	}
	if ( $id ) {
		foreach ( $meta as $key => $value ) {
			update_term_meta( $id, $key, $value );
		}
		update_term_meta( $id, '_ni_demo', 1 );
	}
	return $id;
}

/**
 * Ensure a global WooCommerce attribute exists and is registered now.
 *
 * @param string $name Label.
 * @param string $slug Slug without pa_.
 * @return string Taxonomy name.
 */
function ni_demo_attribute( $name, $slug ) {
	$taxonomy = 'pa_' . $slug;
	if ( ! wc_attribute_taxonomy_id_by_name( $slug ) ) {
		wc_create_attribute(
			array(
				'name'         => $name,
				'slug'         => $slug,
				'type'         => 'select',
				'order_by'     => 'menu_order',
				'has_archives' => false,
			)
		);
		delete_transient( 'wc_attribute_taxonomies' );
	}
	if ( ! taxonomy_exists( $taxonomy ) ) {
		register_taxonomy(
			$taxonomy,
			array( 'product' ),
			array(
				'hierarchical' => false,
				'public'       => false,
				'show_ui'      => false,
				'query_var'    => false,
				'rewrite'      => false,
				'labels'       => array( 'name' => $name ),
			)
		);
	}
	return $taxonomy;
}

/**
 * Run the import.
 *
 * @return array Summary.
 */
function ni_demo_import() {
	if ( ! ni_wc() ) {
		return array( 'error' => 'WooCommerce saknas' );
	}
	if ( function_exists( 'set_time_limit' ) ) {
		set_time_limit( 600 ); // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged -- Long import.
	}
	wp_defer_term_counting( true );
	mt_srand( 42 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.rand_seeding_mt_srand -- Deterministic demo data.

	$data    = ni_demo_data();
	$summary = array();

	update_option( 'woocommerce_dimension_unit', 'cm' );
	update_option( 'woocommerce_currency', 'SEK' );
	update_option( 'woocommerce_currency_pos', 'right_space' );
	update_option( 'woocommerce_price_thousand_sep', ' ' );
	update_option( 'woocommerce_price_decimal_sep', ',' );
	update_option( 'woocommerce_price_num_decimals', 0 );

	// Rooms.
	$room_ids = array();
	foreach ( $data['rooms'] as $i => $room ) {
		$image                = ni_demo_attachment(
			'ni-rum-' . $room[1],
			1600,
			1000,
			static function ( $img, $w, $h ) use ( $room, $i ) {
				ni_demo_scene( $img, $w, $h, $room[2], $i );
			},
			$room[0]
		);
		$room_ids[ $room[1] ] = ni_demo_term(
			$room[0],
			'ni_room',
			array(
				'slug'        => $room[1],
				/* translators: %s: room name in lowercase. */
				'description' => sprintf( __( 'Möbler, belysning och inredning för %s – utvalt från Nordens bästa butiker.', 'nordicinterior' ), mb_strtolower( $room[0] ) ),
			),
			array(
				'ni_tone'    => $room[2],
				'ni_popular' => $room[3],
				'ni_order'   => $i,
				'ni_months'  => $room[4],
				'ni_image'   => $image,
			)
		);
	}
	$summary['rooms'] = count( $room_ids );

	// Styles.
	$style_ids = array();
	foreach ( $data['styles'] as $i => $style ) {
		$image                  = ni_demo_attachment(
			'ni-stil-' . $style[1],
			1600,
			1000,
			static function ( $img, $w, $h ) use ( $i ) {
				ni_demo_scene( $img, $w, $h, array( '#E9E2D6', '#DED8CC', '#EEE9E0', '#E3D9CB', '#E8DCCB', '#D9D6D0', '#E6D8C6', '#EADBC8' )[ $i % 8 ], $i + 2 );
			},
			$style[0]
		);
		$style_ids[ $style[1] ] = ni_demo_term(
			$style[0],
			'ni_style',
			array(
				'slug'        => $style[1],
				'description' => $style[2],
			),
			array(
				'ni_lead'  => $style[2],
				'ni_order' => $i,
				'ni_image' => $image,
			)
		);
	}
	$summary['styles'] = count( $style_ids );

	// Brands.
	$brand_ids = array();
	if ( taxonomy_exists( 'product_brand' ) ) {
		foreach ( $data['brands'] as $i => $brand ) {
			$brand_ids[ $brand[1] ] = ni_demo_term(
				$brand[0],
				'product_brand',
				array( 'slug' => $brand[1] ),
				array(
					'ni_country'  => $brand[2],
					'ni_website'  => $brand[3],
					'ni_featured' => $brand[4],
					'ni_order'    => $i,
				)
			);
		}
	}
	$summary['brands'] = count( $brand_ids );

	// Stores.
	$store_ids = array();
	foreach ( $data['stores'] as $i => $store ) {
		$store_ids[ $store[1] ] = ni_demo_term(
			$store[0],
			'ni_store',
			array( 'slug' => $store[1] ),
			array(
				'ni_website'  => $store[2],
				'ni_shipping' => $store[3],
				'ni_returns'  => $store[4],
				'ni_delivery' => $store[5],
				'ni_order'    => $i,
			)
		);
	}
	$summary['stores'] = count( $store_ids );

	// Categories (three levels).
	$cat_ids = array();
	foreach ( $data['categories'] as $name => $def ) {
		$top = ni_demo_term( $name, 'product_cat', array( 'slug' => $def['slug'] ) );
		foreach ( $def['children'] as $child => $grandchildren ) {
			$child_id                            = ni_demo_term( $child, 'product_cat', array( 'parent' => $top ) );
			$cat_ids[ sanitize_title( $child ) ] = $child_id;
			foreach ( $grandchildren as $grand ) {
				$cat_ids[ sanitize_title( $grand ) ] = ni_demo_term( $grand, 'product_cat', array( 'parent' => $child_id ) );
			}
		}
	}
	$summary['categories'] = count( $cat_ids ) + count( $data['categories'] );

	// Attributes.
	$tax_material = ni_demo_attribute( 'Material', 'material' );
	$tax_color    = ni_demo_attribute( 'Färg', 'farg' );
	$tax_designer = ni_demo_attribute( 'Designer', 'designer' );
	foreach ( $data['materials'] as $material ) {
		ni_demo_term( $material, $tax_material );
	}
	foreach ( $data['colors'] as $color => $hex ) {
		ni_demo_term( $color, $tax_color, array(), array( 'ni_hex' => $hex ) );
	}
	foreach ( $data['designers'] as $designer ) {
		ni_demo_term( $designer, $tax_designer );
	}

	// Products.
	$brand_keys  = array_keys( $brand_ids );
	$store_keys  = array_keys( $store_ids );
	$product_ids = array();
	$n           = 0;
	foreach ( $data['products'] as $tpl ) {
		list( $type, $cat_slug, $names, $dims, $price_range, $rooms, $styles, $materials, $colors, $shape ) = $tpl;
		foreach ( $names as $k => $name ) {
			++$n;
			$sku = sprintf( 'NI-DEMO-%03d', $n );
			$id  = (int) wc_get_product_id_by_sku( $sku );
			if ( $id ) {
				$product_ids[] = $id;
				continue;
			}
			$brand    = $brand_keys ? $brand_keys[ ( $n * 7 + $k ) % count( $brand_keys ) ] : '';
			$store    = $store_keys[ ( $n * 5 + $k ) % count( $store_keys ) ];
			$color    = $colors[ $k % count( $colors ) ];
			$color2   = $colors[ ( $k + 1 ) % count( $colors ) ];
			$material = $materials[ $k % count( $materials ) ];
			$designer = $data['designers'][ $n % count( $data['designers'] ) ];
			$price    = (int) ( round( mt_rand( $price_range[0], $price_range[1] ) / 100 ) * 100 - 5 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.rand_mt_rand -- Seeded, deterministic demo data.
			$price    = max( 95, $price );
			$on_sale  = 0 === $n % 3;
			$sale     = $on_sale ? (int) ( round( $price * ( 1 - mt_rand( 10, 30 ) / 100 ) / 10 ) * 10 - 5 ) : 0; // phpcs:ignore WordPress.WP.AlternativeFunctions.rand_mt_rand -- Seeded, deterministic demo data.

			$image = ni_demo_attachment(
				'ni-produkt-' . $sku,
				800,
				1000,
				static function ( $img ) use ( $shape, $data, $color ) {
					ni_demo_shape( $img, $shape, $data['colors'][ $color ] );
				},
				$name . ' – ' . mb_strtolower( $color )
			);

			$product = new WC_Product_External();
			$product->set_name( $name );
			$product->set_sku( $sku );
			$product->set_status( 'publish' );
			$product->set_regular_price( (string) $price );
			if ( $sale ) {
				$product->set_sale_price( (string) $sale );
			}
			$product->set_product_url( 'https://www.example.com/' . $store . '/' . sanitize_title( $name ) . '?aff=nordicinterior' );
			$product->set_button_text( '' );
			$product->set_width( (string) ( $dims[0] + ( $k * 10 ) ) );
			$product->set_length( (string) $dims[1] );
			$product->set_height( (string) $dims[2] );
			/* translators: 1: product name, 2: product type in lowercase, 3: material in lowercase, 4: designer. */
			$product->set_short_description( sprintf( __( '%1$s är en tidlös %2$s i %3$s, formgiven av %4$s.', 'nordicinterior' ), $name, mb_strtolower( $type ), mb_strtolower( $material ), $designer ) );
			$product->set_description(
				sprintf(
					/* translators: 1: product name, 2: material in lowercase. */
					__( '<p>%1$s kombinerar enkel form med omsorgsfullt valda material. Ytan i %2$s åldras vackert och passar lika bra i ett nordiskt hem som i ett japandi-inspirerat rum.</p><p>Demoprodukt – priser, mått och länkar är exempel.</p>', 'nordicinterior' ),
					$name,
					mb_strtolower( $material )
				)
			);
			if ( $image ) {
				$product->set_image_id( $image );
			}
			if ( isset( $cat_ids[ $cat_slug ] ) ) {
				$product->set_category_ids( array( $cat_ids[ $cat_slug ] ) );
			}

			$attributes = array();
			foreach ( array(
				$tax_material => array( $material ),
				$tax_color    => array_unique( array( $color, $color2 ) ),
				$tax_designer => array( $designer ),
			) as $tax => $values ) {
				$term_ids = array();
				foreach ( $values as $value ) {
					$term = get_term_by( 'name', $value, $tax );
					if ( $term ) {
						$term_ids[] = (int) $term->term_id;
					}
				}
				$attr = new WC_Product_Attribute();
				$attr->set_id( wc_attribute_taxonomy_id_by_name( substr( $tax, 3 ) ) );
				$attr->set_name( $tax );
				$attr->set_options( $term_ids );
				$attr->set_visible( true );
				$attributes[] = $attr;
			}
			$product->set_attributes( $attributes );
			$product->update_meta_data( '_ni_series', strtok( $name, ' ' ) );
			$product->update_meta_data( '_ni_care', __( 'Torka av med lätt fuktad trasa. Undvik direkt solljus.', 'nordicinterior' ) );
			$product->update_meta_data( '_ni_demo', 1 );
			$id = $product->save();

			wp_set_object_terms( $id, array_map( static fn( $r ) => $room_ids[ $r ] ?? 0, $rooms ), 'ni_room' );
			wp_set_object_terms( $id, array( $style_ids[ $styles[ $k % count( $styles ) ] ] ?? 0 ), 'ni_style' );
			wp_set_object_terms( $id, array( $store_ids[ $store ] ), 'ni_store' );
			if ( $brand ) {
				wp_set_object_terms( $id, array( $brand_ids[ $brand ] ), 'product_brand' );
			}
			// Some products get a recent price drop to show the "Prissänkt" badge.
			if ( ! $on_sale && 0 === $n % 5 ) {
				update_post_meta( $id, '_ni_prev_price', $price + (int) round( $price * 0.12 ) );
				update_post_meta( $id, '_ni_price_changed', time() - DAY_IN_SECONDS );
			}
			$product_ids[] = $id;
		}
	}
	wp_defer_term_counting( false );
	$summary['products'] = count( $product_ids );

	// Anonymous demo clicks for the last 30 days (for "Populärast" and the dashboard).
	global $wpdb;
	$table = $wpdb->prefix . 'ni_clicks';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$has = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
	if ( 0 === $has ) {
		$placements = array_keys( ni_placements() );
		foreach ( $product_ids as $pid ) {
			$clicks = mt_rand( 0, 30 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.rand_mt_rand -- Seeded, deterministic demo data.
			for ( $c = 0; $c < $clicks; $c++ ) {
				ni_record_click(
					array(
						'object_id' => $pid,
						'placement' => $placements[ mt_rand( 0, 5 ) ], // phpcs:ignore WordPress.WP.AlternativeFunctions.rand_mt_rand -- Seeded, deterministic demo data.
						// Spread over the last 30 days.
						'created'   => wp_date( 'Y-m-d H:i:s', time() - mt_rand( 0, 30 * DAY_IN_SECONDS ) ), // phpcs:ignore WordPress.WP.AlternativeFunctions.rand_mt_rand -- Seeded, deterministic demo data.
					)
				);
			}
		}
	}

	// Campaigns.
	$campaigns = array(
		array( __( 'Höstrea – upp till 30 % på soffor och fåtöljer', 'nordicinterior' ), 'royal-design', 'HOST30', '−30 %', 12, 1, array( 'vardagsrum' ) ),
		array( __( 'Fri frakt på alla mattor', 'nordicinterior' ), 'rum21', '', __( 'Fri frakt', 'nordicinterior' ), 5, 0, array( 'vardagsrum', 'sovrum' ) ),
		array( __( '20 % på belysning', 'nordicinterior' ), 'nordiska-galleriet', 'LJUS20', '−20 %', 20, 0, array( 'matrum', 'kok' ) ),
	);
	foreach ( $campaigns as $i => $c ) {
		$existing = get_page_by_path( sanitize_title( $c[0] ), OBJECT, 'ni_campaign' );
		if ( $existing ) {
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_type'    => 'ni_campaign',
				'post_status'  => 'publish',
				'post_title'   => $c[0],
				'post_excerpt' => __( 'Gäller utvalda produkter så långt lagret räcker. Demokampanj.', 'nordicinterior' ),
			)
		);
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		update_post_meta( $id, '_ni_url', 'https://www.example.com/' . $c[1] . '/kampanj' );
		update_post_meta( $id, '_ni_code', $c[2] );
		update_post_meta( $id, '_ni_badge', $c[3] );
		update_post_meta( $id, '_ni_start', wp_date( 'Y-m-d H:i:00', time() - 3 * DAY_IN_SECONDS ) );
		update_post_meta( $id, '_ni_end', wp_date( 'Y-m-d 23:59:00', time() + $c[4] * DAY_IN_SECONDS ) );
		update_post_meta( $id, '_ni_main', $c[5] ? '1' : '' );
		update_post_meta( $id, '_ni_demo', 1 );
		wp_set_object_terms( $id, array( $store_ids[ $c[1] ] ), 'ni_store' );
		wp_set_object_terms( $id, array_map( static fn( $r ) => $room_ids[ $r ], $c[6] ), 'ni_room' );
		$thumb = ni_demo_attachment(
			'ni-kampanj-' . $i,
			1200,
			750,
			static function ( $img, $w, $h ) use ( $i ) {
				ni_demo_scene( $img, $w, $h, array( '#E4D9C6', '#E3DCD3', '#DED6C4' )[ $i ], $i + 5 );
			},
			$c[0]
		);
		if ( $thumb ) {
			set_post_thumbnail( $id, $thumb );
		}
	}
	$summary['campaigns'] = count( $campaigns );

	// Article categories and posts.
	$cats = array();
	foreach ( array( 'Guider', 'Inredningstips', 'Shoppa looken' ) as $cat ) {
		$cats[ $cat ] = ni_demo_term( $cat, 'category' );
	}
	$pick      = static function ( $offset, $count ) use ( $product_ids ) {
		return array_slice( $product_ids, $offset, $count );
	};
	$posts     = array(
		array( __( 'Så väljer du rätt soffa – mått, material och komfort', 'nordicinterior' ), 'Guider', 'vardagsrum', 'skandinavisk', $pick( 0, 4 ), false ),
		array( __( 'Guide: belysning i sovrummet', 'nordicinterior' ), 'Guider', 'sovrum', 'japandi', $pick( 34, 4 ), false ),
		array( __( 'Japandi hemma – fem enkla grepp', 'nordicinterior' ), 'Inredningstips', 'vardagsrum', 'japandi', $pick( 14, 4 ), false ),
		array( __( 'Skapa en lugn hall med smart förvaring', 'nordicinterior' ), 'Inredningstips', 'hall', 'minimalistisk', $pick( 49, 3 ), false ),
		array( __( 'Shoppa looken: varmt vardagsrum i ek och bouclé', 'nordicinterior' ), 'Shoppa looken', 'vardagsrum', 'skandinavisk', array( $product_ids[0], $product_ids[11], $product_ids[33], $product_ids[37], $product_ids[39] ), true ),
		array( __( 'Shoppa looken: ljust matrum i japandi', 'nordicinterior' ), 'Shoppa looken', 'matrum', 'japandi', array( $product_ids[17], $product_ids[22], $product_ids[29], $product_ids[42] ), true ),
	);
	$look_post = 0;
	foreach ( $posts as $i => $p ) {
		if ( get_page_by_path( sanitize_title( $p[0] ), OBJECT, 'post' ) ) {
			continue;
		}
		$image    = ni_demo_attachment(
			'ni-artikel-' . $i,
			1600,
			1000,
			static function ( $img, $w, $h ) use ( $i ) {
				ni_demo_scene( $img, $w, $h, array( '#E7DFD3', '#E3DCD3', '#E9E2D6', '#E4D9C6', '#E6D8C6', '#EEE9E0' )[ $i ], $i * 3 + 1 );
			},
			$p[0]
		);
		$content  = '<!-- wp:paragraph --><p>' . esc_html__( 'Ett hem blir lugnt när varje sak har sin plats och materialen får tala. Här är våra bästa tips – och produkterna vi själva skulle välja.', 'nordicinterior' ) . '</p><!-- /wp:paragraph -->';
		$content .= '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html__( 'Börja med måtten', 'nordicinterior' ) . '</h2><!-- /wp:heading -->';
		$content .= '<!-- wp:paragraph --><p>' . esc_html__( 'Mät rummet och markera möblernas mått med maskeringstejp på golvet. Lämna minst 80 cm för gångytor och tänk på hur dagsljuset faller.', 'nordicinterior' ) . '</p><!-- /wp:paragraph -->';
		if ( $p[5] ) {
			$points = array();
			$coords = array( array( 38, 66 ), array( 62, 30 ), array( 22, 52 ), array( 78, 58 ), array( 50, 82 ) );
			foreach ( $p[4] as $j => $pid ) {
				$points[] = array(
					'id' => $pid,
					'x'  => $coords[ $j ][0],
					'y'  => $coords[ $j ][1],
				);
			}
			$attrs    = wp_json_encode(
				array(
					'image'  => $image,
					'title'  => $p[0],
					'points' => $points,
					'align'  => 'wide',
				),
				JSON_UNESCAPED_UNICODE
			);
			$content .= '<!-- wp:nordicinterior/shop-the-look ' . $attrs . ' /-->';
		} else {
			$content .= '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html__( 'Välj material som åldras vackert', 'nordicinterior' ) . '</h2><!-- /wp:heading -->';
			$content .= '<!-- wp:paragraph --><p>' . esc_html__( 'Massivt trä, ull och linne blir bara vackrare med tiden. Kombinera gärna två träslag och låt textilierna stå för färgen.', 'nordicinterior' ) . '</p><!-- /wp:paragraph -->';
		}
		$id = wp_insert_post(
			array(
				'post_type'     => 'post',
				'post_status'   => 'publish',
				'post_title'    => $p[0],
				'post_excerpt'  => __( 'Enkla grepp för ett lugnare, varmare hem – med produkter från Nordens bästa butiker.', 'nordicinterior' ),
				'post_content'  => $content,
				'post_category' => array( $cats[ $p[1] ] ),
			)
		);
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		set_post_thumbnail( $id, $image );
		wp_set_object_terms( $id, array( $room_ids[ $p[2] ] ), 'ni_room' );
		wp_set_object_terms( $id, array( $style_ids[ $p[3] ] ), 'ni_style' );
		update_post_meta( $id, '_ni_related_products', implode( ',', $p[4] ) );
		update_post_meta( $id, '_ni_demo', 1 );
		ni_index_post_products( $id, get_post( $id ) );
		if ( $p[5] && ! $look_post ) {
			$look_post = $id;
		}
	}
	$summary['posts'] = count( $posts );

	// Pages.
	$pages    = array(
		'front'  => array( __( 'Startsida', 'nordicinterior' ), '' ),
		'blog'   => array( __( 'Inspiration', 'nordicinterior' ), '' ),
		'brands' => array( __( 'Alla varumärken', 'nordicinterior' ), 'page-templates/brands.php' ),
		'stores' => array( __( 'Alla butiker', 'nordicinterior' ), 'page-templates/stores.php' ),
		'sale'   => array( __( 'Rea', 'nordicinterior' ), 'page-templates/sale.php' ),
		'rooms'  => array( __( 'Alla rum', 'nordicinterior' ), 'page-templates/rooms.php' ),
		'styles' => array( __( 'Alla stilar', 'nordicinterior' ), 'page-templates/styles.php' ),
		'about'  => array( __( 'Om Nordic Interior', 'nordicinterior' ), '' ),
	);
	$page_ids = array();
	foreach ( $pages as $key => $page ) {
		$existing = get_page_by_path( sanitize_title( $page[0] ) );
		if ( $existing ) {
			$page_ids[ $key ] = $existing->ID;
			continue;
		}
		$content          = 'about' === $key ? '<!-- wp:paragraph --><p>' . esc_html( (string) ni_mod( 'disclosure_text' ) ) . '</p><!-- /wp:paragraph -->' : '';
		$page_ids[ $key ] = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $page[0],
				'post_content' => $content,
			)
		);
		if ( $page[1] ) {
			update_post_meta( $page_ids[ $key ], '_wp_page_template', $page[1] );
		}
		update_post_meta( $page_ids[ $key ], '_ni_demo', 1 );
	}
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $page_ids['front'] );
	update_option( 'page_for_posts', $page_ids['blog'] );
	wp_cache_flush_group( 'nordicinterior' );

	// Hero image and look post.
	$hero = ni_demo_attachment(
		'ni-hero',
		1920,
		1000,
		static function ( $img, $w, $h ) {
			ni_demo_scene( $img, $w, $h, '#E6DCCB', 0 );
		},
		__( 'Ljust vardagsrum med soffa i bouclé och golvlampa', 'nordicinterior' )
	);
	set_theme_mod( 'ni_hero_image_1', $hero );
	if ( $look_post ) {
		set_theme_mod( 'ni_look_post', $look_post );
	}

	// Footer menu.
	$footer = wp_get_nav_menu_object( __( 'Sidfotsmeny', 'nordicinterior' ) );
	if ( ! $footer ) {
		$footer_id = wp_create_nav_menu( __( 'Sidfotsmeny', 'nordicinterior' ) );
		if ( ! is_wp_error( $footer_id ) ) {
			foreach ( array( 'about', 'stores', 'brands' ) as $key ) {
				wp_update_nav_menu_item(
					$footer_id,
					0,
					array(
						'menu-item-object'    => 'page',
						'menu-item-object-id' => $page_ids[ $key ],
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
					)
				);
			}
			$locations           = (array) get_theme_mod( 'nav_menu_locations', array() );
			$locations['footer'] = $footer_id;
			set_theme_mod( 'nav_menu_locations', $locations );
		}
	}

	ni_build_primary_menu();
	ni_search_vocab_flush();
	ni_recalc();
	flush_rewrite_rules( false );

	return $summary;
}
