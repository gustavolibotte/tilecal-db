<?php

namespace App\Models;
use App\Entities\OperationLog;

class Equipment2FileModel extends LogModel
{
    protected $table            = 'equipments_to_files';
    protected $useAutoIncrement = true;
    protected $returnType       =  \App\Entities\Equipment2File::class;
    protected $allowedFields    = ['equipment_id', 'file_id'];

    /**
     * Adds filename to log
     *
     * @param int|string $id  File id
     * @param OperationLog $operationLog operationLog to add name
     * 
     * @return OperationLog modified OperationLog with name
     */
    protected function addName(int|string $id, OperationLog $operationLog): OperationLog
    {   
        $fileModel = model('FileModel');
        $file = $fileModel
            ->select('name')
            ->find($id);
        $changes = $operationLog->changes;
        $changes['file_name'] = $file->name;
        $operationLog->changes = $changes;
        return $operationLog;
    }
}
