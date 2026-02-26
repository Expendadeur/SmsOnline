<?php

class DashboardController extends Controller {
    private $friendshipModel;
    private $messageModel;
    private $userModel;

    public function __construct() {
        if (!Security::isLoggedIn()) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
                $this->jsonResponse(['error' => 'Session expirée']);
                exit;
            }
            header('Location: ' . BASE_URL . '/Auth/login');
            exit;
        }
        $this->friendshipModel = $this->model('Friendship');
        $this->messageModel = $this->model('Message');
        $this->userModel = $this->model('User');
    }

    public function index() {
        $userId = $_SESSION['user_id'];
        $allUsers = $this->prepareUserData($userId);
        
        $data = [
            'allUsers' => $allUsers
        ];

        $this->view('dashboard/index', $data);
    }

    public function getUserListJson() {
        $userId = $_SESSION['user_id'];
        $allUsers = $this->prepareUserData($userId);
        $this->jsonResponse($allUsers);
    }

    private function prepareUserData($userId) {
        $allUsers = $this->friendshipModel->getAllUsersWithStatus($userId);
        $unread = $this->messageModel->countUnread($userId);
        
        $unreadMap = [];
        foreach ($unread as $u) {
            $unreadMap[$u['sender_id']] = $u['count'];
        }

        foreach ($allUsers as &$user) {
            $user['unread_count'] = $unreadMap[$user['id']] ?? 0;
            
            // Online status (seen in last 65 seconds for precision)
            $user['is_online'] = false;
            if (!empty($user['last_seen'])) {
                try {
                    $lastSeen = new DateTime($user['last_seen']);
                    $now = new DateTime();
                    $diff = $now->getTimestamp() - $lastSeen->getTimestamp();
                    $user['is_online'] = ($diff < 65);
                } catch (Exception $e) {
                    $user['is_online'] = false;
                }
            }
        }
        return $allUsers;
    }

    public function sendRequest($receiverId) {
        $existing = $this->friendshipModel->checkFriendship($_SESSION['user_id'], $receiverId);
        if ($existing) {
            $this->jsonResponse(['success' => false, 'error' => 'Déjà envoyé ou existant.']);
            return;
        }

        if ($this->friendshipModel->sendRequest($_SESSION['user_id'], $receiverId)) {
            $this->jsonResponse(['success' => true]);
        } else {
            $this->jsonResponse(['success' => false]);
        }
    }

    public function acceptRequest($senderId) {
        if ($this->friendshipModel->updateStatus($senderId, $_SESSION['user_id'], 'accepted')) {
            $this->jsonResponse(['success' => true]);
        } else {
            $this->jsonResponse(['success' => false]);
        }
    }

    public function rejectRequest($senderId) {
        if ($this->friendshipModel->updateStatus($senderId, $_SESSION['user_id'], 'rejected')) {
            $this->jsonResponse(['success' => true]);
        } else {
            $this->jsonResponse(['success' => false]);
        }
    }

    public function heartbeat() {
        if ($this->userModel->updateLastSeen($_SESSION['user_id'])) {
            $this->jsonResponse(['success' => true]);
        } else {
            $this->jsonResponse(['success' => false]);
        }
    }
}
