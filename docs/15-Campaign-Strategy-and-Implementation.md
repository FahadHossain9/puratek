# Puratek documentation campaign — revision 6 implementation plan

The team's latest welcome copy is the editorial source. Build the campaign around **standard → evidence → evaluation → purchase support → batch continuity**, rather than fitting every message into a coupon template. Brand source: puratekpeptides.com, reviewed 28 September 2026.

## Implementation

1. W1: the documentation standard, welcome offer, three verification steps, four curated product cards in one column. Preserve all four requested products, including PUR-3R, as client-directed draft content. Normalize pasted `:and` / `:not` punctuation only.
2. W2: use the later Purity vs. Identity version as the main design, with a compact semantic comparison table; stack the educational cards on small screens. Preserve the earlier COA checklist version as a selectable alternative, not an additional scheduled send.
3. W3: specifications, documentation and traceability checklist, catalog path and WELCOME10 reminder. Replace the old dispatch-focused W3.
4. Rewrite the other 20 email briefs/copy for a distinct role in this same campaign. Do not add a long discovery section to a saved-cart or service email just to match W1.
5. Keep primary #F7931E, original logo files, 600px desktop / fluid mobile, table structure, inline styles and live editable text. Add an explicit branded illustration panel; use actual source vial photos in curated product modules. No invented COAs, labels or laboratory scenes.
6. Dynamic cart images, item names, quantities, prices and restore links come from the installed FunnelKit event tag. Curated W1 products are static editorial selections, not personalization. Export replaces the complete sample cart with a configured plugin block.
7. Deliver separate mobile/desktop and light/dark Figma HTML, review pages, source copy, flow specifications and FunnelKit module mapping. Verify browser rendering, links, blocked-image fallbacks, long cart content and export mapping. No live sending or platform activation.

## Scope

23 email roles remain in the library, plus one W2 design/content alternative. Initial proposal: W1–W3 and C1/C2a-or-C2b/C3 (seven templates, six messages on either branch). W4, C4, broadcasts and lifecycle paths are optional and event-dependent, not a requirement to send all 23.

## Source decisions and unresolved evidence

- The user supplied four W1 products; this supersedes the old internal blanket product-name ban for that draft. It does not resolve product/provider/legal review. Do not disguise compounds or imply human-use benefits. PUR-3R remains clearly identified in the handoff.
- Puratek's homepage describes a 99% guarantee, while its separate policy has different conditions/thresholds. Remove blanket numeric refund promises from this campaign until the client reconciles the terms.
- WELCOME10 is the team's requested visible code. Configure and verify one actual first-order offer across the welcome sequence; do not invent an expiry or print “whenever” as an unlimited eligibility promise. The client's supplied wording is retained for design with the configuration dependency documented.
- Static product photos show packaging, not current order/batch evidence. Current availability, variation, printed label and COA must agree before publication. No generated packaging is presented as the actual product.
- Test specificity matters: HPLC and mass spectrometry are examples of relevant evidence, not claims that every SKU has identical tests. Keep the comparison educational and method-qualified.

## Primary references

- [Puratek brand and documentation positioning](https://puratekpeptides.com/)
- [Puratek catalog](https://puratekpeptides.com/shop/) and [certifications](https://puratekpeptides.com/certifications/)
- [Purity guarantee policy](https://puratekpeptides.com/purity-guarantee-policy/)
- [FunnelKit Raw HTML](https://funnelkit.com/docs/autonami-2/email-builder/ways-to-build-an-email/)
- [FunnelKit merge tags and product rows](https://funnelkit.com/docs/autonami-2/automations/merge-tags/)
- [FDA intended-use enforcement example](https://www.fda.gov/inspections-compliance-enforcement-and-criminal-investigations/warning-letters/gram-peptides-721806-03312026)
- [FTC CAN-SPAM guide](https://www.ftc.gov/business-guidance/resources/can-spam-act-compliance-guide-business)

## Research findings applied to the campaign

| Observed source | Implication for these emails |
| --- | --- |
| Puratek homepage: documentation, independent testing, first-order offer and US fulfillment | Use the client's documentation positioning and verified product-page destinations. Do not carry over physiological product descriptions or testimonials. |
| Official product pages: BPC-157, GHK-Cu, PUR-3R and MOTS-c images | Use those originals instead of inventing package labels. The pictured variations are 10 mg, 100 mg, 30 mg and 40 mg respectively; GHK-Cu/PUR-3R/MOTS-c card titles deliberately do not promise a selected size. |
| [Policy page](https://puratekpeptides.com/purity-guarantee-policy/): 98.5% threshold and conditional eligibility | Omit the old unconditional 99% refund sentence. |
| [Other guarantee page](https://puratekpeptides.com/puratek-purity-guarantee/): standard 99%, a 97% exception and separate test-credit terms | Request one authoritative policy at deployment. Copying a number from the homepage is insufficient. |
| FunnelKit documentation distinguishes contact and event tags | Keep curated discovery static; use the captured cart event for selected products. Never populate a cart from unrelated last-order data. |
| FDA intended-use example; FTC commercial-email requirements | Do not infer that a research-use footer validates every product/claim. Preserve factual documentation messaging and complete commercial-email footer configuration. |

Public pages verify what the brand currently says and shows, not the underlying truth of every batch claim. No laboratory report, current SKU inventory, actual coupon configuration or provider approval was independently authenticated in this workspace.

## Why each flow differs

Welcome earns understanding before asking for evaluation. Cart messages remove the friction of returning to an existing selection. Post-purchase connects physical labels and documents after verified events. Repeat engagement prompts current-information review, rather than assuming use or a reorder interval. Win-back asks whether the information remains useful and must honor an actual sunset rule. Broadcasts extend one educational/service topic at a time. High-value support offers a conversation instead of another discount.

The complete email-by-email brief, section order and measurement is generated in guide 16. These are strategic recommendations to test, not claims of a measured lift.
