<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class EmailVerificationModel extends Model
{
    protected $table = 'email_verifications';

    public function createVerification(array $data)
    {
        $payload = [
            'user_id' => $data['user_id'] ?? null,
            'email' => $data['email'] ?? '',
            'code' => $data['code'] ?? '',
            // default expiry: 45 seconds from now
            'expires_at' => $data['expires_at'] ?? date('Y-m-d H:i:s', strtotime('+45 seconds')),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        // include meta only if the column exists in DB
        if (!empty($data['meta']) && $this->columnExists('meta')) {
            $payload['meta'] = json_encode($data['meta']);
        }

        // If expires_at was not provided, perform an INSERT using DB time (DATE_ADD(NOW(), INTERVAL 45 SECOND))
        if (empty($data['expires_at'])) {
            try {
                $cols = ['user_id', 'email', 'code', 'expires_at', 'created_at'];
                $placeholders = ['NULL', '?', '?', 'DATE_ADD(NOW(), INTERVAL 45 SECOND)', 'NOW()'];
                $params = [$payload['email'], $payload['code']];

                if (isset($payload['meta'])) {
                    $cols[] = 'meta';
                    $placeholders[] = '?';
                    $params[] = $payload['meta'];
                }

                $colList = implode(', ', $cols);
                $phList = implode(', ', $placeholders);

                $sql = "INSERT INTO {$this->table} ({$colList}) VALUES ({$phList})";
                $this->db->raw($sql, $params);
                $idRow = $this->db->raw('SELECT LAST_INSERT_ID() AS id')->fetch(PDO::FETCH_ASSOC);
                return $idRow['id'] ?? 0;
            } catch (Throwable $e) {
                // log the raw-insert error for debugging, then fallback to PHP-computed expiry and normal insert
                error_log('EmailVerificationModel raw insert failed: ' . $e->getMessage());
                $payload['expires_at'] = date('Y-m-d H:i:s', strtotime('+45 seconds'));
                $insertId = $this->db->table($this->table)->insert($payload);
                return $insertId;
            }
        }

        $insertId = $this->db->table($this->table)->insert($payload);
        return $insertId;
    }

    public function getValidByIdAndCode($id, $code)
    {
        return $this->db->table($this->table)
            ->where('id', $id)
            ->where('code', $code)
            ->where('expires_at', '>=', date('Y-m-d H:i:s'))
            ->where_null('verified_at')
            ->get();
    }

    public function getValidByUserAndCode($user_id, $code)
    {
        return $this->db->table($this->table)
            ->where('user_id', $user_id)
            ->where('code', $code)
            ->where('expires_at', '>=', date('Y-m-d H:i:s'))
            ->where_null('verified_at')
            ->get();
    }

    public function getLatestByUser($user_id)
    {
        return $this->db->table($this->table)
            ->where('user_id', $user_id)
            ->order_by('created_at', 'DESC')
            ->get();
    }

    public function getById($id)
    {
        return $this->db->table($this->table)
            ->where('id', $id)
            ->get();
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
            error_log("EmailVerificationModel: table '{$this->table}' missing - " . $e->getMessage());
            $exists = false;
        }

        return $exists;
    }

    public function columnExists(string $column): bool
    {
        try {
            $cols = $this->db->raw("DESCRIBE {$this->table}")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($cols as $c) {
                if (($c['Field'] ?? $c['field'] ?? '') === $column) {
                    return true;
                }
            }
        } catch (PDOException $e) {
            // if table missing or other error, assume column absent
            return false;
        }

        return false;
    }

    public function markVerified($id)
    {
        return $this->db->table($this->table)
            ->where('id', $id)
            ->update(['verified_at' => date('Y-m-d H:i:s')]);
    }

    public function markVerifiedWithUser($id, $user_id)
    {
        return $this->db->table($this->table)
            ->where('id', $id)
            ->update(['verified_at' => date('Y-m-d H:i:s'), 'user_id' => $user_id]);
    }

    public function isVerified($user_id)
    {
        $row = $this->db->table($this->table)
            ->where('user_id', $user_id)
            ->where_not_null('verified_at')
            ->get();

        return !empty($row);
    }
}
