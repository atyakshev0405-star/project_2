<?php
// Authentication class

class Auth {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Login admin user
     * @param string $username
     * @param string $password
     * @return bool Success status
     */
    public function login($username, $password) {
        $query = "SELECT id, username, password FROM admins WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            
            // Verify password
            if($password == $row['password']) {
                // Set session
                $_SESSION['admin_id'] = $row['id'];
                $_SESSION['admin_username'] = $row['username'];
                $_SESSION['logged_in'] = true;
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Logout admin user
     */
    public function logout() {
        session_unset();
        session_destroy();
    }
    
    /**
     * Check if user is logged in
     * @return bool
     */
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Require admin access (redirect if not logged in)
     */
    public function requireAdmin() {
        if (!$this->isLoggedIn()) {
            header('Location: ../public/login.php');
            exit();
        }
    }
    
    /**
     * Hash password (for creating new admins)
     * @param string $password
     * @return string Hashed password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
?>
