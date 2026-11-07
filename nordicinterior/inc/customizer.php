<?php
/**
 * Theme Adjustments (Customizer): one schema drives registration,
 * sanitisation, live preview, export, import and reset.
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sections of the Theme Adjustments panel.
 *
 * @return array<string,string>
 */
function ni_customizer_sections() {
	return array(
		'colors'      => __( 'Färger', 'nordicinterior' ),
		'typography'  => __( 'Typografi', 'nordicinterior' ),
		'form'        => __( 'Form', 'nordicinterior' ),
		'header'      => __( 'Header', 'nordicinterior' ),
		'footer'      => __( 'Sidfot', 'nordicinterior' ),
		'products'    => __( 'Produkter', 'nordicinterior' ),
		'search'      => __( 'Sök', 'nordicinterior' ),
		'affiliate'   => __( 'Affiliate', 'nordicinterior' ),
		'home'        => __( 'Startsida', 'nordicinterior' ),
		'posts'       => __( 'Inlägg', 'nordicinterior' ),
		'performance' => __( 'Prestanda', 'nordicinterior' ),
		'tools'       => __( 'Export, import och återställning', 'nordicinterior' ),
	);
}

/**
 * The full settings schema.
 *
 * Keys are stored as theme mods "ni_{key}". "css" marks settings that are
 * previewed live through CSS custom properties (postMessage).
 *
 * @return array<string, array>
 */
