# Reference analysis and design implementation — revision 4

**Historical reference analysis. Revision 5 supersedes the color and artwork implementation below; see [current orange system](14-Orange-System-and-Artwork-Plan.md).**

28 September 2026. All four supplied references were inspected in full and as readable section crops. The three PDFs each contain one tall 600pt-wide page: AusBioLabs 5465pt high, GreenLabs 6015pt, HerGlowLabs 5433pt. The PNG is a separate Neurogen design, 600 × 4637px; it is **not** the GreenLabs PDF despite sharing the filename stem.

## 1. AusBioLabs — `ausbiolabs example.pdf`

| Section, in reading order | Observation | Puratek adaptation |
| --- | --- | --- |
| Logo and ambient header | White wordmark, amber light and almost-black ground create a strong identity | Keep original Puratek logo; white header and restrained orange top rule, rather than adopting another brand's black/gold palette |
| Science Week hero | Short oversized headline, compact context, obvious CTA | Larger focused live heading and one action per email; no imported event or dates |
| Product still life | Depth from podium, glass, bottle and packaging; imagery gives scale | New ivory/orange studio composition using supplied GHK-Cu as reference; no prohibited compounds, invented test badges or laboratory claims |
| Verification introduction | Explains why documentation matters before listing proof | Dedicated documentation section labels and concise lead copy |
| Four proof tiles | HPLC, identity, COA and archive form a clear visual group | Numbered vertical proof rows with existing verified copy; no two-column grid or invented public archive |
| Floating vial composition | Adds depth but overlaps and consumes height | Self-contained raster panel in document flow; no overlapping text or image positioning required |
| Verification CTA | Repeats the main objective | One primary CTA retained; no need to repeat it after every block |
| Three review/product cards | Product image, testimonial, stars and button repeated | Exclude third-party testimonials, stars and prohibited products; keep only neutral product identity and batch-document context |
| Collection CTA | Another competing navigation path | Omit broad multi-product merchandising |
| Human support section | Distinct supporting message and team context | Compact support card with existing Puratek contact details; do not import credentials or staff claims |
| Footer | Repeated wordmark, brand summary, navigation and disclaimer | Dark charcoal footer using existing light logo; readable legal text and actual Puratek links. Fix the reference's brand mismatch in subscription text and low-contrast fine print by not copying either |

## 2. GreenLabs — `Welcome Email 1.pdf`

| Section | Observation | Puratek adaptation |
| --- | --- | --- |
| Welcome/offer hero | Warm light, centered type, large 15% offer, code and pill CTA before merchandise | Offer-first W1/W4/B3; keep Puratek's existing 10% terms and one live CTA |
| Five-vial shelf | Tangible product photography anchors hero | One eligible product composite, not a group implying a bundle |
| Editorial introduction | Divider and short heading establish next section | Small orange section labels and breathing room |
| Three product panels | Repeated image/text split, long mechanisms and many buttons | Do not import compound copy or split layouts; use one focused body section |
| Repeated discount CTA | Reinforces offer but lengthens email | Single offer/action block near the beginning of offer-led emails |
| Blends grid | Two-column category expansion | Exclude; outside Puratek house rules and client's single-column request |
| Starter kit and bulk grid | Adds injection-associated props and separate 30% discount | Exclude kits, syringes, bulk promises and unapproved discounts |
| Large open kit | High-quality dimensional photography, very tall | Adopt studio lighting/depth only; no kit depiction |
| Dark reminder block | Clear change in pace, repeats offer | Use charcoal offer frame with a cream inset; no duplicate coupon blocks |
| Three proof columns | Useful labels but crowded on a phone | Vertical proof treatment where relevant |
| Large dark footer | Visually definite end, logo/navigation/legal | Compact charcoal footer; preserve US 21+ and required disclaimer rather than AU 18+/TGA language |

## 3. HerGlowLabs — `International Literacy Day.pdf`

| Section | Observation | Puratek adaptation |
| --- | --- | --- |
| Campaign strip and logo | Event label separates campaign from evergreen template | Small email-purpose eyebrow, not an invented holiday campaign |
| Editorial hero | Soft gradient, bold headline, one question and CTA | White-to-cream hero with live title; no pink/serif brand substitution |
| Product lineup | Hero depth and bottom alignment | One raster panel; no prohibited-product lineup |
| Framed education panel | Border, three icons and decorative edge objects create a module | Single-column document checklist with numbered live text; no floating vial edges or overlaid copy |
| Four alternating product cards | Attractive repetition but too many goals; benefits/mechanisms and prohibited products | Exclude merchandising and claims; apply consistent card geometry to relevant existing content instead |
| Editorial closing CTA | Returns to initial premise | Keep the email's original primary destination |
| Rewards/referral pair | Secondary commercial program with separate incentive | Exclude unverified rewards/affiliate programs and $50 promise |
| Footer | Outlined navigation, readable brand close, oversized cropped wordmark | Original Puratek light logo on charcoal with standard legal links; no huge decorative wordmark or excessive navigation |

