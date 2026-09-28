# Client review and Vercel handoff

## Review flow

Homepage → click an email name → dedicated `/emails/<id>/` page. The page shows subject/preheader and one mobile email, with no sidebar or strategy dashboard. Below the email, the client can expand feedback or implementation details. The first set contains 10 proposed launch templates; the remaining 13 stay under “Later / optional”.

Feedback is a local browser draft. “Copy notes” and “Download notes” create a handoff the client can send to the team. No fake submit confirmation, account, database or shared comment storage is provided. A downloaded note contains the template ID, revision and page URL. A live shared-feedback service can be connected separately if desired.

## Connect to GitHub / Vercel

Two checked-in configurations support either repository layout:

| GitHub repository root | Vercel root directory | Build / output |
| --- | --- | --- |
| Entire `puratek-email-everything` workspace | Repository root | Root `vercel.json`: install/build within `puratek-email`; publish `puratek-email/dist` |
| Contents of `puratek-email/` only | Repository root | Nested `vercel.json`: npm ci / npm run build; publish `dist` |

Use Node 22 and Framework Preset “Other”. Static files are emitted as real directories with `index.html`, so a direct shared email URL works without an SPA rewrite. The build requires no email-provider or OpenAI credentials. The output excludes raw client archives, private chats, node_modules, rejected generated art and unpublished source assets.

[Vercel configuration reference](https://vercel.com/docs/project-configuration/vercel-json)

No repository remote was provided, so no GitHub push or Vercel deployment has been performed. The project is configured for that next step; it is not claimed live.

## Before sharing

Use Vercel Deployment Protection if the material must remain private. The noindex metadata and robots file discourage indexing; they are not access control. Preview drafts expose illustrative codes and product information, not real contacts or recovery tokens. Do not add real customer information to review HTML or client feedback.

Confirm direct links load, all selected images resolve, mobile overflow is absent and the intended revision is deployed. Production email image hosting remains the client's approved domain, separate from the Vercel review site.