function ni_customizer_schema() {
	$yes_no = array(
		'type' => 'checkbox',
	);

	$schemes = array();
	foreach ( ni_color_schemes() as $key => $scheme ) {
		$schemes[ $key ] = $scheme['label'];
	}

	$fonts_heading = array();
	$fonts_body    = array();
	foreach ( ni_fonts() as $key => $font ) {
		if ( in_array( $key, array( 'jost', 'system' ), true ) ) {
			$fonts_heading[ $key ] = $font['label'];
		}
		if ( in_array( $key, array( 'inter', 'dmsans', 'system' ), true ) ) {
			$fonts_body[ $key ] = $font['label'];
		}
	}

	$schema = array(
		// Colours.
		'color_scheme' => array(
			'section' => 'colors',
			'type'    => 'select',
			'label'   => __( 'Färgschema', 'nordicinterior' ),
			'choices' => $schemes,
		),
	);

	$color_labels = array(
		'ink'     => __( 'Bläck (primär/rubriker)', 'nordicinterior' ),
		'text'    => __( 'Text', 'nordicinterior' ),
		'muted'   => __( 'Dämpad text', 'nordicinterior' ),
		'linen'   => __( 'Linne', 'nordicinterior' ),
		'oat'     => __( 'Havre (bakgrund)', 'nordicinterior' ),
		'surface' => __( 'Yta', 'nordicinterior' ),
		'line'    => __( 'Linje', 'nordicinterior' ),
		'sage'    => __( 'Salvia (accent)', 'nordicinterior' ),
		'oak'     => __( 'Ek (länk/hover)', 'nordicinterior' ),
		'clay'    => __( 'Lera (rea)', 'nordicinterior' ),
		'dark'    => __( 'Mörk sektion', 'nordicinterior' ),
	);
	foreach ( $color_labels as $key => $label ) {
		$schema[ 'color_' . $key ] = array(
			'section'     => 'colors',
			'type'        => 'color',
			'label'       => $label,
			'css'         => '--ni-' . $key,
			'default'     => '',
			'description' => 'ink' === $key ? __( 'Lämna tomt för att använda färgschemats värde.', 'nordicinterior' ) : '',
		);
	}

	$schema += array(
		// Typography.
		'font_heading'           => array(
			'section' => 'typography',
			'type'    => 'select',
			'label'   => __( 'Rubriktypsnitt (lokalt)', 'nordicinterior' ),
			'choices' => $fonts_heading,
		),
		'font_body'              => array(
			'section' => 'typography',
			'type'    => 'select',
			'label'   => __( 'Brödtext (lokalt)', 'nordicinterior' ),
			'choices' => $fonts_body,
		),
		'font_size'              => array(
			'section' => 'typography',
			'type'    => 'range',
			'label'   => __( 'Grundstorlek (px)', 'nordicinterior' ),
			'min'     => 15,
			'max'     => 20,
			'step'    => 1,
			'css'     => '--ni-font-size',
			'unit'    => 'px',
		),
		'type_scale'             => array(
			'section' => 'typography',
			'type'    => 'select',
			'label'   => __( 'Skala', 'nordicinterior' ),
			'choices' => array(
				'1.2'   => __( 'Liten (1,2)', 'nordicinterior' ),
				'1.25'  => __( 'Normal (1,25)', 'nordicinterior' ),
				'1.333' => __( 'Stor (1,333)', 'nordicinterior' ),
			),
			'css'     => '--ni-scale',
		),
		'line_height'            => array(
			'section' => 'typography',
			'type'    => 'range',
			'label'   => __( 'Radavstånd', 'nordicinterior' ),
			'min'     => 1.4,
			'max'     => 1.9,
			'step'    => 0.05,
			'css'     => '--ni-line-height',
		),
		'heading_tracking'       => array(
			'section' => 'typography',
			'type'    => 'range',
			'label'   => __( 'Teckenavstånd rubriker (em)', 'nordicinterior' ),
			'min'     => 0,
			'max'     => 0.2,
			'step'    => 0.01,
			'css'     => '--ni-heading-tracking',
			'unit'    => 'em',
		),
		'heading_uppercase'      => array_merge(
			$yes_no,
			array(
				'section' => 'typography',
				'label'   => __( 'Versaler i huvudrubriker', 'nordicinterior' ),
			)
		),
		// Form.
		'radius'                 => array(
			'section' => 'form',
			'type'    => 'range',
			'label'   => __( 'Hörnradie (px)', 'nordicinterior' ),
			'min'     => 0,
			'max'     => 8,
			'step'    => 1,
			'css'     => '--ni-radius',
			'unit'    => 'px',
		),
		'borders'                => array_merge(
			$yes_no,
			array(
				'section' => 'form',
				'label'   => __( 'Tunna kantlinjer', 'nordicinterior' ),
			)
		),
		'spacing'                => array(
			'section' => 'form',
			'type'    => 'select',
			'label'   => __( 'Luft', 'nordicinterior' ),
			'choices' => array(
				'compact' => __( 'Kompakt', 'nordicinterior' ),
				'normal'  => __( 'Normal', 'nordicinterior' ),
				'airy'    => __( 'Luftig', 'nordicinterior' ),
			),
		),
		'button_style'           => array(
			'section' => 'form',
			'type'    => 'select',
			'label'   => __( 'Knappstil', 'nordicinterior' ),
			'choices' => array(
				'filled'  => __( 'Fylld', 'nordicinterior' ),
				'outline' => __( 'Kontur', 'nordicinterior' ),
				'text'    => __( 'Textlänk med pil', 'nordicinterior' ),
			),
		),
		// Header.
		'header_layout'          => array(
			'section' => 'header',
			'type'    => 'select',
			'label'   => __( 'Layout', 'nordicinterior' ),
			'choices' => array(
				'left'   => __( 'Logotyp till vänster, meny till höger', 'nordicinterior' ),
				'center' => __( 'Centrerad logotyp', 'nordicinterior' ),
			),
		),
		'header_sticky'          => array_merge(
			$yes_no,
			array(
				'section' => 'header',
				'label'   => __( 'Sticky och krympande header', 'nordicinterior' ),
			)
		),
		'logo_variant'           => array(
			'section' => 'header',
			'type'    => 'select',
			'label'   => __( 'Ordbild', 'nordicinterior' ),
			'choices' => array(
				'two-rows' => __( 'Två rader', 'nordicinterior' ),
				'oneline'  => __( 'En rad', 'nordicinterior' ),
			),
		),
		'logo_width'             => array(
			'section' => 'header',
			'type'    => 'range',
			'label'   => __( 'Logobredd (px)', 'nordicinterior' ),
			'min'     => 120,
			'max'     => 320,
			'step'    => 5,
			'css'     => '--ni-logo-width',
			'unit'    => 'px',
		),
		'rooms_button'           => array_merge(
			$yes_no,
			array(
				'section' => 'header',
				'label'   => __( 'Visa knappen för rum-menyn', 'nordicinterior' ),
			)
		),
		'rooms_button_text'      => array(
			'section' => 'header',
			'type'    => 'text',
			'label'   => __( 'Knapptext för rum-menyn', 'nordicinterior' ),
		),
		'topbar'                 => array_merge(
			$yes_no,
			array(
				'section' => 'header',
				'label'   => __( 'Visa toppfält', 'nordicinterior' ),
			)
		),
		'topbar_text'            => array(
			'section' => 'header',
			'type'    => 'text',
			'label'   => __( 'Toppfält – USP 1', 'nordicinterior' ),
		),
		'topbar_text_2'          => array(
			'section' => 'header',
			'type'    => 'text',
			'label'   => __( 'Toppfält – USP 2', 'nordicinterior' ),
		),
		'bottom_nav'             => array_merge(
			$yes_no,
			array(
				'section' => 'header',
				'label'   => __( 'Bottennavigering på mobil (Hem, Rum, Varumärken, Rea)', 'nordicinterior' ),
			)
		),
		// Footer.
		'footer_bg'              => array(
			'section' => 'footer',
			'type'    => 'color',
			'label'   => __( 'Bakgrund', 'nordicinterior' ),
			'css'     => '--ni-footer-bg',
		),
		'footer_text'            => array(
			'section' => 'footer',
			'type'    => 'color',
			'label'   => __( 'Text', 'nordicinterior' ),
			'css'     => '--ni-footer-text',
		),
		'copyright'              => array(
			'section'     => 'footer',
			'type'        => 'text',
			'label'       => __( 'Copyright', 'nordicinterior' ),
			'description' => __( '{year} ersätts med aktuellt år.', 'nordicinterior' ),
		),
		'social_instagram'       => array(
			'section' => 'footer',
			'type'    => 'url',
			'label'   => 'Instagram',
		),
		'social_pinterest'       => array(
			'section' => 'footer',
			'type'    => 'url',
			'label'   => 'Pinterest',
		),
		'social_facebook'        => array(
			'section' => 'footer',
			'type'    => 'url',
			'label'   => 'Facebook',
		),
		'social_tiktok'          => array(
			'section' => 'footer',
			'type'    => 'url',
			'label'   => 'TikTok',
		),
		// Products.
		'card_style'             => array(
			'section' => 'products',
			'type'    => 'select',
			'label'   => __( 'Kortstil', 'nordicinterior' ),
			'choices' => array(
				'minimal' => __( 'Minimal', 'nordicinterior' ),
				'line'    => __( 'Linje', 'nordicinterior' ),
				'surface' => __( 'Yta', 'nordicinterior' ),
			),
		),
		'image_ratio'            => array(
			'section' => 'products',
			'type'    => 'select',
			'label'   => __( 'Bildförhållande', 'nordicinterior' ),
			'choices' => array(
				'1-1' => '1:1',
				'4-5' => '4:5',
			),
		),
		'cols_desktop'           => array(
			'section' => 'products',
			'type'    => 'range',
			'label'   => __( 'Kolumner – dator', 'nordicinterior' ),
			'min'     => 2,
			'max'     => 6,
			'step'    => 1,
			'css'     => '--ni-cols-desktop',
		),
		'cols_tablet'            => array(
			'section' => 'products',
			'type'    => 'range',
			'label'   => __( 'Kolumner – surfplatta', 'nordicinterior' ),
			'min'     => 2,
			'max'     => 4,
			'step'    => 1,
			'css'     => '--ni-cols-tablet',
		),
		'cols_mobile'            => array(
			'section' => 'products',
			'type'    => 'range',
			'label'   => __( 'Kolumner – mobil', 'nordicinterior' ),
			'min'     => 1,
			'max'     => 2,
			'step'    => 1,
			'css'     => '--ni-cols-mobile',
		),
		'card_show_brand'        => array_merge(
			$yes_no,
			array(
				'section' => 'products',
				'label'   => __( 'Visa varumärke på kort', 'nordicinterior' ),
			)
		),
		'card_show_store'        => array_merge(
			$yes_no,
			array(
				'section' => 'products',
				'label'   => __( 'Visa butik på kort', 'nordicinterior' ),
			)
		),
		'card_show_colors'       => array_merge(
			$yes_no,
			array(
				'section' => 'products',
				'label'   => __( 'Visa färgprickar på kort', 'nordicinterior' ),
			)
		),
		'card_show_cta'          => array_merge(
			$yes_no,
			array(
				'section' => 'products',
				'label'   => __( 'Visa ”Till butiken” på kort', 'nordicinterior' ),
			)
		),
		'card_cta_style'         => array(
			'section' => 'products',
			'type'    => 'select',
			'label'   => __( 'CTA på kort', 'nordicinterior' ),
			'choices' => array(
				'link'   => __( 'Textlänk med pil', 'nordicinterior' ),
				'button' => __( 'Tunn knapp', 'nordicinterior' ),
			),
		),
		'cta_text'               => array(
			'section' => 'products',
			'type'    => 'text',
			'label'   => __( 'CTA-text', 'nordicinterior' ),
		),
		'filter_position'        => array(
			'section'     => 'products',
			'type'        => 'select',
			'label'       => __( 'Filtrens position', 'nordicinterior' ),
			'description' => __( 'På mobil visas filtren alltid i en utfällbar panel.', 'nordicinterior' ),
			'choices'     => array(
				'left'   => __( 'Vänster', 'nordicinterior' ),
				'top'    => __( 'Ovanför', 'nordicinterior' ),
				'drawer' => __( 'Utfällbar panel', 'nordicinterior' ),
			),
		),
		'pagination'             => array(
			'section' => 'products',
			'type'    => 'select',
			'label'   => __( 'Paginering', 'nordicinterior' ),
			'choices' => array(
				'loadmore' => __( 'Ladda fler', 'nordicinterior' ),
				'numbers'  => __( 'Sidnummer', 'nordicinterior' ),
			),
		),
		'per_page'               => array(
			'section' => 'products',
			'type'    => 'number',
			'label'   => __( 'Produkter per sida', 'nordicinterior' ),
			'min'     => 8,
			'max'     => 96,
			'step'    => 4,
		),
		// Search.
		'search_placeholder'     => array(
			'section' => 'search',
			'type'    => 'text',
			'label'   => __( 'Platshållare', 'nordicinterior' ),
		),
		'search_groups'          => array(
			'section'     => 'search',
			'type'        => 'text',
			'label'       => __( 'Grupper i live-sök', 'nordicinterior' ),
			'description' => __( 'Kommaseparerat i önskad ordning: product, product_cat, ni_room, ni_style, product_brand, post.', 'nordicinterior' ),
		),
		'popular_searches'       => array(
			'section'     => 'search',
			'type'        => 'textarea',
			'label'       => __( 'Populära sökningar', 'nordicinterior' ),
			'description' => __( 'En per rad. Visas som chips under sökfältet.', 'nordicinterior' ),
		),
		'popular_auto'           => array_merge(
			$yes_no,
			array(
				'section' => 'search',
				'label'   => __( 'Fyll på med de vanligaste sökningarna', 'nordicinterior' ),
			)
		),
		'synonyms'               => array(
			'section'     => 'search',
			'type'        => 'textarea',
			'label'       => __( 'Synonymer', 'nordicinterior' ),
			'description' => __( 'En grupp per rad, separerad med kommatecken, t.ex. ”byrå, kommod”.', 'nordicinterior' ),
		),
		// Affiliate.
		'new_tab'                => array_merge(
			$yes_no,
			array(
				'section' => 'affiliate',
				'label'   => __( 'Öppna butikslänkar i ny flik', 'nordicinterior' ),
			)
		),
		'disclosure_text'        => array(
			'section' => 'affiliate',
			'type'    => 'textarea',
			'label'   => __( 'Affiliate-information', 'nordicinterior' ),
		),
		'disclosure_footer'      => array_merge(
			$yes_no,
			array(
				'section' => 'affiliate',
				'label'   => __( 'Visa i sidfoten', 'nordicinterior' ),
			)
		),
		'disclosure_product'     => array_merge(
			$yes_no,
			array(
				'section' => 'affiliate',
				'label'   => __( 'Visa på produktsidor', 'nordicinterior' ),
			)
		),
		'disclosure_listing'     => array_merge(
			$yes_no,
			array(
				'section' => 'affiliate',
				'label'   => __( 'Visa i listningar', 'nordicinterior' ),
			)
		),
		'disclosure_post'        => array_merge(
			$yes_no,
			array(
				'section' => 'affiliate',
				'label'   => __( 'Visa i artiklar', 'nordicinterior' ),
			)
		),
		'go_prefix'              => array(
			'section'     => 'affiliate',
			'type'        => 'text',
			'label'       => __( 'Prefix för kampanjlänkar', 'nordicinterior' ),
			'description' => __( 'Standard ”go” ger /go/c/{id}/. Spara Permalänkar efter ändring.', 'nordicinterior' ),
		),
		'campaign_params'        => array(
			'section'     => 'affiliate',
			'type'        => 'text',
			'label'       => __( 'UTM-parametrar för kampanjer', 'nordicinterior' ),
			'description' => __( 'Variabler: {id}, {slug}, {placement}.', 'nordicinterior' ),
		),
		'campaign_subid'         => array(
			'section'     => 'affiliate',
			'type'        => 'text',
			'label'       => __( 'SubID-parameter', 'nordicinterior' ),
			'description' => __( 'T.ex. epi={placement} (Adtraction) eller clickref={slug} (Awin).', 'nordicinterior' ),
		),
		// Front page.
		'home_sections'          => array(
			'section' => 'home',
			'type'    => 'sortable',
			'label'   => __( 'Sektioner och ordning', 'nordicinterior' ),
		),
		'home_sections_off'      => array(
			'section' => 'home',
			'type'    => 'hidden',
			'label'   => '',
		),
		'hero_title'             => array(
			'section' => 'home',
			'type'    => 'text',
			'label'   => __( 'Hero – rubrik', 'nordicinterior' ),
		),
		'hero_text'              => array(
			'section' => 'home',
			'type'    => 'textarea',
			'label'   => __( 'Hero – text', 'nordicinterior' ),
		),
		'hero_image_1'           => array(
			'section' => 'home',
			'type'    => 'image',
			'label'   => __( 'Hero – bild 1', 'nordicinterior' ),
		),
		'hero_image_2'           => array(
			'section' => 'home',
			'type'    => 'image',
			'label'   => __( 'Hero – bild 2 (bildspel)', 'nordicinterior' ),
		),
		'hero_image_3'           => array(
			'section' => 'home',
			'type'    => 'image',
			'label'   => __( 'Hero – bild 3 (bildspel)', 'nordicinterior' ),
		),
		'hero_slider'            => array_merge(
			$yes_no,
			array(
				'section' => 'home',
				'label'   => __( 'Visa bilderna som bildspel', 'nordicinterior' ),
			)
		),
		'rooms_title'            => array(
			'section' => 'home',
			'type'    => 'text',
			'label'   => __( 'Rum – rubrik', 'nordicinterior' ),
		),
		'rooms_source'           => array(
			'section' => 'home',
			'type'    => 'select',
			'label'   => __( 'Rum – källa', 'nordicinterior' ),
			'choices' => array(
				'flagged' => __( 'Flaggade (”Populärt just nu”)', 'nordicinterior' ),
				'season'  => __( 'Säsong', 'nordicinterior' ),
				'clicks'  => __( 'Klick', 'nordicinterior' ),
				'order'   => __( 'Ordning', 'nordicinterior' ),
			),
		),
		'rooms_count'            => array(
			'section' => 'home',
			'type'    => 'number',
			'label'   => __( 'Rum – antal', 'nordicinterior' ),
			'min'     => 2,
			'max'     => 12,
			'step'    => 1,
		),
		'styles_title'           => array(
			'section' => 'home',
			'type'    => 'text',
			'label'   => __( 'Stilar – rubrik', 'nordicinterior' ),
		),
		'styles_count'           => array(
			'section' => 'home',
			'type'    => 'number',
			'label'   => __( 'Stilar – antal', 'nordicinterior' ),
			'min'     => 2,
			'max'     => 12,
			'step'    => 1,
		),
		'products_title'         => array(
			'section' => 'home',
			'type'    => 'text',
			'label'   => __( 'Produkter – rubrik', 'nordicinterior' ),
		),
		'products_source'        => array(
			'section' => 'home',
			'type'    => 'select',
			'label'   => __( 'Produkter – urval', 'nordicinterior' ),
			'choices' => array(
				'popular'    => __( 'Populärast', 'nordicinterior' ),
				'new'        => __( 'Nytt', 'nordicinterior' ),
				'sale'       => __( 'Rea', 'nordicinterior' ),
				'featured'   => __( 'Utvalda', 'nordicinterior' ),
				'handpicked' => __( 'Handplockade', 'nordicinterior' ),
			),
		),
		'products_ids'           => array(
			'section' => 'home',
			'type'    => 'text',
			'label'   => __( 'Handplockade produkt-ID:n', 'nordicinterior' ),
		),
		'products_tabs_by'       => array(
			'section' => 'home',
			'type'    => 'select',
			'label'   => __( 'Flikar', 'nordicinterior' ),
			'choices' => array(
				'room'     => __( 'Per rum', 'nordicinterior' ),
				'category' => __( 'Per kategori', 'nordicinterior' ),
				'none'     => __( 'Inga flikar', 'nordicinterior' ),
			),
		),
		'products_tabs'          => array(
			'section'     => 'home',
			'type'        => 'text',
			'label'       => __( 'Flikar – val och ordning', 'nordicinterior' ),
			'description' => __( 'Slugs separerade med kommatecken, t.ex. vardagsrum,sovrum,kok.', 'nordicinterior' ),
		),
		'products_per_tab'       => array(
			'section' => 'home',
			'type'    => 'number',
			'label'   => __( 'Produkter per flik', 'nordicinterior' ),
			'min'     => 2,
			'max'     => 24,
			'step'    => 1,
		),
		'look_post'              => array(
			'section' => 'home',
			'type'    => 'look',
			'label'   => __( 'Shoppa looken – inlägg', 'nordicinterior' ),
		),
		'inspiration_title'      => array(
			'section' => 'home',
			'type'    => 'text',
			'label'   => __( 'Inspiration – rubrik', 'nordicinterior' ),
		),
		'brands_title'           => array(
			'section' => 'home',
			'type'    => 'text',
			'label'   => __( 'Varumärken – rubrik', 'nordicinterior' ),
		),
		'categories_title'       => array(
			'section' => 'home',
			'type'    => 'text',
			'label'   => __( 'Kategorier – rubrik', 'nordicinterior' ),
		),
		'newsletter_title'       => array(
			'section' => 'home',
			'type'    => 'text',
			'label'   => __( 'Nyhetsbrev – rubrik', 'nordicinterior' ),
		),
		'newsletter_text'        => array(
			'section' => 'home',
			'type'    => 'text',
			'label'   => __( 'Nyhetsbrev – text', 'nordicinterior' ),
		),
		'newsletter_shortcode'   => array(
			'section' => 'home',
			'type'    => 'text',
			'label'   => __( 'Nyhetsbrev – formulär-shortcode', 'nordicinterior' ),
		),
		// Posts.
		'post_sidebar'           => array(
			'section' => 'posts',
			'type'    => 'select',
			'label'   => __( 'Sidofältets placering', 'nordicinterior' ),
			'choices' => array(
				'right' => __( 'Till höger (under innehållet på mobil)', 'nordicinterior' ),
				'none'  => __( 'Inget sidofält', 'nordicinterior' ),
			),
		),
		// Performance.
		'perf_preload_fonts'     => array_merge(
			$yes_no,
			array(
				'section' => 'performance',
				'label'   => __( 'Förladda typsnitt', 'nordicinterior' ),
			)
		),
		'perf_hero_preload'      => array_merge(
			$yes_no,
			array(
				'section' => 'performance',
				'label'   => __( 'Förladda hero-bilden (LCP)', 'nordicinterior' ),
			)
		),
		'perf_webp'              => array_merge(
			$yes_no,
			array(
				'section' => 'performance',
				'label'   => __( 'Skapa WebP-versioner av uppladdade bilder', 'nordicinterior' ),
			)
		),
		'perf_disable_emoji'     => array_merge(
			$yes_no,
			array(
				'section' => 'performance',
				'label'   => __( 'Ta bort emoji-skript', 'nordicinterior' ),
			)
		),
		'perf_disable_wc_blocks' => array_merge(
			$yes_no,
			array(
				'section' => 'performance',
				'label'   => __( 'Ladda inte WooCommerce block-CSS', 'nordicinterior' ),
			)
		),
	);

	return $schema;
}

