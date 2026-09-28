// Historical baseline deck. Current rendered campaign copy is authored by campaign.ts.
// Single source for every word in the Puratek emails.
// Copy is taken verbatim from "Puratek Peptides — Email Copy Deck (pre-HTML)".
// Anything not in the deck is listed in `strategy.addedText` so the client can approve it.

export type Block =
  | { t: "p"; text: string }
  | { t: "list"; items: string[] }
  | { t: "qa"; items: [string, string][] }
  | { t: "steps"; items: [string, string][] };

export type Offer = { title: string; code: string; terms: string };

export type Strategy = {
  goal: string;
  trigger: string;
  audience: string;
  segment: string;
  timing: string;
  kpi: string;
  stopRules: string;
  included: string[];
  leftOut: string[];
  compliance: string[];
  addedText: string[];
  score: string;
  lift: string;
};

export type CampaignSection = {
 id:string; eyebrow?:string; heading?:string; paragraphs?:string[]; items?:[string,string][]; comparison?:boolean; products?:{name:string;copy:string;file:string;href:string;cta:string}[]; setup?:string; cta?:{label:string;href:string}; offer?:Offer; cart?:boolean; tone?:"white"|"surface";
};
export type Email = {
 variantOf?:string; heroCopy?:string[]; heroAction?:{label:string;href:string}; heroSetup?:string; sections?:CampaignSection[];
  id: string;
  flow: "Welcome" | "Abandoned cart" | "Broadcast" | "Post-purchase" | "Repeat engagement" | "Win-back" | "Support";
  status?: "existing-copy-revised" | "draft";
  art?: { file: string; alt: string; kind?: string; width?:number; height?:number };
  name: string;
  subjectA: string;
  subjectB: string;
  preheader: string;
  eyebrow: string;
  headline: string;
  body: Block[];
  proof?: "default" | "testing" | "dispatch";
  cart?: boolean;
  offer?: Offer;
  cta: { label: string; href: string };
  secondary?: { label: string; href: string };
  afterCta?: string;
  support: boolean;
  strategy: Strategy;
};

export const PROOF: Record<string, [string, string][]> = {
  default: [
    ["Third-party tested", "Every batch checked by an independent lab."],
    ["Batch-specific COA", "Review the certificate before you order."],
    ["Ships same day", "Order by 1\u00A0PM\u00A0PST, Mon\u2060–\u2060Fri."],
  ],
  testing: [
    ["Independent lab", "Samples are tested outside Puratek."],
    ["Batch-matched COA", "Match it to the batch number on your vial."],
    ["99% guarantee", "Full refund if a batch tests below 99%."],
  ],
  dispatch: [
    ["Same-day dispatch", "Order by 1\u00A0PM\u00A0PST, Mon\u2060–\u2060Fri."],
    ["Tracking included", "Every order ships with tracking."],
    ["Phone support", "702-518-4855, Mon\u2060–\u2060Fri, 9\u00A0AM\u2060–\u20604\u00A0PM\u00A0PST."],
  ],
};

const SITE = "https://puratekpeptides.com";
const utm = (path: string, campaign: string) =>
  `${SITE}${path}?utm_source=email&utm_medium=${campaign.startsWith("b") ? "broadcast" : "flow"}&utm_campaign=${campaign}`;

const STOP_FLOW = "Spam ≥ 0.20% · hard bounce ≥ 1% · unsubscribe ≥ 0.8% per send → pause flow";
const STOP_BROADCAST = "Release next tier only if spam < 0.10%, bounce < 0.5%, clicks ≥ baseline; stop at spam ≥ 0.20%";

const WELCOME_OFFER: Offer = {
  title: "10% off your first order",
  code: "%%COUPON_CODE%%",
  terms: "Single use. Valid until %%COUPON_EXPIRY%%. Can't be combined with other codes.",
};

const COMMON_COMPLIANCE = [
  "No compound names, effects, use or dosing words (Section 4 banned list checked)",
  "Research-use disclaimer + 21+ statement in locked footer",
  "All links point to puratekpeptides.com with neutral UTMs (Privacy Policy URL pending from client)",
];

