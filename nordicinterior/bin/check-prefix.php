<?php
/**
 * Verifies that all global functions, classes and hooks declared by the
 * theme use the ni_/NI_ prefix (replacement for WPCS PrefixAllGlobals,
 * which does not accept two-letter prefixes).
 *
 * Usage: php bin/check-prefix.php
 *
 * @package NordicInterior
 */

// phpcs:disable WordPress.WP.AlternativeFunctions, WordPress.Security.EscapeOutput

$root   = dirname( __DIR__ );
$errors = array();
$it     = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ) );
foreach ( $it as $file ) {
	if ( 'php' !== $file->getExtension() || false !== strpos( $file->getPathname(), '/vendor/' ) || false !== strpos( $file->getPathname(), '/bin/' ) ) {
		continue;
	}
	$tokens = token_get_all( file_get_contents( $file->getPathname() ) );
	$depth  = 0;
	$class  = 0;
	$count  = count( $tokens );
	for ( $i = 0; $i < $count; $i++ ) {
		$t = $tokens[ $i ];
		if ( '{' === $t || ( is_array( $t ) && in_array( $t[0], array( T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES ), true ) ) ) {
			++$depth;
		} elseif ( '}' === $t ) {
			--$depth;
			if ( $class && $depth < $class ) {
				$class = 0;
			}
		}
		if ( ! is_array( $t ) ) {
			continue;
		}
		$next = static function () use ( $tokens, &$i ) {
			for ( $j = $i + 1; isset( $tokens[ $j ] ); $j++ ) {
				if ( is_array( $tokens[ $j ] ) && T_WHITESPACE === $tokens[ $j ][0] ) {
					continue;
				}
				return $tokens[ $j ];
			}
			return null;
		};
		if ( T_CLASS === $t[0] ) {
			$n = $next();
			if ( is_array( $n ) && T_STRING === $n[0] ) {
				if ( 0 !== strpos( $n[1], 'NI_' ) ) {
					$errors[] = $file->getPathname() . ':' . $t[2] . ' class ' . $n[1];
				}
				$class = $depth + 1;
			}
		}
		if ( T_FUNCTION === $t[0] && 0 === $class && 0 === $depth ) {
			$n = $next();
			if ( is_array( $n ) && T_STRING === $n[0] && 0 !== strpos( $n[1], 'ni_' ) ) {
				$errors[] = $file->getPathname() . ':' . $t[2] . ' function ' . $n[1];
			}
		}
		if ( T_STRING === $t[0] && in_array( $t[1], array( 'do_action', 'apply_filters' ), true ) ) {
			for ( $j = $i + 1; isset( $tokens[ $j ] ); $j++ ) {
				if ( is_array( $tokens[ $j ] ) && T_CONSTANT_ENCAPSED_STRING === $tokens[ $j ][0] ) {
					$hook = trim( $tokens[ $j ][1], '\'"' );
					$core = array( 'woocommerce_before_main_content', 'woocommerce_after_main_content', 'woocommerce_before_single_product', 'woocommerce_after_single_product', 'nav_menu_item_title' );
					if ( 0 !== strpos( $hook, 'ni_' ) && ! in_array( $hook, $core, true ) ) {
						$errors[] = $file->getPathname() . ':' . $t[2] . ' hook ' . $hook;
					}
					break;
				}
				if ( ';' === $tokens[ $j ] ) {
					break;
				}
			}
		}
	}
}
if ( $errors ) {
	echo implode( PHP_EOL, $errors ), PHP_EOL;
	exit( 1 );
}
echo "OK: all globals use the ni_/NI_ prefix.\n";
