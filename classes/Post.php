<?php
require_once __DIR__ . '/../config/database.php';

class Post {
    private $conn;
    private $table = 'posts';
    
    public $id;
    public $user_id;
    public $description;
    public $image;
    public $likes;
    public $dislikes;
    public $created_at;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }
    
    public function createPost() {
        $query = "INSERT INTO " . $this->table . " 
                  (user_id, description, image) 
                  VALUES (:user_id, :description, :image)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':image', $this->image);
        
        return $stmt->execute();
    }
    
    public function getUserPosts($user_id) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = :user_id 
                  ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function deletePost($post_id, $user_id) {
        $query = "DELETE FROM " . $this->table . " 
                  WHERE id = :post_id AND user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':post_id', $post_id);
        $stmt->bindParam(':user_id', $user_id);
        
        return $stmt->execute();
    }
    
    public function updateReaction($user_id, $post_id, $reaction) {
        // Simple like/dislike increment
        $column = $reaction === 'like' ? 'likes' : 'dislikes';
        
        $query = "UPDATE " . $this->table . " 
                  SET $column = $column + 1 
                  WHERE id = :post_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':post_id', $post_id);
        
        return $stmt->execute();
    }
}
?>