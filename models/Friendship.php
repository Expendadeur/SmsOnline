<?php

class Friendship extends Model {
    public function sendRequest($senderId, $receiverId) {
        $this->db->query("INSERT INTO friendships (sender_id, receiver_id, status) VALUES (:sender_id, :receiver_id, 'pending')");
        $this->db->bind(':sender_id', $senderId);
        $this->db->bind(':receiver_id', $receiverId);
        return $this->db->execute();
    }

    public function updateStatus($senderId, $receiverId, $status) {
        $this->db->query("UPDATE friendships SET status = :status WHERE (sender_id = :sender_id AND receiver_id = :receiver_id) OR (sender_id = :r2 AND receiver_id = :s2)");
        $this->db->bind(':status', $status);
        $this->db->bind(':sender_id', $senderId);
        $this->db->bind(':receiver_id', $receiverId);
        $this->db->bind(':r2', $receiverId);
        $this->db->bind(':s2', $senderId);
        return $this->db->execute();
    }

    public function getFriendships($userId) {
        $this->db->query("SELECT f.*, u.nom, u.prenom, u.username, u.photo, u.last_seen, u.is_verified
                          FROM friendships f 
                          JOIN users u ON (f.sender_id = u.id OR f.receiver_id = u.id)
                          WHERE (f.sender_id = :u1 OR f.receiver_id = :u2) 
                          AND u.id != :u3");
        $this->db->bind(':u1', $userId);
        $this->db->bind(':u2', $userId);
        $this->db->bind(':u3', $userId);
        return $this->db->resultSet();
    }

    public function getPendingRequests($userId) {
        $this->db->query("SELECT f.*, u.nom, u.prenom, u.username, u.photo 
                          FROM friendships f 
                          JOIN users u ON f.sender_id = u.id 
                          WHERE f.receiver_id = :userId AND f.status = 'pending'");
        $this->db->bind(':userId', $userId);
        return $this->db->resultSet();
    }

    public function checkFriendship($user1, $user2) {
        $this->db->query("SELECT * FROM friendships WHERE (sender_id = :u1 AND receiver_id = :u2) OR (sender_id = :u3 AND receiver_id = :u4)");
        $this->db->bind(':u1', $user1);
        $this->db->bind(':u2', $user2);
        $this->db->bind(':u3', $user2);
        $this->db->bind(':u4', $user1);
        return $this->db->single();
    }

    public function searchUsers($query, $currentUserId) {
        $this->db->query("SELECT id, nom, prenom, username, photo, is_verified FROM users WHERE (username LIKE :q1 OR nom LIKE :q2 OR prenom LIKE :q3) AND id != :userId");
        $this->db->bind(':q1', "%$query%");
        $this->db->bind(':q2', "%$query%");
        $this->db->bind(':q3', "%$query%");
        $this->db->bind(':userId', $currentUserId);
        return $this->db->resultSet();
    }

    public function getAllUsersWithStatus($userId) {
        $this->db->query("SELECT u.id, u.nom, u.prenom, u.username, u.photo, u.last_seen, u.is_verified,
                                 f.status as friendship_status, f.sender_id as friendship_sender
                          FROM users u
                          LEFT JOIN friendships f ON (
                              (f.sender_id = :uid1 AND f.receiver_id = u.id) OR 
                              (f.sender_id = u.id AND f.receiver_id = :uid2)
                          )
                          WHERE u.id != :uid3
                          ORDER BY u.username ASC");
        $this->db->bind(':uid1', $userId);
        $this->db->bind(':uid2', $userId);
        $this->db->bind(':uid3', $userId);
        return $this->db->resultSet();
    }
}
