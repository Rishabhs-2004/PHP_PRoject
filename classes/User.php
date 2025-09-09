<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    private $table = 'users';
    
    public $id;
    public $full_name;
    public $email;
    public $password;
    public $age;
    public $profile_picture;
    public $created_at;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    public function signup() {
        $query = "INSERT INTO " . $this->table . " 
                  (full_name, email, password, age, profile_picture) 
                  VALUES (:full_name, :email, :password, :age, :profile_picture)";
        
        $stmt = $this->conn->prepare($query);
        
        // Hash password
        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);
        
        $stmt->bindParam(':full_name', $this->full_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':age', $this->age);
        $stmt->bindParam(':profile_picture', $this->profile_picture);
        
        return $stmt->execute();
    }
    
    public function login($email, $password) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Debug: Check both password methods
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                return $user;
            }
        }
        return false;
    }
    
    public function emailExists($email) {
        $query = "SELECT id FROM " . $this->table . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    public function getUserById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function updateProfile($id, $full_name, $age) {
        $query = "UPDATE " . $this->table . " 
                  SET full_name = :full_name, age = :age 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':age', $age);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
}
?>