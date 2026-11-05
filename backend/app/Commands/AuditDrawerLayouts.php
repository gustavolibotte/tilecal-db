<?php
namespace App\Commands;

use App\Libraries\DrawerAssembly;
use App\Libraries\DrawerLayout;
use App\Libraries\UpgradePosition;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

final class AuditDrawerLayouts extends BaseCommand
{
    protected $group = 'Assembly';
    protected $name = 'assembly:audit-layouts';
    protected $description = 'Read-only audit of drawer contexts, occupancy, positions and hierarchy.';
    public function run(array $params)
    {
        $db = db_connect();
        $rows = $db->query('SELECT e.id, t.name AS type_name FROM equipments e JOIN equipment_types t ON t.id=e.type_id WHERE e.deleted_at IS NULL ORDER BY e.id')->getResultArray();
        $issues = 0;
        foreach ($rows as $row) {
            if (!DrawerLayout::managed($row['type_name'])) continue;
            try {
                $db->transException(true)->transBegin();
                $equipment = UpgradePosition::equipment($row['id']);
                $profile = DrawerAssembly::resolve($equipment);
                DrawerAssembly::validateExisting($equipment, $profile, true);
                CLI::write($row['id'] . ': OK');
            } catch (\Throwable $e) {
                $issues++; CLI::write($row['id'] . ': REVIEW — ' . $e->getMessage());
            } finally { $db->transRollback(); }
        }
        CLI::write("Audit finished: {$issues} drawers need context, completion or review. No rows written.");
        return $issues ? EXIT_ERROR : EXIT_SUCCESS;
    }
}
