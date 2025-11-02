<?php
class Database {
    private $host = "localhost";
    private $db_name = "reservasi_kelas";
    private $username = "root";
    private $password = "anin";  // Kosongkan untuk XAMPP default
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                $this->username, 
                $this->password,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
            // Untuk debugging, tampilkan detail koneksi
            echo "<br>Host: " . $this->host;
            echo "<br>Database: " . $this->db_name;
            echo "<br>Username: " . $this->username;
        }
        return $this->conn;
    }
}
?>