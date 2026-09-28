export type Flow = { id:string; name:string; priority:number; trigger:string; templates:string[]; schedule:string[]; exits:string[]; dependencies:string[]; status:'specification-only'; reentryDays:number; };
const globalExits = ['No recorded consent','Non-US or unknown source','Unsubscribed, hard bounced or complained'];
export const FLOWS: Flow[] = [
 {id:'welcome',name:'Welcome',priority:2,trigger:'account_created_with_opt_in',templates:['w1','w2','w3'],schedule:['0h','48h: W2 COA checklist recommended; W2-alt reference only','96h'],exits:[...globalExits,'First order placed'],dependencies:['Verify WELCOME10 once; preserve its identity and eligibility across W1–W3; W4 is optional','Actual account and consent events'],status:'specification-only',reentryDays:36500},
 {id:'cart',name:'Abandoned cart',priority:1,trigger:'eligible_cart_abandoned',templates:['c1','c2a','c2b','c3'],schedule:['1h','24h: orders=0 → c2a; otherwise c2b','48h'],exits:[...globalExits,'Order placed','Cart empty or recovered','Cart > $500 or quantity ≥10 → h1'],dependencies:['Captured cart identity','Verified restore tag','Recheck order count at C2','First-order coupon 10%, single use, 7 days, no stacking'],status:'specification-only',reentryDays:7},
 {id:'support',name:'High-value cart support',priority:1,trigger:'cart_total_gt_500_or_quantity_gte_10',templates:['h1'],schedule:['1h'],exits:[...globalExits,'Order placed','Cart empty or recovered'],dependencies:['Monitored replies','Do not enroll in standard cart flow'],status:'specification-only',reentryDays:7},
 {id:'post-purchase',name:'Post-purchase',priority:3,trigger:'paid_order_then_dispatch_and_delivery',templates:['p1','p2','p3','p4'],schedule:['Paid +2h','Dispatch +1d','Verified delivery +7d','Verified delivery +21d'],exits:[...globalExits,'Refund, dispute or open support issue','Newer order supersedes sequence'],dependencies:['Verified payment, dispatch and delivery events','Avoid duplicate transactional notifications','Approve new copy'],status:'specification-only',reentryDays:1},
 {id:'repeat',name:'Repeat engagement',priority:4,trigger:'consented_engaged_purchaser',templates:['r1','r2','r3'],schedule:['Last order +30d','Last order +60d','Last order +90d'],exits:[...globalExits,'New order','Not engaged in last 90 days','Open support issue'],dependencies:['Reliable engagement and order timestamps','No assumed consumption or replenishment schedule','Approve new copy'],status:'specification-only',reentryDays:90},
 {id:'winback',name:'Win-back',priority:4,trigger:'approved_inactive_purchaser_segment',templates:['x1','x2','x3'],schedule:['Last meaningful engagement +90d','X1 +7d','X1 +14d'],exits:[...globalExits,'Meaningful engagement or order','Final nonresponse → sunset'],dependencies:['Separate audience approval','Sunset nonresponders after X3','Approve new copy'],status:'specification-only',reentryDays:365},
 {id:'broadcast',name:'Broadcast options',priority:5,trigger:'approved_editorial_schedule',templates:['b1','b2','b3'],schedule:['One option per week initially'],exits:[...globalExits,'Not engaged in last 90 days','Active welcome/cart flow','Frequency cap'],dependencies:['Choose one option, not all three','B3 sale/coupon approval','Tier monitoring and provider readiness'],status:'specification-only',reentryDays:7},
];
export const OPPORTUNITIES = [
 ['Optional W4 / C4','Only engaged eligible contacts; valid welcome offer for W4, open-cart/support relevance for C4'],['Consent confirmation','Recorded opt-in and verified confirmation endpoint'],['Preferences','Real list-preferences endpoint; not a profile page'],['Re-permission / sunset','Prior permission, audience approval and suppression integration'],['Failed payment','Verified gateway events; no automatic charge retry'],['Delivery exception','Fulfillment event integration and monitored support'],['Service review request','Verified delivery and approved review destination'],['Neutral back-in-stock','Product-specific consent, stock events and product-tier gate'],['Browse abandonment','Identifiable consented browsing events; unsupported until verified'],['Holiday dispatch','Approved dates, time zone and actual service hours'],['Subject testing','Installed split-path support and adequate sample size'],
].map(([name,dependency])=>({name,dependency,status:'dependency-gated specification',enabled:false}));
export const POLICY = {enabled:false,marketingPer24Hours:1,initialBroadcastsPer7Days:1,timezone:'UNCONFIRMED: fixed PST vs America/Los_Angeles',queueExpiryHours:24,recheckBeforeSend:true,duplicateKey:'eventId/contactId/flowId/stepId',approval:'Local specifications only; map and test installed platform before enabling'};
export type ContactState = {consent:boolean;country:string;unknownSource?:boolean;suppressed?:boolean;orderPlaced?:boolean;orders:number;cartTotal:number;cartQuantity:number;cartEmpty?:boolean;cartRecovered?:boolean;sentLast24h?:boolean;duplicateEvent?:boolean;daysSinceCartTrigger?:number;couponExpired?:boolean;supportIssue?:boolean;competingHigherPriority?:boolean;engaged90?:boolean;winbackApproved?:boolean};
export function decision(flow:string,step:string,s:ContactState):string {
 if(!s.consent||s.country!=='US'||s.unknownSource||s.suppressed) return 'suppress';
 if(s.duplicateEvent) return 'deduplicate';
 if((s.orderPlaced||(flow==='welcome'&&s.orders>0))&&['welcome','cart','support','repeat','winback'].includes(flow)) return 'exit-order';
 if(s.supportIssue&&['post-purchase','repeat','winback'].includes(flow)) return 'pause-support';
 if(['cart','support'].includes(flow)) {
  if(s.cartEmpty||s.cartRecovered) return 'exit-cart';
  if((s.daysSinceCartTrigger??8)<7&&step==='enroll') return 'suppress-reentry';
  if(flow==='cart'&&(s.cartTotal>500||s.cartQuantity>=10))return 'route-h1';
 }
 if(s.sentLast24h||s.competingHigherPriority)return 'defer-recheck';
 if(['repeat','broadcast'].includes(flow)&&!s.engaged90)return 'suppress-inactive';
 if(flow==='winback'&&!s.winbackApproved)return 'hold-approval';
 if(step==='c2a'&&s.orders>0)return 'route-c2b';
 if((['w1','w2','w2-alt','w3','w4','c2a'].includes(step))&&s.couponExpired)return 'skip-expired-offer';
 return 'eligible-draft';
}
