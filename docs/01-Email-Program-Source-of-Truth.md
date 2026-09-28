# Puratek Peptides — Email Program Source of Truth

Sep 27, 2026 · @sakib

## 1. Executive summary

**Client:** Puratek LLC, d/b/a Puratek Peptides — puratekpeptides.com (US-only fulfilment, \~16,000 accounts, \~$600k/month). **Stack:** WooCommerce + FunnelKit Automations, sending through Mailgun. **Scope now:** Welcome flow (4), Abandoned cart (4), broadcasts.

**The honest risk picture.** Email is not where this business is most likely to get hurt — the website is. But email *multiplies* whatever the site says: every email is a timestamped, forwardable record of how the company markets its products, sent to thousands of people at once. FDA's 2026 warning letters to research-peptide sellers judged "intended use" from the *whole* marketing context, and treated coded product names (e.g. "GLP-1-R peptide" for retatrutide) as no protection. So the goal is not to disguise what is sold; it is to make sure nothing we send adds evidence of human use, and that every email reads as a legitimate lab-supply communication to a regulator, a payment processor, Mailgun's abuse desk, or a Gmail spam classifier.

**Five decisions that matter most**

1. **Fix the site issues in Section 2 before the first send.** Several live items (a meta description that says "GLP-3R", a public review that says "60mg Tirz", a staging domain in the footer) will be one click from every email.
2. **Exclude the incretin-class products (PUR-1S, PUR-2T, PUR-3R, Cagrilintide) from all email promotion** — no product blocks, no "bestseller" features, no images. Cart emails show them only as plain line items the customer already chose. These are exactly the compounds the 2024–2026 FDA letters targeted. Strongest recommendation in this doc; client should confirm in writing.
3. **Never promote or cross-sell bacteriostatic water in email.** FDA letters cite BAC water sold alongside peptides as evidence of preparation for injection.
4. **Dedicated, authenticated, warmed sending subdomain** (Section 5). With \~16k contacts, one bad broadcast can push Gmail's complaint rate past 0.3% and trigger outright rejection.
5. **Two-person sign-off on every email** against the QA checklist (Section 11), client as final approver.

**What this program will and won't do.** It builds a high-converting program on quality, documentation, service and speed — genuinely Puratek's strongest story. It will not create alternate code names, wording designed to slip past filters or regulators, or copy that nudges toward human use. Beyond ethics, that approach demonstrably fails: it's the pattern FDA quotes back in its letters. **The client should have FDA/regulatory counsel review the site and templates**; this document is operational guidance, not legal advice.

## 2. Site audit — fix before any email goes out

Reviewed 27 Sept 2026: home, shop grid, PUR-1S, PUR-2T, PUR-3R, BW, FAQ, Disclaimer, Terms. Every email links here, so these are email risks too.