## 4. Neurogen — `Welcome Email 1.png`

| Section | Observation | Puratek adaptation |
| --- | --- | --- |
| Logo/hero | Gold on ivory, selective headline emphasis, coupon and action | Closest palette reference; keep Puratek orange, charcoal and system typography |
| Three-vial scene | Gold caps and studio reflections create a polished opening | New supplied-product composite in warm ivory/orange; product text reviewed separately |
| Curved transition | Moves from hero into white body | Use clean section boundaries and rounded panels; avoid fragile email clipping/masks |
| Four category cards | Two-column merchandising and body-system categories | Exclude categories, claims and grids |
| Broad catalog CTA | Central full-width action | Retain one wide primary button |
| Confidence section | Short explanatory copy and colored key phrase | One section label plus concise copy; no new unsupported assurances |
| Four proof tiles | Clear borders and icons form a strong visual system | Stacked numbered proof/step blocks, no four-tile matrix |
| Hand holding vial/splash | Dramatic composition but implies contact/use | Exclude hand, splash and bodily context |
| Final coupon panel | Bold offer repetition | One offer block only, visible early where the offer is the message |
| Black footer | Strong contrast and clear endpoint | Charcoal Puratek footer with readable text; do not import Australian claims, age threshold or copyright brand |

## Shared design decisions

- One column at both widths; 600px desktop content and 375px mobile review. Main body 16px/25px, heading 34px/39px desktop and 28px/33px mobile; short hero lines, no fixed-height text.
- Keep original Puratek logos, orange #F7931E, charcoal #242424, ivory #FFF6EA, white and gray. Panel radius 16px, inset offer 12px, pill CTA 100px. Vertical rhythm varies by purpose, not arbitrary filler height.
- Distinct offer, documentation, service, cart and editorial treatments share one implementation. All 23 templates get the header, section, action, support and footer system.
- New 3D assets contain no baked-in email headings, discounts, buttons or testimonials. Product label remains raster; surrounding captions and email text remain editable HTML.
- Keep supplied original product file, generated masters, optimized exports, exact prompts, hashes and approval notes. Generated product composite is draft artwork and is not a pixel-identical reproduction of the original photograph.
- Do not reproduce the references as long all-image emails. No new claims, testimonials, AU locality, discount rates, human-use imagery or prohibited compound marketing.

## Per-email adaptation

| Email | Treatment and content emphasis |
| --- | --- |
| W1 | Offer first; studio product panel; short introduction |
| W2 | Documentation header; numbered review checklist; no promotional product image |
| W3 | Service header; dispatch details; clear contact route |
| W4 | Offer-first reminder; no repeated proof strip |
| C1 | Compact cart header and saved items; no hero image |
| C2a | Cart + one offer block after items; no repeated proof strip |
| C2b | Compact returning-customer reassurance and cart |
| C3 | Readable vertical question cards, then cart/action |
| C4 | Quiet personal support note |
| B1 | Editorial COA guide with numbered live text |
| B2 | Dispatch editorial header and relevant proof |
| B3 | Offer first, abstract amber sculpture; hold for campaign approval |
| P1 | Order-support note, no order-confirmation duplication added |
| P2 | Three documentation steps; no product merchandising |
| P3 | Service-feedback note, no invented ratings |
| P4 | Document-support note |
| R1 | Documentation-led re-engagement |
| R2 | Catalog editorial with abstract amber sculpture |
| R3 | Quiet catalog planning note, no assumed replenishment cadence |
| X1 | Restrained documentation invitation |
| X2 | Direct support note |
| X3 | Minimal sunset note, no promotional imagery |
| H1 | Personal support treatment, no automated upsell |

## Figma and FunnelKit delivery

Build 92 single-email Figma files (23 × mobile/desktop × light/dark), embedded raster assets, live text and buttons, no scripts or iframe. Provide a complete import directory and per-email download links; separate PNG screenshots are visual references only. Preserve source tokens and an asset inventory for manual component creation if the extension does not infer native components. Do not claim a native Figma file, Auto Layout or connector fidelity without testing the actual extension.

FunnelKit receives separately built table-based HTML with hosted-image placeholders, not base64 assets or review controls. Solid fills remain if gradients/radii simplify. Validate all templates, both review sizes, blocked images, overflow, contrast, primary actions and export completeness. Installed FunnelKit and delivered mailbox QA remain required before activation.
