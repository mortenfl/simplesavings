<?php
/**
 * Generates languages/nordicinterior.pot without WP-CLI.
 * (`wp i18n make-pot . languages/nordicinterior.pot` gives the same result.)
 *
 * Usage: php bin/make-pot.php
 *
 * @package NordicInterior
 */

// phpcs:disable

$root = dirname( __DIR__ );
$funcs = array(
	'__' => array( 's' ), '_e' => array( 's' ), 'esc_html__' => array( 's' ), 'esc_html_e' => array( 's' ),
	'esc_attr__' => array( 's' ), 'esc_attr_e' => array( 's' ), '_x' => array( 's', 'c' ), '_ex' => array( 's', 'c' ),
	'esc_html_x' => array( 's', 'c' ), 'esc_attr_x' => array( 's', 'c' ), '_n' => array( 's', 'p', null ),
	'_nx' => array( 's', 'p', null, 'c' ), '_n_noop' => array( 's', 'p' ),
);
$entries = array();
$add = static function ( $e, $ref, $comment ) use ( &$entries ) {
	$key = ( $e['c'] ?? '' ) . "\x04" . $e['s'];
	if ( ! isset( $entries[ $key ] ) ) {
		$entries[ $key ] = $e + array( 'refs' => array(), 'comments' => array() );
	}
	$entries[ $key ]['refs'][] = $ref;
	if ( $comment && ! in_array( $comment, $entries[ $key ]['comments'], true ) ) {
		$entries[ $key ]['comments'][] = $comment;
	}
};
$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ) );
$files = array();
foreach ( $it as $f ) {
	$path = $f->getPathname();
	if ( preg_match( '#/(vendor|node_modules|bin)/#', $path ) ) {
		continue;
	}
	if ( in_array( $f->getExtension(), array( 'php', 'js' ), true ) ) {
		$files[] = $path;
	}
}
sort( $files );
foreach ( $files as $path ) {
	$rel = substr( $path, strlen( $root ) + 1 );
	$src = file_get_contents( $path );
	if ( 'js' === pathinfo( $path, PATHINFO_EXTENSION ) ) {
		if ( preg_match_all( "/__\(\s*'((?:[^'\\\\]|\\\\.)*)'\s*,\s*'nordicinterior'\s*\)/", $src, $m, PREG_OFFSET_CAPTURE ) ) {
			foreach ( $m[1] as $hit ) {
				$line = substr_count( substr( $src, 0, $hit[1] ), "\n" ) + 1;
				$add( array( 's' => stripslashes( $hit[0] ) ), "$rel:$line", '' );
			}
		}
		continue;
	}
	$tokens = token_get_all( $src );
	$comment = '';
	$n = count( $tokens );
	for ( $i = 0; $i < $n; $i++ ) {
		$t = $tokens[ $i ];
		if ( is_array( $t ) && T_COMMENT === $t[0] && preg_match( '#translators:#i', $t[1] ) ) {
			$comment = trim( preg_replace( '#^/\*+|\*+/$|^//#', '', $t[1] ) );
			continue;
		}
		if ( ! is_array( $t ) || T_STRING !== $t[0] || ! isset( $funcs[ $t[1] ] ) ) {
			continue;
		}
		// Next non-whitespace must be "(".
		$j = $i + 1;
		while ( isset( $tokens[ $j ] ) && is_array( $tokens[ $j ] ) && T_WHITESPACE === $tokens[ $j ][0] ) {
			$j++;
		}
		if ( '(' !== ( $tokens[ $j ] ?? '' ) ) {
			continue;
		}
		$args = array();
		$cur = null;
		$depth = 0;
		for ( $k = $j + 1; isset( $tokens[ $k ] ); $k++ ) {
			$tk = $tokens[ $k ];
			if ( '(' === $tk || '[' === $tk ) {
				$depth++;
				$cur = false;
				continue;
			}
			if ( ')' === $tk || ']' === $tk ) {
				if ( 0 === $depth ) {
					$args[] = $cur;
					break;
				}
				$depth--;
				continue;
			}
			if ( ',' === $tk && 0 === $depth ) {
				$args[] = $cur;
				$cur = null;
				continue;
			}
			if ( is_array( $tk ) && T_WHITESPACE === $tk[0] ) {
				continue;
			}
			if ( is_array( $tk ) && T_CONSTANT_ENCAPSED_STRING === $tk[0] && null === $cur ) {
				$q = $tk[1][0];
				$cur = substr( $tk[1], 1, -1 );
				$cur = "'" === $q ? str_replace( array( "\\'", '\\\\' ), array( "'", '\\' ), $cur ) : stripcslashes( $cur );
			} else {
				$cur = false;
			}
		}
		$spec = $funcs[ $t[1] ];
		$domain = $args[ count( $spec ) ] ?? null;
		if ( 'nordicinterior' !== $domain ) {
			continue;
		}
		$e = array();
		foreach ( $spec as $idx => $key ) {
			if ( $key && is_string( $args[ $idx ] ?? null ) ) {
				$e[ $key ] = $args[ $idx ];
			}
		}
		if ( isset( $e['s'] ) ) {
			$add( $e, "$rel:{$t[2]}", $comment );
		}
		$comment = '';
	}
}
$esc = static function ( $s ) {
	return '"' . str_replace( array( '\\', '"', "\n", "\t" ), array( '\\\\', '\\"', '\\n', '\\t' ), $s ) . '"';
};
$out = "# Copyright (C) " . date( 'Y' ) . " Nordic Interior\n# This file is distributed under the GPL-2.0-or-later.\nmsgid \"\"\nmsgstr \"\"\n"
	. "\"Project-Id-Version: Nordic Interior 1.0.0\\n\"\n\"Report-Msgid-Bugs-To: https://www.nordicinterior.se/\\n\"\n"
	. "\"POT-Creation-Date: " . gmdate( 'Y-m-d H:i+0000' ) . "\\n\"\n\"MIME-Version: 1.0\\n\"\n\"Content-Type: text/plain; charset=UTF-8\\n\"\n\"Content-Transfer-Encoding: 8bit\\n\"\n"
	. "\"PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE\\n\"\n\"Last-Translator: FULL NAME <EMAIL@ADDRESS>\\n\"\n\"Language-Team: LANGUAGE <LL@li.org>\\n\"\n\"X-Domain: nordicinterior\\n\"\n";
// Theme header strings.
$out .= "\n#. Theme Name of the theme\nmsgid \"Nordic Interior\"\nmsgstr \"\"\n";
foreach ( $entries as $e ) {
	$out .= "\n";
	foreach ( $e['comments'] as $c ) {
		$out .= '#. ' . $c . "\n";
	}
	$out .= '#: ' . implode( ' ', array_slice( $e['refs'], 0, 8 ) ) . "\n";
	if ( preg_match( '/%[0-9$]*[sd]/', $e['s'] . ( $e['p'] ?? '' ) ) ) {
		$out .= "#, php-format\n";
	}
	if ( isset( $e['c'] ) ) {
		$out .= 'msgctxt ' . $esc( $e['c'] ) . "\n";
	}
	$out .= 'msgid ' . $esc( $e['s'] ) . "\n";
	if ( isset( $e['p'] ) ) {
		$out .= 'msgid_plural ' . $esc( $e['p'] ) . "\nmsgstr[0] \"\"\nmsgstr[1] \"\"\n";
	} else {
		$out .= "msgstr \"\"\n";
	}
}
file_put_contents( $root . '/languages/nordicinterior.pot', $out );
echo count( $entries ) . " strings written to languages/nordicinterior.pot\n";
