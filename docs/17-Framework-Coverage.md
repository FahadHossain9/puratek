# GreenLabs framework coverage in Puratek

Audit date: 28 September 2026. Reference: `/Users/fahadhossain/Downloads/GreenLabs AU Email Automation Framework.docx`, read directly using textutil. Compared against the current Puratek `dist/manifest.json`, `dist/flows/specification.json` and `src/flows.ts`.

## Finding

Puratek does not fully cover the framework. There are corresponding draft slots for the five lifecycle families, but several serve different purposes. In particular, documentation-support and generic catalog reminders do not replace purchase-based next-product discovery, complementary-product selection or validated reorder targeting.

“Covered” below means the purpose is represented in the local draft, not that the automation is configured, tested or live. All current flows explicitly have `status: specification-only`; the policy has `enabled: false`. This audit changes no copy, designs, coupons or live configuration. Instructions and implementation statuses inside the GreenLabs document describe that other store; they are not authorizations or verified facts about Puratek.

## All 18 core touchpoints

| Framework touchpoint | Puratek evidence | Coverage and remaining work |
| --- | --- | --- |
| Welcome 1: welcome and first purchase | W1 brand introduction, verification approach, four products and first-order offer | Covered in draft. |
| Welcome 2: trust and brand introduction | W2 purity/identity education and documentation; W2-alt COA checklist | Covered in draft. Choose one version, not two sends. |
| Welcome 3: product/research education | W3 specifications, documentation and traceability checklist | Partial: strong evaluation education, but limited product/category-specific context. |
| Welcome 4: conversion and first order | W4 questions and first-order offer | Covered as an optional draft. It is excluded from the proposed initial three-email welcome sequence. |
| Cart 1: initial reminder | C1 sample cart products and restore action, specified at 1h | Covered in draft/specification; installed cart rendering remains unverified. |
| Cart 2: first-order decision support | C2a first-order offer; C2b returning-customer alternative | Covered with deliberate adaptation: Puratek 10%, not GreenLabs 15%. Returning customers get a separate message rather than simply skipping the offer step. |
| Cart 3: objection handling and social proof | C3 specifications, batch documents and order questions | Partial: objection handling exists; no approved service testimonial or other actual customer social proof is included. |
| Cart 4: final human recovery | C4 reply/contact invitation | Partial: support angle exists, but the current draft omits the cart and restore action requested by the framework. It is optional and excluded from the initial sequence. |
| Post-purchase 1: expectations and support | P1 order-confirmation reference and contact route | Partial: no substantive preparation, dispatch or tracking expectation section. Existing transactional emails are referenced, not inspected or proven to cover these gaps. |
| Post-purchase 2: relevant education | P2 label/batch/COA matching checklist | Partial: useful documentation education, but no adaptation to the purchased product/category. |
| Post-purchase 3: review/feedback | P3 reply/contact request about service and documentation | Covered for the document's “share feedback” option. A public-review destination and collection workflow are not implemented; those would be additional scope. |
| Post-purchase 4: repeat purchase/next product | P4 asks for order and batch references to locate documents | Missing intended role: this is documentation support, not a relevant next-purchase bridge. |
| Cross-sell 1: relevant product discovery | R1 invites review of current batch documentation | Missing intended role: no purchase-linked product selection, product cards or relevance rules. |
| Cross-sell 2: complementary product | R2 provides a generic catalog invitation | Missing intended role: no approved complementary-product mapping or explanation tied to the previous selection. |
| Cross-sell 3: reorder | R3 invites catalog review and planning | Partial: repeat-purchase invitation exists, but the proposed 30/60/90-day repeat checkpoints are not derived from validated behavior; no specific reorder destination/selection is implemented. |
| Win-back 1: re-engagement | X1 documentation invitation without an immediate discount | Covered as a draft/specification; inactive audience definition and enrollment remain unverified. |
| Win-back 2: discovery/reminder | X2 asks about missing information and routes to support | Partial: service angle overlaps the framework, but it provides no fresh product/category discovery and no product exploration CTA. |
| Win-back 3: final opportunity | X3 preferences/reply and promised marketing pause | Covered as an adapted draft. The actual sunset/suppression action is not installed. The framework's incentive is optional, so absence of a discount is not a gap. |

## Additional opportunities

| Opportunity | Current Puratek state |
| --- | --- |
| Browse/viewed-product abandonment | Explicit dependency-gated opportunity only. No finished flow, email or validated event integration. |
| Site abandonment | Not separately represented in the opportunity list. No finished flow, email or verified integration. |

The GreenLabs document itself describes these two opportunities as unconfirmed in that store's setup. That does not establish universal FunnelKit support or non-support, and does not verify Puratek's installation.

## Why 23 emails do not equal complete coverage

Puratek's 23 roles comprise four welcome, five cart variants, four post-purchase, three repeat-engagement, three win-back, three broadcasts and one high-value support email. The extra returning-customer cart variant, broadcasts and support message increase the count without filling the missing lifecycle purposes. W2-alt is a twenty-fourth design, not a twenty-fourth automation role.

The currently proposed initial set is seven templates: W1–W3 and C1/C2a/C2b/C3. Each cart contact receives one C2 branch. W4/C4 and the other lifecycle roles are outside that initial set. Local HTML, browser and export tests do not establish live trigger, timing, audience or delivery behavior.

## Design coverage is a separate gap

The DOCX specifies purpose, sequence and audience logic. It does not define the detailed visual compositions in the separate PDF/PNG references. Completing this lifecycle checklist would not by itself finish the richer visual design pass discussed in the conversation. Several existing drafts still use a short body under the common illustration panel.

## Recommended next work

1. Map each of the 18 reference purposes to an explicit Puratek keep/adapt/omit decision. Retain the additional variants only where they have a clear job.
2. Rework P4 and R1–R3 around useful, purchase-relevant discovery/reordering, with approved product mappings and evidence-based eligibility; avoid assumed consumption schedules or implying products should be used together.
3. Add the missing C3 social proof only from a real approved service review. Restore a cart/action module in C4 if retaining that reference role.
4. Complete P1's service expectations and P2's purchase-relevant education; give X2 a concrete reason to revisit the catalog. Product-specific W3 education is an optional enhancement to match the reference more closely.
5. Apply the richer, purpose-specific section compositions across the families, while keeping live product blocks and reusable table-based modules straightforward to implement.
6. Configure and verify the selected flows in the actual store, including conditional branches, exclusions, event data, dynamic blocks, feedback routes and sunset actions. Keep unverified opportunities labeled as such.

No new discount, recommendation rule, review, delivery event or capability is approved merely by appearing in the GreenLabs reference.
