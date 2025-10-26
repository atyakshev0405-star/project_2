<?php
// Group model class

class Group {
    private $conn;
    private $table = 'groups';
    
    public $id;
    public $group_name;
    public $student_count;
    public $stream;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Get all groups
    public function getAll() {
        $query = "SELECT * FROM `" . $this->table . "` ORDER BY stream, group_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    // Get groups by stream
    public function getByStream($stream) {
        $query = "SELECT * FROM `" . $this->table . "` WHERE stream = :stream ORDER BY group_name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':stream', $stream);
        $stmt->execute();
        return $stmt;
    }
    
    // Get single group
    public function getById($id) {
        $query = "SELECT * FROM `" . $this->table . "` WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    // Create group
    public function create() {
        $query = "INSERT INTO `" . $this->table . "` (group_name, student_count, stream) 
                  VALUES (:group_name, :student_count, :stream)";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->group_name = htmlspecialchars(strip_tags($this->group_name));
        $this->student_count = htmlspecialchars(strip_tags($this->student_count));
        $this->stream = htmlspecialchars(strip_tags($this->stream));
        
        // Bind
        $stmt->bindParam(':group_name', $this->group_name);
        $stmt->bindParam(':student_count', $this->student_count);
        $stmt->bindParam(':stream', $this->stream);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Delete group
    public function delete() {
        $query = "DELETE FROM `" . $this->table . "` WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Count groups by stream
    public function countByStream($stream) {
        $query = "SELECT COUNT(*) as total FROM `" . $this->table . "` WHERE stream = :stream";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':stream', $stream);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row['total'];
    }
    
    // Update group (добавьте этот метод если его нет)
    public function update() {
        $query = "UPDATE `" . $this->table . "` 
                  SET group_name = :group_name, student_count = :student_count, stream = :stream 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->group_name = htmlspecialchars(strip_tags($this->group_name));
        $this->student_count = htmlspecialchars(strip_tags($this->student_count));
        $this->stream = htmlspecialchars(strip_tags($this->stream));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        // Bind
        $stmt->bindParam(':group_name', $this->group_name);
        $stmt->bindParam(':student_count', $this->student_count);
        $stmt->bindParam(':stream', $this->stream);
        $stmt->bindParam(':id', $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
}
?>