# Puratek Peptides — Email Copy Deck (pre-HTML)

Sep 28, 2026 · @sakib

## 0. How to use this deck

> **Revised 28 Sep after the independent audit.** Headline for W1 drops the name (no "Welcome to Puratek," when a name is empty); "results" → "lab report" in W2 and C3 (banned word); B3 headline matched to its Monday end time; W4 and B3 CTAs relabelled because codes are entered at checkout; footer drops "Update preferences" and the template drops "View in browser" (FunnelKit tags unverified). Built emails and boards: see the Puratek Email Review hub.

This is the **text-final** layer. Every word that goes into the HTML is here, section by section, with why it's in, what was left out and why, and a rating. Once the client approves this deck, HTML is a pure layout job: nobody writes copy inside the template. Rules come from the [Email Program Source of Truth](https://claude.ai/code/artifact/df463dc7-bbea-4cb5-89bf-2e39340ef217).

**Scoring rubric (each email /10)**

| Criterion | Points | What earns it |
| --- | --- | --- |
| Compliance | 3 | Zero banned terms, correct product tiers, disclaimer + footer complete |
| Clarity | 2 | One job, one CTA, understood in 5 seconds on a phone |
| Proof | 2 | Uses Puratek's real proof (COA, testing, dispatch time, guarantee, phone support) instead of adjectives |
| Conversion mechanics | 2 | Subject/preheader pair works together, CTA is specific, friction removed |
| Brand polish | 1 | Voice consistent, no typos, product names exact |

Compliance is a gate: an email scoring under 3 on compliance doesn't ship regardless of total. Scores are my assessment of the copy; real proof comes from the first two weeks of send data.

**Placeholders (fill before HTML)**

- `{{first_name|there}}`: FunnelKit first-name merge with fallback "there"
- `{{coupon_code}}`: the site's 10% first-order code (confirm the code name; SAVEMY10 was a separate time-boxed campaign code)
- `{{coupon_expiry}}`: 14 days after signup
- `[POSTAL ADDRESS]`: Puratek LLC mailing address (required by CAN-SPAM)
- `[PRIVACY URL]`: site has no Privacy Policy page yet

**Blockers before HTML**

1. Brand assets from client: logo files (light + dark), exact hex codes, font names. Tokens in Section 1 are placeholders sampled from the logo mark.
2. Site fixes 1–9 from the Source of Truth doc (every CTA lands on the site).
3. **The client's payment-plugin concern (28 Sept).** Don't send a single email until the site is confirmed clean. If the site is compromised and gets flagged by Google Safe Browsing, every link in every email becomes a phishing signal and the sending domain's reputation goes with it.
4. Anik's Welcome 1–2 drafts live in a private Google Doc I can't open. Paste them here or share access and I'll reconcile them with this deck.

## 1. Template anatomy and brand tokens

One master template, eight fixed blocks. Every email in this deck is assembled only from these blocks, so each maps 1:1 to a saved block in FunnelKit.

| Block | Width / height | Contents | Why it exists |
| --- | --- | --- | --- |
| A. Preheader bar | 600 × 0 (hidden text) | Preheader text + "View in browser" | Controls the inbox preview line |
| B. Header | 600 × 72 | Logo, centred, links to home | Brand recognition; no nav menu (menus add links and look like a website, not a message) |
| C. Headline | 600, auto | H1, 24–28px, max 8 words | The one idea of the email |
| D. Body text | 600, auto | 1–3 short paragraphs, 16px, line-height 1.5 | Explains the idea |
| E. Proof strip | 600 × \~110 | 3 columns: icon + 2-word label + 1 line | Carries Puratek's real proof points; stacks to 1 column on mobile |
| F. Offer / cart box | 600, auto | Code box *or* plain cart table (no images) | Only in emails with an offer or cart |
| G. CTA button | 280 × 48, centred | Bulletproof HTML button | One primary action per email |
| H. Support line | 600, auto | Phone, email, hours | Human reassurance; Puratek's real differentiator |
| I. Compliance footer (locked) | 600, auto | Disclaimer, 21+, address, links, unsubscribe | Legal + deliverability; never edited per email |

**Brand tokens (placeholders until client sends assets)**

