# Email design and asset review — revision 7

> Revision 7 unifies all outputs into one orange/navy/white palette and removes alternate theme exports. See 18-Feedback-and-Implementation-Reference.md.

> Revision 6 adds real catalog vial photos, an original-logo panel with each illustration, and a new W3 checklist visual. `assets/products-r6/sources.json` records official image URLs and hashes; originals are retained. Earlier asset tiers below are historical and superseded for the client-requested W1 draft.

Source: `Puratek peptide-20260928T092318Z-1-001.zip`, inspected 28 September 2026. It contains 14 product JPEGs, a long landing-page reference and a visual brand board. The board is a visual reference, not a verified legal/corporate fact sheet.

## Current email interpretation — revision 7

Primary orange `#F7931E` fills the hero and its illustration region in every email. It also controls CTA fills, offer outlines and support accents. Headings on orange are deep navy `#13172A`; main reading surfaces are white, with gray used only for secondary content. The original Puratek logos remain intact. A deep navy footer gives the email a clear endpoint.

| Element | Current implementation |
| --- | --- |
| Primary orange | `#F7931E`, large hero surface in the shared palette |
| Hero text | `#13172A`; live HTML text |
| Body | White/light gray; 16px / 25px copy |
| Headline | 34px / 39px desktop; 28px / 33px mobile |
| Width and insets | 600px max email; 32px desktop / 24px mobile |
| Hero art | One unique 3:2 orange-and-white illustration per email, fluid 536px desktop / 327px at a 375px mobile width |
| Radius | 12px artwork/support/offer details, 100px CTA |
| Footer | Deep navy #13172A; existing light logo; readable 13px / 20px text |
| Dark mode | One authored palette; no alternate theme exports |

No fixed heights on text, no cream hero gradient, no gold-toned podium, no black coupon frame. All critical copy and buttons remain live text. Gradients, absolute positioning, overlapping content and external fonts are not layout dependencies.

## Current artwork

`assets/automation-r5/` contains 23 new masters, 23 optimized JPEGs and exact prompts. Each email has its own assigned image; none is reused across templates. `assets/manifest.json` identifies consumers, dimensions and hashes. The old navy and revision 4 ivory/gold imagery have no active consumers and are not copied into current output.

The original client product assets remain preserved in the workspace. Revision 5 deliberately uses purpose-specific editorial metaphors instead of product renders; it does not redraw packaging or imply real batch documents. See [orange-system plan](14-Orange-System-and-Artwork-Plan.md).

## Asset decisions

| ZIP filename | Visible label / content | Decision |
| --- | --- | --- |
| image-01.jpg | B/c 157 | Hold: apparent naming inconsistency; do not redraw label |
| image-03.jpg, image-25.jpg | GLP-1S | Excluded from promotion |
| image-04.jpg, image-05.jpg, image-22.jpg | GLP-2T | Excluded from promotion |
| image-06.jpg | GLP-3R | Excluded from promotion |
| image-09.jpg | SS-31 | Hold: outside prior neutral tier; do not expand list without review |
| image-10.jpg | Kvp | Hold: does not match KPV spelling |
| image-11.jpg | Ghk-cu, 100 MG | Selected for draft references; verify current SKU, label and COA before release |
| image-16.jpg, image-24.jpg | NAD+ | Retained as source assets; not needed for the initial email set |
| image-20.jpg | Klow | Hold: blend/identity review |
| image-27...jpg | Melanotan 2 | Excluded from promotion |
| puratek landing.png | Full website screenshot | Visual reference only; not embedded as an email screenshot |
| ChatGPT Image Jun 4…png | Brand board, TESA, Australia location and various claims | Palette/layout reference only; do not reuse unverified address, contact details, claims or restricted-product panels |

The original ZIP is retained unchanged. The table above records the source-product review, not the active revision 5 artwork map. All production image URLs still require verified hosting on the approved client domain.
