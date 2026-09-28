import * as React from 'react';
import {Html,Head,Body,Preview,Text,Heading,Img,Link,Button} from '@react-email/components';
import {PROOF,type Email,type Block,type CampaignSection} from './content';
import {T,R,FONT,DARK} from './design-tokens';
import {layoutFor} from './layout';
export {T} from './design-tokens';
export type RenderOpts={logoLight:string;site:string;address:string;imageBase:string;preview?:boolean};
const paragraph:React.CSSProperties={fontFamily:FONT.body,fontSize:'16px',lineHeight:'25px',color:T.body,margin:'0 0 14px'};
export const DISCLAIMER='All products sold by Puratek are strictly for laboratory and research use only. They are not intended for human or animal consumption, diagnostic, therapeutic, or clinical use. These statements have not been evaluated by the U.S. Food and Drug Administration.';
function Section({name,children,bg=T.white,pad='24px 32px',className='bg-card',style={}}:{name:string;children:React.ReactNode;bg?:string;pad?:string;className?:string;style?:React.CSSProperties}){
 return <table data-section={name} role="presentation" width="100%" cellPadding={0} cellSpacing={0} border={0} style={{backgroundColor:bg,borderCollapse:'separate'}}><tbody><tr><td className={`px ${className}`} style={{padding:pad,backgroundColor:bg,...style}}>{children}</td></tr></tbody></table>;
}
function Label({children}:{children:React.ReactNode}){return <Text className="tx-o" style={{fontFamily:FONT.body,fontSize:11,lineHeight:'17px',fontWeight:700,letterSpacing:'1.4px',textTransform:'uppercase',color:T.orangeText,margin:'0 0 16px'}}>{children}</Text>}
function BlockView({b}:{b:Block}){
 if(b.t==='p')return <Text className="tx-b" style={paragraph}>{b.text}</Text>;
 return <table role="presentation" width="100%" cellPadding={0} cellSpacing={0} border={0}><tbody>{b.items.map((item,i)=>{
 const pair=Array.isArray(item)?item:null;
 return <tr key={i}><td className="content-card bg-surface" style={{padding:'18px 20px',backgroundColor:T.surface,borderBottom:`6px solid ${T.white}`,borderRadius:12}}><Text className="tx-o" style={{...paragraph,fontSize:11,lineHeight:'16px',fontWeight:700,letterSpacing:1,color:T.orangeText,margin:'0 0 6px'}}>{String(i+1).padStart(2,'0')}</Text><Text className="tx-b" style={{...paragraph,margin:0}}>{pair?<><strong className="tx-h" style={{color:T.heading}}>{pair[0]}</strong><br/>{pair[1]}</>:item}</Text></td></tr>;
 })}</tbody></table>;
}
function Proof({kind}:{kind:string}){
 return <Section name="proof" bg={T.surface} className="bg-surface" pad="24px 32px 8px"><Label>The Puratek standard</Label>{PROOF[kind].map(([label,desc],i)=><Text key={label} className="tx-b" style={{...paragraph,fontSize:14,lineHeight:'22px',margin:'0 0 16px',paddingBottom:16,borderBottom:`1px solid ${T.line}`}}><span className="tx-o" style={{color:T.orangeText,fontWeight:700,fontSize:11,letterSpacing:1}}>{String(i+1).padStart(2,'0')}</span><br/><strong className="tx-h" style={{color:T.heading,fontSize:16}}>{label}</strong><br/>{desc}</Text>)}</Section>;
}
function Cart(){
 const th:React.CSSProperties={fontFamily:FONT.body,fontSize:12,lineHeight:'18px',color:T.muted,textAlign:'left',padding:'0 0 10px',borderBottom:`1px solid ${T.line}`};
 return <div data-cart="start"><table data-section="cart" aria-label="Items in your cart" width="100%" cellPadding={0} cellSpacing={0} border={0} style={{tableLayout:'fixed',borderCollapse:'collapse',margin:'8px 0'}}><thead><tr><th scope="col" className="tx-m" style={{...th,width:'44%'}}>Product</th><th scope="col" className="tx-m" style={{...th,width:'20%'}}>Size</th><th scope="col" className="tx-m" style={{...th,width:'11%',textAlign:'center'}}>Qty</th><th scope="col" className="tx-m" style={{...th,width:'25%',textAlign:'right'}}>Price</th></tr></thead><tbody><tr><td colSpan={4}>__CART_ROWS__</td></tr></tbody></table><Text className="tx-h" style={{...paragraph,fontSize:14,textAlign:'right',fontWeight:700,margin:'12px 0 4px'}}>Subtotal __CART_SUBTOTAL__</Text><span data-cart="end"/></div>;
}
function Offer({offer}:{offer:NonNullable<Email['offer']>}){
 return <table data-section="offer" role="presentation" width="100%" cellPadding={0} cellSpacing={0} border={0} style={{margin:'0 0 20px',backgroundColor:T.orange,borderRadius:16,padding:2}}><tbody><tr><td className="bg-card" align="center" style={{backgroundColor:T.white,border:`1px solid ${T.orange}`,borderRadius:R.offer,padding:'22px 16px'}}><Text className="tx-h" style={{...paragraph,fontSize:15,fontWeight:700,color:T.heading,margin:'0 0 8px'}}>{offer.title}</Text><Text className="coupon tx-h" style={{fontFamily:FONT.mono,fontSize:24,lineHeight:'30px',fontWeight:700,letterSpacing:2,color:T.heading,overflowWrap:'anywhere',wordBreak:'break-word',margin:'0 0 8px'}}>{offer.code}</Text><Text className="tx-m" style={{...paragraph,fontSize:13,lineHeight:'20px',color:T.muted,margin:0}}>{offer.terms}</Text></td></tr></tbody></table>;
}
function CampaignButton({action}:{action:Email['cta']}){
 return <Button className="btn" href={action.href} style={{backgroundColor:T.orange,color:T.heading,fontFamily:FONT.body,fontSize:14,fontWeight:700,lineHeight:'22px',padding:'16px 20px',borderRadius:R.button,textDecoration:'none',display:'block',textAlign:'center'}}>{action.label}</Button>;
}
function Comparison(){
 const columns=[['PURITY','How much of the material meets the stated purity specification','Purity analysis','HPLC / relevant analytical evidence','How pure is it?'],['IDENTITY','Whether the material is actually the compound it claims to be','Identity confirmation','Mass spectrometry / relevant identity evidence','What is it?']];
 return <table data-comparison="true" aria-label="Purity versus identity" width="100%" cellPadding={0} cellSpacing={0} style={{tableLayout:'fixed',border:`1px solid ${T.line}`,borderRadius:12,margin:'20px 0'}}><tbody><tr>{columns.map((c,i)=><td key={c[0]} className="comparison-cell bg-card" width="50%" style={{verticalAlign:'top',padding:20,backgroundColor:T.white,borderLeft:i?`1px solid ${T.line}`:0}}><Label>{c[0]}</Label><Text className="tx-h" style={{...paragraph,fontWeight:700,color:T.heading}}>{c[4]}</Text><Text className="tx-b" style={{...paragraph,fontSize:14,lineHeight:'22px'}}>{c[1]}</Text><Text className="tx-b" style={{...paragraph,fontSize:14,lineHeight:'22px'}}>{c[2]}</Text><Text className="tx-m" style={{...paragraph,fontSize:13,lineHeight:'21px',color:T.muted,margin:0}}>{c[3]}</Text></td>)}</tr></tbody></table>;
}
function CampaignBlock({section:s,opts}:{section:CampaignSection;opts:RenderOpts}){
 return <Section name={s.id} bg={s.tone==='surface'?T.surface:T.white} className={s.tone==='surface'?'bg-surface':'bg-card'} pad="32px 32px">
 {s.eyebrow&&<Label>{s.eyebrow}</Label>}
 {s.heading&&<Heading as="h2" className="tx-h" style={{fontFamily:FONT.heading,fontSize:26,lineHeight:'32px',letterSpacing:'-0.4px',color:T.heading,margin:'0 0 18px'}}>{s.heading}</Heading>}
 {s.paragraphs?.map((text,i)=><Text key={i} className="tx-b" style={paragraph}>{text}</Text>)}
 {s.comparison&&<Comparison/>}
 {s.items?.map(([title,copy],i)=><table role="presentation" key={title} width="100%" cellPadding={0} cellSpacing={0} style={{margin:'20px 0',borderTop:`2px solid ${T.orange}`}}><tbody><tr><td style={{paddingTop:16}}><Label>{String(i+1).padStart(2,'0')}</Label><Heading as="h3" className="tx-h" style={{...paragraph,fontSize:19,lineHeight:'25px',fontWeight:700,color:T.heading,margin:'0 0 8px'}}>{title}</Heading><Text className="tx-b" style={{...paragraph,margin:0}}>{copy}</Text></td></tr></tbody></table>)}
 {s.products?.map(p=><table data-product={p.name} key={p.name} role="presentation" width="100%" cellPadding={0} cellSpacing={0} style={{border:`1px solid ${T.line}`,borderRadius:12,margin:'24px 0'}}><tbody><tr><td align="center" style={{padding:16,backgroundColor:'#FAFAFA',borderRadius:'12px 12px 0 0'}}><Link href={p.href}><Img src={`${opts.imageBase}/${p.file}`} alt={`${p.name} — original Puratek catalog photograph`} width={160} height={184} style={{display:'block',width:160,height:'auto',maxWidth:'100%'}}/></Link></td></tr><tr><td style={{padding:20}}><Heading as="h3" className="tx-h" style={{...paragraph,fontWeight:700,fontSize:21,color:T.heading,margin:'0 0 8px'}}>{p.name}</Heading><Text className="tx-b" style={paragraph}>{p.copy}</Text><Link className="lnk" href={p.href} style={{display:'block',fontFamily:FONT.body,fontSize:13,fontWeight:700,lineHeight:'22px',padding:'14px 0',color:T.orangeText}}>{p.cta} →</Link></td></tr></tbody></table>)}
 {s.cart&&<Cart/>}
 {s.offer&&<Offer offer={s.offer}/>}
 {s.setup&&<Text className="tx-b" style={{...paragraph,margin:'18px 0'}}>{s.setup}</Text>}
 {s.cta&&<CampaignButton action={s.cta}/>}
 </Section>;
}
function Action({e}:{e:Email}){const linkStyle={color:T.orangeText,textDecoration:'underline'};return (<Section name="action" pad="24px 32px 12px">
 {e.offer&&<Offer offer={e.offer}/>}
 <table role="presentation" width="100%" cellPadding={0} cellSpacing={0} border={0}><tbody><tr><td align="center"><Button className="btn" href={e.cta.href} style={{backgroundColor:T.button,color:T.buttonText,fontFamily:FONT.body,fontSize:16,fontWeight:700,lineHeight:'22px',padding:'16px 26px',borderRadius:R.button,textDecoration:'none',display:'block',textAlign:'center'}}>{e.cta.label}</Button></td></tr></tbody></table>
 {e.secondary&&<Text style={{...paragraph,fontSize:14,textAlign:'center',margin:'4px 0 0'}}><Link className="lnk" href={e.secondary.href} style={{...linkStyle,display:'inline-block',padding:'12px 6px'}}>{e.secondary.label}</Link></Text>}
 {e.afterCta&&<Text className="tx-m" style={{...paragraph,fontSize:14,color:T.muted,margin:'12px 0 0'}}>{e.afterCta}</Text>}
 </Section>);}
