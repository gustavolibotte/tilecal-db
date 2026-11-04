<?php
namespace App\Libraries;

/** Read-only search query builder. All identifiers are fixed; all user text is bound. */
final class EquipmentSearch
{
    public const GROUPS = ['identifiers', 'type', 'location', 'status', 'owner', 'comments'];
    public const MODES = ['contains', 'all_words', 'word', 'prefix', 'exact'];
    public const CATEGORIES = ['equipment', 'location', 'detector', 'status', 'institute'];

    public static function options(array $input): array
    {
        foreach ($input as $value) if (!is_scalar($value) && $value !== null) throw new \InvalidArgumentException('Invalid search parameters.');
        $q = trim((string)($input['q'] ?? ''));
        if (mb_strlen($q) > 200) throw new \InvalidArgumentException('Use at most 200 characters.');
        $mode = $input['mode'] ?? 'contains';
        $category = $input['category'] ?? 'equipment';
        $sort = $input['sort'] ?? 'relevance';
        $fields = isset($input['fields']) ? explode(',', $input['fields']) : self::GROUPS;
        if (!in_array($mode, self::MODES, true) || !in_array($category, self::CATEGORIES, true) ||
            !in_array($sort, ['relevance', 'id', 'updated'], true) || !$fields || array_diff($fields, self::GROUPS)) {
            throw new \InvalidArgumentException('Invalid search mode, category, fields or ordering.');
        }
        $page = filter_var($input['page'] ?? 1, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1,'max_range'=>10000]]);
        if ($page === false) throw new \InvalidArgumentException('Invalid page.');
        $terms = $mode === 'all_words' ? preg_split('/\s+/u', $q, -1, PREG_SPLIT_NO_EMPTY) : [$q];
        if (count($terms) > 10) throw new \InvalidArgumentException('Use at most 10 words in All words mode.');
        return compact('q', 'mode', 'category', 'sort', 'fields', 'page', 'terms');
    }

    public static function definition(string $category): array
    {
        if ($category === 'equipment') return [
            'from'=>'equipments e LEFT JOIN equipment_types t ON t.id=e.type_id LEFT JOIN locations l ON l.id=e.location_id LEFT JOIN detector_locations d ON d.id=e.detector_location_id LEFT JOIN statuses s ON s.id=e.status_id LEFT JOIN institutes i ON i.id=e.owner_id',
            'id'=>'e.id', 'deleted'=>'e.deleted_at', 'updated'=>'e.updated_at',
            'select'=>'e.id AS label, t.name AS description, e.updated_at, e.serial, l.name AS location, d.name AS detector_location, s.name AS status',
            'fields'=>[
                'id'=>['e.id','ID','identifiers'], 'serial'=>['e.serial','Serial','identifiers'],
                'type'=>['t.name','Type','type'], 'location'=>['l.name','Location','location'],
                'location_description'=>['l.description','Location description','location'],
                'detector'=>['d.name','Detector Location','location'], 'status'=>['s.name','Status','status'],
                'status_description'=>['s.description','Status description','status'],
                'owner'=>['i.name','Owner','owner'], 'owner_mne'=>['i.mne','Owner code','owner'],
                'comments'=>['e.comments','Comments','comments']
            ]
        ];
        $definitions = [
            'location'=>['locations','name','description','location','Location'],
            'detector'=>['detector_locations','name','name','location','Detector Location'],
            'status'=>['statuses','name','description','status','Status'],
            'institute'=>['institutes','mne','name','owner','Institute']
        ];
        [$table,$name,$description,$group,$label] = $definitions[$category];
        return ['from'=>"$table e", 'id'=>"e.$name", 'deleted'=>'e.deleted_at', 'updated'=>'e.updated_at',
            'select'=>"e.$name AS label, e.$description AS description, e.updated_at",
            'fields'=>['name'=>["e.$name",$label,$group], 'description'=>["e.$description",'Description',$group]]];
    }

    public static function condition(string $column, string $term, string $mode, array &$bindings): string
    {
        if (in_array($column, ['e.id', 'e.serial'], true) && preg_match('/^20[A-Z0-9]{12}$/iD', $term)) {
            $original = self::condition('CAST(' . $column . ' AS CHAR)', $term, $mode, $bindings);
            $suffix = self::condition($column, substr($term, 7), $mode, $bindings);
            return "($original OR $suffix)";
        }
        $column = "LOWER(COALESCE($column, ''))";
        $term = mb_strtolower($term);
        if ($mode === 'exact') { $bindings[] = $term; return "$column = ?"; }
        if ($mode === 'word') {
            // Unicode letters/numbers define a word; punctuation is treated literally.
            $bindings[] = '(^|[^\\p{L}\\p{N}_])' . preg_quote($term, '/') . '([^\\p{L}\\p{N}_]|$)';
            return "REGEXP_LIKE($column, ?, 'c')";
        }
        $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term);
        $bindings[] = ($mode === 'prefix' ? '' : '%') . $escaped . '%';
        return "$column LIKE ? ESCAPE '!'";
    }

    public static function plan(array $options, string $category): array
    {
        $definition = self::definition($category);
        $fields = array_filter($definition['fields'], fn($f)=>in_array($f[2], $options['fields'], true));
        $whereBindings = []; $clauses = [];
        foreach ($options['terms'] as $term) {
            $parts = [];
            foreach ($fields as $field) $parts[] = self::condition($field[0], $term, $options['mode'], $whereBindings);
            $clauses[] = $parts ? '(' . implode(' OR ', $parts) . ')' : '0=1';
        }
        $where = $definition['deleted'] . ' IS NULL AND ' . ($clauses ? implode(' AND ', $clauses) : '0=1');
        $select = [$definition['select']]; $selectBindings = []; $scores = [];
        foreach ($fields as $key=>$field) {
            $parts = [];
            foreach ($options['terms'] as $term) $parts[] = self::condition($field[0], $term, $options['mode'], $selectBindings);
            $select[] = '(' . implode(' OR ', $parts) . ") AS match_$key";
            $select[] = $field[0] . " AS value_$key";
        }
        foreach ($fields as $key=>$field) {
            $exact = self::condition($field[0], $options['q'], 'exact', $selectBindings);
            $prefix = self::condition($field[0], $options['q'], 'prefix', $selectBindings);
            $weight = $key === 'id' ? 1000 : ($key === 'serial' ? 500 : 100);
            $scores[] = "CASE WHEN $exact THEN $weight WHEN $prefix THEN 20 ELSE 0 END";
        }
        $select[] = ($scores ? implode(' + ', $scores) : '0') . ' AS relevance';
        $order = ['relevance'=>'relevance DESC, ' . $definition['updated'] . ' DESC',
            'id'=>$definition['id'] . ' ASC', 'updated'=>$definition['updated'] . ' DESC'][$options['sort']];
        return [
            'count_sql'=>"SELECT COUNT(*) AS total FROM {$definition['from']} WHERE $where",
            'count_bindings'=>$whereBindings,
            'sql'=>'SELECT ' . implode(', ', $select) . " FROM {$definition['from']} WHERE $where ORDER BY $order, {$definition['id']} ASC",
            'bindings'=>array_merge($selectBindings, $whereBindings), 'fields'=>$fields
        ];
    }

    public static function snippet(string $value, array $terms, string $mode = 'contains'): string
    {
        $value = preg_replace('/\s+/u', ' ', $value);
        $position = false;
        foreach ($terms as $term) {
            if ($mode === 'word') {
                $pattern = '/(?<![\\p{L}\\p{N}_])' . preg_quote($term, '/') . '(?![\\p{L}\\p{N}_])/iu';
                $found = preg_match($pattern, $value, $match, PREG_OFFSET_CAPTURE)
                    ? mb_strlen(substr($value, 0, $match[0][1])) : false;
            } else {
                $found = mb_stripos($value, $term);
            }
            if ($found !== false && ($position === false || $found < $position)) $position = $found;
        }
        $start = max(0, (int)$position - 60);
        return ($start ? '…' : '') . mb_substr($value, $start, 240) . (mb_strlen($value) > $start + 240 ? '…' : '');
    }

    public static function result(array $row, array $plan, array $options): array
    {
        $matches = [];
        foreach ($plan['fields'] as $key=>$field) if (!empty($row['match_' . $key])) {
            $matches[] = ['field'=>$field[1], 'text'=>self::snippet((string)$row['value_' . $key], $options['terms'], $options['mode'])];
        }
        $label = (string)$row['label'];
        $urls = ['equipment'=>'/equipment/' . rawurlencode($label),
            'location'=>'/equipment?location_name=' . rawurlencode($label),
            'detector'=>'/equipment?detector_location_name=' . rawurlencode($label),
            'status'=>'/equipment?status_name=' . rawurlencode($label),
            'institute'=>'/equipment?owner_mne=' . rawurlencode($label)];
        return ['type'=>$options['category'], 'label'=>$label, 'description'=>$row['description'],
            'url'=>$urls[$options['category']], 'matches'=>$matches,
            'serial'=>$row['serial'] ?? null, 'location'=>$row['location'] ?? null,
            'detector_location'=>$row['detector_location'] ?? null, 'status'=>$row['status'] ?? null];
    }
}