/**
 * Sanitise a value for a schema field.
 *
 * @param mixed $value Value.
 * @param array $field Field.
 * @return mixed
 */
function ni_sanitize_field( $value, $field ) {
	switch ( $field['type'] ) {
		case 'checkbox':
			return (bool) $value;
		case 'select':
			$value = (string) $value;
			return array_key_exists( $value, $field['choices'] ) ? $value : (string) array_key_first( $field['choices'] );
		case 'range':
		case 'number':
			$value = (float) $value;
			$value = max( (float) $field['min'], min( (float) $field['max'], $value ) );
			return ( floor( (float) $field['step'] ) === (float) $field['step'] ) ? (int) $value : $value;
		case 'color':
			return (string) sanitize_hex_color( (string) $value );
		case 'url':
			return esc_url_raw( (string) $value );
		case 'image':
		case 'look':
			return absint( $value );
		case 'textarea':
			return wp_kses( (string) $value, array( 'a' => array( 'href' => true ) ) );
		case 'sortable':
		case 'hidden':
			$keys = array_filter( array_map( 'sanitize_key', explode( ',', (string) $value ) ) );
			return implode( ',', array_intersect( $keys, array_keys( ni_sections() ) ) );
		default:
			return sanitize_text_field( (string) $value );
	}
}