- Primary / text: #111111 (site theme is black)
- Accent / buttons: orange-gold from the logo mark, approx #E8A33D (confirm)
- Background: #FFFFFF body, #F5F5F3 for proof strip and footer
- Button text: #111111 on accent (check 4.5:1 contrast once final hex is known; if it fails, use white on #111111 buttons)
- Fonts: web font TBC, fallback Helvetica/Arial

**Locked footer text (Block I, verbatim)**

> All products sold by Puratek are strictly for laboratory and research use only. They are not intended for human or animal consumption, diagnostic, therapeutic, or clinical use. These statements have not been evaluated by the U.S. Food and Drug Administration.
>
> Puratek sells only to customers 21 years of age or older.
>
> Puratek LLC · \[POSTAL ADDRESS\]
>
> support@puratekpeptides.com · 702-518-4855 · Mon–Fri, 9 AM–4 PM PST
>
> Terms · Disclaimer · Privacy Policy · Unsubscribe
>
> You're receiving this because you created an account at puratekpeptides.com.

**Support line (Block H, verbatim)**

> Questions about documentation, testing, or shipping? Call 702-518-4855 or reply to this email. Mon–Fri, 9 AM–4 PM PST.

**Proof strip (Block E, default text)**

| Third-party tested | Batch-specific COA | Ships same day |
| --- | --- | --- |
| Every batch checked by an independent lab. | Review the certificate before you order. | Order by 1 PM PST, Mon–Fri. |

**Deliberately left out of the template:** social icons (accounts not reviewed yet), product grid/"shop the catalog" block (pulls in GLP-class items), countdown timers (spam-filter signal and pressure language), hero lifestyle images (people/bodies banned).

## 2. Welcome flow

Trigger: account created with marketing opt-in. Exit: first order or unsubscribe. From: Puratek Peptides <hello@mail.puratekpeptides.com>, reply-to support@puratekpeptides.com.

### W1: Welcome + code (immediately)

- **Subject A:** Welcome to Puratek. Your 10% code is inside
- **Subject B:** Your Puratek account is ready
- **Preheader:** Batch-tested research peptides, shipped same day from the USA.

**C. Headline:** Welcome to Puratek

**D. Body:**

> Thanks for creating your account. Puratek is a US-based supplier of research peptides for laboratory use, and we run the business on one idea: you should be able to verify what you receive, not just trust it.
>
> Here's what that means for every order.

**E. Proof strip:** default.

**F. Offer box:**

> 10% off your first order **{{coupon\_code}}** Single use. Valid until {{coupon\_expiry}}. Can't be combined with other codes.

**G. CTA:** Browse the catalog → /shop/

**H. Support line:** default. **I. Footer:** locked.

**Why these sections:** The headline and first paragraph set positioning (verification over trust) in two sentences. The proof strip does the persuading instead of adjectives. The code sits after the proof so it reads as a welcome, not a bribe. The support line signals that real people answer, which is Puratek's strongest differentiator.

