<?php
require_once __DIR__ . "/../models/PrayerPoint.php";
class PrayerPointController {
    private $model;
    public function __construct(PDO $pdo){ $this->model = new PrayerPoint($pdo); }
    public function index(){ return $this->model->all(); }
    public function store($text){ return $this->model->create($text); }
    public function delete($id){ return $this->model->delete($id); }
}
