# Puratek email campaign

Mobile-first email review site, FunnelKit HTML drafts and Figma import library.

The application is in `puratek-email/`. Use Node 22 or newer:

```sh
npm ci --prefix puratek-email
npm run check --prefix puratek-email
npm run serve --prefix puratek-email
```

Open http://127.0.0.1:5173/. The complete HTML and image ZIP is generated at build time and can be downloaded from the review site.

## Vercel

Import this repository with the repository root as Root Directory. The root `vercel.json` installs/builds the nested application and publishes `puratek-email/dist`. Use Node 22. No environment variables are required for the static review site.

## Contents

- 23 email roles plus one alternative to W2
- 96 mobile/desktop, light/dark Figma HTML views
- Original logos, catalog images, illustration masters and provenance
- Explicit coverage gaps and optional-flow labels
- A 10% first-order offer for Puratek

Generated sites, ZIPs, dependencies, local platform configuration and supplied third-party design references are excluded from Git. They are not needed to build the review site.

See `puratek-email/README.md` for validation and export commands. All automation definitions are local specifications; nothing is activated by deploying this review site. Actual FunnelKit and Figma extension behavior needs installation-specific verification.

Current design decisions and inventory reconciliation: [29 September feedback reference](docs/18-Feedback-and-Implementation-Reference.md). [Blank notes file](docs/19-Working-Notes.md) is available for future notes.
