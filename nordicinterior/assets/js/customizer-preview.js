/**
 * Theme Adjustments live preview: settings that map to CSS custom
 * properties are applied instantly without reloading.
 */
( function ( api ) {
	'use strict';

	var map = ( window.niPreview && window.niPreview.map ) || {};
	var root = document.documentElement;

	Object.keys( map ).forEach( function ( setting ) {
		api( setting, function ( value ) {
			value.bind( function ( to ) {
				var def = map[ setting ];
				if ( to === '' || to === null || typeof to === 'undefined' ) {
					root.style.removeProperty( def.var );
					return;
				}
				root.style.setProperty( def.var, to + ( def.unit || '' ) );
			} );
		} );
	} );

	api( 'ni_topbar_text', function ( value ) {
		value.bind( function ( to ) {
			var el = document.querySelector( '.ni-topbar__usp--1' );
			if ( el ) {
				el.textContent = to;
			}
		} );
	} );
}( wp.customize ) );
