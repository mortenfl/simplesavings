/**
 * Nordic Interior – core UI: sticky header, menus, drawer, tabs, carousels,
 * copy buttons, countdowns, hero slider, product gallery, brand filter.
 * Vanilla JS, no dependencies.
 */
( function () {
	'use strict';

	var cfg = window.niConfig || { i18n: {} };
	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var desktop = window.matchMedia( '(min-width: 1024px)' );

	function $all( sel, ctx ) {
		return Array.prototype.slice.call( ( ctx || document ).querySelectorAll( sel ) );
	}

	/* ---------------------------------------------------------------
	 * Sticky, shrinking header
	 * ------------------------------------------------------------- */
	var header = document.querySelector( '[data-ni-header]' );
	if ( header && document.body.classList.contains( 'ni-sticky-header' ) ) {
		var ticking = false;
		var update = function () {
			header.classList.toggle( 'is-scrolled', window.scrollY > 40 );
			ticking = false;
		};
		window.addEventListener( 'scroll', function () {
			if ( ! ticking ) {
				window.requestAnimationFrame( update );
				ticking = true;
			}
		}, { passive: true } );
		update();
	}
	var setHeaderHeight = function () {
		if ( header ) {
			document.documentElement.style.setProperty( '--ni-header-h', header.offsetHeight + 'px' );
		}
	};
	setHeaderHeight();
	window.addEventListener( 'resize', setHeaderHeight );

	/* ---------------------------------------------------------------
	 * Submenus (disclosure pattern) – desktop dropdowns/mega and the
	 * accordion in the mobile drawer.
	 * ------------------------------------------------------------- */
	function closeSubmenus( except ) {
		$all( '.ni-menu > li.is-open' ).forEach( function ( li ) {
			if ( li !== except ) {
				li.classList.remove( 'is-open' );
				var b = li.querySelector( ':scope > [data-ni-submenu]' );
				if ( b ) {
					b.setAttribute( 'aria-expanded', 'false' );
				}
			}
		} );
	}

	document.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest( '[data-ni-submenu]' );
		if ( btn ) {
			var li = btn.parentElement;
			var open = btn.getAttribute( 'aria-expanded' ) !== 'true';
			if ( ! btn.closest( '.ni-drawer' ) ) {
				closeSubmenus( li );
			}
			btn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			li.classList.toggle( 'is-open', open );
			if ( ! btn.getAttribute( 'aria-controls' ) ) {
				var sub = li.querySelector( ':scope > .sub-menu' );
				if ( sub ) {
					sub.id = sub.id || 'ni-sub-' + Math.random().toString( 36 ).slice( 2, 8 );
					btn.setAttribute( 'aria-controls', sub.id );
				}
			}
			return;
		}
		if ( ! e.target.closest( '.ni-menu' ) ) {
			closeSubmenus();
		}
		if ( roomsPanel && ! e.target.closest( '#ni-rooms-panel' ) && ! e.target.closest( '[data-ni-rooms-toggle]' ) ) {
			toggleRooms( false );
		}
	} );

	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key !== 'Escape' ) {
			return;
		}
		var openLi = document.querySelector( '.ni-menu > li.is-open' );
		if ( openLi ) {
			closeSubmenus();
			var b = openLi.querySelector( ':scope > [data-ni-submenu]' );
			if ( b ) {
				b.focus();
			}
		}
		if ( roomsPanel && ! roomsPanel.hidden ) {
			toggleRooms( false );
			roomsBtn.focus();
		}
	} );

	// Close desktop submenus when focus leaves them.
	$all( '.ni-menu > li' ).forEach( function ( li ) {
		li.addEventListener( 'focusout', function ( e ) {
			if ( desktop.matches && ! li.contains( e.relatedTarget ) ) {
				li.classList.remove( 'is-open' );
				var b = li.querySelector( ':scope > [data-ni-submenu]' );
				if ( b ) {
					b.setAttribute( 'aria-expanded', 'false' );
				}
			}
		} );
	} );

	/* ---------------------------------------------------------------
	 * "Alla rum" panel
	 * ------------------------------------------------------------- */
	var roomsBtn = document.querySelector( '[data-ni-rooms-toggle]' );
	var roomsPanel = document.getElementById( 'ni-rooms-panel' );
	function toggleRooms( force ) {
		if ( ! roomsBtn || ! roomsPanel ) {
			return;
		}
		var open = typeof force === 'boolean' ? force : roomsPanel.hidden;
		roomsPanel.hidden = ! open;
		roomsBtn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		header.classList.toggle( 'has-panel', open );
	}
	if ( roomsBtn && roomsPanel ) {
		roomsBtn.addEventListener( 'click', function () {
			closeSubmenus();
			toggleRooms();
		} );
	}

	/* ---------------------------------------------------------------
	 * Dialog helper (drawer, filter panel): focus trap + scroll lock
	 * ------------------------------------------------------------- */
	var focusables = 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select, textarea, summary, [tabindex]:not([tabindex="-1"])';

	window.niDialog = function ( el, opener, onClose ) {
		var last = opener || document.activeElement;
		el.hidden = false;
		document.documentElement.classList.add( 'ni-lock' );
		window.requestAnimationFrame( function () {
			el.classList.add( 'is-open' );
		} );
		var first = el.querySelector( focusables );
		if ( first ) {
			first.focus();
		}
		function trap( e ) {
			if ( e.key === 'Escape' ) {
				close();
				return;
			}
			if ( e.key !== 'Tab' ) {
				return;
			}
			var items = $all( focusables, el ).filter( function ( n ) {
				return n.offsetParent !== null;
			} );
			if ( ! items.length ) {
				return;
			}
			var a = items[ 0 ];
			var z = items[ items.length - 1 ];
			if ( e.shiftKey && document.activeElement === a ) {
				e.preventDefault();
				z.focus();
			} else if ( ! e.shiftKey && document.activeElement === z ) {
				e.preventDefault();
				a.focus();
			}
		}
		function close() {
			el.classList.remove( 'is-open' );
			document.documentElement.classList.remove( 'ni-lock' );
			el.removeEventListener( 'keydown', trap );
			setTimeout( function () {
				el.hidden = true;
			}, reduceMotion ? 0 : 250 );
			if ( last && last.focus ) {
				last.focus();
			}
			if ( onClose ) {
				onClose();
			}
		}
		el.addEventListener( 'keydown', trap );
		return close;
	};

	/* Drawer */
	var drawer = document.querySelector( '[data-ni-drawer]' );
	var drawerOpen = document.querySelector( '[data-ni-drawer-open]' );
	var closeDrawer = null;
	if ( drawer && drawerOpen ) {
		drawerOpen.addEventListener( 'click', function () {
			drawerOpen.setAttribute( 'aria-expanded', 'true' );
			closeDrawer = window.niDialog( drawer, drawerOpen, function () {
				drawerOpen.setAttribute( 'aria-expanded', 'false' );
				closeDrawer = null;
			} );
		} );
		$all( '[data-ni-drawer-close]', drawer ).forEach( function ( b ) {
			b.addEventListener( 'click', function () {
				if ( closeDrawer ) {
					closeDrawer();
				}
			} );
		} );
	}

	/* ---------------------------------------------------------------
	 * Tabs (ARIA tablist with arrow keys)
	 * ------------------------------------------------------------- */
	$all( '[data-ni-tabs]' ).forEach( function ( wrap ) {
		var tabs = $all( ':scope > [role="tablist"] [role="tab"]', wrap );
		function select( tab, focus ) {
			tabs.forEach( function ( t ) {
				var on = t === tab;
				t.setAttribute( 'aria-selected', on ? 'true' : 'false' );
				t.tabIndex = on ? 0 : -1;
				var panel = document.getElementById( t.getAttribute( 'aria-controls' ) );
				if ( panel ) {
					panel.hidden = ! on;
				}
			} );
			if ( focus ) {
				tab.focus();
			}
		}
		tabs.forEach( function ( tab, i ) {
			tab.addEventListener( 'click', function () {
				select( tab );
			} );
			tab.addEventListener( 'keydown', function ( e ) {
				var n = null;
				if ( e.key === 'ArrowRight' ) {
					n = tabs[ ( i + 1 ) % tabs.length ];
				} else if ( e.key === 'ArrowLeft' ) {
					n = tabs[ ( i - 1 + tabs.length ) % tabs.length ];
				} else if ( e.key === 'Home' ) {
					n = tabs[ 0 ];
				} else if ( e.key === 'End' ) {
					n = tabs[ tabs.length - 1 ];
				}
				if ( n ) {
					e.preventDefault();
					select( n, true );
				}
			} );
		} );
	} );

	/* ---------------------------------------------------------------
	 * Carousels (scroll-snap rails with prev/next)
	 * ------------------------------------------------------------- */
	function initCarousels( ctx ) {
		$all( '[data-ni-carousel]', ctx ).forEach( function ( c ) {
			if ( c.dataset.niReady ) {
				return;
			}
			c.dataset.niReady = '1';
			var rail = c.querySelector( '.ni-rail' );
			var prev = c.querySelector( '[data-ni-prev]' );
			var next = c.querySelector( '[data-ni-next]' );
			if ( ! rail || ! prev || ! next ) {
				return;
			}
			var state = function () {
				var max = rail.scrollWidth - rail.clientWidth - 2;
				prev.disabled = rail.scrollLeft <= 2;
				next.disabled = rail.scrollLeft >= max;
				c.classList.toggle( 'is-static', max <= 0 );
			};
			var go = function ( dir ) {
				rail.scrollBy( { left: dir * rail.clientWidth * 0.9, behavior: reduceMotion ? 'auto' : 'smooth' } );
			};
			prev.addEventListener( 'click', function () {
				go( -1 );
			} );
			next.addEventListener( 'click', function () {
				go( 1 );
			} );
			rail.addEventListener( 'scroll', function () {
				window.requestAnimationFrame( state );
			}, { passive: true } );
			window.addEventListener( 'resize', state );
			state();
		} );
	}
	initCarousels( document );
	window.niInitCarousels = initCarousels;

	/* ---------------------------------------------------------------
	 * Copy discount codes
	 * ------------------------------------------------------------- */
	document.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest( '[data-ni-copy]' );
		if ( ! btn ) {
			return;
		}
		var text = btn.getAttribute( 'data-ni-copy' );
		var label = btn.querySelector( 'span' );
		var done = function () {
			if ( label ) {
				label.textContent = cfg.i18n.copied || 'Kopierad!';
				setTimeout( function () {
					label.textContent = cfg.i18n.copy || 'Kopiera';
				}, 2000 );
			}
		};
		if ( navigator.clipboard && window.isSecureContext ) {
			navigator.clipboard.writeText( text ).then( done );
		} else {
			var ta = document.createElement( 'textarea' );
			ta.value = text;
			ta.setAttribute( 'readonly', '' );
			ta.style.position = 'absolute';
			ta.style.left = '-9999px';
			document.body.appendChild( ta );
			ta.select();
			try {
				document.execCommand( 'copy' );
				done();
			} catch ( err ) {}
			document.body.removeChild( ta );
		}
	} );

	/* ---------------------------------------------------------------
	 * Discreet countdowns ("Slutar om 3 d 4 h")
	 * ------------------------------------------------------------- */
	function tick() {
		$all( '[data-ni-countdown]' ).forEach( function ( el ) {
			var end = Date.parse( el.getAttribute( 'data-ni-countdown' ) );
			var out = el.querySelector( '.ni-countdown__text' );
			if ( ! end || ! out ) {
				return;
			}
			var diff = end - Date.now();
			if ( diff <= 0 ) {
				out.textContent = cfg.i18n.ended || 'Kampanjen har avslutats';
				return;
			}
			var d = Math.floor( diff / 864e5 );
			var h = Math.floor( ( diff % 864e5 ) / 36e5 );
			var m = Math.floor( ( diff % 36e5 ) / 6e4 );
			out.textContent = 'Slutar om ' + ( d > 0 ? d + ' d ' + h + ' h' : h + ' h ' + m + ' min' );
		} );
	}
	if ( document.querySelector( '[data-ni-countdown]' ) ) {
		tick();
		setInterval( tick, 60000 );
	}

	/* ---------------------------------------------------------------
	 * Hero slider (crossfade, pausable, no autoplay with reduced motion)
	 * ------------------------------------------------------------- */
	$all( '[data-ni-slider]' ).forEach( function ( hero ) {
		var slides = $all( '.ni-hero__slide', hero );
		var toggle = hero.querySelector( '[data-ni-slider-toggle]' );
		var i = 0;
		var timer = null;
		var show = function ( n ) {
			slides[ i ].classList.remove( 'is-active' );
			slides[ i ].setAttribute( 'aria-hidden', 'true' );
			i = n % slides.length;
			slides[ i ].classList.add( 'is-active' );
			slides[ i ].removeAttribute( 'aria-hidden' );
		};
		var play = function () {
			timer = setInterval( function () {
				show( i + 1 );
			}, 6500 );
			if ( toggle ) {
				toggle.setAttribute( 'aria-pressed', 'false' );
				toggle.classList.remove( 'is-paused' );
			}
		};
		var pause = function () {
			clearInterval( timer );
			timer = null;
			if ( toggle ) {
				toggle.setAttribute( 'aria-pressed', 'true' );
				toggle.classList.add( 'is-paused' );
			}
		};
		if ( toggle ) {
			toggle.addEventListener( 'click', function () {
				if ( timer ) {
					pause();
				} else {
					play();
				}
			} );
		}
		if ( reduceMotion ) {
			pause();
		} else {
			play();
		}
	} );

	/* ---------------------------------------------------------------
	 * Product gallery: thumbnails scroll the snap strip
	 * ------------------------------------------------------------- */
	$all( '[data-ni-gallery]' ).forEach( function ( g ) {
		var strip = g.querySelector( '.ni-product__slides' );
		var thumbs = $all( '[data-ni-thumb]', g );
		if ( ! strip ) {
			return;
		}
		thumbs.forEach( function ( t ) {
			t.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				var n = parseInt( t.getAttribute( 'data-ni-thumb' ), 10 );
				strip.scrollTo( { left: n * strip.clientWidth, behavior: reduceMotion ? 'auto' : 'smooth' } );
			} );
		} );
		strip.addEventListener( 'scroll', function () {
			var n = Math.round( strip.scrollLeft / Math.max( 1, strip.clientWidth ) );
			thumbs.forEach( function ( t, i ) {
				if ( i === n ) {
					t.setAttribute( 'aria-current', 'true' );
				} else {
					t.removeAttribute( 'aria-current' );
				}
			} );
		}, { passive: true } );
	} );

	/* ---------------------------------------------------------------
	 * Brand A–Ö filter
	 * ------------------------------------------------------------- */
	$all( '[data-ni-brand-filter]' ).forEach( function ( wrap ) {
		var input = wrap.querySelector( '[data-ni-brand-input]' );
		var status = wrap.querySelector( '[data-ni-brand-status]' );
		if ( ! input ) {
			return;
		}
		input.addEventListener( 'input', function () {
			var q = input.value.trim().toLowerCase();
			var shown = 0;
			$all( '.ni-letter-group', wrap ).forEach( function ( group ) {
				var any = false;
				$all( 'li[data-name]', group ).forEach( function ( li ) {
					var on = ! q || li.getAttribute( 'data-name' ).indexOf( q ) !== -1;
					li.hidden = ! on;
					if ( on ) {
						any = true;
						shown++;
					}
				} );
				group.hidden = ! any;
			} );
			if ( status ) {
				status.textContent = shown + ' varumärken';
			}
		} );
	} );
}() );
