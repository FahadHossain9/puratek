# Email layout research and implementation — revision 3

Researched 28 September 2026. Scope: email layout and review experience, not a website redesign. “Other version” is interpreted as desktop alongside the existing mobile view, pending clarification.

## Decision

Keep one responsive email design per template. Show mobile and desktop on separate review views, selectable above the email. Do not show two emails, strategy panels or competing content columns side by side. Desktop does not need extra copy.

Use a 600px maximum email width; the mobile review is 375px and also tested at 320px. Main body copy stays at 16px/25px; primary buttons have at least a 48px rendered height in browser QA. These are project design targets, not legal requirements.

## Evidence and its limits

- [Mailchimp: template widths](https://mailchimp.com/help/about-template-widths/) describes its templates as no wider than 600px. This supports the desktop width choice; it is not a rule imposed by FunnelKit.
- [Mailchimp: mobile friendliness](https://templates.mailchimp.com/design/mobile-friendliness/) recommends readable text, large touch targets and responsive adaptation. It allows multiple layout approaches. Choosing one column throughout is our response to the client's request for less density, not a claim that all two-column emails fail.
- [Mailchimp: layout and purpose](https://templates.mailchimp.com/design/layout-and-purpose/) distinguishes email from website design and prioritizes the opening message. We use one primary task per email and move review commentary below the preview.
- [Can I Email: border radius](https://www.caniemail.com/features/css-border-radius/) and [linear gradients](https://www.caniemail.com/features/css-linear-gradient/) document uneven support across clients. Keep solid cream/orange backgrounds and functional links when decorative effects disappear. Their compatibility tests cover specific client versions; do not interpret the matrix as a guarantee for every current mailbox.
- [FunnelKit: three email builders](https://funnelkit.com/docs/autonami-2/email-builder/ways-to-build-an-email/) explicitly provides Raw HTML for externally constructed email code. The review-view switch and feedback controls belong only to the website; the email export contains neither.

## Changes

1. Mobile/Desktop links on all 23 dedicated review pages; only one version renders at a time. Desktop remains responsive on a phone, with a note explaining its 600px target.
2. Product artwork above its caption rather than next to a text column. Keep supplied artwork small; no new unrelated decorative images.
3. Remove repetitive proof sections from W1 and W3. Keep the welcome offer and dispatch message focused; mandatory footer content is preserved.
4. Replace the old 1240px email-plus-strategy boards with one centered email and collapsed timing/purpose notes below it. Remove old score badges and detailed duplicate strategy copy from these boards.
5. Provide both mobile and desktop Figma links. Use the same content and brand tokens across both versions.

The cart's product/size/quantity/price table is transactional data, not a second marketing content column. Replacing the installed FunnelKit cart block still requires saved-template and mailbox testing.

## Validation

Check both review versions, active link labels, navigation, local feedback continuity, 320px overflow, 600px desktop width, loaded assets, stacked image/caption geometry, and the old board URLs. Re-run the email rendering matrix and export checks. Browser checks cannot prove Outlook/Gmail delivered rendering or Figma connector fidelity. No emails are activated or sent.
