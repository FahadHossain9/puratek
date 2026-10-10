# Puratek 1.4.0 — signup consent and suppression bridge

2026-09-29. Local implementation only. Live snippets were opened/read, never edited, toggled or saved. No live forms, OTP requests, customer records or email tests were submitted.

## New evidence

The active live WPCode snippet **46045, Custom Registration Fields — Display** collects first/last name, research field, institution and age/research acknowledgement. Its save callback writes `age_research_confirmed`, not marketing consent; it contains no signup country. The **46047** JavaScript snippet only reorders registration fields.

Active snippet **46048, WooCommerce Email Verification on Registration** has a two-phase OTP flow. It stores a whitelisted field set in `woo_otp_pending_<token>`, restores those fields during verification, calls `wc_create_new_customer()`, then writes `_woo_email_verified = '1'`. Marketing consent and country are absent from that whitelist. These inspected sources establish the relevant integration contract, not proof that no other site code ever contributes a field.

The Omnisend 1.22.8 source inspected in `work/live-source` stores contact IDs in its contact cache, not a current subscription-status ledger. Its listed REST routes cover connection/settings/status, not a demonstrated unsubscribe-to-FunnelKit synchronization. No verified live external unsubscribe bridge was found. A historical checkout opt-in cannot substitute for current subscription evidence.

## Implemented locally

`includes/consent-bridge.php` renders an unchecked, optional marketing checkbox and a country selector with no default through `woocommerce_register_form`. It does not alter the vendor or live OTP snippet.

The local bridge observes WordPress's `set_transient` hook for the verified source's pending OTP record. With both the OTP nonce and a separate consent nonce valid, it stores a minimal companion snapshot: exact email, explicit consent, validated country code, capture time, expiry and wording revision. It stores no password or OTP. Snapshot expiry is at most 15 minutes. Failed attempts and resend requests cannot overwrite or extend that original evidence. An expired evidence record requires restarting registration rather than guessing consent.

The bridge observes `woocommerce_created_customer`, waits for `_woo_email_verified = '1'` in the same request, and cross-checks pending identity/expiry before saving consent and evaluating Welcome. `user_register` alone is too early. Later verification POST fields cannot upgrade the original unchecked consent or change the original country.

Execution requires the exact local environment/host and an email preapproved by the local administrator in option `pfi_signup_test_emails` (an array of lowercase `@example.invalid` addresses). The test harness temporarily supplies/restores that option. The bridge never allows an arbitrary visitor to authorize their own test account. At completion it writes the existing fixture authorization and consent metadata; production remains disabled regardless of this option.

Local external-status contract:

```php
do_action('pfi_test_subscription_status', $synthetic_user_id, 'omnisend', 'unsubscribed', time());
```

Only authorized local fixture accounts and providers `omnisend`/`funnelkit` are accepted. `unsubscribed` and `unknown` set a sticky `_pfi_external_suppressed` hold, rechecked before lifecycle sends and legacy Welcome sends. Stale observations are ignored; unsubscribe wins equal timestamps. A later subscribe does not clear the hold or grant consent. There is no automatic reactivation UI. This PHP hook is a local test contract, **not** a live webhook or bidirectional provider sync.

## Verification and scope

See `all-flows-1.4-test-results.json` for the final native run. The harness exercises real WordPress transient/user-meta hooks and real FunnelKit queue processing with source-shaped OTP fixtures. It tests nonce failure, original checkbox/country preservation, identity mismatch, missing verification, expired pending data, absent administrator approval, duplicate verification, queued suppression and all earlier lifecycle scenarios.

The harness does **not** call the live OTP endpoint, send/verify a real OTP email, use Omnisend credentials, or certify the complete browser registration flow. No live customer data was imported. Long schedules use virtual local time. SMTP/WordPress external HTTP remain blocked in the local lab. Native test fixtures are returned inactive and their pending queues paused.

Pure mapping/no-send (54), lifecycle policy (56) and lifecycle package (7) checks also pass. Production license handling/vendor code is unchanged.

## Files and next dependency

- `puratek-flow-importer-1.4.0.zip`: new importer/runtime/bridge.
- `puratek-all-flows-local-1.0.0.zip`: unchanged seven-flow content package; no duplicate content version is needed.
- `all-flows-local-mappings.json`: local URLs only.

Before production, the new signup fields/wording and their actual OTP browser behavior must be reviewed. External unsubscribe needs an authenticated, provider-supported inbound observation mechanism, reconciliation/freshness rules and an explicit re-consent policy. Real cart recovery, carrier dispatch/delivery, engagement/support events and duplicate-email migration remain blocked as documented in the 1.3 handoff. No production ZIP or live sending mode is delivered here.
