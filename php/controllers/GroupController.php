<?php
require_once __DIR__ . "/../models/Group.php";
class GroupController {
    private $model;
    public function __construct(PDO $pdo){ $this->model = new Group($pdo); }
    public function index(){ return $this->model->all(); }
    public function store($name){ return $this->model->create($name); }
    public function update($id, $name){ return $this->model->update($id, $name); }
    public function delete($id){ return $this->model->delete($id); }
    public function find($id){ return $this->model->find($id); }
}
