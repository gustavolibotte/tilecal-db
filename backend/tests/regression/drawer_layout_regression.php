<?php
require __DIR__ . '/../../app/Libraries/UpgradePosition.php';
require __DIR__ . '/../../app/Libraries/DrawerLayout.php';
require __DIR__ . '/../../app/Libraries/DrawerAssembly.php';
use App\Libraries\DrawerLayout as Layout;
use App\Libraries\DrawerAssembly as Assembly;
$count = 0;
function check($condition, $message) { global $count; $count++; if (!$condition) throw new RuntimeException($message); }
function rejects($callback) { try { $callback(); } catch (DomainException $e) { check(true, $e->getMessage()); return; } throw new RuntimeException('Expected rejection'); }
$expected = [
 'LB' => ['MD1'=>range(1,12),'MD2'=>range(13,24),'MD3'=>[25,26,27,28,29,30,31,34,35,36],'MD4'=>[37,38,39,40,41,42,43,45,46,47,48]],
 'EB' => ['MD1'=>range(1,12),'MD2'=>[13,14,15,16,17,18,21,22,23,24],'mD4'=>[29,30],'MD3'=>[33,34,37,38,41,42],'mD5'=>[43,44]],
 'SHORT_EBA15_EBC18' => ['MD1'=>range(5,16),'MD2'=>range(17,24),'mD6'=>[29,30,33,34],'MD3'=>[37,38,41,42,43,44],'mD7'=>[]],
];
foreach ($expected as $configuration => $units) {
 $all=[];
 foreach ($units as $unit=>$positions) {
  $type = str_starts_with($unit,'MD') ? 'Mini Drawer (Standard)' : 'Micro Drawer ' . substr($unit,2);
  $specs=Layout::specifications($type,['configuration'=>$configuration,'unit'=>$unit]);
  check(array_values(array_column($specs,'position'))===$positions,"Exact physical occupancy: $configuration $unit");
  $boards=array_filter($specs,fn($s)=>!isset($s['position']));
  check(count($boards)===(str_starts_with($unit,'MD')?3:0),'Boards only in mini-drawers');
  $all=array_merge($all,$positions);
  if (str_starts_with($unit,'MD')) {
   check($specs===Layout::specifications('Mini Drawer (With Patch-Panel)',['configuration'=>$configuration,'unit'=>$unit]),'Patch panel does not override topology');
  }
 }
 check(count(array_unique($all))===count($all),'No repeated physical positions');
 check($all===Layout::layout($configuration)['occupied_pmt_slots'],'Order and total match approved layout');
}
foreach (['LBA','LBC','EBA','EBC'] as $partition) for($module=1;$module<=64;$module++) {
 $location=sprintf('%s%02d',$partition,$module);
 $wanted=in_array($location,['EBA15','EBC18'])?'SHORT_EBA15_EBC18':(str_starts_with($partition,'LB')?'LB':'EB');
 check(Layout::configuration($location)===$wanted,'Correct module classification');
}
foreach(['EBC15','EBA18'] as $name) check(Layout::configuration($name)==='EB','Do not classify unrelated modules as short');
foreach(['','LBA00','EBA65','LBC1','LBA01x','LBCT1'] as $name) rejects(fn()=>Layout::configuration($name));
rejects(fn()=>Layout::profile('Mini Drawer','EBA01','MD4'));
rejects(fn()=>Layout::profile('Mini Drawer','','MD1'));
rejects(fn()=>Layout::profile('Micro Drawer 4','EBA15',''));
rejects(fn()=>Layout::profile('P2 EB Super Drawer','EBA15',''));
foreach(Layout::SUPERS as $type=>$config) {
 $slots=Layout::specifications($type,['configuration'=>$config,'unit'=>'']);
 check(array_column($slots,'unit')===array_keys($expected[$config]),'Correct hierarchical order');
 foreach($slots as $slot) if($slot['unit']==='mD6') check($slot['types']===['Micro Drawer 6'],'Correct short EB micro-drawer type');
}
$specs=Layout::specifications('Mini Drawer',['configuration'=>'EB','unit'=>'MD3']);
check(array_values(array_column(array_filter($specs,fn($s)=>isset($s['position'])),'name')) === [
 'mini_drawer_pmt_block_slot_03','mini_drawer_pmt_block_slot_04','mini_drawer_pmt_block_slot_07',
 'mini_drawer_pmt_block_slot_08','mini_drawer_pmt_block_slot_11','mini_drawer_pmt_block_slot_12'], 'Mini slots preserve local physical numbering across empty positions');
foreach($specs as $slot) if(isset($slot['position'])) {
 check($slot['types']===\App\Libraries\UpgradePosition::BLOCKS && $slot['cable']===(in_array($slot['position'],[41,42])?'Long Cable':'Short Cable'),'Cable type follows physical position');
}
$catalog=[['id'=>91,'name'=>'drawer_8_hv_bus_board_slot']];
$bound=Assembly::bind($specs,$catalog);
check($bound[2]['slot_id']===91,'Reuse existing HV metadata');
check($bound[2]['name']==='drawer_8_hv_bus_board_slot','Keep existing slot name');
$catalog[]=['id'=>92,'name'=>'mini_drawer_hv_bus_board_slot'];
rejects(fn()=>Assembly::bind($specs,$catalog));
$hv=['types'=>['HV Bus Board'],'label'=>'HV Bus Board'];
rejects(fn()=>Assembly::checkType($hv,['type_id'=>32,'type_name'=>'HV Bus Board']));
Assembly::checkType($hv,['type_id'=>31,'type_name'=>'HV Bus Board']);
check(true,'HV type 31 accepted');
echo "Passed $count drawer layout assertions (no database writes).\n";
