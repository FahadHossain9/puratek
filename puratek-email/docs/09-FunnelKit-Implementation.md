# FunnelKit implementation — campaign revision 6

Use **Send Email → Raw HTML** for these table-based templates. Text, sections, buttons and coupons remain HTML; product photos and illustrations are image assets. These files are not a FunnelKit workflow-import JSON. The portable flow specification must be mapped to the installed events and conditions.

[Raw HTML documentation](https://funnelkit.com/docs/autonami-2/email-builder/ways-to-build-an-email/) · [Merge tags](https://funnelkit.com/docs/autonami-2/automations/merge-tags/)

## What stays static and what comes from the store

| Module | Source | Implementation |
| --- | --- | --- |
| Brand header and illustration | Original logos + campaign artwork | Upload the supplied files; map exact HTTPS URLs. Original logo remains separate from the illustration, so no generated logo is substituted. |
| W1 curated discovery | Four editorial product choices from the team | Static image/name/copy/product-page link. No fixed prices, stock count or personal recommendation claim. Refresh imagery when packaging/variation changes. |
| Abandoned-cart products | Actual captured WooCommerce cart event | Replace the whole preview block using the installed cart-items tag. Choose product rows with thumbnails if supported by the installed version, one product per row. Do not use a two/three-column grid. |
| Cart restore | Captured cart event | Use the installed restore-link tag; verify it restores the same cart. |
| Order details | Transactional system | P1 points to the existing confirmation. No fabricated order-specific data is included. P2 explains matching a label and COA; it does not invent an automatic batch-to-order integration. |
| Welcome offer | Client-requested WELCOME10 | Map `%%COUPON_CODE%%` to the verified WELCOME10 code. Check first-order eligibility across W1, W2, W2-alt, W3 and optional W4. Do not generate a different code for every send. |
| Cart first-order offer | Configured coupon action | C2a uses its mapped coupon and expiry, single use and no stacking. Route returning customers to C2b. |
| Footer | Actual sender/list configuration | Valid postal address, installed unsubscribe and preference destination, published privacy policy. |

The earlier text-only/no-thumbnail rule was a conservative internal choice. The user now explicitly requests real dynamic product images. Revision 6 supports that direction. **The sample row is a design target, not proof of the installed plugin's HTML.** Public FunnelKit docs list configurable product-row templates for order items; they do not establish the exact cart-tag options available in this store. Copy the event-specific tag from the installed picker, preview it with images, then read back a delivered seed email. If it cannot render a single-column image row, use the native row output or obtain a separately tested custom callback/template; do not silently promise pixel-identical markup.

## Mapping and export

`config/funnelkit.example.json` lists the required markers and static asset URLs. Copy it to a local config and fill actual values. Then run:

```sh
npm run funnelkit:export -- --config config/funnelkit.local.json
```

The exporter produces configured Raw HTML and metadata only after mappings and confirmations pass. `dist/preview`, review pages and `dist/figma` must never be pasted into FunnelKit. They include sample values or embedded images for design review.

| Marker | Required value |
| --- | --- |
| `%%CART_ITEMS%%` | Full configured cart-event merge tag for rows, including supported image options |
| `%%CART_LINK%%` | Installed cart restore URL tag |
| `%%COUPON_CODE%%` | Verified WELCOME10; shared welcome identity |
| `%%COUPON_EXPIRY%%` | Only if a chosen design contains it; actual expiry, never a mock date |
| `%%CART_COUPON_CODE%%`, `%%CART_COUPON_EXPIRY%%` | C2a coupon and actual expiry |
| `%%SALE_CODE%%`, `%%SALE_END_DATE%%` | Optional approved B3 offer |
| `{{business_address}}`, `{{unsubscribe_link}}` | Verified address and installed unsubscribe syntax |
| `%%PRIVACY_URL%%`, `%%PREFERENCES_URL%%` | Real published destinations or verified preference tag |

Map each actual image URL, including both logo versions, the selected editorial image, and W1's four catalog photographs. The preview's cart thumbnails are not static send assets; they disappear with the complete cart block during export. FunnelKit supplies actual selected-product thumbnails.

## Configure inactive flows

- Welcome: recorded marketing opt-in → W1 immediately → W2 at day 2 → W3 at day 4. Choose **W2 OR W2-alt**. Recheck consent, no first order, active competing cart and offer eligibility at every step. Withhold a stale-offer message; do not print an expired promise. Optional W4 is not part of the initial sequence.
- Cart: eligible abandonment → C1 at 1h → C2a if no previous order, otherwise C2b at 24h → C3 at 48h. Times are measured from abandonment; the 24h frequency cap can defer the next message. Exit on order, empty/recovered cart or suppression. C4 is optional.
- High-value carts: route to H1 only once that support branch is configured; otherwise hold them out of standard cart enrollment.
- Post-purchase, repeat, win-back and broadcast: map the specification's actual events and audience gates. Never infer delivery from elapsed time or replenishment from presumed product use. X3 requires a real suppression action after nonresponse.

## Verify before activation

Save/read back the HTML; preview a controlled contact/cart with one, several and missing-image products; test long names and coupons at 320px; verify restore and coupon behavior; test purchase-between-delay and unsubscribe-before-send; confirm first-order and mutual-exclusion branches; inspect actual mailbox rendering and delivered opt-out headers. Keep flows inactive until those checks pass.

The workspace is not connected to the client's WordPress installation. No coupons, live flows, messages or customer records were created. [Automation setup](https://funnelkit.com/docs/autonami-2/automations/setting-up-your-first-automation/) · [Coupon auto-application](https://funnelkit.com/docs/autonami-2/carts/auto-apply-coupons/)