/**
 * Register panel, sections, settings and controls.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function ni_customize_register( $wp_customize ) {
	require_once NI_DIR . '/inc/class-ni-customize-sortable-control.php';
	require_once NI_DIR . '/inc/class-ni-customize-tools-control.php';

	$wp_customize->add_panel(
		'ni_theme',
		array(
			'title'       => __( 'Theme Adjustments', 'nordicinterior' ),
			'description' => __( 'Alla designval för Nordic Interior. Ändringar förhandsgranskas direkt.', 'nordicinterior' ),
			'priority'    => 20,
		)
	);
	foreach ( ni_customizer_sections() as $id => $title ) {
		$wp_customize->add_section(
			'ni_' . $id,
			array(
				'title' => $title,
				'panel' => 'ni_theme',
			)
		);
	}

	$defaults = ni_defaults();
	foreach ( ni_customizer_schema() as $key => $field ) {
		$setting_id = 'ni_' . $key;
		$wp_customize->add_setting(
			$setting_id,
			array(
				'default'           => $field['default'] ?? ( $defaults[ $key ] ?? '' ),
				'transport'         => ! empty( $field['css'] ) || 'topbar_text' === $key ? 'postMessage' : 'refresh',
				'sanitize_callback' => static function ( $value ) use ( $field ) {
					return ni_sanitize_field( $value, $field );
				},
				'capability'        => 'edit_theme_options',
			)
		);

		$args = array(
			'label'       => $field['label'],
			'section'     => 'ni_' . $field['section'],
			'description' => $field['description'] ?? '',
		);
		switch ( $field['type'] ) {
			case 'color':
				$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $setting_id, $args ) );
				break;
			case 'image':
				$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, $setting_id, array_merge( $args, array( 'mime_type' => 'image' ) ) ) );
				break;
			case 'sortable':
				$wp_customize->add_control(
					new NI_Customize_Sortable_Control(
						$wp_customize,
						$setting_id,
						array_merge(
							$args,
							array(
								'settings' => array(
									'default' => 'ni_home_sections',
									'off'     => 'ni_home_sections_off',
								),
								'choices'  => ni_sections(),
							)
						)
					)
				);
				break;
			case 'hidden':
				break;
			case 'look':
				$wp_customize->add_control(
					$setting_id,
					array_merge(
						$args,
						array(
							'type'    => 'select',
							'choices' => ni_posts_with_looks(),
						)
					)
				);
				break;
			case 'range':
			case 'number':
				$wp_customize->add_control(
					$setting_id,
					array_merge(
						$args,
						array(
							'type'        => $field['type'],
							'input_attrs' => array(
								'min'  => $field['min'],
								'max'  => $field['max'],
								'step' => $field['step'],
							),
						)
					)
				);
				break;
			default:
				$wp_customize->add_control(
					$setting_id,
					array_merge(
						$args,
						array(
							'type'    => $field['type'],
							'choices' => $field['choices'] ?? array(),
						)
					)
				);
		}
	}

	// Tools: export, import, reset.
	$wp_customize->add_setting(
		'ni_tools',
		array(
			'type'              => 'option',
			'sanitize_callback' => '__return_empty_string',
			'capability'        => 'edit_theme_options',
		)
	);
	$wp_customize->add_control(
		new NI_Customize_Tools_Control(
			$wp_customize,
			'ni_tools',
			array(
				'section' => 'ni_tools',
				'label'   => __( 'Inställningar', 'nordicinterior' ),
			)
		)
	);

	// Live preview for the topbar text and hero title.
	$wp_customize->selective_refresh->add_partial(
		'ni_topbar_text',
		array(
			'selector'        => '.ni-topbar__usp--1',
			'render_callback' => static function () {
				return esc_html( (string) ni_mod( 'topbar_text' ) );
			},
		)
	);
}
add_action( 'customize_register', 'ni_customize_register' );

/**
 * Live preview script: maps settings to CSS custom properties.
 */
