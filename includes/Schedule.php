<?php
// Schedule model class

class Schedule {
    private $conn;
    private $table = 'schedules';
    
    public $id;
    public $group_id;
    public $schedule_date;
    public $arrival_time;
    public $stream;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Get schedule for a specific date
    public function getByDate($date) {
        $query = "SELECT s.*, g.group_name, g.student_count, g.stream 
                  FROM schedules s
                  JOIN `groups` g ON s.group_id = g.id
                  WHERE s.schedule_date = :date
                  ORDER BY s.arrival_time";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':date', $date);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Get schedule for date range
    public function getByDateRange($start_date, $end_date) {
        $query = "SELECT s.*, g.group_name, g.student_count, g.stream 
                  FROM " . $this->table . " s
                  JOIN `groups` g ON s.group_id = g.id
                  WHERE s.schedule_date BETWEEN :start_date AND :end_date
                  ORDER BY s.schedule_date, s.arrival_time";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
        return $stmt;
    }
    
    // Get schedule for specific group and date
    public function getByGroupAndDate($group_id, $date) {
        $query = "SELECT s.*, g.group_name 
                  FROM " . $this->table . " s
                  JOIN `groups` g ON s.group_id = g.id
                  WHERE s.group_id = :group_id AND s.schedule_date = :date
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':group_id', $group_id);
        $stmt->bindParam(':date', $date);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    // Create schedule entry
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (group_id, schedule_date, arrival_time, stream) 
                  VALUES (:group_id, :schedule_date, :arrival_time, :stream)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':group_id', $this->group_id);
        $stmt->bindParam(':schedule_date', $this->schedule_date);
        $stmt->bindParam(':arrival_time', $this->arrival_time);
        $stmt->bindParam(':stream', $this->stream);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Delete schedules for date range
    public function deleteByDateRange($start_date, $end_date) {
        $query = "DELETE FROM " . $this->table . " 
                  WHERE schedule_date BETWEEN :start_date AND :end_date";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Check if schedule exists for date range
    public function existsForDateRange($start_date, $end_date) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " 
                  WHERE schedule_date BETWEEN :start_date AND :end_date";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
        $row = $stmt->fetch();
        
        return $row['total'] > 0;
    }
}
?>