<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use PDO;

class User extends Entity
{
    protected $datamap = [];
    protected $dates   = ['authTime'];
    protected $casts   = [];

    public function isAuthorized(string $action){

        if ($this->canAll()){
            return true;
        }
        else{
            $actionMethod = sprintf('can%s', ucfirst($action));
            return $this->$actionMethod();
        }
    }

    public function isAdmin(){
        return in_array('admin', $this->roles);
    }

    public function isEditor(){
        return in_array('editor', $this->roles);
    }

    public function isViewer(){
        return in_array('viewer', $this->roles);
    }

    protected function canAll(){
        return $this->isAdmin();
    }

    protected function canDelete(){
        return ($this->isEditor() or $this->isAdmin());
    }

    protected function canRegister(){
        return ($this->isEditor() or $this->isAdmin());
    }

    protected function canEdit(){
        return ($this->isEditor() or $this->isAdmin());
    }

    protected function canAlter(){
        return ($this->isAdmin() or $this->isEditor());
    }

    protected function canUpload(){
        return ($this->isAdmin() or $this->isEditor());
    }

    protected function canDownload(){
        return ($this->isAdmin() or $this->isEditor());
    }

    protected function canView(){
        return true;
    }
}
