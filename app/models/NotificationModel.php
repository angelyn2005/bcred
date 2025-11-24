<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class NotificationModel extends Model
{
    protected $table = 'notifications';

    /**
     * Store a notification for a user
     *
     * @param array $data
     * @return int|bool
     */
    public function addNotification(array $data)
    {
        $payload = [
            'user_id'    => $data['user_id'] ?? null,
            'request_id' => $data['request_id'] ?? null,
            'title'      => $data['title'] ?? '',
            'message'    => $data['message'] ?? null,
            'channel'    => $data['channel'] ?? 'in-app',
            'is_read'    => (int) ($data['is_read'] ?? 0),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        if (empty($payload['user_id']) || $payload['title'] === '') {
            return false;
        }

        return $this->db
            ->table($this->table)
            ->insert($payload);
    }

    /**
     * Retrieve notifications for a user
     *
     * @param int  $user_id
     * @param bool $onlyUnread
     * @param int  $limit
     * @return array
     */
    public function getNotificationsByUser($user_id, $onlyUnread = false, $limit = 10)
    {
        $query = $this->db
            ->table($this->table)
            ->where('user_id', $user_id);

        if ($onlyUnread) {
            $query->where('is_read', 0);
        }

        return $query
            ->order_by('created_at', 'DESC')
            ->limit($limit)
            ->get_all() ?? [];
    }

    /**
     * Mark a notification as read
     *
     * @param int $notification_id
     * @return int|bool
     */
    public function markAsRead($notification_id)
    {
        if (empty($notification_id)) {
            return false;
        }

        return $this->db
            ->table($this->table)
            ->where('id', $notification_id)
            ->update([
                'is_read'    => 1,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
    }
}
