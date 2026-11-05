// Run with Node after installing jsdom as an optional local test dependency.
import {createRequire} from 'node:module';
import {fileURLToPath} from 'node:url';
import {dirname, join} from 'node:path';
import {mkdtemp, rm} from 'node:fs/promises';
import {tmpdir} from 'node:os';
import {build} from 'esbuild';
const require = createRequire(import.meta.url);
const {JSDOM} = require(process.env.JSDOM_MODULE || 'jsdom');
const dom = new JSDOM('<html><head></head><body></body></html>', {url:'http://localhost'});
for (const key of ['window','document','navigator','HTMLElement','HTMLInputElement','HTMLButtonElement','Event','KeyboardEvent','Node','Element','getComputedStyle']) {
  Object.defineProperty(globalThis,key,{value:dom.window[key],configurable:true});
}
globalThis.requestAnimationFrame = callback => setTimeout(callback,0);
globalThis.cancelAnimationFrame = clearTimeout;
globalThis.IS_REACT_ACT_ENVIRONMENT = true;
const output = await mkdtemp(join(tmpdir(),'tilecal-ui-'));
try {
  await build({entryPoints:[join(dirname(fileURLToPath(import.meta.url)), 'drawer-ui.test.tsx')],
    outfile:join(output,'test.cjs'),bundle:true,platform:'node',format:'cjs',jsx:'automatic',loader:{'.css':'empty'},
    define:{'process.env.NODE_ENV':'"development"',__INVALID_DATA_MSG__:'"Invalid data"',__DEFAULT_ERROR_MSG__:'"Error"',__MYSQL_MAX_TEXT_SIZE__:'65535',__FIELD_REQUIRED_MSG__:'"Required"',__FIELD_POSITIVE_MSG__:'"Positive"'},
    plugins:[{name:'read-only-api-fixture',setup(build){
      build.onResolve({filter:/utils\/DbClient$/},()=>({path:'api-fixture',namespace:'fixture'}));
      build.onLoad({filter:/.*/,namespace:'fixture'},()=>({loader:'js',contents:`
        export function getEndpoint(name) {return name;}
        export function makeMutationFunction() {return async()=>{throw new Error('Test must not write');};}
        export async function defaultQueryFunction({queryKey:[endpoint,,params]}) {
          if(endpoint==='assembly_layout') return {data:[{managed:true,ready:true,issues:[],locations:['EBA01'],occupied:{},
            profile:{configuration:'EB',unit:'mD4',location:''},physical:{pmt_block_count:2,empty_slots:[25,26,27,28]},
            slots:[29,30].map((position,index)=>({name:'micro_drawer_4_pmt_block_slot_0'+(index+1),label:'PMT Block — position '+position,position,allowed_type_ids:['15']}))}]};
          if(endpoint==='equipment_type') return {data:[{slot_names:[]}]};
          if(endpoint==='equipment') return {data:[]};
          if(endpoint==='assembly_scan') return {data:[{id:params.equipment_id,type_name:'PMT Block (Long Cable)',positions:{box_position:7,version:'test-version'}}]};
          throw new Error('Unexpected endpoint: '+endpoint);
        }
      `}));
    }}]});
  const {run} = require(join(output,'test.cjs')); await run();
} finally {dom.window.close();await rm(output,{recursive:true,force:true});}

process.exit(0);
