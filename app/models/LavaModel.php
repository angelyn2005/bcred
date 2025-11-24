<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class LavaModel
{
    protected $db;

    public function __construct()
    {
        // Use the global database instance from LavaLust
        global $db;

        if (!$db) {
            // fallback: create a new instance if global doesn't exist
            $db = new Database();
        }

        $this->db = $db;
    }

    public function insert($table, $data)
    {
        return $this->db->insert($table, $data);
    }

    public function select($table, $columns = '*', $conditions = [], $extra = '')
    {
        return $this->db->select($table, $columns, $conditions, $extra);
    }

    public function update($table, $data, $conditions)
    {
        return $this->db->update($table, $data, $conditions);
    }

    public function delete($table, $conditions)
    {
        return $this->db->delete($table, $conditions);
    }
}
