## Version 1.9.0: local native builder prototype

Read the repository's `docs/21-WordPress-Automation-Handoff.md` for current
packaging, validation and limitations. The native mode-5 builder prototype
preserves source rendered HTML and creates inactive local drafts. Licensed
editor Edit/Save, design fidelity, dynamic context mapping and a multi-variant
editor UI are not yet verified or complete. Do not deploy this as a live
automation system. The sections below are historical implementation notes.

## Version 1.4: local OTP consent and unsubscribe suppression

Read LOCAL-1.4-HANDOFF.md first. Adds optional consent/country fields, a source-matched two-phase OTP evidence bridge for preapproved synthetic local accounts, and sticky local external-subscription suppression. This is not live Omnisend synchronization. Production stays locked.

## Version 1.3: source mapping and local test protection

Read LOCAL-1.3-HANDOFF.md first. Adds exact synthetic-recipient authorization, paid/refund readback, guarded native recipient/transport checks and Tools -> Puratek Integration Readiness with a no-send policy rehearsal. Production remains locked. The seven-flow content package is unchanged at 1.0.0.

## Version 1.2: seven primary local flows

This release adds all seven lifecycle flows, 21 template variants, shared priority/frequency coordination and Tools -> Puratek Flow Rules. Read ALL-FLOWS-HANDOFF.md for current scope, installation, event contracts, tests and production limitations. The Welcome-only notes below describe the earlier profile and are superseded by that handoff for the new lifecycle-local-v1 package. Production remains locked.

# Puratek Flow Importer 1.4.0

Reusable WordPress importer for **versioned, prepared FunnelKit packages**. This first adapter imports verified linear email/delay workflows as inactive review drafts. It does not convert arbitrary design/source ZIPs into approved campaigns.

## Local usage

1. Install `puratek-flow-importer-1.4.0.zip` on the local WordPress site through Plugins, then activate it.
2. Open **Tools → Puratek Flow Importer**.
3. Upload a compatible automation package ZIP. Upload only previews; it creates no automations.
4. Supply mappings JSON, click **Refresh preview**, and inspect the flow list and step details.
5. Select the flows, acknowledge review-only status, and click **Import selected as inactive**.
6. Inspect the resulting inactive workflows in FunnelKit. This plugin never activates them.

For the included demo package, use:

```json
{"store_url":"http://email-test-puratek.local"}
```

Select only `email-demo` to demonstrate selective import. `unselected-demo` should stay unimported. The demo uses an abandoned-cart trigger, three sequential wait steps and three clearly marked demo emails. **It is not Puratek's production cart campaign.** Do not activate it.

## Package format

ZIP either the following files directly or one containing folder:

```
puratek-package.json
workflows/cart.json
emails/c1.html
```

Example manifest:

```json
{
  "schema": 1,
  "package_id": "puratek-campaigns",
  "flows": [
    {
      "id": "cart",
      "version": "1.0.0",
      "file": "workflows/cart.json",
      "emails": [
        {
          "step_id": 2,
          "html": "emails/c1.html",
          "subject": "Your cart is saved"
        }
      ]
    }
  ]
}
```

`cart.json` must be a genuine native FunnelKit v2 export array containing exactly one automation. `step_id` is the original database step ID in that export, not a visual node ID or email position. The importer verifies that bindings target email actions and lets FunnelKit remap step IDs. Optional `emails` bindings replace only those actions with Raw HTML mode. Without bindings, existing native editor data and HTML are preserved.

Use stable lowercase IDs and versions containing letters, numbers, dot, underscore or hyphen (maximum 80 characters). A package accepts up to 24 flow records, each with up to 100 native steps. This is a package capacity, not a claim that the client's 24 designs have been implemented.

Use `[[PFI:store_url]]`, for example, inside email HTML for site-specific values. Mapping values are HTML-escaped text/URLs or native merge tags, not arbitrary markup. `%%UPPERCASE_SOURCE_TOKENS%%` must be converted before import. Native `{{...}}` FunnelKit tags are preserved, **not validated against every installed integration**. Check them on staging. Images must already have correct URLs; this release does not upload assets or test remote URLs.

The source `puratek-main.zip` contains React/TypeScript design and flow specifications. It is not a native import package and is also above the current 64 MB ZIP limit. Build its approved email HTML locally, prepare verified native workflow exports, then assemble smaller packages using the schema above. Never execute code from an uploaded source ZIP inside WordPress.

## Versions, conflicts and rollback