export const CSS=`
:root{color-scheme:light dark;supported-color-schemes:light dark}
.email-hero,.hero-art{background-color:${T.orange}!important}.hero-ink{color:${T.heading}!important}
table{mso-table-lspace:0pt;mso-table-rspace:0pt}a{word-break:normal}
@media only screen and (max-width:620px){
 .container{width:100%!important}.px{padding-left:24px!important;padding-right:24px!important}
 .comparison-cell{display:block!important;width:auto!important}.comparison-cell+.comparison-cell{border-left:0!important;border-top:1px solid #ddd!important}
 .h1{font-size:28px!important;line-height:33px!important}.btn{display:block!important;text-align:center!important}
}
@media (prefers-color-scheme:dark){
 .content-card{border-bottom-color:${DARK.card}!important}.support-card{border-color:#494949!important;border-left-color:${T.orange}!important}
 .bg-page{background-color:${DARK.page}!important}.bg-card{background-color:${DARK.card}!important}
 .bg-surface{background-color:${DARK.surface}!important}.bg-cream{background-color:${DARK.cream}!important}
 .tx-h{color:${DARK.heading}!important}.tx-b{color:${DARK.body}!important}.tx-m{color:${DARK.muted}!important}
 .tx-o,.lnk{color:${DARK.orange}!important}
}
[data-ogsb] .bg-page{background-color:${DARK.page}!important}[data-ogsb] .bg-card{background-color:${DARK.card}!important}
[data-ogsb] .content-card{border-bottom-color:${DARK.card}!important}[data-ogsb] .support-card{border-color:#494949!important;border-left-color:${T.orange}!important}
[data-ogsc] .tx-h{color:${DARK.heading}!important}[data-ogsc] .tx-b{color:${DARK.body}!important}[data-ogsc] .tx-m{color:${DARK.muted}!important}
`;
export function PuratekEmail({e,opts}:{e:Email;opts:RenderOpts}){
 const url=(p:string)=>`${opts.site}${p}?utm_source=email&utm_medium=footer&utm_campaign=${e.id}`;
 const linkStyle={color:T.orangeText,textDecoration:'underline'};
 const footer:React.CSSProperties={fontFamily:FONT.body,fontSize:13,lineHeight:'20px',color:'#DDDDDD',margin:'0 0 14px'};
 const layout=layoutFor(e);
 const centered=false;
 return <Html lang="en"><Head><title>{e.subjectA}</title><meta charSet="utf-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/><meta name="color-scheme" content="light dark"/><meta name="supported-color-schemes" content="light dark"/><meta name="format-detection" content="telephone=no,address=no,email=no"/><style>{CSS}</style></Head><Body className="bg-page" style={{backgroundColor:T.page,margin:0,padding:0,fontFamily:FONT.body}}><Preview>{e.preheader}</Preview>
 <table role="presentation" width="100%" cellPadding={0} cellSpacing={0} border={0} className="bg-page" style={{backgroundColor:T.page}}><tbody><tr><td align="center" style={{padding:'16px 0'}}>
 <table role="presentation" data-email={e.id} data-layout={layout.kind} className="container" width="600" cellPadding={0} cellSpacing={0} border={0} style={{width:'100%',maxWidth:600,margin:'0 auto',backgroundColor:T.white,borderCollapse:'separate'}}><tbody><tr><td>
 <Section name="brand" pad="24px 32px 20px" className="brand-bar" style={{borderTop:`3px solid ${T.orange}`,backgroundColor:T.white,textAlign:centered?'center':'left'}}><Link href={url('/')}><Img src={opts.logoLight} alt="Puratek" width={166} height={36} style={{display:'block',border:0,margin:centered?'0 auto':0}}/></Link></Section>
 <Section name="hero" pad="28px 32px 12px" bg={T.orange} className="email-hero" style={{textAlign:'left'}}>
 <Text className="hero-ink" style={{fontFamily:FONT.body,fontSize:11,lineHeight:'16px',fontWeight:700,letterSpacing:'1.5px',textTransform:'uppercase',color:T.heading,margin:'0 0 12px'}}>{e.sections?e.eyebrow:layout.label}</Text>
 <Heading as="h1" className="h1 hero-ink" style={{fontFamily:FONT.heading,fontSize:34,lineHeight:'39px',fontWeight:700,color:T.heading,letterSpacing:'-0.7px',margin:0}}>{e.headline}</Heading>
 {e.heroCopy?.map((text,i)=><Text key={i} className="hero-ink" style={{...paragraph,color:T.heading,margin:"16px 0 0"}}>{text}</Text>)}
 </Section>
 {e.art&&<Section name="hero-art" bg={T.orange} className="hero-art" pad="16px 32px 28px"><table role="presentation" width="100%" cellPadding={0} cellSpacing={0}><tbody><tr><td><Img data-editorial-art="true" src={`${opts.imageBase}/${e.art.file}`} alt={e.art.alt} width={536} height={Math.round(536*(e.art.height||800)/(e.art.width||1200))} style={{display:'block',width:'100%',maxWidth:536,height:'auto',borderRadius:'12px 12px 0 0'}}/></td></tr><tr><td style={{backgroundColor:T.heading,borderRadius:'0 0 12px 12px',padding:'18px 20px'}}><Img src={opts.logoLight.replace('logo-dark','logo-light')} alt="Puratek" width={112} height={24} style={{display:'block',margin:'0 0 10px'}}/><Text style={{fontFamily:FONT.body,fontSize:12,lineHeight:'18px',color:'#FFFFFF',margin:0}}>Quality You Can Verify, Not Just Trust.</Text></td></tr></tbody></table></Section>}
 {e.sections?<>{e.heroAction&&<Section name="hero-action"><CampaignButton action={e.heroAction}/></Section>}{e.sections.map(section=><CampaignBlock key={section.id} section={section} opts={opts}/>)}</>:<>
 {layout.offerFirst&&<Action e={e}/>}
 <Section name="body" pad="28px 32px 12px"><Label>{layout.bodyLabel}</Label>
 {e.body.map((b,i)=><BlockView key={i} b={b}/>)}
 {e.cart&&<Cart/>}
 </Section>

 {e.proof&&<Proof kind={e.proof}/>}
 {!layout.offerFirst&&<Action e={e}/>}</>}
 {e.support&&<Section name="support" pad="12px 32px 28px"><table role="presentation" width="100%" cellPadding={0} cellSpacing={0} border={0}><tbody><tr><td className="support-card bg-card" style={{padding:'20px',backgroundColor:T.white,borderRadius:12,border:`1px solid ${T.line}`,borderLeft:`4px solid ${T.orange}`}}><Text className="tx-h" style={{...paragraph,fontSize:18,lineHeight:'24px',fontWeight:700,color:T.heading,margin:'0 0 8px'}}>A question? We are here.</Text><Text className="tx-b" style={{...paragraph,fontSize:14,lineHeight:'22px',margin:0}}>Reply to this email for documentation or order support.<br/><Link className="lnk" href="tel:+17025184855" style={linkStyle}>702-518-4855</Link><br/>Mon–Fri, 9 AM–4 PM PST</Text></td></tr></tbody></table></Section>}
 <Section name="footer" bg={T.navy} className="brand-footer" pad="32px 32px">
 <Img src={opts.logoLight.replace('logo-dark','logo-light')} alt="Puratek" width={144} height={31} style={{display:'block',margin:'0 0 24px'}}/>
 <Text className="footer-copy" style={footer}>{DISCLAIMER}</Text><Text className="footer-copy" style={footer}>Puratek sells only to customers 21 years of age or older.</Text>
 <Text className="footer-copy" style={footer}>Puratek LLC · {opts.address}<br/><Link href="mailto:support@puratekpeptides.com" className="footer-copy" style={{color:'#DDDDDD',textDecoration:'underline'}}>support@puratekpeptides.com</Link><br/>702-518-4855 · {"Mon\u2060–\u2060Fri, 9\u00A0AM\u2060–\u20604\u00A0PM\u00A0PST"}</Text>
 <Text className="footer-copy" style={footer}>{[['Terms',url('/terms-conditions/')],['Disclaimer',url('/disclaimer/')],['Privacy','%%PRIVACY_URL%%']].map(([label,href],i)=><React.Fragment key={label}>{i>0?' · ':''}<Link className="footer-copy" href={href} style={{color:'#DDDDDD',textDecoration:'underline'}}>{label}</Link></React.Fragment>)}</Text>
 <Text className="footer-copy" style={footer}><Link className="footer-copy" href="{{unsubscribe_link}}" style={{color:'#DDDDDD',textDecoration:'underline'}}>Unsubscribe</Link>{' · '}<Link className="footer-copy" href="%%PREFERENCES_URL%%" style={{color:'#DDDDDD',textDecoration:'underline'}}>Email preferences</Link></Text>
 <Text className="footer-copy" style={{...footer,margin:0}}>Marketing email from Puratek Peptides. You’re receiving this because you opted in.</Text>
 </Section>
 </td></tr></tbody></table></td></tr></tbody></table>
 </Body></Html>;
}
