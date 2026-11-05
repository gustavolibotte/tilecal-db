<?php
/** Exercise production policy with an in-memory read adapter, not a production database. */
require __DIR__ . '/../../app/Libraries/EquipmentParams.php';
require __DIR__ . '/../../app/Libraries/UpgradePosition.php';
require __DIR__ . '/../../app/Libraries/DrawerLayout.php';
require __DIR__ . '/../../app/Libraries/DrawerAssembly.php';
use App\Libraries\DrawerLayout as Layout;
use App\Libraries\DrawerAssembly as Assembly;

final class Result {
    public function __construct(private array $rows) {}
    public function getResultArray() { return $this->rows; }
    public function getRowArray() { return $this->rows[0] ?? null; }
}
final class ReadTable {
    private array $filters = [];
    public function __construct(private array $rows) {}
    public function where($key,$value) { $this->filters[$key]=$value; return $this; }
    public function get() { return new Result(array_values(array_filter($this->rows,function($row) {
        foreach($this->filters as $key=>$value) if(($row[$key]??null)!=$value) return false;
        return true;
    }))); }
}
final class ReadDatabase {
    public array $tables = [];
    public function table($name) { return new ReadTable($this->tables[$name]??[]); }
    public function query($sql,$args) {
        if(str_contains($sql,'FROM equipments e')) return $this->table('equipments')->where('id',$args[0])->get();
        if(str_contains($sql,'FROM parent_child_equips')) {
            $side=str_contains($sql,'WHERE parent_equip_id')?'parent_equip_id':'child_equip_id';
            return $this->table('parent_child_equips')->where($side,$args[0])->where('deleted_at',null)->get();
        }
        throw new RuntimeException('Unexpected query in read-only test: '.$sql);
    }
}
$db = new ReadDatabase();
function db_connect() { global $db; return $db; }
$count=0;
function check($ok,$message) { global $count; $count++; if(!$ok) throw new RuntimeException($message); }
function rejects($callback) { try {$callback();} catch(DomainException $e) {check(true,$e->getMessage()); return;} throw new RuntimeException('Expected rejection'); }
function equipment($id,$type,$typeId,$params=[]) { return ['id'=>$id,'type_name'=>$type,'type_id'=>$typeId,'type_params'=>json_encode((object)$params),'detector_location_id'=>1,'deleted_at'=>null]; }
$db->tables['detector_locations']=[['id'=>1,'name'=>'EBA01','deleted_at'=>null],['id'=>2,'name'=>'EBA15','deleted_at'=>null]];
$parent=equipment('2000001','Mini Drawer (Standard)',8,['preserve_me'=>'unchanged','drawer_assembly_profile'=>['configuration'=>'EB','unit'=>'MD3']]);
$db->tables['equipments']=[$parent];
$profile=Assembly::resolve($parent,['drawer_unit'=>'']);
check($profile['unit']==='MD3','An empty request uses the saved unit');
$specs=Layout::specifications($parent['type_name'],$profile);
$entries=[];
foreach($specs as $i=>$slot) {
 $db->tables['type_slots'][]=['id'=>$i+1,'name'=>$slot['name'],'parent_type_id'=>8,'deleted_at'=>null];
 $id=sprintf('PB%05d',$i);
 $type=$slot['types'][0];
 $child=equipment($id,$type,$type==='HV Bus Board'?31:100+$i,isset($slot['position'])?['planned_upgrade_position'=>$slot['position']]:[]);
 $db->tables['equipments'][]=$child;
 $entries[]=['name'=>$slot['name'],'equipment_id'=>$id]+(isset($slot['position'])?['upgrade_position'=>$slot['position']]:[]);
}
$input=['slots'=>$entries,'drawer_unit'=>'MD3'];
$before=serialize($db->tables);
check(Assembly::validateSlots($parent,$input,true)===$profile,'Valid EB MD3 assembly');
check(serialize($db->tables)===$before,'Validation writes nothing and preserves unrelated parameters');
$bad=$input;$bad['slots'][3]['upgrade_position']=31;
rejects(fn()=>Assembly::validateSlots($parent,$bad,true));
$bad=$input;array_pop($bad['slots']);
rejects(fn()=>Assembly::validateSlots($parent,$bad,true));
check(Assembly::validateSlots($parent,['slots'=>[$entries[0]]],false)===$profile,'Scanner can validate one component');
$bad=$input;$bad['slots'][0]['name']='unexpected_hv_slot';
rejects(fn()=>Assembly::validateSlots($parent,$bad,true));
rejects(fn()=>Assembly::resolve($parent,['detector_location_name'=>'EBA15']));
foreach($entries as $i=>$entry) $db->tables['parent_child_equips'][]=['id'=>$i+1,'parent_equip_id'=>$parent['id'],'child_equip_id'=>$entry['equipment_id'],'slot_id'=>$i+1,'deleted_at'=>null];
check(count(Assembly::validateExisting($parent,$profile,true))===9,'Complete EB MD3 has three boards and six blocks');
rejects(fn()=>Assembly::validateSlots($parent,$input,true));
$before=serialize($db->tables);
$db->tables['parent_child_equips'][0]['slot_id']=999;
rejects(fn()=>Assembly::validateExisting($parent,$profile));
$db->tables=unserialize($before);
$db->tables['equipments'][4]['type_params']='{"planned_upgrade_position":32}';
rejects(fn()=>Assembly::validateExisting($parent,$profile));
$db->tables=unserialize($before);
// Hierarchical scan checks the child's physical layout and context.
$super=equipment('3000001','P2 EB Super Drawer',20);
$db->tables['equipments'][]=$super;
$superProfile=Layout::profile($super['type_name'],'EBA01','');
$superSlots=Layout::specifications($super['type_name'],$superProfile);
foreach($superSlots as $i=>$slot) $db->tables['type_slots'][]=['id'=>100+$i,'name'=>$slot['name'],'parent_type_id'=>20,'deleted_at'=>null];
$md3=$superSlots[3];
Assembly::validateChild($super,$superProfile,$md3,$parent,true);
check(true,'Complete child drawer accepted in correct super-drawer unit');
rejects(fn()=>Assembly::validateChild($super,$superProfile,$superSlots[1],$parent,true));
$db->tables['parent_child_equips'][]=['id'=>100,'parent_equip_id'=>$super['id'],'child_equip_id'=>$parent['id'],'slot_id'=>103,'deleted_at'=>null];
check(Assembly::resolve($parent)===$profile,'Mounted mini-drawer context agrees with parent slot');
rejects(fn()=>Assembly::resolve($parent,['drawer_unit'=>'MD2']));
$blank=equipment('2000002','Mini Drawer (Standard)',8);$blank['detector_location_id']=null;
rejects(fn()=>Assembly::resolve($blank));
check(Assembly::resolve($blank,['detector_location_name'=>'EBA15','drawer_unit'=>'MD2'])['configuration']==='SHORT_EBA15_EBC18','Standalone mini-drawer selects short context');
$spacer=equipment('7000001','Micro Drawer 7',12);$spacer['detector_location_id']=null;
$p=Assembly::resolve($spacer);
check($p['unit']==='mD7','Micro-drawer profile requires no module assignment');
check(Assembly::validateExisting($spacer,$p,true)===[],'Empty mD7 is a complete mechanical spacer');
echo "Passed $count assembly service assertions with an in-memory read adapter.\n";
