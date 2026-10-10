# Puratek importer 1.3.0 — local integration preparation

Date: 2026-09-29. Live settings/plugins/automations/orders were not changed. Source ZIPs were read locally; provider plugins were not activated locally and no live credentials were copied into the importer.

## Deliverables

- `puratek-flow-importer-1.3.0.zip`: updated reusable WordPress importer.
- `puratek-all-flows-local-1.0.0.zip`: existing, unchanged package containing seven primary flows, 21 template variants and 18 action steps. Re-import skips its tracked unchanged revisions; a new runtime version does not require a duplicate content revision.
- `all-flows-local-mappings.json`: LOCAL URLs and mappings only.
- `integration-map-test-results.json`: 54 pure mapping/recipient/no-send checks.
- `all-flows-1.3-test-results.json`: 27 native WordPress/FunnelKit scenarios.

Use these on the local site only. This release does NOT enable production execution or supply a production automation ZIP. Do not remove the local restriction or substitute live URLs to bypass it.

## Changes in this release

1. Exact test-recipient authorization: local environment + exact local host + valid `@example.invalid` account + `_pfi_test_fixture = '1'` + `_pfi_test_authorized_email` matching its current email. Removing/changing authorization also blocks already queued work. Earlier fixtures must be explicitly authorized again; inactive drafts are unaffected.
2. Paid normalization requires a matching registered customer, real created/paid timestamps, a paid order status, and no refund. Missing payment time is no longer replaced with the current time. It uses WooCommerce accessors, not assumed postmeta storage, so it does not bypass HPOS.
3. Refund amounts/status are read again before each lifecycle decision. Even a partial refund without a lifecycle callback puts purchaser marketing on hold. This is deliberately conservative across the account's order history; resolution/scoping of old refunds needs a production policy before launch.
4. Native execution rejects recipient overrides, a resolved email that differs from the authorized account, non-WordPress email connectors, and a missing local blocked-mail transport. Native testing still requires its local MU mail/HTTP guard and capture harness. This is not a production SMTP firewall.
5. **Tools → Puratek Integration Readiness** lists the verified source contracts and unresolved dependencies. On the local site it includes a **No-send rehearsal** form: select flow, matching step, elapsed hours and synthetic consent. `would_send` is a simulated decision, never an actual email. The evaluator creates no contacts, orders, queues or native actions; results are rendered for that request only. It does not test provider callbacks, cron delivery, HTML rendering or site-wide frequency coordination.

The existing ZIP importer still previews before import, imports selected workflows inactive, skips identical revisions, rejects changed content under the same version, protects manually edited/active predecessors, and supports rollback of untouched inactive imported revisions. Version updates create review drafts rather than silently replacing running flows.

## Source mapping evidence

Examined copies in `work/live-source/`:

- **Account Gate 1.0.0-rc7:** `includes/class-puratek-account-gate.php:151` renders username/email/password and `woocommerce_register_form` hooks. It does not itself collect marketing consent or country. Other site snippets/hook contributors are not proven absent. Existing acceptance of research terms is not marketing consent.
- **Omnisend 1.22.8:** `omnisend-woocommerce-hooks.php:221–239` reads `omnisend_newsletter_checkbox` and writes order meta `marketing_opt_in_consent = checkout`; the handler attaches to `woocommerce_checkout_update_order_meta` at line 256. The block-checkout integration also writes this meta. Historical checkout evidence is NOT a verified current subscription status, signup permission or revocation sync. The mapper reports evidence without granting permission.
- **PRISM 1.1.2.0:** `includes/class-completion.php:73` calls `WC_Order::payment_complete()` after verification; `_psc_payment_completed` is saved afterward. Therefore do not require that later marker inside the payment-complete callback. The local lifecycle adapter listens to the native WooCommerce hook, but a real gateway transaction was not performed.
- **AST Pro 4.8.2:** `includes/class-ast-pro-actions.php:1838–1843` saves `_wc_shipment_tracking_items`. Tracking label creation and `date_shipped` do not establish verified dispatch/delivery. The mapper leaves both timestamps null. Missing fulfillment events continue to hold the dependent steps.

ZIP SHA256 provenance:

```text
puratek-account-gate.zip  11D2AEA040F87745D827C31798410441B8642106A5C585AAC6F4488A779735FD
prism-simple-checkout.zip 29E69ED815DFE91CB031D0600B958EDDD6C7DE49914FC9AF9E162C6C5C43880C
omnisend-connect.zip      80BA6D7BA03A2B849CCFBC92C5D1DD4EFE49B438400A8021F692E137EB1229E4
ast-pro.zip               134F96ECB5DE7EE6284BD936E5F285635C3B8B6B04178BBEBD5648F0347F8F12
```

## Verification

- 54 mapping/no-send tests PASS.
- 56 existing lifecycle policy checks PASS.
- 7 lifecycle package checks and 14 ZIP/parser checks PASS.
- 27 native scenarios PASS, including all seven flows, variants/timing, purchase exits, consent revocation, native unsubscribe, duplicate-event handling, cross-flow priority/cap, uncertain transport, exact allowlist removal, missing paid timestamp and partial-refund readback.
- Native harness captured 40 mail attempts (including synthetic WooCommerce notifications; one intentional lifecycle failure). SMTP was not invoked. This is not an inbox-delivery test.
- Seven content drafts (IDs 28–34) and new execution fixtures (55–61) were read back inactive; fixture queues were paused. Previously completed fixtures remain inactive.
- Native long intervals use virtual local time, not a real 90-day cron soak.
- Existing vendor constant-redefinition warnings were recorded (23 in the native run); vendor code and license checks were not modified. The local admin still shows its invalid-license/update notice.
- Browser rehearsal verified eligible → `would_send` and revoked consent → `stop`, both with `transport_invoked: false`.

## Remaining launch blockers

Production sending and a real-mail live allowlist test are not implemented/approved in this release. The following need genuine provider evidence or a business decision, not guessed fields:

- Signup consent/country capture, consent timing after registration and external unsubscribe/revocation propagation.
- Real FunnelKit cart event payload/recovery URL and guest identity; the initial 15-minute wait and 15-day post-order cool-off must be reconciled with the new flow rules.
- Verified carrier dispatch/full-shipment/delivery events; engagement, support/dispute and editorial audience sources.
- Ownership/migration of the existing active FunnelKit cart flow and the separate enabled one-hour cart email; Omnisend external campaign audit.
- Complete sender address, live links/assets/coupons, final copy and mailbox/cron verification.

The importer must remain inactive on live until these are resolved and a concrete, scoped installation/test is approved. Do not enable live global SMTP simulation: it can suppress existing transactional mail.
