<?php

namespace App\Entities;

class User extends BaseEntity
{
    protected $datamap = [];
    protected $dates   = ['authTime'];
    protected $casts   = [];

    public function isAuthorized(?string $action){
        
        if (empty($action)){
            return false;
        } else if($this->canAll()){
            return true;
        } else{
            $actionMethod = sprintf('can%s', ucfirst($action));
            return $this->$actionMethod();
        }
    }

    public function isAdmin(){
        return in_array('admin', $this->roles);
    }

    public function isDeleter(){
        return in_array('deleter', $this->roles);
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
        return $this->isDeleter();
    }

    protected function canRegister(){
        return $this->isEditor();
    }

    protected function canEdit(){
        return $this->isEditor();
    }

    protected function canAlter(){
        return $this->isEditor();
    }

    protected function canUpload(){
        return $this->isEditor();
    }

    protected function canDownload(){
        return $this->isViewer();
    }

    protected function canView(){
        return true;
    }
}
