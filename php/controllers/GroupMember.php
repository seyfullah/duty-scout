<?php
require_once __DIR__ . "/../models/GroupMember.php";
class MemberController {
    private $model;
    public function __construct(PDO $pdo){ $this->model = new GroupMember($pdo); }
    public function index(){ return $this->model->all(); }
    public function store($group_id, $member_id){ return $this->model->create($group_id, $member_id); }
    public function delete($id){ return $this->model->delete($id); }
}