- Same package ID + flow ID + version + identical mapped payload: skip; reuse the tracked automation ID.
- Same identity/version with changed payload or mappings: reject. Increase that flow's version.
- New version: create a separate inactive draft; preserve the previous version and unselected flows.
- A tracked predecessor that is active, missing or manually changed blocks import for that flow. Review it manually; this release does not reconcile edits or replace active workflows.
- Rollback removes only the selected, unchanged inactive draft with no active/completed/trail contact records. It preserves the earlier versions. This is rollback of an import, not rollback of emails already sent.
- Snapshot and source payload remain in the revision registry. **Download backup** produces JSON for inspection/manual recovery. There is no snapshot-restore upload UI; re-uploading the original compatible package can recreate the rolled-back revision as a new inactive draft with new IDs.
- Reimporting a rolled-back revision retains its latest import snapshot. Export earlier snapshots separately if an audit requires every incarnation's database IDs.
- Plugin deactivation/deletion deliberately does not delete the registry or imported automations. Keep full database backups for disaster recovery.

## Compatibility and boundaries

Requires PHP 8.1+, ZipArchive, WordPress 6.5+, active FunnelKit Automations **3.8.5.2**, and active Automations Pro **3.8.5**. Tested locally on WordPress 7.1.2 with WooCommerce 11.1.2. The exact FunnelKit versions are intentionally gated until another adapter is tested. WooCommerce must be active for WooCommerce events; a workflow's event must be registered.

Supported graph nodes: start/end, wait, wp_sendemail, and the guarded pfi_welcome_email action for the welcome-local-v1 profile. Branching, conditions as graph nodes, goals/benchmarks, jumps and other actions are rejected. The importer preserves native event metadata; it does not invent consent rules, stop-on-purchase behavior, absolute schedules, cross-flow frequency caps, priority routing, coupons, order/product mappings or segmentation.

Version 1.1 adds the reviewed R8 Welcome W1/W2/W3 package and a local-only native event/action extension with consent, US country, purchase/unsubscribe exits, absolute 0/48/96-hour timing, deduplication and a Welcome marketing ledger. Execution requires synthetic marked accounts on email-test-puratek.local. Other campaigns, site-wide cross-flow ledger integration, live field mappings and staging delivery validation remain outstanding. Raw HTML bindings do not become drag-and-drop layouts. Existing native builder data can be preserved, but editing may remain license-gated. No license check is modified.

## Operational behavior

Administrator capability and WordPress nonces protect actions. Per-user preview expires after 30 minutes, and a preview token prevents importing a replaced preview from another tab. ZIP entries are read without filesystem extraction. Unsafe paths, symlinks, duplicate paths, malformed JSON, unsupported graphs and unresolved package tokens are rejected.

Limits: ZIP 64 MB, expanded archive 128 MB, individual entry 8 MB, 1500 entries, parsed preview 8 MB, bound email HTML 102 KB, registry 32 MB. The server's upload/PHP memory limits still apply. Split large template libraries into smaller prepared packages.

Imports use FunnelKit's native importer with status 2 (inactive), followed by status/step-count/content/ID-remapping readback. An InnoDB transaction covers selected imports and registry updates. A database advisory lock serializes this plugin's import/rollback requests. Failed imports roll back database rows; WordPress/FunnelKit hooks run normally and arbitrary third-party hook side effects outside the database cannot be undone. Test the target plugin stack on staging. Avoid simultaneously editing the same draft while importing or rolling back.

The reusable plugin is not a global email firewall. The separate local-only MU guard on `email-test-puratek.local` blocks WordPress mail and outbound WordPress HTTP during testing. No real customer data, credentials or third-party plugin binaries are bundled here.

## Verified tests

14 package/parser checks and 13 local WordPress integration checks passed. Coverage includes malicious paths, missing/unsafe mappings, invalid graphs, selection, duplicate retries, version conflicts, manual edits, rollback, reimport after rollback, authorization, concurrent importer locking, injected native failures and batch rollback without orphan automation/step/meta rows. The admin upload → mapping preview → selected import → retry path was also tested through Chrome. 18 additional Welcome execution/policy checks passed using native FunnelKit processing, a virtual local clock and pre-SMTP capture. Both the package automation and execution fixture were verified inactive afterward. Real mailbox delivery, other client campaigns and licensed visual editing were not tested.

## Welcome source converter (1.1)

Use the separate puratek-welcome-converter.zip against the reviewed puratek-main.zip R8 source. It renders three approved emails and creates the compatible welcome-local-v1 package; it does not translate arbitrary React projects or all flow families. Upload puratek-welcome-local-1.0.0.zip in Tools and use welcome-local-mappings.json to preview/import. The plugin includes this revision's eight image assets. HTML emails remain Raw HTML, not drag-and-drop layouts.

The Welcome extension is deliberately local-only until production consent/country mappings, business address, endpoints, coupon eligibility and cross-flow policies are verified. Other senders do not yet update its frequency ledger. The included mappings contain test placeholders. See WELCOME-HANDOFF.md for full test boundaries and production prerequisites.
