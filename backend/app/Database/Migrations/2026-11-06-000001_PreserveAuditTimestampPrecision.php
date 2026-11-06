<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

/** Preserve the installed routine's definer, characteristics and history policy. */
class PreserveAuditTimestampPrecision extends Migration
{
    public function up()
    {
        $this->replacePrecision(false);
    }

    public function down()
    {
        $this->replacePrecision(true);
    }

    private function replacePrecision(bool $reverse): void
    {
        $name = 'get_action_from_update';
        $row = $this->db->query("SHOW CREATE FUNCTION `get_action_from_update`")->getRowArray();
        $original = $row['Create Function'] ?? null;
        if (!is_string($original)) throw new RuntimeException('SHOW CREATE FUNCTION privilege required.');
        $source = $reverse ? 'TIMESTAMP\\(6\\)' : 'TIMESTAMP(?!\\s*\\()';
        $target = $reverse ? 'TIMESTAMP' : 'TIMESTAMP(6)';
        $pattern = '/(\\b(?:new|old)_(?:deleted|updated)_at`?\\s+)' . $source . '(?=\\s*[,\\)])/i';
        $new = preg_replace($pattern, '${1}' . $target, $original, -1, $count);
        if ($count === 0 && preg_match_all('/\b(?:new|old)_(?:deleted|updated)_at`?\s+' . preg_quote($target, '/') . '(?=\s*[,\)])/i', $original) === 4) return;
        if ($count !== 4) throw new RuntimeException('Unexpected audit routine signature. Review SHOW CREATE FUNCTION.');
        // The DBA verified before deployment that no routine-specific grants exist.
        $session = $this->db->query(
            'SELECT @@SESSION.sql_mode AS sql_mode, @@SESSION.character_set_client AS character_set_client,
                    @@SESSION.collation_connection AS collation_connection, @@collation_database AS db_collation'
        )->getRowArray();
        try {
            $this->setContext($row);
            $this->replaceRoutine($name, $new, $original);
        } finally {
            $this->setContext($session);
        }
    }

    private function setContext(array $settings): void
    {
        $this->db->query('SET SESSION sql_mode = ?, SESSION character_set_client = ?, SESSION collation_connection = ?',
            [$settings['sql_mode'], $settings['character_set_client'], $settings['collation_connection']]);
    }

    private function replaceRoutine(string $name, string $new, string $original): void
    {
        // Compile the definition before replacing the live routine. This also checks definer permissions.
        $probe = $name . '_precision_check';
        $probeSql = preg_replace('/(FUNCTION\\s+)`get_action_from_update`/i', '${1}`' . $probe . '`', $new, 1, $n);
        if ($n !== 1) throw new RuntimeException('Could not identify the audit routine name.');
        $this->db->query($probeSql);
        try {
            $metadata = $this->db->query(
                'SELECT DEFINER, SECURITY_TYPE, SQL_MODE, CHARACTER_SET_NAME, COLLATION_NAME,
                        CHARACTER_SET_CLIENT, COLLATION_CONNECTION, IS_DETERMINISTIC, SQL_DATA_ACCESS
                 FROM information_schema.ROUTINES WHERE ROUTINE_SCHEMA=DATABASE()
                 AND ROUTINE_NAME IN (?,?)', [$name, $probe]
            )->getResultArray();
            if (count($metadata) !== 2 || $metadata[0] !== $metadata[1]) {
                throw new RuntimeException('Routine characteristics changed. Review the definition with the DBA.');
            }
        } finally {
            $this->db->query('DROP FUNCTION `get_action_from_update_precision_check`');
        }
        $this->db->query('DROP FUNCTION `get_action_from_update`');
        try {
            $this->db->query($new);
        } catch (\Throwable $e) {
            $this->db->query($original);
            throw $e;
        }
    }
}