export const EMAILS: Email[] = [
  // ───────────────────────── WELCOME ─────────────────────────
  {
    id: "w1",
    flow: "Welcome",
    name: "W1 · Welcome + code",
    subjectA: "Welcome to Puratek. Your 10% code is inside",
    subjectB: "Your Puratek account is ready",
    preheader: "Batch-tested research peptides, shipped same day from the USA.",
    eyebrow: "Welcome",
    headline: "Welcome to Puratek",
    body: [
      {
        t: "p",
        text: "Thanks for creating your account. Puratek is a US-based supplier of research peptides for laboratory use, and we run the business on one idea: you should be able to verify what you receive, not just trust it.",
      },
      { t: "p", text: "Here's what that means for every order." },
    ],
    proof: "default",
    offer: WELCOME_OFFER,
    cta: { label: "Browse the catalog", href: utm("/shop/", "welcome_w1") },
    support: true,
    strategy: {
      goal: "Set positioning (verify, don't trust) and deliver the first-order code",
      trigger: "Account created with marketing opt-in",
      audience: "New accounts, US, opted in",
      segment: "Every new opted-in account (flow, not a batch)",
      timing: "Immediately",
      kpi: "First-order conversion within 14 days; click rate secondary",
      stopRules: STOP_FLOW,
      included: [
        "Headline + two-sentence intro: positioning before any offer",
        "Proof strip: facts do the persuading instead of adjectives",
        "Code after proof so it reads as a welcome, not a bribe",
        "Support line: real people answer, Puratek's strongest differentiator",
      ],
      leftOut: [
        "Product recommendations / bestsellers (site bestsellers are GLP-class)",
        "\"Why research peptides\" explainer (drifts toward use-case copy)",
        "Bulk-quantity discount (reads as consumer volume buying)",
        "Social links (accounts not yet reviewed)",
      ],
      compliance: COMMON_COMPLIANCE,
      addedText: ["Eyebrow label \"Welcome\"", "Proof strip one-liners", "Deck change: headline drops the first name so an empty name never reads \"Welcome to Puratek,\""],
      score: "9 / 10",
      lift: "A/B test subject lines on the first 1,000 sends",
    },
  },
  {
    id: "w2",
    flow: "Welcome",
    name: "W2 · How testing works",
    subjectA: "What happens to a batch before it reaches you",
    subjectB: "The checks behind every Puratek batch",
    preheader: "Purity, identity, net content, sterility, and more. Documented.",
    eyebrow: "Batch testing",
    headline: "Every batch is tested before it ships",
    body: [
      { t: "p", text: "Before a batch goes on sale, a sample goes to an independent lab. It's checked for:" },
      {
        t: "list",
        items: ["Purity (HPLC)", "Identity", "Net content", "Endotoxins", "Sterility", "Heavy metals", "Conformity to specification"],
      },
      {
        t: "p",
        text: "The lab report is published as a batch-specific certificate of analysis (COA). You can review it before you order and match it to the batch number on your vial.",
      },
      { t: "p", text: "If your own testing shows a batch below 99% purity, contact us for a full refund." },
    ],
    proof: "testing",
    cta: { label: "See certifications", href: utm("/certifications/", "welcome_w2") },
    support: true,
    strategy: {
      goal: "Answer the #1 new-supplier hesitation: is it what the label says?",
      trigger: "W1 sent, no order yet",
      audience: "Welcome flow, orders = 0",
      segment: "Flow",
      timing: "Day 2",
      kpi: "Clicks to /certifications/; assisted first orders",
      stopRules: STOP_FLOW,
      included: [
        "Plain-language testing list mirrored from the site",
        "COA explained as something the reader can check",
        "Guarantee closes the loop with a concrete remedy",
      ],
      leftOut: [
        "\"Fentanyl screening\" (true, but a strong spam-filter trigger)",
        "Sample COA image (only a generic neutral-tier COA if client supplies one)",
        "Offer (W1 already delivered the code; W2 stays educational)",
      ],
      compliance: [...COMMON_COMPLIANCE, "Counsel check: \"Endotoxins\" and \"Sterility\" mirror the site but are injectable-style testing terms"],
      addedText: ["Eyebrow label \"Batch testing\"", "Proof strip one-liners for the testing variant", "Deck change: \"The results are published\" → \"The lab report is published\" (\"results\" is on the banned list)"],
      score: "9 / 10",
      lift: "Add a generic neutral-tier COA image with alt text",
    },
  },
  {
    id: "w3",
    flow: "Welcome",
    name: "W3 · Dispatch & support",
    subjectA: "Order by 1 PM PST, ships today",
    subjectB: "Questions? A real person answers",
    preheader: "Same-day dispatch from the USA, tracking on every order.",
    eyebrow: "Shipping & support",
    headline: "Fast, tracked, and answered by people",
    body: [
      {
        t: "p",
        text: "Orders placed before 1 PM PST, Monday to Friday, ship the same day from within the United States, with tracking.",
      },
      {
        t: "p",
        text: "When your order arrives, keep vials sealed and store them cold and away from light until your lab is ready. Check the product page for anything specific.",
      },
      {
        t: "p",
        text: "Need help with documentation or an order? Our team is available by phone and email, Monday to Friday, 9 AM–4 PM PST.",
      },
    ],
    proof: "dispatch",
    cta: { label: "Contact our team", href: utm("/contact-us/", "welcome_w3") },
    secondary: { label: "Browse the catalog", href: utm("/shop/", "welcome_w3") },
    support: false,
    strategy: {
      goal: "Remove logistics anxiety, the second-biggest reason first orders stall",
      trigger: "W2 sent, no order yet",
      audience: "Welcome flow, orders = 0",
      segment: "Flow",
      timing: "Day 4",
      kpi: "Contact-page visits and first orders",
      stopRules: STOP_FLOW,
      included: [
        "Dispatch cut-off stated exactly as on the site",
        "Storage line mirrors the site FAQ word for word (no new claims)",
        "Support hours in body, so the separate support block is dropped",
      ],
      leftOut: [
        "Neutral-tier product row (adds links, dilutes the single job)",
        "Handling detail beyond the FAQ wording (use-adjacent)",
      ],
      compliance: COMMON_COMPLIANCE,
      addedText: ["Eyebrow label \"Shipping & support\"", "Proof strip one-liners for the dispatch variant"],
      score: "8.5 / 10",
      lift: "Secondary CTA kept as a plain text link only",
    },
  },
  {
    id: "w4",
    flow: "Welcome",
    name: "W4 · Code reminder",
    subjectA: "Your 10% code is still waiting",
    subjectB: "Everything you need for your first order",
    preheader: "Code, COAs, and same-day shipping, all in one place.",
    eyebrow: "Your first order",
    headline: "Ready when you are",
    body: [
      { t: "p", text: "A quick recap before your first-order code expires on %%COUPON_EXPIRY%%." },
      {
        t: "qa",
        items: [
          ["Is it tested?", "Yes, every batch, by an independent lab, with a COA you can read first."],
          ["When does it ship?", "Same day if you order by 1 PM PST, Monday to Friday."],
          ["Are these products approved for human use?", "No. Everything we sell is for laboratory research only."],
        ],
      },
    ],
    proof: "default",
    offer: WELCOME_OFFER,
    cta: { label: "Browse the catalog", href: utm("/shop/", "welcome_w4") },
    support: true,
    strategy: {
      goal: "Final path to the first order before the code expires",
      trigger: "W3 sent, no order yet",
      audience: "Welcome flow, orders = 0",
      segment: "Flow",
      timing: "Day 7",
      kpi: "Code redemptions",
      stopRules: STOP_FLOW,
      included: [
        "Q&A answers the last objections in the reader's own words",
        "Plain \"not approved for human use\" answer sets expectations and filters risky buyers",
        "Code repeated with a plain expiry date",
      ],
      leftOut: ["\"Last chance\" / urgency language (spam and pressure signal)", "Bigger discount (trains people to wait)"],
      compliance: COMMON_COMPLIANCE,
      addedText: ["Eyebrow label \"Your first order\"", "Deck change: CTA \"Use my 10% code\" → \"Shop with my 10% code\""],
      score: "9 / 10",
      lift: "CTA relabelled from the deck's \"Use my 10% code\" because the code is entered at checkout; if a coupon-URL plugin is added, restore the deck label and auto-apply",
    },
  },

  // ───────────────────────── ABANDONED CART ─────────────────────────
  {
    id: "c1",
    flow: "Abandoned cart",
    name: "C1 · Reminder",
    subjectA: "Your cart is saved",
    subjectB: "You left something in your Puratek cart",
    preheader: "Your items are held. Order by 1 PM PST to ship today.",
    eyebrow: "Your cart",
    headline: "Your cart is saved",
    body: [{ t: "p", text: "You left a few items in your cart. They're saved, so you can pick up where you left off." }],
    cart: true,
    cta: { label: "Return to my cart", href: "%%CART_LINK%%" },
    afterCta: "Orders placed before 1 PM PST, Monday to Friday, ship the same day.",
    support: true,
    strategy: {
      goal: "A clean path back for people who simply got distracted",
      trigger: "Cart abandoned with captured email, contact not unsubscribed",
      audience: "All abandoners (exit on order / empty cart / unsubscribe)",
      segment: "Flow; suppressed if triggered in last 7 days; carts > $500 go to support-led email",
      timing: "1 hour",
      kpi: "Recovered orders",
      stopRules: STOP_FLOW,
      included: ["Shortest email in the program", "Text-only cart table", "Dispatch line gives a reason to finish today without urgency words"],
      leftOut: ["Proof strip (they already chose; reads as over-selling)", "Discount (the first touch never discounts)", "Product images and cross-sell"],
      compliance: [...COMMON_COMPLIANCE, "Cart table is text only: no thumbnails for any product"],
      addedText: ["Eyebrow label \"Your cart\"", "Cart table column labels"],
      score: "9 / 10",
      lift: "Test the cart restore link on mobile before launch",
    },
  },
  {
    id: "c2a",
    flow: "Abandoned cart",
    name: "C2a · First-order code",
    subjectA: "10% off the cart you saved",
    subjectB: "A first-order code for your saved cart",
    preheader: "Tested, documented, and shipped same day from the USA.",
    eyebrow: "Saved cart",
    headline: "Your first order, 10% off",
    body: [
      {
        t: "p",
        text: "Trying a new supplier is a decision. Here's what stands behind this one: every batch is tested by an independent lab, every product has a batch-specific COA you can check before you buy, and if a batch tests below 99% purity, you get a full refund.",
      },
    ],
    proof: "default",
    cart: true,
    offer: {
      title: "Your code",
      code: "%%CART_COUPON_CODE%%",
      terms: "Single use. Valid until %%CART_COUPON_EXPIRY%%. Can't be combined with other codes.",
    },
    cta: { label: "Return to my cart", href: "%%CART_LINK%%" },
    support: true,
    strategy: {
      goal: "Lower first-order friction: trust first, code as the nudge",
      trigger: "C1 sent, cart still open",
      audience: "Abandoners with orders = 0",
      segment: "Flow",
      timing: "24 hours",
      kpi: "Recovered first orders; code redemptions",
      stopRules: STOP_FLOW,
      included: ["Body names the risk (a new supplier) and answers it with three verifiable facts", "Proof strip + cart + code", "Single-use 7-day code"],
      leftOut: ["15% from the GreenLabs template (undercuts the site's 10%, trains waiting)"],
      compliance: [...COMMON_COMPLIANCE, "Cart table is text only: no thumbnails for any product"],
      addedText: ["Eyebrow label \"Saved cart\"", "Offer box title \"Your code\" and terms line (deck gave only \"code, single use, expiry 7 days\")"],
      score: "9 / 10",
      lift: "Separate 7-day cart code so it never conflicts with the Welcome code's date",
    },
  },
  {
    id: "c2b",
    flow: "Abandoned cart",
    name: "C2b · Returning customer",
    subjectA: "Your cart is still here",
    subjectB: "Ready when you are",
    preheader: "Same testing, same documentation, same-day shipping.",
    eyebrow: "Your cart",
    headline: "Welcome back",
    body: [
      {
        t: "p",
        text: "Your cart is saved. Every batch still comes with its own certificate of analysis, and orders before 1 PM PST still ship the same day.",
      },
    ],
    cart: true,
    cta: { label: "Return to my cart", href: "%%CART_LINK%%" },
    support: true,
    strategy: {
      goal: "Continuity for loyal buyers, without discounting",
      trigger: "C1 sent, cart still open",
      audience: "Abandoners with orders ≥ 1",
      segment: "Flow",
      timing: "24 hours",
      kpi: "Recovered repeat orders",
      stopRules: STOP_FLOW,
      included: ["Two-sentence reassurance", "Text-only cart table"],
      leftOut: ["Discount (margin protection; returning buyers convert without it)"],
      compliance: [...COMMON_COMPLIANCE, "Cart table is text only: no thumbnails for any product"],
      addedText: ["Eyebrow label \"Your cart\""],
      score: "8.5 / 10",
      lift: "Plain by design; its job is to not annoy loyal customers",
    },
  },
  {
    id: "c3",
    flow: "Abandoned cart",
    name: "C3 · Three quick answers",
    subjectA: "Questions about COAs or shipping?",
    subjectB: "How we verify every batch",
    preheader: "Three quick answers before you decide.",
    eyebrow: "Before you decide",
    headline: "Three quick answers",
    body: [
      {
        t: "qa",
        items: [
          ["Where do I find the COA?", "On each product page, matched to the batch you receive."],
          ["When will it ship?", "Same day for orders before 1 PM PST, Monday to Friday, with tracking."],
          ["What if purity is below 99%?", "Contact us with your lab report for a full refund."],
        ],
      },
    ],
    cart: true,
    cta: { label: "Return to my cart", href: "%%CART_LINK%%" },
    secondary: { label: "Ask a question", href: utm("/contact-us/", "cart_c3") },
    support: true,
    strategy: {
      goal: "Handle the three objections Puratek can answer with facts",
      trigger: "C2a/C2b sent, cart still open",
      audience: "All remaining abandoners",
      segment: "Flow",
      timing: "48 hours",
      kpi: "Recovered orders; contact-page clicks",
      stopRules: STOP_FLOW,
      included: ["Q&A on COA, shipping and guarantee", "Cart table", "\"Ask a question\" as a text link"],
      leftOut: [
        "Customer review (slot reserved: add only a consented, service-only review)",
        "Any review mentioning products, results or discounts",
      ],
      compliance: [...COMMON_COMPLIANCE, "Cart table is text only: no thumbnails for any product"],
      addedText: ["Eyebrow label \"Before you decide\"", "Deck change: \"your results\" → \"your lab report\" (\"results\" is on the banned list)"],
      score: "8.5 / 10 (9 with an approved review)",
      lift: "One consented service review, e.g. the COA phone-call review",
    },
  },
  {
    id: "c4",
    flow: "Abandoned cart",
    name: "C4 · Final note",
    subjectA: "Should we release your cart?",
    subjectB: "A last note about your saved cart",
    preheader: "No pressure. Reply if something's holding you up.",
    eyebrow: "A quick note",
    headline: "Anything we can help with?",
    body: [
      {
        t: "p",
        text: "We'll keep your cart for a little longer. If something is holding you up, whether it's a question about documentation, payment, or shipping, just reply to this email. A real person on our team will answer.",
      },
    ],
    cart: true,
    cta: { label: "Return to my cart", href: "%%CART_LINK%%" },
    support: false,
    strategy: {
      goal: "Human last touch that recovers people with a real question",
      trigger: "C3 sent, cart still open",
      audience: "All remaining abandoners",
      segment: "Flow",
      timing: "72 hours",
      kpi: "Recovered orders; replies (a positive Gmail signal)",
      stopRules: STOP_FLOW,
      included: ["Reply invitation in body, so the support block is dropped", "Cart table"],
      leftOut: [
        "New or bigger discount, countdown language",
        "Subject A only if carts truly expire; otherwise use Subject B",
      ],
      compliance: [...COMMON_COMPLIANCE, "Replies must route to a monitored inbox"],
      addedText: ["Eyebrow label \"A quick note\""],
      score: "9 / 10",
      lift: "Route replies to a monitored inbox with an approved RUO reply",
    },
  },

  // ───────────────────────── BROADCASTS ─────────────────────────
  {
    id: "b1",
    flow: "Broadcast",
    name: "B1 · Read your COA",
    subjectA: "The standard is documentation",
    subjectB: "How to read a Puratek COA in 60 seconds",
    preheader: "Batch numbers, purity, identity: what each line means.",
    eyebrow: "Documentation",
    headline: "Read your COA in 60 seconds",
    body: [
      {
        t: "p",
        text: "Every Puratek vial has a batch number. That number matches a certificate of analysis from an independent lab. Here's what to look for:",
      },
      {
        t: "steps",
        items: [
          ["Batch number.", "Should match the label on your vial."],
          ["Purity (HPLC).", "Our standard is 99% or higher."],
          ["Identity.", "Confirms the material is what the label says."],
          ["Testing lab and date.", "Tells you who tested it and when."],
        ],
      },
      { t: "p", text: "If anything on a COA doesn't line up with your order, call us. We'll walk through it with you." },
    ],
    cta: { label: "View certifications", href: utm("/certifications/", "b1_coa") },
    support: true,
    strategy: {
      goal: "Loyalty and repeat orders without selling any product",
      trigger: "Broadcast, Tuesday of week 1",
      audience: "Purchasers with confirmed consent, US only; excludes contacts in the Welcome flow",
      segment: "Consented engaged-90 contacts: 30-day tier, then 90-day tier; 180-day tier excluded",
      timing: "Week 1 option: documentation; choose one broadcast only",
      kpi: "Click rate vs baseline; repeat orders within 7 days",
      stopRules: STOP_BROADCAST,
      included: ["Product-neutral COA explainer", "Numbered checklist the reader can use", "Support offer"],
      leftOut: ["Any product names (the explainer is product-neutral by design)"],
      compliance: COMMON_COMPLIANCE,
      addedText: ["Eyebrow label \"Documentation\""],
      score: "9 / 10",
      lift: "—",
    },
  },
  {
    id: "b2",
    flow: "Broadcast",
    name: "B2 · From our shelf to your lab",
    subjectA: "Order by 1 PM PST, ships today",
    subjectB: "How your order gets from our shelf to your lab",
    preheader: "Batch-matched, checked, and shipped with tracking.",
    eyebrow: "How it works",
    headline: "From our shelf to your lab",
    body: [
      {
        t: "steps",
        items: [
          ["You order.", "Check product and batch documents first."],
          ["We match and verify.", "Your order is matched to its tested batch and checked before it leaves."],
          ["Same-day shipping.", "Orders before 1 PM PST, Monday to Friday, ship the same day with tracking."],
        ],
      },
    ],
    proof: "default",
    cta: { label: "Shop the catalog", href: utm("/shop/", "b2_ships_today") },
    support: true,
    strategy: {
      goal: "Reinforce speed, the main reason reviewers say they reorder",
      trigger: "Broadcast, Thursday of week 1",
      audience: "Purchasers with confirmed consent, US only; excludes contacts in the Welcome flow",
      segment: "Tiered release, same as B1",
      timing: "Alternative weekly option: dispatch; not an additional week-1 send",
      kpi: "Clicks and orders within 48 hours",
      stopRules: STOP_BROADCAST,
      included: ["Mirrors the site's \"How it works\" section, so every claim is already public", "Proof strip"],
      leftOut: ["Delivery-time promises (carriers vary; only dispatch time is promised)"],
      compliance: COMMON_COMPLIANCE,
      addedText: ["Eyebrow label \"How it works\""],
      score: "8.5 / 10",
      lift: "Subject A duplicates W3 Subject A; fine because broadcasts exclude Welcome-flow contacts. Pair with B3; B3 does the selling",
    },
  },
  {
    id: "b3",
    flow: "Broadcast",
    name: "B3 · Weekend store-wide sale",
    subjectA: "10% off the Puratek catalog through Monday",
    subjectB: "A weekend code for your next order",
    preheader: "Same testing, same COAs, same-day shipping. One code, three days.",
    eyebrow: "Store-wide sale",
    headline: "10% off, through Monday",
    body: [
      {
        t: "p",
        text: "Use the code below on any order through Monday at 11:59 PM PST. Every batch is still third-party tested, every product still has its COA, and orders before 1 PM PST on Monday still ship the same day.",
      },
    ],
    offer: {
      title: "10% off the catalog",
      code: "%%SALE_CODE%%",
      terms: "Valid through Monday, %%SALE_END_DATE%%, 11:59 PM PST · one use per customer · can't be combined with other codes",
    },
    cta: { label: "Shop the catalog", href: utm("/shop/", "b3_weekend_sale") },
    support: true,
    strategy: {
      goal: "Drive revenue by promoting the store, not a compound",
      trigger: "Broadcast, Saturday of week 1 (only if client approves a sale)",
      audience: "Purchasers engaged in the last 90 days, confirmed consent, US only; excludes contacts in the Welcome flow",
      segment: "Promotional send: 30-day tier, then 90-day tier; 4-hour check between tiers; 180-day tier excluded",
      timing: "Saturday → Monday 11:59 PM PST",
      kpi: "Revenue per recipient; code redemptions",
      stopRules: STOP_BROADCAST,
      included: ["Store-wide offer with exact end time", "No-combination rule stated (closes the stacking seen in a homepage review)", "Proof sentence keeps it on-brand"],
      leftOut: ["\"Bestsellers on sale\" product blocks", "\"Stock up\" language (implies consumption)", "Countdown timer"],
      compliance: [...COMMON_COMPLIANCE, "Code must auto-expire in WooCommerce at the stated time"],
      addedText: ["Eyebrow label \"Store-wide sale\"", "Offer title \"10% off the catalog\"", "Deck change: headline \"this weekend only\" → \"through Monday\" (matched to the stated end time)", "Deck change: CTA \"Shop with 10% off\" → \"Shop the catalog\" (the code is entered at checkout)"],
      score: "8.5 / 10",
      lift: "Price-only angle is inherently weaker; keep the proof sentence",
    },
  },
];
