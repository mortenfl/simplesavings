# Nordic Interior – WordPress-tema

Ett klassiskt PHP-tema för **www.nordicinterior.se**: en svensk katalog för inredning och möbler där alla produkter är WooCommerce-produkter av typen *Extern/Affiliate*. Temat säljer ingenting själv. Det hjälper besökaren att hitta rätt efter rum, stil, kategori, material, färg, mått, varumärke och butik, och skickar vidare till butiken via produktens befintliga affiliatelänk.

- Textdomän `nordicinterior`, prefix `ni_` / `NI_`
- WordPress 6.6+, PHP 8.1+, WooCommerce 9.6+ (inbyggda varumärken `product_brand`)
- Inget byggsteg: handskriven CSS och vanilla JS, ingen jQuery i frontend
- Alla designvärden är CSS-variabler (`--ni-*`) som styrs från **Utseende → Anpassa → Theme Adjustments**

---

## Installation

1. Installera och aktivera **WooCommerce** (9.6 eller senare).
2. Ladda upp `nordicinterior.zip` under **Utseende → Teman → Lägg till nytt** och aktivera temat.
3. Gå till **Nordic Interior → Kom igång**:
   - följ checklistan,
   - klicka **Importera demoinnehåll** om du vill se temat med innehåll,
   - klicka **Skapa huvudmeny**.
4. Spara permalänkarna en gång (**Inställningar → Permalänkar → Spara**) om du har bytt prefix för kampanjlänkar.

### WP-CLI

```bash
wp nordicinterior demo          # demoinnehåll: 9 rum, 8 stilar, 16 varumärken, 6 butiker, 60 produkter …
wp nordicinterior menu          # bygger "Huvudmeny" och väljer den som huvud- och mobilmeny
wp nordicinterior check-links   # kontrollerar alla affiliatelänkar nu (OK / trasig / okänd)
wp nordicinterior recalc        # räknar om _ni_clicks och _ni_discount
```

---

## Datamodell

| Begrepp | Implementation |
|---|---|
| Produkt | WooCommerce `external`. Produkt-URL = affiliatelänk. Knapptext valfri (standard "Till butiken"). Extra fält: Serie, Skötsel, Leveranstid. |
| Kategori | `product_cat`, två–tre nivåer |
| Rum | `ni_room` (slug `rum`). Termmeta: bild, färgton, "Populärt just nu", ordning, säsongsmånader |
| Stil | `ni_style` (slug `stil`). Termmeta: bild, beskrivning, ordning |
| Varumärke | `product_brand` (WooCommerce). Termmeta: logotyp, utvalt, webbplats, ursprungsland, ordning |
| Butik | `ni_store` (slug `butik`). Termmeta: logotyp, webbplats, fraktinfo, returrätt, leveranstid |
| Attribut | `pa_material`, `pa_farg` (med färgprick `ni_hex`), `pa_designer`. Mått: WooCommerce bredd/längd(=djup)/höjd i cm |
| Kampanj | Posttyp `ni_campaign`: länk, rabattkod, badge, start/slut, huvudkampanj, koppling till rum/stil/varumärke/butik. Visas bara inom sitt intervall |
| Inspiration | Inlägg i kategorierna Guider, Inredningstips och Shoppa looken, kopplade till rum, stil och varumärke. Fältet "Relaterade produkter" tar produkt-ID:n |
| Shoppa looken | Blocket **Shoppa looken** eller `[ni_shop_the_look image="123" points="45:30:62\|46:71:40"]` (produkt-ID:x%:y%) |

Sorteringsmeta per produkt: `_ni_clicks` (klick senaste 30 dagarna) och `_ni_discount` (rabatt i %), omräknade varje natt. Prishistorik sparas i `_ni_price_history` när priset ändras och ger märket **Prissänkt**.

---

## Affiliate och klickspårning

- Produktlänkar går **direkt** till WooCommerce-produktens URL. Inget skrivs om.
- Länkarna har `rel="sponsored nofollow noopener"` och kan öppnas i ny flik (Theme Adjustments → Affiliate).
- `track.js` skickar en beacon till `POST /wp-json/nordicinterior/v1/click` med produkt, placering, rum och stil.
- Inga personuppgifter sparas: ingen IP, ingen user agent, inga cookies. Botar och inloggade redaktörer räknas inte. Spärren mot dubbelklick använder en saltad hash som roterar dagligen och lever i 10 sekunder.
- Placeringar: `card`, `product`, `look`, `search`, `home`, `sidebar`, `email`, `campaign`. Länkar i nyhetsbrev till interna sidor kan få `?ni_src=email`; klicken räknas då som "mejl".
- Kampanjer går via `/go/c/{id}/` med valbara UTM- och subID-parametrar (variabler `{id}`, `{slug}`, `{placement}`). `/go/` får `X-Robots-Tag: noindex` och blockeras i robots.txt.
- Varje natt kontrolleras alla affiliatelänkar i omgångar om 25. Trasiga länkar listas under **Nordic Interior → Länkkontroll** och i en kolumn i produktlistan.
- Det finns inget köpflöde. Varukorg, kassa och Mitt konto omdirigeras till startsidan och inga "Lägg i varukorg"-knappar visas.

