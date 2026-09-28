# Campaign role and section map

23 roles + one mutually exclusive W2 alternative. New non-welcome copy is draft for review.

## W1 — The Standard Is Documentation.

- Role: Establish the documentation standard, introduce verification, then offer a curated catalog path.
- Trigger: Account created with marketing opt-in
- Timing: Immediately after recorded marketing opt-in
- Audience: New accounts, US, opted in
- Sections: welcome-offer → From Batch to Documentation. → Find What Fits Your Research.
- Artwork: automation-r8/w1.jpg; product-focused composition
- Dynamic: Verified coupon data; business address, opt-out and preferences.
- Measurement: Documentation/catalog clicks and assisted first orders; offer eligibility and complaints
- Exit/suppression: Spam ≥ 0.20% · hard bounce ≥ 1% · unsubscribe ≥ 0.8% per send → pause flow

## W2 — Don’t Just Read the Claim. Read the Evidence.

- Role: Teach the COA checklist, open the documentation, then invite catalog review.
- Trigger: W1 sent, no order yet
- Timing: Day 2; no first order and no competing cart message
- Audience: Welcome flow, orders = 0
- Sections: What Should a COA Actually Tell You? → Don’t Take Our Word for It. → Now Put the Checklist to Work.
- Artwork: automation-r8/w2.jpg; product-focused composition
- Dynamic: Verified coupon data; business address, opt-out and preferences.
- Measurement: Documentation/catalog clicks and assisted first orders; offer eligibility and complaints
- Exit/suppression: Spam ≥ 0.20% · hard bounce ≥ 1% · unsubscribe ≥ 0.8% per send → pause flow

## W2-ALT — Don’t Just Read the Claim. Read the Evidence.

- Role: Retained purity and identity comparison for reference; not an additional send.
- Trigger: W1 sent, no order yet
- Timing: Archived editorial alternative to W2; recommended sequence uses the COA checklist
- Audience: Welcome flow, orders = 0
- Sections: Two questions. Different evidence. → Don’t Take Our Word for It. → Now Put the Checklist to Work.
- Artwork: automation-r8/w2.jpg; product-focused composition
- Dynamic: Verified coupon data; business address, opt-out and preferences.
- Measurement: Documentation/catalog clicks and assisted first orders; offer eligibility and complaints
- Exit/suppression: Spam ≥ 0.20% · hard bounce ≥ 1% · unsubscribe ≥ 0.8% per send → pause flow

## W3 — Before you choose a research material, know what to look for.

- Role: Turn education into a practical specifications/documentation/traceability evaluation.
- Trigger: W2 sent, no order yet
- Timing: Day 4; no first order and no competing cart message
- Audience: Welcome flow, orders = 0
- Sections: Three things worth reviewing. → Now that you know what to look for, start exploring. → Ready when you are.
- Artwork: automation-r8/w3.jpg; product-focused composition
- Dynamic: Verified coupon data; business address, opt-out and preferences.
- Measurement: Documentation/catalog clicks and assisted first orders; offer eligibility and complaints
- Exit/suppression: Spam ≥ 0.20% · hard bounce ≥ 1% · unsubscribe ≥ 0.8% per send → pause flow

## W4 — The standard stays the same.

- Role: Optional welcome follow-up for engaged nonbuyers, only while the offer is valid.
- Trigger: W3 sent, no order yet
- Timing: Day 7
- Audience: Welcome flow, orders = 0
- Sections: Before you decide. → Your first-order offer.
- Artwork: automation-r8/w4.jpg; product-focused composition
- Dynamic: Verified coupon data; business address, opt-out and preferences.
- Measurement: Documentation/catalog clicks and assisted first orders; offer eligibility and complaints
- Exit/suppression: Spam ≥ 0.20% · hard bounce ≥ 1% · unsubscribe ≥ 0.8% per send → pause flow

## C1 — Your cart. Your next step.

- Role: Restore the selected cart with minimal distraction; no new recommendations.
- Trigger: Cart abandoned with captured email, contact not unsubscribed
- Timing: 1 hour
- Audience: All abandoners (exit on order / empty cart / unsubscribe)
- Sections: Review your selection. → A question before ordering?
- Artwork: automation-r8/c1.jpg; product-focused composition
- Dynamic: Full cart block and restore link; business address, opt-out and preferences.
- Measurement: Restored carts and completed orders; branch and coupon performance
- Exit/suppression: Spam ≥ 0.20% · hard bounce ≥ 1% · unsubscribe ≥ 0.8% per send → pause flow