| # | Severity | Where | Issue | Fix |
| --- | --- | --- | --- | --- |
| 1 | Critical | PUR-3R meta description (Google snippet) | Reads "GLP-3R research peptide" — shows in search results and link previews. Mirrors the "GLP-1-R peptide" wording FDA quoted in the Gram Peptides letter (31 Mar 2026). | Rewrite to match PUR-2T/PUR-1S neutral pattern; resubmit URL in Search Console. |
| 2 | Critical | Homepage reviews carousel | A review says a customer got "60mg Tirz" and "stacked" codes. Real compound name + consumer framing, on the homepage. | Remove it. Set a moderation rule: no review goes live mentioning compound nicknames, dosing, body effects, or "stack". |
| 3 | Critical | PUR-1S description | Lists a molecular formula and describes a C18 diacid chain "for extended half-life" — identifies the compound exactly and uses pharmacokinetic language. | Legal review. At minimum remove half-life / clinical-style phrasing. |
| 4 | Critical | BW product + homepage copy | Homepage says bacteriostatic water "goes into so many first orders"; BW page cross-links PUR-3R; description says "for reconstitution". FDA letters cite this pairing. | Counsel decision on BW. Remove cross-links between BW and peptides; remove the homepage sentence. |
| 5 | High | Footer logo, "Home" link, BW age-gate logo image | Point to **test5.instaquirk.tech** (staging). Leaks dev infrastructure, looks like a phishing redirect to filters. | Replace with puratekpeptides.com everywhere; block staging from indexing. |
| 6 | High | Terms §10 | Returns contact is **support@wheat-eland-381594.hostingersite.com** (Hostinger temp domain). | Change to support@puratekpeptides.com. |
| 7 | High | Age requirement | Terms §4 says 18+; age gate and registration say 21+. | Align to 21+ everywhere (emails will say 21+). |
| 8 | High | Open Graph tags, all pages | og:title is "Get Premium Research Peptides With Fair Prices" everywhere; og:description empty; og:url always the homepage. Every shared link previews the same generic card. | Per-page OG title/description; fixes how links render in some mail clients too. |
| 9 | Medium | PUR-2T meta description | Ends with stray "Puratek.UR" — typo in Google snippet. | Fix. Client is sensitive to typos; this is live. |
| 10 | Medium | TESA product | Display name coded but URL slug is /tesamorelin/ (a compound FDA named in the Aug 2026 letters). | Counsel decision; note that coding names doesn't help either way. |
| 11 | Medium | Homepage testing list | "Fentanyl Screening" — fine on-site, but a strong spam-filter trigger. | Never use this word in email. |
| 12 | Medium | Site meta | Omnisend verification tag present — an earlier ESP. | Confirm Omnisend is fully off and export its unsubscribe/suppression list into FunnelKit before sending. |
| 13 | Low | Offers | Site offers 10% first order; the GreenLabs template uses 15%; homepage review mentions stacking codes. | One offer, one code, no stacking. Emails use **10%** unless client changes it. |
| 14 | Low | Social | TikTok and Instagram linked in footer. Two different TikTok handles appear (@puratekpeptides and @puratek). | Confirm correct handle; social icons in email only if accounts are clean of human-use content. |

**Positives to lean on in email:** third-party testing, batch-specific COAs, 99% purity guarantee with refund, same-day US dispatch before 1 PM PST, real phone support (702-518-4855, Mon–Fri 9–4 PST), strong research-only disclaimers, 21+ gate, researcher-type capture at registration.

## 3. Regulatory reality (US) and what it means for email

**What happened in 2026.** FDA published seven warning letters on 7 April 2026 (dated 31 March) and five more dated 24 August 2026 (Peak Performance Peptides, Royal Peptides, NuScience Peptides, Peptide Partners, Tex Peptides). Compounds named include semaglutide, tirzepatide, retatrutide, survodutide, mazdutide, tesamorelin, SS-31, PT-141 — plus bacteriostatic water sold for reconstitution.

**How FDA decides.** Under FD&C Act §201(g)(1), intended use is inferred from everything around the product. The letters repeatedly say that despite RUO labelling, evidence from the website established the products were intended as drugs for human use. Evidence cited: weight/glucose/appetite language, dosing or "once-weekly" language, bundles pitched for metabolic research, BAC water, peptide calculators and guides, customer testimonials — and **coded names**: Gram Peptides called retatrutide "GLP-1-R peptide" and tirzepatide "GLP-2 peptide"; FDA named both. Earlier letters (Summit Research, Dec 2024) named cagrilintide and quoted "Tirz" and "Reta" nicknames and the company's Facebook page.

**What this means for every email**

- Emails are marketing context. An email that says "our most popular metabolic peptide" is as usable as a product page.
- Disclaimers don't neutralise body copy. A footer saying "not for human use" doesn't rescue a subject line that implies it.
- Customer words count. Never quote reviews that mention effects, dosing, or nicknames.
- Payment processors and Mailgun read the same signals. Their tolerance is lower than FDA's; losing either is faster than any FDA action.

**Europe / UK.** The site ships US-only. If any EU/UK contacts exist, marketing to them needs GDPR/PECR consent and the product category is higher risk there. Default: **suppress all non-US contacts** until the client confirms otherwise.

