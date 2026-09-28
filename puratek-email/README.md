# Puratek documentation campaign — r6

A campaign-specific email system with the team’s W1–W3 welcome copy, 20 additional roles and one mutually exclusive W2 alternative. All emails use Puratek orange #F7931E, original logos, live text and mobile-first table layouts.

```sh
# Node 22+
npm ci
npm run check
npm run serve
```

Review: http://127.0.0.1:5173/ — open one name at a time, compare mobile/desktop side by side and download local feedback notes.

## Outputs

- `dist/send`: 24 FunnelKit Raw HTML deployment drafts; configure before importing.
- `dist/figma`: 48 standalone files (24 designs × mobile/desktop, one brand palette), embedded assets, no scripts/iframes.
- `dist/emails`: dedicated review pages.
- `dist/flows/specification.json`: inactive portable specifications, not plugin import JSON.
- `docs/05-Current-Copy-Deck.md`, `15-Campaign-Strategy-and-Implementation.md`, `16-Campaign-Role-and-Section-Map.md`: copy and strategy sources.
- `assets/automation-r5`: retained masters/prompts; W3's older artwork is inactive.
- `assets/automation-r6`: new W3 checklist master and exact built-in generation prompt.
- `assets/products-r6`: four official product photographs, resized email exports and provenance. Labels are unchanged.

## Checks and handoff

```sh
npm run check
node --import tsx scripts/test-funnelkit.ts
QA_BROWSER_CHANNEL=chrome npm run qa:render
npm run package
```

`npm run package` requires current successful browser evidence. It writes the full review ZIP and the Figma-only ZIP under `handoff/`. Changing source/assets requires rebuilding and rerunning relevant checks. `node --import tsx scripts/register-artwork.ts` refreshes current image hashes and dimensions without replacing the campaign selections.

## FunnelKit

Read `docs/09-FunnelKit-Implementation.md`. Map actual static image URLs, verified WELCOME10, footer/list fields and the installed cart tag using a local copy of `config/funnelkit.example.json`:

```sh
npm run funnelkit:export -- --config config/funnelkit.local.json
```

A configured cart replaces the whole sample block; product images are supplied by the store. Raw HTML output does not create workflows or coupons. Seven initial templates cover welcome/cart. W2 or W2-alt occupy the same step. Other roles require their specified event/audience data.

Actual extension import, native cart-row rendering, mailbox delivery, legal/provider eligibility and live automation state are not verified by the local build. No live sends or deployment were performed.

Current design decisions and inventory reconciliation: [29 September feedback reference](docs/18-Feedback-and-Implementation-Reference.md). [Blank notes file](docs/19-Working-Notes.md) is available for future notes.
