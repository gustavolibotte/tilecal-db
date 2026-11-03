<?php
/** Run against the actual CodeIgniter Entity/casts, not an Entity substitute. */
$framework = $argv[1] ?? __DIR__ . '/../../vendor/codeigniter4/framework/system';
if (!is_file($framework . '/Entity/Entity.php')) {
    fwrite(STDERR, "CodeIgniter system directory not found. Install backend dependencies or pass its path.\n");
    exit(1);
}
spl_autoload_register(static function ($class) use ($framework) {
    foreach (['CodeIgniter\\' => $framework . '/', 'App\\' => __DIR__ . '/../../app/'] as $prefix => $base) {
        if (str_starts_with($class, $prefix)) {
            $path = $base . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
            if (is_file($path)) require $path;
        }
    }
});
use App\Entities\Equipment;
use App\Libraries\EquipmentParams;
use App\Libraries\UpgradePosition;
$count = 0;
function check($condition, string $message): void {
    global $count; $count++;
    if (!$condition) throw new RuntimeException($message);
}
// Build a realistic fixture without ambiguous PHP/JSON escaping.
$base = json_encode(['phase1_position'=>0,'box_position'=>12,'qe'=>19.1,
    'note'=>'a "quote"', 'unknown'=>(object)['empty'=>(object)[], 'items'=>[1,null,false]]]);
foreach (['{}', '[]', $base] as $raw) {
    $expected = EquipmentParams::decode($raw);
    for ($layer = 0; $layer < 5; $layer++) {
        // Reproduce the previous PUT path using the real framework casts.
        $read = new Equipment(['type_params'=>$raw]);
        check(is_string($read->type_params), 'Constructor encodes an already encoded string');
        $broken = new Equipment(['type_params'=>$read->type_params]);
        check(json_decode($broken->toRawArray()['type_params']) === $raw, 'Old PUT adds one layer');
        check(json_encode(EquipmentParams::decode($raw)) === json_encode($expected), 'Wrapped values recovered');
        check(json_encode(UpgradePosition::params(['type_params'=>$raw])) === json_encode($expected), 'Positions use same decoder');
        $row = ['type_params'=>$raw, 'comments'=>'keep']; EquipmentParams::present($row);
        check(is_object($row['type_params']) && $row['comments'] === 'keep', 'API exposes an object');
        $payload = EquipmentParams::editPayload(['id'=>'PB00149','comments'=>'edited'], ['comments'=>'edited'], [], $raw);
        $entity = new Equipment($payload);
        check(!array_key_exists('type_params',$entity->toRawArray()), 'Unrelated edit must not rewrite JSON');
        $payload = EquipmentParams::editPayload(['id'=>'0AA0001','qe'=>20.5], ['qe'=>20.5], ['qe'], $raw);
        $entity = new Equipment($payload);
        $saved = json_decode($entity->toRawArray()['type_params']);
        check(is_object($saved) && $saved->qe === 20.5, 'Edited parameters encoded once');
        if (isset($expected['box_position'])) {
            check($saved->box_position === 12 && $saved->phase1_position === 0, 'Positions preserved');
            check(json_encode($saved->unknown) === json_encode($expected['unknown']), 'Nested data preserved');
        }
        $raw = json_encode($raw);
    }
}
foreach (['not JSON','"not JSON"','123','true','null','[1,2,3]'] as $raw) {
    try { EquipmentParams::decode($raw); throw new RuntimeException('Invalid data accepted'); }
    catch (DomainException $e) { check(true, 'Invalid data refused'); }
    $row=['type_params'=>$raw];EquipmentParams::present($row);
    check(isset($row['type_params_error']) && $row['type_params'] === null, 'Malformed data reported');
    $payload=EquipmentParams::editPayload(['comments'=>'ok'],['comments'=>'ok'],[],$raw);
    check(!array_key_exists('type_params',$payload),'Malformed JSON not erased by unrelated edit');
}
$payload=EquipmentParams::editPayload(['qe'=>null,'comments'=>'ok'],['comments'=>'ok'],['qe'],$base);
check(!isset($payload['type_params']) && !array_key_exists('qe',$payload),'Omitted parameters never cleared');
echo "Passed {$count} parameter/real-Entity assertions. No database access.\n";
