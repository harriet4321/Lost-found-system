<?php
// includes/session.php

session_start();

function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function login($username, $password) {
    global $db;
    
    $stmt = $db->prepare("SELECT id, username, password, full_name, user_type, email FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['email'] = $user['email'];
            return true;
        }
    }
    return false;
}

function logout() {
    session_destroy();
    header('Location: index.php');
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
?>