### Cookie-plugins

Spårningen är anonym och sker helt på den egna domänen, så den kräver normalt inget samtycke. Temats skript har `data-cookieconsent="ignore"` (Cookiebot) och `data-category="functional"`. Kontrollera ändå detta mot er egen integritetspolicy.

---

## Produktflöden (WP All Import)

Använd WP All Import Pro med WooCommerce-tillägget och välj **Extern/Affiliate-produkt**.

| Fält | Adtraction | Awin | Partner-ads | Tradedoubler |
|---|---|---|---|---|
| `_product_url` | `{TrackingUrl}` | `{aw_deep_link}` | `{vareurl}` | `{productUrl}` |
| Ordinarie pris | `{OriginalPrice}` | `{rrp_price}` | `{glpris}` | `{previousPrice}` |
| Pris / reapris | `{Price}` | `{search_price}` | `{nypris}` | `{price}` |
| SKU | `{Sku}` | `{merchant_product_id}` | `{varenummer}` | `{sku}` |
| `product_brand` | `{Brand}` | `{brand_name}` | `{brand}` | `{brand}` |
| `ni_store` | fast värde per flöde | | | |
| `product_cat`, `ni_room`, `ni_style` | Mapping rules utifrån butikens kategori och nyckelord | | | |
| `pa_material` / `pa_farg` / `pa_designer` | `{Material}` / `{Color}` / `{Designer}` | `{material}` / `{colour}` | `{materiale}` / `{farve}` | `{fields_material}` / `{fields_color}` |

Mått i cm (bredd → `_width`, djup → `_length`, höjd → `_height`) kan tolkas ur en textsträng med en funktion i WP All Imports funktionsredigerare:

```php
function ni_dim( $text, $key ) {
	// "B 210 x D 90 x H 80 cm" → 210 / 90 / 80
	if ( preg_match( '/' . $key . '\s*:?\s*(\d+(?:[.,]\d+)?)/i', $text, $m ) ) {
		return str_replace( ',', '.', $m[1] );
	}
	return '';
}
// [ni_dim({Dimensions[1]},"B")]  [ni_dim({Dimensions[1]},"D")]  [ni_dim({Dimensions[1]},"H")]
```

Efter varje importerad produkt (`pmxi_saved_post`) sparas prishistoriken, och efter importen (`pmxi_after_xml_import`) töms cachen. Kör **Räkna om nu** eller `wp nordicinterior recalc` efter första importen.

---

## Sök

- Det finns inget sökfält i headern. Sök finns i hero, på söksidan och på 404-sidan.
- Live-sök: `GET /wp-json/nordicinterior/v1/search?q=` med en ARIA 1.2-combobox. Förslagen grupperas i produkter, kategorier, rum, stilar, varumärken och artiklar.
- Ord som matchar rum, stil, varumärke, butik, material, färg, designer eller kategori blir filter. "soffa bouclé vardagsrum" ger Soffor + Bouclé + Vardagsrum.
- Synonymer redigeras under Theme Adjustments → Sök. Svensk stamning används som reserv ("soffor" → "soff"). SKU-matchning går direkt till produkten.
- "Menade du …?", populära sökningar och anonym sökloggning. **Nordic Interior → Sökningar** visar sökningar utan träff.
- Med SearchWP eller Relevanssi aktivt sköter pluginet textdelen, medan temat fortsätter att omvandla ord till filter. FiboSearch fungerar med sina egna sökfält.

## Listningar och filter

- Filtren är vanliga GET-parametrar (`f_kategori`, `f_rum`, `f_stil`, `f_marke`, `f_butik`, `f_material`, `f_farg`, `f_designer`, `pris_min/max`, `bredd_min/max`, `djup_min/max`, `hojd_min/max`, `rea`, `sortera`, `visa`). De fungerar utan JavaScript. `filters.js` hämtar samma URL med `?ni_partial=1` och byter bara resultatdelen (AJAX), med `pushState` och "Ladda fler".
- Filtrerade sidor får `noindex, follow`, även när Yoast eller Rank Math är aktivt.
- Indexerbara landningssidor: `/rum/{rum}/kategori/{kategori}/` och `/stil/{stil}/kategori/{kategori}/`. Egen rubrik och ingress skrivs under **Nordic Interior → Landningssidor**. Sidorna ingår i core-sitemapen.