## C2A — Start with confidence in the information.

- Role: First-order cart branch: answer documentation hesitation and show the approved offer.
- Trigger: C1 sent, cart still open
- Timing: 24 hours
- Audience: Abandoners with orders = 0
- Sections: Your saved materials. → A first-order offer.
- Artwork: automation-r8/c2a.jpg; product-focused composition
- Dynamic: Full cart block and restore link; Verified coupon data; business address, opt-out and preferences.
- Measurement: Restored carts and completed orders; branch and coupon performance
- Exit/suppression: Spam ≥ 0.20% · hard bounce ≥ 1% · unsubscribe ≥ 0.8% per send → pause flow

## C2B — A familiar supplier. A fresh review.

- Role: Returning-customer branch: current batch information, no automatic discount.
- Trigger: C1 sent, cart still open
- Timing: 24 hours
- Audience: Abandoners with orders ≥ 1
- Sections: Pick up where you left off.
- Artwork: automation-r8/c2b.jpg; product-focused composition
- Dynamic: Full cart block and restore link; business address, opt-out and preferences.
- Measurement: Restored carts and completed orders; branch and coupon performance
- Exit/suppression: Spam ≥ 0.20% · hard bounce ≥ 1% · unsubscribe ≥ 0.8% per send → pause flow

## C3 — What would help you decide?

- Role: Resolve the remaining information gap without introducing a stronger discount.
- Trigger: C2a/C2b sent, cart still open
- Timing: 48 hours
- Audience: All remaining abandoners
- Sections: The information behind the order. → Your saved selection.
- Artwork: automation-r8/c3.jpg; product-focused composition
- Dynamic: Full cart block and restore link; business address, opt-out and preferences.
- Measurement: Restored carts and completed orders; branch and coupon performance
- Exit/suppression: Spam ≥ 0.20% · hard bounce ≥ 1% · unsubscribe ≥ 0.8% per send → pause flow

## C4 — A person can help.

- Role: Optional final support note only; no invented cart expiry or urgency.
- Trigger: C3 sent, cart still open
- Timing: 72 hours
- Audience: All remaining abandoners
- Sections: Let’s find the right information.
- Artwork: automation-r8/c4.jpg; product-focused composition
- Dynamic: business address, opt-out and preferences.
- Measurement: Restored carts and completed orders; branch and coupon performance
- Exit/suppression: Spam ≥ 0.20% · hard bounce ≥ 1% · unsubscribe ≥ 0.8% per send → pause flow

## B1 — A number needs context.

- Role: Educational broadcast for engaged subscribers; deepen W2 rather than repeat the welcome offer.
- Trigger: Broadcast, Tuesday of week 1
- Timing: Week 1 option: documentation; choose one broadcast only
- Audience: Purchasers with confirmed consent, US only; excludes contacts in the Welcome flow
- Sections: Read the document in context. → Bring the document into the decision.
- Artwork: automation-r8/b1.jpg; product-focused composition
- Dynamic: business address, opt-out and preferences.
- Measurement: Qualified documentation/catalog clicks and assisted orders
- Exit/suppression: Release next tier only if spam < 0.10%, bounce < 0.5%, clicks ≥ baseline; stop at spam ≥ 0.20%

## B2 — Clarity after checkout.

- Role: Explain service touchpoints without promising an unverified dispatch outcome.
- Trigger: Broadcast, Thursday of week 1
- Timing: Alternative weekly option: dispatch; not an additional week-1 send
- Audience: Purchasers with confirmed consent, US only; excludes contacts in the Welcome flow
- Sections: Know where to look. → Review the current shipping information.
- Artwork: automation-r8/b2.jpg; product-focused composition
- Dynamic: business address, opt-out and preferences.
- Measurement: Qualified documentation/catalog clicks and assisted orders
- Exit/suppression: Release next tier only if spam < 0.10%, bounce < 0.5%, clicks ≥ baseline; stop at spam ≥ 0.20%

## B3 — An offer. The same documentation standard.

