# Puratek — automation, imagery and HTML-to-Figma implementation plan

Prepared 28 September 2026. Status: implementation plan; no new flows activated or emails sent.

## 1. Outcome and scope

Extend the existing project into a complete, consistent lifecycle-email library with original imagery, reusable HTML components, reproducible exports and documented automation logic. Preserve Puratek's existing logo, navy/orange design, rounded cards, pill buttons, typography hierarchy and section structure.

The first delivery is this plan, as requested. The implementation will produce the designs and local automation specifications first. Installing flows in FunnelKit, publishing assets and sending campaigns are separate deployment steps requiring the actual store configuration and the existing program's launch checks.

“All possible automation” means a complete inventory of useful lifecycle and operational opportunities, with implemented templates, draft specifications and unsupported dependencies clearly distinguished. It does not mean enabling every flow for every contact.

## 2. What I reviewed

- `START-HERE.md`, `LINKS.md`, both source documents and the local Puratek compliance skill.
- The GreenLabs lifecycle framework, marketer chat and Team Puratek screenshot.
- `src/content.ts`, `src/Email.tsx`, `src/build.tsx`, `src/index.template.html`, package scripts and generated output structure.
- Both previous audit reports, their extraction approach and representative visual evidence.
- Saved website HTML for brand comparison. These are snapshots, not confirmation of today's live site.
- Current official FunnelKit documentation for automation conditions, merge tags and cart coupon application; links are at the end.

The bundle has **12 email definitions**: 4 welcome, 5 cart variants across 4 steps, and 3 broadcast concepts. It contains 12 files in each of `send`, `preview`, `preview-dark` and `boards`, plus the review hub and manifest. Only two logo assets are present; there is no original supporting image library. Current send HTML is approximately 22–42 KB before platform processing.

The GreenLabs framework's 18 touchpoints are a reference, not 18 Puratek automations already installed. The chat requests a client-ready HTML/Figma handoff and initial setup around October 5; neither deadline nor historical status establishes deployment readiness.

### Existing work to retain

- Shared React Email renderer, typed content blocks and separation of preview/send output.
- A–I structure: preheader → brand header/headline → body → optional proof/cart/offer → primary CTA → support → locked footer.
- Existing logo files; do not redraw, recolor or generate a replacement logo.
- Cart thumbnails disabled, restrained product handling and live-text offers.
- Source-level fixes already present: nonshrinking preview iframe, independent dark/mobile controls, height messaging, hash updates, safer proof accent tables and nonbreaking support hours.

### Current findings and planned fixes

| Finding | Evidence / implication | Implementation |
| --- | --- | --- |
| Review hub lacks a complete HTML document | Source and built hub omit doctype, charset and viewport metadata | Add explicit document structure, UTF-8 and viewport; test via HTTP |
| Desktop preview remains misleading | Iframe is 600px wide while responsive CSS activates at `max-width:620px` | Use a 680px desktop viewport containing the unchanged 600px email; label viewport and email width separately |
| Boards discard email head styles | Board builder extracts only body markup | Carry required email CSS into each export; scope board chrome; generate explicit mobile/light/dark variants |
| Brand references disagree | Deck tokens are marked placeholders; renderer uses a more developed palette | Record the renderer as the working baseline; document differences instead of silently reverting |
| Broadcast schedule conflicts | Deck has Tue/Thu/Sat sends; program starts at one broadcast/week | Build all three options but propose one initial send, not all three |
| Audience rules disagree | B1 retains a 180-day tier; program specifies engaged-90 for promotions | Default marketing broadcasts to consented engaged-90 contacts; do not infer a service-message exemption |
| Coupon behavior is unproven | W4 still links to `/shop/`; C2a has an unresolved recovery link | Test actual coupon application; until verified use a truthful generic CTA plus code instructions |
| W3 phone is plain text | Dispatch proof strip supplies the number as a string | Add a typed telephone link without changing appearance |
| Mobile proof inset differs | `.pxs` becomes 16px, body inset is 24px | Align intended content edges and verify screenshots |
| Preferences and browser links lack confirmed destinations | Removed from template; some documentation still says they exist | Reconcile docs and manifest; add only verified functions, never a dummy preferences link |
| Fixed sample dates and claims can mislead | Preview coupons are hard-coded; audit scores are historic | Label all sample data and dated audit scores; make preview clock configurable |
| Audit scripts are not portable | Extraction script uses `/home/claude/...` | Resolve paths relative to project root and produce machine-readable results |

Source inspection and existing screenshots support this plan. I have not rebuilt the project, rerun a browser matrix, tested a real mailbox, imported into your connector or inspected the live store in this planning pass. Older 404 and site-security findings remain historical dependencies, not newly verified incidents.

