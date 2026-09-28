// Historical r2–r5 transformations; no longer imported by build.tsx.
import type {Email} from './content';
export function revise(e:Email):Email {
 const updated={...e,body:[...e.body],strategy:{...e.strategy,addedText:[...e.strategy.addedText,'r2: Mobile email adaptation of supplied brand reference; system fonts, client artwork and shorter sections. Changed copy is draft for review.']}};
 if(e.id==='w1')updated.body=[{t:'p',text:'Welcome to Puratek Peptides. Review batch documentation before you order, and contact our team if you need help finding a COA.'},{t:'p',text:'Your first-order code is below.'}];
 if(e.id==='w2'){
  updated.headline='Start with the batch document';updated.preheader='Purity, identity and batch details, in one document.';
  updated.body=[{t:'p',text:'Each batch has a certificate of analysis from an independent lab. Review the document before you order.'},{t:'list',items:['Match the batch number to the label.','Check the reported purity and identity.','Look for the testing lab and date.']},{t:'p',text:'If your own testing shows a batch below 99% purity, contact us with your lab report for a full refund.'}];
  updated.proof=undefined;updated.strategy.addedText.push('Removed endotoxin/sterility list and duplicate proof strip; no manufactured COA is shown.');
 }
 if(e.id==='w3'){
  updated.headline='Dispatch, tracking, real support';updated.body=[{t:'p',text:'Orders placed before 1 PM PST, Monday to Friday, ship the same day from within the United States, with tracking.'},{t:'p',text:'Questions about your order or its documentation? Our team is available by phone and email.'}];updated.strategy.addedText.push('Removed generic storage instructions; use product-specific verified documentation instead.');
 }
 if(e.id==='c2a')updated.body=[{t:'p',text:'Review the batch documentation before you decide. If this is your first order, the code below takes 10% off your saved cart.'}];
 if(e.id==='c4'){updated.subjectA='A last note about your saved cart';updated.subjectB='Can we help with your saved cart?';updated.body=[{t:'p',text:'If a question about documentation, payment or shipping is holding you up, reply to this email. A person on our team can help.'}];updated.strategy.addedText.push('Removed unverified cart-release/expiry claim.');}
 if(['w1','w3','w4','c2a'].includes(e.id)){updated.proof=undefined;updated.strategy.addedText.push('r3: Removed repetitive proof strip to keep one focused message.');}
 if(e.id==='w1'){updated.headline='Welcome to Puratek';updated.body=[{t:'p',text:'Welcome to Puratek Peptides. Review the batch documentation before you order. If you need help finding a COA, our team is here.'}];}
 updated.strategy.addedText.push('r4: Reference-led single-column design; live text, focused sections and charcoal brand footer.');
 if(e.id==='b3')updated.body=updated.body.map(b=>b.t==='p'?{...b,text:b.text.replace('Use the code below','Use your code')}:b);
 updated.strategy.addedText.push('r5: Solid primary-orange hero, one unique illustration per email and a consistent neutral reading surface.');
 return updated;
}
