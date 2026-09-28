import type { Email, Block } from './content';
const site = 'https://puratekpeptides.com';
const paragraph = (text: string): Block => ({t:'p',text});
function draft(id: string, flow: Email['flow'], title: string, subjectB: string, preheader: string, body: Block[], cta: string, destination: string, trigger: string, timing: string): Email {
  return {id,flow,status:'draft',name:`${id.toUpperCase()} · ${title}`,subjectA:title,subjectB,preheader,eyebrow:flow,headline:title,body,cta:{label:cta,href:`${site}${destination}?utm_source=email&utm_medium=flow&utm_campaign=${id}`},support:true,strategy:{
    goal:preheader,trigger,audience:'US contacts with recorded marketing consent; no suppression, unresolved support issue, refund or dispute',segment:'Eligible contacts outside competing flows; one marketing email per contact per day',timing,kpi:'Qualified clicks, support replies and subsequent orders; complaints and unsubscribes',stopRules:'Recheck eligibility immediately before send. Stop on unsubscribe, complaint or hard bounce. Pause for refund/dispute/support issue; exit on a new order where applicable.',included:['One clear action, live text and optional brand illustration','Documentation/service positioning and locked research-use footer'],leftOut:['Product recommendations, usage guidance and unapproved incentives','No invented testimonials, batch documents or delivery events'],compliance:['Draft for copy review; not approved for sending','No product-use claims or prohibited product imagery'],addedText:['Entire new template is draft copy; approve before production'],score:'Not rated',lift:'Verify the event integration, approve copy and run seed tests before activation.'}};
}
export const LIFECYCLE: Email[] = [
 draft('p1','Post-purchase','Support for your Puratek order','We’re here for order questions','Documentation and order support, in one place.',[
 paragraph('Thank you for choosing Puratek Peptides. If you have a question about your order or its documentation, our team can help.'),
 paragraph('Your order confirmation contains your purchase details. Keep that message handy when you contact us so we can find the right information.'),
 paragraph('For questions about dispatch or tracking, reply to this email or contact our team below.')],'Contact our team','/contact-us/','Verified paid order; opted-in customer; exclude duplicate order-service notifications','Proposed: 2 hours after verified payment'),
 draft('p2','Post-purchase','Keep your batch documents together','A guide to your batch documentation','Know where to find the paperwork for your order.',[
 paragraph('Each product has a batch-specific certificate of analysis. When your order arrives, match the batch number on the label to its document.'),
 {t:'steps',items:[['Find the batch number.','Check the label on the item you received.'],['Match the document.','Look for the same batch number on the certificate of analysis.'],['Ask if something is unclear.','Contact our team for help finding or understanding the document.']]}],'View certifications','/certifications/','Verified dispatch event; marketing consent required','Proposed: 1 day after dispatch'),
 draft('p3','Post-purchase','How was your ordering experience?','Tell us about our service','Your feedback helps us improve documentation and support.',[
 paragraph('We’d appreciate your feedback on the ordering experience: communication, packaging, dispatch updates and access to documentation.'),
 paragraph('Was anything difficult to find? Is there something our team could have explained more clearly? Reply to this email or use our contact page.'),
 paragraph('Please keep feedback focused on service and documentation. Our products are supplied for laboratory and research use only.')],'Share service feedback','/contact-us/','Verified delivery event; no refund/dispute/open support issue','Proposed: 7 days after confirmed delivery'),
 draft('p4','Post-purchase','Need a hand with documentation?','Your documentation questions are welcome','A direct route to the Puratek support team.',[
 paragraph('If you need help finding a batch document or reviewing the details of your order, our team is available.'),
 paragraph('Send us your order reference and the batch number you’re asking about. We’ll help you locate the matching information.')],'Ask a question','/contact-us/','Verified delivery; no newer order or active support issue','Proposed: 21 days after confirmed delivery'),
 draft('r1','Repeat engagement','Documentation before your next order','Start with the batch document','Review available documentation before deciding.',[
 paragraph('When you’re ready to review the catalog, start with the documentation. Batch-specific certificates of analysis let you check the material before placing an order.'),
 paragraph('If you need help locating a document, reply and our team will point you in the right direction.')],'View certifications','/certifications/','Consented purchaser; engaged within 90 days; no competing flow','Proposed eligibility checkpoint: 30 days after last order'),
 draft('r2','Repeat engagement','A clear way to browse Puratek','The catalog and paperwork, together','Explore the catalog at your own pace.',[
 paragraph('Our catalog brings product details and batch documentation together so you can review the information before making a decision.'),
 paragraph('Questions about documents, dispatch or an earlier order? Contact our team. We’re here to help with those details.')],'Browse the catalog','/shop/','Consented engaged purchaser; no new order since enrollment','Proposed eligibility checkpoint: 60 days after last order'),
 draft('r3','Repeat engagement','Planning another order?','Questions before you order again?','Check the current details and documents first.',[
 paragraph('If you’re considering another order, review the current catalog and its batch documents first. Availability and batch details may differ from your previous purchase.'),
 paragraph('There’s no schedule to follow. Browse when it suits your laboratory’s needs, or contact us with documentation questions.')],'Browse the catalog','/shop/','Consented purchaser still meaningfully engaged; no newer order','Proposed eligibility checkpoint: 90 days after last order'),
 draft('x1','Win-back','A note from Puratek','Would you like to hear from us?','Documentation and support remain a click away.',[
 paragraph('It’s been a little while since you last visited. If you’re reviewing suppliers or batch documentation, the Puratek team is here to answer questions.'),
 paragraph('You can review our certifications below. If our emails are no longer useful, you can unsubscribe using the link at the bottom.')],'View certifications','/certifications/','Separate client-approved inactive purchaser segment with recorded permission','Proposed: 90 days since meaningful engagement; disabled pending audience approval'),
 draft('x2','Win-back','Can we help you find something?','Your questions are welcome','Ask us about documentation, dispatch or support.',[
 paragraph('If a documentation or service question has kept you from finding what you need, reply to this email.'),
 paragraph('Our team can help you locate a batch document or explain our ordering and dispatch information. There’s no need to place an order to ask a question.')],'Contact our team','/contact-us/','Eligible win-back contact with no engagement/order since X1','Proposed: 7 days after X1'),
 draft('x3','Win-back','We’ll leave the next step to you','A final note from Puratek','Stay in touch only if these emails are useful.',[
 paragraph('We haven’t heard from you, so this is the last message in this series. If you’d like to stay in touch, reply to this email and let our team know.'),
 paragraph('Otherwise, we’ll pause marketing emails after this series. You can also unsubscribe immediately using the link below.')],'Contact our team','/contact-us/','Eligible win-back contact with no engagement/order; sunset automation required','Proposed: 14 days after X1; suppress marketing after final nonresponse'),
 draft('h1','Support','Questions about your saved cart?','Our team can help with your order','A person can help with documentation and order details.',[
 paragraph('You have a cart saved with Puratek. If you’d like help reviewing documentation or confirming order details, contact our team.'),
 paragraph('Reply with your question and we’ll help you find the information you need.')],'Contact our team','/contact-us/','Eligible abandoned cart total > $500 or quantity ≥10; replaces standard cart flow','Proposed: 1 hour after abandonment; seven-day re-entry limit'),
];
