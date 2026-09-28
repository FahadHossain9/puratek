import fs from 'node:fs';
import path from 'node:path';
import type {Email} from './content';
import {documentPage,nav} from './review';
import {downloadPanel,libraryStatus} from './library';
import {T,R,FONT,DIMENSIONS} from './design-tokens';
export function writeFigmaGuide(out:string,emails:Email[]){
 const escape=(s:string)=>s.replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]!));
 const records=emails.flatMap(e=>['mobile','desktop'].map(size=>{const file=`${e.id}-${size}.html`,html=fs.readFileSync(path.join(out,'figma',file),'utf8');return {id:e.id,variantOf:e.variantOf||null,name:e.name,size,theme:'brand',file,viewportWidth:size==='mobile'?375:680,emailWidth:size==='mobile'?375:600,sections:[...html.matchAll(/data-section="([^"]+)"/g)].map(m=>m[1]),rasterArtwork:e.art?.file||null};}));
 fs.writeFileSync(path.join(out,'figma','import-manifest.json'),JSON.stringify(records,null,2));
 fs.writeFileSync(path.join(out,'figma','design-tokens.json'),JSON.stringify({colors:T,radii:R,fonts:FONT,dimensions:DIMENSIONS},null,2));
 fs.copyFileSync('docs/13-Figma-Import-Handoff.md',path.join(out,'figma','README.md'));
 fs.writeFileSync(path.join(out,'figma','index.html'),documentPage('Figma import library',`<main class="shell">${nav('../')}<h1>Figma import library</h1>${downloadPanel('../')}<p>One email per file. Live text, embedded images, no review controls. Select mobile or desktop, then import that individual page with your extension.</p><p><a href="../guides/figma/">Import instructions and limitations</a> · <a href="design-tokens.json" download>Design tokens</a> · <a href="import-manifest.json" download>Import manifest</a></p>${emails.map(e=>`<section class="guide"><h2 style="margin-top:0">${escape(e.name)}</h2><p>${escape(libraryStatus(e))}</p><p>Mobile · 375px</p><div class="resources"><a href="${e.id}-mobile.html">Open email</a><a href="${e.id}-mobile.html" download>Download HTML</a></div><p>Desktop · 600px email in a 680px frame</p><div class="resources"><a href="${e.id}-desktop.html">Open email</a><a href="${e.id}-desktop.html" download>Download HTML</a></div></section>`).join('')}</main>`,'../'));
}
