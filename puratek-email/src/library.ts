import type {Email} from './content';
export const INITIAL_IDS=['w1','w2','w3','c1','c2a','c2b','c3'];
export const COVERAGE:Record<string,string>={
 w3:'Product/category detail still needed',c3:'Approved service social proof still needed',c4:'Cart and restore action still needed',
 p1:'Fulfillment expectations still needed',p2:'Purchase-specific education still needed',p4:'Next-purchase role not yet covered',
 r1:'Purchase-based discovery not yet covered',r2:'Complementary-product role not yet covered',r3:'Reorder targeting not yet validated',x2:'Product discovery still needed',
};
export const libraryStatus=(e:Email)=>e.variantOf?'Reference alternative — W2 COA checklist recommended':`${INITIAL_IDS.includes(e.id)?'Proposed initial set':'Optional / later'}${COVERAGE[e.id]?' · '+COVERAGE[e.id]:''}`;
export const DOWNLOAD_FILE='puratek-all-emails.zip';
export function downloadPanel(base=''){
 return `<section data-download-panel class="download-panel"><h2>Download the complete email library</h2><p>24 designs · 48 Figma views · email HTML · logos, product photos and illustration resources. Puratek’s first-order offer stays at 10%.</p><a class="download download-all" href="${base}downloads/${DOWNLOAD_FILE}" download="${DOWNLOAD_FILE}">Download all emails + images (ZIP)</a><p class="review-note">Unzip, open START-HERE.html, then capture an individual Figma HTML page with your extension. Includes current drafts; coverage gaps remain labeled.</p></section>`;
}