## Startsidan och sektioner

Under Theme Adjustments → Startsida väljer du och drar sektionerna i önskad ordning: hero, rum, stilar, kampanjer, produkter i flikar, Shoppa looken, inspiration, varumärken, kategorier, nyhetsbrev. Alla sektioner kan också användas på andra sidor: `[ni_section name="rooms" title="Valfri rubrik"]`.

## Sidmallar

Alla varumärken (A–Ö), Alla butiker, Rea, Alla rum och Alla stilar finns under **Sidattribut → Mall**. Kampanjarkivet ligger på `/kampanjer/`.

## Widgetområden

Sidfot 1–3 (Om oss, Länklista, Nyhetsbrev), Blogg-, Varumärken-, Butiker- och Rea-sidofält. Tomma områden visar standardinnehåll. Temats widgets: *NI: Om oss*, *NI: Länklista*, *NI: Nyhetsbrev*, *NI: Aktuell kampanj*, *NI: Populära rum och stilar*, *NI: Mer inspiration*.

## Mega-meny

Under **Utseende → Menyer** har varje menypunkt på nivå 1 fälten: mega-meny på/av, antal kolumner (2–5), stämningsbild, bildtext och bildlänk. Nivå 2 blir kolumnrubriker och nivå 3 länkar. Menypunkter med CSS-klassen `ni-sale` (eller titeln "REA") visas i lerfärg.

---

## SEO, tillgänglighet och prestanda

- JSON-LD: Product (Brand, Offer med affiliatelänk, butik som säljare, material, färg och mått), ItemList, BreadcrumbList, WebSite med SearchAction, Organization och Article. Datum och författare finns bara i schemat. När Yoast, Rank Math, SEOPress eller AIOSEO är aktivt lämnar temat WebSite, Organization, Breadcrumb och Article åt pluginet.
- Open Graph med produktmeta för Pinterest Rich Pins. "Spara"-knapp på inspirationsbilder utan tredjepartsskript.
- WCAG 2.2 AA: synliga fokusmarkeringar, ARIA för combobox, flikar, menyer (disclosure), dialoger (fokusfälla) och Shoppa looken-punkter, tryckytor på minst 44 px och respekt för `prefers-reduced-motion`.
- **Färgkontrast:** "Dämpad text" #7A736A når bara 4,2:1 mot havre. Standardvärdet är därför mörkat till #655E56 (5,8:1). Ek och Lera används i originalton för ytor och linjer och i mörkare härledda toner (`--ni-link`, `--ni-clay-text`) för text, så att all text håller AA.
- Typsnitt (Jost, Inter, DM Sans – latin, bara använda vikter) hostas lokalt och förladdas. Hero-bilden förladdas som LCP-bild. Inga jQuery- eller WooCommerce-skript laddas i frontend.

## Utveckling

```bash
composer global require wp-coding-standards/wpcs phpcompatibility/phpcompatibility-wp
phpcs                          # använder phpcs.xml.dist
php bin/check-prefix.php       # kontrollerar prefixet ni_/NI_ (se nedan)
php bin/make-pot.php           # bygger languages/nordicinterior.pot (eller: wp i18n make-pot)
```

- WPCS-sniffen `PrefixAllGlobals` godtar inte prefix kortare än tre tecken. Briefen kräver `ni_`, så sniffen är avstängd i `phpcs.xml.dist` och ersatt av `bin/check-prefix.php`.
- **HPOS:** temat läser och skriver aldrig ordrar och är därmed kompatibelt. WooCommerces deklarations-API (`FeaturesUtil::declare_compatibility`) tar bara emot plugin-filer, så ett tema kan inte deklarera något.
- **Theme Check:** temat registrerar posttyp, taxonomier, shortcodes, REST-rutter och egna tabeller eftersom briefen kräver det. WordPress.org-katalogens Theme Check klassar sådant som "plugin territory". För en publicering i katalogen behöver de delarna flyttas till ett följeplugin. För den här sajten är allt samlat i temat med avsikt.

## Licens

GPL-2.0-or-later. Typsnitten Jost, Inter och DM Sans är licensierade under SIL Open Font License 1.1.
