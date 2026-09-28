import {writeDownloads} from './downloads';
import * as React from "react";
import { render } from "@react-email/render";
import fs from "node:fs";
import path from "node:path";
import { EMAILS, type Email } from "./content";
import { PuratekEmail, T, CSS } from "./Email";
import { LIFECYCLE } from "./lifecycle";
import {writeReview,LAUNCH_IDS} from "./review";
import {writeGuides} from "./guides";
import {writeFigmaGuide} from './figma-guide';
import {campaign,coaAlternative,PRODUCT_FILES} from "./campaign";
import {FLOWS,POLICY,OPPORTUNITIES} from "./flows";
import { createHash } from "node:crypto";
const ASSETS = JSON.parse(fs.readFileSync("assets/manifest.json", "utf8"));
const MAIN_EMAILS: Email[] = [...EMAILS, ...LIFECYCLE].map(campaign).map(e => ({...e, status:e.status || "existing-copy-revised", art: ASSETS.find((a:any)=>a.usedIn.includes(e.id))}));
const ALL_EMAILS=[...MAIN_EMAILS.slice(0,2),coaAlternative(MAIN_EMAILS[1]),...MAIN_EMAILS.slice(2)];
const PREVIEW_DATE = process.env.PREVIEW_DATE || "2026-09-28";
const date = (days:number) => new Date(Date.parse(PREVIEW_DATE + "T12:00:00Z") + days * 86400000).toLocaleDateString("en-US", {year:"numeric",month:"long",day:"numeric",timeZone:"UTC"});
if (!/^\d{4}-\d{2}-\d{2}$/.test(PREVIEW_DATE) || Number.isNaN(Date.parse(PREVIEW_DATE))) throw new Error("Invalid PREVIEW_DATE");

const OUT = path.resolve(".dist-next");
const SITE = "https://puratekpeptides.com";
const HOSTED_LOGO = `${SITE}/wp-content/uploads/email/puratek-logo-dark@2x.png`; // ← upload this file to WordPress media first

const SAMPLE: Record<string, string> = {
  "%%COUPON_CODE%%": "WELCOME10",
  "%%COUPON_EXPIRY%%": date(14),
  "%%CART_COUPON_CODE%%": "CART10-7QX4",
  "%%CART_COUPON_EXPIRY%%": date(7),
  "%%CART_LINK%%": `${SITE}/cart/`,
  "%%SALE_CODE%%": "WEEKEND10",
  "%%SALE_END_DATE%%": date(7),
  "%%PRIVACY_URL%%": "#privacy-policy-pending",
  "{{unsubscribe_link}}": "#unsubscribe-preview",
  "%%PREFERENCES_URL%%": "#preferences-pending",
};
const HL = (t: string) => `<span style="background-color:#FFE58A;color:#0F1523;padding:0 4px">${t}</span>`;

const rowCell = `font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:22px;overflow-wrap:anywhere;word-break:break-word;color:${T.heading};padding:12px 0;border-bottom:1px solid ${T.line}`;
const sampleRow = (name: string, size: string, qty: string, price: string,file:string) =>
  `<tr><td class="tx-h" style="${rowCell}"><img src="../assets/products-r6/${file}-email.jpg" alt="${name}" width="52" style="display:block;width:52px;height:auto;margin:0 0 8px">${name}</td><td class="tx-h" style="${rowCell}">${size}</td><td class="tx-h" align="center" style="${rowCell};text-align:center">${qty}</td><td class="tx-h" align="right" style="${rowCell};text-align:right;white-space:nowrap">${price}</td></tr>`;
const SAMPLE_ROWS = sampleRow("BPC-157", "10 mg", "1", "$29.95","bpc-157") + sampleRow("GHK-Cu", "100 mg", "1", "$34.95","ghk-cu");
const CART_ROW = /<tr[^>]*>\s*<td[^>]*colspan="4"[^>]*>\s*__CART_ROWS__\s*<\/td>\s*<\/tr>/i;
const CART_BLOCK = /<div data-cart="start">[\s\S]*?<span data-cart="end"><\/span>\s*<\/div>/i;
const SEND_CART = `<div style="margin:4px 0 24px">%%CART_ITEMS%%</div>`; // Entire row block comes from the installed cart event tag; sample rows never ship.

// keep short time phrases on one line
const nobreak = (h: string) =>
  h.replace(/Mon–Fri/g, "Mon&#8288;–&#8288;Fri").replace(/9 AM–4 PM PST/g, "9&nbsp;AM&#8288;–&#8288;4&nbsp;PM&nbsp;PST").replace(/1 PM PST/g, "1&nbsp;PM&nbsp;PST");

