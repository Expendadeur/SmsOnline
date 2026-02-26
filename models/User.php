<?php

class User extends Model {
    public function register($data) {
        $this->db->query("INSERT INTO users (nom, prenom, cni, photo, username, password, telephone, date_naissance) VALUES (:nom, :prenom, :cni, :photo, :username, :password, :telephone, :date_naissance)");
        $this->db->bind(':nom', $data['nom']);
        $this->db->bind(':prenom', $data['prenom']);
        $this->db->bind(':cni', $data['cni']);
        $this->db->bind(':photo', $data['photo']);
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':password', $data['password']);
        $this->db->bind(':telephone', $data['telephone']);
        $this->db->bind(':date_naissance', $data['date_naissance']);

        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function findUserByUsername($username) {
        $this->db->query("SELECT * FROM users WHERE username = :username");
        $this->db->bind(':username', $username);
        return $this->db->single();
    }

    public function findUserByCNI($cni) {
        $this->db->query("SELECT * FROM users WHERE cni = :cni");
        $this->db->bind(':cni', $cni);
        return $this->db->single();
    }

    public function findUserByPhone($phone) {
        $this->db->query("SELECT * FROM users WHERE telephone = :phone");
        $this->db->bind(':phone', $phone);
        return $this->db->single();
    }

    public function login($username, $password) {
        $row = $this->findUserByUsername($username);
        if ($row) {
            if (password_verify($password, $row['password'])) {
                return $row;
            }
        }
        return false;
    }

    public function canUpdateCredentials($userId) {
        $this->db->query("SELECT last_credential_update FROM users WHERE id = :id");
        $this->db->bind(':id', $userId);
        $row = $this->db->single();
        
        if (!$row['last_credential_update']) return true;

        $lastUpdate = new DateTime($row['last_credential_update']);
        $now = new DateTime();
        $diff = $now->diff($lastUpdate)->days;

        return ($diff >= 14);
    }

    public function updateCredentials($userId, $data) {
        $sql = "UPDATE users SET last_credential_update = NOW()";
        if (isset($data['username'])) $sql .= ", username = :username";
        if (isset($data['password'])) $sql .= ", password = :password";
        if (isset($data['telephone'])) $sql .= ", telephone = :telephone";
        $sql .= " WHERE id = :id";

        $this->db->query($sql);
        $this->db->bind(':id', $userId);
        if (isset($data['username'])) $this->db->bind(':username', $data['username']);
        if (isset($data['password'])) $this->db->bind(':password', $data['password']);
        if (isset($data['telephone'])) $this->db->bind(':telephone', $data['telephone']);

        return $this->db->execute();
    }

    public function updateLastSeen($userId) {
        $this->db->query("UPDATE users SET last_seen = NOW() WHERE id = :id");
        $this->db->bind(':id', $userId);
        return $this->db->execute();
    }

    public function getOnlineCount() {
        $this->db->query("SELECT COUNT(*) as count FROM users WHERE last_seen > (NOW() - INTERVAL 65 SECOND)");
        $row = $this->db->single();
        return $row ? (int)$row['count'] : 0;
    }
}
