<?php
function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        
        session_start();
    }
}

function isLoggedIn() {
    return isset($_SESSION['user']) && isset($_SESSION['user']['id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}