function ni_customize_preview_js() {
	$map = array();
	foreach ( ni_customizer_schema() as $key => $field ) {
		if ( ! empty( $field['css'] ) ) {
			$map[ 'ni_' . $key ] = array(
				'var'  => $field['css'],
				'unit' => $field['unit'] ?? '',
			);
		}
	}
	wp_enqueue_script( 'ni-customizer-preview', NI_URI . '/assets/js/customizer-preview.js', array( 'customize-preview' ), NI_VERSION, true );
	wp_localize_script( 'ni-customizer-preview', 'niPreview', array( 'map' => $map ) );
}
add_action( 'customize_preview_init', 'ni_customize_preview_js' );

/**
 * Controls script: sortable sections, colour schemes, tools.
 */
function ni_customize_controls_js() {
	$schemes = array();
	foreach ( ni_color_schemes() as $key => $scheme ) {
		$schemes[ $key ] = $scheme['colors'];
	}
	wp_enqueue_style( 'ni-customizer', NI_URI . '/assets/css/admin.css', array(), NI_VERSION );
	wp_enqueue_script( 'ni-customizer-controls', NI_URI . '/assets/js/customizer-controls.js', array( 'customize-controls' ), NI_VERSION, true );
	wp_localize_script(
		'ni-customizer-controls',
		'niControls',
		array(
			'schemes' => $schemes,
			'ajax'    => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'ni_customizer_tools' ),
			'i18n'    => array(
				'confirmReset'  => __( 'Återställa alla Theme Adjustments till standard? Detta går inte att ångra.', 'nordicinterior' ),
				'confirmImport' => __( 'Importera inställningarna? Nuvarande värden skrivs över.', 'nordicinterior' ),
				'done'          => __( 'Klart – laddar om …', 'nordicinterior' ),
				'error'         => __( 'Något gick fel.', 'nordicinterior' ),
			),
		)
	);
}
add_action( 'customize_controls_enqueue_scripts', 'ni_customize_controls_js' );

