/**
 * Block editor UI for "Shoppa looken" (no build step: wp.element.createElement).
 * Choose a room image, click on the image to add a point, then enter the
 * product ID for each point. Points can be nudged with the X/Y fields.
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var __ = wp.i18n.__;
	var be = wp.blockEditor;
	var c = wp.components;
	var ServerSideRender = wp.serverSideRender;

	wp.blocks.registerBlockType( 'nordicinterior/shop-the-look', {
		apiVersion: 3,
		title: __( 'Shoppa looken', 'nordicinterior' ),
		icon: 'location',
		category: 'media',
		supports: { align: [ 'wide', 'full' ], html: false },
		attributes: {
			image: { type: 'number', default: 0 },
			title: { type: 'string', default: '' },
			points: { type: 'array', default: [] },
			align: { type: 'string', default: '' }
		},
		edit: function ( props ) {
			var a = props.attributes;
			var set = props.setAttributes;
			var blockProps = be.useBlockProps ? be.useBlockProps() : {};
			var media = wp.data.useSelect( function ( select ) {
				return a.image ? select( 'core' ).getMedia( a.image ) : null;
			}, [ a.image ] );

			function updatePoint( i, key, value ) {
				var pts = a.points.map( function ( p, j ) {
					if ( j !== i ) {
						return p;
					}
					var copy = Object.assign( {}, p );
					copy[ key ] = key === 'id' ? parseInt( value, 10 ) || 0 : Math.max( 0, Math.min( 100, parseFloat( value ) || 0 ) );
					return copy;
				} );
				set( { points: pts } );
			}

			function addPoint( e ) {
				var rect = e.currentTarget.getBoundingClientRect();
				var x = Math.round( ( ( e.clientX - rect.left ) / rect.width ) * 1000 ) / 10;
				var y = Math.round( ( ( e.clientY - rect.top ) / rect.height ) * 1000 ) / 10;
				set( { points: a.points.concat( [ { id: 0, x: x, y: y } ] ) } );
			}

			var sidebar = el( be.InspectorControls, {},
				el( c.PanelBody, { title: __( 'Looken', 'nordicinterior' ), initialOpen: true },
					el( c.TextControl, {
						label: __( 'Bildtext (valfri)', 'nordicinterior' ),
						value: a.title,
						onChange: function ( v ) {
							set( { title: v } );
						}
					} ),
					a.points.map( function ( p, i ) {
						return el( 'div', { key: i, className: 'ni-look-editor__point' },
							el( 'strong', {}, __( 'Punkt', 'nordicinterior' ) + ' ' + ( i + 1 ) ),
							el( c.TextControl, {
								label: __( 'Produkt-ID', 'nordicinterior' ),
								type: 'number',
								value: p.id || '',
								onChange: function ( v ) {
									updatePoint( i, 'id', v );
								}
							} ),
							el( c.TextControl, {
								label: 'X %',
								type: 'number',
								value: p.x,
								onChange: function ( v ) {
									updatePoint( i, 'x', v );
								}
							} ),
							el( c.TextControl, {
								label: 'Y %',
								type: 'number',
								value: p.y,
								onChange: function ( v ) {
									updatePoint( i, 'y', v );
								}
							} ),
							el( c.Button, {
								isDestructive: true,
								variant: 'link',
								onClick: function () {
									set( { points: a.points.filter( function ( q, j ) {
										return j !== i;
									} ) } );
								}
							}, __( 'Ta bort punkt', 'nordicinterior' ) )
						);
					} )
				)
			);

			var picker = el( be.MediaUploadCheck, {},
				el( be.MediaUpload, {
					onSelect: function ( m ) {
						set( { image: m.id } );
					},
					allowedTypes: [ 'image' ],
					value: a.image,
					render: function ( o ) {
						return el( c.Button, { variant: 'secondary', onClick: o.open }, a.image ? __( 'Byt bild', 'nordicinterior' ) : __( 'Välj rumsbild', 'nordicinterior' ) );
					}
				} )
			);

			var stage = null;
			if ( media && media.source_url ) {
				stage = el( 'div', { className: 'ni-look-editor' },
					el( 'p', { className: 'components-base-control__help' }, __( 'Klicka i bilden för att lägga till en produktpunkt. Ange produkt-ID i sidopanelen.', 'nordicinterior' ) ),
					el( 'div', {
						className: 'ni-look-editor__stage',
						onClick: addPoint,
						style: { position: 'relative', cursor: 'crosshair' }
					},
					el( 'img', { src: media.source_url, alt: '', style: { display: 'block', width: '100%' } } ),
					a.points.map( function ( p, i ) {
						return el( 'span', {
							key: i,
							className: 'ni-look-editor__dot' + ( p.id ? '' : ' is-empty' ),
							style: { left: p.x + '%', top: p.y + '%' }
						}, i + 1 );
					} ) )
				);
			}

			return el( Fragment, {},
				sidebar,
				el( 'div', blockProps,
					el( 'div', { className: 'ni-look-editor__toolbar' }, picker ),
					stage,
					a.image && a.points.some( function ( p ) {
						return p.id;
					} ) && ServerSideRender
						? el( c.Disabled, {}, el( ServerSideRender, { block: 'nordicinterior/shop-the-look', attributes: a } ) )
						: null
				)
			);
		},
		save: function () {
			return null; // Rendered in PHP.
		}
	} );
}( window.wp ) );