**Left out, and why:** Product recommendations and bestsellers (the site's bestsellers are GLP-class). A "why research peptides" explainer (drifts toward use-case copy). The bulk-quantity discount (reads as consumer volume buying). Social links.

**Rating: 9/10.** Compliance 3 · Clarity 2 · Proof 2 · Conversion 1.5 · Polish 0.5 (unrated until brand assets and exact code are in). *To reach 9.5+:* A/B test subject lines on the first 1,000 sends.

### W2: How testing works (day 2)

- **Subject A:** What happens to a batch before it reaches you
- **Subject B:** The checks behind every Puratek batch
- **Preheader:** Purity, identity, net content, sterility, and more. Documented.

**C. Headline:** Every batch is tested before it ships

**D. Body:**

> Before a batch goes on sale, a sample goes to an independent lab. It's checked for:
>
> • Purity (HPLC) • Identity • Net content • Endotoxins • Sterility • Heavy metals • Conformity to specification
>
> The lab report is published as a batch-specific certificate of analysis (COA). You can review it before you order and match it to the batch number on your vial.
>
> If your own testing shows a batch below 99% purity, contact us for a full refund.

**E. Proof strip:** swap to "Independent lab · Batch-matched COA · 99% guarantee".

**G. CTA:** See certifications → /certifications/

**Why these sections:** Converts the site's testing section into an email that answers the #1 hesitation for a new supplier: "is it what the label says?" The guarantee closes the loop with a concrete remedy.

**Left out, and why:** "Fentanyl screening" (true, but a strong spam-filter trigger; the list reads complete without it). A sample COA image (only if the client provides a generic neutral-tier COA; never a GLP-class one). No offer (keeps W2 educational; W1 already delivered the code).

**Rating: 9/10.** Loses 1 on conversion because there's no offer, which is intentional.

### W3: Dispatch and support (day 4)

- **Subject A:** Order by 1 PM PST, ships today
- **Subject B:** Questions? A real person answers
- **Preheader:** Same-day dispatch from the USA, tracking on every order.

**C. Headline:** Fast, tracked, and answered by people

**D. Body:**

> Orders placed before 1 PM PST, Monday to Friday, ship the same day from within the United States, with tracking.
>
> When your order arrives, keep vials sealed and store them cold and away from light until your lab is ready. Check the product page for anything specific.
>
> Need help with documentation or an order? Our team is available by phone and email, Monday to Friday, 9 AM–4 PM PST.

**E. Proof strip:** swap to "Same-day dispatch · Tracking included · Phone support".

**G. CTA:** Contact our team → /contact-us/ (secondary text link: Browse the catalog)

**Why these sections:** Removes logistics anxiety, which is the second-biggest reason first orders stall. The storage line mirrors the site FAQ word-for-word, so no new claims enter the record.

**Left out, and why:** The neutral-tier product row from the source doc. On review, one product row in a service email adds link count and dilutes the single job. Reconstitution or handling beyond the FAQ wording (use-adjacent).

**Rating: 8.5/10.** Two CTAs cost a little clarity; keep the second as a plain text link only.

### W4: Code reminder (day 7)

- **Subject A:** Your 10% code is still waiting
- **Subject B:** Everything you need for your first order
- **Preheader:** Code, COAs, and same-day shipping, all in one place.

**C. Headline:** Ready when you are

**D. Body:**

> A quick recap before your first-order code expires on {{coupon\_expiry}}.
>
> **Is it tested?** Yes, every batch, by an independent lab, with a COA you can read first. **When does it ship?** Same day if you order by 1 PM PST, Monday to Friday. **Are these products approved for human use?** No. Everything we sell is for laboratory research only.

**E. Proof strip:** default.

**F. Offer box:** same as W1.

**G. CTA:** Shop with my 10% code → /shop/ (code auto-applied via URL if FunnelKit supports it)

**Why these sections:** Q&A format answers the last objections in the reader's own words. The "not approved for human use" answer is deliberate: it sets expectations plainly, reinforces RUO in the record, and filters out customers who would cause the business risk.

**Left out, and why:** "Last chance" / "expires soon!!" urgency (spam-filter and pressure language; a plain date works). Increasing the discount (trains people to wait).

**Rating: 9/10.**

## 3. Abandoned cart flow

Trigger: cart abandoned with captured email, contact not unsubscribed. Exit before every step: order placed, cart emptied, unsubscribed. Suppress if triggered in the last 7 days. Carts over $500 or 10+ items skip this flow and get a support-led email.

**Cart table setting (all cart emails):** product thumbnails **off** for every item. That handles the do-not-promote tier without per-product rules, which FunnelKit handles poorly. Columns: Product · Size · Qty · Price, then subtotal. No "You may also like" block.

### C1: Reminder (1 hour, everyone)

- **Subject A:** Your cart is saved
- **Subject B:** You left something in your Puratek cart
- **Preheader:** Your items are held. Order by 1 PM PST to ship today.

**C. Headline:** Your cart is saved

**D. Body:**

> You left a few items in your cart. They're saved, so you can pick up where you left off.

**F. Cart table.**

**G. CTA:** Return to my cart → cart restore link

**D2 (below CTA):** > Orders placed before 1 PM PST, Monday to Friday, ship the same day.

**H. Support line. I. Footer.**

**Why these sections:** Shortest email in the program. At one hour, most abandoners got distracted, so the only job is a clean path back. The dispatch line adds a reason to finish today without urgency language.

**Left out:** proof strip (they already chose; proof here reads as over-selling), discount (first touch should never discount).

**Rating: 9/10.**

### C2a: First-order code (24 hours, orders = 0)

- **Subject A:** 10% off the cart you saved
- **Subject B:** A first-order code for your saved cart
- **Preheader:** Tested, documented, and shipped same day from the USA.

**C. Headline:** Your first order, 10% off

**D. Body:**

> Trying a new supplier is a decision. Here's what stands behind this one: every batch is tested by an independent lab, every product has a batch-specific COA you can check before you buy, and if a batch tests below 99% purity, you get a full refund.

**E. Proof strip:** default. **F. Cart table + offer box** (code, single use, expiry 7 days).

**G. CTA:** Complete my order with 10% off

**Why these sections:** First-timers stall on trust, not price. The body names the risk ("a new supplier") and answers it with three verifiable facts; the code is the nudge, not the argument.

**Left out:** percentage higher than the site's 10% (the GreenLabs template's 15% would undercut the site offer and train discount-waiting).

