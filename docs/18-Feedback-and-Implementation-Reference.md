# Puratek feedback and implementation reference

Updated 29 September 2026. For current hero, imagery, catalog and W2 decisions, see [revision 8 feedback implementation](20-Final-Marketer-Feedback.md). This document preserves the earlier inventory and integration context.

## Source and scope

Reviewed the two supplied Bengali meeting transcripts, the website screenshot, and Puratek_Peptides_Email_Template_List.docx (inventory dated 28 September 2026). Statements in these sources are feedback and reported observations, not instructions to activate automations, contact people, modify a live database, or reproduce private meeting details. No raw transcripts are published in this repository.

The current request is to simplify the design, correct the colors, maintain one version, retain mobile/desktop comparison, document the work and push it to Git. This revision covers that work. The meeting's proposed WordPress importer is a separate integration task; it is not implemented or verified by this static email project.

## Design decisions applied

- One authored palette for every email and both screen sizes. No light/dark selector, separate dark preview, or duplicate theme exports.
- Deep navy #13172A replaces charcoal in the brand header, footer and headings. This navy was verified in the public homepage CSS on 29 September 2026 and matches the supplied website reference.
- Retain established logo orange #F7931E for heroes, CTAs, offer borders and accents. The website also uses other orange shades; these are not additional email palettes.
- White reading surfaces, quiet gray secondary surfaces, readable navy text. Original logo assets remain intact; white wordmark on navy header/footer.
- Retain the existing section sequence, 600px email width, 375px mobile view, 32px desktop / 24px mobile padding, 12px cards and pill buttons. Mobile and desktop remain side by side.
- Keep copy, links, coupons, cart data and legal footer as live HTML. Keep decorative art as images. A whole-email image would compromise editability, dynamic content and image-blocked reading.
- Preserve the marketer's welcome copy and 10% WELCOME10 offer. W2-alt is an editorial alternative to W2, not another theme or additional send.
- 24 designs represent 23 email roles plus one W2 alternative, not 24 automations. Three broadcast drafts are campaigns, not lifecycle automation steps.
- 48 standalone Figma views, 24 responsive previews and 24 send drafts. ZIP downloads are rebuilt from current output.

## Inventory reconciliation

The supplied DOCX reports settings observed on 28 September, not independently verified delivery. Its per-system counts overlap and must not be summed into a unique template count.

| System | Reported state | Relationship to this project |
| --- | --- | --- |
| FunnelKit transactional | 14 notification types, 10 enabled and 4 disabled | Existing operational emails, not replaced by the marketing drafts |
| FunnelKit abandoned cart | One active workflow with three emails; sequential waits of 1h, 24h, 48h | Existing recovery path must be mapped before importing the proposed C1/C2/C3 sequence; do not treat sequential waits as elapsed times |
| WooCommerce | 23 notification types: 7 enabled, 3 manual, 13 disabled | Several disabled native notifications have active FunnelKit equivalents; retain existing routing |
| Spark | Four WordPress editor entries | Editor controls do not prove sending state |
| AffiliateWP | Seven enabled notifications and one disabled monthly summary | Separate affiliate operations; outside this marketing library |
| Fluent Forms | Two enabled notifications | Subscription notification is not evidence of a customer welcome sequence or successful delivery |
| Cart Abandonment Recovery plugin | Tracking and one 1h template enabled | Potential overlap with FunnelKit recovery; actual duplicate delivery is unproven |
| FunnelKit template library | Eight saved designs | Saved designs are not active workflows; historical “Abandoned Cart 15%” is not approval for a Puratek 15% offer |
| FunnelKit broadcasts | One draft named Test | No scheduled/ongoing/completed broadcasts reported in that inventory |
| Omnisend / other notices | External dashboard not reviewed; other custom and Stripe payout notices unverified | Remain outside verified coverage |

New Account is transactional account confirmation, not the full opt-in welcome series. Post-purchase marketing must not replace order receipts, processing, shipment or refund notifications. Admin notifications have different recipients and must remain separate.

## FunnelKit implementation handoff

1. Use current source HTML directly; Figma is an optional review/design handoff, not a necessary round trip for implementation.
2. Upload image resources and configure their hosted URLs. Map address, privacy/preferences, unsubscribe, restore-cart and coupon fields to the installed platform.
3. Keep real cart items, product images, prices and recovery links dynamic. Current browser cart rows are sample data. The exporter replaces the complete marked cart region with the installed cart tag.
4. If rebuilding in the visual builder, use static HTML/content sections above and below the native dynamic cart block. Do not split an arbitrary HTML table at an unsafe boundary. Raw HTML is not claimed to convert into native editable builder widgets.
5. Inspect the installed FunnelKit/Pro versions, licenses, native export schema and a real saved draft before designing an importer. A ZIP of this library is not a native workflow import package.
6. Future importer requirements: stable IDs and versions, preview/dry run, explicit selection of individual templates or whole flows, detection of manual changes, preserve-or-review conflicts, backup/rollback, idempotent updates and inactive defaults. Do not overwrite unselected records or activate on import.
7. Match existing recovery/transactional ownership before import. Test purchase exits, opt-out suppression, delays, coupon eligibility and scheduling on an isolated test environment with outbound mail blocked or restricted to agreed test recipients.
8. Review each inactive flow, test real inbox rendering and dynamic data, then have the responsible team activate it separately. A queued job or SMTP error proves neither successful delivery nor correct inbox rendering. Local hosting alone does not prevent outbound email.

## Testing and remaining work

One HTML palette is tested in browser light and dark preferences at 320, 375, 430 and 680px. Mail clients can still override colors; this is not a promise of identical rendering in every inbox. Test Gmail, Apple Mail and Outlook in actual light/dark modes before sending.

Existing role-specific content gaps remain labeled in the library and framework coverage guide. Approved social proof, purchase-specific recommendations and some reorder targeting still need the marketing team's input. No claims, testimonials, audience size or revenue claims were invented from meeting discussion.

Weekly campaign planning remains a separate workstream. No campaigns were scheduled, test emails sent, live automations created or database records changed by this revision.

Use 19-Working-Notes.md for additional feedback; it is intentionally empty at creation.
