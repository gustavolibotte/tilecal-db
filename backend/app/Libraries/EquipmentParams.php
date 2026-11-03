<?php
namespace App\Libraries;

/** Decode legacy JSON wrappers in memory only. Never infer or discard parameter values. */
final class EquipmentParams
{
    public static function decode($value): array
    {
        if ($value === null) return [];
        for ($depth = 0; is_string($value); $depth++) {
            if ($depth >= 16) throw new \DomainException('Too many JSON layers in equipment parameters.');
            try {
                $value = json_decode($value, false, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw new \DomainException('Equipment parameters contain invalid JSON.', 0, $e);
            }
        }
        if ($value instanceof \stdClass) return get_object_vars($value);
        // [] was the historic representation of equipment without parameters.
        if (is_array($value) && ($value === [] || !array_is_list($value))) return $value;
        throw new \DomainException('Equipment parameters must contain a JSON object.');
    }

    /** Preserve the raw column unless an actual parameter was submitted and validated. */
    public static function editPayload(array $valid, array $submitted, array $names, $raw): array
    {
        $changes = [];
        foreach ($names as $name) {
            if (!array_key_exists($name, $valid)) continue;
            if (array_key_exists($name, $submitted)) $changes[$name] = $valid[$name];
            unset($valid[$name]);
        }
        unset($valid['type_params']);
        if ($changes) $valid['type_params'] = array_replace(self::decode($raw), $changes);
        return $valid;
    }

    /** Presentation does not write back to either current rows or historical rows. */
    public static function present(array &$row): void
    {
        try {
            $row['type_params'] = (object)self::decode($row['type_params'] ?? null);
        } catch (\DomainException $e) {
            $row['type_params'] = null;
            $row['type_params_error'] = $e->getMessage();
        }
    }
}
