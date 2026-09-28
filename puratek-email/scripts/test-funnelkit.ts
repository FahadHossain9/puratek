import assert from 'node:assert/strict';
import fs from 'node:fs';
import {compile} from './funnelkit-export';
const config=JSON.parse(fs.readFileSync('config/funnelkit.example.json','utf8'));
assert(compile(config).errors.length>0,'Unconfigured example must be blocked');
const test=structuredClone(config);test.templateIds=['w1','c1'];test.confirmed={copyAndAssetsReviewed:true,installedTagsVerified:true,imageUrlsVerified:true};
// Synthetic parser fixtures only; these are not recommendations for installed picker syntax.
for(const key of Object.keys(test.placeholderMap))test.placeholderMap[key]='{{qa_fixture}}';
test.placeholderMap['%%PRIVACY_URL%%']='https://puratekpeptides.com/privacy-policy/';
test.placeholderMap['%%PREFERENCES_URL%%']='https://puratekpeptides.com/preferences/';
for(const key of Object.keys(test.assetUrls))test.assetUrls[key]='https://puratekpeptides.com/wp-content/uploads/'+key.replaceAll('/','-');
test.placeholderMap['%%CART_ITEMS%%']='{{qa_cart_fixture layout="rows" images="yes"}}';
const result=compile(test);assert.deepEqual(result.errors,[]);assert(!result.outputs.w1.includes('%%'));assert(!result.outputs.c1.includes('%%'));assert(result.outputs.w1.includes('{{qa_fixture}}'));assert(!/data:image|<script/.test(result.outputs.w1));
assert(result.outputs.c1.includes(test.placeholderMap['%%CART_ITEMS%%']),'Cart tag parameters must be preserved exactly');
assert(!result.outputs.c1.includes('products-r6/'),'Sample cart images must not leak into send');
const wrong=structuredClone(test);wrong.assetUrls['automation-r5/w1.jpg']='https://example.com/image.jpg';assert(compile(wrong).errors.some(x=>x.includes('image URL')));
const missing=structuredClone(test);missing.placeholderMap['%%CART_ITEMS%%']='';assert(compile(missing).errors.some(x=>x.includes('CART_ITEMS')));
const all=structuredClone(test);all.templateIds=JSON.parse(fs.readFileSync('dist/manifest.json','utf8')).map((e:any)=>e.id);const allResult=compile(all);assert.deepEqual(allResult.errors,[]);assert.equal(Object.keys(allResult.outputs).length,24);
fs.writeFileSync('qa/funnelkit-export-tests.json',JSON.stringify({passed:8,scope:'Mapping/export utility with synthetic fixtures; not installed FunnelKit validation'},null,2));console.log('8 FunnelKit export scenarios passed, including all 24 designs');