## 3. Design system to preserve

Extract the following into `src/design-tokens.ts`, consumed by emails, boards, hub and a generated brand-guideline page. Preserve values first; make any necessary accessibility change explicit.

| Token | Existing baseline |
| --- | --- |
| Navy / headings | `#0F1523` |
| Decorative brand orange | `#E06D01` |
| CTA background | `#B85900`, white label |
| Links on white | `#B35400` |
| Accent on navy | `#F59120` |
| Body / muted text | `#404657` / `#5B6170` |
| White / proof surface | `#FFFFFF` / `#F6F6F6` |
| Page / offer / divider | `#EEF0F3` / `#FFF4EA` / `#E3E6EB` |
| Dark page / card / proof / offer | `#0B0F19` / `#151B2B` / `#1C2335` / `#2A2016` |
| Dark heading / body / muted | `#F2F3F5` / `#C9CDD6` / `#A8ADB8` |
| Email / offer radius | 12px |
| Strategy board card / hub card | 16px / 14px |
| CTA radius | 100px |
| Offer border | 2px dashed decorative orange |
| Email width / inner padding | 600px / 40px desktop, 24px mobile |
| Headings | Anek Telugu, Arial/Helvetica fallback; 32/38px desktop, 26/32px mobile |
| Body | Cabin, Arial/Helvetica fallback; 16/26px |
| Supporting copy | 15px; footer and terms 14px; labels 13px |
| Logo display | 180 × 39px using existing asset |

The orange variants serve different contrast roles; they are not permission to introduce unrelated accents. Keep exact colors, radius and spacing in HTML/CSS. Generated pixels cannot guarantee exact token values. Validate actual foreground/background contrast rather than trusting comments in the source.

Font loading is an explicit QA step. Supply a documented system-font fallback for email clients, and ensure fonts are available before Figma import. Never turn whole emails into images to preserve typography.

## 4. Original image production

Use the built-in image generator for new raster artwork. Keep the logo, all headings, offers, coupon codes, statistics, proof statements and CTA labels as real HTML. The imagegen workflow will save selected assets into this project with versioned filenames and provenance.

### Initial asset family

| Asset | Concept | Intended usage | Target export |
| --- | --- | --- | --- |
| `documentation-still-life-v1` | Abstract paper/document forms, subtle glass and navy/orange studio lighting; no readable report or seal | W2, B1, post-purchase documentation | 1040 × 480px, displayed at up to 520 × 240px |
| `dispatch-still-life-v1` | Unbranded closed shipping carton and restrained packing materials on a clean studio surface | W3, B2, delivery/support education | Same 2× format |
| `brand-material-study-v1` | Minimal abstract precision forms with warm orange accent and ample negative space | Welcome or win-back body insert where useful | Same 2× format |

Generate a coordinated family, not a different visual style for every email. Use a restrained editorial still-life direction that fits the established design. Insert artwork below the header in relevant body sections; preserve the logo/headline header and keep short cart/final-note emails mostly text. A new image slot is a documented optional extension, not a restructuring of every template.

Prompt constraints for each asset: navy `#0F1523` and orange `#E06D01` as color references; neutral cream/white surfaces; realistic materials; deliberate empty space; no text, logo, fabricated COA, signature, certification badge, lab result, labeled vial, person, body, syringe, pen, medical-use scene or performance claim. Synthetic scenes must not be described as Puratek's actual facility, packaging or laboratory.

A real COA illustration requires a supplied approved document with appropriate redaction. Until available, use an explicitly illustrative document motif with live explanatory copy; never manufacture batch data or a certificate.

Production steps:

1. Generate and inspect the three master concepts against the existing visual baseline.
2. Reject inaccurate objects, unwanted text, visual claims and off-brand composition; regenerate targeted variants where needed.
3. Save masters, selected email-safe JPEG/PNG exports, dimensions, crop instructions, prompt, generation date, alt text and usage mappings in `assets/manifest.json`.
4. Aim for roughly 80–180 KB per supporting image, while checking quality; keep the layout comfortably below the program's 40% image-area guideline.
5. Apply 12px clipping and exact surrounding token colors in HTML; verify dark-mode edges and blocked-image behavior.
6. Copy assets into exported packages. Browser/Figma exports may use bundled or embedded assets according to connector support. Send HTML must use approved hosted image URLs.

Image generation is planned here, not claimed complete. No API key is needed for the available built-in image tool.

## 5. Complete lifecycle library