- Role: Optional approved promotion; no automatic discount calendar or product recommendations.
- Trigger: Broadcast, Saturday of week 1 (only if client approves a sale)
- Timing: Saturday → Monday 11:59 PM PST
- Audience: Purchasers engaged in the last 90 days, confirmed consent, US only; excludes contacts in the Welcome flow
- Sections: Review the offer details.
- Artwork: automation-r8/b3.jpg; product-focused composition
- Dynamic: business address, opt-out and preferences.
- Measurement: Qualified documentation/catalog clicks and assisted orders
- Exit/suppression: Release next tier only if spam < 0.10%, bounce < 0.5%, clicks ≥ baseline; stop at spam ≥ 0.20%

## P1 — The next step is keeping the details together.

- Role: Optional marketing service orientation; avoid duplicating the transactional receipt.
- Trigger: Verified paid order; opted-in customer; exclude duplicate order-service notifications
- Timing: Proposed: 2 hours after verified payment
- Audience: US contacts with recorded marketing consent; no suppression, unresolved support issue, refund or dispute
- Sections: One useful reference.
- Artwork: automation-r8/p1.jpg; product-focused composition
- Dynamic: business address, opt-out and preferences.
- Measurement: Documentation access and service feedback; support resolution
- Exit/suppression: Recheck eligibility immediately before send. Stop on unsubscribe, complaint or hard bounce. Pause for refund/dispute/support issue; exit on a new order where applicable.

## P2 — Connect the material to its document.

- Role: Post-dispatch documentation continuity; do not imply delivery before a verified event.
- Trigger: Verified dispatch event; marketing consent required
- Timing: Proposed: 1 day after dispatch
- Audience: US contacts with recorded marketing consent; no suppression, unresolved support issue, refund or dispute
- Sections: Make the connection. → Keep the reference together.
- Artwork: automation-r8/p2.jpg; product-focused composition
- Dynamic: business address, opt-out and preferences.
- Measurement: Documentation access and service feedback; support resolution
- Exit/suppression: Recheck eligibility immediately before send. Stop on unsubscribe, complaint or hard bounce. Pause for refund/dispute/support issue; exit on a new order where applicable.

## P3 — Was the information easy to find?

- Role: Ask for service feedback after verified delivery; no outcome testimonials.
- Trigger: Verified delivery event; no refund/dispute/open support issue
- Timing: Proposed: 7 days after confirmed delivery
- Audience: US contacts with recorded marketing consent; no suppression, unresolved support issue, refund or dispute
- Sections: Tell us what could be clearer.
- Artwork: automation-r8/p3.jpg; product-focused composition
- Dynamic: business address, opt-out and preferences.
- Measurement: Documentation access and service feedback; support resolution
- Exit/suppression: Recheck eligibility immediately before send. Stop on unsubscribe, complaint or hard bounce. Pause for refund/dispute/support issue; exit on a new order where applicable.

## P4 — Still looking for a batch document?

- Role: Optional documentation assistance; skip contacts with an active support case.
- Trigger: Verified delivery; no newer order or active support issue
- Timing: Proposed: 21 days after confirmed delivery
- Audience: US contacts with recorded marketing consent; no suppression, unresolved support issue, refund or dispute
- Sections: Send the details we can match.
- Artwork: automation-r8/p4.jpg; product-focused composition
- Dynamic: business address, opt-out and preferences.
- Measurement: Documentation access and service feedback; support resolution
- Exit/suppression: Recheck eligibility immediately before send. Stop on unsubscribe, complaint or hard bounce. Pause for refund/dispute/support issue; exit on a new order where applicable.

## R1 — A new order deserves a new review.

- Role: Repeat-purchase education based on engagement, never assumed usage or depletion.
- Trigger: Consented purchaser; engaged within 90 days; no competing flow
- Timing: Proposed eligibility checkpoint: 30 days after last order
- Audience: US contacts with recorded marketing consent; no suppression, unresolved support issue, refund or dispute
- Sections: Review the current reference.
- Artwork: automation-r8/r1.jpg; product-focused composition
- Dynamic: business address, opt-out and preferences.
- Measurement: Qualified catalog/documentation clicks and subsequent orders
- Exit/suppression: Recheck eligibility immediately before send. Stop on unsubscribe, complaint or hard bounce. Pause for refund/dispute/support issue; exit on a new order where applicable.

## R2 — Explore with the details in front of you.

