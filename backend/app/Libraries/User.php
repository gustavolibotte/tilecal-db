<?php

namespace App\Library;

class User{

    protected string $username;
    protected array $roles;

    public function __construct(){}

    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function asArray(): array
    {
        return [
            'username' => $this->username,
            'roles' => $this->roles
        ];
    }

    public function setSystemUser(): Self
    {
        $this->setUsername('tilep2db');
        $this->setRoles(['admin', 'viewer', 'editor', 'deleter']);
        return $this;
    }

    public function setTestUser(): Self
    {
        $this->setUsername('anakin.skywalker');
        $this->setRoles(['admin', 'deleter', 'editor', 'viewer', 'sith_lord', 'the_chosen_one']);
        return $this;
    }

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

    public function is(string $role){
        return in_array($role, $this->getRoles());
    }

    public function isAdmin(){
        return in_array('admin', $this->getRoles());
    }

    public function isDeleter(){
        return in_array('deleter', $this->getRoles());
    }

    public function isEditor(){
        return in_array('editor', $this->getRoles());
    }

    public function isViewer(){
        return in_array('viewer', $this->getRoles());
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
        return $this->isViewer();
    }

    public function isSet(){
        return (
            isset($this->username) &&
            isset($this->roles)
        );
    }

}