async function renderEmail(e: Email, mode: "send" | "preview", logo: string) {
  const address = mode === "send" ? "{{business_address}}" : "[Verified business postal address]";
  let html = await render(<PuratekEmail e={e} opts={{ logoLight: logo, site: SITE, address: "__ADDRESS__", imageBase: mode === "send" ? `${SITE}/wp-content/uploads/email` : "../assets", preview: mode === "preview" }} />, { pretty: true });
  html = html.replace("__ADDRESS__", address);
  if (mode === "send") {
    html = html.replace(CART_BLOCK, SEND_CART);
  } else {
    html = html.replace(CART_ROW, SAMPLE_ROWS).replace("__CART_SUBTOTAL__", "$64.90");
    html = html.replace(/<div data-cart="start">/g, "<div>").replace(/<span data-cart="end"><\/span>/g, "");
    for (const [k, v] of Object.entries(SAMPLE)) html = html.split(k).join(v);
  }
  if (/__CART_|data-cart/.test(html)) throw new Error(`cart marker left in ${e.id}`);
  if (mode === "preview" && /%%|\{\{/.test(html)) throw new Error(`token left in preview ${e.id}: ${html.match(/(%%\w+%%|\{\{[^}]+\}\})/)?.[0]}`);
  if (mode === "preview") html = html.replace("</body>", `<script>(function(){function s(){parent.postMessage({ptEmailHeight:Math.ceil(document.body.getBoundingClientRect().height)},"*")}addEventListener("load",function(){s();setTimeout(s,500)});addEventListener("resize",s)})()</script></body>`);
  return nobreak(html);
}

// Independent audit (round 2, 28 Sep 2026) totals, shown next to the builder's own score
const AUDIT: Record<string, string> = { w1: "9.0", w2: "8.5", w3: "8.0", w4: "8.5", c1: "8.5", c2a: "8.5", c2b: "8.5", c3: "9.0", c4: "8.0", b1: "9.0", b2: "8.5", b3: "7.5" };
const TEMPLATE_CHANGES = [
 "r6: Campaign-specific sections replace the previous fixed body/offer layout.",
 "W1–W3 follow the team copy; W2 has a mutually exclusive COA-checklist alternative.",
 "Original Puratek logos, real catalog vial images and branded editorial panels; no invented labels or COAs.",
 "Cart samples are replaced in full by the installed FunnelKit event tag at export.",
 "Seven initial templates; sixteen optional roles; one W2 alternative. All are deployment drafts.",
];
const esc = (s: string) => s.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
const li = (xs: string[]) => `<ul>${xs.map((x) => `<li>${esc(x)}</li>`).join("")}</ul>`;

function board(e: Email, emailHtml: string) {
  const body=emailHtml.match(/<body[^>]*>([\s\S]*)<\/body>/i)![1];
  return `<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>${esc(e.name)} · Puratek</title><style>${CSS.replace('@media (prefers-color-scheme:dark){','@media not all{')}body{margin:0;background:#F2F2F2;font-family:Arial,Helvetica,sans-serif;color:#242424}.board{max-width:600px;margin:auto}.inbox{padding:24px;font-size:14px;line-height:1.6}.inbox h1{font-size:22px;margin:0 0 12px}.notes{margin:16px 24px 32px;font-size:14px;line-height:1.6}.notes summary{cursor:pointer;font-weight:bold}a{color:#A44700}</style></head><body><main class="board"><header class="inbox"><h1>${esc(e.name)}</h1><strong>Subject:</strong> ${esc(e.subjectA)}<br/><strong>Preheader:</strong> ${esc(e.preheader)}</header>${body}<details class="notes"><summary>Purpose and timing</summary><p>${esc(e.strategy.goal)}</p><p>${esc(e.strategy.timing)}</p><p>Draft design. Sample dynamic data. <a href="../emails/${e.id}/">Open review and feedback</a>.</p></details></main></body></html>`;
}

function fixedStyles(mobile:boolean,dark:boolean) {
  return CSS.replace('@media only screen and (max-width:620px){', mobile ? '@media all{' : '@media not all{').replace('@media (prefers-color-scheme:dark){', dark ? '@media all{' : '@media not all{');
}
function embed(html:string) {
  return html.replace(/src="\.\.\/assets\/([^"?]+)"/g, (_,file) => {
    const mime=file.endsWith('.jpg')?'image/jpeg':'image/png';
    return `src="data:${mime};base64,${fs.readFileSync(path.join('assets',file)).toString('base64')}"`;
  });
}
function figma(html:string,mobile:boolean,dark:boolean) {
  return embed(html).replace(/<script[\s\S]*?<\/script>/gi,'')
    .replace(/<style>[\s\S]*?<\/style>/, () => `<style>${fixedStyles(mobile,dark)}</style>`)
    .replace('</head>',`<style>html,body{width:${mobile?375:680}px;min-width:${mobile?375:680}px;max-width:${mobile?375:680}px;margin:0!important}*{animation:none!important}</style></head>`);
}
async function main() {
  fs.rmSync(OUT, { recursive: true, force: true });
  for (const d of ["send", "preview", "preview-dark", "boards", "assets", "figma", "brand", "flows"]) fs.mkdirSync(path.join(OUT, d), { recursive: true });
  for(const file of ['puratek-logo-dark@2x.png','puratek-logo-light@2x.png',...ASSETS.map((a:any)=>a.file),...PRODUCT_FILES]){const dest=path.join(OUT,'assets',file);fs.mkdirSync(path.dirname(dest),{recursive:true});fs.copyFileSync(path.join('assets',file),dest);}
  fs.writeFileSync(path.join(OUT,'assets/manifest.json'),JSON.stringify(ASSETS,null,2));
  const previews=new Map<string,string>();
  const manifest = [];
  for (const e of ALL_EMAILS) {
    const send = await renderEmail(e, "send", HOSTED_LOGO);
    const prev = await renderEmail(e, "preview", "../assets/puratek-logo-dark@2x.png");
    previews.set(e.id,prev);
    const light=prev.replace('@media (prefers-color-scheme:dark){','@media not all{');
    fs.writeFileSync(path.join(OUT, "send", `${e.id}.html`), send);
    fs.writeFileSync(path.join(OUT, "preview", `${e.id}.html`), light);
    fs.writeFileSync(path.join(OUT, "preview-dark", `${e.id}.html`), prev.replace("@media (prefers-color-scheme:dark){", "@media all{"));
    fs.writeFileSync(path.join(OUT, "boards", `${e.id}.html`), board(e, embed(prev)));
    for(const mobile of [false,true]) for(const dark of [false,true]) fs.writeFileSync(path.join(OUT,'figma',`${e.id}-${mobile?'mobile':'desktop'}-${dark?'dark':'light'}.html`),figma(prev,mobile,dark));
    manifest.push({ ...e, audit: AUDIT[e.id] || null, auditDate:AUDIT[e.id]?'2026-09-28, before current revision':null, templateChanges: TEMPLATE_CHANGES, previewDate:PREVIEW_DATE, bytes: Buffer.byteLength(send), sendReady:false, launchRecommendation:LAUNCH_IDS.includes(e.id)?"proposed-start":"later-optional" });
  }
  fs.writeFileSync(path.join(OUT, "manifest.json"), JSON.stringify(manifest, null, 2));
  writeReview(OUT,ALL_EMAILS,previews,fixedStyles(true,false),fixedStyles(false,false));
  writeGuides(OUT);
  fs.writeFileSync(path.join(OUT,'flows/specification.json'),JSON.stringify({policy:POLICY,flows:FLOWS,opportunities:OPPORTUNITIES},null,2));
  fs.writeFileSync(path.join(OUT,'robots.txt'),'User-agent: *\nDisallow: /\n');
  writeFigmaGuide(OUT,ALL_EMAILS);
  writeDownloads(OUT);
  const hashes:Record<string,string>={};
  function hashDir(dir:string){for(const d of fs.readdirSync(dir,{withFileTypes:true})){const f=path.join(dir,d.name);if(d.isDirectory())hashDir(f);else hashes[path.relative(OUT,f)]=createHash('sha256').update(fs.readFileSync(f)).digest('hex');}}
  hashDir(OUT);fs.writeFileSync(path.join(OUT,'checksums.json'),JSON.stringify(hashes,null,2));
  const { validate } = await import('../scripts/validate');
  const result=validate(OUT);if(result.errors.length)throw new Error(result.errors.join('\n'));
  fs.rmSync('dist.previous',{recursive:true,force:true});
  if(fs.existsSync('dist'))fs.renameSync('dist','dist.previous');
  fs.renameSync(OUT,'dist');
  console.log(`Built ${manifest.length} templates, ${manifest.length*4} standalone Figma variants, dedicated client pages and researched guides. 7 proposed starters, 16 optional roles and 1 alternative. Draft send files only.`);
}
main().catch(error=>{console.error(error);process.exitCode=1});
