import type {Email} from './content';
export type Layout={kind:'offer'|'document'|'service'|'cart'|'editorial';label:string;bodyLabel:string;offerFirst?:boolean};
export const LAYOUTS:Record<string,Layout>={
 w1:{kind:'offer',label:'A welcome from our team',bodyLabel:'Documentation before you order',offerFirst:true},
 'w2-alt':{kind:'document',label:'Read the evidence',bodyLabel:'COA checklist'},
 w2:{kind:'document',label:'The documentation',bodyLabel:'Three things to check'},
 w3:{kind:'service',label:'Dispatch & support',bodyLabel:'Know what happens next'},
 w4:{kind:'offer',label:'Your first-order code',bodyLabel:'At your own pace',offerFirst:true},
 c1:{kind:'cart',label:'Your saved cart',bodyLabel:'Your selected items'},
 c2a:{kind:'cart',label:'Your first order',bodyLabel:'Review your cart'},
 c2b:{kind:'cart',label:'Welcome back',bodyLabel:'Pick up where you left off'},
 c3:{kind:'cart',label:'Before you decide',bodyLabel:'Your questions, answered'},
 c4:{kind:'service',label:'A note from our team',bodyLabel:'We are here to help'},
 b1:{kind:'document',label:'The Puratek journal',bodyLabel:'Read the document'},
 b2:{kind:'editorial',label:'Behind your order',bodyLabel:'Dispatch, explained'},
 b3:{kind:'offer',label:'A Puratek offer',bodyLabel:'Review the details',offerFirst:true},
 p1:{kind:'service',label:'Order support',bodyLabel:'Keep in touch'},
 p2:{kind:'document',label:'Your batch documents',bodyLabel:'A simple reference'},
 p3:{kind:'service',label:'Your ordering experience',bodyLabel:'We would like to hear from you'},
 p4:{kind:'service',label:'Documentation support',bodyLabel:'Start with your order reference'},
 r1:{kind:'document',label:'Before your next order',bodyLabel:'Start with the document'},
 r2:{kind:'editorial',label:'Explore Puratek',bodyLabel:'Browse with the details at hand'},
 r3:{kind:'editorial',label:'When you are ready',bodyLabel:'Review the current details'},
 x1:{kind:'document',label:'A note from Puratek',bodyLabel:'Documentation comes first'},
 x2:{kind:'service',label:'Your questions are welcome',bodyLabel:'A direct line to our team'},
 x3:{kind:'service',label:'Your preferences',bodyLabel:'The choice is yours'},
 h1:{kind:'service',label:'Personal order support',bodyLabel:'Let us help with the details'},
};
export const layoutFor=(e:Email)=>{const layout=LAYOUTS[e.id];if(!layout)throw Error('Missing layout: '+e.id);return layout};
