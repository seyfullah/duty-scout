<?php
// models/Member.php
class Member {
    private $pdo;
    public function __construct(PDO $pdo){ $this->pdo = $pdo; }

    public function all() {
        $stmt = $this->pdo->query("SELECT * FROM members ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function find($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM members WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($name) {
        $stmt = $this->pdo->prepare("INSERT INTO members (name) VALUES (?)");
        return $stmt->execute([$name]);
    }

    public function update($id, $name) {
        $stmt = $this->pdo->prepare("UPDATE members SET name = ? WHERE id = ?");
        return $stmt->execute([$name, $id]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM members WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
