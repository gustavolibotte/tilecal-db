<?php
/** Isolated regression tests. In-memory adapters; never connects to a database. */
namespace App\Entities {
    #[\AllowDynamicProperties]
    class Equipment { public function __construct(array $data) { foreach ($data as $k => $v) $this->$k = $v; } }
}
namespace App\Models { class LogModel { protected $db; } }
namespace App\Libraries {
    final class UpgradePosition {
        public const BLOCKS = ['PMT Block', 'PMT Block (Standard)', 'PMT Block (Long Cable)'];
        public const DRAWERS = ['Mini Drawer', 'Mini Drawer (Standard)', 'Mini Drawer (With Patch-Panel)', 'Micro Drawer 4', 'Micro Drawer 5', 'Micro Drawer 6', 'Micro Drawer 7'];
        public static function equipment(string $id): array { return \State::$rows[$id]; }
        public static function params(array $row): array { return $row['type_params'] ?? []; }
        public static function relations(string $id, string $side): array {
            return array_values(array_filter(\State::$links, fn($r) => $r[$side . '_equip_id'] === $id));
        }
        public static function snapshot(string $id): array { return ['box_position' => \State::$rows[$id]['position'] ?? null]; }
        public static function prepareAssembly(...$args): ?array { return null; }
        public static function applyAssembly(?array $plan): ?array { return null; }
    }
}
namespace {
    require __DIR__ . '/../../app/Libraries/DrawerLayout.php';
    require __DIR__ . '/../../app/Libraries/DrawerAssembly.php';
    require __DIR__ . '/../../app/Models/EquipmentModel.php';
    use App\Libraries\DrawerAssembly as Assembly;
    use App\Libraries\DrawerLayout as Layout;
    use App\Libraries\UpgradePosition;
    class State {
        public static array $rows = [], $links = [], $catalog = [], $updates = [];
        public static int $nextSlot = 1;
    }
    class FakeDB {
        private string $table = ''; private array $filters = [];
        public function table($name): self { $q = new self; $q->table = $name; return $q; }
        public function where($key, $value): self { $this->filters[$key] = $value; return $this; }
        public function get(): self { return $this; }
        public function getResultArray(): array {
            $rows = $this->table === 'type_slots' ? State::$catalog : [['id'=>1,'name'=>'EBA14','deleted_at'=>null], ['id'=>2,'name'=>'LBA14','deleted_at'=>null], ['id'=>3,'name'=>'EBA15','deleted_at'=>null]];
            return array_values(array_filter($rows, function($row) {
                foreach ($this->filters as $key=>$value) if (($row[$key] ?? null) != $value) return false;
                return true;
            }));
        }
        public function getRowArray(): ?array { return $this->getResultArray()[0] ?? null; }
        public function transException($v): self { return $this; }
        public function transBegin(): bool { return true; }
        public function transStatus(): bool { return true; }
        public function transCommit(): bool { return true; }
        public function transRollback(): bool { return true; }
    }
    class FakeEquipmentModel extends \App\Models\EquipmentModel {
        public function __construct() { $this->db = db_connect(); }
        public function select($field): self { return $this; }
        public function find($id) { return (object)State::$rows[$id]; }
        public function update($id = null, $data = null): bool {
            // Reproduce the history trigger's failure on a second update in one request.
            if (isset(State::$updates[$id])) throw new \RuntimeException("Column 'action' cannot be null");
            State::$updates[$id] = (array)$data;
            State::$rows[$id] = array_replace(State::$rows[$id], (array)$data);
            return true;
        }
        public function deleteParent(string $childId, bool $updateStatus = false): bool { return true; }
        public function addParent(string $childId, string $parentId, int $slotId, bool $updateStatus = false): bool {
            State::$links[] = ['child_equip_id'=>$childId,'parent_equip_id'=>$parentId,'slot_id'=>$slotId]; return true;
        }
    }
    function db_connect() { return new FakeDB; }
    function model($name) {
        if ($name === 'EquipmentModel') return new FakeEquipmentModel;
        return new class { public function where(...$args) { return $this; } public function first() { return (object)['id'=>2]; } };
    }
    $count = 0;
    function check($ok, $message) { global $count; $count++; if (!$ok) throw new \RuntimeException($message); }
    function rejects($call, string $text) {
        try { $call(); } catch (\DomainException $e) { check(str_contains($e->getMessage(), $text), $e->getMessage()); return; }
        throw new \RuntimeException('Expected rejection: ' . $text);
    }
    function equipment($id, $type, $typeId, $profile): array {
        $row = ['id'=>$id,'type_name'=>$type,'type_id'=>$typeId,'location_id'=>7,'type_params'=>[]];
        State::$rows[$id] = $row;
        foreach (Layout::specifications($type, $profile) as $spec) {
            $found = array_filter(State::$catalog, fn($s)=>$s['parent_type_id']===$typeId && $s['name']===$spec['name']);
            if (!$found) State::$catalog[] = ['id'=>State::$nextSlot++,'parent_type_id'=>$typeId,'name'=>$spec['name'],'deleted_at'=>null];
        }
        return $row;
    }
    function fill($row, $profile): void {
        foreach (Assembly::slots($row, $profile) as $slot) {
            $id = 'C' . str_pad((string)count(State::$rows), 6, '0', STR_PAD_LEFT);
            State::$rows[$id] = ['id'=>$id,'type_name'=>$slot['types'][0], 'type_id'=>$slot['types']===['HV Bus Board']?31:99,'type_params'=>[], 'position'=>$slot['position']??null];
            if (isset($slot['cable'])) {
                foreach (['FENICS', 'Active HV Divider'] as $index => $family) {
                    $part = $id . $index;
                    State::$rows[$part] = ['type_name' => $family . ' (' . $slot['cable'] . ')'];
                    State::$links[] = ['parent_equip_id'=>$id,'child_equip_id'=>$part,'slot_id'=>1000+$index];
                }
            }
            State::$links[] = ['parent_equip_id'=>$row['id'],'child_equip_id'=>$id,'slot_id'=>$slot['slot_id']];
        }
    }
    foreach (Layout::SUPERS as $type=>$config) {
        State::$rows = State::$links = State::$catalog = State::$updates = []; State::$nextSlot = 1;
        $location = $config==='LB'?'LBA14':($config==='EB'?'EBA14':'EBA15');
        $profile = Layout::profile($type, $location, '');
        $parent = equipment('2213810', $type, 15, $profile);
        $entries=[];
        foreach (Assembly::slots($parent,$profile) as $i=>$slot) {
            $cp=['configuration'=>$config,'unit'=>$slot['unit'],'location'=>$location];
            $child=equipment('D'.str_pad((string)$i,6,'0',STR_PAD_LEFT),$slot['types'][0],100+$i,$cp);
            $entry=['name'=>$slot['name'],'equipment_id'=>$child['id']];
            $input=['detector_location_name'=>$location,'slots'=>[$entry]];
            if (Layout::specifications($child['type_name'],$cp)) {
                rejects(fn()=>Assembly::validateSlots($parent,$input,false),'drawer assembly is incomplete. Missing:');
                check(State::$updates===[],'Scan must not write');
            }
            State::$rows[$child['id']]['type_name']='Wrong type';
            rejects(fn()=>Assembly::validateSlots($parent,$input,false),'incompatible equipment type');
            State::$rows[$child['id']]['type_name']=$child['type_name'];
            State::$rows[$child['id']]['detector_location_id']=$location==='EBA14'?2:1;
            rejects(fn()=>Assembly::validateSlots($parent,$input,false),'Detector Location differs');
            unset(State::$rows[$child['id']]['detector_location_id']);
            fill($child,$cp);
            check(Assembly::validateSlots($parent,$input,false)!==null,'Complete child scan succeeds');
            $entries[]=$entry;
        }
        check(Assembly::validateSlots($parent,['detector_location_name'=>$location,'slots'=>$entries],true)===$profile,'Complete super-drawer validates');
        // Attach every complete drawer and repeat the final recursive validation.
        foreach (Assembly::slots($parent,$profile) as $i=>$slot) {
            $cp=['configuration'=>$config,'unit'=>$slot['unit'],'location'=>$location];
            (new FakeEquipmentModel)->updateParent($entries[$i]['equipment_id'],$parent['id'],$slot['slot_id'],null,null,$cp);
        }
        Assembly::persist($parent,$profile,$entries);
        check(count(Assembly::validateExisting(State::$rows[$parent['id']],$profile,true))===count($entries),'Final recursive validation succeeds');
        check(count(State::$updates)===count($entries)+1,'One equipment update per participant');
        // Remaining cases check existing assemblies rather than trying to reattach them.
        $first=$entries[0]['equipment_id'];
        State::$rows[$first]['type_params'][Assembly::PARAM]=['configuration'=>$config,'unit'=>'MD99'];
        rejects(fn()=>Assembly::validateExisting(State::$rows[$parent['id']],$profile,true),'saved drawer profile');
        unset(State::$rows[$first]['type_params'][Assembly::PARAM]);
        $blockLink=array_values(array_filter(State::$links,fn($r)=>$r['parent_equip_id']===$first))[3];
        State::$rows[$blockLink['child_equip_id']]['position']=48;
        rejects(fn()=>Assembly::validateExisting(State::$rows[$parent['id']],$profile,true),'existing Upgrade Position');
    }
    foreach ([41 => 'PB00095', 42 => 'PB00097'] as $position => $id) {
        $block = ['id' => $id, 'type_name' => 'PMT Block', 'type_id' => 2];
        $slot = ['position' => $position, 'label' => "position $position", 'types' => UpgradePosition::BLOCKS, 'cable' => 'Long Cable'];
        State::$links = [];
        foreach (['FENICS', 'Active HV Divider'] as $family) {
            State::$rows[$family] = ['type_name' => "$family (Long Cable)"];
            State::$links[] = ['parent_equip_id' => $id, 'child_equip_id' => $family];
        }
        Assembly::validateChild([], [], $slot, $block, true);
        foreach (['FENICS', 'Active HV Divider'] as $family) {
            State::$rows[$family]['type_name'] = "$family (Short Cable)";
            rejects(fn()=>Assembly::validateChild([], [], $slot, $block, true), "$family (Long Cable)");
            State::$rows[$family]['type_name'] = "$family (Long Cable)";
        }
        array_pop(State::$links);
        rejects(fn()=>Assembly::validateChild([], [], $slot, $block, false), 'Mounted: none');
    }
    // Real updateParent + real profileChanges + real persist, with an audit-sensitive fake model.
    State::$rows = State::$links = State::$catalog = State::$updates = []; State::$nextSlot=1;
    $profile=['configuration'=>'EB','unit'=>'','location'=>'EBA14'];
    $parent=equipment('2213810','P2 EB Super Drawer',15,$profile);
    $cp=['configuration'=>'EB','unit'=>'mD4','location'=>'EBA14'];
    $child=equipment('2213713','Micro Drawer 4',10,$cp);
    State::$rows['2213713']['type_params']=['unrelated'=>['keep'=>true]];
    $slot=Assembly::slots($parent,$profile)[2];
    (new FakeEquipmentModel)->updateParent('2213713','2213810',$slot['slot_id'],null,null,$cp);
    Assembly::persist($parent,$profile,[['name'=>$slot['name'],'equipment_id'=>'2213713']]);
    check(count(State::$updates)===2,'Exactly one UPDATE per child and parent');
    check(State::$rows['2213713']['type_params']['unrelated']===['keep'=>true],'Preserve unrelated metadata');
    check(State::$rows['2213713']['detector_location_id']===1,'Inherit detector location');
    check(State::$rows['2213713']['status_id']===2,'Set installed status');
    check(Assembly::profileChanges(State::$rows['2213713'],$cp)===[],'Unchanged profile produces no update');
    check(count(State::$links)===1,'One attachment relation');
    echo "Passed $count super-drawer assertions (in-memory adapters; no production access).\n";
}