Target the following **23 core templates**: 19 standard lifecycle variants, 3 broadcast concepts and 1 high-value-cart support variant. These are templates within flows, not 23 independent automations. Existing IDs remain stable.

| Family / IDs | Count | Trigger and proposed timing | Content and exit logic |
| --- | --- | --- | --- |
| Welcome `w1–w4` | 4 existing | Consented account creation; immediately, day 2, day 4, day 7 | Welcome/code, documentation, dispatch/support, code reminder. Exit on purchase, unsubscribe or suppression. W1/W4 reuse one coupon and expiry |
| Cart `c1,c2a,c2b,c3,c4` | 5 existing | Captured eligible abandoned cart; 1h, 24h branch, 48h, 72h from abandonment | Returning customers bypass discount. Recheck order count before C2a; exit on purchase, empty/recovered cart, unsubscribe or suppression; seven-day re-entry limit |
| Post-purchase `p1–p4` | 4 new | Verified paid order, then dispatch/delivery signals where available | Proposed: support after payment, batch-document guidance after dispatch, service feedback 7 days after confirmed delivery, documentation/support follow-up 21 days after delivery. New refund/dispute/support issue pauses promotional steps |
| Repeat engagement `r1–r3` | 3 new | Consented customer outside recent active flows; proposed 30/60/90-day eligibility checkpoints | Adapt GreenLabs cross-sell to documentation updates, catalog navigation and optional customer-directed reorder. No complementary compounds, consumption schedule or automated product pairing. Send only while engaged and eligible; stop on purchase or suppression |
| Win-back `x1–x3` | 3 new | Separate client-approved inactive-customer segment with recorded consent; proposed 90 days since meaningful engagement, then +7/+14 days | Brand/documentation update, support invitation, final preference/continuation message. No unapproved discount. Exit on meaningful engagement/order; suppress after final nonresponse. Disabled pending separate audience approval |
| Broadcast `b1–b3` | 3 existing | Scheduled editorial releases | Documentation, dispatch, approved sale. Treat as a library of options; one broadcast/week initially; no default three-send first week |
| High-value cart `h1` | 1 new | Cart > $500 or quantity ≥10 | Support-led alternative to standard cart flow; omit discount; record support routing; stop on purchase/unsubscribe |

New timings are proposed defaults, not client-approved policy. Payment, dispatch and delivery must be real events; elapsed time is not proof of delivery. Do not duplicate WooCommerce receipts or shipping notifications. Marketing post-purchase steps require marketing eligibility; genuine order-service messages use a separate transactional stream.

Each new template gets subject A/B, preheader, headline, body, CTA, block rationale, audience, triggers, stop rules, dependencies and review status. Existing approved copy stays intact unless a named correction is documented in the copy deck and board. New copy is marked draft.

### Additional opportunities to specify, with dependencies

| Opportunity | What can be prepared | Activation dependency |
| --- | --- | --- |
| Consent capture / double opt-in | Confirmation states, template and event specification | Actual registration/checkout integration and consent storage |
| Preferences / subscription confirmation | Real preference choices and confirmation template | Verified preference endpoint; profile-edit page is not a substitute |
| Re-permission / sunset | Eligibility decision and suppression workflow | Recorded prior permission and provider/client approval; unknown-source contacts remain suppressed |
| Failed-payment / order-status help | Transactional message and state transition map | Gateway and WooCommerce statuses; no duplicate provider messages or automatic charge retries |
| Delivery exception / refund support | Support template and event mapping | Reliable fulfillment events and monitored support workflow |
| Review request / follow-up | Service-only request and suppression rules | Verified delivery, consent where required and approved review destination |
| Neutral product back-in-stock | Tier checks, opt-in specification and draft | Product-specific consent and verified stock events; prohibited products excluded |
| Browse abandonment | Technical specification only initially | Consent-aware identifiable browsing events; not confirmed by the supplied bundle |
| Holiday dispatch / support hours | Reusable schedule-based template | Approved dates, actual hours and dispatch rules |
| Subject A/B testing | Variant metadata and reporting plan | Sufficient audience, chosen metric and verified platform support |

These candidates receive their own specification and readiness status. Do not invent events or portray them as already supported. Product-use education, replenishment based on assumed consumption, prohibited-product promotions and uncontrolled cross-selling are excluded by the supplied Puratek rules.

## 6. Automation engine and operational safeguards

Create `src/flows.ts` as structured flow definitions, separate from email rendering. Include trigger, eligibility, delays, branch, exit conditions, re-entry window, priority, coupon policy, template IDs and dependencies. Generate a readable flow map and implementation checklist from those definitions.

