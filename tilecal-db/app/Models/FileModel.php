<?php

namespace App\Models;

use CodeIgniter\Files\Exceptions\FileNotFoundException;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Validation\ValidationInterface;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

class FileModel extends LogModel
{
    protected $table            = 'files';
    protected $useAutoIncrement = true;
    protected $returnType       = \App\Entities\File::class;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'description', 'path'];
    protected bool $copyFile;

    public function __construct(?ConnectionInterface $db = null, ?ValidationInterface $validation = null, bool $copyFile=false)
    {
        parent::__construct($db, $validation);
        $this->copyFile = $copyFile;
    }

    public function initialize(){
        parent::initialize();
        helper(['app_array']);
        $this->beforeInsert[] = 'moveFile';
        $this->beforeUpdate[] = 'moveFile';
    }

    public function fullQuery(BaseBuilder|Model $queryBuilder=null): BaseBuilder|Model
    {
        if (is_null($queryBuilder)){
            $queryBuilder = $this;
        }
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
        $equipments2FilesModel = model('Equipments2FilesModel');
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

    protected function moveFile(array $data): array
    {
        helper(['app_array', 'path', 'filesystem']);
        $appPaths = config('Paths');
        $filepath = dot_search_with_default('path', $data['data'], null);
        if (is_null($filepath) || str_starts_with($filepath, $appPaths->uploadedFilesDirectory)){
            return $data;
        }
        if (!get_file_info($filepath)){
            throw new FileNotFoundException(
                sprintf(
                    "The path %s doesn't exist.",
                    $filepath
                )
            );
        }
        $time = \Config\Services::time();
        $now = $time->getRequestTimestamp()->format('Y_m_d_H_i_s_u');
        $authenticator = \Config\Services::authenticator();
        $username = $authenticator->getUser()->username;
        $file = new \CodeIgniter\Files\File($filepath);
        $extension = sprintf('.%s', $file->guessExtension());
        if (!str_ends_with($data['data']['name'], $extension)){
            $data['data']['name'] .= $extension;
        }
        $newFilename = sprintf(
            '%s_%s_%s',
            $now,
            $username,
            $data['data']['name']
        );
        $data['data']['path'] = joinPaths([$appPaths->uploadedFilesDirectory, $newFilename]);
        if ($this->copyFile){
            copy($filepath, $data['data']['path']);
        } else{
            $file->move($appPaths->uploadedFilesDirectory, $newFilename);
        }
        return $data;
    }
}