/**
 * Collect all current theme settings for export.
 *
 * @return array<string,mixed>
 */
function ni_export_settings() {
	$out = array();
	foreach ( array_keys( ni_customizer_schema() ) as $key ) {
		$value = get_theme_mod( 'ni_' . $key, null );
		if ( null !== $value ) {
			$out[ $key ] = $value;
		}
	}
	return $out;
}

/**
 * AJAX: export / import / reset.
 */
function ni_ajax_customizer_tools() {
	check_ajax_referer( 'ni_customizer_tools', 'nonce' );
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_send_json_error( null, 403 );
	}
	$task = isset( $_POST['task'] ) ? sanitize_key( wp_unslash( $_POST['task'] ) ) : '';
	switch ( $task ) {
		case 'export':
			wp_send_json_success(
				array(
					'theme'    => 'nordicinterior',
					'version'  => NI_VERSION,
					'exported' => gmdate( 'c' ),
					'settings' => ni_export_settings(),
				)
			);
			break;
		case 'import':
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- JSON decoded and every value sanitised per schema below.
			$json = isset( $_POST['data'] ) ? json_decode( wp_unslash( $_POST['data'] ), true ) : null;
			if ( ! is_array( $json ) || empty( $json['settings'] ) || ! is_array( $json['settings'] ) ) {
				wp_send_json_error( array( 'message' => __( 'Ogiltig fil.', 'nordicinterior' ) ), 400 );
			}
			$schema = ni_customizer_schema();
			$count  = 0;
			foreach ( $json['settings'] as $key => $value ) {
				if ( isset( $schema[ $key ] ) ) {
					set_theme_mod( 'ni_' . $key, ni_sanitize_field( $value, $schema[ $key ] ) );
					++$count;
				}
			}
			wp_send_json_success( array( 'imported' => $count ) );
			break;
		case 'reset':
			foreach ( array_keys( ni_customizer_schema() ) as $key ) {
				remove_theme_mod( 'ni_' . $key );
			}
			wp_send_json_success();
			break;
	}
	wp_send_json_error( null, 400 );
}
add_action( 'wp_ajax_ni_customizer_tools', 'ni_ajax_customizer_tools' );
