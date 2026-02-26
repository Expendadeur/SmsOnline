<?php

class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = $this->model('User');
    }

    public function index() {
        $this->login();
    }

    public function login() {
        if (Security::isLoggedIn()) {
            header('Location: ' . BASE_URL . '/Dashboard/index');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $username = $this->sanitize($_POST['username']);
            $password = $_POST['password'];

            $loggedInUser = $this->userModel->login($username, $password);

            if ($loggedInUser) {
                Security::setSession($loggedInUser);
                $this->userModel->updateLastSeen($loggedInUser['id']);
                header('Location: ' . BASE_URL . '/Dashboard/index');
            } else {
                $data = ['error' => 'Identifiants incorrects'];
                $this->view('auth/login', $data);
            }
        } else {
            $this->view('auth/login');
        }
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
            
            $data = [
                'nom' => $this->sanitize($_POST['nom']),
                'prenom' => $this->sanitize($_POST['prenom']),
                'cni' => $this->sanitize($_POST['cni']),
                'username' => $this->sanitize($_POST['username']),
                'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                'telephone' => $this->sanitize($_POST['telephone']),
                'date_naissance' => $_POST['date_naissance'],
                'photo' => 'default_profile.png',
                'errors' => []
            ];

            // Validate Age >= 15
            $dob = new DateTime($data['date_naissance']);
            $today = new DateTime();
            $age = $today->diff($dob)->y;
            if ($age < 15) {
                $data['errors']['age'] = "Vous devez avoir au moins 15 ans.";
            }

            // Check uniqueness
            if ($this->userModel->findUserByCNI($data['cni'])) {
                $data['errors']['cni'] = "Ce CNI est déjà utilisé.";
            }
            if ($this->userModel->findUserByUsername($data['username'])) {
                $data['errors']['username'] = "Ce nom d'utilisateur est déjà pris.";
            }
            if ($this->userModel->findUserByPhone($data['telephone'])) {
                $data['errors']['telephone'] = "Ce numéro de téléphone est déjà utilisé.";
            }

            // Handle Photo Upload
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png'];
                $filename = $_FILES['photo']['name'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed)) {
                    $data['errors']['photo'] = "Seuls les fichiers JPG et PNG sont autorisés.";
                } else {
                    $newName = uniqid() . '.' . $ext;
                    if (move_uploaded_file($_FILES['photo']['tmp_name'], UPLOAD_DIR . $newName)) {
                        $data['photo'] = $newName;
                    } else {
                        $data['errors']['photo'] = "Erreur lors de l'upload de l'image.";
                    }
                }
            }

            if (empty($data['errors'])) {
                if ($this->userModel->register($data)) {
                    if ($isAjax) {
                        $this->jsonResponse(['success' => true]);
                    } else {
                        header('Location: ' . BASE_URL . '/Auth/login');
                    }
                } else {
                    $data['errors']['general'] = "Une erreur est survenue lors de l'inscription.";
                    if ($isAjax) {
                        $this->jsonResponse(['errors' => $data['errors']]);
                    } else {
                        $this->view('auth/register', $data);
                    }
                }
            } else {
                if ($isAjax) {
                    $this->jsonResponse(['errors' => $data['errors']]);
                } else {
                    $this->view('auth/register', $data);
                }
            }
        } else {
            $this->view('auth/register');
        }
    }

    public function logout() {
        Security::logout();
        header('Location: ' . BASE_URL . '/Auth/login');
    }
}