- Check consent, US eligibility, suppression, order/cart state and frequency limits immediately before each marketing send, not only at entry.
- Use a proposed priority of cart → welcome → post-purchase marketing → repeat/win-back → broadcast. Order-service notifications stay separate.
- Enforce the supplied maximum of one marketing email/contact/day; define queued-message expiry and recheck eligibility when deferred. Keep timing and coupon truth consistent if a send moves.
- Prevent repeat enrollment and duplicate sends with stable event IDs and contact/flow/step keys. Handle duplicate or delayed webhooks, retries and unsubscribe races. Verify whether the installed platform supplies these guarantees before adding custom code.
- Create the welcome coupon once, retain its identity and expiry for W4, and keep cart coupon policy distinct. Verify single-use, first-order eligibility, nonstacking and expiry in the store.
- Confirm whether “PST” means fixed UTC−8 or Pacific local time. Store an explicit timezone and do not silently change approved copy or coupon deadlines.
- Record send attempt, platform acceptance, delivered, bounced, complained, clicked and ordered as distinct states. Acceptance is not delivery; open rates alone are not reliable engagement proof.
- Integrate bounce/complaint/unsubscribe suppression when the installed services are available. Preserve legacy suppression records and a rollback/export of configuration changes.
- Generate monitoring reports at the program's 2h/24h checkpoints. Treat historical thresholds in the source document as client policy pending current provider verification, not newly established legal requirements.

FunnelKit import formats and API coverage must be verified against the installed version/license. Produce implementation instructions and portable specifications first; do not call arbitrary JSON an importable FunnelKit flow.

## 7. Build and export automation

Proposed structure (new paths are planned, not existing):

```text
puratek-email/
  src/
    design-tokens.ts
    content.ts
    flows.ts
    Email.tsx
    build.tsx
    index.template.html
  assets/
    manifest.json
    generated/
  scripts/
    validate.ts
    render-qa.ts
    package-handoff.ts
  dist/
    send/                 # draft platform templates until deployment checks pass
    preview/              # sample light emails
    preview-dark/         # forced-dark samples
    boards/               # existing strategy + email format retained
    figma/                # email-only desktop/mobile light/dark documents
    brand/                # tokens, component sheet and image usage guide
    flows/                # diagrams and configuration specifications
    assets/
    manifest.json
    index.html
  qa/                     # machine-readable checks and screenshots
  handoff/                # versioned ZIP, inventory and import instructions
```

Planned commands: `npm run build`, `npm run validate`, `npm run qa:render`, `npm run package`, and a separate `npm run validate:release` that fails on unresolved deployment inputs. `dev` should rebuild on source changes; current `dev` builds once and serves.

Builds should be deterministic for a specified preview date, validate before replacing the previous successful output, and publish an artifact manifest containing template IDs, dimensions, token version, asset mappings and checksums. Keep a recoverable original bundle; edit source, not generated HTML.

### Figma connector contract

1. Retain each 1240px strategy board: 520px strategy card + 40px gap + 600px email, with 40px outer margins.
2. Add standalone email-only documents for clean import, with no iframe, scripts, dashboard controls or hover-dependent content.
3. Provide explicit desktop and 375px mobile light/dark variants. Materialize the correct responsive styles where the connector cannot evaluate media queries.
4. Use real text, image elements and simple containers; add stable block IDs and names such as `W2 / Proof / Item 01`. Whether these become Figma layer names depends on the connector.
5. Resolve fonts and images before capture; offer local bundled assets and an embedded-image export when supported. Do not rely on localhost being reachable from a remote importer.
6. Test W1 (offer), C2a (cart/offer), W2 (new artwork) and C4 (plain note) first. Compare imported widths, type, image crop, corner radius, spacing and CTA dimensions against browser renders, then export the full library.
7. Include a generated component/brand page for reusable Figma components. Do not promise automatic native components, Auto Layout or perfect editability unless the connector proves those capabilities.

Keep email-compatible table markup for sending. Figma-oriented wrappers can differ, but both outputs must consume the same content, tokens and image mappings. Never send exported Figma HTML back to customers.

## 8. Verification and acceptance

