<?php
class UsersModel extends Model {
  protected $table = 'users';

  public function create($data) {
    $sql = "INSERT INTO users (fullname, username, email, password, role)
            VALUES (:fullname, :username, :email, :password, :role)";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute($data);
  }

  public function findByUsername($username) {
    $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :u LIMIT 1");
    $stmt->execute(['u'=>$username]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }
}
