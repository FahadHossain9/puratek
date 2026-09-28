# Puratek campaign handoff — revision 6

The current campaign is in `puratek-email/`. Use Node 22+, `npm ci`, then `npm run serve`. Open http://127.0.0.1:5173/.

## Delivered

- 23 campaign roles and one alternative to W2; 24 dedicated review pages with mobile/desktop switches.
- Team-supplied W1–W3 strategy and complete copy implemented as individually authored sections, including all four W1 product cards.
- W2 Purity vs. Identity is primary; W2-alt preserves the earlier COA checklist. Choose one at Day 2.
- 96 separate Figma HTML variants with embedded images and live text.
- 24 FunnelKit Raw HTML drafts with a strict configuration/export utility. Cart sample rows are fully removed and replaced by the installed event tag.
- 22 retained editorial illustrations plus one new W3 checklist illustration. Original-logo brand panels; four real Puratek catalog photographs with source URLs, original files and hashes.
- Full current copy deck (05), launch scope (07), implementation mapping (09), Figma handoff (13), campaign plan/research (15), per-email role/section map (16), and machine-readable flow specifications in `dist/flows/specification.json`.

## Review order

Start with W1, then compare W2 and W2-alt, then W3. Review the cart branch next. The homepage proposes seven starting templates; other roles remain available for a later stage. Review notes stay in the browser and must be downloaded/copied to share.

## Validation boundaries

The build checks HTML, asset references, required footer markers, wording, clipping budget and local flow decisions. Browser QA covers four widths, light/dark, dedicated review pages, all Figma variants and image-disabled fallback. The export utility checks placeholders, hosted-asset mapping, cart-tag parameter preservation and sample removal.

These are local artifacts, not live automations. Actual Figma extension behavior, the store's cart-row options, delivered mailbox rendering, coupon eligibility, postal address and opt-out configuration remain installation checks. No GitHub push, Vercel deployment, store change or send is implied.

## Copy/source decisions

Pasted `:and` and `:not` were normalized to readable punctuation. Other non-welcome roles were authored for review. WELCOME10 remains the requested code; verify its actual configuration. The previous blanket 99% refund claim is removed because current Puratek pages disagree on guarantee conditions. The client-requested PUR-3R card is retained transparently in the local draft; this supersedes the old internal exclusion, not product/provider review.
