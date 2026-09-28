import fs from 'node:fs';
import path from 'node:path';
import {marked} from 'marked';
import {documentPage,nav} from './review';
const pages=[['feedback','18-Feedback-and-Implementation-Reference.md'],['coverage','17-Framework-Coverage.md'],['campaign-map','16-Campaign-Role-and-Section-Map.md'],['campaign','15-Campaign-Strategy-and-Implementation.md'],['restrictions','06-US-Email-Restrictions.md'],['scope','07-Launch-Scope.md'],['brand','08-Email-Brand-and-Assets.md'],['funnelkit','09-FunnelKit-Implementation.md'],['sharing','10-Client-Review-and-Vercel.md'],['layout','11-Email-Layout-Research.md'],['references','12-Reference-Analysis-and-Design-Plan.md'],['figma','13-Figma-Import-Handoff.md'],['orange','14-Orange-System-and-Artwork-Plan.md']];
export function writeGuides(out:string){
 for(const [slug,file] of pages){const md=fs.readFileSync(path.join('docs',file),'utf8');let html=marked.parse(md) as string;html=html.replace(/<table>/g,'<div class="table-wrap"><table>').replace(/<\/table>/g,'</table></div>');const body=`<main class="shell">${nav('../../')}<p><a href="../../index.html">← All emails</a></p><article class="guide">${html}</article></main>`;fs.mkdirSync(path.join(out,'guides',slug),{recursive:true});fs.writeFileSync(path.join(out,'guides',slug,'index.html'),documentPage(file.slice(3,-3),body,'../../'));}
}
