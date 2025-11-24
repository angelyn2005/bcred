<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class AttachmentModel extends Model
{
    protected $table = 'attachments';

    /**
     * Save an attachment record
     *
     * @param array $data
     * @return int|bool
     */
    public function addAttachment(array $data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');

        return $this->db
            ->table($this->table)
            ->insert($data);
    }

    /**
     * Get all attachments linked to a request
     *
     * @param int $request_id
     * @return array
     */
    public function getAttachmentsByRequest($request_id)
    {
        return $this->db
            ->table($this->table)
            ->where('request_id', $request_id)
            ->order_by('created_at', 'ASC')
            ->get_all() ?? [];
    }

    /**
     * Delete attachments tied to a request
     *
     * @param int $request_id
     * @return int|bool
     */
    public function deleteByRequest($request_id)
    {
        return $this->db
            ->table($this->table)
            ->where('request_id', $request_id)
            ->delete();
    }
}
