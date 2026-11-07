/**
 * Theme Adjustments controls: sortable front page sections, colour
 * schemes, and export / import / reset.
 */
( function ( api ) {
	'use strict';

	var cfg = window.niControls || {};

	/* Sortable sections ------------------------------------------------ */
	api.controlConstructor[ 'ni-sortable' ] = api.Control.extend( {
		ready: function () {
			var control = this;
			var list = control.container[ 0 ].querySelector( '[data-ni-sortable]' );
			if ( ! list ) {
				return;
			}
			var dragged = null;

			function save() {
				var order = [];
				var off = [];
				Array.prototype.forEach.call( list.children, function ( li ) {
					order.push( li.dataset.key );
					if ( ! li.querySelector( 'input' ).checked ) {
						off.push( li.dataset.key );
					}
				} );
				control.settings[ 'default' ].set( order.join( ',' ) );
				control.settings.off.set( off.join( ',' ) );
			}

			list.addEventListener( 'change', save );
			list.addEventListener( 'click', function ( e ) {
				var btn = e.target.closest( 'button[data-dir]' );
				if ( ! btn ) {
					return;
				}
				var li = btn.closest( 'li' );
				if ( btn.dataset.dir === 'up' && li.previousElementSibling ) {
					list.insertBefore( li, li.previousElementSibling );
				} else if ( btn.dataset.dir === 'down' && li.nextElementSibling ) {
					list.insertBefore( li.nextElementSibling, li );
				}
				btn.focus();
				save();
			} );
			list.addEventListener( 'dragstart', function ( e ) {
				dragged = e.target.closest( 'li' );
				if ( dragged ) {
					dragged.classList.add( 'is-dragging' );
					e.dataTransfer.effectAllowed = 'move';
				}
			} );
			list.addEventListener( 'dragend', function () {
				if ( dragged ) {
					dragged.classList.remove( 'is-dragging' );
				}
				dragged = null;
				save();
			} );
			list.addEventListener( 'dragover', function ( e ) {
				e.preventDefault();
				var over = e.target.closest( 'li' );
				if ( ! dragged || ! over || over === dragged ) {
					return;
				}
				var rect = over.getBoundingClientRect();
				var after = e.clientY > rect.top + rect.height / 2;
				list.insertBefore( dragged, after ? over.nextSibling : over );
			} );
		}
	} );

	/* Colour scheme presets ------------------------------------------- */
	api( 'ni_color_scheme', function ( setting ) {
		setting.bind( function ( scheme ) {
			var colors = cfg.schemes && cfg.schemes[ scheme ];
			if ( ! colors ) {
				return;
			}
			Object.keys( colors ).forEach( function ( key ) {
				var s = api( 'ni_color_' + key );
				if ( s ) {
					s.set( colors[ key ] );
				}
			} );
			if ( api( 'ni_footer_bg' ) ) {
				api( 'ni_footer_bg' ).set( colors.dark );
			}
		} );
	} );

	/* Export / import / reset ----------------------------------------- */
	function post( task, extra ) {
		var body = new URLSearchParams();
		body.append( 'action', 'ni_customizer_tools' );
		body.append( 'nonce', cfg.nonce );
		body.append( 'task', task );
		Object.keys( extra || {} ).forEach( function ( k ) {
			body.append( k, extra[ k ] );
		} );
		return fetch( cfg.ajax, { method: 'POST', body: body, credentials: 'same-origin' } ).then( function ( r ) {
			return r.json();
		} );
	}

	api.bind( 'ready', function () {
		var wrap = document.querySelector( '[data-ni-tools]' );
		if ( ! wrap ) {
			return;
		}
		var status = wrap.querySelector( '.ni-tools__status' );
		var say = function ( msg ) {
			status.textContent = msg;
		};

		wrap.querySelector( '[data-ni-export]' ).addEventListener( 'click', function () {
			post( 'export' ).then( function ( res ) {
				if ( ! res.success ) {
					say( cfg.i18n.error );
					return;
				}
				var blob = new Blob( [ JSON.stringify( res.data, null, 2 ) ], { type: 'application/json' } );
				var a = document.createElement( 'a' );
				a.href = URL.createObjectURL( blob );
				a.download = 'nordicinterior-installningar-' + new Date().toISOString().slice( 0, 10 ) + '.json';
				document.body.appendChild( a );
				a.click();
				a.remove();
			} );
		} );

		wrap.querySelector( '[data-ni-import]' ).addEventListener( 'change', function ( e ) {
			var file = e.target.files[ 0 ];
			if ( ! file || ! window.confirm( cfg.i18n.confirmImport ) ) {
				e.target.value = '';
				return;
			}
			file.text().then( function ( text ) {
				return post( 'import', { data: text } );
			} ).then( function ( res ) {
				if ( res && res.success ) {
					say( cfg.i18n.done );
					window.location.reload();
				} else {
					say( ( res && res.data && res.data.message ) || cfg.i18n.error );
				}
			} );
		} );

		wrap.querySelector( '[data-ni-reset]' ).addEventListener( 'click', function () {
			if ( ! window.confirm( cfg.i18n.confirmReset ) ) {
				return;
			}
			post( 'reset' ).then( function ( res ) {
				if ( res.success ) {
					say( cfg.i18n.done );
					window.location.reload();
				} else {
					say( cfg.i18n.error );
				}
			} );
		} );
	} );
}( wp.customize ) );
