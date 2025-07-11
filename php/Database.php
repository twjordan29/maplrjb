<?php
require_once __DIR__ . '/config.php';

class Database
{
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USERNAME;
    private $password = DB_PASSWORD;
    public $conn;

    public function getConnection()
    {
        $this->conn = null;

        // Turn off error reporting to prevent echoing errors to the output
        mysqli_report(MYSQLI_REPORT_OFF);

        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);

        if ($this->conn->connect_error) {
            // Log the error to a file for production environments
            error_log("Database connection failed: " . $this->conn->connect_error);
            // Return null if connection fails, do not echo or die.
            return null;
        }

        // Set the character set to utf8mb4 for better unicode support
        $this->conn->set_charset("utf8mb4");

        return $this->conn;
    }
}
?>