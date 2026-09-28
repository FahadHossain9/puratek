import fs from 'node:fs';
import path from 'node:path';
import {deflateRawSync} from 'node:zlib';
import {createHash} from 'node:crypto';
import {DOWNLOAD_FILE} from './library';
// Standard ZIP archive, built using Node only so the static deployment needs no system zip executable.
const crcTable=Array.from({length:256},(_,n)=>{let c=n;for(let i=0;i<8;i++)c=c&1?0xedb88320^(c>>>1):c>>>1;return c>>>0});
function crc32(data:Buffer){let c=0xffffffff;for(const b of data)c=crcTable[(c^b)&255]^(c>>>8);return (c^0xffffffff)>>>0}
function zip(files:Map<string,Buffer>){
 const local:Buffer[]=[],central:Buffer[]=[];let offset=0;
 for(const [name,data] of files){
  const n=Buffer.from(name),compressed=deflateRawSync(data,{level:6}),crc=crc32(data),h=Buffer.alloc(30);
  h.writeUInt32LE(0x04034b50);h.writeUInt16LE(20,4);h.writeUInt16LE(0x800,6);h.writeUInt16LE(8,8);h.writeUInt16LE(33,12);h.writeUInt32LE(crc,14);h.writeUInt32LE(compressed.length,18);h.writeUInt32LE(data.length,22);h.writeUInt16LE(n.length,26);
  const c=Buffer.alloc(46);c.writeUInt32LE(0x02014b50);c.writeUInt16LE(20,4);c.writeUInt16LE(20,6);c.writeUInt16LE(0x800,8);c.writeUInt16LE(8,10);c.writeUInt16LE(33,14);c.writeUInt32LE(crc,16);c.writeUInt32LE(compressed.length,20);c.writeUInt32LE(data.length,24);c.writeUInt16LE(n.length,28);c.writeUInt32LE(offset,42);
  local.push(h,n,compressed);central.push(c,n);offset+=h.length+n.length+compressed.length;
 }
 const directory=Buffer.concat(central),end=Buffer.alloc(22);end.writeUInt32LE(0x06054b50);end.writeUInt16LE(files.size,8);end.writeUInt16LE(files.size,10);end.writeUInt32LE(directory.length,12);end.writeUInt32LE(offset,16);return Buffer.concat([...local,directory,end]);
}
export function writeDownloads(out:string){
 const files=new Map<string,Buffer>();
 function addTree(dir:string,prefix:string){for(const e of fs.readdirSync(dir,{withFileTypes:true})){const source=path.join(dir,e.name),name=path.posix.join(prefix,e.name);if(e.isDirectory())addTree(source,name);else{let b=fs.readFileSync(source);if(e.name.endsWith('.html'))b=Buffer.from(b.toString().replace(/<section data-download-panel[\s\S]*?<\/section>/g,''));files.set(name,b)}}}
 for(const dir of ['figma','preview','send','assets','emails','guides','flows'])addTree(path.join(out,dir),dir);
 files.set('index.html',Buffer.from(fs.readFileSync(path.join(out,'index.html'),'utf8').replace(/<section data-download-panel[\s\S]*?<\/section>/g,'')));
 files.set('manifest.json',fs.readFileSync(path.join(out,'manifest.json')));
 const assets=JSON.parse(fs.readFileSync('assets/manifest.json','utf8'));
 for(const a of assets)files.set('image-resources/'+a.master,fs.readFileSync('assets/'+a.master));
 for(const file of ['automation-r5/prompts.json','automation-r6/prompts.json','products-r6/sources.json'])files.set('image-resources/'+file,fs.readFileSync('assets/'+file));
 for(const p of JSON.parse(fs.readFileSync('assets/products-r6/sources.json','utf8')))files.set('image-resources/products-r6/'+p.original,fs.readFileSync('assets/products-r6/'+p.original));
 for(const file of ['05-Current-Copy-Deck.md','09-FunnelKit-Implementation.md','13-Figma-Import-Handoff.md','17-Framework-Coverage.md','18-Feedback-and-Implementation-Reference.md','19-Working-Notes.md'])files.set('instructions/'+file,fs.readFileSync('docs/'+file));
 files.set('START-HERE.html',Buffer.from(`<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Puratek email library</title><body style="font:16px/1.7 Arial;max-width:760px;margin:40px auto;padding:24px;color:#242424"><h1>Puratek email library</h1><p>23 email roles plus one W2 alternative. The welcome offer is 10%.</p><ol><li>Unzip the complete folder.</li><li>Open <a href="figma/index.html">the Figma library</a> and choose one email and screen size.</li><li>Serve this folder locally and capture the individual page with your browser extension, or deploy it and import its URL.</li></ol><p>The ZIP itself is not a native Figma file. Embedded images and live text are prepared for HTML import; extension fidelity still needs verification.</p><p><a href="index.html">Review all emails and coverage status</a> · <a href="instructions/09-FunnelKit-Implementation.md">FunnelKit instructions</a></p><p><b>Folders:</b> figma = 48 import views; preview = responsive HTML; send = 24 unconfigured FunnelKit drafts; assets = images used by the HTML; image-resources = original artwork and product sources.</p><p>Cart rows in previews are samples. Configure the live cart tag, images and footer before using send files. No automations are activated. Current content gaps are recorded in the review library and coverage guide.</p></body></html>`));
 files.set('inventory.json',Buffer.from(JSON.stringify([...files].map(([file,data])=>({file,bytes:data.length,sha256:createHash('sha256').update(data).digest('hex')})),null,2)));
 fs.mkdirSync(path.join(out,'downloads'),{recursive:true});const archive=zip(files);fs.writeFileSync(path.join(out,'downloads',DOWNLOAD_FILE),archive);
 fs.writeFileSync(path.join(out,'downloads/manifest.json'),JSON.stringify({file:DOWNLOAD_FILE,bytes:archive.length,sha256:createHash('sha256').update(archive).digest('hex'),designs:24,figmaFiles:48,entries:files.size},null,2));
}