**Rating: 9/10.**

### C2b: Returning customers (24 hours, orders ≥ 1)

- **Subject A:** Your cart is still here
- **Subject B:** Ready when you are
- **Preheader:** Same testing, same documentation, same-day shipping.

**C. Headline:** Welcome back

**D. Body:**

> Your cart is saved. Every batch still comes with its own certificate of analysis, and orders before 1 PM PST still ship the same day.

**F. Cart table. G. CTA:** Return to my cart

**Why:** Returning buyers know the product; they need continuity, not persuasion.

**Left out:** discount (margin protection; returning buyers convert without it).

**Rating: 8.5/10.** Deliberately plain; its job is to not annoy loyal customers.

### C3: Questions (48 hours, everyone)

- **Subject A:** Questions about COAs or shipping?
- **Subject B:** How we verify every batch
- **Preheader:** Three quick answers before you decide.

**C. Headline:** Three quick answers

**D. Body:**

> **Where do I find the COA?** On each product page, matched to the batch you receive. **When will it ship?** Same day for orders before 1 PM PST, Monday to Friday, with tracking. **What if purity is below 99%?** Contact us with your lab report for a full refund.

**Review (optional, only if approved):** one real customer review about service, shipping speed, or COA support, e.g. the site's review about a call explaining a COA, *if the customer consents to its use in email*.

**F. Cart table. G. CTA:** Return to my cart (secondary text link: Ask a question)

**Why:** Handles the three objections Puratek can answer with facts. The review is service-only, which is the one category of social proof that adds no compliance risk.

**Left out:** any review mentioning products, results, or discounts (the homepage review mentioning "60mg Tirz" and stacked codes is an example of what never goes in email).

**Rating: 8.5/10 without the review, 9/10 with an approved one.**

### C4: Final note (72 hours, everyone)

- **Subject A:** Should we release your cart?
- **Subject B:** A last note about your saved cart
- **Preheader:** No pressure. Reply if something's holding you up.

**C. Headline:** Anything we can help with?

**D. Body:**

> We'll keep your cart for a little longer. If something is holding you up, whether it's a question about documentation, payment, or shipping, just reply to this email. A real person on our team will answer.

**F. Cart table. G. CTA:** Return to my cart

**Why:** Human tone at the end recovers people who had a real question. The reply invitation also generates positive engagement signals for Gmail.

**Left out:** new or bigger discount, countdown language. "Release your cart" is used only if carts really expire; if WooCommerce keeps carts indefinitely, use Subject B.

**Rating: 9/10.**

## 4. Week-1 broadcasts

Audience: existing purchasers with confirmed consent, US only, released in tiers (30-day buyers → 90 → 180), with a Postmaster check between tiers. Contacts currently in the Welcome flow are excluded from broadcasts that week.

### B1: "The standard is documentation" (Tuesday)

- **Subject A:** The standard is documentation
- **Subject B:** How to read a Puratek COA in 60 seconds
- **Preheader:** Batch numbers, purity, identity: what each line means.

**C. Headline:** Read your COA in 60 seconds

**D. Body:**

> Every Puratek vial has a batch number. That number matches a certificate of analysis from an independent lab. Here's what to look for:
>
> **1. Batch number.** Should match the label on your vial. **2. Purity (HPLC).** Our standard is 99% or higher. **3. Identity.** Confirms the material is what the label says. **4. Testing lab and date.** Tells you who tested it and when.
>
> If anything on a COA doesn't line up with your order, call us. We'll walk through it with you.

