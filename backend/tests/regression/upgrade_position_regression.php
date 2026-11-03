<?php
/** Standalone service regression tests with an in-memory adapter; no real database. */
namespace App\Entities {
    class Equipment {
        public array $data;
        public function __construct(array $data) { $this->data = $data; }
    }
}
namespace {
    require __DIR__ . '/../../app/Libraries/EquipmentParams.php';
    require __DIR__ . '/../../app/Libraries/PositionConflict.php';
    require __DIR__ . '/../../app/Libraries/UpgradePosition.php';
    require __DIR__ . '/../../app/Validation/Validator.php';
    use App\Libraries\UpgradePosition as Positions;
    use App\Libraries\PositionConflict;

    final class MemoryResult {
        public function __construct(private array $rows) {}
        public function getRowArray() { return $this->rows[0] ?? null; }
        public function getResultArray() { return $this->rows; }
    }
    final class MemoryDatabase {
        public array $equipment = []; public array $relations = []; public array $history = [];
        public function query(string $sql, array $args) {
            if (str_contains($sql, 'FROM equipments e')) {
                $row = $this->equipment[$args[0]] ?? null;
                return new MemoryResult($row && $row['deleted_at'] === null ? [$row] : []);
            }
            if (str_contains($sql, 'FROM parent_child_equips')) {
                $key = str_contains($sql, 'WHERE parent_equip_id') ? 'parent_equip_id' : 'child_equip_id';
                return new MemoryResult(array_values(array_filter($this->relations,
                    fn($r) => $r[$key] === $args[0] && $r['deleted_at'] === null)));
            }
            throw new \RuntimeException('Unexpected test query');
        }
        public function update($id, $entity) {
            $this->history[] = $this->equipment[$id];
            $this->equipment[$id]['type_params'] = json_encode($entity->data['type_params']);
            $this->equipment[$id]['updated_at'] = (string)(count($this->history) + 1);
            return true;
        }
    }
    $database = new MemoryDatabase();
    function db_connect() { global $database; return $database; }
    function model($name) { global $database; return $database; }
    $count = 0;
    function check($ok, $message) { global $count; ++$count; if (!$ok) throw new \RuntimeException($message); }
    function fails($call, $message) {
        try { $call(); } catch (\DomainException $e) { check(true, $message); return; }
        check(false, $message);
    }
    function row($id, $type, $params) { return ['id' => $id, 'type_name' => $type,
        'type_params' => json_encode($params), 'updated_at' => '1', 'deleted_at' => null]; }

