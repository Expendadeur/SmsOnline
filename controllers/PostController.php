<?php

class PostController extends Controller {
    private $postModel;

    public function __construct() {
        if (!Security::isLoggedIn()) {
            $this->jsonResponse(['error' => 'Session expirée']);
            exit;
        }
        $this->postModel = $this->model('Post');
    }

    public function create() {
        try {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $userId = $_SESSION['user_id'];
                $content = !empty($_POST['content']) ? $this->sanitize($_POST['content']) : '';
                $mediaPath = null;
                $mediaType = 'text';

                if (isset($_FILES['media']) && $_FILES['media']['error'] == 0) {
                    $filename = $_FILES['media']['name'];
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    $imgAllowed = ['jpg', 'jpeg', 'png', 'gif'];
                    $vidAllowed = ['mp4', 'webm', 'ogg'];
                    if (in_array($ext, $imgAllowed)) {
                        $mediaType = 'image';
                    } elseif (in_array($ext, $vidAllowed)) {
                        $mediaType = 'video';
                    } else {
                        $this->jsonResponse(['error' => 'Type de fichier non supporté.']);
                        return;
                    }
                    $newName = uniqid('post_') . '.' . $ext;
                    if (move_uploaded_file($_FILES['media']['tmp_name'], UPLOAD_DIR . $newName)) {
                        $mediaPath = $newName;
                    } else {
                        $this->jsonResponse(['error' => 'Erreur upload.']);
                        return;
                    }
                }

                if (empty($content) && empty($mediaPath)) {
                    $this->jsonResponse(['error' => 'La publication ne peut pas être vide.']);
                    return;
                }

                if ($this->postModel->createPost($userId, $content, $mediaPath, $mediaType)) {
                    $this->jsonResponse(['success' => true]);
                } else {
                    $this->jsonResponse(['error' => 'Erreur lors de la publication.']);
                }
            }
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()]);
        }
    }

    public function getFeed() {
        try {
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            $posts = $this->postModel->getFeed(20, $offset);
            $this->jsonResponse($posts);
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()]);
        }
    }

    public function like($postId) {
        try {
            $userId = $_SESSION['user_id'];
            $status = $this->postModel->toggleLike($postId, $userId);
            $this->jsonResponse(['success' => (bool)$status, 'status' => $status]);
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()]);
        }
    }

    public function comment($postId) {
        try {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $userId = $_SESSION['user_id'];
                $content = $this->sanitize($_POST['content'] ?? '');
                if (empty($content)) {
                    $this->jsonResponse(['error' => 'Commentaire vide.']);
                    return;
                }
                $result = $this->postModel->addComment($postId, $userId, $content);
                $this->jsonResponse(['success' => (bool)$result]);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()]);
        }
    }

    public function getComments($postId) {
        try {
            $comments = $this->postModel->getComments($postId);
            $this->jsonResponse($comments);
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()]);
        }
    }

    public function trackView($postId) {
        try {
            $this->postModel->incrementView($postId);
            $this->jsonResponse(['success' => true]);
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()]);
        }
    }

    public function getSidebarData() {
        try {
            $verified = $this->postModel->getVerifiedUsers(5);
            $totalViews = $this->postModel->getGlobalViews();
            $userModel = $this->model('User');
            $onlineCount = $userModel->getOnlineCount();
            $this->jsonResponse([
                'verified' => $verified,
                'stats' => ['total_views' => $totalViews, 'online_count' => $onlineCount]
            ]);
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()]);
        }
    }

    // =================== SHARE ===================
    public function share($postId) {
        try {
            $senderId = $_SESSION['user_id'];
            $receiverId = $_POST['receiver_id'] ?? null;
            
            $post = $this->postModel->getPost($postId);
            if (!$post) {
                $this->jsonResponse(['error' => 'Post introuvable.']);
                return;
            }

            // 1. Record the share globally
            $sharesCount = $this->postModel->sharePost($postId, $senderId);

            // 2. If a specific friend was selected, send them a message
            if ($receiverId) {
                $messageModel = $this->model('Message');
                $shareMsg = "A partagé un post : " . BASE_URL . "/Dashboard?post=" . $postId;
                $messageModel->send($senderId, $receiverId, $shareMsg);
            }

            $this->jsonResponse([
                'success' => true, 
                'shares_count' => $sharesCount, 
                'post' => $post
            ]);
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()]);
        }
    }

    // =================== EDIT ===================
    public function editPost($postId) {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->jsonResponse(['error' => 'Méthode invalide.']);
                return;
            }
            $userId = $_SESSION['user_id'];
            $content = $this->sanitize($_POST['content'] ?? '');
            if (empty($content)) {
                $this->jsonResponse(['error' => 'Le contenu ne peut pas être vide.']);
                return;
            }
            $result = $this->postModel->updatePost($postId, $userId, $content);
            if ($result) {
                $this->jsonResponse(['success' => true, 'content' => $content]);
            } else {
                $this->jsonResponse(['error' => 'Modification impossible (pas votre post ?)']);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()]);
        }
    }

    // =================== DELETE ===================
    public function deletePost($postId) {
        try {
            $userId = $_SESSION['user_id'];
            $result = $this->postModel->deletePost($postId, $userId);
            if ($result) {
                $this->jsonResponse(['success' => true]);
            } else {
                $this->jsonResponse(['error' => 'Suppression impossible (pas votre post ?)']);
            }
        } catch (Exception $e) {
            $this->jsonResponse(['error' => $e->getMessage()]);
        }
    }
}
