<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

class FileModel extends LogModel
{
    protected $table            = 'files';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\File::class;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'description', 'path'];
    
    public function initialize(){
        parent::initialize();
        $this->afterDelete[] = 'deleteRelationsCallback';
    }

    public function fullQuery(BaseBuilder|Model $queryBuilder=null): BaseBuilder|Model
    {
        if (is_null($queryBuilder)){
            $queryBuilder = $this;
        }
        return $queryBuilder;
    }

    public function joinEquipments(BaseBuilder|Model $queryBuilder=null): BaseBuilder|Model
    {
        if (is_null($queryBuilder)){
            $queryBuilder = $this;
        }
        $queryBuilder = $queryBuilder
            ->join('equipments_to_files', 'files.id = equipments_to_files.file_id', 'left');
        return $queryBuilder;
    }

    /**
     * Returns an array with all the related files to a specific equipment given its id
     *
     * @param string|int $id equipment id to get related files from
     * 
     * @param array array with related files entities
     */
    public function getEquipmentRelated(string|int $id): array
    {
        $query = $this->select('files.id, files.name, files.description, files.created_at, files.deleted_at')
                       ->join('equipments_to_files', 'files.id = equipments_to_files.file_id')
                       ->where('equipments_to_files.equipment_id', $id);
        if ($this->tempUseSoftDeletes){
            $query = $query->where('equipments_to_files.deleted_at IS NULL');
        }
        $result = $query->findAll();
        return $result;
    }

    public function deleteRelations(int|string|array $id): bool
    {
        $equipments2FilesModel = model('Equipment2FileModel');
        $whereClause = (is_array($id))? 'whereIn': 'where';
        $relations = $equipments2FilesModel
            ->select('id')
            ->$whereClause('file_id', $id)
            ->findAll();
        foreach ($relations as $relation){
            $equipments2FilesModel->delete($relation->id, true);
        }
        return true;
    }

    protected function deleteRelationsCallback(array $data): array
    {
        $this->deleteRelations($data['id']);
        return $data;
    }

}
