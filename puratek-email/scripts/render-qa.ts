import {chromium} from 'playwright';
import fs from 'node:fs';
import {tmpdir} from 'node:os';
import path from 'node:path';
import {once} from 'node:events';
import {createHash} from 'node:crypto';
import {startServer} from './serve';
const server=startServer(0);await once(server,'listening');const address=server.address();const base=`http://127.0.0.1:${typeof address==='object'&&address?address.port:0}`;
let browser;
const errors:string[]=[],results:any[]=[],reviewResults:any[]=[];
fs.mkdirSync('qa/screenshots/r6',{recursive:true});
const manifest=JSON.parse(fs.readFileSync('dist/manifest.json','utf8'));
try{
 browser=await chromium.launch({headless:true,...(process.env.QA_BROWSER_CHANNEL?{channel:process.env.QA_BROWSER_CHANNEL}:{})});
 const page=await browser.newPage({deviceScaleFactor:1,acceptDownloads:true});
 page.on('pageerror',error=>errors.push('Page JS error: '+error.message));
 for(const e of manifest)for(const width of [320,375,430,680])for(const dark of [false,true]){
  await page.emulateMedia({colorScheme:dark?'dark':'light'});await page.setViewportSize({width,height:900});const response=await page.goto(`${base}/preview/${e.id}.html`,{waitUntil:'load'});
  const check=await page.evaluate(()=>{
   const button=document.querySelector('.btn')!,email=document.querySelector('[data-email]')!,hero=document.querySelector('[data-section="hero"] td')!;
   return {overflow:document.documentElement.scrollWidth>innerWidth+1,images:[...document.images].every(i=>i.complete&&i.naturalWidth>0),ctaHeight:button.getBoundingClientRect().height,ctaRadius:getComputedStyle(button).borderRadius,emailWidth:email.getBoundingClientRect().width,headingSize:getComputedStyle(document.querySelector('.h1')!).fontSize,bodyBackground:getComputedStyle(document.body).backgroundColor,heroBackground:getComputedStyle(hero).backgroundColor,heroInk:getComputedStyle(document.querySelector('.h1')!).color,artCount:document.querySelectorAll('[data-section="hero-art"] img[data-editorial-art]').length,gradient:getComputedStyle(hero).backgroundImage,layout:email.getAttribute('data-layout'),primaryActions:document.querySelectorAll('.btn').length,cardBorders:[...document.querySelectorAll('.content-card')].map(el=>getComputedStyle(el).borderBottomColor),footerColor:getComputedStyle(document.querySelector('.brand-footer')!).backgroundColor,footer:document.body.innerText.includes('21 years of age')&&!!document.querySelector('a[href="#unsubscribe-preview"]'),sections:[...document.querySelectorAll('[data-section]')].map(el=>({name:el.getAttribute('data-section'),height:Math.round(el.getBoundingClientRect().height)})),height:Math.ceil(document.body.getBoundingClientRect().height)};
  });
  const label=`${e.id}-${width}-${dark?'dark':'light'}`;results.push({id:e.id,width,dark,http:response?.status(),...check});
  if(response?.status()!==200||check.overflow||!check.images||!check.footer||!check.layout||check.primaryActions!==(Number(!!e.heroAction)+e.sections.filter((s:any)=>s.cta).length)||check.footerColor!=='rgb(19, 23, 42)'||check.heroBackground!=='rgb(247, 147, 30)'||check.heroInk!=='rgb(19, 23, 42)'||check.artCount!==1||check.gradient!=='none'||check.ctaHeight<48||check.ctaRadius!=='100px'||check.bodyBackground!=='rgb(242, 242, 242)')errors.push(label+': '+JSON.stringify(check));
  if(width===375)await page.screenshot({path:`qa/screenshots/r6/${label}.png`,fullPage:true});
 }
 for(const e of manifest)for(const width of [375,1280]){
  await page.setViewportSize({width,height:900});const target=width===1280?'desktop.html':'';const r=await page.goto(`${base}/emails/${e.id}/${target}`,{waitUntil:'load'});
  const check=await page.evaluate(()=>{
   const m=document.querySelector('.is-mobile [data-email]')!,d=document.querySelector('.is-desktop [data-email]')!;
   const mr=m.getBoundingClientRect(),dr=d.getBoundingClientRect(),scroll=document.querySelector('.comparison-scroll')!;
   return {overflow:document.documentElement.scrollWidth>innerWidth+1,mobileWidth:mr.width,desktopWidth:dr.width,aligned:Math.abs(mr.top-dr.top)<1,sideBySide:dr.left>=mr.right,mobileHeading:getComputedStyle(m.querySelector('.h1')!).fontSize,desktopHeading:getComputedStyle(d.querySelector('.h1')!).fontSize,stages:document.querySelectorAll('.review-stage').length,tabs:!!document.querySelector('.view-switch'),images:[...document.images].every(i=>i.naturalWidth>0),notes:!!document.querySelector('#notes'),canScroll:scroll.scrollWidth>scroll.clientWidth};
  });reviewResults.push({id:e.id,width,http:r?.status(),...check});
  if(r?.status()!==200||check.overflow||check.mobileWidth!==375||check.desktopWidth!==600||!check.aligned||!check.sideBySide||check.mobileHeading!=='28px'||check.desktopHeading!=='34px'||check.stages!==2||check.tabs||!check.images||!check.notes||(width===375&&!check.canScroll))errors.push('Comparison '+e.id+': '+JSON.stringify(check));
  if(e.id==='w2'&&!await page.evaluate(()=>getComputedStyle(document.querySelector('.is-mobile .comparison-cell')!).display==='block'&&getComputedStyle(document.querySelector('.is-desktop .comparison-cell')!).display==='table-cell'))errors.push('Comparison cell layouts '+width);
  if(width===1280&&['w1','w2','c1'].includes(e.id))await page.screenshot({path:`qa/screenshots/r6/compare-${e.id}.png`,fullPage:false});
  if(width===375){await page.locator('.comparison-scroll').evaluate(el=>el.scrollLeft=el.scrollWidth);if(!await page.locator('.is-desktop').isVisible())errors.push('Desktop inaccessible in narrow comparison '+e.id);}
 }
 await page.setViewportSize({width:680,height:900});await page.goto(`${base}/preview/w1.html`);
 if(!await page.evaluate(()=>{const art=document.querySelector('[data-section="hero-art"]')!,hero=document.querySelector('[data-section="hero"]')!;return art.getBoundingClientRect().top>=hero.getBoundingClientRect().bottom-1&&getComputedStyle(art).backgroundColor==='rgb(247, 147, 30)'}))errors.push('Hero composition mismatch');
 for(const width of [375,1280]){
  await page.setViewportSize({width,height:900});await page.goto(base);
  const visible=await page.locator('.email-link:visible').count();const links=await page.locator('.email-link').count();
  if(visible!==24||links!==24)errors.push('Index scope count mismatch');
  if(await page.locator('iframe').count())errors.push('Homepage has iframe');
  if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1))errors.push('Index overflow');
  await page.screenshot({path:`qa/screenshots/r6/index-${width}.png`,fullPage:true});
 }
 await page.locator('.email-link').first().click();if(!page.url().endsWith('/emails/w1/'))errors.push('Index link did not open dedicated page');
 await page.getByText('Leave feedback on this email',{exact:true}).click();await page.locator('#reviewer').fill('QA reviewer');await page.locator('#decision').selectOption('Changes requested');await page.locator('#notes').fill('QA test: shorten the introduction.');
 await page.reload();await page.getByText('Leave feedback on this email',{exact:true}).click();if(await page.locator('#notes').inputValue()!=='QA test: shorten the introduction.')errors.push('Feedback persistence failed');
 const downloadPromise=page.waitForEvent('download');await page.locator('#download-feedback').click();const download=await downloadPromise;await download.saveAs('qa/feedback-sample.txt');const note=fs.readFileSync('qa/feedback-sample.txt','utf8');if(!note.includes('w1/')||!note.includes('Changes requested')||!note.includes('QA test:'))errors.push('Feedback export incomplete');
 await page.goto(`${base}/emails/w1/desktop.html`);await page.getByText('Leave feedback on this email',{exact:true}).click();if(await page.locator('#notes').inputValue()!=='QA test: shorten the introduction.')errors.push('Feedback lost on legacy comparison URL');
 for(const slug of ['feedback','restrictions','scope','brand','funnelkit','sharing','layout','references','figma','orange','campaign','campaign-map','coverage']){await page.setViewportSize({width:375,height:900});const r=await page.goto(`${base}/guides/${slug}/`);if(r?.status()!==200||await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1))errors.push('Guide failure '+slug);}
 for(const e of manifest)for(const mobile of [true,false]){
  const width=mobile?375:680;await page.setViewportSize({width,height:900});await page.goto(`${base}/figma/${e.id}-${mobile?'mobile':'desktop'}.html`);
  const good=await page.evaluate(({width,mobile})=>document.documentElement.scrollWidth===width&&[...document.images].every(i=>i.naturalWidth>0)&&!document.querySelector('script,iframe')&&getComputedStyle(document.querySelector('.h1')!).fontSize===(mobile?'28px':'34px'),{width,mobile});if(!good)errors.push('Figma '+e.id+'/'+mobile);
 }
 // Verify campaign sections, the comparison's mobile stack, and exact team copy survive rendering.
 for(const id of ['w1','w2','w2-alt','w3']){
  await page.setViewportSize({width:375,height:900});await page.goto(`${base}/preview/${id}.html`);
  const record=manifest.find((e:any)=>e.id===id);
  for(const section of record.sections)if(!await page.locator(`[data-section="${section.id}"]`).count())errors.push(`Missing campaign section ${id}/${section.id}`);
 }
 await page.goto(`${base}/preview/w1.html`);if(await page.locator('[data-product]').count()!==4)errors.push('W1 must contain four requested products');
 await page.goto(`${base}/preview/w2.html`);if(!await page.evaluate(()=>{const c=document.querySelectorAll('.comparison-cell');return c.length===2&&c[1].getBoundingClientRect().top>=c[0].getBoundingClientRect().bottom-1}))errors.push('Mobile comparison not stacked');
 await page.goto(`${base}/preview/w2-alt.html`);if(await page.locator('[data-comparison]').count())errors.push('W2 alternative still has comparison');
 await page.goto(`${base}/figma/`);if(await page.locator('a[href$="-dark.html"]').count()!==0)errors.push('Figma directory still offers separate dark variants');
 await page.setViewportSize({width:320,height:900});await page.goto(`${base}/preview/c2a.html`);await page.evaluate(()=>{document.querySelector('.coupon')!.textContent='CART10-ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';const row=document.querySelector('table[aria-label="Items in your cart"] tbody tr')!;row.querySelector('td')!.textContent='A long catalog item name ABCDEFGHIJKLMNOPQRSTUVWXYZ';for(let i=0;i<8;i++)row.parentElement!.append(row.cloneNode(true));});
 if(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth+1))errors.push('Long cart/coupon overflow');await page.screenshot({path:'qa/screenshots/r6/stress-cart-320.png',fullPage:true});
 const archiveInfo=JSON.parse(fs.readFileSync('dist/downloads/manifest.json','utf8'));
 for(const target of ['','figma/','emails/w1/']){
  await page.goto(base+'/'+target);const button=page.getByRole('link',{name:'Download all emails + images (ZIP)',exact:true});
  if(!await button.isVisible()||new URL(await button.getAttribute('href')||'',page.url()).pathname!=='/downloads/puratek-all-emails.zip')errors.push('Missing ZIP button '+target);
 }
 await page.goto(base);const archivePromise=page.waitForEvent('download');await page.getByRole('link',{name:'Download all emails + images (ZIP)',exact:true}).click();const archiveDownload=await archivePromise;
 const tempDir=fs.mkdtempSync(path.join(tmpdir(),'puratek-download-')),tempFile=path.join(tempDir,'library.zip');await archiveDownload.saveAs(tempFile);
 if(archiveDownload.suggestedFilename()!==archiveInfo.file||createHash('sha256').update(fs.readFileSync(tempFile)).digest('hex')!==archiveInfo.sha256)errors.push('ZIP download differs from current build');fs.rmSync(tempDir,{recursive:true,force:true});
 await page.route('**/assets/**',route=>route.abort());await page.goto(`${base}/preview/w1.html`);await page.addStyleTag({content:'.email-hero{background-image:none!important}'});
 if(!await page.locator('.btn').first().isVisible()||!await page.getByText('Puratek sells only to customers 21 years of age or older.').isVisible())errors.push('Images/gradient disabled fallback failed');await page.screenshot({path:'qa/screenshots/r6/images-gradient-disabled.png',fullPage:true});
 const report={revision:'r7',scope:'Local headless Chrome; no installed FunnelKit, email mailbox client or Figma connector verification',sourceChecksum:createHash('sha256').update(fs.readFileSync('dist/checksums.json')).digest('hex'),emailCases:results.length,reviewCases:reviewResults.length,mobileFigmaCases:manifest.length,desktopFigmaCases:manifest.length,zipDownload:'Homepage click saved complete archive; checksum matches build; buttons verified on homepage, Figma library and W1',feedback:'Local persistence and downloadable text tested; no server submission',errors,reviewResults,results};
 fs.writeFileSync('qa/render-report.json',JSON.stringify(report,null,2));console.log(`${results.length} email cases, ${reviewResults.length} dedicated pages, ${manifest.length*2} Figma cases; ${errors.length} failures`);if(errors.length){console.error(errors.join('\n'));process.exitCode=1;}
}finally{await browser?.close();server.close()}
