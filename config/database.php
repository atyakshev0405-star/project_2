<?php
// Database configuration for Canteen Schedule System

define('DB_HOST', '127.0.1.28');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'php-canteen-app.local');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', 3306);

class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $charset = DB_CHARSET;
    private $port = DB_PORT;
    private $conn;

    public function connect() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->dbname . ";charset=" . $this->charset;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->conn = new PDO($dsn, $this->user, $this->pass, $options);
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }

        return $this->conn;
    }
}
?>
