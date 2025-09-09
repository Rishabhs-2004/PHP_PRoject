<?php
session_start();
require_once __DIR__ . '/classes/User.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all fields']);
        exit;
    }
    
    $user = new User();
    $user_data = $user->login($email, $password);
    
    if ($user_data) {
        $_SESSION['user_id'] = $user_data['id'];
        $_SESSION['user_name'] = $user_data['full_name'];
        $_SESSION['user_email'] = $user_data['email'];
        
        // Redirect to ultimate project
        header('Location: ultimate.php');
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>