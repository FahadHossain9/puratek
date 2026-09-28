# US research-peptide email: restrictions and wording

Reviewed 28 September 2026. Scope: US-facing marketing emails, their product imagery and linked landing pages. This is an operational guide, not a legal opinion or permission to sell a product.

> **Revision 6 update:** The team’s supplied campaign now governs W1–W3. The prior product-name/no-photo house restrictions below are historical defaults: the user explicitly requested four W1 product cards including PUR-3R, plus live cart thumbnails. Those are included in the local draft. This does not establish regulatory/provider eligibility; do not silently substitute coded names or imply human use. The 99% blanket refund claim has been removed because the site’s guarantee pages conflict. See guides 15–16 for the current campaign and guide 09 for dynamic image mapping.

## What Claude already researched

The original detailed research is in `01-Email-Program-Source-of-Truth.md`; the condensed house rules are in `../skill/puratek-email-compliance/SKILL.md`. Keep both as historical sources. This guide distinguishes government requirements, provider policies and our own conservative editorial rules. The original list is **not a universal US list of illegal words**.

## 1. FDA: intended use matters more than a disclaimer

FDA's March 31, 2026 Gram Peptides letter identifies both the actual compounds and coded GLP names, cites physiological claims, and discusses bacteriostatic water sold alongside peptides. Its research-use labeling did not overcome contrary intended-use evidence. Do not rename compounds or use indirect wording to preserve human-use messaging. [FDA: Gram Peptides](https://www.fda.gov/inspections-compliance-enforcement-and-criminal-investigations/warning-letters/gram-peptides-721806-03312026)

