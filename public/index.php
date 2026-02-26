<?php
/**
 * SMSOnline Index Entry Point
 */
ob_start();

session_start();

// Suppress error display to prevent contamination of AJAX JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once '../config/config.php';
require_once '../core/Database.php';
require_once '../core/Controller.php';
require_once '../core/Model.php';
require_once '../core/Security.php';
require_once '../core/App.php';

$app = new App();
