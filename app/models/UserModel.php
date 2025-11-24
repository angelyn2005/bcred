<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class UserModel extends Model
{
    protected $table = 'users';

    // Kunin lahat ng users
    public function getAllUsers()
    {
        return $this->db->table($this->table)->get_all();
    }

    public function getUserById($id)
    {
        return $this->db->table($this->table)->where('id', $id)->get();
    }

    public function deleteUser($id)
    {
        return $this->db->table($this->table)->where('id', $id)->delete();
    }

    public function getUserByUsername($username)
    {
        return $this->db->table($this->table)
                        ->where('username', $username)
                        ->get();
    }

    public function getUserByEmail($email)
    {
        return $this->db->table($this->table)
                        ->where('email', $email)
                        ->get();
    }

    public function getAdminUser()
    {
        return $this->db->table($this->table)
                        ->where('role', 'admin')
                        ->get();
    }

    public function createUser(array $data)
    {
        return $this->db->table($this->table)->insert($data);
    }

    public function updateUser($id, array $data)
    {
        if (empty($id) || empty($data)) {
            return false;
        }

        return $this->db->table($this->table)
                        ->where('id', $id)
                        ->update($data);
    }

    public function activateUser($id)
    {
        return $this->db->table($this->table)
                        ->where('id', $id)
                        ->update(['is_active' => 1]);
    }

    public function deactivateUser($id)
    {
        return $this->db->table($this->table)
                        ->where('id', $id)
                        ->update(['is_active' => 0]);
    }
}