- Role: An engaged-purchaser catalog invitation without speculative recommendations.
- Trigger: Consented engaged purchaser; no new order since enrollment
- Timing: Proposed eligibility checkpoint: 60 days after last order
- Audience: US contacts with recorded marketing consent; no suppression, unresolved support issue, refund or dispute
- Sections: Product information comes first.
- Artwork: automation-r8/r2.jpg; product-focused composition
- Dynamic: business address, opt-out and preferences.
- Measurement: Qualified catalog/documentation clicks and subsequent orders
- Exit/suppression: Recheck eligibility immediately before send. Stop on unsubscribe, complaint or hard bounce. Pause for refund/dispute/support issue; exit on a new order where applicable.

## R3 — Planning starts with current information.

- Role: Low-pressure planning touchpoint, conditional on recent meaningful engagement.
- Trigger: Consented purchaser still meaningfully engaged; no newer order
- Timing: Proposed eligibility checkpoint: 90 days after last order
- Audience: US contacts with recorded marketing consent; no suppression, unresolved support issue, refund or dispute
- Sections: Move at your laboratory’s pace.
- Artwork: automation-r8/r3.jpg; product-focused composition
- Dynamic: business address, opt-out and preferences.
- Measurement: Qualified catalog/documentation clicks and subsequent orders
- Exit/suppression: Recheck eligibility immediately before send. Stop on unsubscribe, complaint or hard bounce. Pause for refund/dispute/support issue; exit on a new order where applicable.

## X1 — The documentation is a place to start.

- Role: One evidence-led re-engagement invitation for an approved consented segment.
- Trigger: Separate client-approved inactive purchaser segment with recorded permission
- Timing: Proposed: 90 days since meaningful engagement; disabled pending audience approval
- Audience: US contacts with recorded marketing consent; no suppression, unresolved support issue, refund or dispute
- Sections: Review before deciding.
- Artwork: automation-r8/x1.jpg; product-focused composition
- Dynamic: business address, opt-out and preferences.
- Measurement: Explicit engagement/preferences and correct sunset suppression
- Exit/suppression: Recheck eligibility immediately before send. Stop on unsubscribe, complaint or hard bounce. Pause for refund/dispute/support issue; exit on a new order where applicable.

## X2 — What information are you missing?

- Role: Re-engagement through a useful service conversation, no fabricated familiarity.
- Trigger: Eligible win-back contact with no engagement/order since X1
- Timing: Proposed: 7 days after X1
- Audience: US contacts with recorded marketing consent; no suppression, unresolved support issue, refund or dispute
- Sections: Ask before you order.
- Artwork: automation-r8/x2.jpg; product-focused composition
- Dynamic: business address, opt-out and preferences.
- Measurement: Explicit engagement/preferences and correct sunset suppression
- Exit/suppression: Recheck eligibility immediately before send. Stop on unsubscribe, complaint or hard bounce. Pause for refund/dispute/support issue; exit on a new order where applicable.

## X3 — We’ll leave the next step to you.

- Role: Honor a real sunset rule after nonresponse; requires suppression integration.
- Trigger: Eligible win-back contact with no engagement/order; sunset automation required
- Timing: Proposed: 14 days after X1; suppress marketing after final nonresponse
- Audience: US contacts with recorded marketing consent; no suppression, unresolved support issue, refund or dispute
- Sections: Keep only what is useful.
- Artwork: automation-r8/x3.jpg; product-focused composition
- Dynamic: business address, opt-out and preferences.
- Measurement: Explicit engagement/preferences and correct sunset suppression
- Exit/suppression: Recheck eligibility immediately before send. Stop on unsubscribe, complaint or hard bounce. Pause for refund/dispute/support issue; exit on a new order where applicable.

## H1 — Let’s review the details together.

- Role: Support-led branch for complex/high-value carts; replaces standard cart reminders.
- Trigger: Eligible abandoned cart total > $500 or quantity ≥10; replaces standard cart flow
- Timing: Proposed: 1 hour after abandonment; seven-day re-entry limit
- Audience: US contacts with recorded marketing consent; no suppression, unresolved support issue, refund or dispute
- Sections: A useful place to begin.
- Artwork: automation-r8/h1.jpg; product-focused composition
- Dynamic: business address, opt-out and preferences.
- Measurement: Qualified support replies and resolved order questions
- Exit/suppression: Recheck eligibility immediately before send. Stop on unsubscribe, complaint or hard bounce. Pause for refund/dispute/support issue; exit on a new order where applicable.