**G. CTA:** View certifications

**Why:** Turns Puratek's core proof into something useful for customers who already bought, which builds loyalty and repeat orders without selling any product. It also leaves a strong "documentation-first lab supplier" record.

**Left out:** any product names (a COA explainer is product-neutral by design).

**Rating: 9/10.**

### B2: "Ships today" (Thursday)

- **Subject A:** Order by 1 PM PST, ships today
- **Subject B:** How your order gets from our shelf to your lab
- **Preheader:** Batch-matched, checked, and shipped with tracking.

**C. Headline:** From our shelf to your lab

**D. Body (three steps):**

> **1. You order.** Check product and batch documents first. **2. We match and verify.** Your order is matched to its tested batch and checked before it leaves. **3. Same-day shipping.** Orders before 1 PM PST, Monday to Friday, ship the same day with tracking.

**E. Proof strip:** default. **G. CTA:** Shop the catalog. **H. Support line.**

**Why:** Mirrors the site's "How it works" section, so every claim is already public and verified. Reinforces speed, the main reason reviewers say they reorder.

**Left out:** delivery-time promises (carriers vary; the site only promises dispatch time, so email does too).

**Rating: 8.5/10.** Solid, but closer to informational than conversion; the week's sale email does the selling.

### B3: Store-wide sale (Saturday, runs to Monday 11:59 PM PST)

*Only if the client approves a sale for week 1.*

- **Subject A:** 10% off the Puratek catalog through Monday
- **Subject B:** A weekend code for your next order
- **Preheader:** Same testing, same COAs, same-day shipping. One code, three days.

**C. Headline:** 10% off, through Monday

**D. Body:**

> Use the code below on any order through Monday at 11:59 PM PST. Every batch is still third-party tested, every product still has its COA, and orders before 1 PM PST on Monday still ship the same day.

**F. Offer box:** code · "Valid through Monday, \[date\], 11:59 PM PST · one use per customer · can't be combined with other codes".

**G. CTA:** Shop the catalog

**Why:** A catalog-wide sale promotes the store, not a compound, which keeps the do-not-promote tier out of email while still driving revenue. Stating the end time and the no-combination rule prevents the code stacking visible in the homepage review.

**Left out:** "bestsellers on sale" product blocks, "stock up" language (implies consumption), countdown timer.

**Rating: 8.5/10.** Price-only sales are inherently weaker copy; the proof sentence keeps it on-brand. Confirm in WooCommerce that the code expires automatically at the stated time (the SAVEMY10 backend check from 26 Sept is the model).

## 5. Scorecard and path to 9+

| Email | Score | Main limiter | What lifts it |
| --- | --- | --- | --- |
| W1 Welcome + code | 9 | Brand polish unrated | Real logo/hex; subject A/B test |
| W2 Testing | 9 | No offer (by design) | A generic neutral-tier COA image |
| W3 Dispatch & support | 8.5 | Two CTAs | Secondary CTA as plain text link only |
| W4 Code reminder | 9 | — | Auto-apply code via URL |
| C1 Reminder | 9 | — | Cart restore link tested on mobile |
| C2a First-order code | 9 | — | — |
| C2b Returning | 8.5 | Plain by design | Accept; its job is not to annoy |
| C3 Questions | 8.5–9 | Needs approved review | One consented service review |
| C4 Final note | 9 | — | Replies routed to a monitored inbox |
| B1 COA explainer | 9 | — | — |
| B2 Ships today | 8.5 | Informational | Pair with B3 in the same week |
| B3 Store-wide sale | 8.5 | Price-only angle | Proof sentence kept; exact end time |

**Every email clears the compliance gate (3/3)** on copy alone. That score assumes the Source of Truth site fixes are done: an email is only as compliant as the page it links to.

**Approval sequence**

1. Client reads this deck and comments inline (text sign-off).
2. Client sends brand assets → tokens in Section 1 finalised.
3. HTML master template built from the Section 1 blocks, with all 12 emails assembled from approved text only.
4. HTML → Figma for client visual sign-off.
5. Rebuild as saved blocks in FunnelKit → QA checklist → seed tests → go live after the site security check is cleared.
