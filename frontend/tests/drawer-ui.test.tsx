import React from 'react';
import {createRoot} from 'react-dom/client';
import {act} from 'react-dom/test-utils';
import {QueryClient, QueryClientProvider} from '@tanstack/react-query';
import AssembleForm from '../src/components/assemble_form/AssembleForm';
import {defaultQueryFunction} from '../src/utils/DbClient';

const host = document.createElement('div'); document.body.appendChild(host);
const root = createRoot(host);
let count = 0;
function check(value: any, message: string) { count++; if (!value) throw new Error(message); }
const wait = (ms = 30) => new Promise(resolve => setTimeout(resolve, ms));
async function flush(ms = 30) { await act(async () => {await wait(ms);}); }
async function scan(id: string, value: string) {
  const input = document.getElementById(id) as HTMLInputElement;
  check(input && !input.disabled, 'Scan field is enabled: ' + id);
  await act(async () => {
    input.focus();
    Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value')!.set!.call(input, value);
    input.dispatchEvent(new Event('input', {bubbles: true}));
  });
  await act(async () => {input.dispatchEvent(new KeyboardEvent('keydown', {key:'Enter', bubbles:true}));});
  await flush();
}
export async function run() {
  const client = new QueryClient({defaultOptions: {queries: {queryFn:defaultQueryFunction, retry:false}, mutations:{retry:false}}});
  await act(async () => {root.render(<QueryClientProvider client={client}><AssembleForm parentId="4000001" parentTypeName="Micro Drawer 4" /></QueryClientProvider>);});
  await flush(); await flush();
  check(host.textContent?.includes('2 PMT blocks'), 'Fixed micro-drawer occupancy shown');
  check(!host.textContent?.includes('HV Bus Board'), 'No HV board in micro-drawer');
  check(!host.querySelector('input[type=checkbox]'), 'No physical-slot checkbox');
  check(host.querySelectorAll('input[data-assembly-field]').length === 4, 'Two ID/position pairs');
  const button = () => Array.from(host.querySelectorAll('button')).find(b=>b.textContent?.includes('Confirm Assembly'))!;
  check(button().disabled, 'Incomplete assembly blocked');
  await scan('micro_drawer_4_pmt_block_slot_01','PB00001');
  check(document.activeElement?.id === 'micro_drawer_4_pmt_block_slot_01-position','ID validation advances focus');
  await scan('micro_drawer_4_pmt_block_slot_01-position','28');
  check(host.textContent?.includes('Expected physical position 29'), 'Wrong physical position rejected');
  check(document.activeElement?.id === 'micro_drawer_4_pmt_block_slot_01-position','Invalid position retains focus');
  await scan('micro_drawer_4_pmt_block_slot_01-position','29');
  check(document.activeElement?.id === 'micro_drawer_4_pmt_block_slot_02','Valid position advances to next block');
  await scan('micro_drawer_4_pmt_block_slot_02','PB00002');
  await scan('micro_drawer_4_pmt_block_slot_02-position','30');
  check(!button().disabled,'Complete valid assembly enables summary');
  await act(async () => {button().click();});
  await flush();
  check(document.body.textContent?.includes('Position mismatch. The scanned position will replace the current position.'),'Existing position conflict remains visible');
  check(document.body.textContent?.includes('Scanned Upgrade Position'),'Summary retains scanned values');
  await act(async () => {root.unmount();}); client.clear(); host.remove();
  console.log(`Passed ${count} drawer UI assertions.`);
}
