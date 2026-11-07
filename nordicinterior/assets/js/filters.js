/**
 * AJAX product filters. The filter form is a normal GET form; this script
 * fetches the same URL with ?ni_partial=1 and swaps the results fragment,
 * keeps the address bar in sync (pushState) and handles "Ladda fler".
 * On mobile (and in "drawer" mode) the filters live in a slide-in panel.
 */
( function () {
	'use strict';

	var cfg = window.niConfig || { i18n: {} };
	var listing = document.querySelector( '[data-ni-listing]' );
	if ( ! listing || ! window.fetch ) {
		return;
	}
	var form = listing.querySelector( '[data-ni-filters]' );
	var panel = listing.querySelector( '[data-ni-filter-panel]' );
	var closePanel = null;
	var controller = null;
	var drawerMode = window.matchMedia( '(max-width: 1023px)' );

	function isDrawer() {
		return drawerMode.matches || listing.classList.contains( 'ni-listing--filters-drawer' );
	}

	/** Build a clean URL from the form: arrays become comma lists. */
	function formUrl() {
		var action = form.getAttribute( 'action' ) || window.location.pathname;
		var data = new FormData( form );
		var params = new URLSearchParams();
		var lists = {};
		data.forEach( function ( value, key ) {
			if ( value === '' || key === 'sortera' ) {
				return; // Sorting is added below, only when not default.
			}
			if ( key.slice( -2 ) === '[]' ) {
				var k = key.slice( 0, -2 );
				( lists[ k ] = lists[ k ] || [] ).push( value );
			} else {
				params.set( key, value );
			}
		} );
		Object.keys( lists ).forEach( function ( k ) {
			params.set( k, lists[ k ].join( ',' ) );
		} );
		var sort = listing.querySelector( '[data-ni-sort]' );
		if ( sort && sort.value && sort.value !== sort.options[ 0 ].value ) {
			params.set( 'sortera', sort.value );
		}
		var qs = params.toString();
		return action + ( qs ? ( action.indexOf( '?' ) === -1 ? '?' : '&' ) + qs : '' );
	}

	function partialUrl( url ) {
		var u = new URL( url, window.location.href );
		u.searchParams.set( 'ni_partial', '1' );
		return u.toString();
	}

	function results() {
		return listing.querySelector( '[data-ni-results]' );
	}

	function load( url, opts ) {
		opts = opts || {};
		var box = results();
		if ( controller ) {
			controller.abort();
		}
		controller = window.AbortController ? new AbortController() : null;
		box.setAttribute( 'aria-busy', 'true' );
		listing.classList.add( 'is-loading' );

		return fetch( partialUrl( url ), { credentials: 'same-origin', signal: controller ? controller.signal : undefined } )
			.then( function ( r ) {
				if ( ! r.ok ) {
					throw new Error( r.status );
				}
				return r.text();
			} )
			.then( function ( html ) {
				var doc = new DOMParser().parseFromString( html, 'text/html' );
				var fresh = doc.querySelector( '[data-ni-results]' );
				if ( ! fresh ) {
					throw new Error( 'fragment' );
				}
				if ( opts.append ) {
					var grid = box.querySelector( '[data-ni-grid]' );
					var newGrid = fresh.querySelector( '[data-ni-grid]' );
					if ( grid && newGrid ) {
						var firstNew = newGrid.firstElementChild;
						while ( newGrid.firstElementChild ) {
							grid.appendChild( newGrid.firstElementChild );
						}
						var pag = box.querySelector( '[data-ni-pagination]' );
						var newPag = fresh.querySelector( '[data-ni-pagination]' );
						if ( pag && newPag ) {
							pag.innerHTML = newPag.innerHTML;
						}
						// Move focus to the first new product for keyboard users.
						var link = firstNew && firstNew.querySelector( '.ni-card__title a' );
						if ( link ) {
							link.focus( { preventScroll: true } );
						}
					}
				} else {
					box.innerHTML = fresh.innerHTML;
					if ( opts.scroll ) {
						var top = listing.getBoundingClientRect().top + window.scrollY - 120;
						if ( window.scrollY > top ) {
							window.scrollTo( { top: top } );
						}
					}
				}
				if ( opts.push !== false ) {
					history.pushState( { niFilters: true }, '', url );
				}
				syncForm( url );
				if ( window.niInitCarousels ) {
					window.niInitCarousels( box );
				}
			} )
			.catch( function ( err ) {
				if ( err && err.name === 'AbortError' ) {
					return;
				}
				window.location.href = url; // Graceful fallback: full page load.
			} )
			.then( function () {
				box.setAttribute( 'aria-busy', 'false' );
				listing.classList.remove( 'is-loading' );
			} );
	}

	/** Reflect URL state in the form (after chip removal / back button). */
	function syncForm( url ) {
		var params = new URL( url, window.location.href ).searchParams;
		Array.prototype.forEach.call( form.elements, function ( el ) {
			if ( ! el.name ) {
				return;
			}
			var name = el.name.replace( /\[\]$/, '' );
			if ( el.type === 'checkbox' ) {
				var list = ( params.get( name ) || '' ).split( ',' );
				el.checked = el.name.slice( -2 ) === '[]' ? list.indexOf( el.value ) !== -1 : params.get( name ) === el.value;
			} else if ( el.type === 'number' ) {
				el.value = params.get( name ) || '';
			}
		} );
		// Update the badges in summaries.
		Array.prototype.forEach.call( form.querySelectorAll( 'details.ni-filter' ), function ( d ) {
			var n = d.querySelectorAll( 'input[type="checkbox"]:checked' ).length;
			var badge = d.querySelector( '.ni-badge' );
			if ( n && ! badge ) {
				badge = document.createElement( 'span' );
				badge.className = 'ni-badge';
				d.querySelector( 'summary' ).insertBefore( badge, d.querySelector( 'summary svg' ) );
			}
			if ( badge ) {
				if ( n ) {
					badge.textContent = n;
				} else {
					badge.remove();
				}
			}
		} );
	}

	if ( form ) {
		// Live update on change (checkboxes immediately, numbers on change).
		form.addEventListener( 'change', function ( e ) {
			if ( e.target.type === 'number' && e.target.value !== '' && ! e.target.checkValidity() ) {
				return;
			}
			load( formUrl(), { scroll: ! isDrawer() } );
		} );
		form.addEventListener( 'submit', function ( e ) {
			e.preventDefault();
			load( formUrl(), { scroll: true } ).then( function () {
				if ( closePanel ) {
					closePanel();
				}
			} );
		} );
	}

	// Sort select (outside the form element, linked with form="").
	listing.addEventListener( 'change', function ( e ) {
		if ( e.target.matches( '[data-ni-sort]' ) ) {
			e.stopPropagation();
			load( formUrl() );
		}
	} );

	// Chips, view toggle, clear and pagination links.
	listing.addEventListener( 'click', function ( e ) {
		var a = e.target.closest( 'a[data-ni-filter-link], .ni-pagination a' );
		if ( a && listing.contains( a ) ) {
			e.preventDefault();
			load( a.href, { scroll: true } );
			return;
		}
		var more = e.target.closest( '[data-ni-loadmore]' );
		if ( more ) {
			e.preventDefault();
			more.classList.add( 'is-loading' );
			more.textContent = cfg.i18n.loading || 'Laddar …';
			load( more.href, { append: true } );
		}
	} );

	window.addEventListener( 'popstate', function () {
		load( window.location.href, { push: false } );
	} );

	// Filter panel (drawer on mobile / drawer mode).
	var openBtn = listing.querySelector( '[data-ni-filters-open]' );
	listing.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest( '[data-ni-filters-open]' );
		if ( btn && panel ) {
			if ( isDrawer() ) {
				btn.setAttribute( 'aria-expanded', 'true' );
				panel.setAttribute( 'role', 'dialog' );
				panel.setAttribute( 'aria-modal', 'true' );
				panel.setAttribute( 'aria-label', cfg.i18n.filtersOpen || 'Filter' );
				closePanel = window.niDialog( panel, btn, function () {
					btn.setAttribute( 'aria-expanded', 'false' );
					closePanel = null;
				} );
			} else {
				var first = panel.querySelector( 'summary, input' );
				if ( first ) {
					first.focus();
				}
			}
		}
		if ( e.target.closest( '[data-ni-filters-close]' ) && closePanel ) {
			closePanel();
		}
	} );

	// Desktop (non-drawer): the panel is always visible.
	function layout() {
		if ( ! panel ) {
			return;
		}
		if ( isDrawer() ) {
			if ( ! panel.classList.contains( 'is-open' ) ) {
				panel.hidden = true;
			}
		} else {
			panel.hidden = false;
			panel.removeAttribute( 'role' );
			panel.removeAttribute( 'aria-modal' );
			if ( closePanel ) {
				closePanel();
			}
			panel.hidden = false;
		}
	}
	layout();
	if ( drawerMode.addEventListener ) {
		drawerMode.addEventListener( 'change', layout );
	}
	if ( openBtn ) {
		openBtn.hidden = false;
	}
}() );