    foreach ([[null,null,null], [0,null,0], [null,0,0], [3,3,3], ['03',3,3], [null,48,48]] as [$a,$b,$want]) {
        check(Positions::choose($a,$b) === $want, 'Automatic resolution');
    }
    check(Positions::choose(0,48,'block') === 0, 'Zero selected from block');
    check(Positions::choose(0,48,'pmt') === 48, 'PMT choice');
    fails(fn() => Positions::choose(0,48), 'Conflict must not silently choose');
    foreach ([-1,49,1.5,true,false,'x','1.5',[],new \stdClass()] as $value) {
        fails(fn() => Positions::normalize($value), 'Reject invalid input');
    }
    foreach ([[],['box_position'=>null],['box_position'=>''],['box_position'=>0]] as $input) {
        $result = \App\Validation\Validator::validate($input, ['box_position' => [Positions::class . '::optionalRule' => []]]);
        check($result['valid'], 'Optional creation validation, including zero');
    }
    $database->equipment['PB00001'] = row('PB00001','PMT Block',[Positions::RESERVED=>12,'keep'=>'block metadata']);
    $database->equipment['0AA0001'] = row('0AA0001','PMT',['box_position'=>9,'phase1_position'=>3,'qe'=>19.1]);
    $empty = Positions::snapshot('PB00001');
    check($empty['pmt_id'] === null && $empty['box_position'] === 12 && $empty['phase1_position'] === null, 'Empty block exposes reservation only');
    try { Positions::prepareAssembly('PB00001','0AA0001',null); throw new \RuntimeException('Expected conflict'); }
    catch (PositionConflict $e) { $conflict = $e->details; }
    check(count($database->history) === 0, 'Conflict does not write');
    $decision = ['pmt_id'=>'0AA0001', 'source'=>'block', 'version'=>$conflict['version']];
    $plan = Positions::prepareAssembly('PB00001','0AA0001',$decision);
    check($plan['value'] === 12, 'Valid choice accepted');
    // Another editor changes the block between the conflict dialog and confirmation.
    Positions::write($database->equipment['PB00001'], [Positions::RESERVED=>10]);
    try { Positions::prepareAssembly('PB00001','0AA0001',$decision); throw new \RuntimeException('Expected stale conflict'); }
    catch (PositionConflict $e) { check($e->details['changed'] && $e->details['block_position']===10, 'Stale choice refreshed'); $conflict=$e->details; }
    $plan = Positions::prepareAssembly('PB00001','0AA0001',['pmt_id'=>'0AA0001','source'=>'pmt','version'=>$conflict['version']]);
    $params = Positions::applyAssembly($plan);
    check($params === null, 'Unchanged PMT avoids a redundant update');
    check(Positions::params($database->equipment['PB00001'])[Positions::RESERVED] === 9, 'Reservation reconciled');
    check(Positions::params($database->equipment['PB00001'])['keep'] === 'block metadata', 'Unrelated JSON preserved');
    $database->relations[] = ['id'=>1,'parent_equip_id'=>'PB00001','child_equip_id'=>'0AA0001','deleted_at'=>null];
    $mounted = Positions::snapshot('PB00001');
    check($mounted['pmt_id'] === '0AA0001' && $mounted['phase1_position'] === 3 && $mounted['box_position'] === 9, 'Mounted values inherited');
    check(Positions::snapshot('0AA0001')['block_id'] === 'PB00001', 'PMT editor resolves its block');
    // Pre-existing assembly with no reservation: keep its actual position on detach.
    $database->equipment['PB00001']['type_params'] = '{"keep":"block metadata"}';
    Positions::retainBeforeDetach('PB00001','0AA0001');
    $database->relations = [];
    check(Positions::snapshot('PB00001')['box_position'] === 9, 'Detach retains position');
    check(Positions::params($database->equipment['0AA0001'])['phase1_position'] === 3, 'Phase1 never transferred');
    check(Positions::params($database->equipment['0AA0001'])['qe'] === 19.1, 'PMT metadata preserved');
    fails(fn() => Positions::prepareAssembly('PB00001','0AA0001',$decision), 'Stale choice after conflict disappears must reopen confirmation');
    // Barcode drawer positions: 1..48, atomic plan, stale snapshot, empty/mounted blocks.
    foreach ([0,49,-1,1.5,true,null,'12','1e1',[]] as $value) {
        fails(fn() => Positions::scanPosition($value), 'Reject invalid scanned position');
    }
    check(Positions::scanPosition(1) === 1 && Positions::scanPosition(48) === 48, 'Inclusive scanner range');
    foreach (Positions::DRAWERS as $drawerType) {
        $database->equipment['2000113'] = row('2000113',$drawerType,[]);
        $snapshot = Positions::snapshot('PB00001');
        $slot = ['equipment_id'=>'PB00001','upgrade_position'=>12,'position_version'=>$snapshot['version']];
        $before = $database->history;
        $plan = Positions::prepareDrawer('2000113',$slot);
        check($database->history === $before, 'Preparing drawer does not write');
        $params = Positions::applyAssembly($plan);
        check($params[Positions::RESERVED] === 12 && $params['keep'] === 'block metadata', 'Reservation preserves JSON');
        $database->update('PB00001',new \App\Entities\Equipment(['type_params'=>$params]));
    }
    $database->relations[] = ['id'=>2,'parent_equip_id'=>'PB00001','child_equip_id'=>'0AA0001','deleted_at'=>null];
    $snapshot = Positions::snapshot('PB00001');
    $slot = ['equipment_id'=>'PB00001','upgrade_position'=>48,'position_version'=>$snapshot['version']];
    $plan = Positions::prepareDrawer('2000113',$slot);
    $params = Positions::applyAssembly($plan);
    $database->update('PB00001',new \App\Entities\Equipment(['type_params'=>$params]));
    check(Positions::snapshot('PB00001')['box_position'] === 48, 'Mounted PMT follows scanned position');
    check(Positions::params($database->equipment['PB00001'])[Positions::RESERVED] === 48, 'Block and PMT consistent');
    check(Positions::params($database->equipment['0AA0001'])['phase1_position'] === 3, 'Phase1 untouched by scan');
    check(Positions::params($database->equipment['0AA0001'])['qe'] === 19.1, 'Other PMT parameters untouched');
    fails(fn() => Positions::prepareDrawer('2000113',$slot), 'Stale scan cannot overwrite');
    fails(fn() => Positions::prepareDrawer('2000113',['equipment_id'=>'PB00001']), 'Position required in drawer');
    fails(fn() => Positions::prepareDrawer('PB00001',['equipment_id'=>'0AA0001','upgrade_position'=>12]), 'Reject position outside drawer/block flow');
    echo "Passed {$count} service assertions (in-memory adapter; no MySQL).\n";
}
