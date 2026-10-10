# Puratek All Flows — local handoff (plugin 1.2.0)

## What these ZIPs do

Install `puratek-flow-importer-1.2.0.zip` under WordPress Plugins. Upload `puratek-all-flows-local-1.0.0.zip` under Tools -> Puratek Flow Importer. Supply `all-flows-local-mappings.json`, refresh the preview, select all seven flows, acknowledge review status and import. The result is seven inactive native FunnelKit automations, not live email activation. Re-uploading the same package skips the existing revisions.

The original `puratek-main (1).zip` is React design/specification source. It is byte-identical to the earlier source ZIP (SHA256 08718D2001D59F346C4FF70D8447F53A3B98E967193300B41D968EE87CA2C4BD). WordPress must receive the prepared all-flows package, not that source archive. The separate converter turns the reviewed source into the prepared package locally; it never executes source inside WordPress.

## Implemented scope

Seven primary flows / 21 template variants / 18 native action steps:

- Welcome: W1/W2/W3 at opt-in +0/+48/+96 hours; first order exits.
- Cart: C1/C2/C3 at abandonment +1/+24/+48 hours. C2 chooses C2A for zero orders or C2B for returning buyers at execution. The shared rolling cap normally moves actual sends to +1/+25/+49 hours. New order, empty/recovered/replaced cart exits.
- High-value support: total >500 USD OR quantity >=10, H1 at abandonment +1 hour, replacing standard cart. An in-flight standard cart that crosses the threshold transfers to Support using its original abandonment timestamp. High-value qualification remains sticky for that cart.
- Post-purchase: payment +2h, dispatch +1d, verified delivery +7d/+21d. Missing events wait; they are never inferred from order age. Refund/dispute/support flags hold; a newer order exits.
- Repeat: last order +30/+60/+90 days for purchasers meaningfully engaged within 90 days. Inactivity/new order exits; support/refund/dispute holds.
- Win-back: approved inactive purchasers at last meaningful engagement +90d, then actual X1 send +7d/+14d. Order/new engagement exits. Completion sunsets the coordinated marketing flows; uncertain delivery requires review instead of claiming completion.
- Broadcast: one approved B1/B2/B3 option per event. Minimum seven days between enrollments AND actual broadcast sends. Active eligible Welcome/cart holds. B3 needs explicit promotion approval and a matching valid 10% coupon.

All seven share consent, country, known-source, suppression, recipient lock, send history, uncertain-delivery hold and one marketing email per rolling 24 hours. Priority is Cart/Support, Welcome, Post-purchase, Repeat/Win-back, Broadcast. Equal priority resolves by earlier enrollment, then stable flow/run ID. A message expires 24 hours after its due time. Missing fulfillment events stop waiting after 180 days (local safeguard). Relative schedules use UTC elapsed seconds. No unverified PST/DST calendar is guessed.

Re-entry windows from the source: Welcome once, Cart/Support 7 days, Post-purchase 1 day, Repeat 90 days, Win-back 365 days, Broadcast 7 days. Version changes do not bypass these identity rules. Older active legacy Welcome revisions block the new coordinator to prevent duplicate welcome sends.

Optional W4/C4, the W2 reference alternative and 11 dependency-gated opportunities are NOT automatically enabled. They have no fully approved event/timing definition in this package. This is coverage of all seven primary flow categories, not a claim every optional idea is implemented.

## Where to inspect locally

Site: http://email-test-puratek.local

- Tools -> Puratek Flow Importer: ZIP upload, mapping, selection, version history and rollback.
- Tools -> Puratek Flow Rules: separate expandable sections for each category, conditions, timing, exits and shared policy.
- Tools -> All flows verification: local-only native test runner, installed separately as an MU plugin.
- FunnelKit -> Automations: package drafts IDs 28 through 34, all inactive. Separate labelled execution fixtures are also inactive; their unfinished native queues are paused.

The plugin includes 26 lifecycle image assets. Native actions delegate rendering/tracking/unsubscribe handling to FunnelKit Send Email. Cart rows/restore links and coupon values are populated at execution. Email content is Raw HTML, not drag-and-drop builder data. Update lifecycle email variants through reviewed source + converter + versioned package; changing the ordinary editor's default preview field alone does not update the coordinated variant set.

Compatibility remains deliberately gated to FunnelKit Automations 3.8.5.2 + Pro 3.8.5; PHP 8.1+ / ZipArchive. Tested on local WordPress 7.1.2 and WooCommerce 11.1.2. License checks/vendor files were not modified.

## Local event contract

