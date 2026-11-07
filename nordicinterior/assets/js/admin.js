/**
 * Admin helpers: media pickers for term meta / menu items and the
 * affiliate URL in product Quick Edit.
 */
( function () {
	'use strict';

	var t = window.niAdmin || {};

	/* Media picker ([data-ni-media]) */
	document.addEventListener( 'click', function ( e ) {
		var choose = e.target.closest( '[data-ni-media-choose]' );
		var remove = e.target.closest( '[data-ni-media-remove]' );
		if ( ! choose && ! remove ) {
			return;
		}
		e.preventDefault();
		var box = ( choose || remove ).closest( '[data-ni-media]' );
		var input = box.querySelector( 'input[type="hidden"]' );
		var preview = box.querySelector( '.ni-media__preview' );
		if ( remove ) {
			input.value = '';
			preview.innerHTML = '';
			input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
			return;
		}
		if ( ! window.wp || ! wp.media ) {
			return;
		}
		var frame = wp.media( {
			title: t.choose || 'Välj bild',
			button: { text: t.use || 'Använd bild' },
			library: { type: 'image' },
			multiple: false
		} );
		frame.on( 'select', function () {
			var att = frame.state().get( 'selection' ).first().toJSON();
			input.value = att.id;
			var url = ( att.sizes && att.sizes.thumbnail ) ? att.sizes.thumbnail.url : att.url;
			preview.innerHTML = '';
			var img = document.createElement( 'img' );
			img.src = url;
			img.alt = '';
			preview.appendChild( img );
			input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		} );
		frame.open();
	} );

	/* Reset term form fields after AJAX "Add term" */
	if ( window.jQuery && document.getElementById( 'addtag' ) ) {
		window.jQuery( document ).ajaxComplete( function ( event, xhr, settings ) {
			if ( settings.data && settings.data.indexOf( 'action=add-tag' ) !== -1 ) {
				Array.prototype.forEach.call( document.querySelectorAll( '#addtag [data-ni-media]' ), function ( box ) {
					box.querySelector( 'input' ).value = '';
					box.querySelector( '.ni-media__preview' ).innerHTML = '';
				} );
			}
		} );
	}

	/* Quick Edit: populate the affiliate URL */
	window.addEventListener( 'load', function () {
	if ( window.inlineEditPost ) {
		var original = window.inlineEditPost.edit;
		window.inlineEditPost.edit = function ( id ) {
			original.apply( this, arguments );
			var postId = typeof id === 'object' ? parseInt( this.getId( id ), 10 ) : id;
			var row = document.getElementById( 'post-' + postId );
			var edit = document.getElementById( 'edit-' + postId );
			if ( ! row || ! edit ) {
				return;
			}
			var holder = row.querySelector( '.ni-qe-url' );
			var input = edit.querySelector( '.ni-qe-url-input' );
			if ( holder && input ) {
				input.value = holder.getAttribute( 'data-url' ) || '';
			}
		};
	}
	} );
}() );
