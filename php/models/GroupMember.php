<?php
// models/GroupMember.php
class GroupMember {
    private $pdo;
    public function __construct(PDO $pdo){ $this->pdo = $pdo; }

    public function all() {
        $sql = "SELECT gm.*, g.name AS group_name, m.name AS member_name
                FROM group_members gm
                JOIN groups g ON g.id = gm.group_id
                JOIN members m ON m.id = gm.member_id
                ORDER BY gm.id DESC";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function create($group_id, $member_id) {
        $stmt = $this->pdo->prepare("INSERT INTO group_members (group_id, member_id) VALUES (?, ?)");
        return $stmt->execute([$group_id, $member_id]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM group_members WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
