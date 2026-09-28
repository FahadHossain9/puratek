import fs from 'node:fs';
import {createHash} from 'node:crypto';
function jpegSize(data:Buffer){
 let offset=2;while(offset<data.length){if(data[offset++]!==0xff)continue;let marker=data[offset++];while(marker===0xff)marker=data[offset++];if(marker===0xd9||marker===0xda)break;const length=data.readUInt16BE(offset);if([0xc0,0xc1,0xc2,0xc3,0xc5,0xc6,0xc7,0xc9,0xca,0xcb,0xcd,0xce,0xcf].includes(marker))return {width:data.readUInt16BE(offset+5),height:data.readUInt16BE(offset+3)};offset+=length;}throw Error('JPEG dimensions missing');
}
const records=JSON.parse(fs.readFileSync('assets/manifest.json','utf8'));
for(const record of records){
 fs.readFileSync('assets/'+record.master);
 const data=fs.readFileSync('assets/'+record.file);
 Object.assign(record,jpegSize(data),{bytes:data.length,sha256:createHash('sha256').update(data).digest('hex')});
}
if(records.length!==23||new Set(records.map((r:any)=>r.sha256)).size!==23)throw Error('Expected 23 distinct active editorial images');
fs.writeFileSync('assets/manifest.json',JSON.stringify(records,null,2));
const config=JSON.parse(fs.readFileSync('config/funnelkit.example.json','utf8'));
const products=JSON.parse(fs.readFileSync('assets/products-r6/sources.json','utf8')).map((p:any)=>`products-r6/${p.id}-email.jpg`);
const files=['puratek-logo-dark@2x.png','puratek-logo-light@2x.png',...records.map((r:any)=>r.file),...products];
config.assetUrls=Object.fromEntries(files.map(file=>[file,config.assetUrls[file]||'']));
fs.writeFileSync('config/funnelkit.example.json',JSON.stringify(config,null,2));
console.log('Verified 23 active editorial images; preserved asset mapping values and campaign selections.');
