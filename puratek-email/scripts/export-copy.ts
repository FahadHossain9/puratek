import fs from 'node:fs';
const all=JSON.parse(fs.readFileSync('dist/manifest.json','utf8'));
let md='# Puratek — current campaign copy deck (r8)\n\nGenerated from the implementation. W1–W3 use team-supplied copy with punctuation normalized; W2 uses the recommended COA checklist; W2-alt retains the comparison for reference. Other roles are newly authored drafts. Exact visible coupon and product values require store configuration.\n\n';
let map='# Campaign role and section map\n\n23 roles + one mutually exclusive W2 alternative. New non-welcome copy is draft for review.\n\n';
for(const e of all){
 md+=`## ${e.name}\n\nSubject: ${e.subjectA}\n\nAlternative subject: ${e.subjectB}\n\nPreheader: ${e.preheader}\n\n**${e.eyebrow}**\n\n### ${e.headline}\n\n${(e.heroCopy||[]).join('\n\n')}\n\n`;
 if(e.heroAction)md+=`CTA: ${e.heroAction.label} → ${e.heroAction.href}\n\n`;
 for(const s of e.sections||[]){md+=`### ${s.heading||s.eyebrow||s.id}\n\n${s.eyebrow||''}\n\n${(s.paragraphs||[]).join('\n\n')}\n\n`;
  if(s.items)md+=s.items.map((p:string[],i:number)=>`${i+1}. **${p[0]}** ${p[1]}`).join('\n')+'\n\n';
  if(s.comparison)md+='PURITY: How much of the material meets the stated purity specification. Purity analysis. HPLC / relevant analytical evidence. “How pure is it?”\n\nIDENTITY: Whether the material is actually the compound it claims to be. Identity confirmation. Mass spectrometry / relevant identity evidence. “What is it?”\n\n';
  for(const p of s.products||[])md+=`**${p.name}** — ${p.copy}\n\n${p.cta} → ${p.href}\n\n`;
  if(s.cart)md+='[Dynamic cart product rows: store image, selected variation, quantity and price. Sample data is not sent.]\n\n';
  if(s.offer)md+=`**${s.offer.title}** · ${s.offer.code} · ${s.offer.terms}\n\n`;
  if(s.setup)md+=s.setup+'\n\n';if(s.cta)md+=`CTA: ${s.cta.label} → ${s.cta.href}\n\n`;
 }
 md+=`Artwork: ${e.art.file}\n\nPurpose: ${e.strategy.goal}\n\nTiming: ${e.strategy.timing}\n\n`;
 map+=`## ${e.id.toUpperCase()} — ${e.headline}\n\n- Role: ${e.strategy.goal}\n- Trigger: ${e.strategy.trigger}\n- Timing: ${e.strategy.timing}\n- Audience: ${e.strategy.audience}\n- Sections: ${(e.sections||[]).map((s:any)=>s.heading||s.id).join(' → ')}\n- Artwork: ${e.art.file}; product-focused composition\n- Dynamic: ${e.cart?'Full cart block and restore link; ':''}${JSON.stringify(e.sections).includes('COUPON')?'Verified coupon data; ':''}business address, opt-out and preferences.\n- Measurement: ${e.strategy.kpi}\n- Exit/suppression: ${e.strategy.stopRules}\n\n`;
}
for(const dir of ['docs',...(fs.existsSync('../docs')?['../docs']:[])]){fs.writeFileSync(`${dir}/05-Current-Copy-Deck.md`,md);fs.writeFileSync(`${dir}/16-Campaign-Role-and-Section-Map.md`,map)}
console.log('Exported full campaign copy and section map');