Sources: [FDA — Gram Peptides letter](https://www.fda.gov/inspections-compliance-enforcement-and-criminal-investigations/warning-letters/gram-peptides-721806-03312026) · [FDA — Royal Peptides letter](https://www.fda.gov/inspections-compliance-enforcement-and-criminal-investigations/warning-letters/royal-peptides-llc-734884-08242026) · [FDA — Summit Research letter](https://www.fda.gov/inspections-compliance-enforcement-and-criminal-investigations/warning-letters/summit-research-peptides-695607-12102024) · [FDA — unapproved GLP-1 concerns](https://www.fda.gov/drugs/drug-alerts-and-statements/fdas-concerns-unapproved-glp-1-drugs-used-weight-loss)

## 4. Content rules

**Voice.** Calm, precise, lab-supplier. Short sentences. Confidence comes from proof (COA, batch, purity, dispatch time), never from hype. Address the reader as a researcher or lab, never "you" as a body.

**Product handling tiers**

| Tier | Products | Allowed in email |
| --- | --- | --- |
| Do not promote | PUR-1S, PUR-2T, PUR-3R, Cagrilintide, TESA, Melanotan-1, any blend pitched together, BW / HOS BW | Plain cart line item only (name, size, qty, price). No images, no features, no recommendations, no "back in stock". |
| Neutral | BPC-157, GHK-Cu, KPV, MOTS-c, NAD+, Glutathione, Ipamorelin, CJC-1295, Semax, Selank | Name + size + "batch COA available". No mechanism, benefit or use-case copy. |
| Brand-level | Testing process, COAs, purity guarantee, shipping, support | Freely — this is the conversion story. |

Tier assignments are a starting point; counsel may move items.

**Always say (approved language)**

- research use only · for laboratory research · batch-specific certificate of analysis · third-party tested · ≥99% purity (HPLC) · lyophilized · ships same day from the USA · order before 1 PM PST · questions about documentation, testing or shipping
- Standard footer disclaimer (verbatim from site): *All products sold by Puratek are strictly for laboratory and research use only. They are not intended for human or animal consumption, diagnostic, therapeutic, or clinical use. These statements have not been evaluated by the U.S. Food and Drug Administration.*

**Never say (banned in subject, preheader, body, alt text, image text, link text, URL parameters)**

- Real or nickname names of coded products, or anything linking a code to a compound (GLP, incretin, receptor agonist, "sema", "tirz", "reta", "the 2T", etc.)
- Body/effect words: weight, fat, appetite, metabolism, glucose, insulin, energy, recovery, healing, anti-aging, skin, tan, libido, muscle, sleep, cognition, longevity, results, transformation
- Use words: dose, dosing, protocol, cycle, stack, inject, reconstitute, mix, units, syringe, pen, weekly, per day, beginner
- Clinical words: treat, cure, therapy, patient, clinical results, trials show, FDA-approved (or "FDA" in any positive sense)
- Pressure/spam words: "last chance", "act now", "miracle", "guaranteed results", "free" in subject lines, ALL CAPS, multiple !!!, $$$
- Filter-sensitive words: fentanyl, pharmacy, Rx, prescription, pills, meds
- "Stack" in any sense, including stacking discount codes

**Images**

- Vials only if label shows neutral-tier name; never show coded GLP-class vials.
- No syringes, pens, people, bodies, before/after, food, gym, mirrors, scales.
- Every image has alt text written to the same rules; no text-only-in-image offers (Gmail clips and filters image-heavy mail).

**Reviews in email:** only about shipping speed, packaging, COA clarity, support. Edit nothing; if a review mentions anything banned, don't use it at all.

## 5. Deliverability and infrastructure

**Volume reality.** 16k contacts × welcome + cart + \~1 broadcast/week ≈ 70–110k emails/month. That makes Puratek a Gmail/Yahoo/Microsoft **bulk sender** (5,000+/day to one provider on broadcast days), and the rules apply permanently once crossed.

**Non-negotiable setup (before first send)**

1. **Sending subdomain:** e.g. `mail.puratekpeptides.com` for marketing; keep order/transactional mail on a separate subdomain or stream so a marketing problem never stops order confirmations.
2. **SPF** and **DKIM (2048-bit)** for the Mailgun subdomain; **DMARC** published on the root, starting `p=none` with reporting (rua), moving to `quarantine` after 4–6 clean weeks. From-domain must **align** with DKIM.
3. **One-click unsubscribe (RFC 8058)** — `List-Unsubscribe` + `List-Unsubscribe-Post` headers on every marketing email; confirm FunnelKit→Mailgun actually emits both (test in Gmail "Show original"). Honour within 48 hours.
4. **Custom tracking domain** in Mailgun (e.g. `links.puratekpeptides.com`) so click-tracking links aren't on a shared Mailgun domain.
5. **Dedicated IP?** At \~100k/month a dedicated IP is borderline; if used, it needs a 4–6 week warm-up. Ask Mailgun's team; don't buy one and blast.
6. **Mailgun AUP check:** Mailgun can refuse senders in categories it sees as risky "whether permitted by law or not". Tell the Mailgun account manager what the business is up front and get written OK; discovering it mid-broadcast is the worst case.
7. **Monitoring:** Google Postmaster Tools, Yahoo CFL, Microsoft SNDS, DMARC report parser, Mailgun bounce/complaint webhooks feeding back to FunnelKit suppression.

**Thresholds and actions**

| Metric | Target | Stop and investigate |
| --- | --- | --- |
| Gmail spam rate | < 0.10% | ≥ 0.20% (0.30% = enforcement) |
| Hard bounce | < 0.5% | ≥ 1% |
| Unsubscribe per send | < 0.3% | ≥ 0.8% |
| Open rate (directional only, Apple MPP inflates) | — | sudden 30%+ drop = likely spam-foldering |

**Warm-up plan for the first broadcast**

Don't send to 16k on day one. Tier by engagement: Day 1 → buyers from last 30 days (\~most engaged); Day 3 → last 90 days; Day 5 → last 180 days; Day 8+ → rest. Watch Postmaster between tiers. Anyone with no open/click/order in 180+ days gets a single re-permission email, then suppression if no response.

**FunnelKit specifics to verify**

- Abandoned-cart capture requires an email captured at checkout; confirm it only fires for logged-in or email-entered carts (GDPR/consent note in Section 6).
- Exit conditions: order placed, unsubscribed, cart recovered — on every step.
- Global frequency cap: max 1 marketing email per contact per day across all automations and broadcasts.
- UTM tagging on every link; no UTMs containing product codes.

## 6. Consent, list hygiene and law

**The biggest hidden risk: "16,000 users" ≠ "16,000 marketing subscribers."** The registration form captures a 21+/research-use attestation and terms consent, but no visible marketing opt-in checkbox. Under US CAN-SPAM, existing customers can be emailed with a clear opt-out — but Mailgun's AUP requires permission "expressly obtained", and spam complaints come from people who don't remember agreeing. Before broadcast:

- Segment contacts into **Purchasers** (bought within 24 months — strong implied relationship), **Registered non-buyers**, and **Unknown source** (imported, Omnisend legacy, affiliate).
- **Unknown source → do not email.** Registered non-buyers → one re-permission email first.
- Add an unticked "Email me research updates and offers" checkbox to registration and checkout going forward; store timestamp + IP + source in FunnelKit.

**CAN-SPAM checklist (every commercial email)**

- Accurate From name ("Puratek Peptides") and From address on puratekpeptides.com
- Subject line not deceptive; no fake "Re:" or "Your order" on marketing mail
- Physical postal address of Puratek LLC in footer (**needed from client**)
- Clear unsubscribe link, working 30 days after send, honoured within 10 business days (target 48h per Gmail)
- Identified as an advertisement where it is one (the offer framing does this)

**Transactional vs marketing.** Cart recovery emails are generally treated as commercial — they get the full footer and unsubscribe. Order confirmations/shipping updates are transactional — no promos inside them.

**State laws.** California (CCPA/CPRA) and similar state privacy laws: privacy policy must describe email tracking and sharing with Mailgun/FunnelKit. **Note: no Privacy Policy link appeared in the site footer** — only Terms, Disclaimer, Shipping, Purity. Add one; emails will link to it.

**EU/UK.** Suppress by default (Section 3). If client insists: separate list, explicit opt-in only, GDPR-compliant privacy notice, DPA with Mailgun (EU region).

**Data minimisation in FunnelKit.** Don't store or merge "research field" into subject lines. Never pass product names into email subject merge tags.

## 7. Welcome flow — 4 emails

Adapted from the GreenLabs AU framework (structure kept, offer and claims changed for Puratek/US). **Trigger:** account created *with* marketing opt-in. **Exit:** first order placed or unsubscribed. **Offer:** the site's existing 10% first-order code, single-use, non-stackable, 14-day expiry. **Conversion angle throughout:** proof beats promises — show the paperwork.

| # | Timing | Job | Subject (A / B) | Preheader | Body outline | CTA |
| --- | --- | --- | --- | --- | --- | --- |
| 1 | Immediately | Welcome + deliver code | Welcome to Puratek — your 10% code is inside / Your Puratek account is ready | Batch-tested research peptides, shipped same day from the USA. | Thank-you; what Puratek is (lab-supply, US-based); 3 proof points (third-party tested, batch COA, ships same day before 1 PM PST); code block; one line on research-use-only. | View the catalog |
| 2 | Day 2 | Trust: how testing works | What happens to a batch before it reaches you / 8 checks behind every Puratek batch | Purity, identity, net content, endotoxins and more. | The testing process in plain words (omit the fentanyl item); how to read a COA with an annotated generic COA image; the 99% purity guarantee and refund. | See certifications |
| 3 | Day 4 | Service & logistics | Order by 1 PM PST, ships today / Questions? A real person answers | Same-day dispatch, tracking, and phone support. | Dispatch cut-off; tracking; storage-on-arrival basics (keep sealed, cold, dark — same wording as FAQ); support hours and phone; one neutral-tier product row (name, size, "COA available"). | Contact support / Shop |
| 4 | Day 7 | Conversion | Your 10% code expires in 7 days / Still deciding? Here's everything in one place | Code, COAs and same-day shipping — ready when you are. | Recap the 3 proofs; code with expiry; short FAQ (legal status: RUO only; FDA approval: no; COA: yes); support line. | Use my 10% code |

**Rules specific to Welcome**

- No product recommendations based on research field. It looks personalised; to a regulator it looks like steering.
- No "bestsellers" / "most popular" blocks — on this site the bestsellers are the GLP-class codes.
- Email 3's product row uses neutral-tier items only, rotated.
- Don't mention the bulk-quantity discounts (10%/15% for 5+/10+ items) in welcome — it reads as volume consumer buying.

## 8. Abandoned cart flow — 4 emails

**Trigger:** cart abandoned with a captured email of a contact who has not unsubscribed. **Exit:** order placed, cart emptied, or unsubscribed — checked before every step. **Branch:** first-time vs returning customer (GreenLabs logic kept).

| # | Timing | Audience | Job | Subject (A / B) | Body outline | CTA |
| --- | --- | --- | --- | --- | --- | --- |
| 1 | 1 hour | All | Reminder | Your cart is saved / You left something in your cart | Plain cart table (name, size, qty, price — no product images for do-not-promote tier; neutral tier may show vial image); dispatch cut-off line; support line. | Return to cart |
| 2 | 24 hours | **First-time only** (orders = 0) | Lower first-order friction | Your first order: 10% off with this code / A first-order code for your saved cart | Cart table; 10% single-use code; 3 proofs (tested, COA, same-day); guarantee line. | Complete order with 10% off |
| 2b | 24 hours | Returning customers | Reassure, no discount | Your cart is still here / Ready when you are | Cart table; "same batch-level documentation as your last order"; reorder convenience. | Return to cart |
| 3 | 48 hours | All | Objections | Questions about COAs or shipping? / How we verify every batch | Cart table; 3 short Q&As (Where's the COA? When does it ship? What if purity is below 99%?); one approved service-only review. | Return to cart / Ask a question |
| 4 | 72 hours | All | Final, human | Should we release your cart? / Last note about your saved cart | Short, plain-text-style; offer help by reply or phone; no new discount, no urgency language. | Return to cart |

**Rules specific to Cart**

- **Cart line items for do-not-promote products:** show only the product name as it appears in WooCommerce, size, qty, price. No thumbnail, no description, no "complete your research" framing, no "customers also bought".
- **Never add BW / HOS BW as a suggested add-on**, even if it's in the cart.
- No cross-sell block anywhere in the cart flow.
- Discount only in email 2 for first-timers; never increase it later (it trains waiting).
- Suppress the flow if the same contact triggered it in the last 7 days (prevents cart-bait abuse and fatigue).
- High-value carts (e.g. > $500 or 10+ vials): route to a plain "can we help with a larger order?" email from support, not the standard flow — B2B buyers respond to humans, and it's a natural compliance checkpoint.

## 9. Broadcast campaign rules

**Cadence:** start at 1/week max; never more than 2/week. Engaged-only (opened/clicked/ordered in last 90 days) for promotional sends; full list only for genuine service news (new COA portal, shipping changes, holiday cut-offs).

**Good broadcast themes (low risk, still convert)**

- New batch COAs published (neutral-tier products only)
- Testing lab / methodology updates
- Shipping cut-offs for holidays; new carrier options
- Support hours, new phone line, order-tracking improvements
- Store-wide sale framed as a sale on the lab-supply store ("10% off the catalog this week") — **never** product-specific promotions for do-not-promote tier

**Banned broadcast types**

- "Back in stock" or "restock" for GLP-class codes (these drive the most human-use-signalling behaviour)
- Holiday/New-Year framing tied to bodies ("new year, new…")
- Bundles, kits, "research packs" combining compounds
- Anything educational about what a compound does
- Affiliate/partner program recruitment emails to customers (affiliates often publish uncontrolled human-use content; keep affiliate comms on a separate list with its own rules)

**Sale-event mechanics (e.g. July 4th-style sales)**

- One code, clear start/end, no stacking with first-order code (the homepage review shows stacking already happened — close it in WooCommerce).
- Send to engaged segment first, wait 4 hours, check Postmaster/complaints, then release to the next tier.
- Subject lines: "Puratek store-wide: 10% off through Sunday" style. No "!!!", no emojis in subject for the first 60 days of sending.

## 10. Design and brand rules

- **Palette:** follow the site — black/near-black primary (site theme colour is rgb(0,0,0)), white background for body, one accent for buttons. Confirm exact hex values and logo files with client; don't sample from screenshots.
- **Logo:** use the file served from puratekpeptides.com, **never** from test5.instaquirk.tech. Host all email images on the sending or main domain, not third-party CDNs.
- **Layout:** single column, 600px, live text for all offers and CTAs, image-to-text ratio well under 40% image. One primary CTA per email.
- **Typography:** system-safe fonts with web-font fallback; body ≥ 15px; buttons as bulletproof HTML, not images.
- **Header:** logo only. No hero images of vials for do-not-promote products; no lab-coat stock people.
- **Footer (every email, fixed template block):** research-use disclaimer (verbatim, Section 4) · 21+ statement · Puratek LLC postal address · support email + phone + hours · links to Terms, Disclaimer, Privacy Policy · unsubscribe + preference link. Social icons off until accounts are reviewed.
- **Dark mode:** test logo and buttons in Apple Mail and Gmail dark mode; provide a light-outline logo variant.
- **Accessibility:** alt text on every image (written to Section 4 rules), 4.5:1 contrast, links distinguishable without colour.
- **Naming:** "Puratek Peptides" in From name and first mention; "Puratek" thereafter. Never "Puratek Labs", "Puratek Pharma", or anything implying a pharmacy.

## 11. Pre-send QA checklist

Two people sign every email: builder + reviewer. Client approves copy before it goes live. Any "no" = do not send.

**Compliance**

- [ ] No banned word from Section 4 in subject, preheader, body, alt text, image text, link text, or UTM (run a find for each list; keep a saved checker)
- [ ] No do-not-promote product appears except as a plain cart line item
- [ ] No BW / HOS BW anywhere as a suggestion
- [ ] No review, quote or testimonial beyond service/shipping/COA topics
- [ ] Research-use disclaimer present, verbatim
- [ ] 21+ statement present
- [ ] Offer matches the site (10%, single code, expiry stated, no stacking)

**Links and brand**

- [ ] Every link resolves to puratekpeptides.com (none to test5.instaquirk.tech, hostingersite.com, or Mailgun default domains)
- [ ] Landing pages linked are clean per Section 2
- [ ] Logo and images load from own domain; alt text on all
- [ ] Spelling and grammar checked twice (tool + human read aloud); product names match WooCommerce exactly
- [ ] From name "Puratek Peptides"; reply-to is a monitored inbox

**Legal/footer**

- [ ] Postal address, unsubscribe link, preference link, Terms/Disclaimer/Privacy links all work
- [ ] List-Unsubscribe + List-Unsubscribe-Post headers present (check raw source in Gmail)

**Technical**

- [ ] SPF/DKIM/DMARC pass and align (Gmail "Show original")
- [ ] Rendered in Gmail web/app, Apple Mail (light + dark), Outlook desktop, Outlook.com, Yahoo
- [ ] Size under 102 KB HTML (Gmail clipping)
- [ ] Merge tags have fallbacks; test with a contact that has empty first name
- [ ] Seed/inbox-placement test to Gmail, Outlook, Yahoo seed accounts lands in Inbox/Promotions, not Spam

**Audience and timing**

- [ ] Segment correct (engaged tier; non-US suppressed; unknown-source suppressed; unsubscribes and bounces suppressed)
- [ ] Frequency cap respected (no one gets 2 marketing emails today)
- [ ] Automation exit conditions tested with a real test order

**After send (first 24h)**

- [ ] Postmaster spam rate, bounces and unsubscribes checked at 2h and 24h against Section 5 thresholds
- [ ] Replies read; any human-use question answered with the standard RUO reply, never with usage info

## 12. Open questions for the client

Answers change the build; please fill in inline.

1. **Domain confirmation:** the brief mentions "peptidepeptides.com" and "puratekwebtech.com"; this audit used **puratekpeptides.com**. Is that the only store domain, and the one we send from?
2. **Consent source of the 16k:** how many are purchasers vs registered-only vs imported? Was Omnisend used to email them, and can we get its unsubscribe/bounce export?
3. **Product tiers:** does the client accept excluding PUR-1S/2T/3R, Cagrilintide, TESA, Melanotan-1 and BW from all email promotion? (Section 4)
4. **Site fixes:** who owns fixes 1–9 in Section 2, and by when? Recommended: before first send.
5. **Regulatory counsel:** does Puratek have FDA/regulatory counsel who can review templates once?
6. **Mailgun:** existing account or new? Has Mailgun been told the business category? Shared or dedicated IP? Current sending domain?
7. **Offer policy:** confirm 10% first-order (site) vs 15% (GreenLabs template); expiry length; close code stacking in WooCommerce.
8. **Postal address** for CAN-SPAM footer, and a **Privacy Policy** URL.
9. **Non-US contacts:** any exist? OK to suppress?
10. **Reply handling:** who monitors replies, and is there an approved canned response for human-use questions?
11. **Brand assets:** logo files (light/dark), exact hex codes, font names.
12. **GreenLabs framework:** do you want Post-Purchase, Cross-sell/Reorder and Win-back specified for Puratek next? (Cross-sell is the highest-risk branch here and needs its own rules.)
