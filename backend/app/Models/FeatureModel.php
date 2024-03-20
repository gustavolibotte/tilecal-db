<?php

namespace App\Models;
use App\Entities\Feature;

class FeatureModel extends BaseModel
{
    protected $table            = 'features';
    protected $useAutoIncrement = true;
    protected $returnType       = Feature::class;
    protected $allowedFields    = ['name', 'status'];

    private array $cache = [];

    public function findName(string $name): Feature
    {
        if (array_key_exists($name, $this->cache)){
            return $this->cache[$name];
        } else{
            $feature = $this->where('name', $name)->first();
            $this->cache[$name] = $feature;
            return $feature;
        }
    }
}
