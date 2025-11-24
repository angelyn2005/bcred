<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class RequestModel extends Model
{
    /* 🟩 USER FUNCTIONS */

    public function getRequestsByUser($user_id)
    {
        return $this->db->table('requests')
                        ->where('user_id', $user_id)
                        ->order_by('created_at', 'DESC')
                        ->get_all();
    }

    public function addRequest($data)
    {
        $data['status'] = 'pending';
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->table('requests')->insert($data);
    }

    public function getAttachments($request_id)
    {
        return $this->db->table('attachments')
                        ->where('request_id', $request_id)
                        ->get_all();
    }

    public function addAttachment($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->table('attachments')->insert($data);
    }

    public function countAll()
    {
        return $this->db->table('requests')->countAll();
    }

    public function countByStatus($status)
    {
        return $this->db->table('requests')
                        ->where('status', $status)
                        ->countAllResults();
    }

    public function countByType($type)
    {
        return $this->db->table('requests')
                        ->where('document_type', $type)
                        ->countAllResults();
    }

    public function getMonthlyRequestCounts($months = 12)
    {
        $months = max(1, (int) $months);
        $table = $this->getRequestsTable();
        $start = date('Y-m-01 00:00:00', strtotime(sprintf('-%d months', $months - 1)));

        $stmt = $this->db->raw(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym,
                    COUNT(*) AS total
             FROM {$table}
             WHERE created_at >= :start
             GROUP BY ym
             ORDER BY ym ASC",
            [':start' => $start]
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getDocumentDistribution(array $targetLabels = [])
    {
        if (empty($targetLabels)) {
            $targetLabels = [
                'Barangay Clearance',
                'Indigency Certificate',
                'Residency Certificate',
                'Business Permit',
                'Barangay ID',
            ];
        }

        $table = $this->getRequestsTable();
        $stmt = $this->db->raw(
            "SELECT document_type, COUNT(*) AS total
             FROM {$table}
             GROUP BY document_type"
        );

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $distribution = array_fill_keys($targetLabels, 0);

        $aliases = [
            'Barangay Clearance'      => 'Barangay Clearance',
            'Certificate of Indigency'=> 'Indigency Certificate',
            'Indigency Certificate'   => 'Indigency Certificate',
            'Certificate of Residency'=> 'Residency Certificate',
            'Residency Certificate'   => 'Residency Certificate',
            'Business Permit'         => 'Business Permit',
            'Barangay ID'             => 'Barangay ID',
            'Barangay Identification' => 'Barangay ID',
            'Barangay Identification Card' => 'Barangay ID',
        ];

        foreach ($rows as $row) {
            $rawType = trim($row['document_type'] ?? '');
            if ($rawType === '') {
                continue;
            }

            $normalized = $aliases[$rawType] ?? null;
            if ($normalized && array_key_exists($normalized, $distribution)) {
                $distribution[$normalized] += (int) ($row['total'] ?? 0);
            }
        }

        return $distribution;
    }

    /**
     * Get document distribution but only for requests with a specific status (e.g., 'released').
     * Returns an associative array keyed by normalized label with integer counts.
     *
     * @param string $status
     * @param array $targetLabels
     * @return array
     */
    public function getDocumentDistributionByStatus(string $status, array $targetLabels = []): array
    {
        if (empty($targetLabels)) {
            $targetLabels = [
                'Barangay Clearance',
                'Indigency Certificate',
                'Residency Certificate',
                'Business Permit',
                'Barangay ID',
            ];
        }

        $table = $this->getRequestsTable();
        $stmt = $this->db->raw(
            "SELECT document_type, COUNT(*) AS total
             FROM {$table}
             WHERE LOWER(status) = :status
             GROUP BY document_type",
            [':status' => strtolower(trim($status))]
        );

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $distribution = array_fill_keys($targetLabels, 0);

        $aliases = [
            'Barangay Clearance'      => 'Barangay Clearance',
            'Certificate of Indigency'=> 'Indigency Certificate',
            'Indigency Certificate'   => 'Indigency Certificate',
            'Certificate of Residency'=> 'Residency Certificate',
            'Residency Certificate'   => 'Residency Certificate',
            'Business Permit'         => 'Business Permit',
            'Barangay ID'             => 'Barangay ID',
            'Barangay Identification' => 'Barangay ID',
            'Barangay Identification Card' => 'Barangay ID',
        ];

        foreach ($rows as $row) {
            $rawType = trim($row['document_type'] ?? '');
            if ($rawType === '') continue;
            $normalized = $aliases[$rawType] ?? null;
            if ($normalized && array_key_exists($normalized, $distribution)) {
                $distribution[$normalized] += (int) ($row['total'] ?? 0);
            }
        }

        return $distribution;
    }

    public function getDocumentDistributionRaw()
    {
        return $this->db->table('requests')
                        ->select('document_type, COUNT(*) AS total')
                        ->group_by('document_type')
                        ->get_all() ?? [];
    }


    public function getComments($request_id)
    {
        return $this->db->table('request_comments')
                        ->where('request_id', $request_id)
                        ->order_by('created_at', 'ASC')
                        ->get_all();
    }

    public function addComment($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->table('request_comments')->insert($data);
    }

    /* 🟦 ADMIN FUNCTIONS */

    // Kunin lahat ng requests (for admin dashboard) kasama user info
public function getAllRequests()
{
    $requests = $this->db->table('requests')
                         ->order_by('created_at', 'DESC')
                         ->get_all();

    foreach ($requests as &$r) {
        $user = $this->db->table('users')
                         ->where('id', $r['user_id'])
                         ->get();
        $r['fullname'] = $user['fullname'] ?? '';
        $r['email'] = $user['email'] ?? '';
    }

    return $requests;
}


    public function getRequestById($id)
{
    return $this->db->table('requests r')
                    ->select('r.*, u.fullname, u.email')
                    ->left_join('users u', 'r.user_id = u.id')
                    ->where('r.id', $id)
                    ->get();
}


    public function updateRequestStatus($id, $status, $admin_note = '')
    {
        if (empty($id) || empty($status)) {
            return false;
        }

        $this->ensureStatusColumnAllows($status);

        return $this->db->table('requests')
                ->where('id', $id)
                ->update([
                    'status' => $status,
                    'admin_note' => $admin_note,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
    }

    /**
     * Make sure the requests.status column supports the provided status value.
     *
     * @param string $status
     * @return void
     */
    private function ensureStatusColumnAllows($status)
    {
        $status = trim((string) $status);
        if ($status === '') {
            return;
        }

        try {
            $table = (config_item('dbprefix') ?? '') . 'requests';
            $stmt = $this->db->raw("SHOW COLUMNS FROM {$table} LIKE 'status'");
            $column = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

            if (!$column) {
                return;
            }

            $type = $column['Type'] ?? '';
            if (stripos($type, 'enum(') === false || stripos($type, "'" . $status . "'") !== false) {
                return;
            }

            $definedValues = [];
            if (preg_match("/enum\\((.*)\\)/i", $type, $matches)) {
                $definedValues = array_map(
                    fn($value) => trim($value, " '"),
                    explode(',', $matches[1])
                );
            }

            $desiredValues = array_unique(array_filter(array_merge(
                $definedValues,
                ['pending', 'approved', 'rejected', 'released']
            )));

            if (!in_array($status, $desiredValues, true)) {
                $desiredValues[] = $status;
            }

            $enumList = implode(',', array_map(
                fn($value) => "'" . addslashes($value) . "'",
                $desiredValues
            ));

            $this->db->raw(
                "ALTER TABLE {$table} MODIFY status ENUM({$enumList}) NOT NULL DEFAULT 'pending'"
            );
        } catch (Throwable $e) {
            error_log('Failed to adjust requests.status enum: ' . $e->getMessage());
        }
    }

    public function deleteRequest($id)
    {
        return $this->db->table('requests')
                        ->where('id', $id)
                        ->delete();
    }

    private function getRequestsTable(): string
    {
        $prefix = config_item('dbprefix') ?? '';
        return $prefix . 'requests';
    }
}
?>
