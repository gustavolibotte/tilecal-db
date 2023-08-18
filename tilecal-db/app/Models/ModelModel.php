<?php

namespace App\Models;

use App\Entities\OperationLog;

class ModelModel extends LogModel
{
    protected $table            = 'models';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\Model::class;
    protected $allowedFields    = ['name', 'in_detector', 'system', 'label', 'params', 'description'];

    // Custom
    protected $allowedModelParams = [];

    protected function getAllowedModelParams(){
        if (empty($this->allowedModelParams)){
            $models = $this->findAll();
            foreach ($models as $model){
                $this->allowedModelParams[$model->name] = $model->params;
            }
        }
        return $this->allowedModelParams;
    }
}
