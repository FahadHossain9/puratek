import fs from 'node:fs';
import path from 'node:path';
import { pathToFileURL } from 'node:url';
import {LAYOUTS} from '../src/layout';
import {T,DARK} from '../src/design-tokens';
export const DISCLAIMER = 'All products sold by Puratek are strictly for laboratory and research use only. They are not intended for human or animal consumption, diagnostic, therapeutic, or clinical use. These statements have not been evaluated by the U.S. Food and Drug Administration.';
const allowed = new Set(['%%COUPON_CODE%%','%%COUPON_EXPIRY%%','%%CART_COUPON_CODE%%','%%CART_COUPON_EXPIRY%%','%%CART_LINK%%','%%CART_ITEMS%%','%%SALE_CODE%%','%%SALE_END_DATE%%','%%PRIVACY_URL%%','%%PREFERENCES_URL%%','{{business_address}}','{{unsubscribe_link}}']);
const banned = /\b(GLP|incretin|sema|tirz|reta|weight|fat|appetite|metabolism|glucose|insulin|energy|recovery|healing|anti-aging|skin|tan|libido|muscle|sleep|cognition|longevity|results|transformation|dose|dosing|protocol|cycle|stack|inject|reconstitute|mix|units|syringe|pen|weekly|beginner|treat|cure|therapy|patient|FDA-approved|last chance|act now|miracle|fentanyl|pharmacy|Rx|prescription|pills|meds)\b/i;
export function validate(root='dist',release=false){
 const errors:string[]=[],warnings:string[]=[];
 const fail=(test:boolean,message:string)=>{if(!test)errors.push(message)};
 const manifest=JSON.parse(fs.readFileSync(path.join(root,'manifest.json'),'utf8'));
 fail(manifest.length===24,'Expected 23 roles plus one W2 alternative');fail(new Set(manifest.map((e:any)=>e.id)).size===manifest.length,'Duplicate template ID');
 const artworks=manifest.map((e:any)=>e.art?.file);fail(artworks.every((file:any)=>/^automation-r[56]\//.test(file||'')),'Every email needs registered campaign artwork');fail(new Set(artworks).size===23,'Artwork must be unique per email');
 for(const e of manifest){
  if(e.id!=='w1')fail(!JSON.stringify(e.sections).includes('PUR-3R'),`${e.id}: PUR-3R outside client-requested W1`);
  const send=fs.readFileSync(path.join(root,'send',`${e.id}.html`),'utf8');
  const text=send.replace(/<style[\s\S]*?<\/style>/gi,'').replace(/<[^>]+>/g,' ').replace(/\s+/g,' ').trim();
  fail(text.includes(DISCLAIMER),`${e.id}: missing exact disclaimer`);
  fail(text.includes('21 years of age'),`${e.id}: missing age statement`);
  fail(send.includes('{{unsubscribe_link}}'),`${e.id}: missing unsubscribe`);
  fail(!/<script\b|<iframe\b|display:\s*(?:grid|flex)|fonts\.googleapis/i.test(send),`${e.id}: script in send output`);
  fail(!/data:image\//.test(send),`${e.id}: embedded raster in send output`);
  fail(Buffer.byteLength(send)<102*1024,`${e.id}: HTML clipping risk`);
  fail(!/__CART_|__ADDRESS__/.test(send),`${e.id}: internal markers leaked`);
  const content = JSON.stringify([e.subjectA,e.subjectB,e.preheader,e.eyebrow,e.headline,e.body,e.heroCopy,e.heroAction,e.sections,e.cta.label,e.secondary?.label,e.afterCta,e.art?.alt,LAYOUTS[e.id]]);
  fail(!banned.test(content),`${e.id}: restricted term ${content.match(banned)?.[0]}`);
  fail(!/\b(PUR-1S|PUR-2T|Cagrilintide|TESA|Melanotan-1|BW|HOS BW)\b/i.test(content),`${e.id}: prohibited promotion`);
  if(!e.sections&&LAYOUTS[e.id]?.offerFirst)fail(!JSON.stringify(e.body).includes('code below'),`${e.id}: offer-first copy points below`);
  if(e.cart)fail((send.match(/%%CART_ITEMS%%/g)||[]).length===1,`${e.id}: invalid cart replacement`);
  for(const token of send.match(/%%[A-Z_]+%%|\{\{[^}]+\}\}/g)||[])fail(allowed.has(token),`${e.id}: unknown token ${token}`);
  for(const url of send.matchAll(/(?:href|src)="([^"]+)"/g)){
   if(url[1].startsWith('https://')&&!url[1].startsWith('https://fonts.googleapis.com/'))fail(new URL(url[1].replace(/&amp;/g,'&')).hostname==='puratekpeptides.com',`${e.id}: off-domain URL`);
  }
  for(const dir of ['preview','preview-dark']){
   const html=fs.readFileSync(path.join(root,dir,`${e.id}.html`),'utf8');
   fail(!/%%[A-Z_]+%%|\{\{/.test(html),`${dir}/${e.id}: unresolved sample tags`);
   for(const m of html.matchAll(/src="\.\.\/assets\/([^"]+)"/g))fail(fs.existsSync(path.join(root,'assets',m[1])),`${e.id}: missing asset ${m[1]}`);
  }
  for(const size of ['desktop','mobile'])for(const theme of ['light','dark']){
   const html=fs.readFileSync(path.join(root,'figma',`${e.id}-${size}-${theme}.html`),'utf8');
   fail(!/<script|<iframe/i.test(html),`${e.id}: interactive Figma export`);
   fail(html.includes('data:image/'),`${e.id}: assets not embedded`);
  }
  if(release){fail(!/%%[A-Z_]+%%/.test(send),`${e.id}: unresolved deployment placeholders`);fail(e.sendReady===true,`${e.id}: production sign-off missing`);}
 }
 const luminance=(hex:string)=>{const [r,g,b]=hex.slice(1).match(/../g)!.map(h=>parseInt(h,16)/255).map(v=>v<=0.04045?v/12.92:((v+0.055)/1.055)**2.4);return .2126*r+.7152*g+.0722*b;};
 for(const [label,fg,bg] of [['CTA',T.buttonText,T.button],['Body',T.body,T.white],['Muted',T.muted,T.page],['Link',T.orangeText,T.white],['Offer',T.muted,T.cream],['Hero',T.heading,T.orange],['Brand footer','#DDDDDD',T.navy],['Dark body',DARK.body,DARK.card],['Dark footer',DARK.muted,DARK.page],['Dark link',DARK.orange,DARK.card]]){const a=luminance(fg),b=luminance(bg),ratio=(Math.max(a,b)+.05)/(Math.min(a,b)+.05);fail(ratio>=4.5,`${label}: contrast ${ratio.toFixed(2)}`);}
 fail(!fs.existsSync(path.join(root,'assets/generated')),'Rejected generated artwork copied into output');
 for(const e of manifest){const review=fs.readFileSync(path.join(root,'emails',e.id,'index.html'),'utf8');fail(!/<iframe\b/i.test(review),`${e.id}: iframe in review`);fail(review.includes('Download notes'),`${e.id}: missing review controls`);}
 const hub=fs.readFileSync(path.join(root,'index.html'),'utf8');fail(/<!doctype html>/i.test(hub)&&/charset="utf-8"/.test(hub)&&/name="viewport"/.test(hub),'Hub document metadata missing');fail(!/<iframe\b/i.test(hub),'Homepage is still a dashboard');
 warnings.push('W1 contains client-directed PUR-3R imagery/copy; prior internal restriction superseded for this draft only. Product/provider review is unresolved.');
 warnings.push('Local checks do not verify the Figma connector, installed FunnelKit tags, mailbox clients or provider delivery.');
 if(!release)warnings.push('All send files are deployment drafts; address, privacy/preferences URLs, hosted assets, platform tags and approvals remain required.');
 return {mode:release?'release':'local',templates:manifest.length,errors,warnings};
}
if(process.argv[1]&&import.meta.url===pathToFileURL(path.resolve(process.argv[1])).href){const report=validate('dist',process.argv.includes('--release'));fs.mkdirSync('qa',{recursive:true});fs.writeFileSync(`qa/${report.mode}-validation.json`,JSON.stringify(report,null,2));console.log(JSON.stringify(report,null,2));if(report.errors.length)process.exitCode=1;}
