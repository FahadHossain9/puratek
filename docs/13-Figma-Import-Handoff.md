# Figma handoff — documentation campaign r6

Import an individual file from `dist/figma/`, by serving it locally and capturing it with the browser extension, or importing its deployed URL. The directory page is only an index. Each email has mobile (375px) and desktop (680px viewport, 600px email), using the same brand palette.

There are **48 files: (23 roles + one W2 alternative) × two screen sizes**. W2-alt is not an additional scheduled email.

Text, buttons, comparison cells, section labels, offers and product-card copy remain HTML. Illustrations, original logos and official catalog photographs are embedded raster assets. No scripts, iframes or remote font downloads are needed. The importer may rasterize or restructure HTML; native components and Auto Layout are not guaranteed until the actual extension is tested.

Start with W1 mobile light, then W2/W2-alt and W3. Check section order, hero color #F7931E, original-logo artwork panel, the four product cards, mobile comparison stacking and footer. Desktop and mobile use the same authored copy. The comparison becomes two compact cells only at desktop width; all product cards stay one column.

The mobile review page includes feedback tools. Those controls are excluded from import files. Product data in cart examples is illustrative; the actual FunnelKit block is populated at send time and may differ in spacing.

`import-manifest.json` lists files, sections and image references. `design-tokens.json` records exact colors, radii, fonts and dimensions. No live Figma extension import has been performed.

## Asset provenance

Original Puratek logos are preserved separately. 22 r5 editorial illustrations remain active; W3 has a new checklist illustration in `assets/automation-r6`, generated with the built-in image tool. Exact prompts and masters are included in the full source handoff. Four official product images downloaded from puratekpeptides.com on 28 September 2026 are recorded in `assets/products-r6/sources.json`; only resizing/JPEG compression was applied. Their labels were not regenerated or altered. W1 displays the requested product names without inventing current pricing or batch matches.

## Download everything

Use **Download all emails + images (ZIP)** on the homepage, Figma library or any email review page. Every standard build creates `dist/downloads/puratek-all-emails.zip`, so the download is included in a static deployment automatically.

The archive contains 48 Figma import pages, 24 FunnelKit HTML drafts, one responsive preview per design, all linked image assets, original illustration masters, original product photos, prompts, source metadata and instructions. Open `START-HERE.html` after extracting the complete archive. Import individual Figma HTML pages through your extension; the ZIP is not a native `.fig` file.

The library shows all automation families, their launch status and the gaps found in the GreenLabs framework audit. Downloading the archive does not change their draft or incomplete status. Puratek uses 10%, not the other store’s 15%.

Revision 8: all views use white hero text on navy with product compositions. W2 is the COA checklist; W2-alt is the reference comparison. Desktop W1 catalog uses two columns, mobile uses one.
