/**
 * Click tracking without changing the link: when a visitor follows an
 * affiliate link (a[data-ni-click]) a beacon is sent to
 * POST /wp-json/nordicinterior/v1/click with product, placement, room and
 * style. No cookies, no personal data. The link itself is never rewritten.
 */
( function () {
	'use strict';

	var cfg = window.niConfig || {};
	if ( ! cfg.rest ) {
		return;
	}
	var endpoint = cfg.rest + 'click';
	var ctx = cfg.context || {};
	var sent = {};

	function send( a ) {
		var id = a.getAttribute( 'data-ni-click' );
		if ( ! id ) {
			return;
		}
		// De-duplicate double events (click + auxclick) within one second.
		var key = id + '|' + a.getAttribute( 'data-placement' );
		if ( sent[ key ] && Date.now() - sent[ key ] < 1000 ) {
			return;
		}
		sent[ key ] = Date.now();

		var data = new URLSearchParams();
		data.append( 'product', id );
		data.append( 'placement', ctx.source === 'email' ? 'email' : ( a.getAttribute( 'data-placement' ) || 'card' ) );
		data.append( 'room', ctx.room || a.getAttribute( 'data-room' ) || '' );
		data.append( 'style', ctx.style || a.getAttribute( 'data-style' ) || '' );
		if ( cfg.nonce ) {
			// Logged-in users: lets the server recognise (and skip) editors.
			data.append( '_wpnonce', cfg.nonce );
		}

		if ( navigator.sendBeacon ) {
			navigator.sendBeacon( endpoint, data );
		} else if ( window.fetch ) {
			fetch( endpoint, { method: 'POST', body: data, keepalive: true, credentials: 'same-origin' } );
		}
	}

	function handler( e ) {
		if ( e.type === 'auxclick' && e.button !== 1 ) {
			return;
		}
		var a = e.target.closest && e.target.closest( 'a[data-ni-click]' );
		if ( a ) {
			send( a );
		}
	}

	document.addEventListener( 'click', handler, true );
	document.addEventListener( 'auxclick', handler, true );
	// Keyboard "open in new tab" via context menu cannot be detected; Enter fires click.
}() );
