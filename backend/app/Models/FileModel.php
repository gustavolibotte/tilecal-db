<?php

namespace App\Models;

use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Files\File;
use CodeIgniter\Validation\ValidationInterface;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

class FileModel extends LogModel
{
    protected $table            = 'files';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\File::class;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'description', 'fileObj', 'path', 'copy'];
    protected bool $copyFile;

    public function __construct(?ConnectionInterface $db = null, ?ValidationInterface $validation = null, bool $copyFile=false)
    {
        parent::__construct($db, $validation);
        $this->copyFile = $copyFile;
    }

    public function initialize(){
        parent::initialize();
        helper(['app_array']);
        $this->beforeInsert[] = 'treatFileObj';
        $this->beforeUpdate[] = 'treatFileObj';
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

    protected function treatFileObj(array $data): array
    {
        helper(['app_array', 'path', 'filesystem']);
        $fileObj = remove_value('fileObj', $data['data'], false);
        $copy = remove_value('copy', $data['data'], false);
        
        if (!($fileObj instanceof File)){
            return $data;
        }
        // $data = $this->checkExtension($data, $fileObj);
        $appPaths = config('Paths');
        $newName = $fileObj->getRandomName();
        $newPath = joinPaths([
            $appPaths->uploadedFilesDirectory,
            $newName
        ]);
        if ($copy){
            copy($fileObj->getRealPath(), $newPath);
        } else{
            $fileObj->move($appPaths->uploadedFilesDirectory, $newName);
        }
        $data['data']['path'] = $newPath;
        return $data;
    }

    protected function checkExtension(array $data, File $fileObj): array
    {
        if (!array_key_exists('name', $data['data'])){
            return $data;
        }
        $extension = '.' . $fileObj->guessExtension();
        if (!str_ends_with($data['data']['name'], $extension)){
            $data['data']['name'] .= $extension;
        }
        return $data;
    }

}
