import http from 'node:http';
import fs from 'node:fs';
import path from 'node:path';
import {pathToFileURL} from 'node:url';
export function startServer(port=5173){
 const root=path.resolve('dist');
 const server=http.createServer((req,res)=>{
  let url:string;try{url=decodeURIComponent(new URL(req.url||'/', 'http://localhost').pathname)}catch{res.writeHead(400).end();return;}
  let file=path.resolve(root,'.'+url);if(!file.startsWith(root+path.sep)&&file!==root){res.writeHead(403).end();return;}
  try{if(fs.statSync(file).isDirectory())file=path.join(file,'index.html');const data=fs.readFileSync(file);const types:Record<string,string>={'.zip':'application/zip','.html':'text/html; charset=utf-8','.json':'application/json; charset=utf-8','.jpg':'image/jpeg','.png':'image/png','.woff2':'font/woff2'};res.writeHead(200,{'Content-Type':types[path.extname(file)]||'application/octet-stream','Cache-Control':'no-store',...(file.endsWith('.zip')?{'Content-Disposition':`attachment; filename="${path.basename(file)}"`}:{})});res.end(data)}catch{res.writeHead(404,{'Content-Type':'text/plain'});res.end('Not found')}
 });server.listen(port,'127.0.0.1',()=>{const address=server.address();console.log(`Puratek review: http://127.0.0.1:${typeof address==='object'&&address?address.port:port}`)});return server;
}
if(process.argv[1]&&import.meta.url===pathToFileURL(path.resolve(process.argv[1])).href)startServer(Number(process.env.PORT||5173));
