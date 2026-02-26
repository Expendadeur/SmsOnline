<?php

class Post extends Model {
    public function createPost($userId, $content, $mediaPath = null, $mediaType = 'text') {
        $this->db->query("INSERT INTO posts (user_id, content, media_path, media_type) VALUES (:user_id, :content, :media_path, :media_type)");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':content', $content);
        $this->db->bind(':media_path', $mediaPath);
        $this->db->bind(':media_type', $mediaType);
        return $this->db->execute();
    }

    public function getFeed($limit = 20, $offset = 0) {
        $this->db->query("SELECT p.*, u.username, u.prenom, u.nom, u.photo, u.is_verified,
                          (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as likes_count,
                          (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comments_count,
                          (SELECT COUNT(*) FROM post_shares WHERE post_id = p.id) as shares_count
                          FROM posts p 
                          JOIN users u ON p.user_id = u.id 
                          ORDER BY p.created_at DESC 
                          LIMIT :limit OFFSET :offset");
        $this->db->bind(':limit', (int)$limit, PDO::PARAM_INT);
        $this->db->bind(':offset', (int)$offset, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function toggleLike($postId, $userId) {
        $this->db->query("SELECT id FROM likes WHERE post_id = :p1 AND user_id = :u1");
        $this->db->bind(':p1', $postId);
        $this->db->bind(':u1', $userId);
        $row = $this->db->single();
        if ($row) {
            $this->db->query("DELETE FROM likes WHERE post_id = :p2 AND user_id = :u2");
            $this->db->bind(':p2', $postId);
            $this->db->bind(':u2', $userId);
            return $this->db->execute() ? 'unliked' : false;
        } else {
            $this->db->query("INSERT INTO likes (post_id, user_id) VALUES (:p3, :u3)");
            $this->db->bind(':p3', $postId);
            $this->db->bind(':u3', $userId);
            return $this->db->execute() ? 'liked' : false;
        }
    }

    public function addComment($postId, $userId, $content) {
        $this->db->query("INSERT INTO comments (post_id, user_id, content) VALUES (:post_id, :user_id, :content)");
        $this->db->bind(':post_id', $postId);
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':content', $content);
        return $this->db->execute();
    }

    public function getComments($postId) {
        $this->db->query("SELECT c.*, u.username, u.prenom, u.nom, u.photo 
                          FROM comments c 
                          JOIN users u ON c.user_id = u.id 
                          WHERE c.post_id = :post_id 
                          ORDER BY c.created_at ASC");
        $this->db->bind(':post_id', $postId);
        return $this->db->resultSet();
    }

    public function incrementView($postId) {
        $this->db->query("UPDATE posts SET view_count = view_count + 1 WHERE id = :id_v");
        $this->db->bind(':id_v', $postId);
        $this->db->execute();
        $this->db->query("SELECT user_id FROM posts WHERE id = :id_a");
        $this->db->bind(':id_a', $postId);
        $post = $this->db->single();
        if ($post && isset($post['user_id'])) {
            $this->checkAndVerifyUser($post['user_id']);
        }
    }

    public function checkAndVerifyUser($userId) {
        $this->db->query("SELECT SUM(view_count) as total_views FROM posts WHERE user_id = :u_id");
        $this->db->bind(':u_id', $userId);
        $result = $this->db->single();
        if ($result && isset($result['total_views']) && $result['total_views'] >= 100) {
            $this->db->query("UPDATE users SET is_verified = 1 WHERE id = :user_id_v AND is_verified = 0");
            $this->db->bind(':user_id_v', $userId);
            $this->db->execute();
        }
    }

    public function getVerifiedUsers($limit = 5) {
        $this->db->query("SELECT id, prenom, nom, username, photo, is_verified 
                          FROM users WHERE is_verified = 1 ORDER BY id DESC LIMIT :limit_v");
        $this->db->bind(':limit_v', (int)$limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    public function getGlobalViews() {
        $this->db->query("SELECT SUM(view_count) as total FROM posts");
        $res = $this->db->single();
        return ($res && isset($res['total'])) ? (int)$res['total'] : 0;
    }

    // =================== SHARE ===================
    public function sharePost($postId, $userId) {
        // Record the share
        $this->db->query("INSERT IGNORE INTO post_shares (post_id, user_id) VALUES (:post_id, :user_id)");
        $this->db->bind(':post_id', $postId);
        $this->db->bind(':user_id', $userId);
        $this->db->execute();
        // Get share count
        $this->db->query("SELECT COUNT(*) as cnt FROM post_shares WHERE post_id = :pid");
        $this->db->bind(':pid', $postId);
        $r = $this->db->single();
        return $r ? (int)$r['cnt'] : 0;
    }

    public function getPost($postId) {
        $this->db->query("SELECT p.*, u.username, u.prenom, u.nom, u.photo, u.is_verified,
                          (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as likes_count,
                          (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comments_count,
                          (SELECT COUNT(*) FROM post_shares WHERE post_id = p.id) as shares_count
                          FROM posts p JOIN users u ON p.user_id = u.id WHERE p.id = :post_id");
        $this->db->bind(':post_id', $postId);
        return $this->db->single();
    }

    // =================== EDIT ===================
    public function updatePost($postId, $userId, $content) {
        $this->db->query("UPDATE posts SET content = :content WHERE id = :post_id AND user_id = :user_id");
        $this->db->bind(':content', $content);
        $this->db->bind(':post_id', $postId);
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }

    // =================== DELETE ===================
    public function deletePost($postId, $userId) {
        $this->db->query("DELETE FROM posts WHERE id = :post_id AND user_id = :user_id");
        $this->db->bind(':post_id', $postId);
        $this->db->bind(':user_id', $userId);
        return $this->db->execute();
    }
}
