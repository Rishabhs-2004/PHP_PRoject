<?php
session_start();
require_once __DIR__ . '/classes/User.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = new User();
    
    // Server-side validation
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $age = (int)$_POST['age'];
    
    $errors = [];
    
    if (strlen($full_name) < 2) {
        $errors[] = 'Full name must be at least 2 characters';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }
    
    if ($age < 13 || $age > 120) {
        $errors[] = 'Age must be between 13 and 120';
    }
    
    if ($user->emailExists($email)) {
        $errors[] = 'Email already exists';
    }
    
    // Handle file upload
    $profile_picture = 'default.jpg';
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = $_FILES['profile_picture']['type'];
        $file_size = $_FILES['profile_picture']['size'];
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = 'Only JPG, PNG, and GIF files are allowed';
        }
        
        if ($file_size > 5 * 1024 * 1024) {
            $errors[] = 'File size must be less than 5MB';
        }
        
        if (empty($errors)) {
            $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $profile_picture = uniqid() . '.' . $file_extension;
            $upload_path = 'uploads/' . $profile_picture;
            
            if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                $errors[] = 'Failed to upload profile picture';
            }
        }
    }
    
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
        exit;
    }
    
    // Create user
    $user->full_name = $full_name;
    $user->email = $email;
    $user->password = $password;
    $user->age = $age;
    $user->profile_picture = $profile_picture;
    
    if ($user->signup()) {
        echo json_encode(['success' => true, 'message' => 'Account created successfully', 'redirect' => 'login_simple.php']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create account']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>