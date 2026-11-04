<?php
require __DIR__ . '/../../app/Libraries/MainboardFuses.php';
require __DIR__ . '/../../app/Libraries/EquipmentParams.php';
require __DIR__ . '/../../app/Libraries/UpgradePosition.php';
require __DIR__ . '/../../app/Libraries/DrawerSlotPlan.php';
require __DIR__ . '/../../app/Validation/Validator.php';
use App\Libraries\MainboardFuses as Fuses;
use App\Libraries\EquipmentParams as Params;
use App\Libraries\DrawerSlotPlan as Slots;
use App\Libraries\UpgradePosition as Positions;
$count=0;
function check($ok,$message) { global $count; $count++; if(!$ok) throw new RuntimeException($message); }
function rejects($callback) { try {$callback();} catch(DomainException $e) {check(true,'Rejected');return;} check(false,'Expected rejection'); }
foreach ([null,true,false] as $value) {
    $input=['fuses_removed'=>$value];$error='';
    check(Fuses::optionalRule('fuses_removed',$input,$error,[]),'Valid tri-state');
    $payload=Params::editPayload($input,$input,Fuses::parameterNames('Mainboard',[]),'{"existing":12}');
    check(array_key_exists('fuses_removed',$payload['type_params']) && $payload['type_params']['fuses_removed']===$value,'Retain false/null');
    check($payload['type_params']['existing']===12,'Preserve metadata');
}
foreach ([0,1,'Yes','No','false',[],new stdClass] as $value) {
    $input=['fuses_removed'=>$value];$error='';check(!Fuses::optionalRule('fuses_removed',$input,$error,[]),'Reject nonboolean');
}
$payload=Params::editPayload(['fuses_removed'=>null,'comments'=>'changed'],['comments'=>'changed'],Fuses::parameterNames('Mainboard',[]),'{"fuses_removed":true}');
check(!array_key_exists('type_params',$payload),'Unrelated edit preserves stored fuse flag');
check(Fuses::parameterNames('PMT',[])===[],'No flag for other types');
$types=[['id'=>31,'name'=>'HV Bus Board','active'=>1,'deleted_at'=>null]];
$ids=[45,8,43,9,10,11,12];
foreach (Positions::DRAWERS as $i=>$name) $types[]=['id'=>$ids[$i],'name'=>$name,'active'=>1,'deleted_at'=>null];
$plan=Slots::plan($types,[],[]);
check(count($plan)===7,'All seven drawer types');
$slots=[];$allowed=[];
foreach($plan as $i=>$item) {
 check($item['action']==='add','Initial addition');
 $slots[]=['id'=>$i+1,'name'=>$item['name'],'parent_type_id'=>$item['parent_type_id'],'deleted_at'=>null];
 $allowed[]=['slot_id'=>$i+1,'allowed_type_id'=>31,'deleted_at'=>null];
}
$before=serialize([$types,$slots,$allowed]);
foreach(Slots::plan($types,$slots,$allowed) as $item) check($item['action']==='keep','Idempotence');
check($before===serialize([$types,$slots,$allowed]),'Planning does not mutate existing metadata');
rejects(fn()=>Slots::plan(array_slice($types,1),[],[]));
rejects(fn()=>Slots::plan($types,$slots,[]));
$slots[]=['id'=>99,'name'=>'other_hv_slot','parent_type_id'=>45,'deleted_at'=>null];
$allowed[]=['slot_id'=>99,'allowed_type_id'=>31,'deleted_at'=>null];
rejects(fn()=>Slots::plan($types,$slots,$allowed));
echo "Passed {$count} drawer/fuse assertions (no database).\n";
