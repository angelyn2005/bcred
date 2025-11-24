<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class ActivityLogModel extends Model
{
    protected $table = 'activity_logs';

    public function record(array $payload): bool
    {
        if (!$this->tableExists()) {
            return false;
        }

        $data = [
            'action'     => $payload['action'] ?? null,
            'details'    => $payload['details'] ?? null,
            'request_id' => $payload['request_id'] ?? null,
            'admin_id'   => $payload['admin_id'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        try {
            return (bool) $this->db->table($this->table)->insert($data);
        } catch (PDOException $e) {
            error_log('ActivityLogModel::record failed: ' . $e->getMessage());
            return false;
        }
    }

    public function getLogs(array $filters = [], $limit = null): array
    {
        if (!$this->tableExists()) {
            return [];
        }

        $builder = $this->db->table($this->table)
            ->select("{$this->table}.*, users.fullname AS admin_name")
            ->left_join('users', "{$this->table}.admin_id = users.id")
            ->order_by("{$this->table}.created_at", 'DESC');

        $search = trim($filters['search'] ?? '');
        if ($search !== '') {
            $likeValue = '%' . $search . '%';
            $builder->grouped(function($query) use ($likeValue) {
                $query->like("{$this->table}.action", $likeValue);
                $query->or_like("{$this->table}.details", $likeValue);
                $query->or_like("{$this->table}.request_id", $likeValue);
                $query->or_like('users.fullname', $likeValue);
            });
        }

        $action = $filters['action'] ?? null;
        if (!empty($action) && strtolower($action) !== 'all') {
            $builder->where("{$this->table}.action", $action);
        }

        if (!empty($filters['request_id'])) {
            $builder->where("{$this->table}.request_id", $filters['request_id']);
        }

        $effectiveLimit = $limit ?? ($filters['limit'] ?? null);
        if ($effectiveLimit !== null) {
            $builder->limit((int) $effectiveLimit);
        }

        try {
            return $builder->get_all() ?? [];
        } catch (PDOException $e) {
            error_log('ActivityLogModel::getLogs failed: ' . $e->getMessage());
            return [];
        }
    }

    public function getDistinctActions(): array
    {
        if (!$this->tableExists()) {
            return [];
        }

        $rows = $this->db->table($this->table)
            ->select('DISTINCT action AS action_name')
            ->where_not_null('action')
            ->order_by('action_name', 'ASC')
            ->get_all() ?? [];

        return array_values(array_filter(array_map(
            fn($row) => $row['action_name'] ?? null,
            $rows
        )));
    }

    private function tableExists(): bool
    {
        static $exists = null;
        if ($exists !== null) {
            return $exists;
        }

        try {
            $this->db->raw("DESCRIBE {$this->table}");
            $exists = true;
        } catch (PDOException $e) {
            error_log("ActivityLogModel: table '{$this->table}' missing - " . $e->getMessage());
            $exists = false;
        }

        return $exists;
    }
}