The August 24, 2026 Peptide Partners letter is another example of FDA treating research-labeled products as drugs based on marketing context. A footer cannot make a contradictory campaign acceptable. Product photos, customer quotations and destination pages need the same review as email copy. [FDA: Peptide Partners](https://www.fda.gov/inspections-compliance-enforcement-and-criminal-investigations/warning-letters/peptide-partners-llc-735063-08242026)

Application to these drafts: discuss documentation, verified order service and accurate commercial terms. Do not explain human effects, administration, treatment or usage schedules. Brand-only language is not a safe harbor for the underlying catalog. Counsel must assess the actual products, claims and destination pages.

## 2. FTC: commercial email duties

Use accurate sender information and truthful subjects; identify advertising appropriately; include a valid postal address and a clear opt-out. Keep the opt-out functional for at least 30 days and honor requests within 10 business days. Hiring an agency does not transfer responsibility. B2B marketing is covered too. Mixed promotional/order-service emails require a primary-purpose assessment; an existing customer relationship does not automatically make an email transactional. [FTC: CAN-SPAM business guide](https://www.ftc.gov/business-guidance/resources/can-spam-act-compliance-guide-business)

Our implementation includes an explicit marketing label and required footer fields. A literal placeholder is not an address or working opt-out. The initial automation audience requires recorded consent under the provider policy below; do not treat the registration count as the subscriber count.

## 3. Mailgun and Gmail: separate provider requirements

Mailgun's AUP requires clear, provable permission for non-transactional messages; bought, rented and scraped lists are prohibited. It also requires a marketing unsubscribe link and a published privacy policy linked from emails. Retain consent evidence and validate that the provider accepts the actual business/use case. CAN-SPAM compliance alone does not establish provider eligibility. [Mailgun AUP](https://www.mailgun.com/legal/aup/)

Gmail's bulk-sender requirements include SPF, DKIM, DMARC and alignment, low complaint rates, and one-click unsubscribe for marketing/subscription mail. Gmail advises keeping spam below 0.1% and avoiding 0.3% or higher. A footer link alone is not RFC 8058 one-click headers. Verify raw delivered headers through the actual FunnelKit/Mailgun chain. [Gmail sender guidelines](https://support.google.com/mail/answer/81126?hl=en)

The project's 0.20% complaint pause, 1% hard-bounce pause and 0.8% unsubscribe pause are **internal operating thresholds**, not statutory limits or universal provider thresholds. Delay tier expansion when metrics are missing; absence of complaints in an incomplete dashboard is not proof of readiness.

## 4. Words and claims: the working editorial restriction list

The following list is a conservative project rule applied to promotional subjects, preheaders, visible copy, alt text and image labels. Avoiding these words does not make an otherwise problematic claim lawful. Some terms have legitimate contexts: for example, required disclaimer text and a technical cart-recovery tag are treated separately.

| Category | Avoid in promotional messaging | Why |
| --- | --- | --- |
| Restricted compound promotion | GLP, GLP-1/2/3 shorthand, incretin, receptor agonist, sema, tirz, reta; coded substitutions that imply the same compounds | Do not disguise the actual product or market restricted products indirectly |
| Body/function outcomes | weight, fat, appetite, metabolism, glucose, insulin, energy, healing, recovery, anti-aging, skin, tan, libido, muscle, sleep, cognition, longevity, transformation, results | Can communicate human-use or outcome claims; broad house list is deliberately conservative |
| Administration/use instructions | dose, dosing, protocol, cycle, stack, inject, injection, reconstitute, mix, units, syringe, pen, weekly, per day, beginner | Do not suggest consumption, administration or schedules |
| Clinical claims | treat, cure, therapy, patient, clinical results, trials show, FDA-approved | Do not imply approved therapeutic status or medical efficacy |
| Pressure/exaggeration | last chance, act now, miracle, guaranteed results, excessive punctuation, ALL-CAPS subjects | Truthfulness, expectation and brand-quality concerns; not an automatic legal blacklist |
| Terms requiring contextual review | fentanyl, pharmacy, Rx, prescription, pills, meds; “free” in a subject | House caution, not evidence that one word automatically triggers a spam filter |

**No evasion substitutions.** Replacing “weight loss” with a euphemism while keeping the same promise is not an acceptable rewrite. Remove the claim and change the message to factual documentation or service information.

Examples of appropriate scope, only when accurate: “View the batch COA”; “Questions about documentation?”; “Orders before [verified cutoff] dispatch the same business day”; “10% off your first order, valid until [real date].” Never invent the lab, test result, batch match, turnaround, guarantee or testimonial.

## 5. Product and imagery decisions for this project

These are internal publication restrictions, **not a determination that the remaining products are FDA-approved or safe**.

- Do not promote PUR-1S/2T/3R or GLP-coded equivalents, Cagrilintide, TESA, Melanotan products, blends/kits, BW/HOS BW. Preserve only necessary plain cart line items for products the customer already selected; no photos, recommendations or add-ons.
- The prior neutral list includes BPC-157, GHK-Cu, KPV, MOTS-c, NAD+, Glutathione, Ipamorelin, CJC-1295, Semax and Selank. This is only an editorial tier. Name, verified size and factual documentation availability still require review.
- No bodies, before/after imagery, administration equipment, people using products or invented certificates. No product image may be relabeled to evade review.
- The new ZIP's GLP products, SS-31, Klow blend and Melanotan 2 images are withheld. “B/c 157” and “Kvp” labels need correction/verification by the source owner. The brand board's Australia address, TESA panel, product claims and contact email are not adopted for this US client.
- The supplied GHK-Cu image is used as a draft documentation/product reference. It visibly says **100 mg**, not 50 mg. Current SKU, printed claims and batch evidence must be verified before release. Original pixels are retained except resizing/compression.

## 6. Niche-specific difficulties that affect launch scope

1. Human-use implication can come from the combination of text, images and landing pages; a word checker alone cannot approve a campaign.
2. A purity claim is not a safety/effectiveness claim. Do not turn “99%” into a medical guarantee or fake test badge.
3. Broad discounts can promote a restricted catalog even without naming a compound. Hold the sale campaign pending product/legal review.
4. Consent, complaint handling and payment/provider acceptance are distinct dependencies. A deleted payment plugin is not proof that the prior security issue is resolved.
5. Automatic product recommendations, cross-sell and assumed replenishment intervals are a poor fit for the present evidence. Do not launch them merely because templates exist.
6. Lifecycle marketing and transactional order notifications must not duplicate one another or bypass consent through a “transactional” label.
7. The mailing address, timezone/cutoff, guarantee terms and coupon behavior must be real. Mockup text and example codes are not production configuration.

## 7. Before sending

Approve actual copy and assets; verify catalog/claims and destination pages; map installed tags; test real coupon eligibility, expiry and nonstacking; confirm suppression and frequency rules; verify headers and opt-out; test target mailbox clients and a controlled seed audience. The supplied program requires builder/reviewer checks and client final approval. No legal or deliverability certification is implied by local HTML validation.

US state privacy/consumer-protection duties can also apply depending on the business, contacts and tracking practices. Those facts were not supplied; this guide does not claim a complete state-by-state legal review.
