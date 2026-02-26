<?php

class Controller {
    public function model($model) {
        require_once '../models/' . $model . '.php';
        return new $model();
    }

    public function view($view, $data = []) {
        if (file_exists('../views/' . $view . '.php')) {
            require_once '../views/' . $view . '.php';
        } else {
            die('View does not exist');
        }
    }

    protected function sanitize($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }

    /**
     * Robust JSON response helper
     * - Clears any existing output buffers to prevent PHP warnings from breaking JSON
     * - Disables display_errors just-in-time
     */
    protected function jsonResponse($data) {
        // Prevent any previous output/warnings from contaminating the JSON
        if (ob_get_length()) ob_clean(); 
        
        // Ensure no future warnings appear in this specific response
        error_reporting(0);
        ini_set('display_errors', 0);

        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
