/**
 * Live search with an ARIA 1.2 combobox (input + listbox).
 * Suggestions are grouped: products, categories, rooms, styles, brands and
 * articles. Arrow keys move, Enter opens, Escape closes.
 */
( function () {
	'use strict';

	var cfg = window.niConfig || { i18n: {} };
	var i18n = cfg.i18n || {};
	if ( ! cfg.rest ) {
		return;
	}

	function esc( s ) {
		return String( s == null ? '' : s ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ];
		} );
	}

	function debounce( fn, ms ) {
		var t;
		return function () {
			var args = arguments;
			clearTimeout( t );
			t = setTimeout( function () {
				fn.apply( null, args );
			}, ms );
		};
	}

	Array.prototype.forEach.call( document.querySelectorAll( '[data-ni-search]' ), function ( form ) {
		var input = form.querySelector( 'input[type="search"]' );
		var panel = form.querySelector( '[role="listbox"]' );
		var status = form.querySelector( '[data-ni-search-status]' );
		if ( ! input || ! panel ) {
			return;
		}
		var active = -1;
		var controller = null;
		var cache = {};

		function options() {
			return Array.prototype.slice.call( panel.querySelectorAll( '[role="option"]' ) );
		}

		function setActive( i ) {
			var opts = options();
			opts.forEach( function ( o ) {
				o.setAttribute( 'aria-selected', 'false' );
				o.classList.remove( 'is-active' );
			} );
			active = i;
			if ( i >= 0 && opts[ i ] ) {
				opts[ i ].setAttribute( 'aria-selected', 'true' );
				opts[ i ].classList.add( 'is-active' );
				input.setAttribute( 'aria-activedescendant', opts[ i ].id );
				opts[ i ].scrollIntoView( { block: 'nearest' } );
			} else {
				input.removeAttribute( 'aria-activedescendant' );
			}
		}

		function open( on ) {
			panel.hidden = ! on;
			input.setAttribute( 'aria-expanded', on ? 'true' : 'false' );
			form.classList.toggle( 'is-open', on );
			if ( ! on ) {
				setActive( -1 );
			}
		}

		function render( data ) {
			var html = '';
			var n = 0;
			var uid = input.id;
			if ( data.filters && data.filters.length ) {
				html += '<div class="ni-search__filters" role="presentation">' + esc( data.filters.join( ' · ' ) ) + '</div>';
			}
			( data.groups || [] ).forEach( function ( g ) {
				var label = ( i18n.groups && i18n.groups[ g.type ] ) || g.type;
				html += '<div class="ni-search__group" role="group" aria-label="' + esc( label ) + '"><div class="ni-search__group-title" role="presentation">' + esc( label ) + ( g.total ? ' <span>(' + g.total + ')</span>' : '' ) + '</div>';
				g.items.forEach( function ( it ) {
					var id = uid + '-opt-' + n++;
					html += '<a class="ni-search__option ni-search__option--' + esc( g.type ) + '" role="option" id="' + id + '" aria-selected="false" tabindex="-1" href="' + esc( it.url ) + '">';
					if ( it.image ) {
						html += '<img src="' + esc( it.image ) + '" alt="" width="48" height="48" loading="lazy">';
					}
					html += '<span class="ni-search__text"><span class="ni-search__title">' + esc( it.title ) + '</span>';
					if ( it.meta ) {
						html += '<span class="ni-search__meta">' + esc( it.meta ) + '</span>';
					}
					html += '</span>';
					if ( it.price ) {
						html += '<span class="ni-search__price">' + esc( it.price ) + '</span>';
					}
					html += '</a>';
				} );
				html += '</div>';
			} );
			if ( ! n ) {
				html += '<p class="ni-search__empty" role="presentation">';
				if ( data.didYouMean ) {
					var url = cfg.searchUrl + '?s=' + encodeURIComponent( data.didYouMean ) + '&post_type=product';
					var id2 = uid + '-opt-' + n++;
					html += esc( i18n.didYouMean || 'Menade du' ) + ' <a role="option" id="' + id2 + '" aria-selected="false" tabindex="-1" class="ni-search__option" href="' + esc( url ) + '">' + esc( data.didYouMean ) + '</a>?';
				} else {
					html += esc( i18n.noResults || '' );
				}
				html += '</p>';
			}
			var all = uid + '-opt-' + n++;
			html += '<a class="ni-search__all ni-search__option" role="option" id="' + all + '" aria-selected="false" tabindex="-1" href="' + esc( data.url ) + '">' + esc( ( i18n.seeAll || 'Visa alla resultat för ”%s”' ).replace( '%s', data.query ) ) + '</a>';
			panel.innerHTML = html;
			setActive( -1 );
			open( true );
			if ( status ) {
				status.textContent = ( i18n.results || '%d förslag' ).replace( '%d', n - 1 );
			}
		}

		var fetchSuggestions = debounce( function ( q ) {
			if ( cache[ q ] ) {
				render( cache[ q ] );
				return;
			}
			if ( controller ) {
				controller.abort();
			}
			controller = window.AbortController ? new AbortController() : null;
			form.classList.add( 'is-loading' );
			fetch( cfg.rest + 'search?q=' + encodeURIComponent( q ), { signal: controller ? controller.signal : undefined, credentials: 'same-origin' } )
				.then( function ( r ) {
					return r.json();
				} )
				.then( function ( data ) {
					cache[ q ] = data;
					if ( input.value.trim() === q ) {
						render( data );
					}
				} )
				.catch( function () {} )
				.then( function () {
					form.classList.remove( 'is-loading' );
				} );
		}, 180 );

		input.addEventListener( 'input', function () {
			var q = input.value.trim();
			if ( q.length < 2 ) {
				open( false );
				return;
			}
			fetchSuggestions( q );
		} );

		input.addEventListener( 'keydown', function ( e ) {
			var opts = options();
			if ( e.key === 'ArrowDown' ) {
				e.preventDefault();
				if ( panel.hidden && input.value.trim().length >= 2 ) {
					fetchSuggestions( input.value.trim() );
					return;
				}
				setActive( Math.min( opts.length - 1, active + 1 ) );
			} else if ( e.key === 'ArrowUp' ) {
				e.preventDefault();
				setActive( Math.max( -1, active - 1 ) );
			} else if ( e.key === 'Enter' ) {
				if ( active >= 0 && opts[ active ] ) {
					e.preventDefault();
					window.location.href = opts[ active ].href;
				}
			} else if ( e.key === 'Escape' ) {
				if ( ! panel.hidden ) {
					e.preventDefault();
					open( false );
				} else {
					input.value = '';
				}
			} else if ( e.key === 'Home' || e.key === 'End' ) {
				setActive( -1 );
			}
		} );

		// Keep focus on the input when choosing with the mouse.
		panel.addEventListener( 'mousedown', function ( e ) {
			if ( e.target.closest( '[role="option"]' ) ) {
				e.preventDefault();
			}
		} );

		input.addEventListener( 'blur', function () {
			setTimeout( function () {
				if ( ! form.contains( document.activeElement ) ) {
					open( false );
				}
			}, 150 );
		} );

		document.addEventListener( 'click', function ( e ) {
			if ( ! form.contains( e.target ) ) {
				open( false );
			}
		} );
	} );
}() );