Execution requires WordPress environment `local`, exact host `email-test-puratek.local`, an `@example.invalid` synthetic account and `_pfi_test_fixture = '1'`.

Consent meta `_pfi_marketing_consent` must be an array with `granted => true`, a positive nonfuture Unix `at`, and `source => 'local-account-opt-in'`. `_pfi_country` must be `US`. Native unsubscribes/contact suppression and `_pfi_suppressed` / `_pfi_sunset` are respected.

For reviewed local integration code, call:

```php
do_action('pfi_lifecycle_event', $test_user_id, $event, $payload);
```

This is an in-process PHP hook, not a public webhook or unauthenticated endpoint.

- `account`: account opt-in enrollment; WordPress user_register is connected automatically when consent is already present.
- `paid`: `order_id`; requires an actual paid WooCommerce order belonging to the account. WooCommerce payment_complete is connected automatically. Repeat and eligible Win-back runs are prepared alongside Post-purchase.
- `cart`: stable `cart_id`, numeric `cart_total`, integer `cart_quantity`, verified local `restore_url`, and `items` arrays (`name`, `size`, `quantity`, `price`). Optional `coupon_code` for C2A. Repeated cart events update the live local cart state, while original run timestamps remain fixed.
- `cart_empty`, `cart_recovered`: exit the applicable cart sequence.
- `dispatch`, `delivery`: `order_id` plus verified Unix `at`; must refer to a paid order for that account and cannot predate payment or lie in the future.
- `engagement`: records a verified meaningful engagement timestamp; production must decide which evidence qualifies. Do not equate a tracking pixel with reliable engagement.
- `support_issue`, `refund`, `dispute`, `winback_approved`: payload `value => true/false`.
- `broadcast`: unique `campaign_id`, `approved => true`, one `option` (`b1`/`b2`/`b3`). B3 additionally requires `promotion_approved => true` and a real matching `coupon_code`.

Local context is in `_pfi_lifecycle_state`; run identities, status and per-step delivery state are in `_pfi_lifecycle_runs`. An uncertain transport result remains reserved and blocks other coordinated sends until manually reviewed. It is never automatically resent.

C2A requires a WooCommerce coupon: 10% percentage, individual use, one total use, one use per user, recipient email restriction, exact expiry at abandonment +7d, and `_pfi_first_order_only = '1'`. The local coupon validity hook blocks returning accounts from redeeming marked first-order coupons. B3 requires a matching 10% percentage offer with future expiry, individual use and per-user limit one. Coupon creation is not triggered merely by importing a package.

## Evidence and limits

22 native WordPress/FunnelKit integration scenarios passed, including all seven flows, every main template variant, category transfer, cart re-entry, real local order exit, unsubscribe/consent changes, support hold/resume, coupon expiry and failed transport. The run captured 37 transport attempts (one intentionally failed); SMTP was never invoked. All seven package drafts and seven execution fixtures were read back as inactive afterward.

56 deterministic policy checks passed, covering every flow's consent/country/source/suppression guard, exact timing boundaries, priority prerequisites and broadcast spacing. Seven lifecycle package validation checks, 14 existing ZIP/parser checks and 13 importer integration checks passed. Browser upload -> mapping -> select all -> import correctly returned the existing seven IDs as unchanged. Mobile cart preview showed no horizontal overflow and all images loaded.

Reports: `all-flows-test-results.json`, `lifecycle-policy-test-results.json`, `lifecycle-package-test-results.json`, `plugin-package-test-results.json`, `plugin-integration-test-results.json`, `all-flows-upload-result.json`. Screenshots: `all-flows-tests.png`, `all-flows-rules.png`. Captured HTML: `all-flows-captured/`.

Virtual local time was used to test long delays through the native controller; this was not a 90-day real-time cron soak test. FunnelKit's existing constant-redefinition warning during many registrations in one request is counted in the test report; vendor code is unchanged. Gmail/Outlook delivery/rendering is not certified by a browser preview.

## Still required before production

Production execution is hard-locked. Cart abandonment and real restore links, dispatch/delivery, engagement, support/dispute and editorial audiences currently enter through explicit local adapter events. They are NOT automatically wired to live providers. Live consent/country fields, guest-contact identity, verified business address, privacy/preferences endpoints, production assets/coupons, final copy approvals, provider mappings and staging mailbox/cron validation remain required.

The shared cap and sunset cover these seven coordinated flows only. Unrelated FunnelKit broadcasts, existing automations or other email plugins do not report to this ledger; they must be integrated or excluded before claiming a site-wide cap. Importing never activates flows. No live-site change was made.
