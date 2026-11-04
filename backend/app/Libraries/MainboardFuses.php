<?php
namespace App\Libraries;

final class MainboardFuses
{
    public const FIELD = 'fuses_removed';

    public static function optionalRule(string $field, array &$data, string &$error, array $params): bool
    {
        if (!array_key_exists($field, $data) || $data[$field] === null || is_bool($data[$field])) return true;
        $error = 'Select Yes, No, or leave the fuse information empty.';
        return false;
    }

    public static function parameterNames(string $typeName, array $names): array
    {
        if ($typeName === 'Mainboard' && !in_array(self::FIELD, $names, true)) $names[] = self::FIELD;
        return $names;
    }
}
