<?php

class ChatController extends Controller {
    private $messageModel;
    private $userModel;
    private $friendshipModel;

    public function __construct() {
        if (!Security::isLoggedIn()) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
                $this->jsonResponse(['error' => 'Not authenticated']);
            }
            header('Location: ' . BASE_URL . '/Auth/login');
            exit;
        }
        $this->messageModel = $this->model('Message');
        $this->userModel = $this->model('User');
        $this->friendshipModel = $this->model('Friendship');
    }

    public function getMessages($contactId) {
        $userId = $_SESSION['user_id'];
        $offset = $_GET['offset'] ?? 0;
        
        $this->messageModel->markAsRead($userId, $contactId);
        $messages = $this->messageModel->getMessages($userId, $contactId, $offset);
        $this->jsonResponse($messages);
    }

    public function send() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $senderId = $_SESSION['user_id'];
            $receiverId = $_POST['receiver_id'];
            $text = $this->sanitize($_POST['message']);

            $ship = $this->friendshipModel->checkFriendship($senderId, $receiverId);
            if (!$ship || $ship['status'] != 'accepted') {
                $this->jsonResponse(['error' => 'Vous ne pouvez envoyer des messages qu\'à vos amis acceptés.']);
            }

            if ($this->messageModel->sendMessage($senderId, $receiverId, $text)) {
                $this->jsonResponse(['success' => true]);
            } else {
                $this->jsonResponse(['error' => 'Erreur technique lors de l\'envoi.']);
            }
        }
    }

    public function sendVoice() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['audio'])) {
            $senderId = $_SESSION['user_id'];
            $receiverId = $_POST['receiver_id'];

            $ship = $this->friendshipModel->checkFriendship($senderId, $receiverId);
            if (!$ship || $ship['status'] != 'accepted') {
                $this->jsonResponse(['error' => 'Interdit.']);
            }

            $allowed = ['webm', 'wav', 'mp3', 'ogg'];
            $filename = $_FILES['audio']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (!in_array($ext, $allowed) && $ext != 'blob') {
                $ext = 'webm';
            }

            $newName = 'voice_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['audio']['tmp_name'], UPLOAD_DIR . $newName)) {
                if ($this->messageModel->sendMessage($senderId, $receiverId, $newName, 'audio')) {
                    $this->jsonResponse(['success' => true]);
                }
            }
            $this->jsonResponse(['error' => 'Erreur upload voice.']);
        }
    }

    public function poll($contactId) {
        if (!$contactId) $this->jsonResponse(['messages' => []]);

        $userId = $_SESSION['user_id'];
        $lastId = (int)($_GET['last_id'] ?? 0);
        
        session_write_close();

        $timeout = 25; 
        $start = time();

        while ((time() - $start) < $timeout) {
            // Check for NEW messages
            $newMessages = $this->messageModel->getLatestMessages($userId, $contactId, $lastId);
            
            if (!empty($newMessages)) {
                $this->messageModel->markAsRead($userId, $contactId);
                $this->jsonResponse(['messages' => $newMessages]);
            }

            $this->messageModel->markAsDelivered($userId, $contactId);
            usleep(1000000); 
        }

        $this->jsonResponse(['messages' => []]);
    }

    public function checkNotifications() {
        $userId = $_SESSION['user_id'];
        session_write_close();
        
        $timeout = 20;
        $start = time();

        while ((time() - $start) < $timeout) {
            $unread = $this->messageModel->countUnread($userId);
            if (!empty($unread)) {
                $this->jsonResponse($unread);
            }
            usleep(2000000); 
        }

        $this->jsonResponse([]);
    }
}
