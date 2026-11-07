/**
 * Shoppa looken: clickable product points. Works with mouse, keyboard and
 * touch. On small screens the product card opens as a bottom sheet.
 */
( function () {
	'use strict';

	var mobile = window.matchMedia( '(max-width: 767px)' );

	function closeAll( look, except ) {
		Array.prototype.forEach.call( look.querySelectorAll( '[data-ni-look-dot][aria-expanded="true"]' ), function ( dot ) {
			if ( dot !== except ) {
				dot.setAttribute( 'aria-expanded', 'false' );
				var pop = document.getElementById( dot.getAttribute( 'aria-controls' ) );
				if ( pop ) {
					pop.hidden = true;
					pop.classList.remove( 'is-sheet' );
				}
			}
		} );
		document.documentElement.classList.remove( 'ni-look-sheet-open' );
	}

	Array.prototype.forEach.call( document.querySelectorAll( '[data-ni-look]' ), function ( look ) {
		look.addEventListener( 'click', function ( e ) {
			var dot = e.target.closest( '[data-ni-look-dot]' );
			var closeBtn = e.target.closest( '[data-ni-look-close]' );
			if ( closeBtn ) {
				var pop = closeBtn.closest( '.ni-look__pop' );
				var owner = look.querySelector( '[aria-controls="' + pop.id + '"]' );
				closeAll( look );
				if ( owner ) {
					owner.focus();
				}
				return;
			}
			if ( ! dot ) {
				return;
			}
			var target = document.getElementById( dot.getAttribute( 'aria-controls' ) );
			var open = dot.getAttribute( 'aria-expanded' ) !== 'true';
			closeAll( look, dot );
			dot.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			if ( target ) {
				target.hidden = ! open;
				target.classList.toggle( 'is-sheet', open && mobile.matches );
				if ( open && mobile.matches ) {
					document.documentElement.classList.add( 'ni-look-sheet-open' );
				}
				if ( open ) {
					var focusTarget = target.querySelector( '.ni-card__title a' ) || target.querySelector( 'a, button' );
					if ( focusTarget && ( mobile.matches || e.detail === 0 ) ) {
						focusTarget.focus();
					}
				}
			}
		} );

		look.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' ) {
				var openDot = look.querySelector( '[data-ni-look-dot][aria-expanded="true"]' );
				if ( openDot ) {
					closeAll( look );
					openDot.focus();
				}
			}
		} );

		document.addEventListener( 'click', function ( e ) {
			if ( ! look.contains( e.target ) ) {
				closeAll( look );
			}
		} );
	} );
}() );
