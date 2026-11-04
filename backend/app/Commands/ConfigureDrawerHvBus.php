<?php
namespace App\Commands;

use App\Libraries\DrawerSlotPlan;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

final class ConfigureDrawerHvBus extends BaseCommand
{
    protected $group = 'Assembly';
    protected $name = 'assembly:configure-hv-bus';
    protected $description = 'Preview or add HV Bus Board slots without changing equipment or assemblies.';
    protected $usage = 'assembly:configure-hv-bus [--apply --user username]';
    protected $options = ['--apply'=>'Apply the additive plan.', '--user'=>'Audit username (required with --apply).'];

    public function run(array $params)
    {
        $apply = CLI::getOption('apply') !== null;
        $user = CLI::getOption('user');
        if ($apply && (!is_string($user) || !preg_match('/^[A-Za-z0-9_.-]{1,10}$/D', $user))) {
            CLI::error('Provide --user with the audit username (1–10 characters).'); return EXIT_ERROR;
        }
        $db = db_connect();
        try {
            $db->transException(true)->transBegin();
            // Serialize configuration runs; all reads and inserts share this transaction.
            $types = $db->query('SELECT * FROM equipment_types ORDER BY id FOR UPDATE')->getResultArray();
            $slots = $db->query('SELECT * FROM type_slots ORDER BY id FOR UPDATE')->getResultArray();
            $allowed = $db->query('SELECT * FROM allowed_type_slots ORDER BY id FOR UPDATE')->getResultArray();
            $plan = DrawerSlotPlan::plan($types, $slots, $allowed);
            foreach ($plan as $item) CLI::write(strtoupper($item['action']) . ' ' . $item['type'] . ': ' . $item['name']);
            if (!$apply) {
                $db->transRollback(); CLI::write('Preview only. No rows written.'); return EXIT_SUCCESS;
            }
            $count = 0;
            foreach ($plan as $item) {
                if ($item['action'] !== 'add') continue;
                $stamp = $db->query('SELECT CURRENT_TIMESTAMP(6) AS stamp')->getRowArray()['stamp'];
                $audit = ['user'=>$user, 'created_at'=>$stamp, 'updated_at'=>$stamp];
                if (!$db->table('type_slots')->insert($audit + ['name'=>$item['name'], 'parent_type_id'=>$item['parent_type_id']])) {
                    throw new \RuntimeException('Could not insert slot.');
                }
                $slotId = $db->insertID();
                if (!$db->table('allowed_type_slots')->insert($audit + ['slot_id'=>$slotId, 'allowed_type_id'=>31])) {
                    throw new \RuntimeException('Could not insert allowed type.');
                }
                $count++;
            }
            if (!$db->transStatus() || !$db->transCommit()) throw new \RuntimeException('Could not commit slot configuration.');
            CLI::write("Added {$count} slots and their permissions. Existing equipment and assemblies were not modified.");
            return EXIT_SUCCESS;
        } catch (\Throwable $e) {
            $db->transRollback(); CLI::error($e->getMessage()); return EXIT_ERROR;
        }
    }
}
