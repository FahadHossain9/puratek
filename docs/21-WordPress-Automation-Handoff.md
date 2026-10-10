# WordPress automation plugin handoff

The repository now includes Puratek Flow Importer 1.9.0, its seven-flow local
builder package, and portable validation tests. The existing image/design
application remains in `puratek-email/`.

## Contents

- `wordpress/puratek-flow-importer/`: installable WordPress plugin source and its own artwork.
- `automation-packages/puratek-local/`: manifest and seven native FunnelKit workflow exports, with 21 template variants.
- `tests/flow-importer.php`: package, builder document and execution variant validation.
- `scripts/package-wordpress.ps1`: build the two ZIP files without committing generated archives.

No WordPress database, wp-config, credentials, license keys, contact history,
or downloaded live-site reference export is included.

## Build and install locally

Requires PHP 8.2 with DOM and ZipArchive. The adapter currently requires
FunnelKit Automations Free 3.8.5.2 and Pro 3.8.5.

```powershell
php tests/flow-importer.php
powershell -ExecutionPolicy Bypass -File scripts/package-wordpress.ps1
```

1. Upload `releases/puratek-flow-importer-1.9.0-LOCAL-PROTOTYPE.zip` through WordPress Plugins and activate it.
2. On `email-test-puratek.local`, open Tools → Puratek Flow Importer.
3. Upload `releases/puratek-builder-LOCAL-ONLY.zip` and validate the preview.
4. Select the flows and acknowledge review import, then import them as inactive drafts.
5. Inspect them in FunnelKit Automations. Repeating the same package import preserves unchanged drafts without duplication.

The conversion button is an alternative for this existing local site only:
it requires all seven original imported lifecycle flow revisions. Use the
ZIP route on a fresh installation.

## Verified scope

All 20 plugin PHP files passed syntax validation. Seven workflows and 21
variants passed offline validation. Original rendered HTML is preserved,
block attributes decode as JSON, unsafe active HTML is rejected, and the
visible editor field overrides only its matching execution variant.
Native import and step readback succeeded on the named local site; all
seven drafts remained inactive. Reimport returned unchanged IDs.

HTML bindings now update the runtime variant as well as the editor field.
An optional binding `editor: "builder"` converts the bound HTML into mode 5.
For multi-variant steps, specify the binding `variant` explicitly.

## Outstanding work before a production release

This is a local prototype, not a deployable live automation release.

- The local license prevents native builder Edit/Save and send validation. Vendor license checks are unchanged.
- Native text, image, cart-items, cart-link and coupon blocks are generated, but their editor and saved output require licensed verification.
- Nested and multiple-column source layouts currently become individual block rows. Preserved rendered HTML does not guarantee design fidelity after builder save.
- Native dynamic widgets need lifecycle recipient/cart/coupon context mapping and rendering tests after the editor regenerates HTML.
- Multi-variant steps initially expose their first variant; the variant-switching editor UI is unfinished.
- Production consent, cart synchronization, dispatch and delivery integrations remain unresolved.
- The `lifecycle-local-v1` profile is intentionally rejected outside the named local site. Do not import this package on production or remove guards to make it run.

Test a production adapter on licensed staging before deploying it. Pushing
this code to GitHub does not install the plugin or modify the live store.
