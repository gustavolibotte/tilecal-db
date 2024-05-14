<?php

namespace App\Models;

class Equipment2FileModel extends LogModel
{
    protected $table            = 'equipments_to_files';
    protected $useAutoIncrement = true;
    protected $returnType       =  \App\Entities\Equipment2File::class;
    protected $allowedFields    = ['equipment_id', 'file_id'];

    public function delete($id = null, bool $purge = false)
    {
        $this->db->transException(true)->transBegin();
        $this->builder()
            ->set('user', \Config\Services::user()->getUsername())
            ->where('id', $id)
            ->update();
        parent::delete($id, $purge);
        $this->db->transCommit();
    }
}
