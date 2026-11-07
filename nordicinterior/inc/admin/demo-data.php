<?php
/**
 * Demo data definitions (fictional products – brand and store names are
 * used only as example taxonomy terms).
 *
 * @package NordicInterior
 */

defined( 'ABSPATH' ) || exit;

/**
 * Demo data.
 *
 * @return array
 */
function ni_demo_data() {
	return array(
		'rooms'      => array(
			array( 'Vardagsrum', 'vardagsrum', '#E4D9C6', 1, array( 9, 10, 11, 12, 1, 2 ) ),
			array( 'Sovrum', 'sovrum', '#E3DCD3', 1, array( 11, 12, 1 ) ),
			array( 'Kök', 'kok', '#E6E0D2', 1, array( 3, 4, 5 ) ),
			array( 'Matrum', 'matrum', '#DED6C4', 1, array( 12, 4 ) ),
			array( 'Badrum', 'badrum', '#DEE2DC', 0, array( 2, 3 ) ),
			array( 'Hall', 'hall', '#E7DFD3', 0, array( 8, 9 ) ),
			array( 'Barnrum', 'barnrum', '#EADFD4', 0, array( 8 ) ),
			array( 'Hemmakontor', 'hemmakontor', '#E0DCD2', 1, array( 8, 9, 1 ) ),
			array( 'Balkong & uteplats', 'balkong-uteplats', '#DCE0D3', 0, array( 4, 5, 6, 7 ) ),
		),
		'styles'     => array(
			array( 'Skandinavisk', 'skandinavisk', 'Ljust trä, mjuka textilier och enkla former. Funktion och värme i balans.' ),
			array( 'Japandi', 'japandi', 'Japansk stillhet möter nordisk funktion – låga möbler, naturmaterial och dov palett.' ),
			array( 'Minimalistisk', 'minimalistisk', 'Få föremål, rena linjer och mycket luft. Varje sak har sin plats.' ),
			array( 'Modern klassisk', 'modern-klassisk', 'Designklassiker i nya kombinationer – tidlöst med en personlig twist.' ),
			array( 'Lantlig', 'lantlig', 'Patinerat trä, linne och keramik. Ombonat och avslappnat.' ),
			array( 'Industriell', 'industriell', 'Stål, läder och råa ytor. Robust och grafiskt.' ),
			array( 'Vintage', 'vintage', 'Fynd med historia blandat med nytt. Mässing, valnöt och mönster.' ),
			array( 'Bohemisk', 'bohemisk', 'Rotting, jute och textilier i lager på lager. Varmt och lekfullt.' ),
		),
		'brands'     => array(
			array( 'HAY', 'hay', 'Danmark', 'https://www.hay.com', 1 ),
			array( 'Muuto', 'muuto', 'Danmark', 'https://www.muuto.com', 1 ),
			array( 'String', 'string', 'Sverige', 'https://www.stringfurniture.com', 1 ),
			array( 'Ferm Living', 'ferm-living', 'Danmark', 'https://fermliving.com', 1 ),
			array( 'Audo', 'audo', 'Danmark', 'https://audocph.com', 0 ),
			array( '&Tradition', 'andtradition', 'Danmark', 'https://www.andtradition.com', 1 ),
			array( 'Louis Poulsen', 'louis-poulsen', 'Danmark', 'https://www.louispoulsen.com', 1 ),
			array( 'Fritz Hansen', 'fritz-hansen', 'Danmark', 'https://www.fritzhansen.com', 1 ),
			array( 'Iittala', 'iittala', 'Finland', 'https://www.iittala.com', 0 ),
			array( 'Normann Copenhagen', 'normann-copenhagen', 'Danmark', 'https://www.normann-copenhagen.com', 0 ),
			array( 'Artek', 'artek', 'Finland', 'https://www.artek.fi', 0 ),
			array( 'Gubi', 'gubi', 'Danmark', 'https://www.gubi.com', 0 ),
			array( 'Carl Hansen & Søn', 'carl-hansen-son', 'Danmark', 'https://www.carlhansen.com', 0 ),
			array( 'Hem', 'hem', 'Sverige', 'https://hem.com', 0 ),
			array( 'Design House Stockholm', 'design-house-stockholm', 'Sverige', 'https://designhousestockholm.com', 0 ),
			array( 'Skagerak', 'skagerak', 'Danmark', 'https://www.skagerak.dk', 0 ),
		),
		'stores'     => array(
			array( 'Nordiska Galleriet', 'nordiska-galleriet', 'https://www.nordiskagalleriet.se', 'Fri frakt över 1 000 kr', '30 dagars öppet köp', '2–5 arbetsdagar' ),
			array( 'Royal Design', 'royal-design', 'https://royaldesign.se', 'Fri frakt över 499 kr', '30 dagars returrätt', '1–4 arbetsdagar' ),
			array( 'Rum21', 'rum21', 'https://www.rum21.se', 'Fri frakt över 599 kr', '30 dagars öppet köp', '2–4 arbetsdagar' ),
			array( 'Lagerhaus', 'lagerhaus', 'https://www.lagerhaus.se', 'Frakt från 49 kr', '30 dagars öppet köp', '2–5 arbetsdagar' ),
			array( 'Mio', 'mio', 'https://www.mio.se', 'Hemleverans från 495 kr', '30 dagars öppet köp', '1–3 veckor' ),
			array( 'Jotex', 'jotex', 'https://www.jotex.se', 'Fri frakt över 699 kr', '30 dagars returrätt', '2–5 arbetsdagar' ),
		),
		'categories' => array(
			'Möbler'             => array(
				'slug'     => 'mobler',
				'children' => array(
					'Soffor'   => array( '3-sitssoffor', '2-sitssoffor', 'Hörnsoffor' ),
					'Fåtöljer' => array( 'Loungefåtöljer', 'Gungstolar' ),
					'Bord'     => array( 'Soffbord', 'Matbord', 'Sidobord' ),
					'Stolar'   => array( 'Matstolar', 'Barstolar' ),
					'Sängar'   => array( 'Dubbelsängar', 'Sänggavlar' ),
				),
			),
			'Belysning'          => array(
				'slug'     => 'belysning',
				'children' => array(
					'Taklampor'   => array( 'Pendellampor', 'Plafonder' ),
					'Bordslampor' => array(),
					'Golvlampor'  => array(),
				),
			),
			'Textilier & mattor' => array(
				'slug'     => 'textilier-mattor',
				'children' => array(
					'Mattor'          => array( 'Ullmattor', 'Jutemattor' ),
					'Kuddar & plädar' => array(),
				),
			),
			'Dekoration'         => array(
				'slug'     => 'dekoration',
				'children' => array(
					'Vaser'            => array(),
					'Ljusstakar'       => array(),
					'Tavlor & posters' => array(),
				),
			),
			'Förvaring'          => array(
				'slug'     => 'forvaring',
				'children' => array(
					'Hyllor'     => array(),
					'Byråar'     => array(),
					'Sideboards' => array(),
				),
			),
			'Kök & servering'    => array(
				'slug'     => 'kok-servering',
				'children' => array(
					'Serveringsfat'   => array(),
					'Glas & karaffer' => array(),
				),
			),
			'Utemöbler'          => array(
				'slug'     => 'utemobler',
				'children' => array(
					'Utesoffor' => array(),
					'Utebord'   => array(),
				),
			),
		),
		'materials'  => array( 'Ek', 'Valnöt', 'Bouclé', 'Linne', 'Sammet', 'Marmor', 'Rotting', 'Ull', 'Jute', 'Mässing', 'Glas', 'Keramik', 'Stål', 'Bomull', 'Teak' ),
		'colors'     => array(
			'Vit'        => '#F4F1EA',
			'Beige'      => '#D9C8AE',
			'Sand'       => '#C9B08A',
			'Grå'        => '#9A9A96',
			'Svart'      => '#232322',
			'Grön'       => '#7D8C6C',
			'Blå'        => '#5B6E85',
			'Terrakotta' => '#B4643F',
			'Rosa'       => '#D8A9A0',
			'Natur'      => '#C8A97E',
			'Brun'       => '#6E4B33',
			'Gul'        => '#D9B44A',
		),
		'designers'  => array( 'Ebba Lind', 'Jonas Berg', 'Studio Ask', 'Maja Holm', 'Nils Ek', 'Aino Virtanen', 'Freja Dahl', 'Oskar Lund' ),
		// type, category slug, names, [w,d,h], [min,max price], rooms, styles, materials, colors, shape.
		'products'   => array(
			array( 'Soffa', '3-sitssoffor', array( 'Ro 3-sits', 'Moln 3-sits', 'Sävik 3-sits' ), array( 214, 92, 78 ), array( 12995, 26995 ), array( 'vardagsrum' ), array( 'skandinavisk', 'japandi', 'modern-klassisk' ), array( 'Bouclé', 'Linne', 'Ek' ), array( 'Beige', 'Vit', 'Grå', 'Grön' ), 'sofa' ),
			array( 'Soffa', '2-sitssoffor', array( 'Vila 2-sits', 'Lugn 2-sits' ), array( 168, 88, 76 ), array( 8995, 16995 ), array( 'vardagsrum', 'hemmakontor' ), array( 'minimalistisk', 'skandinavisk' ), array( 'Sammet', 'Linne' ), array( 'Grön', 'Terrakotta', 'Rosa' ), 'sofa' ),
			array( 'Hörnsoffa', 'hornsoffor', array( 'Fjord hörnsoffa', 'Strand hörnsoffa' ), array( 286, 190, 80 ), array( 21995, 34995 ), array( 'vardagsrum' ), array( 'modern-klassisk', 'minimalistisk' ), array( 'Bouclé', 'Bomull' ), array( 'Sand', 'Grå' ), 'sofa' ),
			array( 'Fåtölj', 'loungefatoljer', array( 'Bris loungefåtölj', 'Tuva fåtölj', 'Alm loungefåtölj' ), array( 78, 82, 74 ), array( 5995, 14995 ), array( 'vardagsrum', 'sovrum' ), array( 'skandinavisk', 'vintage', 'japandi' ), array( 'Bouclé', 'Ek', 'Valnöt' ), array( 'Vit', 'Beige', 'Brun' ), 'armchair' ),
			array( 'Gungstol', 'gungstolar', array( 'Våg gungstol' ), array( 64, 88, 92 ), array( 4495, 7995 ), array( 'vardagsrum', 'barnrum' ), array( 'lantlig', 'skandinavisk' ), array( 'Ek', 'Rotting' ), array( 'Natur' ), 'chair' ),
			array( 'Soffbord', 'soffbord', array( 'Sten soffbord', 'Ring soffbord', 'Ö soffbord' ), array( 110, 60, 38 ), array( 3495, 9995 ), array( 'vardagsrum' ), array( 'japandi', 'minimalistisk', 'modern-klassisk' ), array( 'Ek', 'Marmor', 'Valnöt' ), array( 'Natur', 'Vit', 'Brun' ), 'coffeetable' ),
			array( 'Matbord', 'matbord', array( 'Långa matbord', 'Rund matbord Ø120', 'Gård matbord' ), array( 200, 95, 74 ), array( 8995, 19995 ), array( 'matrum', 'kok' ), array( 'skandinavisk', 'lantlig', 'japandi' ), array( 'Ek', 'Valnöt' ), array( 'Natur', 'Brun' ), 'table' ),
			array( 'Sidobord', 'sidobord', array( 'Pelare sidobord' ), array( 45, 45, 52 ), array( 1495, 3995 ), array( 'vardagsrum', 'sovrum' ), array( 'minimalistisk', 'vintage' ), array( 'Mässing', 'Marmor', 'Ek' ), array( 'Svart', 'Vit' ), 'coffeetable' ),
			array( 'Matstol', 'matstolar', array( 'Kil matstol', 'Båge matstol', 'Fyr matstol' ), array( 50, 52, 79 ), array( 1995, 5995 ), array( 'matrum', 'kok' ), array( 'skandinavisk', 'modern-klassisk', 'japandi' ), array( 'Ek', 'Valnöt', 'Linne' ), array( 'Natur', 'Svart', 'Brun' ), 'chair' ),
			array( 'Barstol', 'barstolar', array( 'Hög barstol 65' ), array( 45, 47, 88 ), array( 2495, 4495 ), array( 'kok' ), array( 'industriell', 'minimalistisk' ), array( 'Stål', 'Ek' ), array( 'Svart', 'Grå' ), 'chair' ),
			array( 'Säng', 'dubbelsangar', array( 'Dröm säng 160', 'Vila säng 180' ), array( 168, 212, 95 ), array( 9995, 18995 ), array( 'sovrum' ), array( 'skandinavisk', 'japandi' ), array( 'Ek', 'Linne' ), array( 'Natur', 'Beige' ), 'bed' ),
			array( 'Sänggavel', 'sanggavlar', array( 'Mjuk sänggavel', 'Ribb sänggavel' ), array( 180, 10, 120 ), array( 2995, 6995 ), array( 'sovrum' ), array( 'modern-klassisk', 'bohemisk' ), array( 'Sammet', 'Rotting' ), array( 'Grön', 'Rosa', 'Natur' ), 'headboard' ),
			array( 'Pendellampa', 'pendellampor', array( 'Kupol pendel', 'Glob pendel', 'Skiva pendel' ), array( 40, 40, 30 ), array( 1495, 8995 ), array( 'matrum', 'kok', 'vardagsrum' ), array( 'skandinavisk', 'modern-klassisk', 'industriell' ), array( 'Glas', 'Mässing', 'Stål' ), array( 'Vit', 'Svart', 'Grön' ), 'pendant' ),
			array( 'Plafond', 'plafonder', array( 'Måne plafond' ), array( 35, 35, 12 ), array( 995, 2495 ), array( 'hall', 'badrum' ), array( 'minimalistisk' ), array( 'Glas' ), array( 'Vit' ), 'pendant' ),
			array( 'Bordslampa', 'bordslampor', array( 'Svamp bordslampa', 'Kona bordslampa', 'Lykta bordslampa' ), array( 25, 25, 42 ), array( 995, 4995 ), array( 'sovrum', 'hemmakontor', 'vardagsrum' ), array( 'japandi', 'vintage', 'skandinavisk' ), array( 'Keramik', 'Mässing', 'Linne' ), array( 'Sand', 'Vit', 'Terrakotta' ), 'tablelamp' ),
			array( 'Golvlampa', 'golvlampor', array( 'Stråle golvlampa', 'Båge golvlampa' ), array( 40, 40, 150 ), array( 2495, 7995 ), array( 'vardagsrum', 'hemmakontor' ), array( 'modern-klassisk', 'industriell' ), array( 'Mässing', 'Stål', 'Linne' ), array( 'Svart', 'Vit' ), 'floorlamp' ),
			array( 'Ullmatta', 'ullmattor', array( 'Fält ullmatta 200×300', 'Rand ullmatta 170×240' ), array( 300, 200, 1 ), array( 3995, 11995 ), array( 'vardagsrum', 'sovrum' ), array( 'skandinavisk', 'minimalistisk' ), array( 'Ull' ), array( 'Beige', 'Grå', 'Grön' ), 'rug' ),
			array( 'Jutematta', 'jutemattor', array( 'Strå jutematta 160×230' ), array( 230, 160, 1 ), array( 1495, 2995 ), array( 'vardagsrum', 'hall', 'balkong-uteplats' ), array( 'bohemisk', 'lantlig' ), array( 'Jute' ), array( 'Natur' ), 'rug' ),
			array( 'Kudde', 'kuddar-pladar', array( 'Linne kuddfodral 50×50', 'Sammet kudde 45×45' ), array( 50, 50, 10 ), array( 299, 699 ), array( 'vardagsrum', 'sovrum' ), array( 'skandinavisk', 'lantlig', 'bohemisk' ), array( 'Linne', 'Sammet' ), array( 'Terrakotta', 'Rosa', 'Beige', 'Grön' ), 'cushion' ),
			array( 'Pläd', 'kuddar-pladar', array( 'Ull pläd 130×180' ), array( 180, 130, 1 ), array( 699, 1495 ), array( 'vardagsrum', 'sovrum' ), array( 'skandinavisk', 'lantlig' ), array( 'Ull' ), array( 'Grå', 'Sand' ), 'cushion' ),
			array( 'Vas', 'vaser', array( 'Våg vas', 'Kruka vas', 'Droppe vas' ), array( 18, 18, 30 ), array( 349, 1495 ), array( 'vardagsrum', 'matrum', 'hall' ), array( 'japandi', 'minimalistisk', 'vintage' ), array( 'Keramik', 'Glas' ), array( 'Vit', 'Sand', 'Blå' ), 'vase' ),
			array( 'Ljusstake', 'ljusstakar', array( 'Trio ljusstake', 'Pelare ljusstake' ), array( 12, 12, 20 ), array( 249, 1295 ), array( 'matrum', 'vardagsrum' ), array( 'modern-klassisk', 'vintage' ), array( 'Mässing', 'Stål' ), array( 'Gul', 'Svart' ), 'candle' ),
			array( 'Poster', 'tavlor-posters', array( 'Horisont poster 50×70', 'Form poster 50×70' ), array( 50, 2, 70 ), array( 299, 899 ), array( 'vardagsrum', 'hall', 'hemmakontor' ), array( 'minimalistisk', 'skandinavisk' ), array( 'Bomull' ), array( 'Beige', 'Blå', 'Terrakotta' ), 'poster' ),
			array( 'Hylla', 'hyllor', array( 'Stege hylla', 'Vägghylla 78' ), array( 78, 30, 200 ), array( 1995, 6995 ), array( 'hemmakontor', 'vardagsrum', 'hall' ), array( 'skandinavisk', 'industriell' ), array( 'Ek', 'Stål' ), array( 'Natur', 'Vit', 'Svart' ), 'shelf' ),
			array( 'Byrå', 'byraar', array( 'Lugn byrå', 'Ribb byrå' ), array( 90, 45, 80 ), array( 4995, 11995 ), array( 'sovrum', 'hall' ), array( 'skandinavisk', 'japandi', 'vintage' ), array( 'Ek', 'Valnöt' ), array( 'Natur', 'Brun' ), 'dresser' ),
			array( 'Sideboard', 'sideboards', array( 'Låg sideboard 180' ), array( 180, 45, 70 ), array( 9995, 16995 ), array( 'vardagsrum', 'matrum' ), array( 'modern-klassisk', 'minimalistisk' ), array( 'Valnöt', 'Ek' ), array( 'Brun' ), 'sideboard' ),
			array( 'Serveringsfat', 'serveringsfat', array( 'Sten serveringsfat', 'Oval serveringsfat' ), array( 38, 24, 3 ), array( 249, 795 ), array( 'kok', 'matrum' ), array( 'lantlig', 'japandi' ), array( 'Keramik', 'Marmor' ), array( 'Vit', 'Sand' ), 'plate' ),
			array( 'Glas', 'glas-karaffer', array( 'Kant glas 4-pack', 'Karaff med glas' ), array( 8, 8, 10 ), array( 299, 899 ), array( 'kok', 'matrum' ), array( 'skandinavisk', 'minimalistisk' ), array( 'Glas' ), array( 'Vit', 'Grön', 'Blå' ), 'glass' ),
			array( 'Utesoffa', 'utesoffor', array( 'Brygga utesoffa' ), array( 180, 80, 75 ), array( 7995, 14995 ), array( 'balkong-uteplats' ), array( 'skandinavisk', 'bohemisk' ), array( 'Teak', 'Rotting' ), array( 'Natur' ), 'sofa' ),
			array( 'Utebord', 'utebord', array( 'Brygga utebord', 'Café bord Ø70' ), array( 140, 80, 74 ), array( 2995, 8995 ), array( 'balkong-uteplats' ), array( 'skandinavisk', 'industriell' ), array( 'Teak', 'Stål' ), array( 'Natur', 'Grön' ), 'table' ),
		),
	);
}
