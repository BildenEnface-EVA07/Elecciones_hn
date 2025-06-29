<?php
class Database {
    private $host;
    private $username;
    private $password;
    private $database;
    private $connection;
    private static $instance = null;

    // Deben crear un .env dentro de la carpeta backend
    public function __construct() {
        $this->loadEnv();
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->username = $_ENV['DB_USER'] ?? '';
        $this->password = $_ENV['DB_PASSWORD'] ?? '';
        $this->database = $_ENV['DB_NAME'] ?? '';
        if ($this->connection === null) {
            $this->connection = mysqli_connect(
                $this->host,
                $this->username,
                $this->password,
                $this->database
            );
            if (!$this->connection) {
                die('Error de conexión: ' . mysqli_connect_error());
            }
            mysqli_set_charset($this->connection, 'utf8');
        }
    }

    private function loadEnv() {
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $_ENV[trim($key)] = trim($value);
                }
            }
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql) {
        return mysqli_query($this->connection, $sql);
    }

    public function fetchAll($result) {
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }
}