- Check all template IDs, output counts, required subjects/preheaders, footer text, alt text and asset references.
- Validate tokens by output type: samples fully resolved; draft send files allow only declared placeholders; release artifacts contain only verified platform tags and real required values.
- Check terms using context-aware rules. The verbatim disclaimer contains words that are otherwise restricted; URLs can legitimately include cart recovery terminology. Explicitly allow required text contexts without disabling checks elsewhere. Scan visible copy, subjects, preheaders, alt text and marketing URLs, not arbitrary HTML substrings.
- Validate product tiers, cart image suppression and truthful coupon labels. Reject fabricated certificates, unapproved testimonials and sample data leaking into send output.
- Render every template at desktop viewport 680px with a 600px container and at 375px mobile, light and dark. Add 320px stress checks for long coupons and cart rows.
- Check overflow, font loading, clipping, footer visibility, phone links, image-blocked rendering, long/empty fields and multiple cart rows. Compare tokens and measured radii, not only screenshot similarity.
- Measure final send HTML after platform substitutions/tracking against the program's clipping limit; embedded Figma assets are not allowed to inflate send HTML.
- Run meaningful flow scenarios: purchase before delayed send, unsubscribe while queued, repeated events, returning customer, high-value/quantity cart, expired coupon, coupon stacking, missing consent, non-US/unknown source and overlapping flows.
- Verify the connector import independently. Browser screenshots do not prove Figma fidelity.
- Real Gmail/Apple Mail/Outlook/Yahoo rendering, header authentication, provider delivery and actual coupon/cart recovery require the configured platform and seed accounts. Record these separately from local QA.

## 9. Delivery sequence

1. **Baseline and reconcile:** preserve original outputs; resolve document/source conflicts; extract tokens; capture reproducible baseline renders.
2. **Fix export foundations:** complete hub document, desktop viewport, board styles, sample labels and portable QA.
3. **Generate artwork:** produce and inspect the three coordinated assets, then integrate only where useful.
4. **Finish core library:** retain the 12 existing templates and add 11 new core templates with copy/flow specifications. Document dependency-gated opportunities separately.
5. **Generate handoff:** brand page, flow map, all HTML variants, image manifest, import instructions, QA report and versioned ZIP.
6. **Validate actual connector:** representative imports first, then full library; record any connector-specific limitations.
7. **Configure and launch separately:** map installed FunnelKit tags and events, host assets, resolve client inputs, obtain the existing program's copy/send sign-offs, run seed tests, then enable only approved flows.

For an accelerated October 5 target, prioritize welcome/cart plus one eligible broadcast and the full client-review handoff. Completing the broader library does not require simultaneously enabling every lifecycle flow. Dates remain targets until access and client dependencies are resolved.

## 10. What I need from you

**Nothing else is required to prepare this plan or start local design/build work.** The bundle supplies enough structure, copy and working brand assets.

**For final Figma verification:** the connector's name/version and whether it imports a live URL, uploaded HTML or pasted HTML. If it only works inside your account, a representative imported result or access to the connector is needed to verify the actual output.

**Helpful if available:** a more authoritative brand guide and original approved imagery. Otherwise use the existing renderer's documented design baseline. A real COA is needed only if you want a genuine annotated certificate visual.

**Before live deployment, from the store/client:** actual FunnelKit version/license and merge-tag picker; WordPress/Mailgun configuration access through an approved access method; verified postal address/privacy URL; consent and suppression records; confirmed offer/timezone rules; image-hosting destination; delivery events if post-delivery flows are desired; monitored reply destination; site/security issue status and the program's outstanding content approvals. Do not paste passwords or API secrets into this document.

The local skill's launch rule is “QA before any send (two-person sign-off, client final).” That applies to sending, not preparation of the HTML/Figma deliverable. It does not block local design, image generation or build automation.

## 11. Reference links

- [Project entry point](../START-HERE.md)
- [Program source of truth](01-Email-Program-Source-of-Truth.md)
- [Existing copy deck](02-Email-Copy-Deck.md)
- [Puratek email compliance skill](../skill/puratek-email-compliance/SKILL.md)
- [Round 2 historical audit](../audit/round-2/REPORT.md)
- [FunnelKit automation conditions](https://funnelkit.com/docs/autonami-2/automations/conditions/) — documented conditions; installed availability still needs verification.
- [FunnelKit cart automation setup](https://funnelkit.com/docs/autonami-2/carts/set-up-abandoned-cart-automation/) — trigger, email action and test-cart workflow.
- [FunnelKit merge tags](https://funnelkit.com/docs/autonami-2/how-to/use-merge-tags-in-automations-broadcasts/) — dynamic fields; copy exact syntax from the installed picker.
- [FunnelKit cart coupon auto-application](https://funnelkit.com/docs/autonami-2/carts/auto-apply-coupons/) — documented cart recovery coupon mechanism; not evidence that a generic shop URL applies a coupon.

No claim in this plan certifies regulatory compliance, live-site security, inbox placement or installed connector behavior. Existing business/content restrictions are preserved from the supplied project rather than reinterpreted as fresh legal advice.
