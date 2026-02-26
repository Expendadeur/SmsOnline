<?php

class ProfileController extends Controller {
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
        $this->userModel = $this->model('User');
    }

    public function index() {
        $user = $this->userModel->findUserByUsername($_SESSION['username']);
        $this->view('profile/index', ['user' => $user]);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $userId = $_SESSION['user_id'];
            $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
            
            if (!$this->userModel->canUpdateCredentials($userId)) {
                $msg = 'Vous ne pouvez modifier vos identifiants qu\'une fois tous les 14 jours.';
                if ($isAjax) {
                    $this->jsonResponse(['error' => $msg]);
                } else {
                    $this->view('profile/index', [
                        'error' => $msg,
                        'user' => $this->userModel->findUserByUsername($_SESSION['username'])
                    ]);
                }
                return;
            }

            $data = [];
            if (!empty($_POST['username'])) $data['username'] = $this->sanitize($_POST['username']);
            if (!empty($_POST['telephone'])) $data['telephone'] = $this->sanitize($_POST['telephone']);
            if (!empty($_POST['password'])) $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);

            if ($this->userModel->updateCredentials($userId, $data)) {
                if (isset($data['username'])) $_SESSION['username'] = $data['username'];
                $msg = 'Profil mis à jour avec succès.';
                if ($isAjax) {
                    $this->jsonResponse(['success' => $msg]);
                } else {
                    $this->view('profile/index', [
                        'success' => $msg,
                        'user' => $this->userModel->findUserByUsername($_SESSION['username'])
                    ]);
                }
            } else {
                $msg = 'Erreur lors de la mise à jour.';
                if ($isAjax) {
                    $this->jsonResponse(['error' => $msg]);
                } else {
                    $this->view('profile/index', [
                        'error' => $msg,
                        'user' => $this->userModel->findUserByUsername($_SESSION['username'])
                    ]);
                }
            }
        }
    }
}
