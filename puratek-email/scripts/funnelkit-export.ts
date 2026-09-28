import fs from 'node:fs';
import path from 'node:path';
import {pathToFileURL} from 'node:url';
export type Config={templateIds:string[];confirmed:{copyAndAssetsReviewed:boolean;installedTagsVerified:boolean;imageUrlsVerified:boolean};placeholderMap:Record<string,string>;assetUrls:Record<string,string>};
const safe=(value:string)=>value.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
export function compile(config:Config,root='dist'){
 const errors:string[]=[],outputs:Record<string,string>={},metadata:any[]=[];
 const records=JSON.parse(fs.readFileSync(path.join(root,'manifest.json'),'utf8'));
 if(!config.templateIds?.length)errors.push('Select template IDs.');
 for(const key of ['copyAndAssetsReviewed','installedTagsVerified','imageUrlsVerified'] as const)if(config.confirmed?.[key]!==true)errors.push(`Missing confirmation: ${key}`);
 for(const id of config.templateIds||[]){
  const record=records.find((r:any)=>r.id===id);if(!record){errors.push('Unknown template: '+id);continue}
  let html=fs.readFileSync(path.join(root,'send',id+'.html'),'utf8');
  const markers=[...new Set(html.match(/%%[A-Z_]+%%|\{\{[^}]+\}\}/g)||[])];
  for(const marker of markers){const value=config.placeholderMap?.[marker];if(!value?.trim()||/%%[A-Z_]+%%|<script/i.test(value)){errors.push(`${id}: missing/invalid mapping for ${marker}`);continue}if(['%%PRIVACY_URL%%','%%PREFERENCES_URL%%'].includes(marker)&&!value.startsWith('{{')){try{const u=new URL(value);if(u.protocol!=='https:'||u.hostname!=='puratekpeptides.com')throw Error()}catch{errors.push(`${id}: invalid client URL for ${marker}`)}}if(marker==='%%CART_ITEMS%%'&&!/^\{\{[^{}<>]+\}\}$/.test(value)){errors.push(`${id}: cart items must be one complete installed merge tag`);continue}html=html.split(marker).join(marker==='%%CART_ITEMS%%'?value:safe(value));}
  html=html.replace(/src="https:\/\/puratekpeptides\.com\/wp-content\/uploads\/email\/([^"]+)"/g,(_,file)=>{const target=config.assetUrls?.[file];try{const u=new URL(target);if(u.protocol!=='https:'||u.hostname!=='puratekpeptides.com')throw Error()}catch{errors.push(`${id}: missing/invalid verified image URL for ${file}`);return _}return `src="${safe(target)}"`;});
  if(/%%[A-Z_]+%%/.test(html))errors.push(id+': unresolved placeholders');
  if(/<script|<iframe|data:image\//i.test(html))errors.push(id+': unsafe import artifact');
  if(Buffer.byteLength(html)>102*1024)errors.push(id+': clipping budget exceeded');
  outputs[id]=html;metadata.push({id,subject:record.subjectA,subjectAlternative:record.subjectB,preheader:record.preheader,editor:'Raw HTML',status:'Configured export; in-platform seed test still required'});
 }
 return {errors,outputs,metadata};
}
if(process.argv[1]&&import.meta.url===pathToFileURL(path.resolve(process.argv[1])).href){
 const argument=process.argv.indexOf('--config');if(argument<0||!process.argv[argument+1])throw new Error('Usage: npm run funnelkit:export -- --config config/funnelkit.local.json');
 const config=JSON.parse(fs.readFileSync(process.argv[argument+1],'utf8'));const result=compile(config);
 if(result.errors.length){console.error(result.errors.join('\n'));process.exitCode=1}else{const next='funnelkit-export.next';fs.rmSync(next,{recursive:true,force:true});fs.mkdirSync(next);for(const [id,html] of Object.entries(result.outputs))fs.writeFileSync(path.join(next,id+'.html'),html);fs.writeFileSync(path.join(next,'metadata.json'),JSON.stringify(result.metadata,null,2));fs.writeFileSync(path.join(next,'README.txt'),'Paste these files into FunnelKit Send Email → Raw HTML. Set subjects/preheaders from metadata.json. No automation was imported, activated or sent. Verify saved/delivered output before activation.');fs.rmSync('funnelkit-export',{recursive:true,force:true});fs.renameSync(next,'funnelkit-export');console.log(`Configured ${result.metadata.length} Raw HTML emails; no live actions performed.`)}
}
