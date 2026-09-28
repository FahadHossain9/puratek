import fs from 'node:fs';
import { spawnSync } from 'node:child_process';
import {startServer} from './serve';
function build(){return spawnSync(process.execPath,['--import','tsx','src/build.tsx'],{stdio:'inherit'}).status===0;}
if(!build())process.exit(1);startServer(Number(process.env.PORT||5173));
let timer:ReturnType<typeof setTimeout>;
for(const dir of ['src','assets'])fs.watch(dir,{recursive:true},()=>{clearTimeout(timer);timer=setTimeout(()=>{console.log('Source changed; rebuilding…');build()},200)});
