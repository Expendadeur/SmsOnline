<?php

class Message extends Model {
    public function sendMessage($senderId, $receiverId, $message, $type = 'text') {
        $this->db->query("INSERT INTO messages (sender_id, receiver_id, message, type, status) VALUES (:sender_id, :receiver_id, :message, :type, 'sent')");
        $this->db->bind(':sender_id', $senderId);
        $this->db->bind(':receiver_id', $receiverId);
        $this->db->bind(':message', $message);
        $this->db->bind(':type', $type);
        return $this->db->execute();
    }

    public function getMessages($userId, $contactId, $offset = 0, $limit = 20) {
        $this->db->query("SELECT * FROM messages 
                          WHERE (sender_id = :u1 AND receiver_id = :u2) 
                          OR (sender_id = :u3 AND receiver_id = :u4) 
                          ORDER BY created_at ASC 
                          LIMIT :limit OFFSET :offset");
        $this->db->bind(':u1', $userId);
        $this->db->bind(':u2', $contactId);
        $this->db->bind(':u3', $contactId);
        $this->db->bind(':u4', $userId);
        $this->db->bind(':limit', (int)$limit, PDO::PARAM_INT);
        $this->db->bind(':offset', (int)$offset, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function updateStatus($messageId, $status) {
        $this->db->query("UPDATE messages SET status = :status WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $messageId);
        return $this->db->execute();
    }

    public function markAsRead($userId, $contactId) {
        $this->db->query("UPDATE messages SET status = 'read' WHERE sender_id = :cId AND receiver_id = :uId AND status != 'read'");
        $this->db->bind(':uId', $userId);
        $this->db->bind(':cId', $contactId);
        return $this->db->execute();
    }

    public function markAsDelivered($userId, $contactId) {
        $this->db->query("UPDATE messages SET status = 'delivered' WHERE sender_id = :cId AND receiver_id = :uId AND status = 'sent'");
        $this->db->bind(':uId', $userId);
        $this->db->bind(':cId', $contactId);
        return $this->db->execute();
    }

    public function getLatestMessages($userId, $contactId, $lastId) {
        $this->db->query("SELECT * FROM messages 
                          WHERE ((sender_id = :u1 AND receiver_id = :u2) 
                          OR (sender_id = :u3 AND receiver_id = :u4)) 
                          AND id > :lastId 
                          ORDER BY created_at ASC");
        $this->db->bind(':u1', $userId);
        $this->db->bind(':u2', $contactId);
        $this->db->bind(':u3', $contactId);
        $this->db->bind(':u4', $userId);
        $this->db->bind(':lastId', $lastId);
        return $this->db->resultSet();
    }

    public function countUnread($userId) {
        $this->db->query("SELECT sender_id, COUNT(*) as count FROM messages WHERE receiver_id = :userId AND status != 'read' GROUP BY sender_id");
        $this->db->bind(':userId', $userId);
        return $this->db->resultSet();
    }
}
