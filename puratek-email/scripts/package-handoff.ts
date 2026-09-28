import fs from 'node:fs';
import path from 'node:path';
import {createHash} from 'node:crypto';
import {spawnSync} from 'node:child_process';
import {validate} from './validate';
const check=validate();if(check.errors.length)throw new Error(check.errors.join('\n'));
for(const file of ['qa/render-report.json','qa/flow-scenarios.json'])if(!fs.existsSync(file))throw new Error(`Run QA first: ${file}`);
const render=JSON.parse(fs.readFileSync('qa/render-report.json','utf8'));if(render.errors.length)throw new Error('Render QA has failures');
const fingerprint=createHash('sha256').update(fs.readFileSync('dist/checksums.json')).digest('hex');if(render.revision!=='r7'||render.sourceChecksum!==fingerprint)throw new Error('Render QA is stale; rerun qa:render');
const root='handoff/puratek-email-mobile-review';fs.mkdirSync('handoff',{recursive:true});fs.rmSync(root,{recursive:true,force:true});fs.mkdirSync(root,{recursive:true});
for(const name of ['dist','src','scripts'])fs.cpSync(name,path.join(root,name),{recursive:true});
for(const name of ['package.json','package-lock.json','README.md','vercel.json','.gitignore'])fs.copyFileSync(name,path.join(root,name));
fs.cpSync('dist/assets',path.join(root,'assets'),{recursive:true});
for(const folder of ['automation-r5','automation-r6','products-r6'])fs.cpSync('assets/'+folder,path.join(root,'assets',folder),{recursive:true});
fs.mkdirSync(path.join(root,'config'));fs.copyFileSync('config/funnelkit.example.json',path.join(root,'config/funnelkit.example.json'));
const docs=fs.existsSync('../docs/06-US-Email-Restrictions.md')?'../docs':'docs';fs.cpSync(docs,path.join(root,'docs'),{recursive:true});
fs.mkdirSync(path.join(root,'qa/screenshots'),{recursive:true});fs.cpSync('qa/screenshots/r6',path.join(root,'qa/screenshots/r6'),{recursive:true});
for(const file of fs.readdirSync('qa'))if(fs.statSync(path.join('qa',file)).isFile())fs.copyFileSync(path.join('qa',file),path.join(root,'qa',file));
if(fs.existsSync('../skill'))fs.cpSync('../skill',path.join(root,'skill'),{recursive:true});
fs.writeFileSync(path.join(root,'START-HERE.md'),`# Puratek mobile review — revision 7

Use Node 22+, run npm ci and npm run serve, then open http://127.0.0.1:5173/.

Each email has dedicated mobile and desktop review views. Feedback notes are local; download or copy them to share. Seven templates are proposed starters; sixteen roles are optional. W2 has one alternative, never an additional send.

Read README.md and docs/04-Implementation-Handoff.md. docs/06–10 cover USA restrictions, scope, supplied assets, FunnelKit and Vercel. dist/send contains deployment drafts; dist/figma contains design exports only. No live automation is activated and no connector/mailbox verification is claimed.
`);
const files:string[]=[];function walk(dir:string){for(const e of fs.readdirSync(dir,{withFileTypes:true})){const p=path.join(dir,e.name);if(e.isDirectory())walk(p);else files.push(p)}}walk(root);
fs.writeFileSync(path.join(root,'inventory.json'),JSON.stringify(files.map(p=>({file:path.relative(root,p),bytes:fs.statSync(p).size,sha256:createHash('sha256').update(fs.readFileSync(p)).digest('hex')})),null,2));
const zip='puratek-email-mobile-review.zip';fs.rmSync(path.join('handoff',zip),{force:true});const result=spawnSync('/usr/bin/zip',['-qr',zip,'puratek-email-mobile-review'],{cwd:'handoff',stdio:'inherit'});if(result.status!==0)throw new Error('ZIP creation failed');console.log(path.resolve('handoff',zip));

const figmaRoot='handoff/puratek-figma-import';fs.rmSync(figmaRoot,{recursive:true,force:true});fs.cpSync('dist/figma',figmaRoot,{recursive:true});
const imports=JSON.parse(fs.readFileSync(path.join(figmaRoot,'import-manifest.json'),'utf8'));
fs.writeFileSync(path.join(figmaRoot,'index.html'),'<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Puratek Figma imports</title><body style="font:16px/1.6 Arial;max-width:720px;margin:40px auto;padding:20px"><h1>Puratek Figma imports</h1><p>Open one email below and import that page, not this directory.</p><p><a href="README.md">Instructions</a> · <a href="design-tokens.json">Tokens</a></p>'+imports.map((r:any)=>`<p><a href="${r.file}">${r.id.toUpperCase()} · ${r.size} · ${r.theme}</a></p>`).join('')+'</body></html>');
fs.rmSync('handoff/puratek-figma-import.zip',{force:true});const figmaZip=spawnSync('/usr/bin/zip',['-qr','puratek-figma-import.zip','puratek-figma-import'],{cwd:'handoff',stdio:'inherit'});if(figmaZip.status!==0)throw Error('Figma ZIP failed');console.log(path.resolve('handoff/puratek-figma-import.zip'));
