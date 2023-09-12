<?php

namespace App\Models;

class TypesHierarchyModel extends LogModel
{
    protected $table            = 'types_hierarchy';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\TypeHierarchy::class;
    protected $allowedFields    = ['parent_type_id', 'children_type_ids', 'count'];
}
